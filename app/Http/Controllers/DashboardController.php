<?php

namespace App\Http\Controllers;

use App\Enums\JobStatus;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Financial KPIs (Safe fallbacks)
        $totalRevenueCents = (int) (Payment::sum('amount_in_cents') ?? 0);
        $totalOrdersCount = Job::count();
        $activeOrdersCount = Job::whereIn('status', [JobStatus::RECEIVED, JobStatus::IN_PROGRESS])->count();

        $avgOrderValueCents = $totalOrdersCount > 0 ? (int) round($totalRevenueCents / $totalOrdersCount) : 0;

        // 2. Revenue Breakdown by Category
        $categoryBreakdown = DB::table('job_items')
            ->join('services', 'job_items.service_id', '=', 'services.id')
            ->select(
                'services.id',
                'services.name',
                DB::raw('COALESCE(SUM(job_items.quantity * job_items.price_in_cents), 0) as total_cents'),
                DB::raw('COUNT(*) as total_items')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_cents')
            ->limit(5)
            ->get();

        // 3. Weekly Order Volume (Last 7 Days)
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->format('D');
            $count = Job::whereDate('created_at', $date->toDateString())->count();
            $days->push([
                'day' => $dayName,
                'count' => $count,
                'is_today' => $i === 0
            ]);
        }

        // 4. Live Machine Capacity with eager loaded relations
        $machines = Machine::with(['currentJob.customer'])->get();

        // 5. Recent Active Orders
        $recentJobs = Job::with(['customer', 'machine', 'assignedWorker'])
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'totalRevenueCents',
            'totalOrdersCount',
            'activeOrdersCount',
            'avgOrderValueCents',
            'categoryBreakdown',
            'days',
            'machines',
            'recentJobs'
        ));
    }
}
