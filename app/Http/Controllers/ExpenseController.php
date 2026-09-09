<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->canManageExpenses(), 403, 'Unauthorized. Access to expenses is restricted.');

        $query = Expense::query();

        // 1. Search by title or reference code
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_code', 'like', "%{$search}%");
            });
        }

        // 2. Category Filter
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // 3. Date Range Filter
        $dateFilter = $request->input('date_filter', 'this_month');
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('expense_date', Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate('expense_date', Carbon::yesterday());
                break;
            case 'this_week':
                $query->whereBetween('expense_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'last_month':
                $query->whereBetween('expense_date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
            case 'this_month':
            default:
                $query->whereBetween('expense_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(10)->withQueryString();

        // Metric calculations for current month
        $thisMonthTotalCents = Expense::whereBetween('expense_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('amount_in_cents');
        $thisMonthTotal = round($thisMonthTotalCents / 100, 2);

        $todayTotalCents = Expense::whereDate('expense_date', Carbon::today())->sum('amount_in_cents');
        $todayTotal = round($todayTotalCents / 100, 2);

        $topCategory = Expense::selectRaw('category, SUM(amount_in_cents) as total')
            ->whereBetween('expense_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        return view('expenses.index', compact('expenses', 'thisMonthTotal', 'todayTotal', 'topCategory', 'dateFilter'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canManageExpenses(), 403, 'Unauthorized. Access to expenses is restricted.');

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'category'       => 'required|in:water,electricity,salaries,supplies,maintenance,rent,other,utilities',
            'amount'         => 'required|numeric|min:1',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string',
            'reference_code' => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        Expense::create([
            'title'           => $validated['title'],
            'category'        => $validated['category'],
            'amount_in_cents' => (int) round($validated['amount'] * 100),
            'expense_date'    => $validated['expense_date'],
            'payment_method'  => $validated['payment_method'],
            'reference_code'  => $validated['reference_code'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'created_by'      => auth()->id(),
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function destroy(Expense $expense)
    {
        abort_unless(auth()->user()->canManageExpenses(), 403, 'Unauthorized. Access to expenses is restricted.');

        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense entry removed.');
    }

    public function bulkAction(Request $request)
    {
        abort_unless(auth()->user()->canManageExpenses(), 403, 'Unauthorized. Access to expenses is restricted.');

        $action = $request->input('bulk_action');
        $ids = $request->input('selected_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No expenses selected.');
        }

        if ($action === 'delete') {
            Expense::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' expense records removed.');
        }

        return back()->with('error', 'Invalid bulk action.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->canManageExpenses(), 403, 'Unauthorized. Access to expenses is restricted.');

        $fileName = 'safishwa_expenses_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Date', 'Expense Title', 'Category', 'Amount (KSh)', 'Payment Method', 'Reference Code', 'Notes']);

            $query = Expense::query();
            if ($category = $request->input('category')) {
                $query->where('category', $category);
            }

            $query->chunk(100, function ($expenses) use ($handle) {
                foreach ($expenses as $expense) {
                    fputcsv($handle, [
                        $expense->id,
                        $expense->expense_date->format('Y-m-d'),
                        $expense->title,
                        ucfirst($expense->category),
                        number_format($expense->amount, 2, '.', ''),
                        strtoupper($expense->payment_method),
                        $expense->reference_code ?? 'N/A',
                        $expense->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
