<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class Product_ser
{
    public static function create_product(array $data)
    {

        return Product::Create(
            // ['id' => $data['party_id'] ?? null],
            [
                'name_en' => $data['name_en'],
                // 'name_kn' => $data['name_kn'],
                'type' => $data['type'],
                'c_by' => Auth::guard('tenant')->user()->id ?? null,

            ]
        );
    }

    public static function edit_product(array $data)
    {
        return Product::where('id', $data['product_id'])->update(
            [
                'name_en' => $data['name_en'],
                'name_kn' => $data['name_kn'],

            ]
        );
    }
}
