<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShopSettingController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Safishwa na Tai Commercial Laundry POS
|--------------------------------------------------------------------------
*/

// Root redirect to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public Tracking Route (Resolves route('track') for login page and customers)
Route::get('/track/{ticket?}', function (\Illuminate\Http\Request $request, $ticket = null) {
    if (view()->exists('track')) {
        $ticketNumber = $ticket ?: $request->query('ticket');
        $job = null;
        $searched = false;
        if ($ticketNumber) {
            $searched = true;
            $job = \App\Models\Job::with(['customer', 'items.service', 'machine'])
                ->where('job_number', $ticketNumber)
                ->first();
        }
        return view('track', ['ticket' => $ticketNumber, 'job' => $job, 'searched' => $searched]);
    }
    return redirect()->route('login');
})->name('track');

// Authenticated Staff & Admin Area
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Customers
    Route::resource('customers', CustomerController::class);

    // 3. Orders / Jobs
    Route::get('/jobs/export', [JobController::class, 'export'])->name('jobs.export');
    Route::post('/jobs/bulk-action', [JobController::class, 'bulkAction'])->name('jobs.bulk-action');
    Route::get('/jobs/{job}/receipt', [JobController::class, 'receipt'])->name('jobs.receipt');
    Route::post('/jobs/{job}/assign-machine', [JobController::class, 'assignMachine'])->name('jobs.assign-machine');
    Route::post('/jobs/{job}/mark-ready', [JobController::class, 'markReady'])->name('jobs.mark-ready');
    Route::post('/jobs/{job}/mark-collected', [JobController::class, 'markCollected'])->name('jobs.mark-collected');
    Route::post('/jobs/{job}/collect-payment', [JobController::class, 'collectPayment'])->name('jobs.collect-payment');
    Route::resource('jobs', JobController::class);
    Route::get('/orders', [JobController::class, 'index'])->name('orders.index');

    // 4. Services
    Route::resource('services', ServiceController::class);

    // 5. Machines Fleet
    Route::get('/machines/export', [MachineController::class, 'export'])->name('machines.export');
    Route::post('/machines/{machine}/assign', [MachineController::class, 'assign'])->name('machines.assign');
    Route::post('/machines/{machine}/release', [MachineController::class, 'release'])->name('machines.release');
    Route::resource('machines', MachineController::class);

    // 6. Expenses / Expenditure
    Route::get('/expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    Route::post('/expenses/bulk-action', [ExpenseController::class, 'bulkAction'])->name('expenses.bulk-action');
    Route::resource('expenses', ExpenseController::class);

    // 7. Reports & P&L
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pnl', [ReportController::class, 'exportPnl'])->name('reports.export-pnl');

    // 8. Staff Roles & Permissions Matrix
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::post('/staff/{user}/toggle-admin', [StaffController::class, 'toggleAdmin'])->name('staff.toggle-admin');
    Route::post('/staff/{user}/update-role', [StaffController::class, 'updateRole'])->name('staff.update-role');
    Route::post('/staff/{user}/toggle-permission', [StaffController::class, 'togglePermission'])->name('staff.toggle-permission');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // Role Definition & Matrix Management
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // 9. Unified Settings Console & Hierarchical Sub-modules
    // Settings ➔ Shop & Location
    Route::get('/settings', [ShopSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [ShopSettingController::class, 'update'])->name('settings.update');

    // Settings ➔ Role Management
    Route::get('/settings/roles', [RoleController::class, 'index'])->name('settings.roles');

    // Settings ➔ User Roles / Permissions
    Route::get('/settings/permissions', [StaffController::class, 'permissions'])->name('settings.permissions');

    // Settings ➔ Staff / Team Management
    Route::get('/settings/staff', [StaffController::class, 'index'])->name('settings.staff');

    // Profile Settings
    if (class_exists(ProfileController::class)) {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    }
});

// Breeze Auth Routes
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
