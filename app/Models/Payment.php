<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'amount',
        'status',
        'reference',
        'encrypted_data',
        'transaction_token',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Nunca exponer datos encriptados
    protected $hidden = [
        'encrypted_data',
        'transaction_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    // RF-006: Encriptar datos sensibles
    public function setEncryptedDataAttribute($value)
    {
        $this->attributes['encrypted_data'] = encrypt($value);
    }

    public function getEncryptedDataAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }
}