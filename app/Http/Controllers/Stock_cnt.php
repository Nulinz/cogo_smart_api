<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Load_ser;
use App\Services\Stock_ser;
use Illuminate\Support\Facades\Log;

class Stock_cnt extends Controller
{
    // function for stock_home

    public function stock_home(Request $request)
    {
        
        try{
            $result = Stock_ser::stock_home();

        }catch(\Exception $e){

            Log::error('Error in stock_home: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request.'], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for stock_transaction_list

    public function stock_transaction_list(Request $request)
    {
        
        try{
            $result = Stock_ser::stock_transaction_list($request->all());

        }catch(\Exception $e){

            Log::error('Error in stock_transaction_list: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request.'], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for load_summary

    public function add_load_summary(Request $request)
    {
        $rules = [
            'load_id' => 'required|string',
            'filter_total'=>'nullable|string',
            'filter_billing'=>'nullable|string',
            'filter_price'=>'nullable|string',
            'filter_amount'=>'nullable|string', 
            'product_id'=>'nullable|string',
            'exp_loading'=>'nullable|string',
            'exp_misc'=>'nullable|string',
            'exp_rmc'=>'nullable|string',
            'total'=>'nullable|string',
            'grace'=>'nullable|string',
            'grace_per'=>'nullable|string',
            'billing_amount'=>'nullable|string',
            'avg_price'=>'nullable|string',
            'total_weight'=>'nullable|string',
            'empty_weight'=>'nullable|string',
            'net_weight'=>'nullable|string',
            'avg_per_weight'=>'nullable|string',
            'shift_loss'=>'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::add_load_summary($request->all());

        }catch(\Exception $e){

            Log::error('Error in load_summary: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for get load summary

    public function get_load_summary(Request $request)
    {
        $rules = [
            'load_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::get_load_summary($request->all());

        }catch(\Exception $e){

            Log::error('Error in get_load_summary: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for edit load summary

    public function edit_load_summary(Request $request)
    {
        $rules = [
            'type'=>'required|string|in:completed,draft',
            'load_id' => 'required|string',
            'filter_total'=>'nullable|string',
            'filter_billing'=>'nullable|string',
            'filter_price'=>'nullable|string',
            'filter_amount'=>'nullable|string', 
            'product_id'=>'nullable|string',
            'exp_loading'=>'nullable|string',
            'exp_misc'=>'nullable|string',
            'exp_rmc'=>'nullable|string',
            'total'=>'nullable|string',
            'grace'=>'nullable|string',
            'grace_per'=>'nullable|string',
            'billing_amount'=>'nullable|string',
            'avg_price'=>'nullable|string',
            'total_weight'=>'nullable|string',
            'empty_weight'=>'nullable|string',
            'net_weight'=>'nullable|string',
            'avg_per_weight'=>'nullable|string',
            'shift_loss'=>'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            Log::info('Validation failed in edit_load_summary: '.json_encode($validator->errors()));

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::edit_load_summary($request->all());

        }catch(\Exception $e){

            Log::error('Error in edit_load_summary: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // new summary function

    public function summary_new(Request $request)
    {
        $rules = [
            'load_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::summary_new($request->all());

        }catch(\Exception $e){

            Log::error('Error in summary_new: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for get stock product

    public function get_stock_product(Request $request)
    {
        
        try{
            $result = Stock_ser::get_stock_product($request->all());

        }catch(\Exception $e){

            Log::error('Error in get_stock_product: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request.'], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for add invoice

    public function add_invoice(Request $request)
    {
        $rules = [
            'load_id' => 'required|string',
            'ext_piece'=>'nullable|string',
            'grace_per'=>'nullable|string',
            'price'=>'nullable|string',
            'charges'=>'nullable|array',
            'description'=>'nullable|string',
            'file'=>'nullable|file|mimes:pdf,jpg,jpeg,png',
            'product_profit'=>'nullable|string',
            'loading'=>'nullable|string',
            'commission'=>'nullable|string',
            'final_loss'=>'nullable|array',
            'profit_loss'=>'nullable|string',
            'product_list'=>'required|array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::add_invoice($request->all());

        }catch(\Exception $e){

            Log::error('Error in add_invoice: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for add petty cash

    public function add_petty(Request $request)
    {
        $rules = [
            'emp_id' => 'required|string',
            'type'=>'required|string|in:petty,settle',
            'amount'=>'required|string',
            'date' => 'required|string',
            'method'=>'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::add_petty($validator->validated());

        }catch(\Exception $e){

            Log::error('Error in add_petty: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for petty cash individual

    public function petty_cash_ind(Request $request)
    {
        $rules = [
            'emp_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::petty_cash_ind($validator->validated());

        }catch(\Exception $e){

            Log::error('Error in petty_cash_ind: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for petty cash individual view all

    public function petty_cash_ind_transaction(Request $request)
    {
        $rules = [
            'emp_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::petty_cash_ind_transaction($validator->validated());

        }catch(\Exception $e){

            Log::error('Error in petty_cash_ind_view_all: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for update_loss_invoice

    public function update_loss_invoice(Request $request)
    {
         \Log::info('update_loss_invoice data: '. json_encode($request->all(), JSON_PRETTY_PRINT));
        $rules = [
            'load_id' => 'required|numeric',
            'final_loss_type'=>'required|string',
            'final_loss_amount'=>'required|string',
            'final_loss_piece'=>'nullable|string',
            'profit_loss'=>'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::info('Validation failed in update_loss_invoice: '.json_encode($validator->errors()));
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::update_loss_invoice($validator->validated());

        }catch(\Exception $e){

            Log::error('Error in update_loss_invoice: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for get invoice

    public function get_invoice(Request $request)
    {
        $rules = [
            'load_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::get_invoice($validator->validated());

        }catch(\Exception $e){

            Log::error('Error in get_invoice: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }

    // function for petty_cash_ind_view_all

    public function petty_cash_ind_view_all(Request $request)
    {
        $rules = [
            'emp_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::petty_cash_ind($validator->validated());

        }catch(\Exception $e){

            Log::error('Error in petty_cash_ind_view_all: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }   

    // function for invoice pdf

    public function invoice_pdf(Request $request)
    {
        $rules = [
            'load_id' => 'required|string',
            'type'=>'required|string|in:invoice,sales,others',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try{

            $result = Stock_ser::invoice_pdf($validator->validated());

        }catch(\Exception $e){

            Log::error('Error in invoice_pdf: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request -- '.$e->getMessage()], 500);
        }
       
        return response()->json(['success' => true, 'data' => $result], 200);
    }
}