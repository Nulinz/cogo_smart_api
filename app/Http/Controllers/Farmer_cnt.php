<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Services\Farmer_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Farmer_cnt extends Controller
{

 // method to create or update party

    public function query(Farmer_ser $farmer_ser)
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $start = microtime(true);

       $data = $farmer_ser->farmer_profile_load([]);

        $end = microtime(true);

        // Collect queries
        $queries = collect(DB::getQueryLog())->map(function ($q) {
                return [
                    'sql' => $q['query'],
                    'time_ms' => $q['time'],
                ];
            });

            // Slow queries (top 5)
            $slowQueries = $queries
                ->sortByDesc('time_ms')
                ->take(5)
                ->values();

            // Duplicate queries (grouped)
            $duplicateQueries = $queries
                ->groupBy('sql')
                ->map(function ($items, $sql) {
                    return [
                        'sql' => $sql,
                        'count' => $items->count(),
                        'total_time_ms' => $items->sum('time_ms'),
                    ];
                })
                ->filter(fn($q) => $q['count'] > 1) // only duplicates
                ->sortByDesc('count')
                ->take(5)
                ->values();

            return response()->json([
                // 'data' => $data, // enable if needed
                'debug' => [
                    'query_count' => $queries->count(),
                    'total_query_time_ms' => round($queries->sum('time_ms'), 2),
                    'total_time_ms' => round(($end - $start) * 1000, 2),

                    // 🔥 Advanced insights
                    'slow_queries' => $slowQueries,
                    'duplicate_queries' => $duplicateQueries,
                ]
            ]);
    }


    // Controller methods here

    public function create_farm(Request $request)
    {
        // Method implementation here

        $rule = [
            'farm_id' => 'nullable|string',
            'farm_en' => 'required|string',
            'farm_nick_en' => 'required|string',
            'location' => 'required|string',
            'ph_no' => 'required|string',
            'wp_no' => 'required|string',
            'open_type' => 'required|string',
            'open_bal' => 'required|string',
            'acc_type' => 'nullable|string',
            'b_name' => 'nullable|string',
            'acc_name' => 'nullable|string',
            'acc_no' => 'nullable|string',
            'ifsc' => 'nullable|string',
            'upi' => 'nullable|string',
            'adv' => 'nullable|string',
            'open_type' => 'required|string|in:give,get',
            'open_bal' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        //  \Log::info('Create farm request data: ', $request->all());

        if ($validator->fails()) {

            // \Log::error('Validation failed in create_farm: ', $validator->errors()->toArray());

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $farmer = Farmer_ser::create_farm($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Farmer created/updated successfully',
                'data' => $farmer,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer created/updated failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // / fucntion form individual farmer details

    public function get_farmer_details(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $farmer = Farmer_ser::get_farmer_details($request->farm_id);

            return response()->json([
                'success' => true,
                'data' => $farmer,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer details: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for list of farmers

    public function get_farmer_list(Request $request)
    {
        try {
            $db = $request->tenant_db;
            $cursor = $request->cursor;
            $keyword = $request->keyword;

           // 🔍 If search → skip cache
            if (!empty($keyword)) {
                $farmers = Farmer_ser::get_all_farmers_opt($request->all());
            } else {

                $cacheKey = "farmer_list_{$db}";

                if (!$cursor) {
                    $farmers = Cache::store('redis')->remember($cacheKey, 5, function () use ($request) {
                        return Farmer_ser::get_all_farmers_opt($request->all());
                    });
                } else {
                    // pagination → no cache
                    $farmers = Farmer_ser::get_all_farmers_opt($request->all());
                }
            }

            return response()->json([
                'success' => true,
                'data' => $farmers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmers list: ' . $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
       
    }
    // function for farmer profile

    public function farmer_profile(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $profile = Farmer_ser::farmer_profile($request->all());

            return response()->json([
                'success' => true,
                'data' => $profile,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching farmer profile: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer profile: '.$e->getMessage(),
            ], 500);
        }
    }

     // function for farmer profile

    public function farmer_profile_load(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $profile_load = Farmer_ser::farmer_profile_load($request->all());

            return response()->json([
                'success' => true,
                'data' => $profile_load,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching farmer profile: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer profile: '.$e->getMessage(),
            ], 500);
        }
    }


    // function for farmer advance pending

    public function farmer_advance_pending(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $advance = Farmer_ser::farmer_advance_pending($request->all());

            return response()->json([
                'success' => true,
                'data' => $advance,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer advance pending: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer pay out

    public function farmer_pay_out(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            'type' => 'required|string|in:advance,purchase',
            'amount' => 'required|string',
            'pay_method' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $payout = Farmer_ser::farmer_pay_out($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Farmer pay out recorded successfully',
                'data' => $payout,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record farmer pay out: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer pay in

    public function farmer_pay_in(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            'type' => 'required|string|in:advance_deduct',
            'amount' => 'required|string',
            'pay_method' => 'required|string',
        ];

        // \Log::info('Farmer pay in request data: ', $request->all());

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $payin = Farmer_ser::farmer_pay_in($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Farmer pay in recorded successfully',
                'data' => $payin,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record farmer pay in: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer pay edit

    public function farmer_pay_edit(Request $request)
    {
        $rule = [
            'payment_id' => 'required|string',
            // 'farm_id' => 'required|string',
            // 'type' => 'required|string|in:advance,advance_deduct,purchase',
            'amount' => 'required|string',
            'pay_method' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $payedit = Farmer_ser::farmer_pay_edit($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Farmer payment edited successfully',
                'data' => $payedit,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to edit farmer payment: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer inactive

    public function farmer_inactive(Request $request)
    {
        $keyword = $request->keyword;


        try {

            $farmer = Farmer_ser::farmer_inactive($request->all());
            // if (!empty($keyword)) {
            //     $farmer = Farmer_ser::farmer_inactive_opt($request->all());
            // } else {    
            // }

            return response()->json([
                'success' => true,
                'message' => 'Farmer inactive List fetched successfully',
                'data' => $farmer,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inactive farmer list: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer status update

    public function farmer_status_update(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            'status' => 'required|string|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $farmer = Farmer::find($request->farm_id);

            if ($farmer) {
                $farmer->status = $request->status;
                $farmer->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Farmer status updated successfully',
                'data' => $farmer,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update farmer status: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer advance report

    public function farmer_advance_report(Request $request)
    {

        try {
            $report = Farmer_ser::farmer_advance_report();

            return response()->json([
                'success' => true,
                'data' => $report,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer advance report: '.$e->getMessage(),
            ], 500);
        }

    }

    // function for farmer coconut report

    public function farmer_coconut_report(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $report = Farmer_ser::farmer_coconut_report($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $report,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer coconut report: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer advance deduct report

    public function farmer_advance_deduct_report(Request $request)
    {

        $rule = [
            'farm_id' => 'required|string',
            // 'start_date' => 'nullable|date',
            // 'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $report = Farmer_ser::farmer_advance_deduct_report($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $report,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer advance deduct report: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer payment out report

    public function farmer_payment_out_report(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $report = Farmer_ser::farmer_payment_out_report($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $report,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer payment out report: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer payment pending report

    public function farmer_payment_pending_report(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            // 'start_date' => 'nullable|date',
            // 'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $report = Farmer_ser::farmer_payment_pending_report($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $report,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer payment pending report: '.$e->getMessage(),
            ], 500);
        }
    }
}
