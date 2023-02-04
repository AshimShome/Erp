<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillProduct extends Model
{
    protected $fillable = [
        'product_id',
        'bill_id',
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    public function product()
    {
//        return $this->hasOne('App\Models\ProductService', 'id', 'product_id')->first();
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id');

    }


    public function venderCount()
    {
        return $this->hasOne('App\Models\Bill', 'vender_id', 'id');
    }
}
