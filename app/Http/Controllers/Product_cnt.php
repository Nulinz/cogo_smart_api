<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Product_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Product_cnt extends Controller
{
    public function create_product(Request $request)
    {

        $rule = [
            'name_en' => 'required|string',
            // 'name_kn' => 'required|string',
            'type' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $product = Product_ser::create_product($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // edit the product name

    public function edit_product(Request $request)
    {
        // Method implementation here
        $rule = [
            'product_id' => 'required|integer',
            'name_en' => 'required|string',
            // 'name_kn' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product_ser::edit_product($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // active or deactivate product

    public function active_product(Request $request)
    {
        // Method implementation here

        $rule = [
            'product_id' => 'required|string',
            'status' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product::where('id', $request->product_id)->update(
                [
                    'status' => $request->status,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully',
                'data' => $product,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product status update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to get product list

    public function get_product_list(Request $request)
    {
        try {
            $products = Product::all();

            return response()->json([
                'success' => true,
                'data' => $products,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product list: '.$e->getMessage(),
            ], 500);
        }
    }

    // fucntion to get product details

    public function get_product_details(Request $request)
    {
        $rule = [
            'product_id' => 'required|integer',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product_ser::get_product_details($request->all());

            return response()->json([
                'success' => true,
                'data' => $product,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product details: '.$e->getMessage(),
            ], 500);
        }
    }
}
