<?php

namespace App\Http\Controllers;

use App\Models\Prime_load;
use App\Services\Farmer_ser;
use App\Services\Party_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Chart_cnt extends Controller
{
    public function profit_loss_month(Request $request)
    {
        try {

            $loads = Prime_load::with('invoices')
                // ->where('status', 'inv_completed')
                ->where('id', 10)
                ->whereYear('created_at', now()->year)
                ->get()
                ->groupBy(function ($load) {
                    return $load->created_at->format('m');
                })
                ->map(function ($monthLoads) {
                    return $monthLoads->sum(function ($load) {

                        return $load->invoices->sum(function ($invoice) {

                            $loss_cat = $invoice->final_loss['amount'] ?? 0;
                            $profit_loss = $invoice->profit_loss ?? 0;

                            $final_profit_loss = $profit_loss - $loss_cat;

                            return $final_profit_loss;
                        });
                    });
                });

            return response()->json(['success' => true, 'data' => $loads]);
        } catch (\Exception $e) {
            Log::error('Error in profit_loss_month: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while fetching chart data.'], 500);
        }
    }

    // profit loss by party

    public function profit_loss_party(Request $request)
    {
        try {

            $loads = Prime_load::with(['invoices', 'load_list'])
                // ->where('status', 'inv_completed')
                ->where('party_id', $request->party_id)
                // ->where('id', 10)
                ->whereYear('created_at', now()->year)
                ->get()
                ->groupBy(function ($load) {
                    return $load->created_at->format('m');
                })
                ->map(function ($monthLoads) {
                    $final_loss = 0;
                    $profit_loss = 0;

                    foreach ($monthLoads as $load) {
                        foreach ($load->invoices as $invoice) {

                            $loss_cat = $invoice->final_loss['amount'] ?? 0;
                            $profit = $invoice->profit_loss ?? 0;

                            $profit_loss += $profit;
                            $final_loss += $loss_cat;
                        }
                    }

                    return [
                        'profit_loss' => $profit_loss,
                        'final_loss' => $final_loss,
                    ];
                })->filter(function ($value) {
                    return $value['profit_loss'] != 0;
                });

            return response()->json(['success' => true, 'data' => $loads]);
        } catch (\Exception $e) {
            Log::error('Error in profit_loss_party: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while fetching chart data.'], 500);
        }
    }

    // function for party and farmer advance

    public function party_farmer_advance(Request $request)
    {
        try {
            // Fetch party advance
            $dash_farmer = Farmer_ser::get_all_farmers_opt([]);

            $farmer_card = $dash_farmer['head_card'];

            $dash_party = Party_ser::get_all_party_opt([]);

            $party_card = $dash_party['head_card'];

            return response()->json(['success' => true, 'data' => ['farmer_adv' => $farmer_card['adv_card'], 'farmer_balance' => $farmer_card['balance_card'], 'party_balance' => $party_card['balance']]]);

            // \Log::info('fetch data', ['farmer_card' => $farmer_card, 'party_card' => $party_card, 'user_id' => Auth::guard('tenant')->user()->id]);

        } catch (\Exception $e) {
            Log::error('Error in party_farmer_advance: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while fetching advance data.'], 500);
        }
    }

    public function vehicle_count_month(Request $request)
    {
        try {

            $loads = Prime_load::whereYear('created_at', now()->year)
             // ->where('status', 'inv_completed')
                ->get()
                ->groupBy(function ($load) {
                    return $load->created_at->format('m');
                })
                ->map(function ($monthLoads) {

                    return $monthLoads->count(); // number of vehicles / loads

                });

            return response()->json(['success' => true, 'data' => $loads]);
        } catch (\Exception $e) {
            Log::error('Error in vehicle_count_month: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while fetching chart data.'], 500);
        }
    }

    public function profit_loss_year(Request $request)
    {
        try {

            $year = $request->year ?? now()->year;

            $loads = Prime_load::with(['invoices', 'load_list'])
                // ->where('party_id', $request->party_id)
                ->whereYear('created_at', $year)
                ->get();

            $profit_loss = 0;
            $final_loss = 0;

            foreach ($loads as $load) {
                foreach ($load->invoices as $invoice) {

                    $loss_cat = $invoice->final_loss['amount'] ?? 0;
                    $profit = $invoice->profit_loss ?? 0;

                    $profit_loss += $profit;
                    $final_loss += $loss_cat;
                }
            }

            $result = [
                'profit_loss' => $profit_loss,
                'final_loss' => $final_loss,
                'year' => $year,
            ];

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {

            Log::error('Error in profit_loss_year: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching chart data.',
            ], 500);
        }
    }
}

