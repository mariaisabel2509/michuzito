<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentController extends Controller
{
    // Mostrar formulario de pago
    public function show(Request $request)
    {
        return Inertia::render('Payments/Create', [
            'user' => $request->user()->load('profile'),
        ]);
    }

    // RF-005: Procesar pago
    public function store(Request $request)
    {
        $request->validate([
            'method'    => 'required|in:efectivo,transferencia',
            'amount'    => 'required|numeric|min:0.01',
            'reference' => 'required_if:method,transferencia|nullable|string',
            'notes'     => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // RF-006: Generar token de autenticacion
        $token = Str::random(64);

        $payment = Payment::create([
            'user_id'           => $user->id,
            'method'            => $request->method,
            'amount'            => $request->amount,
            'status'            => $request->method === 'efectivo' ? 'aprobado' : 'pendiente',
            'reference'         => $request->reference,
            'transaction_token' => hash('sha256', $token),
            'notes'             => $request->notes,
            'paid_at'           => $request->method === 'efectivo' ? now() : null,
        ]);

        // RF-007: Generar factura si pago aprobado
        if ($payment->status === 'aprobado') {
            $invoice = $this->generateInvoice($payment, $user);
            $user->notify(new PaymentConfirmedNotification($payment, $invoice));

            return redirect()->route('payments.invoice', $invoice->id)
                ->with('success', 'Pago registrado y factura generada.');
        }

        return redirect()->route('payments.pending', $payment->id)
            ->with('success', 'Pago registrado. Pendiente de confirmacion.');
    }

    // RF-005: Aprobar transferencia (admin)
    public function approve(Request $request, Payment $payment)
    {
        abort_unless(auth()->user()->hasRole('administrador'), 403);

        $payment->update([
            'status'  => 'aprobado',
            'paid_at' => now(),
        ]);

        $invoice = $this->generateInvoice($payment, $payment->user);
        $payment->user->notify(new PaymentConfirmedNotification($payment, $invoice));

        return back()->with('success', 'Pago aprobado y factura generada.');
    }

    // Ver factura
    public function invoice(Invoice $invoice)
    {
        abort_unless(
            auth()->id() === $invoice->user_id || auth()->user()->hasRole('administrador'),
            403
        );

        return Inertia::render('Payments/Invoice', [
            'invoice' => $invoice->load('payment'),
        ]);
    }

    // Ver pago pendiente
    public function pending(Payment $payment)
    {
        abort_unless(auth()->id() === $payment->user_id, 403);

        return Inertia::render('Payments/Pending', [
            'payment' => $payment,
        ]);
    }

    // Lista de pagos para admin
    public function index()
    {
        return Inertia::render('Payments/Index', [
            'payments' => Payment::with(['user', 'invoice'])
                ->orderBy('created_at', 'desc')
                ->paginate(20),
        ]);
    }

    // RF-007: Generar factura electronica
    private function generateInvoice(Payment $payment, $user): Invoice
    {
        $items = [
            [
                'descripcion'    => 'Pago ' . $payment->method,
                'cantidad'       => 1,
                'valor_unitario' => $payment->amount,
                'subtotal'       => $payment->amount,
            ]
        ];

        $subtotal = $payment->amount;
        $tax      = round($subtotal * 0.19, 2); // IVA 19%
        $total    = $subtotal + $tax;

        return Invoice::create([
            'payment_id'      => $payment->id,
            'user_id'         => $user->id,
            'invoice_number'  => Invoice::generateInvoiceNumber(),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'total'           => $total,
            'status'          => 'activa',
            'items'           => $items,
            'client_name'     => $user->name,
            'client_email'    => $user->email,
            'client_phone'    => $user->phone,
            'client_document' => $user->profile?->document_number,
            'issued_at'       => now(),
        ]);
    }
}