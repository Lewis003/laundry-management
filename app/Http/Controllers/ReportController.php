<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Job;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->canViewRevenue(), 403, 'Unauthorized. Access to financial reports is restricted.');

        $period = $request->input('period', 'this_month');

        switch ($period) {
            case 'today':
                $start = Carbon::today()->startOfDay();
                $end   = Carbon::today()->endOfDay();
                $periodLabel = 'Today (' . $start->format('d M Y') . ')';
                break;
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end   = Carbon::now()->endOfWeek();
                $periodLabel = 'This Week (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')';
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end   = Carbon::now()->subMonth()->endOfMonth();
                $periodLabel = 'Last Month (' . $start->format('F Y') . ')';
                break;
            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end   = Carbon::now()->endOfYear();
                $periodLabel = 'This Year (' . $start->format('Y') . ')';
                break;
            case 'this_month':
            default:
                $period = 'this_month';
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfMonth();
                $periodLabel = 'This Month (' . $start->format('F Y') . ')';
                break;
        }

        // 1. Sales Revenue (Safe Calculation)
        $totalRevenue = $this->getRevenueForPeriod($start, $end);

        // 2. Expenses
        $totalExpenses = 0.0;
        $expenseBreakdown = collect();
        if (class_exists(Expense::class) && Schema::hasTable('expenses')) {
            $startDate = $start->copy()->startOfDay();
            $endDate   = $end->copy()->endOfDay();

            $expenseCents = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount_in_cents');
            $totalExpenses = round($expenseCents / 100, 2);

            $expenseBreakdown = Expense::selectRaw('category, SUM(amount_in_cents) as total_cents')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->groupBy('category')
                ->orderByDesc('total_cents')
                ->get()
                ->map(function ($item) {
                    return [
                        'raw_category' => $item->category,
                        'category'     => ucfirst($item->category),
                        'total'        => round($item->total_cents / 100, 2),
                    ];
                });
        }

        // 3. P&L Margin
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // 4. Orders Breakdown
        $ordersCount = Job::whereBetween('created_at', [$start, $end])->count();
        $completedOrders = 0;
        if (Schema::hasColumn('jobs', 'status')) {
            $completedOrders = Job::whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['ready', 'collected'])
                ->count();
        }

        return view('reports.index', compact(
            'period',
            'periodLabel',
            'totalRevenue',
            'totalExpenses',
            'netProfit',
            'profitMargin',
            'expenseBreakdown',
            'ordersCount',
            'completedOrders'
        ));
    }

    public function exportPnl(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->canViewRevenue(), 403, 'Unauthorized. Access to financial reports is restricted.');

        $fileName = 'safishwa_pnl_report_' . date('Y_m_d') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SAFISHWA NA TAI - P&L FINANCIAL STATEMENT']);
            fputcsv($handle, ['Generated At', Carbon::now()->toDateTimeString()]);
            fputcsv($handle, []);

            $rev = $this->getRevenueForPeriod(Carbon::createFromTimestamp(0), Carbon::now());
            $expCents = (class_exists(Expense::class) && Schema::hasTable('expenses')) ? Expense::sum('amount_in_cents') : 0;
            $exp = round($expCents / 100, 2);
            $net = $rev - $exp;

            fputcsv($handle, ['Metric', 'Amount (KSh)']);
            fputcsv($handle, ['Gross Revenue (M-Pesa)', number_format($rev, 2, '.', '')]);
            fputcsv($handle, ['Total Operational Expenses', number_format($exp, 2, '.', '')]);
            fputcsv($handle, ['Net Operating Profit', number_format($net, 2, '.', '')]);
            fputcsv($handle, []);

            if (class_exists(Expense::class) && Schema::hasTable('expenses')) {
                fputcsv($handle, ['EXPENSE CATEGORY BREAKDOWN']);
                fputcsv($handle, ['Category', 'Total Spend (KSh)']);
                $categories = Expense::selectRaw('category, SUM(amount_in_cents) as total')
                    ->groupBy('category')
                    ->get();
                foreach ($categories as $cat) {
                    fputcsv($handle, [ucfirst($cat->category), number_format($cat->total / 100, 2, '.', '')]);
                }
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function getRevenueForPeriod($start, $end): float
    {
        if (class_exists(Payment::class) && Schema::hasTable('payments')) {
            $amountCol = collect(['amount', 'amount_in_cents'])->first(fn($c) => Schema::hasColumn('payments', $c));
            if ($amountCol) {
                $paymentQuery = Payment::whereBetween('created_at', [$start, $end]);
                if (Schema::hasColumn('payments', 'status')) {
                    $paymentQuery->whereIn('status', ['completed', 'paid', 'successful']);
                }
                $sum = $paymentQuery->sum($amountCol);
                if ($sum > 0) {
                    return str_ends_with($amountCol, '_cents') ? round($sum / 100, 2) : round((float) $sum, 2);
                }
            }
        }

        $query = Job::whereBetween('created_at', [$start, $end]);
        if (Schema::hasColumn('jobs', 'payment_status')) {
            $query->where('payment_status', 'paid');
        } elseif (Schema::hasColumn('jobs', 'is_paid')) {
            $query->where('is_paid', 1);
        } elseif (Schema::hasColumn('jobs', 'paid_at')) {
            $query->whereNotNull('paid_at');
        }

        $priceColumn = collect(['total_price', 'price', 'total_amount', 'amount', 'price_in_cents'])
            ->first(fn($col) => Schema::hasColumn('jobs', $col));

        if ($priceColumn) {
            $sum = $query->sum($priceColumn);
            return str_ends_with($priceColumn, '_cents') ? round($sum / 100, 2) : round((float) $sum, 2);
        }

        $jobs = $query->get();
        return round((float) $jobs->sum(fn($j) => $j->total_paid ?? $j->total_price ?? $j->price ?? $j->amount ?? 0), 2);
    }
}
