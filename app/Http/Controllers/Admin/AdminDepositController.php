<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankDeposit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDepositController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'today');

        switch ($filter) {
            case 'week':
                $from = Carbon::now()->startOfWeek();
                $title = 'This Week';
                break;
            case 'month':
                $from = Carbon::now()->startOfMonth();
                $title = 'This Month';
                break;
            default:
                $from = Carbon::today();
                $title = 'Today';
        }

        $to = Carbon::now()->endOfDay();

        $deposits = BankDeposit::with(['depositor','recorder'])
            ->whereDate('deposit_date', '>=', $from->toDateString())
            ->whereDate('deposit_date', '<=', $to->toDateString())
            ->latest('deposit_date')
            ->paginate(20);

        $bankedTotal = $deposits->sum('amount');

        return view('admin.deposits.index', compact('deposits', 'title', 'bankedTotal', 'filter'));
    }
}
