<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'payment_proof_url',
        'shipping_address'
    ];

    // 1 Order punya Banyak Item
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // 1 Order milik 1 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
