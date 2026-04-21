<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Farmer;
use App\Models\Farmer_cash;
use App\Models\Load;
use App\Models\Stock_in;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Farmer_ser
{
    public static function create_farm(array $data)
    {

        $farmer = Farmer::find($data['farm_id'] ?? 0);
        if ($farmer) {
            // Fill the model with new data
            $farmer->fill([
                'farm_en' => $data['farm_en'],
                'farm_nick_en' => $data['farm_nick_en'],
                'location' => $data['location'],
                'ph_no' => $data['ph_no'],
                'wp_no' => $data['wp_no'],
                'open_type' => $data['open_type'],
                'open_bal' => $data['open_bal'],
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);

            // Save only if there are changes
            if ($farmer->isDirty()) {
                $farmer->save();
            }

        } else {
            // Create new record
            $farmer = Farmer::create([
                'farm_en' => $data['farm_en'],
                'farm_kn' => $data['farm_kn'] ?? null,
                'farm_nick_en' => $data['farm_nick_en'],
                'farm_nick_kn' => $data['farm_nick_kn'] ?? null,
                'location' => $data['location'],
                'ph_no' => $data['ph_no'],
                'wp_no' => $data['wp_no'],
                'open_type' => $data['open_type'],
                'open_bal' => $data['open_bal'],
                'adv_prime' => $data['adv'] ?? 0,
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);

            if (! empty($data['b_name'])) {
                $farmer_bank = Bank::create([
                    'type' => 'farmer',
                    'f_id' => $farmer->id,
                    'acc_type' => $data['acc_type'],
                    'b_name' => $data['b_name'],
                    'acc_name' => $data['acc_name'],
                    'acc_no' => $data['acc_no'],
                    'ifsc' => $data['ifsc'],
                    'upi' => $data['upi'],
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]);
            }

            if ($data['adv'] > 0) {
                // Create initial Farmer_cash record for opening balance
                $adv_prime = Farmer_cash::create([
                    'farm_id' => $farmer->id,
                    'type' => 'advance',
                    'amount' => $data['adv'],
                    'method' => 'Cash',
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]);

                Farmer::where('id', $farmer->id)->update(['adv_prime' => $adv_prime->id]);
            }
        }

        return $farmer;

    }

    public static function get_farmer_details($farm_id)
    {
        $farm = Farmer::findOrFail($farm_id);

        $farm->advance = Farmer_cash::where('id', $farm->adv_prime)->value('amount');

        $farm->transaction_count = Farmer_cash::where('farm_id', $farm_id)->where('id','!=', $farm->adv_prime)->count();

        return $farm;
    }

//    public static function get_all_farmers()
//     {
//         $data = Farmer::where('status', 'active')->orderBy('fav', 'DESC')->get()->map(function ($farmer) {

//             $loadPending = Load::where('farmer_id', $farmer->id)
//                 ->sum(DB::raw('total_amt - IFNULL(adv,0)'));

//             $stockPending = Stock_in::where('cat', 'purchase')
//                 ->where('farm_id', $farmer->id)
//                 ->sum(DB::raw('total_amt - IFNULL(adv,0)'));

//             $paidAmount = Farmer_cash::where('farm_id', $farmer->id)
//                 ->where('type', 'purchase')
//                 ->sum('amount');

//             $purchase_pending = ($loadPending + $stockPending) - $paidAmount;

//             $farmer->farmer_pend = $farmer->open_type === 'give'
//                 ? $purchase_pending + $farmer->open_bal
//                 : $purchase_pending - $farmer->open_bal;

//             return $farmer;
//         });

//         $give_bal = 0;
//         $get_bal = 0;

//         $give_bal = $data->where('open_type', 'give')->sum('open_bal');
//         $get_bal = $data->where('open_type', 'get')->sum('open_bal');

//         $load = Load::whereNotNull('farmer_id')->get();

//         $stock_in = Stock_in::where('cat', 'purchase')->whereNotNull('farm_id')->get();

//         $merge = $load->concat($stock_in)->sortByDesc('created_at')->values();

//         $transactions = Farmer_cash::all();

//         $purchase_pending = ($merge->sum('total_amt') - $merge->sum('adv')) - ($transactions->where('type', 'purchase')->sum('amount'));

//         $adv_pending = ($transactions->where('type', 'advance')->sum('amount')) - ($transactions->where('type', 'advance_deduct')->sum('amount'));

//         //  if ($data->open_type === 'give') {
//         //     $final_bal = $purchase_pending + $give_bal;
//         // } elseif ($data->open_type === 'get') {
//         //     $final_bal = $purchase_pending - $get_bal;
//         // }

//         $final_bal = $purchase_pending + $give_bal - $get_bal;

//         $head_card = [
//             'adv_card' => $adv_pending,
//             'balance_card' => $final_bal,
//         ];

//         // 2. Use map to iterate over each farmer and format the array
//         $farmers = $data->map(function ($farmer) use ($adv_pending, $purchase_pending) {
//             return [
//                 'farm_id' => $farmer->id,
//                 'farm_en' => $farmer->farm_en,
//                 'farm_nick_en' => $farmer->farm_nick_en,
//                 'location' => $farmer->location,
//                 'amount' => 0,
//                 'fav' => $farmer->fav,
//                 'adv_card' => $adv_pending,
//                 'balance_card' => $purchase_pending,
//                 'farmer_pend' => $farmer->farmer_pend,
//             ];
//         });

//         return $value = [
//             'head_card' => $head_card,
//             'farmers' => $farmers,
//         ];
//     }

     public static function get_all_farmers_opt(array $data)
    {
         $cursor = $data['cursor'] ?? null;
        $tenant_db = $data['tenant_db'] ?? 'default';
        $keyword = $data['keyword'] ?? null;

        $head_card = null;

        if (!$cursor && !$keyword) {
            // \Log::info('Calculating head card values for tenant: ' . $tenant_db);

                $cacheKey = "farmer_head_card_{$tenant_db}";

                $head_card = Cache::store('redis')->remember($cacheKey, 5, function () {

                try{

                    $totalLoad = Load::selectRaw('SUM(total_amt) as total')->where('cat', 'add')->whereNotNull('farmer_id')->first();

                    $totalStock = Stock_in::selectRaw('SUM(total_amt) as total')
                   
                                ->where('cat', 'purchase')
                                ->whereNotNull('farm_id')

                                ->first();

                    //  \Log::info('Total Load', ['total_load_adv' => $totalLoad->adv, 'total_stock_adv' => $totalStock->adv]);

                    $totalPurchase = ($totalLoad->total + $totalStock->total);
                                    //  - ($totalLoad->adv + $totalStock->adv);

                    //  \Log::info('Total Purchase', ['total' => $totalPurchase]);

                    $transactions = Farmer_cash::selectRaw('
                        SUM(CASE WHEN type = "purchase" THEN amount ELSE 0 END) as purchase,
                        SUM(CASE WHEN type = "advance" THEN amount ELSE 0 END) as advance,
                        SUM(CASE WHEN type = "advance_deduct" THEN amount ELSE 0 END) as deduct
                    ')->first();

                    //  \Log::info('Transactions', ['purchase' => $transactions->purchase, 'advance' => $transactions->advance, 'deduct' => $transactions->deduct]);

                    $purchase_pending = $totalPurchase - $transactions->purchase - $transactions->deduct;
                    $adv_pending = $transactions->advance - $transactions->deduct;

                            // ⚡ IMPORTANT → no get(), use direct sum
                    $give_bal = Farmer::where('open_type', 'give')->sum('open_bal');
                    $get_bal = Farmer::where('open_type', 'get')->sum('open_bal');

                    // \Log::info('Open Balances', ['give_bal' => $give_bal, 'get_bal' => $get_bal]);

                    $final_bal = $purchase_pending + $give_bal - $get_bal;

                    //  \Log::info('Final Balance', ['final_bal' => $final_bal]);

                    return [
                        'adv_card' => $adv_pending,
                        'balance_card' => $final_bal,
                    ];

                } catch (\Exception $e) {
                    \Log::error('Error calculating head card values: ' . $e->getMessage());
                    return [
                        'adv_card' => 0,
                        'balance_card' => 0,
                    ];
                }
            });
        }

        $query = Farmer::where('status', 'active');

        // 🔍 Apply search
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('farm_en', 'like', "%{$keyword}%");
                // ->orWhere('farm_nick_en', 'like', "%{$keyword}%")
                // ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        $farmers = $query
            ->orderBy('fav', 'DESC')
            ->orderByDesc('id')
            ->cursorPaginate(10);

    //    $farmers = Farmer::where('status', 'active')
    //             ->orderBy('fav', 'DESC')
    //             ->orderByDesc('id') // ⚠️ REQUIRED for cursor
    //             ->cursorPaginate(10);
        // ->get();

        $farmerIds = collect($farmers->items())->pluck('id');
    
        $loadPending = Load::selectRaw('farmer_id, SUM(total_amt) as total')
        ->whereIn('farmer_id', $farmerIds)
        ->whereNotNull('farmer_id')
        ->groupBy('farmer_id')
        ->get()
        ->keyBy('farmer_id');

        $stockPending = Stock_in::selectRaw('farm_id, SUM(total_amt) as total')
        ->whereIn('farm_id', $farmerIds)
        ->where('cat', 'purchase')
        ->whereNotNull('farm_id')
        ->groupBy('farm_id')
        ->get()
        ->keyBy('farm_id');

        $paid = Farmer_cash::selectRaw('farm_id, SUM(amount) as total')
        ->whereIn('farm_id', $farmerIds)
        ->where('type', 'purchase')
        ->groupBy('farm_id')
        ->get()
        ->keyBy('farm_id');

         $adv_deduct = Farmer_cash::selectRaw('farm_id, SUM(amount) as total')
        ->whereIn('farm_id', $farmerIds)
        ->where('type', 'advance_deduct')
        ->groupBy('farm_id')
        ->get()
        ->keyBy('farm_id');

        $data = $farmers->map(function ($farmer) use ($loadPending, $stockPending, $paid, $adv_deduct) {

            $load = $loadPending[$farmer->id]->total ?? 0;
            $stock = $stockPending[$farmer->id]->total ?? 0;
            $paidAmt = $paid[$farmer->id]->total ?? 0;
            $advDeduct = $adv_deduct[$farmer->id]->total ?? 0;

            $purchase_pending = ($load + $stock) - ($paidAmt + $advDeduct);

            $farmer->farmer_pend = $farmer->open_type === 'give'
                ? $purchase_pending + $farmer->open_bal
                : $purchase_pending - $farmer->open_bal;

            return $farmer;
        });

        // $totalLoad = Load::selectRaw('SUM(total_amt) as total, SUM(adv) as adv')->first();
        // $totalStock = Stock_in::selectRaw('SUM(total_amt) as total, SUM(adv) as adv')
        //     ->where('cat', 'purchase')
        //     ->first();

        // $totalPurchase = ($totalLoad->total + $totalStock->total)
        //     - ($totalLoad->adv + $totalStock->adv);


        // $transactions = Farmer_cash::selectRaw('
        //     SUM(CASE WHEN type = "purchase" THEN amount ELSE 0 END) as purchase,
        //     SUM(CASE WHEN type = "advance" THEN amount ELSE 0 END) as advance,
        //     SUM(CASE WHEN type = "advance_deduct" THEN amount ELSE 0 END) as deduct
        // ')->first();

        // $purchase_pending = $totalPurchase - $transactions->purchase;
        // $adv_pending = $transactions->advance - $transactions->deduct;

        // $allFarmers = Farmer::where('status', 'active')->get();

        // $give_bal = $allFarmers->where('open_type', 'give')->sum('open_bal');
        // $get_bal = $allFarmers->where('open_type', 'get')->sum('open_bal');

        // // $give_bal = $farmers->where('open_type', 'give')->sum('open_bal');
        // // $get_bal = $farmers->where('open_type', 'get')->sum('open_bal');

        // $final_bal = $purchase_pending + $give_bal - $get_bal;

        // $head_card = [
        //     'adv_card' => $adv_pending,
        //     'balance_card' => $final_bal,
        // ];

        $nextCursor = $farmers->nextCursor()?->encode();
        $prevCursor = $farmers->previousCursor()?->encode();

        $farmersList = $data->map(fn($farmer) => [
            'farm_id' => $farmer->id,
            'farm_en' => $farmer->farm_en,
            'farm_nick_en' => $farmer->farm_nick_en,
            'location' => $farmer->location,
            'amount' => 0,
            'fav' => $farmer->fav,
            // 'adv_card' => $adv_pending,
            // 'balance_card' => $purchase_pending,
            'farmer_pend' => $farmer->farmer_pend,
        ]);

        return [
            'head_card' => $head_card,
            'farmers' => $farmersList,
            'next_url' => $nextCursor,
            'prev_url' => $prevCursor,
        ];
    }


    // fucntion to get farmer profile details
    // public static function farmer_profile(array $data)
    // {

    //     $farm_id = $data['farm_id'];

    //     // \Log::info('Farmer ID: ' . $farm_id);

    //     $data = Farmer::select('id as farm_id', 'farm_en', 'farm_nick_en', 'location', 'ph_no', 'wp_no', 'fav', 'open_type', 'open_bal', 'created_at')
    //         ->where('id', $farm_id)
    //         ->first();

    //     $give_bal = 0;
    //     $get_bal = 0;

    //     //   \Log::info('get balance: ' . $get_bal);

    //     $load = Load::with(['load_data:id,load_seq', 'product_data:id,name_en'])->where('farmer_id', $farm_id)->get()->map(function ($item) {
    //         $item->table = 'e_load';
    //         $adv = $item->adv ?? 0;
    //         $item->farmer_pend = ($item->total_amt);

    //         return $item;
    //     });

    //     // Log::info("load farmer pend", ['total' => $load->sum('farmer_pend')]);

    //     // dd($load->toArray());

    //     $stock_in = Stock_in::with(['product_data:id,name_en'])->where('cat', 'purchase')->where('farm_id', $farm_id)->get()->map(function ($item) {
    //         $item->table = 'stock_in';
    //         $adv = $item->adv ?? 0;
    //         $item->farmer_pend = ($item->total_amt);

    //         return $item;
    //     });

    //     // Log::info("stock_in farmer pend", ['total' => $stock_in->sum('farmer_pend')]);

    //     $transactions = Farmer_cash::with(['load_data:id,load_seq', 'created_by:id,name'])->where('farm_id', $farm_id)->orderBy('created_at', 'desc')->get()->map(function ($item) {
    //         $item->table = 'farmer_cash';

    //         if ($item->method != 'Cash' && $item->method != 'upi' && $item->method != 'cash') {
    //             $item->method_details = Bank::where('id', $item->method)->select('b_name', 'acc_no')->first();
    //         }

    //         return $item;
    //     });

    //     $famer_open_bal = collect([
    //         (object) [
    //             'id' => null,
    //             'farm_id' => $farm_id,
    //             'open_type' => $data->open_type,
    //             'amount' => $data->open_bal,
    //             'method' => null,
    //             'status' => null,
    //             'c_by' => null,
    //             'date' => date('d-m-Y H:i:s', strtotime($data->created_at)),
    //             'created_at' => Carbon::parse($data->created_at),
    //             'table' => 'opening_balance',
    //         ],
    //     ]);

    //     // dd($famer_open_bal);

    //     // merge load and stock in
    //     $merge = $load->concat($stock_in)->sortByDesc('created_at')->values();
    //     $purchase_pending = $merge->sum('farmer_pend') -($transactions->where('type', 'advance_deduct')->sum('amount')) - ($transactions->where('type', 'purchase')->sum('amount'));

    //     // \Log::info('Purchase Pending: ' . $purchase_pending);

    //     if ($data->open_type === 'give') {
    //         $final_bal = $purchase_pending + $data->open_bal;
    //     } elseif ($data->open_type === 'get') {
    //         $final_bal = $purchase_pending - $data->open_bal;
    //     }

    //     // \Log::info('Purchase Pending: ' . $final_bal);

    //     $adv_pending = ($transactions->where('type', 'advance')->sum('amount')) - ($transactions->where('type', 'advance_deduct')->sum('amount'));

    //     $data->balance_card = $final_bal;
    //     $data->adv_card = $adv_pending;

    //     // \Log::info('Final Balance: ' . $final_bal);

    //     $transact_list = $transactions->concat($famer_open_bal)->sortByDesc('created_at')->values();

    //     //   $loads = Load::query()
    //     //   ->from((new Load)->getTable())
    //     //   ->select(
    //     //     'id',
    //     //         'created_at',
    //     //         'total_amt',
    //     //         DB::raw("'e_load' as table_name")
    //     //     )
    //     //     ->where('farmer_id', $farm_id);

    //     //     $stock = Stock_in::query()
    //     //         ->from((new Stock_in)->getTable())
    //     //         ->select(
    //     //             'id',
    //     //             'created_at',
    //     //             'total_amt',
    //     //             DB::raw("'stock_in' as table_name")
    //     //         )
    //     //         ->where('farm_id', $farm_id)
    //     //         ->where('cat', 'purchase');

    //     //     $union = $loads->unionAll($stock);

    //     //     $result = DB::query()
    //     //         ->fromSub($union, 'farmer_activity')
    //     //         ->orderByDesc('created_at')
    //     //         ->orderByDesc('id')
    //     //         ->cursorPaginate(20);


    //         /* ---------- LOAD RELATIONSHIP DATA ---------- */

    //         // $loadIds = collect($result->items())
    //         //     ->where('table_name','e_load')
    //         //     ->pluck('id');

    //         // $stockIds = collect($result->items())
    //         //     ->where('table_name','stock_in')
    //         //     ->pluck('id');


    //         // $loadsData = Load::with([
    //         //         'load_data:id,load_seq',
    //         //         'product_data:id,name_en'
    //         //     ])
    //         //     ->whereIn('id', $loadIds)
    //         //     ->get()
    //         //     ->keyBy('id');


    //         // $stockData = Stock_in::with([
    //         //         'product_data:id,name_en'
    //         //     ])
    //         //     ->whereIn('id', $stockIds)
    //         //     ->get()
    //         //     ->keyBy('id');


    //         /* ---------- ATTACH RELATIONS ---------- */

    //         // $result->through(function ($item) use ($loadsData, $stockData) {

    //         //     if ($item->table_name === 'e_load') {

    //         //         $load = $loadsData[$item->id] ?? null;

    //         //         if ($load) {
    //         //             $item->load_seq = $load->load_data->load_seq ?? null;
    //         //             $item->product = $load->product_data->name_en ?? null;
    //         //         }

    //         //     }

    //         //     if ($item->table_name === 'stock_in') {

    //         //         $stock = $stockData[$item->id] ?? null;

    //         //         if ($stock) {
    //         //             $item->product = $stock->product_data->name_en ?? null;
    //         //         }

    //         //     }

    //         //     $item->farmer_pend = $item->total_amt;

    //         //     return $item;
    //         // });


    //     $resp = [
    //         'profile' => $data,
    //         'loads' => $merge,
    //         'transactions' => $transact_list,
    //     ];

    //     return $resp;
    // }


    // function to get farmer profil

    
    public static function farmer_profile(array $data)
    {
        $farm_id = $data['farm_id'] ?? 2;

        // ✅ Profile
        $farmer = Farmer::select(
            'id as farm_id',
            'farm_en',
            'farm_nick_en',
            'location',
            'ph_no',
            'wp_no',
            'fav',
            'open_type',
            'open_bal',
            'created_at'
        )->findOrFail($farm_id);

        // ✅ Calculations (DB optimized)
        $loadPending = Load::where('farmer_id', $farm_id)
            ->sum(DB::raw('total_amt'));

        $stockPending = Stock_in::where('cat', 'purchase')
            ->where('farm_id', $farm_id)
            ->sum(DB::raw('total_amt'));

        $purchasePaid = Farmer_cash::where('farm_id', $farm_id)
            ->where('type', 'purchase')
            ->sum('amount');

        $advance = Farmer_cash::where('farm_id', $farm_id)
            ->where('type', 'advance')
            ->sum('amount');

        $advanceDeduct = Farmer_cash::where('farm_id', $farm_id)
            ->where('type', 'advance_deduct')
            ->sum('amount');

        $purchase_pending = ($loadPending + $stockPending) - ($purchasePaid +$advanceDeduct);

        \Log::info('Purchase Pending Calculation', [
            'loadPending' => $loadPending,
            'stockPending' => $stockPending,
            'purchasePaid' => $purchasePaid,
            'purchase_pending' => $purchase_pending,
        ]);

        $final_bal = $farmer->open_type === 'give'
            ? $purchase_pending + $farmer->open_bal
            : $purchase_pending - $farmer->open_bal;

        $adv_pending = $advance - $advanceDeduct;

        $farmer->balance_card = $final_bal;
        $farmer->adv_card = $adv_pending;

        // ✅ Transactions only (lightweight)
        $transactions = Farmer_cash::with([
            'load_data:id,load_seq',
            'created_by:id,name',
            'farmer_bank_detail:id,b_name,acc_no'
        ])
            ->where('farm_id', $farm_id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(10)
            ->through(function ($item) {

                $item->table = 'farmer_cash';

                if (!in_array(strtolower($item->method), ['cash', 'upi'])) {
                    $item->method_details = $item->farmer_bank_detail;
                }

                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'method' => $item->method,
                    'method_details' => $item->method_details ?? null,
                    'created_by' => $item->created_by->name ?? null,
                    'created_at' => $item->created_at,
                    'created_at_display' => date('d-m-Y H:i:s', strtotime($item->created_at)),
                    'table' => $item->table,
                ];
            });

            // ✅ Get collection from paginator
                $items = collect($transactions->items());

                // ✅ Check last page
                $isLastPage = $transactions->nextCursor() === null;

                // ✅ Add opening balance
                if ($isLastPage && $farmer) {

                    $items->push([
                        'id' => null,
                        'type' => $farmer->open_type ?? null,
                        'amount' => $farmer->open_bal ?? 0,
                        'method' => null,
                        'method_details' => null,
                        'created_by' => null,
                        'created_at' => \Carbon\Carbon::parse($farmer->created_at ?? now()),
                        'created_at_display' => date(
                            'd-m-Y H:i:s',
                            strtotime($farmer->created_at ?? now())
                        ),
                        'table' => 'opening_balance',
                    ]);

                    // 🔥 maintain order
                    $items = $items->sortByDesc('created_at')->values();
                }

                // ✅ VERY IMPORTANT → put back into paginator
                $transactions->setCollection($items);


        return [
            'profile' => $farmer,
            'transactions' => $transactions->items(),
            'next_url' => $transactions->nextCursor()?->encode(),
            'prev_url' => $transactions->previousCursor()?->encode(),
        ];
    }
   

    // function for farmer profile load more

    public static function farmer_profile_load(array $data){

      $farm_id = $data['farm_id'] ?? 2;

    // ✅ UNION query (FAST)
        $activity = DB::connection('tenant')
            ->query()
            ->fromSub(function ($q) use ($farm_id) {

                $q->from('e_load')
                    ->select(
                        'id',
                        'grace_piece',
                        'grace_per',
                        'bill_piece',
                        'price',
                        'created_at',
                        'total_amt',
                        'bill_amount',
                        'commission',
                        'adv',
                        DB::raw("'e_load' as type")
                    )
                    ->where('farmer_id', $farm_id)

                    ->unionAll(
                        DB::table('stock_in')
                            ->select(
                                'id',
                                'grace_piece',
                                'grace_per',
                                'bill_piece',
                                'price',
                                'created_at',
                                'total_amt',
                                'bill_amount',
                                'commission',
                                'adv',
                                DB::raw("'stock_in' as type")
                            )
                            ->where('farm_id', $farm_id)
                            ->where('cat', 'purchase')
                    );

            }, 'activity')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(10);

        $loadIds = collect($activity->items())
            ->where('type', 'e_load')
            ->pluck('id');

        $stockIds = collect($activity->items())
            ->where('type', 'stock_in')
            ->pluck('id');

        $loadsData = Load::with([
            'load_data:id,load_seq',
            'product_data:id,name_en',
        ])
            ->whereIn('id', $loadIds)
            ->get()
            ->keyBy('id');

        $stockData = Stock_in::with([
            'product_data:id,name_en',
        ])
            ->whereIn('id', $stockIds)
            ->get()
            ->keyBy('id');

        $activityList = collect($activity->items())->map(function ($item) use ($loadsData, $stockData) {

            if ($item->type === 'e_load') {

                $load = $loadsData[$item->id] ?? null;

                return [
                    'id' => $item->id,
                    'type' => 'load',
                    'total_amount' => $item->total_amt,
                    'billing_amount' =>$item->bill_amount,
                    'commission' => $item->commission,
                    'advance_amount' => $item->adv ?? 0,
                    'product' => $load?->product_data?->name_en,
                    'grace_piece' => $item->grace_piece,
                    'grace_per' => $item->grace_per,
                    'bill_piece' => $item->bill_piece,
                    'product_price' => $item->price,
                    'load_seq' => $load?->load_data?->load_seq,
                    'created_at' => date('d-m-Y H:i:s', strtotime($item->created_at)),
                ];
            }

            if ($item->type === 'stock_in') {

                $stock = $stockData[$item->id] ?? null;

                return [
                    'id' => $item->id,
                    'type' => 'stock',
                    'total_amount' => $item->total_amt,
                    'billing_amount' =>$item->bill_amount,
                    'commission' => $item->commission,
                    'advance_amount' => $item->adv ?? 0,
                    'product' => $stock?->product_data?->name_en,
                    'grace_piece' => $item->grace_piece,
                    'grace_per' => $item->grace_per,
                    'bill_piece' => $item->bill_piece,
                    'product_price' => $item->price,
                    'created_at' => date('d-m-Y H:i:s', strtotime($item->created_at)),
                ];
            }

        });


        return [
            'activity' => $activityList,
            'next_url' => $activity->nextCursor()?->encode(),
            'prev_url' => $activity->previousCursor()?->encode(),
        ];



    }

    // function to get farmer advance pending

    public static function farmer_advance_pending(array $data)
    {

        $farm_id = $data['farm_id'];

        $pending_advance = Farmer_cash::where('farm_id', $farm_id)->where('type', 'advance')->sum('amount')
                         - Farmer_cash::where('farm_id', $farm_id)->where('type', 'advance_deduct')->sum('amount');

        // \Log::info('Farmer ID for advance pending: ' . $pending_advance);

        // Logic to calculate pending advance can be added here

        return $pending_advance;

    }

    // function to process farmer pay out
    public static function farmer_pay_out(array $data)
    {

        // Logic to process pay out can be added here

        $farm_cash = Farmer_cash::create([
            'farm_id' => $data['farm_id'] ?? null,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'method' => $data['pay_method'] ?? 'Cash',
            'c_by' => Auth::guard('tenant')->user()->id ?? null,
        ]);

        return $farm_cash;
    }

    // function to process farmer pay in
    public static function farmer_pay_in(array $data)
    {
        // Logic to process pay in can be added here

        $farm_cash = Farmer_cash::create([
            'farm_id' => $data['farm_id'] ?? null,
            'load_id' => $data['load_id'] ?? null,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'method' => $data['pay_method'] ?? 'Cash',
            'c_by' => Auth::guard('tenant')->user()->id ?? null,
        ]);

        return $farm_cash;
    }

    // function to edit farmer pay

    public static function farmer_pay_edit(array $data)
    {

        $farm_cash = Farmer_cash::find($data['payment_id'] ?? 0);
        if ($farm_cash) {
            // Fill the model with new data
            $farm_cash->fill([
                // 'farm_id' => $data['farm_id'] ?? null,
                // 'load_id' => $data['load_id'] ?? null,
                // 'type'    => $data['type'],
                'amount' => $data['amount'],
                'method' => $data['pay_method'] ?? 'Cash',
            ]);

            // Save only if there are changes
            if ($farm_cash->isDirty()) {
                $farm_cash->save();
            }

        } else {
            throw new \Exception('Farmer payment record not found.');
        }

        return $farm_cash;
    }

    // function to get inactive farmers
    public static function farmer_inactive(array $data)
    {
         // 🔍 Apply search
        $keyword = $data['keyword'] ?? null;

        $query = Farmer::where('status', 'inactive');

       
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('farm_en', 'like', "%{$keyword}%");
                // ->orWhere('farm_nick_en', 'like', "%{$keyword}%")
                // ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        $farmers = $query
            // ->orderBy('fav', 'DESC')
            ->orderByDesc('id')
            ->cursorPaginate(10);

        return ['farmers' => $farmers->items(), 'next_url' => $farmers->nextCursor()?->encode(), 'prev_url' => $farmers->previousCursor()?->encode()];
    }

    // function to farmer advance report

    public static function farmer_advance_report()
    {
        $advances = Farmer_cash::query()
            ->with(['farm_data:id,farm_en'])
            ->select('farm_id')
            ->selectRaw("SUM(CASE WHEN type = 'advance' THEN amount ELSE 0 END) as total_advance")
            ->selectRaw("SUM(CASE WHEN type = 'advance_deduct' THEN amount ELSE 0 END) as total_deduct")
            ->selectRaw("
                    SUM(CASE WHEN type = 'advance' THEN amount ELSE 0 END) -
                    SUM(CASE WHEN type = 'advance_deduct' THEN amount ELSE 0 END)
                    as pending_amount
                ")
            ->groupBy('farm_id')
            ->get();

        $adv = $advances->map(function ($item) {
            return [
                'farm_id' => $item->farm_id,
                'farm_en' => $item->farm_data->farm_en ?? 'Unknown',
                'total_advance' => $item->total_advance,
                'total_deduct' => $item->total_deduct,
                'pending_amount' => $item->pending_amount,
            ];
        });

        return $adv;

    }

    // fucntion to get farmer coconut report

    public static function farmer_coconut_report(array $data)
    {

        $farm_id = $data['farm_id'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;

        Log::info('Generating Coconut Report', [
            'farm_id' => $farm_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        $coconutData_in_stock = Stock_in::query()->with(['farm_data:id,farm_en'])->where('cat', 'purchase')->where('farm_id', $farm_id)
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->select('id', 'farm_id', 'total_piece', 'created_at')
            ->get()->map(function ($item) {
                $item->table = 'stock_in';

                return $item;
            });

        $coconutData_in_load = Load::query()->with(['farmer_data:id,farm_en'])->where('cat', 'add')->where('farmer_id', $farm_id)
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->select('id', 'farmer_id', 'total_piece', 'created_at')
            ->get()
            ->map(function ($item) {
                $item->farm_id = $item->farmer_id; // create farm_id manually
                $item->table = 'load';

                return $item;
            });

        // Log::info('Coconut Data in Stock', [
        //     'stock' => $coconutData_in_stock->toArray(),
        // ]);

        $coconutData = $coconutData_in_stock->concat($coconutData_in_load);

        $report = $coconutData->map(function ($item) {
            return [
                'farm_id' => $item->farm_id,
                'farm_en' => $item->farm_data->farm_en
                    ?? $item->farmer_data->farm_en
                    ?? 'Unknown',
                'total_pieces' => $item->total_piece,
                'table' => $item->table ?? 'Unknown',
                'created_at' => date('d-m-Y H:i:s', strtotime($item->created_at)),
            ];
        });

        Log::info('Combined Coconut Data', [
            'combined' => $report->toArray(),
        ]);

        return $report;
    }

    // function to get farmer advance deduct report

    public static function farmer_advance_deduct_report(array $data)
    {

        $farm_id = $data['farm_id'] ?? null;
        // $start_date = $data['start_date'] ?? null;
        // $end_date = $data['end_date'] ?? null;

        $deducts = Farmer_cash::query()

            ->with(['farm_data:id,farm_en','farmer_bank_detail'])
            ->where('farm_id', $farm_id)
            ->where('type', '!=', 'purchase')
            // ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
            //     $query->whereBetween('created_at', [$start_date, $end_date]);
            // })
            ->select('id', 'farm_id', 'type', 'method', 'amount', 'created_at')
            ->orderby('id', 'desc')
            ->get();

        $report = $deducts->map(function ($item) {

            // $method = $item->method;

            if ($item->farmer_bank_detail) {
                $bank = $item->farmer_bank_detail;
            }else{
                $bank = null;
            }

            return [
                'farm_id' => $item->farm_id,
                'farm_en' => $item->farm_data->farm_en ?? 'Unknown',
                'amount' => $item->amount,
                'type' => $item->type,
                'method' => $item->method, // You can replace this with the formatted method if needed
                'bank'=>$bank,
                'date' => date('d-m-Y H:i:s', strtotime($item->created_at)),
            ];
        });

        return $report;
    }

    // function to get farmer payment out report

    public static function farmer_payment_out_report(array $data)
    {

        $farm_id = $data['farm_id'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;

        $payments = Farmer_cash::query()
            ->with(['farm_data:id,farm_en','farmer_bank_detail'])
            ->where('farm_id', $farm_id)
            ->where('type', 'purchase')
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->select('id', 'farm_id', 'amount', 'type', 'method', 'created_at')
            ->orderby('id', 'desc')
            ->get();

        $report = $payments->map(function ($item) {
            return [
                'farm_id' => $item->farm_id,
                'farm_en' => $item->farm_data->farm_en ?? 'Unknown',
                'amount' => $item->amount,
                'method' => $item->method,
                'bank' => $item->farmer_bank_detail,
                'type' => $item->type,
                'date' => date('d-m-Y H:i:s', strtotime($item->created_at)),
            ];
        });

        return $report;

    }

    // function to get farmer payment pending report

   public static function farmer_payment_pending_report(array $data)
    {
        $farm_id    = $data['farm_id'] ?? null;
        // $start_date = $data['start_date'] ?? null;
        // $end_date   = $data['end_date'] ?? null;



        $query = Load::with(['farmer_data:id,farm_en', 'load_data:id,load_seq'])->where('cat', 'add');
            // ->when($farm_id && $farm_id !== 'all', function ($query) use ($farm_id) {
            //     $query->where('farmer_id', $farm_id);
            // })
            // ->when($start_date, function ($query) use ($start_date) {
            //     $query->whereDate('created_at', '>=', $start_date);
            // })
            // ->when($end_date, function ($query) use ($end_date) {
            //     $query->whereDate('created_at', '<=', $end_date);
            // });

        $loads = $query->orderBy('created_at', 'desc')->get();

        $loads = $loads->map(function ($item) {
            return [
                'farm_id'     => $item->farmer_id,
                'farmer_name' => $item->farmer_data->farm_en ?? 'Unknown',
                'load_seq'    => $item->load_data->load_seq ?? null,
                'total_amt'   => $item->total_amt,
                'type'        => 'load',
                'method'      => null,   // ✅ add this
                'created_at'  => $item->created_at,
            ];
        });

         $stock_in = Stock_in::with(['farm_data:id,farm_en', 'product_data:id,name_en'])->where('cat', 'purchase')
            // ->when($farm_id && $farm_id !== 'all', function ($query) use ($farm_id) {
            //     $query->where('farm_id', $farm_id);
            // })
            // ->when($start_date, function ($query) use ($start_date) {
            //     $query->whereDate('created_at', '>=', $start_date);
            // })
            // ->when($end_date, function ($query) use ($end_date) {
            //     $query->whereDate('created_at', '<=', $end_date);
            // })
            ->get()
            ->map(function ($item) {
                return [
                    'farm_id'     => $item->farm_id,
                    'farmer_name' => $item->farm_data->farm_en ?? 'Unknown',
                    'total_amt'   => $item->total_amt,
                    'method'      => null,
                    'type'        => 'stock_in',
                    'created_at'  => $item->created_at,
                ];
            });

        $farmer_purchase_pending = Farmer_cash::query()
            ->with('farm_data:id,farm_en')
            ->where('type', 'purchase');
            // ->when($farm_id && $farm_id !== 'all', function ($query) use ($farm_id) {
            //     $query->where('farm_id', $farm_id);
            // })
            // ->when($start_date, function ($query) use ($start_date) {
            //     $query->whereDate('created_at', '>=', $start_date);
            // })
            // ->when($end_date, function ($query) use ($end_date) {
            //     $query->whereDate('created_at', '<=', $end_date);
            // });

        $purchase_pending = $farmer_purchase_pending->orderBy('created_at', 'desc')->get();

        $purchase_pending = $purchase_pending->map(function ($item) {
            return [
                'farm_id'     => $item->farm_id,   // ✅ ADD THIS
                'farmer_name' => $item->farm_data->farm_en ?? 'Unknown',
                'total_amt'   => $item->amount,
                'method'      => $item->method,
                'type'        => 'cash',
                'created_at'  => $item->created_at,
            ];
        });

         $farmer_advance_deduct = Farmer_cash::query()
            ->with('farm_data:id,farm_en')
            ->where('type', 'advance_deduct');
            // ->when($farm_id && $farm_id !== 'all', function ($query) use ($farm_id) {
            //     $query->where('farm_id', $farm_id);
            // })
            // ->when($start_date, function ($query) use ($start_date) {
            //     $query->whereDate('created_at', '>=', $start_date);
            // })
            // ->when($end_date, function ($query) use ($end_date) {
            //     $query->whereDate('created_at', '<=', $end_date);
            // });

        $farmer_advance_deduct = $farmer_advance_deduct->orderBy('created_at', 'desc')->get();

        $farmer_advance_deduct = $farmer_advance_deduct->map(function ($item) {
            return [
                'farm_id'     => $item->farm_id,   // ✅ ADD THIS
                'farmer_name' => $item->farm_data->farm_en ?? 'Unknown',
                'total_amt'   => $item->amount,
                'method'      => $item->method,
                'type'        => 'advance_deduct',
                'created_at'  => $item->created_at,
            ];
        });

        // \Log::info('Purchase Pending Data', [
        //     'purchase_pending' => $purchase_pending->toArray(),
        // ]);


       

        $concat = $loads->concat($purchase_pending)->concat($stock_in)->concat($farmer_advance_deduct)->sortByDesc('created_at')->values();

        // If farm_id = all → group by farmer
        if ($farm_id === 'all') {

        $farmers = Farmer::get()->keyBy('id');

            $result = $concat->groupBy('farm_id')->map(function ($items, $farm) use ($farmers) {

                $load_total = $items->where('type', 'load')->sum('total_amt');
                $stock_in_total = $items->where('type', 'stock_in')->sum('total_amt');
                $paid_total = $items->where('type', 'cash')->sum('total_amt');
                $advance_deduct_total = $items->where('type', 'advance_deduct')->sum('total_amt');

                $farmer_check = $farmers[$farm] ?? null;

                Log::info("Calculating pending for farm_id: $farm", [
                    'farmer_check' => $farmer_check ? $farmer_check->toArray() : null,
                ]);

                if ($farmer_check) {
                    if ($farmer_check->open_type == 'give') {
                        $load_total += $farmer_check->open_bal;
                    } elseif ($farmer_check->open_type == 'get') {
                        $load_total -= $farmer_check->open_bal;
                    }
                }

                return [
                    'farm_id'        => $farm,
                    'farmer_name'    => $items->first()['farmer_name'],
                    'total_load'     => $load_total,
                    'total_paid'     => $paid_total,
                    'advance_deduct_total' => $advance_deduct_total,
                    'pending_amount' => $load_total + $stock_in_total - $paid_total - $advance_deduct_total,
                    'type'          => $items->first()['type'], // Assuming you want to keep the type of the first item in the group
                ];
            })->values();

        } else {

            $result = $concat->sortByDesc('created_at')->map(function ($item) {

                $farmer_check = Farmer::find($item['farm_id']);

                
                

                if($farmer_check->open_type=='give'){
                    $item['total_amt'] += $farmer_check->open_bal;
                }elseif($farmer_check->open_type=='get'){
                    $item['total_amt'] -= $farmer_check->open_bal;
                }


                return [
                    'farmer_name' => $item['farmer_name'],
                    'total_amt'   => $item['total_amt'],
                    'method'      => $item['method'],
                    'type'        => $item['type'],
                    'created_at'  => date('d-m-Y H:i:s', strtotime($item['created_at'])),
                ];

            })->values();
        }
        return $result;
    }
}
