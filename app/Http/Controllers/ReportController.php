<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function ventas(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $ordersEntregados = Order::where('status', 'entregado')
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->get();

        $totalVentas  = $ordersEntregados->sum('total');
        $totalPedidos = Order::whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])->count();

        $productosMasVendidos = collect();

        foreach ($ordersEntregados as $order) {
            foreach (($order->items ?? []) as $item) {
                $name     = $item['name'] ?? 'Producto';
                $qty      = (int) ($item['qty'] ?? 0);
                $subtotal = (float) ($item['subtotal'] ?? 0);

                $current = $productosMasVendidos->get($name, [
                    'name' => $name,
                    'total_vendido' => 0,
                    'total_ingresos' => 0,
                ]);

                $current['total_vendido']  += $qty;
                $current['total_ingresos'] += $subtotal;

                $productosMasVendidos->put($name, $current);
            }
        }

        $productosMasVendidos = $productosMasVendidos->values()->sortByDesc('total_vendido')->values();

        return Inertia::render('Reports/Ventas', [
            'totalVentas'          => $totalVentas,
            'totalPedidos'         => $totalPedidos,
            'productosMasVendidos' => $productosMasVendidos,
            'desde'                => $desde,
            'hasta'                => $hasta,
        ]);
    }

    public function inventario()
    {
        $productos = Product::select('name', 'category', 'price', 'stock', 'is_available')->get();

        $disponibles = $productos->where('is_available', true)->count();
        $agotados    = $productos->where('is_available', false)->count();

        return Inertia::render('Reports/Inventario', [
            'productos'   => $productos,
            'disponibles' => $disponibles,
            'agotados'    => $agotados,
        ]);
    }

    public function pedidos(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $query = Order::with('cliente')
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);

        $totalPedidos = (clone $query)->count();

        $porEstado = (clone $query)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $pedidos = $query->orderByDesc('created_at')->get()->map(function ($order) {
            return [
                'id'            => $order->id,
                'customer_name' => $order->cliente->name ?? 'N/A',
                'total'         => $order->total,
                'status'        => $order->status,
                'created_at'    => $order->created_at->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('Reports/Pedidos', [
            'totalPedidos' => $totalPedidos,
            'porEstado'    => $porEstado,
            'pedidos'      => $pedidos,
            'desde'        => $desde,
            'hasta'        => $hasta,
        ]);
    }

    public function tiempos(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $orders = Order::with(['cliente', 'repartidor'])
            ->whereNotNull('picked_up_at')
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderByDesc('delivered_at')
            ->get();

        $entregas = $orders->map(function ($order) {
            return [
                'id'              => $order->id,
                'customer_name'   => $order->cliente->name ?? 'N/A',
                'delivery_person' => $order->repartidor->name ?? 'N/A',
                'started_at'      => $order->picked_up_at->format('Y-m-d H:i'),
                'delivered_at'    => $order->delivered_at->format('Y-m-d H:i'),
                'minutos'         => $order->picked_up_at->diffInMinutes($order->delivered_at),
            ];
        });

        $promedioMinutos = $entregas->avg('minutos');

        return Inertia::render('Reports/Tiempos', [
            'entregas'        => $entregas,
            'promedioMinutos' => round($promedioMinutos ?? 0, 1),
            'desde'           => $desde,
            'hasta'           => $hasta,
        ]);
    }
}