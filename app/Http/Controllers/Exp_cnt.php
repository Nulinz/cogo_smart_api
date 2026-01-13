<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Load_ser;
use App\Services\Exp_ser;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class Exp_cnt extends Controller
{
    // Create Expense

    public function create_expense(Request $request)
    {
        Log::info("Create Expense Request", ['request' => $request->all()]);

        // Validation rules
        $rules = [
            'title'     => 'required|string',
            'exp_cat'   => 'required|string',
            'amount'    => 'required|string',
            'notes'     => 'nullable|string',
            // 'status'    => 'required|string|in:active,inactive',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{
             // Create Expenseexpense_home
             $expense = Exp_ser::create_expense($validator->validated());
        }catch(\Exception $e){
            Log::error("Validation Error", ['error' => $e->getMessage()]);
        }
      

        return response()->json([
            'success' => true,
            'data' => $expense,
        ], 200);
    }

    // function to get list of created expenses

    public function expense_created_list(Request $request)
    {
       $rules = [
           'emp_id' => 'required|string',
       ];
         $validator = Validator::make($request->all(), $rules);

         if( $validator->fails() ) {
             return response()->json([
                 'success' => false,
                 'errors' => $validator->errors(),
             ], 422);
         }
        try {
            $expenses = Exp_ser::expense_created_list($validator->validated());
        } catch (\Exception $e) {
            Log::error("Error fetching expenses", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expenses: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $expenses,
        ], 200);
    }

    // function to expense pay out

    public function expense_pay_out(Request $request)
    {
       $rules = [
           'emp_id' => 'required|string',
           'amount' => 'required|string',
           'pay_method' => 'required|string',
       ];
         $validator = Validator::make($request->all(), $rules);

         if( $validator->fails() ) {
             return response()->json([
                 'success' => false,
                 'errors' => $validator->errors(),
             ], 422);
         }
        try {
            // Logic for expense pay out can be added here

            $exp_out = Exp_ser::expense_pay_out($validator->validated());

        } catch (\Exception $e) {
            Log::error("Error processing expense pay out", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process expense pay out: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense pay out processed successfully',
        ], 200);
    }

    // function to update expense status to paid

    public function expense_status_update(Request $request)
    {
       $rules = [
           'expense_id' => 'required|string',
           'status' => 'required|string|in:approved,rejected',
       ];
         $validator = Validator::make($request->all(), $rules);

         if( $validator->fails() ) {
             return response()->json([
                 'success' => false,
                 'errors' => $validator->errors(),
             ], 422);
         }
        try {
            // Logic for expense status update can be added here
                $exp_update = Expense::where('id', $request->expense_id)
                    ->update([
                        'status' => $request->status,
                    ]);

        } catch (\Exception $e) {
            Log::error("Error updating expense status", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense status: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense status updated successfully',
        ], 200);
    }

    // function to get expense home data

    public function expense_home(Request $request)
    {
        try {

            $exp_data = Exp_ser::expense_home();
           

        } catch (\Exception $e) {
            Log::error("Error fetching expense home data", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expense home data: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $exp_data,
        ], 200);
    }

    // function to get expense week data

    public function expense_week(Request $request)
    {

        $rules = [
            'type' => 'required|string|in:week,month,three_month,six_month,year',
            
        ];
          $validator = Validator::make($request->all(), $rules);

          if( $validator->fails() ) {
              return response()->json([
                  'success' => false,
                  'errors' => $validator->errors(),
              ], 422);
          }

        try {

         $exp_data = Exp_ser::expense_week($validator->validated());

        } catch (\Exception $e) {
            Log::error("Error fetching expense week data", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expense week data: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $exp_data,
        ], 200);
    }

    // function to get expense emp profile data

    public function expense_emp_profile(Request $request)
    {
       $rules = [
           'emp_id' => 'required|string',
       ];
         $validator = Validator::make($request->all(), $rules);

         if( $validator->fails() ) {
             return response()->json([
                 'success' => false,
                 'errors' => $validator->errors(),
             ], 422);
         }
        try {
            $emp_profile = Exp_ser::expense_emp_profile($validator->validated());
        } catch (\Exception $e) {
            Log::error("Error fetching expense emp profile", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expense emp profile: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $emp_profile,
        ], 200);
    }

    // function to get expense list

    public function get_exp_list(Request $request)
    {
       try{
             $users = User::where('status', 'active')
                    ->select('id', 'name', 'role','location')
                    ->get()
                    ->map(function ($user) {
                       
                        $petty_cash = Exp_ser::expense_emp_profile(['emp_id' => $user->id]);
                        // $petty_cash = Expense::where('c_by', $user->id)
                        //                 ->where('status', 'approved')
                        //                 ->sum('amount');
                        // $user_paid_list = Farmer_cash::where('c_by', $user->id)->sum('amount');
                        $user->balance = $petty_cash['exp_balance'];
    
                        return $user;
                    });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $users,
        ], 200);
    }

    // function to get expense overall list

    public function expense_overall_list(Request $request)
    {
         $rules = [
           'emp_id' => 'required|string',
       ];
         $validator = Validator::make($request->all(), $rules);

         if( $validator->fails() ) {
             return response()->json([
                 'success' => false,
                 'errors' => $validator->errors(),
             ], 422);
         }

       try{
            $emp_profile = Exp_ser::expense_emp_profile($validator->validated());
             $exp_list = $emp_profile['exp_transaction'];

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $exp_list,
        ], 200);
    }
}
