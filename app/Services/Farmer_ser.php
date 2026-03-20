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

        return $farm;
    }

    public static function get_all_farmers()
    {
        $data = Farmer::where('status', 'active')->orderBy('fav', 'DESC')->get()->map(function ($farmer) {

            $loadPending = Load::where('farmer_id', $farmer->id)
                ->sum(DB::raw('total_amt - IFNULL(adv,0)'));

            $stockPending = Stock_in::where('cat', 'purchase')
                ->where('farm_id', $farmer->id)
                ->sum(DB::raw('total_amt - IFNULL(adv,0)'));

            $paidAmount = Farmer_cash::where('farm_id', $farmer->id)
                ->where('type', 'purchase')
                ->sum('amount');

            $purchase_pending = ($loadPending + $stockPending) - $paidAmount;

            $farmer->farmer_pend = $farmer->open_type === 'give'
                ? $purchase_pending + $farmer->open_bal
                : $purchase_pending - $farmer->open_bal;

            return $farmer;
        });

        $give_bal = 0;
        $get_bal = 0;

        $give_bal = $data->where('open_type', 'give')->sum('open_bal');
        $get_bal = $data->where('open_type', 'get')->sum('open_bal');

        $load = Load::whereNotNull('farmer_id')->get();

        $stock_in = Stock_in::where('cat', 'purchase')->whereNotNull('farm_id')->get();

        $merge = $load->concat($stock_in)->sortByDesc('created_at')->values();

        $transactions = Farmer_cash::all();

        $purchase_pending = ($merge->sum('total_amt') - $merge->sum('adv')) - ($transactions->where('type', 'purchase')->sum('amount'));

        $adv_pending = ($transactions->where('type', 'advance')->sum('amount')) - ($transactions->where('type', 'advance_deduct')->sum('amount'));

        //  if ($data->open_type === 'give') {
        //     $final_bal = $purchase_pending + $give_bal;
        // } elseif ($data->open_type === 'get') {
        //     $final_bal = $purchase_pending - $get_bal;
        // }

        $final_bal = $purchase_pending + $give_bal - $get_bal;

        $head_card = [
            'adv_card' => $adv_pending,
            'balance_card' => $final_bal,
        ];

        // 2. Use map to iterate over each farmer and format the array
        $farmers = $data->map(function ($farmer) use ($adv_pending, $purchase_pending) {
            return [
                'farm_id' => $farmer->id,
                'farm_en' => $farmer->farm_en,
                'farm_nick_en' => $farmer->farm_nick_en,
                'location' => $farmer->location,
                'amount' => 0,
                'fav' => $farmer->fav,
                'adv_card' => $adv_pending,
                'balance_card' => $purchase_pending,
                'farmer_pend' => $farmer->farmer_pend,
            ];
        });

        return $value = [
            'head_card' => $head_card,
            'farmers' => $farmers,
        ];
    }

    // fucntion to get farmer profile details
    public static function farmer_profile(array $data)
    {

        $farm_id = $data['farm_id'];

        // \Log::info('Farmer ID: ' . $farm_id);

        $data = Farmer::select('id as farm_id', 'farm_en', 'farm_nick_en', 'location', 'ph_no', 'wp_no', 'fav', 'open_type', 'open_bal', 'created_at')
            ->where('id', $farm_id)
            ->first();

        $give_bal = 0;
        $get_bal = 0;

        //   \Log::info('get balance: ' . $get_bal);

        $load = Load::with(['load_data:id,load_seq', 'product_data:id,name_en'])->where('farmer_id', $farm_id)->get()->map(function ($item) {
            $item->table = 'e_load';
            $adv = $item->adv ?? 0;
            $item->farmer_pend = ($item->total_amt - $adv);

            return $item;
        });

        // dd($load->toArray());

        $stock_in = Stock_in::with(['product_data:id,name_en'])->where('cat', 'purchase')->where('farm_id', $farm_id)->get()->map(function ($item) {
            $item->table = 'stock_in';
            $adv = $item->adv ?? 0;
            $item->farmer_pend = ($item->total_amt - $adv);

            return $item;
        });

        $transactions = Farmer_cash::with(['load_data:id,load_seq', 'created_by:id,name'])->where('farm_id', $farm_id)->orderBy('created_at', 'desc')->get()->map(function ($item) {
            $item->table = 'farmer_cash';

            if ($item->method != 'Cash' && $item->method != 'upi' && $item->method != 'cash') {
                $item->method_details = Bank::where('id', $item->method)->select('b_name', 'acc_no')->first();
            }

            return $item;
        });

        $famer_open_bal = collect([
            (object) [
                'id' => null,
                'farm_id' => $farm_id,
                'open_type' => $data->open_type,
                'amount' => $data->open_bal,
                'method' => null,
                'status' => null,
                'c_by' => null,
                'date' => date('d-m-Y H:i:s', strtotime($data->created_at)),
                'created_at' => Carbon::parse($data->created_at),
                'table' => 'opening_balance',
            ],
        ]);

        // dd($famer_open_bal);

        // merge load and stock in
        $merge = $load->concat($stock_in)->sortByDesc('created_at')->values();
        $purchase_pending = $merge->sum('farmer_pend') - ($transactions->where('type', 'purchase')->sum('amount'));

        if ($data->open_type === 'give') {
            $final_bal = $purchase_pending + $data->open_bal;
        } elseif ($data->open_type === 'get') {
            $final_bal = $purchase_pending - $data->open_bal;
        }

        $adv_pending = ($transactions->where('type', 'advance')->sum('amount')) - ($transactions->where('type', 'advance_deduct')->sum('amount'));

        $data->balance_card = $final_bal;
        $data->adv_card = $adv_pending;

        // \Log::info('Final Balance: ' . $final_bal);

        $transact_list = $transactions->concat($famer_open_bal)->sortByDesc('created_at')->values();

        $resp = [
            'profile' => $data,
            'loads' => $merge,
            'transactions' => $transact_list,
        ];

        return $resp;
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
    public static function farmer_inactive()
    {
        $farmers = Farmer::where('status', 'inactive')->get();

        return $farmers;
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

            ->with(['farm_data:id,farm_en'])
            ->where('farm_id', $farm_id)
            ->where('type', '!=', 'purchase')
            // ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
            //     $query->whereBetween('created_at', [$start_date, $end_date]);
            // })
            ->select('id', 'farm_id', 'type', 'method', 'amount', 'created_at')
            ->orderby('id', 'desc')
            ->get();

        $report = $deducts->map(function ($item) {
            return [
                'farm_id' => $item->farm_id,
                'farm_en' => $item->farm_data->farm_en ?? 'Unknown',
                'amount' => $item->amount,
                'type' => $item->type,
                'method' => $item->method,
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
            ->with(['farm_data:id,farm_en'])
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
                'type' => $item->type,
                'date' => date('d-m-Y H:i:s', strtotime($item->created_at)),
            ];
        });

        return $report;

    }
}
