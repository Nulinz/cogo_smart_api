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
use App\Models\Filter;
use App\Models\Petty_cash;
use App\Models\Farmer_cash;
use App\Models\Farmer;
use App\Models\Clear_stock;
use App\Services\Party_ser;
use App\Models\Kyc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Stock_ser
{
    public static function stock_home()
    {

        $stock_in  = Stock_in::with('product_data:id,name_en')->where('clear_status', 'not_clear')->get();
        $stock_out = Stock_out::where('clear_status', 'not_clear')->get();

        $stockOutByProduct = $stock_out->groupBy('product_id');

        $products = $stock_in
                    ->groupBy('product_id')
                    ->map(function ($items, $productId) use ($stockOutByProduct) {

                    // Stock IN totals
                    $in_billing = $items->sum('bill_piece');
                    $in_grace   = $items->sum('grace_piece');
                  

                    // Stock OUT totals (safe)
                    $out_items = $stockOutByProduct->get($productId, collect());

                    $out_billing = $out_items->sum('bill_piece');
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
                        'product_amount'      => round(($remaining_billing) * $avg_price),
                    ];
                })->values();

                $total_value = $products->sum('product_amount');

                 // ✅ CALL TRANSACTION LIST HERE
                $transactions = self::stock_transaction_list([]);

                // take only latest 5 transactions
                $latest_transactions = collect($transactions['stock_data'])
                    // ->sortByDesc('created_at')
                    ->take(5)
                    ->values();

           

            $stock_data = Clear_stock::all();

            $stock_sum_clear = $stock_data->sum(function($item){
                return ($item->bill_piece + $item->grace_piece);
            });

        return ['total_card' => $total_value,'products' => $products,'transactions' => $latest_transactions,'clear_stock_count' => $stock_sum_clear];  

    }

    // function to get the stock transsaction in and out 

    public static function stock_transaction_list(array $data)
    {
        $stock_in_query = Stock_in::with('product_data:id,name_en','farm_data:id,farm_en','load_data:id,load_seq')->orderBy('created_at','desc')->get()->map(function($item){
                $item->table = 'in';
                return $item;
        });

        $stock_out_query = Stock_out::with('product:id,name_en','party:id,party_en','load_data:id,load_seq')->orderBy('created_at','desc')->get()->map(function($item){
                $item->table = 'out';
                return $item;
        });

        $merge = $stock_in_query->concat($stock_out_query)->sortByDesc(function ($item) {
                     return Carbon::createFromFormat('d-m-Y H:i:s', $item->created_at);
                    })
                ->values();


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

                    'bill_piece'    => $item->bill_piece ?? 0,

                    'grace_piece'  => $item->grace_piece ?? 0,

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

        if(isset($data['load_id'])){

            $load_id = $data['load_id'];

            $load = Prime_load::where('id', $load_id)->first();

            if(!$load){
                throw new \Exception('Load not found');
            }

            $product_id = $load->product_id;

        }else{
            $product_id = $data['product_id'] ?? null;
        }

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
            'filter_billing'   => $data['filter_billing'] ?? null,
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

          if($data['type']=='completed'){

                $grace_piece = round(($data['grace_new']/100) * $summary->filter_total);

                $load_status_update = Prime_load::where('id', $summary->load_id)->first();
                $load_status_update->load_status = 'sum_completed';
                $load_status_update->save();

                // \Log::info('Creating Stock In for Filter Summary Completion: ', [
                //     'load_id' => $summary->load_id,
                //     'product_id' => $summary->product_id,
                //     'total_piece' => $summary->filter_total,
                //     'grace_piece' => $grace_piece,
                //     'grace_per'   => $data['grace_new'],
                //     'bill_piece'  => $summary->filter_total - $grace_piece,
                //     'price'       => $summary->filter_price,
                //     'bill_amount' => $summary->filter_amount,
                // ]);



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

      
     
        return $summmary;

   }
   

   // function to get load summary

   public static function get_load_summary(array $data)
   {
        // \Log::info('get_load_summary data: '. json_encode($data, JSON_PRETTY_PRINT));
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
    // Log::info('edit_load_summary data: '. json_encode($data, JSON_PRETTY_PRINT));
        $load_id = $data['load_id'];

        $summary = Summary::where('load_id', $load_id)->first();

        if(!$summary){
            throw new \Exception('Summary not found');
        }

         $summary->fill([
                'filter_total' => $data['filter_total'] ?? $summary->filter_total,
                'filter_billing' => $data['filter_billing'] ?? $summary->filter_billing,
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

                $grace_piece = round(($data['grace_new'] / 100) * $summary->filter_total);

                $load_status_update = Prime_load::where('id', $summary->load_id)->first();
                $load_status_update->load_status = 'sum_completed';
                $load_status_update->save();

                // \Log::info('Creating Stock In for Filter Summary Completion: ', [
                //     'grace_new' => $data['grace_new'],
                //     'load_id' => $summary->load_id,
                //     'product_id' => $summary->product_id,
                //     'total_piece' => $summary->filter_total,
                //     'grace_piece' => $grace_piece,
                //     'grace_per'   => $data['grace_new'],
                //     'bill_piece'  => $summary->filter_total - $grace_piece,
                //     'price'       => $summary->filter_price,
                //     'bill_amount' => $summary->filter_amount,
                // ]);



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
    
        $query = Load::with(['farmer_data:id,farm_en,location', 'product_data:id,name_en','load_data:id,load_seq,veh_no,team,party_id,empty_weight','load_data.party_data:id,party_en,party_location'])->where('load_id', $load_id)->orderBy('id', 'desc')->get();

        $query->map(function($item){
            // $item->load_piece = 0; // Access the appended attribute to load team members

              $item->team_members = $item->load_data->getTeamMembersAttribute();
              $item->table_name = 'e_load';

            return $item;
        });

        // Log::info('summary_new query: '. json_encode($query, JSON_PRETTY_PRINT));

        // dd($query);

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


        $prime_load = Prime_load::with(['truck_capacity:id,capacity,charge'])->where('id', $load_id)->first();

        $t_cap = $prime_load->truck_capacity()->first();

        


         $summary = [
            'card_billing_piece'  => $total_bill_piece,
            'card_grace'          => $total_grace,
            'card_billing_amount' => $total_bill_amount,
            'card_total_piece'    => $total_piece,
            'card_total_commision'=> $total_commision,
            'shift_data'       => $shift_data,
            // 'shift_billing_amount' => $shift_data->sum('bill_amount'),
            // 'shift_total_piece'      => $shift_data->sum('total_piece'),
            'party_data'          => $query->first()->load_data->party_data ?? null,
            'filter_piece'        => Filter::where('load_id', $load_id)->sum('total'),
            'load_empty_weight'   => $query->first()->load_data->empty_weight ?? null,
            'loading_charge'      => $t_cap->charge ?? null,
            ];

        return $summary;

   }

  // function to add invoice

   public static function add_invoice(array $data)
   {
    \Log::info('Adding Invoice Data: ', $data);
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


          $bill_piece = $pr['total'] - ($pr['total'] * ($pr['grace'] / 100));


           $stock_out = Stock_out::create([
                'cat'         => 'inv',
                'product_id'  => $pr['product'] ?? null,
                'load_id'     => $load_id,
                'total_piece' => $pr['total']+$pr['grace'] ?? null,
                'grace_piece' => $pr['grace'] ?? null,
                'bill_piece'  => $pr['total'],
                'price'       => $pr['price'] ?? null,
                'bill_amount' => $pr['bill_amt'] ?? null,
                'total_amt' =>   $pr['bill_amt'] ?? null,
                'c_by'        => Auth::guard('tenant')->user()->id ?? null,
            ]);

            // \Log::info('Creating e_inv: ', [' inv error'=>json_encode($e_inv->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)]);
            //  \Log::info("Creating Stock Out:\n" ,['stock error'=> json_encode($stock_out->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)]);


         

            $stock_out->save();
        }

        
          // ✅ Update Prime Load status ONLY AFTER invoice success
        Prime_load::where('id', $load_id)->update(['load_status' => 'inv_completed']);
        
        return $m_inv;
   }

   // function to add petty cash

   public static function add_petty(array $data)
   {
       

        // Create petty cash entry
        $petty_cash = Petty_cash::create([
            'emp_id'      => $data['emp_id'],
            'type'        => $data['type'],
            'amount'      => $data['amount'],
            'method'      => $data['method'],
            'date'        => $data['date'],
            'c_by'        => Auth::guard('tenant')->user()->id ?? null,
        ]);

        return $petty_cash;
   }

   // function to get petty cash individual

   public static function petty_cash_ind(array $data)
   {
        $emp_id = $data['emp_id'] ?? null;

        if(!$emp_id){
            throw new \Exception('Employee ID is required');
        }

        $petty_list = Petty_cash::where('emp_id', $emp_id)->get()->map(function($item){
                $item->table = 'petty';
                return $item;
        });

        $date = now()->toDateString();

        $petty_cash_given = $petty_list->where('type','petty')->sum('amount');
        $petty_cash_settle  = $petty_list->where('type','settle')->sum('amount');

        $farmer_adv = Farmer::where('c_by', $emp_id)->pluck('adv_prime')->filter();

        $farmer_paid_list = Farmer_cash::with(['farm_data:id,farm_en,location'])->whereNotIn('id', $farmer_adv)->where('c_by', $emp_id);
        
        if(isset($data['limit']) && is_int($data['limit'])){
            $farmer_paid_list = $farmer_paid_list->limit($data['limit']);
        }
        $farmer_paid_list = $farmer_paid_list->orderBy('id','desc')
        ->get()->map(function($item){
                $item->table = 'farmer_cash';
                return $item;
        });

        $farmer_today_cash_spent = Farmer_cash::where('c_by', $emp_id)
            ->whereNotIn('id', $farmer_adv)
            ->whereDate('created_at', today())
            ->sum('amount');

        $today_petty_given = Petty_cash::where('emp_id', $emp_id)
            ->whereDate('date', today())
            ->where('type','petty')
            ->sum('amount');

            // \Log::info('Today petty given: '. $today_petty_given);
            // \Log::info('Today employee: '. $emp_id);

        $today_petty_settle = Petty_cash::where('emp_id', $emp_id)
            ->whereDate('date', today())
            ->where('type', 'settle')
            ->sum('amount');

        // $overall_list = $petty_list->concat($farmer_paid_list)->sortByDesc(function ($item) {
        //         return Carbon::createFromFormat('d-m-Y H:i:s', $item->created_at);
        //     })->values();

        // \Log::info('petty cash given: '. $petty_cash_given);
        // \Log::info('petty cash settle: '. $petty_cash_settle);
        // \Log::info('farmer paid sum: '. $farmer_paid_list->sum('amount'));

        // \Log::info('Balance Calculation: ('.$today_petty_given.' - '.$today_petty_settle.') - '.$farmer_today_cash_spent.')');
        


        $balance = ($petty_cash_given - $petty_cash_settle) - $farmer_paid_list->sum('amount');


        return ['cash_given'=>($today_petty_given),'cash_used'=>$farmer_today_cash_spent,'balance' => $balance, 'list' => $farmer_paid_list];
   }

   // function to get petty cash individual view all

   public static function petty_cash_ind_transaction(array $data)
   {
         $emp_id = $data['emp_id'] ?? null;
        // $user_paid_list = Farmer_cash::where('c_by', $data['emp_id'])->orderBy('id','desc')->get();

         $petty_list = Petty_cash::where('emp_id', $emp_id)->orderBy('created_at','desc')->get()->map(function($item){
                $item->table = 'petty';
                return $item;
        });

        return $petty_list;
   }

   // function to update invoice

   public static function update_loss_invoice(array $data)
   {

        $load_id = $data['load_id'];

        $m_inv = M_invoice::where('load_id', $load_id)->first();

        // \Log::info('update_loss_invoice data: '. json_encode($data, JSON_PRETTY_PRINT));

        if(!$m_inv){
            throw new \Exception('Invoice not found');
        }

        

        $final =[ 
            'type'   => $data['final_loss_type'] ?? null,
            'amount' => $data['final_loss_amount'] ?? null,
            'piece'  => $data['final_loss_piece'] ?? null,
        ];  
        $m_inv->fill([
            'final_loss'   => $final,
            'profit_loss'  => $data['profit_loss'] ?? $m_inv->profit_loss,
        ]);

        // Save only if there are changes
        if ($m_inv->isDirty()) {
            $m_inv->save();
        }

        return $m_inv;
   }

   public static function get_invoice(array $data)
   {
        $load_id = $data['load_id'];

        $invoice = M_invoice::where('load_id', $load_id)->exists() ? M_invoice::where('load_id', $load_id)->with(['invoice_items','load_data','invoice_items.product_data:id,name_en'])->orderby('id','desc')->first() : null;

        // $inv_load_charge = collect($invoice->charges ?? []);

       $invoice->inv_loading_charge += collect($invoice->charges ?? [])->sum('amt');


        $invoice->exists_check = M_invoice::where('load_id', $load_id)->exists() ? true : false;

        if(!$invoice){
            throw new \Exception('Invoice not found');
        }

        $prime_load = Prime_load::with(['party_data:id,party_en,party_location,com_name,com_add'])->where('id', $load_id)->first();

        $party_bal = Party_ser::party_profile(['party_id' => $prime_load->party_id]);

        $prime_load->party_balance = $party_bal['data']['balance'] ?? 0;

        $trader_kyc = Kyc::where('user_id', Auth('tenant')->user()->id ?? null)->first();

        return ['invoice' => $invoice, 'prime_load' => $prime_load,'trader_kyc' => $trader_kyc];
   }

   // function to generate invoice pdf

   public static function invoice_pdf(array $data)
   {

        if(($data['type']==='invoice')){
            
        //    $inv_data = Self::get_invoice($data);
          $inv_data =  M_invoice::where('id', $data['load_id'])->with(['invoice_items','load_data','invoice_items.product_data:id,name_en','load_data.party_data:id,party_en,party_location'])->get();

        }else if(($data['type']=='sales')){
            // get e invoice data
            $inv_data = Stock_out::with(['product:id,name_en'])->where('id', $data['load_id'])->get();
        }else{
            $inv_data = Shift::with(['product_data:id,name_en'])->where('id', $data['load_id'])->get();
        }

        return $inv_data;




        // $load_id = $data['load_id'];

        // $invoice = M_invoice::where('load_id', $load_id)->with(['invoice_items','load_data','invoice_items.product_data:id,name_en','load_data.party_data:id,party_en,party_location'])->orderby('id','desc')->first();

        // if(!$invoice){
        //     throw new \Exception('Invoice not found');
        // }

        // return $invoice;
   }

   // function to clear stock

   public static function clear_stock(array $data)
   {
        // Delete all stock in and stock out records

        // \Log::info('Clearing stock for product ID: ',['data' => $data]);

        $product_id = (int)($data['product_id'] ?? null);
        
        try{
            DB::beginTransaction();
                $stock_in_update = Stock_in::where('product_id', $product_id)->where('clear_status', '=', 'not_clear')->update(['clear_status' => 'clear']);
                $stock_out_update = Stock_out::where('product_id', $product_id)->where('clear_status', '=', 'not_clear')->update(['clear_status' => 'clear']);

                if($stock_in_update === 0 && $stock_out_update === 0){
                    throw new \Exception('No active stock records found for the specified product');
                }


                $stock_clear = clear_stock::create([
                    'product_id' => $data['product_id'] ?? null,
                    'bill_piece' => $data['bill_piece'] ?? null,
                    'grace_piece' => $data['grace_piece'] ?? null,
                    'avg_price'  => $data['avg_price'] ?? null,
                    'total_amt'  => $data['total_amt'] ?? null,
                    'status'     => 'active',
                    'c_by'       => Auth::guard('tenant')->user()->id ?? null,
                ]);

                DB::commit();

                return ['message' => 'Stock cleared successfully'];

        }catch (\Exception $e) {
                DB::rollBack();
                return ['error' => 'Failed to clear stock: ' . $e->getMessage()];
        }
            
   }
    
}
