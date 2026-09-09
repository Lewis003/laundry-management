<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Job;
use App\Models\Machine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = Carbon::now()->year;

        // 1. Registered Employees Count
        $registeredEmployees = User::count();

        // 2. Customers Count (Safe table or distinct check)
        if (class_exists(Customer::class) && Schema::hasTable('customers')) {
            $customersCount = Customer::count();
        } elseif (Schema::hasColumn('jobs', 'customer_id')) {
            $customersCount = Job::distinct('customer_id')->count('customer_id');
        } else {
            $customersCount = Job::count();
        }

        // 3. Transactions Volume
        $totalTransactions = Job::count();
        $pendingTransactions = 0;
        if (Schema::hasColumn('jobs', 'status')) {
            $pendingTransactions = Job::whereNotIn('status', ['ready', 'collected'])->count();
        }

        // 4. Financials (This Year)
        // Income
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear   = Carbon::now()->endOfYear();
        $incomeThisYear = $this->getRevenueForPeriod($startOfYear, $endOfYear);

        // Expenditure
        $expenditureThisYear = 0.0;
        if (class_exists(Expense::class) && Schema::hasTable('expenses')) {
            $expCents = Expense::whereBetween('expense_date', [
                $startOfYear->toDateString(),
                $endOfYear->toDateString()
            ])->sum('amount_in_cents');
            $expenditureThisYear = round($expCents / 100, 2);
        }

        // Profit / Loss
        $profitThisYear = $incomeThisYear - $expenditureThisYear;

        // 5. Operational Queue & Equipment Stats
        $readyOrdersCount = Schema::hasColumn('jobs', 'status') ? Job::where('status', 'ready')->count() : 0;
        $todayOrdersCount = Job::whereDate('created_at', Carbon::today())->count();

        $totalMachines = class_exists(Machine::class) && Schema::hasTable('machines') ? Machine::count() : 0;
        $availableMachines = class_exists(Machine::class) && Schema::hasTable('machines')
            ? Machine::where(function($q) { $q->where('status', 'available')->orWhere('is_available', true); })->where('is_active', true)->count()
            : 0;
        $inUseMachines = class_exists(Machine::class) && Schema::hasTable('machines') ? Machine::where('status', 'in_use')->count() : 0;
        $maintenanceMachines = class_exists(Machine::class) && Schema::hasTable('machines')
            ? Machine::where('status', 'maintenance')->orWhere('is_active', false)->count()
            : 0;

        return view('dashboard', compact(
            'registeredEmployees',
            'customersCount',
            'totalTransactions',
            'pendingTransactions',
            'readyOrdersCount',
            'todayOrdersCount',
            'totalMachines',
            'availableMachines',
            'inUseMachines',
            'maintenanceMachines',
            'incomeThisYear',
            'expenditureThisYear',
            'profitThisYear'
        ));
    }

    private function getRevenueForPeriod($start, $end): float
    {
        $query = Job::whereBetween('created_at', [$start, $end]);

        if (Schema::hasColumn('jobs', 'payment_status')) {
            $query->where('payment_status', 'paid');
        } elseif (Schema::hasColumn('jobs', 'is_paid')) {
            $query->where('is_paid', 1);
        }

        $priceCol = collect(['total_price', 'price', 'total_amount', 'amount', 'price_in_cents'])
            ->first(fn($c) => Schema::hasColumn('jobs', $c));

        if ($priceCol) {
            $sum = $query->sum($priceCol);
            return str_ends_with($priceCol, '_cents') ? round($sum / 100, 2) : round((float) $sum, 2);
        }

        $jobs = $query->get();
        return round((float) $jobs->sum(fn($j) => $j->total_price ?? $j->price ?? $j->amount ?? 0), 2);
    }
}
