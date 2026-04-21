<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\E_invoice;
use App\Models\M_invoice;
use App\Models\Party;
use App\Models\Party_cash;
use App\Models\Prime_load;
use App\Models\Shift;
use App\Models\Stock_out;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Party_ser
{
    public static function create_party(array $data)
    {

        $party = Party::find($data['party_id'] ?? 0);

        if ($party) {
            // Fill the model with new data
            $party->fill([
                'party_en' => $data['party_en'],
                'party_nick_en' => $data['party_nick_en'],
                'com_name' => $data['com_name'],
                'com_add' => $data['com_add'],
                'party_location' => $data['party_location'],
                'party_ph_no' => $data['party_ph_no'],
                'party_wp_no' => $data['party_wp_no'],
                'party_open_type' => $data['party_open_type'],
                'party_open_bal' => $data['party_open_bal'],
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);

            // Save only if there are changes
            if ($party->isDirty()) {
                $party->save();
            }

        } else {

            $party = Party::create([
                'party_en' => $data['party_en'],
                'party_kn' => $data['party_kn'] ?? null,
                'party_nick_en' => $data['party_nick_en'],
                'party_nick_kn' => $data['party_nick_kn'] ?? null,
                'com_name' => $data['com_name'],
                'com_add' => $data['com_add'],
                'party_location' => $data['party_location'],
                'party_ph_no' => $data['party_ph_no'],
                'party_wp_no' => $data['party_wp_no'],
                'party_open_type' => $data['party_open_type'],
                'party_open_bal' => $data['party_open_bal'],
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);

            if (! empty($data['party_b_name'])) {
                $party_bank = Bank::create([
                    'type' => 'party',
                    'f_id' => $party->id,
                    'acc_type' => $data['party_acc_type'] ?? null,
                    'b_name' => $data['party_b_name'] ?? null,
                    'acc_name' => $data['party_acc_name'] ?? null,
                    'acc_no' => $data['party_acc_no'] ?? null,
                    'ifsc' => $data['party_ifsc'] ?? null,
                    'upi' => $data['party_upi'] ?? null,
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]);
            }

        }

        return $party;

    }

    public static function get_party_details($party_id)
    {
        $party = Party::findOrFail($party_id);

        $party->transaction_count = Party_cash::where('party_id', $party_id)->count();

        return $party;
    }

    // fetch party list

    // public static function get_all_party()
    // {
    //     $data =  Party::where('status','active')->orderBy('fav','DESC')->get()->map(function($item){

    //          $give_bal = 0;
    //          $get_bal  = 0;

    //         $party_cash_ind = Party_cash::where('party_id', $item->id)->select('id','type','amount','method','created_at')->get();

    //         $party_load_ind = Prime_load::where('party_id', $item->id)->where('status', 'active')->where('load_status','inv_completed')->pluck('id');

    //         $inv_data_ind = E_invoice::whereIn('load_id', $party_load_ind)->select('id','bill_amt')->get();

    //         $inv_amount_ind = $inv_data_ind->sum('bill_amt');

    //         $party_sales_ind = Stock_out::where('cat','sales')->where('farm_id', $item->id)->sum('bill_amount');

    //         $in_cash_ind = $party_cash_ind->where('type','pay_in')->sum('amount');
    //         $out_cash_ind = $party_cash_ind->where('type','pay_out')->sum('amount');
    //         $pt_bal = ($inv_amount_ind + $party_sales_ind +  $out_cash_ind) - $in_cash_ind;

    //     //    \Log::info('Party ID: ' . $inv_amount_ind);
    //     //    \Log::info('Party Sales: ' . $party_sales_ind);
    //     //    \Log::info('In Cash: ' . $in_cash_ind);
    //     //    \Log::info('Out Cash: ' . $out_cash_ind);
    //     //    \Log::info('Party Balance Before Opening: ' . $pt_bal);

    //          if ($item->party_open_type === 'give') {
    //             $give_bal = $item->party_open_bal;
    //             $pt_bal = $pt_bal - $give_bal;
    //         } elseif ($item->party_open_type === 'get') {
    //             $get_bal = $item->party_open_bal;
    //             $pt_bal = $pt_bal + $get_bal;
    //         }

    //     // \Log::info('Give Balance: ' . $give_bal);
    //     // \Log::info('Get Balance: ' . $get_bal);
    //     // \Log::info('Final Party Balance: ' . $pt_bal);

    //         $item->party_bal = $pt_bal;

    //         return $item;

    //     });

    //     $final =  $data->sum('party_bal');

    //     $total_party = $data->count();

    //       \Log::info('Final All Party Balance: ' . $final);

    //     // dd($data->toArray());

    //     // $party_cash = Party_cash::select('id','type','amount','method','created_at')->get();

    //     // $party_load = Prime_load::where('status', 'active')->where('load_status','inv_completed')->pluck('id');

    //     // $inv_data = E_invoice::select('id','bill_amt')->get();

    //     // $inv_amount = $inv_data->sum('bill_amt');

    //     // $party_sales = Stock_out::where('cat','sales')->sum('bill_amount');

    //     // $in_cash = $party_cash->where('type','pay_in')->sum('amount');
    //     // $out_cash = $party_cash->where('type','pay_out')->sum('amount');

    //     // $party_give_get = Party::where('status','active')->get();

    //     // $give_total = $party_give_get->where('open_type', 'give')->sum('open_bal');
    //     // $get_total  = $party_give_get->where('open_type', 'get')->sum('open_bal');

    //     // $bal =  $in_cash - ($inv_amount + $party_sales +  $out_cash)  + ($get_total - $give_total);

    //     $party_card = [
    //         'balance'=> $final,
    //         'total'=>$total_party
    //     ];
    //     // dd($data);

    //     $party = $data->map(function ($party) {
    //             return [
    //                 'party_id'      => $party->id ?? null,
    //                 'party_en'      => $party->party_en ?? null,
    //                 'party_nick_en' => $party->party_nick_en ?? null,
    //                 'party_location'  => $party->party_location ?? null,
    //                 'phone'         => $party->party_ph_no ?? null,
    //                 'amount'       => $party->party_bal ?? null,
    //                 'fav'          => $party->fav ?? null,
    //             ];
    //         });

    //     return ['party'=>$party,'head_card'=>$party_card];

    // }

    public static function get_all_party()
    {
       
        $data = Party::where('status', 'active')
            ->orderBy('fav', 'DESC')
            ->get()
            ->map(function ($item) {

                $in_cash = Party_cash::where('party_id', $item->id)
                    ->where('type', 'pay_in')
                    ->sum('amount');

                $out_cash = Party_cash::where('party_id', $item->id)
                    ->where('type', 'pay_out')
                    ->sum('amount');

                $load_ids = Prime_load::where('party_id', $item->id)
                    ->where('status', 'active')
                    ->where('load_status', 'inv_completed')
                    ->pluck('id');

                $inv_amount = E_invoice::whereIn('load_id', $load_ids)
                    ->sum('bill_amt');

                $inv_prime = M_invoice::whereIn('load_id', $load_ids)
                    ->select('id', 'charges')
                    ->get();

                //    $inv_amount = 0;

                foreach ($inv_prime as $inv) {
                    $charge_out = $inv->charges ?? [];

                    if (is_array($charge_out)) {
                        $inv_amount += array_sum(
                            array_map('floatval', array_column($charge_out, 'amt'))
                        );
                    }
                }

                $shift_others = Shift::where('cat', 'others')
                    ->where('party_id', $item->id)
                    ->where('status', 'active')
                    ->sum('bill_amount');

                $party_sales = Stock_out::where('cat', 'sales')
                    ->where('farm_id', $item->id) // confirm column
                    ->sum('bill_amount');

                $pt_bal = ($inv_amount + $party_sales + $out_cash + $shift_others) - $in_cash;

                if ($item->party_open_type === 'give') {
                    $pt_bal -= $item->party_open_bal;
                } elseif ($item->party_open_type === 'get') {
                    $pt_bal += $item->party_open_bal;
                }

                $item->party_bal = $pt_bal;

                return $item;
            });

        $final = $data->sum('party_bal');
        $total_party = $data->count();

        $party_card = [
            'balance' => $final,
            'total' => $total_party,
        ];

        $party = $data->map(function ($party) {
            return [
                'party_id' => $party->id,
                'party_en' => $party->party_en,
                'party_nick_en' => $party->party_nick_en,
                'party_location' => $party->party_location,
                'phone' => $party->party_ph_no,
                'amount' => $party->party_bal,
                'fav' => $party->fav,
            ];
        });

        return [
            'party' => $party,
            'head_card' => $party_card,
        ];
    }

     public static function get_all_party_opt(array $data = [])
    {

        $cursor = $data['cursor'] ?? null;
        $tenant_db = $data['tenant_db'] ?? null;
        $keyword = $data['keyword'] ?? null;

        $head_card = null;

        if (!$cursor && !$keyword) {

            $cacheKey = "party_head_card_{$tenant_db}";

            $head_card = Cache::store('redis')->remember($cacheKey, 5, function () {

                $inv_total = E_invoice::sum('bill_amt');

                $mInvoices = M_invoice::select('charges', 'final_loss')->get();

                $charge_total = 0;
                $loss_total = 0;

                foreach ($mInvoices as $inv) {
                    $charges = $inv->charges ?? [];

                    if (is_array($charges)) {
                        $charge_total += array_sum(array_column($charges, 'amt'));
                    }

                    $loss_total += $inv->final_loss['amount'] ?? 0;
                }

                $shift_total = Shift::where('cat', 'others')
                    ->where('status', 'active')
                    ->sum('bill_amount');

                $sales_total = Stock_out::where('cat', 'sales')
                    ->sum('bill_amount');

                $cash = Party_cash::selectRaw('
                    SUM(CASE WHEN type = "pay_in" THEN amount ELSE 0 END) as in_cash,
                    SUM(CASE WHEN type = "pay_out" THEN amount ELSE 0 END) as out_cash
                ')->first();

                $totalBalance = (
                    $inv_total
                    + $charge_total
                    + $sales_total
                    + $shift_total
                    + $cash->out_cash
                ) - $cash->in_cash - $loss_total;

                $give_bal = Party::where('party_open_type', 'give')->sum('party_open_bal');
                $get_bal = Party::where('party_open_type', 'get')->sum('party_open_bal');

                $totalBalance = $totalBalance - $give_bal + $get_bal;

                return [
                    'balance' => $totalBalance,
                    'total' => Party::where('status', 'active')->count(),
                ];
            });
        }

         $query = Party::where('status', 'active');

        // 🔍 Apply search
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('party_en', 'like', "%{$keyword}%");
                // ->orWhere('party_nick_en', 'like', "%{$keyword}%")
                // ->orWhere('party_location', 'like', "%{$keyword}%");
            });
        }

        $parties = $query
            ->orderBy('fav', 'DESC')
            ->orderByDesc('id')
            ->cursorPaginate(10);


        //  \Log::info('Fetching all party details with optimized queries');
            // $parties = Party::where('status', 'active')
            // ->orderBy('fav', 'DESC')
            // ->orderByDesc('id') // ⚠️ REQUIRED for cursor
            // ->cursorPaginate(10);
        // ->get();
        $partyIds = collect($parties->items())->pluck('id');

        $cash = Party_cash::whereIn('party_id', $partyIds)
            ->selectRaw('party_id,
            SUM(CASE WHEN type = "pay_in" THEN amount ELSE 0 END) as in_cash,
            SUM(CASE WHEN type = "pay_out" THEN amount ELSE 0 END) as out_cash
            ')
        ->groupBy('party_id')
        ->get()
        ->keyBy('party_id');


        $loads = Prime_load::whereIn('party_id', $partyIds)->where('status', 'active')
        ->where('load_status', 'inv_completed')
        ->get()
        ->groupBy('party_id');

        $loadIds = $loads->flatten()->pluck('id');

        $eInvoices = E_invoice::selectRaw('load_id, SUM(bill_amt) as total')
        ->whereIn('load_id', $loadIds)
        ->groupBy('load_id')
        ->get()
        ->keyBy('load_id');

        $mInvoices = M_invoice::whereIn('load_id', $loadIds)->select('load_id', 'charges', 'final_loss')
        ->get()
        ->groupBy('load_id');

        $shifts = Shift::selectRaw('party_id, SUM(bill_amount) as total')
            ->whereIn('party_id', $partyIds)
        ->where('cat', 'others')
        ->where('status', 'active')
        ->groupBy('party_id')
        ->get()
        ->keyBy('party_id');

        $sales = Stock_out::selectRaw('farm_id, SUM(bill_amount) as total')
        ->whereIn('farm_id', $partyIds)
        ->where('cat', 'sales')
        ->groupBy('farm_id')
        ->get()
        ->keyBy('farm_id');


        $data = $parties->map(function ($item) use ($cash, $loads, $eInvoices, $mInvoices, $shifts, $sales) {

                    $in_cash = $cash[$item->id]->in_cash ?? 0;
                    $out_cash = $cash[$item->id]->out_cash ?? 0;

                    $inv_amount = 0;
                    $loss_amount = 0;

                    $partyLoads = $loads[$item->id] ?? collect();

                    foreach ($partyLoads as $load) {

                        $inv_amount += $eInvoices[$load->id]->total ?? 0;

                        $mInvs = $mInvoices[$load->id] ?? [];

                        foreach ($mInvs as $inv) {
                            $charges = $inv->charges ?? [];
                            $loss_charges = $inv->final_loss ?? [];

                            if (is_array($charges)) {
                                $inv_amount += array_sum(
                                    array_map('floatval', array_column($charges, 'amt'))
                                );
                            }

                             // 🔻 SUBTRACT LOSS (correct way)
                            $inv_amount -= (float) ($loss_charges['amount'] ?? 0);

                            // \Log::info("load_id: {$load->id}, bill_amt: {$eInvoices[$load->id]->total}, charges: " . json_encode($charges) . ", loss: " . json_encode($loss_charges));
                        }
                    }

                    $shift_others = $shifts[$item->id]->total ?? 0;
                    $party_sales = $sales[$item->id]->total ?? 0;

                    $pt_bal = ($inv_amount + $party_sales + $out_cash + $shift_others) - $in_cash;

                    if ($item->party_open_type === 'give') {
                        $pt_bal -= $item->party_open_bal;
                    } elseif ($item->party_open_type === 'get') {
                        $pt_bal += $item->party_open_bal;
                    }

                    $item->party_bal = $pt_bal;

                    return $item;
                });

            
        $nextCursor = $parties->nextCursor()?->encode();
        $prevCursor = $parties->previousCursor()?->encode();

        $party = $data->map(function ($party) {
                return [
                    'party_id'      => $party->id ?? null,
                    'party_en'      => $party->party_en ?? null,
                    'party_nick_en' => $party->party_nick_en ?? null,
                    'party_location'  => $party->party_location ?? null,
                    'phone'         => $party->party_ph_no ?? null,
                    'amount'       => $party->party_bal ?? null,
                    'fav'          => $party->fav ?? null,
                ];
            });

        // $final = $party->sum('amount');

        // $allParties = Party::where('status', 'active')->get();

        // $total_party = $allParties->count();        

        // $party_card = [
        //     'balance' => $final,
        //     'total' => $total_party,
        // ];

        return ['party'=>$party,'head_card'=>$head_card, 'next_url' => $nextCursor, 'prev_url' => $prevCursor,];
 
    }

    // function to get party profile details

    // public static function party_profile(array $data)
    // {

    //     $party_id = $data['party_id'];

    //     // \Log::info('Party ID: ' . $party_id);

    //     $data = Party::select('id as party_id', 'party_en', 'party_nick_en', 'party_location', 'party_ph_no', 'party_wp_no', 'fav', 'party_open_type', 'party_open_bal', 'created_at')
    //         ->where('id', $party_id)
    //         ->first();

    //     $give_bal = 0;
    //     $get_bal = 0;

    //     $party_cash = Party_cash::where('party_id', $party_id)->select('id', 'type', 'amount', 'method', 'created_at')->get()->map(function ($item) {
    //         $item->source = 'cash';

    //         if ($item->method != 'Cash' && $item->method != 'upi' && $item->method != 'cash') {
    //             $item->method_details = Bank::where('id', $item->method)->select('b_name', 'acc_no')->first();
    //         }

    //         return $item;
    //     });

    //     $party_load = Prime_load::where('party_id', $party_id)->where('status', 'active')->where('load_status', 'inv_completed')->pluck('id');

    //     //  \Log::info('Party ID: ' . $party_load);

    //     // $inv_data = E_invoice::whereIn('load_id', $party_load)->select('id','bill_amt')->get();

    //     $inv_data = E_invoice::whereIn('load_id', $party_load)
    //         ->groupBy('load_id')
    //         ->selectRaw("
    //                 load_id,
    //                 SUM(bill_amt) as total_amt,
    //                 DATE_FORMAT(MAX(created_at), '%d-%m-%Y %H:%i:%s') as  invoice_date
    //             ")
    //         ->get()->map(function ($item) {
    //             $item->source = 'invoice';
    //             $item->created_at = $item->invoice_date;
    //             $item->load_name = Prime_load::where('id', $item->load_id)->value('load_seq');
    //             $item->inv_no = M_invoice::where('load_id', $item->load_id)->value('inv_no');

    //             $prime_inv = M_invoice::where('load_id', $item->load_id)
    //                 ->select('id', 'charges', 'final_loss')
    //                 ->first();

    //             $charge_out = $prime_inv->charges ?? [];
    //             $loss = $prime_inv->final_loss ?? null; // your loss json
    //             // \Log::info("loss for Load ID {$prime_inv->id}: ", (array) $loss);

    //             //   \Log::info('Charge Out for Load ID '.$prime_inv->id.': ', $charge_out);
    //             //   \Log::info('total_amt'.$item->total_amt);

    //             $item->total_amt += array_sum(array_column($charge_out, 'amt'));

    //             // Deduct loss
    //             if (! empty($loss)) {

    //                 $item->total_amt -= ($loss['amount'] ?? 0);
    //             }

    //             // $item->total_amt += array_sum($charge_out);

    //             // $arry_sum = array_sum($charge_out);

    //             // \Log::info('total_amt'.$item->total_amt);
    //             // \Log::info('total_amt'.$arry_sum);

    //             // $item->total_amt = $item->total_amt + ($charge_out['total_charge'] ?? 0);
    //             return $item;
    //         });

    //     $inv_amount = $inv_data->sum('total_amt');

    //     $party_others = Shift::with(['product_data:id,name_en'])->where('cat', 'others')->where('party_id', $party_id)->where('status', 'active')->get()->map(function ($item) {
    //         $item->source = 'others';

    //         return $item;
    //     });

    //     $party_other_amount = $party_others->sum('bill_amount');

    //     $party_stock = Stock_out::with(['product:id,name_en'])->where('cat', 'sales')->where('farm_id', $party_id)->select('id', 'total_piece', 'bill_amount', 'created_at', 'product_id', 'inv_no')->get()->map(function ($item) {
    //         $item->source = 'sales';

    //         return $item;
    //     });

    //     $party_sales = $party_stock->sum('bill_amount');

    //     $party_open_balance = collect([
    //         (object) [
    //             'id' => null,
    //             'open_type' => $data->party_open_type,
    //             'amount' => $data->party_open_bal,
    //             'method' => null,
    //             'status' => null,
    //             'c_by' => null,
    //             'date' => date('d-m-Y H:i:s', strtotime($data->created_at)),
    //             'created_at' => $data->created_at,
    //             'table' => 'opening_balance',
    //         ],
    //     ]);

    //     $party_trans = $party_cash->concat($inv_data)->concat($party_others)->concat($party_stock)->concat($party_open_balance)
    //         ->map(function ($item) {

    //             if ($item->created_at instanceof Carbon) {
    //                 $item->sort_ts = $item->created_at->timestamp;
    //             } else {
    //                 // handle both formats
    //                 if (preg_match('/^\d{2}-\d{2}-\d{4}/', $item->created_at)) {
    //                     $dt = Carbon::createFromFormat('d-m-Y H:i:s', $item->created_at);
    //                 } else {
    //                     $dt = Carbon::parse($item->created_at);
    //                 }

    //                 $item->created_at = $dt;          // keep Carbon
    //                 $item->sort_ts = $dt->timestamp; // PURE numeric
    //             }

    //             return $item;

    //         })
    //         ->sortByDesc('sort_ts')->values();

    //     $in_cash = $party_cash->where('type', 'pay_in')->sum('amount');
    //     $out_cash = $party_cash->where('type', 'pay_out')->sum('amount');

    //     $bal = ($inv_amount + $party_sales + $party_other_amount + $out_cash) - $in_cash;

    //     // \Log::info('inv amount: ' . $inv_amount);
    //     // \Log::info('party sales: ' . $party_sales);
    //     // \Log::info('in cash: ' . $in_cash);
    //     // \Log::info('out cash: ' . $out_cash);
    //     // \Log::info('balance: ' . $bal);

    //     if ($data->party_open_type === 'give') {
    //         $give_bal = $data->party_open_bal;
    //         $bal = $bal - $give_bal;
    //     } elseif ($data->party_open_type === 'get') {
    //         $get_bal = $data->party_open_bal;
    //         $bal = $bal + $get_bal;
    //     }

    //     // $data->amount = 0;
    //     $data->balance = $bal;

    //     return ['data' => $data, 'party_cash' => $party_trans];
    // }

 

    public static function party_profile(array $data)
    {
        $party_id = $data['party_id'];

        $cursor = $data['cursor'] ?? null;
        // ✅ Party info
        $party = Party::select(
            'id as party_id',
            'party_en',
            'party_nick_en',
            'party_location',
            'party_ph_no',
            'party_wp_no',
            'fav',
            'party_open_type',
            'party_open_bal',
            'created_at'
        )->findOrFail($party_id);

        // if(!$cursor){
        //    $opening = Party::where('id', $party_id)
        //         ->select([
        //             DB::raw("'opening_balance' as source"),
        //             DB::raw('NULL as id'),
        //             DB::raw('NULL as load_id'),
        //             DB::raw('party_open_bal as amount'),
        //             DB::raw('NULL as bill_amount'),
        //             'created_at',
        //             DB::raw('NULL as method'),
        //             DB::raw('NULL as type')
        //         ]);
        // }



        // ✅ CASH
        $cash = Party_cash::where('party_id', $party_id)
            ->select([
                DB::raw("'cash' as source"),
                'id',
                DB::raw('NULL as load_id'),
                'amount',
                DB::raw('NULL as bill_amount'),
                'created_at',
                'method',
                'type',
            ]);

        // ✅ INVOICE
        $invoice = E_invoice::whereIn('load_id', function ($q) use ($party_id) {
                $q->select('id')
                ->from('m_load')
                ->where('party_id', $party_id)
                ->where('status', 'active')
                ->where('load_status', 'inv_completed');
            })
            ->select([
                DB::raw("'invoice' as source"),
                DB::raw('NULL as id'),
                'load_id',
                DB::raw('SUM(bill_amt) as amount'),
                DB::raw('NULL as bill_amount'),
                DB::raw('MAX(created_at) as created_at'),
                DB::raw('NULL as method'),
                DB::raw('NULL as type'),

            ])
            ->groupBy('load_id');

        // ✅ OTHERS
        $others = Shift::where('party_id', $party_id)
            ->where('cat', 'others')
            ->where('status', 'active')
            ->select([
                DB::raw("'others' as source"),
                'id',
                DB::raw('NULL as load_id'),
                DB::raw('NULL as amount'),
                'bill_amount',
                'created_at',
                DB::raw('NULL as method'),
                 DB::raw('NULL as type'),
            ]);

        // ✅ SALES
        $sales = Stock_out::where('farm_id', $party_id)
            ->where('cat', 'sales')
            ->select([
                DB::raw("'sales' as source"),
                'id',
                DB::raw('NULL as load_id'),
                DB::raw('NULL as amount'),
                'bill_amount',
                'created_at',
                DB::raw('NULL as method'),
                 DB::raw('NULL as type'),
            ]);

        // ✅ UNION ALL
        $unionQuery = $cash
            ->unionAll($invoice)
            ->unionAll($others)
            ->unionAll($sales);

        // if (!$cursor) {
        //     $unionQuery = $unionQuery->unionAll($opening);
        // }

        // ✅ FINAL QUERY (REAL CURSOR PAGINATION)
        $transactions = DB::query()
            ->fromSub($unionQuery, 'transactions')
            ->orderByDesc('created_at')
            ->cursorPaginate(10);

        $isLastPage = is_null($transactions->nextCursor());

        $items = collect($transactions->items());

        $loadIds = $items
        ->where('source', 'invoice')
        ->pluck('load_id')
        ->filter()
        ->unique()
        ->values();

        $load_data = M_invoice::whereIn('id', $loadIds)->select('id','final_loss','charges')->get()->keyBy('id');

            $items = $items->map(function ($row) use ($load_data) {

                $invoiceData = $load_data->groupBy('id');

                    if ($row->source === 'invoice' && isset($invoiceData[$row->load_id])) {

                        $rows = $invoiceData[$row->load_id];

                        $total_loss = 0;
                        $total_driver_adv = 0;

                        foreach ($rows as $inv) {

                            // 🔻 LOSS
                           $total_loss += (float) ($inv->final_loss['amount'] ?? 0);

                            // 🔺 CHARGES
                            $charges = ($inv->charges) ?? [];

                            if (is_array($charges)) {
                                $total_driver_adv  += array_sum(
                                    array_map('floatval', array_column($charges, 'amt'))
                                );
                            }


                            // $total_driver_adv += collect($charges)
                            //     ->filter(fn($c) => strtolower($c['charge_name'] ?? '') === 'driver advance')
                            //     ->sum(fn($c) => (float) ($c['amt'] ?? 0));
                        }

                        // 👉 Adjust
                        $row->amount = ($row->amount - $total_loss) + $total_driver_adv;
                    }

                    return $row;
                });

                // \Log::info('Transactions fetched: ' . count($items) . ', Is Last Page: ' . ($isLastPage ? 'Yes' : 'No'));

                    if ($isLastPage) {
                        // \Log::info('Adding opening balance for party ID: ' . $party_id);
                        $items->push([
                            'source' => 'opening_balance',
                            'id' => null,
                            'load_id' => null,
                            'amount' => $party->party_open_bal,
                            'bill_amount' => null,
                            'created_at' => $party->created_at,
                            'method' => null,
                            'type' => $party->party_open_type,
                        ]);

                        // \Log::info("item".json_encode($items,JSON_PRETTY_PRINT));
                    }

                // ✅ BALANCE (same as before)
                $in_cash = Party_cash::where('party_id', $party_id)->where('type', 'pay_in')->sum('amount');
                $out_cash = Party_cash::where('party_id', $party_id)->where('type', 'pay_out')->sum('amount');

                    // $inv_amount = E_invoice::whereIn('load_id', function ($q) use ($party_id) {
                    //                     $q->select('id')->from('m_load')->where('party_id', $party_id);
                    //                 })->sum('bill_amt');

                            $collection = E_invoice::with([
                                            'load_data:id,party_id',
                                            'invoice:id,final_loss,charges'
                                        ])
                                        ->whereHas('load_data', function ($q) use ($party_id) {
                                            $q->where('party_id', $party_id);
                                        })
                                        ->get();

                            $total_bill = $collection->sum('bill_amt');

                        // \Log::info("collect".json_encode($collection,JSON_PRETTY_PRINT));

                        $total_adjustment = $collection
                            ->unique('inv_id') // 🔥 FIXED (not load_id)
                            ->sum(function ($item) {

                                    $loss_charge = $item->invoice->final_loss['amount'] ?? 0;

                                    // $drice_adv_loading = $item->invoice->charges ?? [];

                                    $charges = $item->invoice->charges ?? [];

                                    $charges_total  = 0;
                                    if (is_array($charges)) {
                                        $charges_total  += array_sum(
                                            array_map('floatval', array_column($charges, 'amt'))
                                        );
                                    }

                                    // 👉 return NUMBER (important)
                                    return $charges_total - $loss_charge;
                                    
                            });

                            $final_amount = ($total_bill + $total_adjustment);

                        // \Log::info('Total Invoice Amount: ' . $total_bill);
                        // \Log::info('Total Adjustment Amount: ' . $total_adjustment);
                        // \Log::info('Final Invoice Amount after Adjustment: ' . $final_amount);

                $sales_amount = Stock_out::where('farm_id', $party_id)->sum('bill_amount');
                $other_amount = Shift::where('party_id', $party_id)->sum('bill_amount');

                $bal = ($final_amount + $sales_amount + $other_amount + $out_cash) - $in_cash;

             
                if ($party->party_open_type === 'give') {
                    $bal -= $party->party_open_bal;
                } elseif ($party->party_open_type === 'get') {
                    $bal += $party->party_open_bal;
                }

                // \Log::info('Final Amount: ' . $final_amount);
                // \Log::info('Sales Amount: ' . $sales_amount);
                // \Log::info('Other Amount: ' . $other_amount);   
                // \Log::info('In Cash: ' . $in_cash);
                // \Log::info('Out Cash: ' . $out_cash);
                // \Log::info('Balance: ' . $bal);


                $party->balance = $bal;

                return [
                    'data' => $party,
                    'transactions' =>  $items->values(),
                    'next_cursor' => optional($transactions->nextCursor())->encode()
                ];
            }


    // function to party pay In

    public static function party_pay_in(array $data)
    {

        $party = Party::findOrFail($data['party_id']);

        if (! $party) {
            throw new \Exception('Party not found');
        }

        $party_cash = Party_cash::create([
            'party_id' => $data['party_id'],
            'type' => 'pay_in',
            'amount' => $data['amount'],
            'method' => $data['method'],
            'c_by' => Auth::guard('tenant')->user()->id ?? null,
        ]);

        return $party_cash;

    }

    // funtion to party pay Out

    public static function party_pay_out(array $data)
    {

        $party = Party::findOrFail($data['party_id']);

        if (! $party) {
            throw new \Exception('Party not found');
        }

        $party_cash = Party_cash::create([
            'party_id' => $data['party_id'],
            'type' => 'pay_out',
            'amount' => $data['amount'],
            'method' => $data['method'],
            'c_by' => Auth::guard('tenant')->user()->id ?? null,
        ]);

        return $party_cash;

    }

    // function to edit party pay In/Out

    public static function party_pay_edit(array $data)
    {

        $party_cash = Party_cash::findOrFail($data['payment_id']);

        if (! $party_cash) {
            throw new \Exception('Party cash record not found');
        }

        // Fill the model with new data
        $party_cash->fill([
            'amount' => $data['amount'],
            'method' => $data['pay_method'],
        ]);

        // Save only if there are changes
        if ($party_cash->isDirty()) {
            $party_cash->save();
        }

        return $party_cash;

    }

    // function to inactivate party

    public static function party_inactive(array $data)
    {

        $query = Party::where('status', 'inactive');

        // 🔍 Apply search
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('party_en', 'like', "%{$keyword}%");
                // ->orWhere('party_nick_en', 'like', "%{$keyword}%")
                // ->orWhere('party_location', 'like', "%{$keyword}%");
            });
        }

        $parties = $query
            // ->orderBy('fav', 'DESC')
            ->orderByDesc('id')
            ->cursorPaginate(10);

        return [
            'parties' => $parties->items(),
            'next_url' => optional($parties->nextCursor())->encode(),
            'prev_url' => optional($parties->previousCursor())->encode(),
        ];

    }

    // function to party invoice report

    public static function party_invoice_report(array $data)
    {
        // code here
        $party_id = $data['party_id'];
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        $party_load = Prime_load::where('party_id', $party_id)->where('status', 'active')->where('load_status', 'inv_completed')->pluck('id');
        $inv_data = M_invoice::with(['invoice_items:id,inv_id,product,bill_amt'])->whereIn('load_id', $party_load)
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->select('id', 'inv_no', 'load_id', 'created_at', 'charges', 'final_loss')
            // ->select('id', 'load_id', 'bill_amt', 'created_at')
            ->get()->map(function ($item) {

                $item->source = 'invoice';
                $item->inv_no = $item->inv_no ?? null;
                // sum of pieces from product column
                $item->total_piece = $item->invoice_items->sum('product');
                $base_amount = $item->invoice_items->sum('bill_amt');

                $inv_prime = $item->charges ?? [];
                $inv_amount = 0;

                if (is_array($inv_prime)) {
                    $inv_amount = array_sum(
                        array_map('floatval', array_column($inv_prime, 'amt'))
                    );
                }

                $loss = $item->final_loss ?? null; // your loss json

                \Log::info("loss for Invoice ID {$item->id}: ", (array) $loss);

                if (! empty($loss)) {
                    $inv_amount -= ($loss['amount'] ?? 0);
                }

                // \Log::info('Charge Out for Invoice ID '.$item->id.': ', $inv_prime);
                // \Log::info('Total Amount before Charge for Invoice ID '.$item->id.': '.$base_amount);
                // \Log::info('Total Amount after Charge for Invoice ID '.$item->id.': '.($base_amount + $inv_amount));

                // sum bill amount
                $item->bill_amount_mapped = $base_amount + $inv_amount;

                return $item;
            });

        // Log::info('Invoice Data: ', $inv_data->toArray());

        $sal_data = Stock_out::with(['product:id,name_en'])->where('cat', 'sales')->where('farm_id', $party_id)
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->select('id', 'total_piece', 'bill_amount', 'created_at', 'product_id', 'inv_no')
            ->get()->map(function ($item) {
                $item->source = 'sales';

                $item->bill_amount_mapped = $item->bill_amount; // Use the sum from invoice_items

                return $item;
            });

        $shift_others = Shift::with(['product_data:id,name_en'])->where('cat', 'others')->where('party_id', $party_id)
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->get()->map(function ($item) {
                $item->source = 'others';
                $item->bill_amount_mapped = $item->bill_amount ?? null;

                return $item;
            });

        \Log::info('Shift Others Data: ', $shift_others->toArray());

        $inv_list = $inv_data
            ->concat($sal_data)
            ->concat($shift_others)
            ->sortByDesc('created_at')
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    // 'load_id' => $item->load_id ?? null,
                    'source' => $item->source,
                    'inv_no' => $item->inv_no ?? null,
                    'total_piece' => $item->total_piece ?? null,
                    'bill_amount' => $item->bill_amount_mapped ?? 0,
                    'created_at' => $item->created_at,
                ];
            });

        return $inv_list;

    }

    // function to party payment out report

    public static function party_payment_out_report(array $data)
    {
        $party_id = $data['party_id'];
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;

        $payment_out = Party_cash::with(['party_bank_detail'])->where('party_id', $party_id)->where('type', 'pay_out')
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->select('id', 'amount', 'method', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()->map(function ($item) {
                
                $item->bank = $item->party_bank_detail ?? null;

                return $item;
            });

        return $payment_out;
    }

    // function to party payment pending in report

    public static function party_payment_pending_report(array $data)
    {
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;

        $parties = Party::select('id', 'party_en','party_open_type','party_open_bal')->get();

        $report = $parties->map(function ($party) use ($start_date, $end_date) {

            $party_id = $party->id;

            /* -----------------------------
            PAYMENT OUT
            ------------------------------*/

            $payment_out = Party_cash::where('party_id', $party_id)
                ->where('type', 'pay_out')
                ->when($start_date, function ($q) use ($start_date) {
                    $q->whereDate('created_at', '>=', $start_date);
                })
                ->when($end_date, function ($q) use ($end_date) {
                    $q->whereDate('created_at', '<=', $end_date);
                })
                ->sum('amount');

            $payment_in = Party_cash::where('party_id', $party_id)
                ->where('type', 'pay_in')
                ->when($start_date, function ($q) use ($start_date) {
                    $q->whereDate('created_at', '>=', $start_date);
                })
                ->when($end_date, function ($q) use ($end_date) {
                    $q->whereDate('created_at', '<=', $end_date);
                })
                ->sum('amount');

            /* -----------------------------
            INVOICE TOTAL
            ------------------------------*/

            $party_load = Prime_load::where('party_id', $party_id)
                ->where('status', 'active')
                ->where('load_status', 'inv_completed')
                ->pluck('id');

            $invoice_total = M_invoice::with('invoice_items')
                ->whereIn('load_id', $party_load)
                ->when($start_date, function ($q) use ($start_date) {
                    $q->whereDate('created_at', '>=', $start_date);
                })
                ->when($end_date, function ($q) use ($end_date) {
                    $q->whereDate('created_at', '<=', $end_date);
                })
                ->get()
                ->sum(function ($inv) {

                    $base_amount = $inv->invoice_items->sum('bill_amt');

                    $charges = $inv->charges ?? [];
                    $charge_amt = 0;

                    if (is_array($charges)) {
                        $charge_amt = array_sum(
                            array_map('floatval', array_column($charges, 'amt'))
                        );
                    }

                    $loss = $inv->final_loss ?? null;

                    if (! empty($loss)) {
                        $charge_amt -= ($loss['amount'] ?? 0);
                    }

                    return $base_amount + $charge_amt;
                });

            /* -----------------------------
            SALES
            ------------------------------*/

            $sales_total = Stock_out::where('cat', 'sales')
                ->where('farm_id', $party_id)
                ->when($start_date, function ($q) use ($start_date) {
                    $q->whereDate('created_at', '>=', $start_date);
                })
                ->when($end_date, function ($q) use ($end_date) {
                    $q->whereDate('created_at', '<=', $end_date);
                })
                ->sum('bill_amount');

            /* -----------------------------
            SHIFT OTHERS
            ------------------------------*/

            $others_total = Shift::where('cat', 'others')
                ->where('party_id', $party_id)
                ->when($start_date, function ($q) use ($start_date) {
                    $q->whereDate('created_at', '>=', $start_date);
                })
                ->when($end_date, function ($q) use ($end_date) {
                    $q->whereDate('created_at', '<=', $end_date);
                })
                ->sum('bill_amount');

            $total_invoice = ($invoice_total + $sales_total + $others_total);

            $pending = $total_invoice +$payment_out - $payment_in;

            if ($party->party_open_type === 'give') {
                $give_bal = $party->party_open_bal;
                $pending = $pending - $give_bal;
                // Log::info('Party ID: ' . $party->id . ' - Give Balance: ' . $give_bal);
            } elseif ($party->party_open_type === 'get') {
                $get_bal = $party->party_open_bal;
                $pending = $pending + $get_bal;
                // Log::info('Party ID: ' . $party->id . ' - Get Balance: ' . $get_bal);
            }

            // Log::info('Party ID: ' . $party->id . ' - Total Invoice: ' . $total_invoice);
            // Log::info('Party ID: ' . $party->id . ' - Payment Out: ' . $payment_out);
            // Log::info('Party ID: ' . $party->id . ' - Pending Amount: ' . $pending);
              

            return [
                    'party_id' => $party->id,
                    'party_name' => $party->party_en,
                    'invoice_total' => $total_invoice,
                    'payment_out' => (int) $payment_out,
                    'pending_amount' => $pending,
                ];
        });

        return $report->values();
    }
}
