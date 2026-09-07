<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StaffController extends Controller
{
    /**
     * Display staff operations and activity tracker.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        if ($currentUser && ($currentUser->role === 'operator' || (method_exists($currentUser, 'isOperator') && $currentUser->isOperator()))) {
            abort(403, 'Wash bay operators are not authorized to view staff analytics.');
        }

        $completedStatuses = [
            'ready',
            'collected',
            'picked_up',
            'completed',
        ];

        $hasUserRelation = Schema::hasTable('jobs') && (
            Schema::hasColumn('jobs', 'user_id') ||
            Schema::hasColumn('jobs', 'operator_id') ||
            Schema::hasColumn('jobs', 'assigned_to')
        );

        if ($hasUserRelation) {
            $staffMembers = User::withCount([
                'assignedJobs as active_jobs_count' => function ($query) {
                    $query->where('status', 'in_progress');
                },
                'assignedJobs as completed_jobs_count' => function ($query) use ($completedStatuses) {
                    $query->whereIn('status', $completedStatuses);
                },
            ])->orderBy('name')->get();
        } else {
            $staffMembers = User::orderBy('name')->get()->map(function ($user) {
                $user->active_jobs_count = 0;
                $user->completed_jobs_count = 0;
                return $user;
            });
        }

        $users = $staffMembers;

        $overview = [
            'received'         => Job::where('status', 'received')->count(),
            'in_progress'      => Job::where('status', 'in_progress')->count(),
            'ready'            => Job::where('status', 'ready')->count(),
            'completed'        => Job::whereIn('status', $completedStatuses)->count(),
            'received_today'   => Job::whereDate('created_at', today())->where('status', 'received')->count(),
            'washing_now'      => Job::where('status', 'in_progress')->count(),
            'ready_for_pickup' => Job::where('status', 'ready')->count(),
            'completed_today'  => Job::whereDate('updated_at', today())->whereIn('status', $completedStatuses)->count(),
        ];
        $todayOverview = $overview;

        return view('staff.index', compact('staffMembers', 'users', 'overview', 'todayOverview'));
    }

    /**
     * Show the form to create a new staff member.
     */
    public function create()
    {
        $currentUser = Auth::user();

        if ($currentUser && method_exists($currentUser, 'isAdmin') && !$currentUser->isAdmin()) {
            abort(403, 'Only administrators can access staff registration.');
        }

        return view('staff.create');
    }

    /**
     * Store a new staff member (Admin only).
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if ($currentUser && method_exists($currentUser, 'isAdmin') && !$currentUser->isAdmin()) {
            abort(403, 'Only administrators can create staff accounts.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', 'string', 'in:admin,cashier,operator'],
        ]);

        $payload = [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $payload['role'] = $validated['role'];
        }

        $staff = User::create($payload);

        return redirect()->route('staff.index')->with('success', "Staff member '{$staff->name}' ({$staff->role}) registered successfully.");
    }

    /**
     * Update a staff member's role (Admin only).
     */
    public function updateRole(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser && method_exists($currentUser, 'isAdmin') && !$currentUser->isAdmin()) {
            abort(403, 'Only administrators can modify staff roles.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,cashier,operator'],
        ]);

        if (Schema::hasColumn('users', 'role')) {
            $user->update(['role' => $validated['role']]);
        }

        return back()->with('success', "Updated role for '{$user->name}' to " . ucfirst($validated['role']) . ".");
    }

    /**
     * Delete or deactivate a staff member.
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return back()->with('success', "Staff account removed successfully.");
    }
}
