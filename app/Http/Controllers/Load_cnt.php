<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Load_ser;
use Illuminate\Support\Facades\Log;

class Load_cnt extends Controller
{
    //function for create load

    public function create_load(Request $request)
    {
        // Log::info('Create load request data: ', $request->all());

        $rule = [
            'prime_load'=>'nullable|string',
            'market' => 'required|string',
            'party_id' => 'required|string',
            'empty_weight' => 'required|string',
            'load_date' => 'required|string',
            'veh_no' => 'required|string',
            'dr_no' => 'required|string',
            'transporter' => 'required|string',
            'quality_price' => 'required|string',
            'filter_price' => 'required|string',
            'req_qty' => 'required|string',
            'truck_capacity' => 'required|string',
            'team' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Load creation logic here

            // logger()->info('Creating load with data: ', $request->all());
            // Assuming Load_ser is a service class that handles load creation
            $load = Load_ser::create_load($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Load created successfully',
                // 'data' => $load,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Load creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Other controller methods can be added here

    public function add_load_item(Request $request)
    {
        // Method implementation here

        

        $rule = [
            'load_id' => 'required|string',
            'farmer_id' => 'required|string',
            'product_id' => 'required|string',
            'total_piece' => 'required|string',
            'grace_piece' => 'required|string',
            'grace_per' => 'required|string',
            'bill_piece' => 'required|string',
            'price' => 'required|numeric',
            'commission' => 'required|string',
            'bill_amount' => 'required|string',
            'adv' => 'required|string',
            'quality' => 'required|string',
            'total_amt' => 'required|string',
        ];

      

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
              Log::info( 'Add load item request data: ' . json_encode($request->all(), JSON_PRETTY_PRINT));
              Log::error('Validation failed in add_load_item: ', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $load_item = Load_ser::add_load_item($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Load item added successfully',
                'data' => $load_item,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Adding load item failed: '.$e->getMessage(),
            ], 500);
        }

    }

    // function to get load list
    public function get_load_list(Request $request)
    {
        try {
            $loads = Load_ser::get_load_list();

            return response()->json([
                'success' => true,
                'data' => $loads['ongoing'],
                'completed' => $loads['completed'],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch load list: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to get individual load list
    public function ind_load_list(Request $request)
    {
        $rule = [
            'load_id' => 'required|string',
        ];  
        $validator = Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $load_items = Load_ser::ind_load_list($request->all());

            return response()->json([
                'success' => true,
                'data' => $load_items,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch individual load list: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to get individual load details
    public function ind_load_details(Request $request)
    {
        // Method implementation here

        $rule = [
            'load_item_id' => 'required|string',
            'type' => 'required|string|in:e_load,e_shift',
        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $load_details = Load_ser::ind_load_details($request->all());

            return response()->json([
                'success' => true,
                'data' => $load_details,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch individual load details: '.$e->getMessage(),
            ], 500);
        }
    }


    // function to add stock in entry

    public function add_purchase(Request $request)
    {
        // Method implementation here

        $rule = [
            'cat' => 'required|string|in:load,manual,purchase',
            'load_id' => 'nullable|string',
            'farmer_id' => 'required|string',
            'product_id' => 'required|string',
            'total_piece' => 'required|string',
            'grace_piece' => 'required|string',
            'grace_per' => 'required|string',
            'bill_piece' => 'required|string',
            'price' => 'required|string',
            'commission' => 'required|string',
            'bill_amount' => 'required|string',
            'adv' => 'nullable|string',
            'quality' => 'required|string',
            'total_amt' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            \Log::info( 'Add purchase request data: ' . json_encode($request->all(), JSON_PRETTY_PRINT));
            \Log::error('Validation failed in add_purchase: ', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $stock_in = Load_ser::add_purchase($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Stock in entry added successfully',
                'data' => $stock_in,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Adding stock in entry failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to add stock out entry

    public function add_sales(Request $request)
    {
        $rule = [
           'cat' => 'required|string|in:load,sales',
            'load_id' => 'nullable|string',
            'party_id' => 'required|string',
            'product_id' => 'required|string',
            'total_piece' => 'required|string',
            'grace_piece' => 'required|string',
            'grace_per' => 'required|string',
            'bill_piece' => 'required|string',
            'price' => 'required|string',
            'commission' => 'nullable|string',
            'bill_amount' => 'required|string',
            'adv' => 'nullable|string',
            'quality' => 'nullable|string',
            'total_amt' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $stock_out = Load_ser::add_sales($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Stock out entry added successfully',
                'data' => $stock_out,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Adding stock out entry failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // create the filter data

    public function add_filter(Request $request)
    {
        $rule = [
            'load_id' => 'required|string',
            'total' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $filter = Load_ser::add_filter($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Filter data added successfully',
                'data' => $filter,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Adding filter data failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to get filter list

    public function get_filter_list(Request $request)
    {
        try {
            $filters = Load_ser::get_filter_list($request->all());

            return response()->json([
                'success' => true,
                'data' => $filters,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch filter list: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to edit filter data

    public function edit_filter(Request $request)
    {
        $rule = [
            'filter_id' => 'required|string',
            'total' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $filter = Load_ser::edit_filter($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Filter data updated successfully',
                'data' => $filter,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Updating filter data failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to add_shift load items

    public function add_shift_item(Request $request)
    {
        $rule = [
            'cat' => 'required|string|in:load,others,stock',
            'load_id' => 'required|string',
            'to_load' => 'nullable|string',
            'party_id' => 'nullable|string',
            'product_id' => 'required|string',
            'total_piece' => 'required|string',
            'grace_piece' => 'required|string',
            'grace_per' => 'required|string',
            'bill_piece' => 'required|string',
            'price' => 'required|string',
            'bill_amount' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            Log::error('Validation failed in add_shift_item: ', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $shift_item = Load_ser::add_shift_item($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Load item shifted successfully',
                'data' => $shift_item,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Shifting load item failed: '.$e->getMessage(),
            ], 500);
        }
    }


    // functipon to get load self list

    public function load_self_list(Request $request)
    {
        $rule = [
            'load_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $loads = Load_ser::load_self_list($request->all());

            return response()->json([
                'success' => true,
                'data' => $loads,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch load self list: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to create stock shift entry

    public function stock_shift(Request $request)
    {
        $rule = [
            'load_id' => 'required|string',
            'total_piece' => 'required|string',
            'grace_piece' => 'required|string',
            'grace_per' => 'required|string',
            'bill_piece' => 'required|string',
            'price' => 'required|string',
            'bill_amount' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $stock_shift = Load_ser::stock_shift($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Stock shift entry created successfully',
                'data' => $stock_shift['stock'],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Creating stock shift entry failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to edit load fetch

    public function edit_load_fetch(Request $request)
    {
        $rule = [
            'load_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $load_data = Load_ser::edit_load_fetch($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $load_data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch load data for editing: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to edit load item

    public function edit_load_item(Request $request)
    {
        // $rule = [
        //     'load_item_id' => 'required|string',
        //     'total_piece' => 'required|string',
        //     'grace_piece' => 'required|string',
        //     'grace_per' => 'required|string',
        //     'bill_piece' => 'required|string',
        //     'price' => 'required|string',
        //     'commission' => 'required|string',
        //     'bill_amount' => 'required|string',
        //     'adv' => 'required|string',
        //     'quality' => 'required|string',
        //     'total_amt' => 'required|string',
        // ];

        // $validator = Validator::make($request->all(), $rule);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

        try {
            $load_item = Load_ser::edit_load_item($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Load item updated successfully',
                'data' => $load_item,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Updating load item failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to fetch load item data for editing

    public function edit_load_item_fetch(Request $request)
    {
        $rule = [
            'load_item_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $load_item_data = Load_ser::edit_load_item_fetch($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $load_item_data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch load item data for editing: '.$e->getMessage(),
            ], 500);
        }
    }   

    
}