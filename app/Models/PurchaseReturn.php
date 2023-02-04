<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function purchaseReturnProduct()
    {
        return $this->hasMany(PurchaseReturnProduct::class);
    }
}
