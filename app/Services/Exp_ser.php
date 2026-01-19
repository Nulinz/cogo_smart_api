<?php

namespace App\Services;

use App\Models\Expense_cat;
use App\Models\Expense;
use App\Models\E_expense;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Exp_ser
{
   
    // function to create expenses
    public static function create_expense(array $data){


      $user = Auth::guard('tenant')->user();

        $status = ($user && $user->role === 'admin')
            ? 'approved'
            : 'pending';

       
        $exp = Expense::create([
            'title' => $data['title'],
            'exp_cat'    => $data['exp_cat'],
            'amount'  => $data['amount'],
            'notes'  => $data['notes'],
            'status'    => $status, 
            'c_by'    => Auth::guard('tenant')->user()->id ?? null,
        ]);
        

       return $exp;
    }

    // function to get list of created expenses
    public static function expense_created_list(array $data){

        $expenses = Expense::where('c_by', $data['emp_id'])->get();

        return $expenses;

    }   

    // function to pay out expense

    public static function expense_pay_out(array $data){

        $expense = E_expense::create([
            'emp_id' => $data['emp_id'],
            'amount' => $data['amount'],
            'method'    => $data['pay_method'],
            'c_by'    => Auth::guard('tenant')->user()->id ?? null,
        ]);

        return $expense;

    }

    public static function expense_home(){

        $expense = Expense::with(['exp_category:id,cat','exp_cby:id,name'])->whereDate('created_at', today())->where('status','approved')->get();

        // dd($expense->toArray());

       $exp_group = $expense
                    ->groupBy('exp_cat')
                    ->mapWithKeys(function ($group) {
                        return [
                            ($group->first()->exp_category->cat ?? 'Uncategorized')
                                => $group->sum('amount')
                        ];
                    });

        $exp_list = Expense::with(['exp_category:id,cat','exp_cby:id,name'])->whereDate('created_at', today())->orderBy('created_at', 'desc')->where('status','pending')->get();

        // \Log::info("Expense Home Data", ['exp_group' => $exp_list, 'total_expense' => $expense->sum('amount')]);
       
        return ['exp_group' => $exp_group, 'exp_list' => $exp_list, 'total_expense' => $expense->sum('amount')];

    }

    // function to get expense of the week

    public static function expense_week(array $data){

        $type = $data['type'] ?? 'week';

        $now  = Carbon::now();

        // \Log::info("Expense Type Requested: $type");

    switch ($type) {
        case 'week':
            $startDate = $now->copy()->startOfWeek();
            $endDate   = $now->copy()->endOfWeek();
            break;

        case 'month':
            $startDate = $now->copy()->startOfMonth();
            $endDate   = $now->copy()->endOfMonth();
            break;

        case 'year':
            $startDate = $now->copy()->startOfYear();
            $endDate   = $now->copy()->endOfYear();
            break;

        case 'three_month':
            $startDate = $now->copy()->subMonths(3)->startOfMonth();
            $endDate   = $now->copy()->endOfMonth();
            break;

        case 'six_month':
            $startDate = $now->copy()->subMonths(6)->startOfMonth();
            $endDate   = $now->copy()->endOfMonth();
            break;

        default:
            // fallback to week
            $startDate = $now->copy()->startOfWeek();
            $endDate   = $now->copy()->endOfWeek();
            break;}   
      
        $expense = Expense::with(['exp_category:id,cat'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status','approved')
                    ->get();

        $exp_group = $expense
                    ->groupBy('exp_cat')
                    ->mapWithKeys(function ($group) {
                        return [
                            ($group->first()->exp_category->cat ?? 'Uncategorized')
                                => $group->sum('amount')
                        ];
                    });

        // $exp_list = Expense::with(['exp_category:id,cat'])
        //             ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
        //             ->orderBy('created_at', 'desc')
        //             ->where('status','pending')
        //             ->get();
       
        return ['exp_group' => $exp_group,'total_expense' => $expense->sum('amount')];

    }

    // function to get expense emp profile

    public static function expense_emp_profile(array $data){

        $emp_id = $data['emp_id'];

        $user_profile = User::where('id', $emp_id)->select('id','name')->first();

        $exp_approve = Expense::with(['exp_category:id,cat'])->where('c_by', $emp_id)->get()->map(function ($item) {
            $item->table = 'expense';
            return $item;
        });

        $exp_transaction = E_expense::where('emp_id', $emp_id)->orderBy('created_at', 'desc')->get()->map(function ($item) {
            $item->table = 'e_expense';
            return $item;
        });

        $exp_balance = ($exp_approve->where('status','approved')->sum('amount') - $exp_transaction->sum('amount'));

        $exp_pending = $exp_approve->where('status','pending')->sum('amount');

        $exp_data = $exp_approve->concat($exp_transaction)->sortByDesc('created_at')->values();

        $exp_out = $exp_transaction->sum('amount');

        return [
                'user_profile' => $user_profile,
                'exp_balance' => $exp_balance,
                'exp_pending' => $exp_pending,
                'exp_transaction' => $exp_data
            ];

    }

}
