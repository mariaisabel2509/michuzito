<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.note'   => 'nullable|string|max:300',
            'address'        => 'required|string|max:255|min:10',
            'payment_method' => 'required|in:efectivo,transferencia',
            'notes'          => 'nullable|string|max:500',
        ], [
            'address.required' => 'La direccion de entrega es obligatoria.',
            'address.min'      => 'La direccion debe tener al menos 10 caracteres.',
            'items.required'   => 'Debes agregar al menos un producto.',
        ]);

        $user     = $request->user();
        $items    = [];
        $subtotal = 0;

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['id']);

            if (!$product->is_available || $product->stock < $item['qty']) {
                return back()->withErrors(['items' => "El producto {$product->name} no tiene suficiente stock."]);
            }

            $itemSubtotal = $product->price * $item['qty'];
            $subtotal    += $itemSubtotal;

            $items[] = [
                'id'        => $product->id,
                'name'      => $product->name,
                'price'     => $product->price,
                'qty'       => $item['qty'],
                'subtotal'  => $itemSubtotal,
                'image_url' => $product->image_url_full,
                'note'      => $item['note'] ?? '',
            ];
        }

        $tax   = round($subtotal * 0.19, 2);
        $total = $subtotal + $tax;

        // Como solo existe un repartidor en el sistema, se asigna automaticamente
        $repartidor = User::role('repartidor')->where('is_active', true)->first();

        $order = Order::create([
            'user_id'        => $user->id,
            'repartidor_id'  => $repartidor?->id,
            'assigned_at'    => $repartidor ? now() : null,
            'status'         => 'en_proceso',
            'items'          => $items,
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'total'          => $total,
            'address'        => $request->address,
            'notes'          => $request->notes,
            'payment_method' => $request->payment_method,
        ]);

        foreach ($request->items as $item) {
            Product::where('id', $item['id'])->decrement('stock', $item['qty']);
            Product::where('id', $item['id'])->where('stock', 0)->update(['is_available' => false]);
        }

        activity()->causedBy($user)->performedOn($order)->log('order_created');

        // Notificar al unico repartidor del nuevo pedido
        if ($repartidor && $repartidor->email) {
            try {
                $repartidor->notify(new OrderStatusNotification($order));
            } catch (\Exception $e) {}
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Pedido creado correctamente.');
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        $user = auth()->user();

        abort_unless(
            $order->user_id === $user->id ||
            $order->repartidor_id === $user->id ||
            $order->vendedor_id === $user->id ||
            $user->hasRole('administrador'),
            403
        );

        return Inertia::render('Orders/Show', [
            'order' => $order->load('cliente', 'repartidor', 'vendedor'),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:en_proceso,en_camino,entregado,cancelado',
        ]);

        $user = auth()->user();

        abort_unless(
            $user->hasRole('administrador') ||
            $order->repartidor_id === $user->id ||
            $order->vendedor_id === $user->id,
            403
        );

        if (!$order->canTransitionTo($request->status)) {
            return back()->withErrors(['status' => 'Transicion de estado no permitida.']);
        }

        $timestamps = [
            'en_camino' => ['picked_up_at' => now()],
            'entregado' => ['delivered_at' => now()],
            'cancelado' => ['cancelled_at' => now()],
        ];

        $order->update(array_merge(
            ['status' => $request->status],
            $timestamps[$request->status] ?? []
        ));

        activity()
            ->causedBy($user)
            ->performedOn($order)
            ->withProperties(['new_status' => $request->status])
            ->log('order_status_updated');

        if (in_array($request->status, ['en_camino', 'entregado', 'cancelado'])) {
            try {
                $order->cliente->notify(new OrderStatusNotification($order));
            } catch (\Exception $e) {}
        }

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function repartidorOrders(Request $request)
    {
        $orders = Order::where('repartidor_id', $request->user()->id)
            ->with('cliente')
            ->orderByRaw("FIELD(status, 'en_camino', 'en_proceso', 'entregado', 'cancelado')")
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/RepartidorOrders', [
            'orders' => $orders,
        ]);
    }

    public function vendedorOrders(Request $request)
    {
        $user = $request->user();

        $orders = Order::where(function($q) use ($user) {
                $q->where('vendedor_id', $user->id)
                  ->orWhere(function($q2) {
                      $q2->whereNull('vendedor_id')->where('status', 'en_proceso');
                  });
            })
            ->with('cliente')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/VendedorOrders', [
            'orders' => $orders,
        ]);
    }

    public function claimOrder(Request $request, Order $order)
    {
        abort_unless(auth()->user()->hasRole('vendedor'), 403);

        if ($order->status !== 'en_proceso' || $order->vendedor_id !== null) {
            return back()->withErrors(['order' => 'Este pedido no esta disponible.']);
        }

        $order->update(['vendedor_id' => auth()->id()]);
        activity()->causedBy(auth()->user())->performedOn($order)->log('order_claimed_by_vendor');

        return back()->with('success', 'Pedido tomado correctamente.');
    }

    public function adminIndex()
    {
        $orders = Order::with(['cliente', 'repartidor', 'vendedor'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $repartidores = User::role('repartidor')->where('is_active', true)->get(['id', 'name', 'phone']);

        return Inertia::render('Orders/AdminOrders', [
            'orders'       => $orders,
            'repartidores' => $repartidores,
        ]);
    }

    public function assignRepartidor(Request $request, Order $order)
    {
        abort_unless(auth()->user()->hasRole('administrador'), 403);

        $request->validate([
            'repartidor_id' => 'required|exists:users,id',
        ]);

        $order->update([
            'repartidor_id' => $request->repartidor_id,
            'assigned_at'   => now(),
        ]);

        $repartidor = User::find($request->repartidor_id);
        if ($repartidor && $repartidor->email) {
            try {
                $repartidor->notify(new OrderStatusNotification($order));
            } catch (\Exception $e) {}
        }

        activity()->causedBy(auth()->user())->performedOn($order)
            ->withProperties(['repartidor_id' => $request->repartidor_id])
            ->log('repartidor_assigned');

        return back()->with('success', 'Repartidor asignado correctamente.');
    }
}