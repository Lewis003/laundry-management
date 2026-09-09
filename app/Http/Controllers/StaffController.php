<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->canManageStaff(), 403, 'Unauthorized. Staff management is restricted to Administrators and Managers.');

        $roles = Role::withCount('users')->orderBy('id', 'asc')->get();
        if ($roles->isEmpty()) {
            $defaultRoles = [
                ['name' => 'admin', 'display_name' => 'Admin / Owner', 'access_level' => 'Full Access', 'primary_actions' => ['user:manage', 'invoice:void', 'refund:issue', 'audit-log:view'], 'is_system' => true],
                ['name' => 'manager', 'display_name' => 'Store Manager', 'access_level' => 'Operational Authority', 'primary_actions' => ['price:override', 'rewash:approve', 'route:assign', 'report:export'], 'is_system' => true],
                ['name' => 'cashier', 'display_name' => 'Front Desk Cashier', 'access_level' => 'Customer Facing', 'primary_actions' => ['order:create', 'order:edit', 'payment:collect', 'tag:generate'], 'is_system' => true],
                ['name' => 'operator', 'display_name' => 'Laundry Operator', 'access_level' => 'Back-end Processing', 'primary_actions' => ['status:update', 'weight:log', 'machine:allocate'], 'is_system' => true],
                ['name' => 'rider', 'display_name' => 'Delivery Rider', 'access_level' => 'Logistics Only', 'primary_actions' => ['delivery:confirm', 'bag:audit'], 'is_system' => true],
            ];
            foreach ($defaultRoles as $r) {
                Role::create($r);
            }
            $roles = Role::withCount('users')->orderBy('id', 'asc')->get();
        }

        $staff = User::with('roleDefinition')->orderBy('name', 'asc')->get();
        $users = $staff;

        if ($request->routeIs('settings.staff')) {
            return view('settings.staff', compact('staff', 'users', 'roles'));
        }

        return view('staff.index', compact('staff', 'users', 'roles'));
    }

    public function permissions(): View
    {
        abort_unless(auth()->user()->canManageStaff(), 403, 'Unauthorized. Staff permissions management is restricted to Administrators and Managers.');

        $roles = Role::withCount('users')->orderBy('id', 'asc')->get();
        $staff = User::with('roleDefinition')->orderBy('name', 'asc')->get();
        $users = $staff;

        return view('settings.permissions', compact('staff', 'users', 'roles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canManageStaff(), 403, 'Unauthorized. Staff management is restricted to Administrators and Managers.');

        $validRoles = Role::pluck('name')->toArray();
        if (empty($validRoles)) {
            $validRoles = ['admin', 'manager', 'cashier', 'operator', 'rider'];
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role'     => ['required', 'string', 'in:' . implode(',', $validRoles)],
        ]);

        $isAdmin = ($validated['role'] === 'admin');

        $defaultPermissions = [
            'view_revenue'    => in_array($validated['role'], ['admin', 'manager']),
            'manage_expenses' => in_array($validated['role'], ['admin', 'manager']),
            'manage_machines' => in_array($validated['role'], ['admin', 'manager', 'operator']),
            'delete_orders'   => in_array($validated['role'], ['admin']),
        ];

        User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
            'is_admin'    => $isAdmin,
            'permissions' => $defaultPermissions,
        ]);

        return redirect()->route('staff.index')->with('success', "Staff member {$validated['name']} registered successfully.");
    }

    public function toggleAdmin(User $user)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators can alter administrative privileges.');

        if ($user->id === auth()->id() && $user->is_admin) {
            return back()->with('error', 'You cannot remove your own admin status.');
        }

        $user->is_admin = !$user->is_admin;
        if ($user->is_admin) {
            $user->role = 'admin';
        }
        $user->save();

        $status = $user->is_admin ? 'granted Admin status' : 'revoked Admin status';
        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    public function updateRole(Request $request, User $user)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators can assign staff roles.');

        $validRoles = Role::pluck('name')->toArray();
        if (empty($validRoles)) {
            $validRoles = ['admin', 'manager', 'cashier', 'operator', 'rider'];
        }

        $role = $request->input('role');
        if (!in_array($role, $validRoles)) {
            return back()->with('error', 'Invalid role selected.');
        }

        $user->role = $role;
        $user->is_admin = ($role === 'admin');
        $user->save();

        return back()->with('success', "Updated {$user->name}'s role to " . ucfirst($role) . '.');
    }

    public function togglePermission(Request $request, User $user)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators can toggle granular permissions.');

        $permission = $request->input('permission');
        $allowed = ['view_revenue', 'manage_expenses', 'manage_machines', 'delete_orders'];

        if (!in_array($permission, $allowed)) {
            return back()->with('error', 'Invalid permission type.');
        }

        $perms = $user->permissions ?? [];
        $currentState = !empty($perms[$permission]);
        $perms[$permission] = !$currentState;

        $user->permissions = $perms;
        $user->save();

        $stateText = $perms[$permission] ? 'ENABLED' : 'DISABLED';
        return back()->with('success', "Permission [{$permission}] {$stateText} for {$user->name}.");
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators can delete staff accounts.');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('staff.index')->with('success', "Staff member {$name} removed.");
    }
}
