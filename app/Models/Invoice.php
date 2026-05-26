<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'payment_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'tax',
        'total',
        'status',
        'items',
        'client_name',
        'client_email',
        'client_phone',
        'client_document',
        'issued_at',
    ];

    protected $casts = [
        'subtotal'  => 'decimal:2',
        'tax'       => 'decimal:2',
        'total'     => 'decimal:2',
        'items'     => 'array',
        'issued_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // RF-007: Generar numero consecutivo unico
    public static function generateInvoiceNumber(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $number = $last ? (int) substr($last->invoice_number, 4) + 1 : 1;
        return 'FAC-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}