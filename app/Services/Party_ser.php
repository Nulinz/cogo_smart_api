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
        return Party::findOrFail($party_id);
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

    // function to get party profile details

    public static function party_profile(array $data)
    {

        $party_id = $data['party_id'];

        // \Log::info('Party ID: ' . $party_id);

        $data = Party::select('id as party_id', 'party_en', 'party_nick_en', 'party_location', 'party_ph_no', 'party_wp_no', 'fav', 'party_open_type', 'party_open_bal', 'created_at')
            ->where('id', $party_id)
            ->first();

        $give_bal = 0;
        $get_bal = 0;

        $party_cash = Party_cash::where('party_id', $party_id)->select('id', 'type', 'amount', 'method', 'created_at')->get()->map(function ($item) {
            $item->source = 'cash';

            if ($item->method != 'Cash' && $item->method != 'upi' && $item->method != 'cash') {
                $item->method_details = Bank::where('id', $item->method)->select('b_name', 'acc_no')->first();
            }

            return $item;
        });

        $party_load = Prime_load::where('party_id', $party_id)->where('status', 'active')->where('load_status', 'inv_completed')->pluck('id');

        //  \Log::info('Party ID: ' . $party_load);

        // $inv_data = E_invoice::whereIn('load_id', $party_load)->select('id','bill_amt')->get();

        $inv_data = E_invoice::whereIn('load_id', $party_load)
            ->groupBy('load_id')
            ->selectRaw("
                    load_id,
                    SUM(bill_amt) as total_amt,
                    DATE_FORMAT(MAX(created_at), '%d-%m-%Y %H:%i:%s') as  invoice_date
                ")
            ->get()->map(function ($item) {
                $item->source = 'invoice';
                $item->created_at = $item->invoice_date;
                $item->load_name = Prime_load::where('id', $item->load_id)->value('load_seq');
                $item->inv_no = M_invoice::where('load_id', $item->load_id)->value('inv_no');

                $prime_inv = M_invoice::where('load_id', $item->load_id)
                    ->select('id', 'charges')
                    ->first();

                $charge_out = $prime_inv->charges ?? [];

                //   \Log::info('Charge Out for Load ID '.$prime_inv->id.': ', $charge_out);
                //   \Log::info('total_amt'.$item->total_amt);

                $item->total_amt += array_sum(array_column($charge_out, 'amt'));
                // $item->total_amt += array_sum($charge_out);

                // $arry_sum = array_sum($charge_out);

                // \Log::info('total_amt'.$item->total_amt);
                // \Log::info('total_amt'.$arry_sum);

                // $item->total_amt = $item->total_amt + ($charge_out['total_charge'] ?? 0);
                return $item;
            });

        $inv_amount = $inv_data->sum('total_amt');

        $party_others = Shift::with(['product_data:id,name_en'])->where('cat', 'others')->where('party_id', $party_id)->where('status', 'active')->get()->map(function ($item) {
            $item->source = 'others';

            return $item;
        });

        $party_other_amount = $party_others->sum('bill_amount');

        $party_stock = Stock_out::with(['product:id,name_en'])->where('cat', 'sales')->where('farm_id', $party_id)->select('id', 'total_piece', 'bill_amount', 'created_at', 'product_id', 'inv_no')->get()->map(function ($item) {
            $item->source = 'sales';

            return $item;
        });

        $party_sales = $party_stock->sum('bill_amount');

        $party_open_balance = collect([
            (object) [
                'id' => null,
                'open_type' => $data->party_open_type,
                'amount' => $data->party_open_bal,
                'method' => null,
                'status' => null,
                'c_by' => null,
                'date' => date('d-m-Y H:i:s', strtotime($data->created_at)),
                'created_at' => $data->created_at,
                'table' => 'opening_balance',
            ],
        ]);

        $party_trans = $party_cash->concat($inv_data)->concat($party_others)->concat($party_stock)->concat($party_open_balance)
            ->map(function ($item) {

                if ($item->created_at instanceof Carbon) {
                    $item->sort_ts = $item->created_at->timestamp;
                } else {
                    // handle both formats
                    if (preg_match('/^\d{2}-\d{2}-\d{4}/', $item->created_at)) {
                        $dt = Carbon::createFromFormat('d-m-Y H:i:s', $item->created_at);
                    } else {
                        $dt = Carbon::parse($item->created_at);
                    }

                    $item->created_at = $dt;          // keep Carbon
                    $item->sort_ts = $dt->timestamp; // PURE numeric
                }

                return $item;

            })
            ->sortByDesc('sort_ts')->values();

        $in_cash = $party_cash->where('type', 'pay_in')->sum('amount');
        $out_cash = $party_cash->where('type', 'pay_out')->sum('amount');

        $bal = ($inv_amount + $party_sales + $party_other_amount + $out_cash) - $in_cash;

        // \Log::info('inv amount: ' . $inv_amount);
        // \Log::info('party sales: ' . $party_sales);
        // \Log::info('in cash: ' . $in_cash);
        // \Log::info('out cash: ' . $out_cash);
        // \Log::info('balance: ' . $bal);

        if ($data->party_open_type === 'give') {
            $give_bal = $data->party_open_bal;
            $bal = $bal - $give_bal;
        } elseif ($data->party_open_type === 'get') {
            $get_bal = $data->party_open_bal;
            $bal = $bal + $get_bal;
        }

        // $data->amount = 0;
        $data->balance = $bal;

        return ['data' => $data, 'party_cash' => $party_trans];
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

    public static function party_inactive()
    {

        $parties = Party::where('status', 'inactive')->get();

        return $parties;

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
            ->select('id', 'inv_no', 'load_id', 'created_at', 'charges')
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

        $payment_out = Party_cash::where('party_id', $party_id)->where('type', 'pay_out')
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            })
            ->select('id', 'amount', 'method', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return $payment_out;
    }

    // function to party payment pending in report

    public static function party_payment_pending_report(array $data)
    {
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;

        $parties = Party::select('id', 'party_en')->get();

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

            $total_invoice = $invoice_total + $sales_total + $others_total;

            $pending = $total_invoice - $payment_out;

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
