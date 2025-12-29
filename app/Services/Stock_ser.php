<?php

namespace App\Services;

use App\Models\Prime_load;
use App\Models\Load;
use App\Models\Shift;
use App\Models\Stock_in;
use App\Models\Stock_out;
use App\Models\Summary;
use App\Models\M_invoice;
use App\Models\E_invoice;
use Illuminate\Support\Facades\Auth;

class Stock_ser
{
    public static function stock_home()
    {

        $stock_in  = Stock_in::with('product_data:id,name_en')->get();
        $stock_out = Stock_out::get();

        $stockOutByProduct = $stock_out->groupBy('product_id');

        $products = $stock_in
                    ->groupBy('product_id')
                    ->map(function ($items, $productId) use ($stockOutByProduct) {

                    // Stock IN totals
                    $in_billing = $items->sum('total_piece');
                    $in_grace   = $items->sum('grace_piece');
                  

                    // Stock OUT totals (safe)
                    $out_items = $stockOutByProduct->get($productId, collect());

                    $out_billing = $out_items->sum('total_piece');
                    $out_grace   = $out_items->sum('grace_piece');
                   

                    // Remaining
                    $remaining_billing = $in_billing - $out_billing;
                    $remaining_grace   = $in_grace - $out_grace;

                    // Weighted avg price (based on IN only)
                    // ✅ Correct average price
                    $avg_price = $in_billing > 0
                        ? $items->sum(fn ($i) => $i->total_piece * $i->price) / $in_billing
                        : 0;

                    return [
                        'product_id'        => $productId,
                        'product_name'      => $items->first()->product_data->name_en ?? null,
                        'billing_piece'     => $remaining_billing,
                        'grace_piece'       => $remaining_grace,
                        'avg_price'         => round($avg_price, 2),
                        'product_amount'      => round(($remaining_billing+$remaining_grace) * $avg_price),
                    ];
                })->values();

                $total_value = $products->sum('product_amount');

                 // ✅ CALL TRANSACTION LIST HERE
                $transactions = self::stock_transaction_list([]);

                // take only latest 5 transactions
                $latest_transactions = collect($transactions['stock_data'])
                    ->sortByDesc('created_at')
                    ->take(5)
                    ->values();

        return ['total_card' => $total_value,'products' => $products,'transactions' => $latest_transactions  ];

    }

    // function to get the stock transsaction in and out 

    public static function stock_transaction_list(array $data)
    {
        $stock_in_query = Stock_in::with('product_data:id,name_en','farm_data:id,farm_en','load_data:id,load_seq')->get()->map(function($item){
                $item->table = 'in';
                return $item;
        });

        $stock_out_query = Stock_out::with('product:id,name_en','party:id,party_en','load_data:id,load_seq')->get()->map(function($item){
                $item->table = 'out';
                return $item;
        });

        $merge = $stock_in_query->merge($stock_out_query)->sortBy('created_at')->values();


        $stock_data = $merge->map(function($item){

           $type =  $item->table;

           if($type=='in'){
                $item->user = $item->farm_data;
                // $item->load_det = $item->load_data->load_seq ?? null;
           }else{
                $item->user = $item->party;
                // $item->load_det = $item->load->load_seq ?? null;
           }

            return [
                    'id'            => $item->id,

                    'product_name'  => $item->table === 'in'
                                        ? $item->product_data->name_en ?? null
                                        : $item->product->name_en ?? null,

                    'total_piece'   => $item->total_piece ?? 0,

                    'party_name'    => $item->table === 'out'
                                        ? $item->party->party_en ?? null
                                        : null,

                    'farmer_name'   => $item->table === 'in'
                                        ? $item->farm_data->farm_en ?? null
                                        : null,

                    'load_seq'      => $item->load_data->load_seq ?? null,

                    'billing_amount'=> $item->bill_amount ?? 0,

                    'type'          => $item->table,

                    'created_at'    => $item->created_at,
                ];
        //    return $item;

        });


        return [
            'stock_data' => $stock_data,
            
        ];
    }

    // fucnction to get stock product

    public static function get_stock_product(array $data)
    {
        $product_id = $data['product_id'] ?? null;

        if(!$product_id){
            throw new \Exception('Product ID is required');
        }

        $stock_in  = Stock_in::where('product_id', $product_id)->sum('total_piece');
        $stock_out = Stock_out::where('product_id', $product_id)->sum('total_piece');

        $stock = $stock_in - $stock_out;
        return $stock;
    }


   // function to create load summary

   public static function add_load_summary(array $data)
   {
        $load_id = $data['load_id'];

        $load = Load::where('id', $load_id)->first();

        if(!$load){
            throw new \Exception('Load not found');
        }

        // calculate summary

        $summmary = Summary::create([
            'load_id'         => $load_id,
            'filter_total'     => $data['filter_total'] ?? null,
            'filter_price'    => $data['filter_price'] ?? null,
            'filter_amount'   => $data['filter_amount'] ?? null,
            'product_id'      => $data['product_id'] ?? null,
            'exp_loading'     => $data['exp_loading'] ?? null,
            'exp_misc'        => $data['exp_misc'] ?? null,
            'exp_rmc'         => $data['exp_rmc'] ?? null,
            'total'           => $data['total'] ?? null,
            'grace'           => $data['grace'] ?? null,
            'grace_per'       => $data['grace_per'] ?? null,
            'billing_amt'  => $data['billing_amt'] ?? null,
            'avg_price'       => $data['avg_price'] ?? null,
            'total_weight'    => $data['total_weight'] ?? null,
            'empty_weight'    => $data['empty_weight'] ?? null,
            'net_weight'      => $data['net_weight'] ?? null,
            'avg_per_weight'  => $data['avg_per_weight'] ?? null,
            'shift_loss'      => $data['shift_loss'] ?? null,
            'c_by'            => Auth::guard('tenant')->user()->id ?? null,
        ]);

     
        return $summmary;

   }
   

   // function to get load summary

   public static function get_load_summary(array $data)
   {
        $load_id = $data['load_id'];

        $summary = Summary::where('load_id', $load_id)->first();

        if(!$summary){
            throw new \Exception('Summary not found');
        }

        return $summary;
   }

   // function to edit load summary

   public static function edit_load_summary(array $data)
   {
        $load_id = $data['load_id'];

        $summary = Summary::where('load_id', $load_id)->first();

        if(!$summary){
            throw new \Exception('Summary not found');
        }

         $summary->fill([
                'filter_total' => $data['filter_total'] ?? $summary->filter_total,
                'filter_price' => $data['filter_price'] ?? $summary->filter_price,
                'filter_amount' => $data['filter_amount'] ?? $summary->filter_amount,
                'product_id' => $data['product_id'] ?? $summary->product_id,
                'exp_loading' => $data['exp_loading'] ?? $summary->exp_loading,
                'exp_misc' => $data['exp_misc'] ?? $summary->exp_misc,
                'exp_rmc' => $data['exp_rmc'] ?? $summary->exp_rmc,
                'total' => $data['total'] ?? $summary->total,
                'grace' => $data['grace'] ?? $summary->grace,
                'grace_per' => $data['grace_per'] ?? $summary->grace_per,
                'billing_amt' => $data['billing_amt'] ?? $summary->billing_amt,
                'avg_price' => $data['avg_price'] ?? $summary->avg_price,
                'total_weight' => $data['total_weight'] ?? $summary->total_weight,
                'empty_weight' => $data['empty_weight'] ?? $summary->empty_weight,
                'net_weight' => $data['net_weight'] ?? $summary->net_weight,
                'avg_per_weight' => $data['avg_per_weight'] ?? $summary->avg_per_weight,
                'shift_loss' => $data['shift_loss'] ?? $summary->shift_loss,
            ]);

            // Save only if there are changes
            if ($summary->isDirty()) {
                $summary->save();
            }

            if($data['type']=='completed'){

                $grace_piece = ($data['grace_new'] * $summary->filter_total);



                $stock_in = Stock_in::create([
                    'cat'      => 'filter',
                    'product_id'    => $summary->product_id,
                    'load_id'       => $summary->load_id,
                    'total_piece'   => $summary->filter_total,
                    'grace_piece'   => $grace_piece,
                    'grace_per'     => $data['grace_new'],
                    'bill_piece'    => $summary->filter_total - $grace_piece,
                    'price'         => $summary->filter_price,
                    'bill_amount'   => $summary->filter_amount,
                    'c_by'          => Auth::guard('tenant')->user()->id ?? null,
                ]);
            }

      
        return $summary;
   }

   // summary new

   public static function summary_new(array $data)
   {
         $load_id = $data['load_id'];
    
        $query = Load::with(['farmer_data:id,farm_en,location', 'product_data:id,name_en','load_data:id,load_seq,veh_no,team'])->where('load_id', $load_id)->orderBy('id', 'desc')->get();

        $query->map(function($item){
            // $item->load_piece = 0; // Access the appended attribute to load team members

              $item->team_members = $item->load_data->getTeamMembersAttribute();
              $item->table_name = 'e_load';

            return $item;
        });

        // $grouped = $query->groupBy(function ($item) {
        //     return ($item->cat === 'add' || $item->cat === 'stock') ? 'add' : 'load';
        // });

        // //  $get_load = $query->groupBy(function ($item) {
        // //     return $item->shift_id != null ? 'shift_from' : 'direct_add';
        // // });

       

        $shift = Shift::with(['load_data:id,load_seq', 'to_load:id,load_seq', 'party_data:id,party_en,party_location'])->where('load_id', $load_id)->get()
                ->map(function ($item) {
                        $item->table_name = 'e_shift';
                        return $item;
                    });

        $total_bill_piece  = ($query->sum('bill_piece'))-($shift->sum('bill_piece'));
        $total_grace       = ($query->sum('grace_piece'))-($shift->sum('grace_piece'));
        $total_bill_amount = ($query->sum('bill_amount'))-($shift->sum('bill_amount'));

        $total_piece = $total_bill_piece + $total_grace;

        $total_commision = ($query->sum('commission'));


        $shift_data = Shift::where('load_id', $load_id)->get();

        // $totalBillingAmount = $shift_data->sum('billing_amount');

        // $avg_rate = $shift_data->avg('price'); 

        // $total_piece += $shift_data->sum('total_piece');


         $summary = [
            'card_billing_piece'  => $total_bill_piece,
            'card_grace'          => $total_grace,
            'card_billing_amount' => $total_bill_amount,
            'card_total_piece'    => $total_piece,
            'card_total_commision'=> $total_commision,
            'shift_avg_rate'       => round($shift_data->avg('price'),2),
            'shift_billing_amount' => $shift_data->sum('bill_amount'),
            'shift_total_piece'      => $shift_data->sum('total_piece'),
            ];

        return $summary;

   }

  // function to add invoice

   public static function add_invoice(array $data)
   {
        $load_id = $data['load_id'];

        $load = Load::where('id', $load_id)->first();

        if(!$load){
            throw new \Exception('Load not found');
        }

        // $load->invoice_no = $data['invoice_no'] ?? $load->invoice_no;
        // $load->invoice_date = $data['invoice_date'] ?? $load->invoice_date;

        // $load->save();

        if(isset($data['file'])){
            $file = $data['file'];
            $fileName = time().'_'.$file->getClientOriginalName();

             // MOVE directly to public/invoices
            $file->move(public_path('invoices'), $fileName);

            $filePath = 'invoices/' . $fileName;

            // $filePath = $file->storeAs('invoices', $fileName, 'public'); 
        }

       $m_inv =  M_invoice::create([
            'load_id'       => $load_id,
            'ext_piece'    => $data['ext_piece'] ?? null,
            'grace_per'    => $data['grace_per'] ?? null,
            'price'        => $data['price'] ?? null,
            'charges'      => $data['charges'] ?? null,
            'description'  => $data['description'] ?? null,
            'file'         => $filePath ?? null,
            'product_profit'=> $data['product_profit'] ?? null,
            'loading'      => $data['loading'] ?? null,
            'commission'   => $data['commission'] ?? null,
            'final_loss'   => $data['final_loss'] ?? null,
            'profit_loss'  => $data['profit_loss'] ?? null,
            'status'       => 'active',
            'c_by'         => Auth::guard('tenant')->user()->id ?? null,
        ]);


        $m_inv->save();


        foreach($data['product_list'] as $pr){

            $e_inv =  E_invoice::create([
                'inv_id'     => $m_inv->id,
                'load_id'    => $load_id,
                'product'    => $pr['product'] ?? null,
                'total'      => $pr['total'] ?? null,
                'grace'      => $pr['grace'] ?? null,
                'price'      => $pr['price'] ?? null,
                'bill_amt'   => $pr['bill_amt'] ?? null,
                'status'     => 'active',
                'c_by'       => Auth::guard('tenant')->user()->id ?? null,
            ]);

            $e_inv->save();
        }

        return $m_inv;
   }

   // function to get invoice

//    public static function get_invoice(array $data)
//    {
//         $load_id = $data['load_id'];

//         $load = Load::where('id', $load_id)->first();

//         if(!$load){
//             throw new \Exception('Load not found');
//         }

//         return [
//             'invoice_no'   => $load->invoice_no,
//             'invoice_date' => $load->invoice_date,
//         ];
//    }
    
}
