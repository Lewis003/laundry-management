<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display the Role-Based Access Control matrix.
     */
    public function index(): View
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isManager(), 403, 'Only Administrators and Managers can access Role Management.');

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

        return view('settings.roles', compact('roles'));
    }

    /**
     * Store a new custom role.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators can configure roles.');

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:50', 'alpha_dash', 'unique:roles,name'],
            'display_name'    => ['required', 'string', 'max:255'],
            'access_level'    => ['required', 'string', 'max:255'],
            'primary_actions' => ['required'],
            'description'     => ['nullable', 'string', 'max:1000'],
        ]);

        $actions = is_array($validated['primary_actions'])
            ? $validated['primary_actions']
            : array_values(array_filter(array_map('trim', explode(',', (string) $validated['primary_actions']))));

        $role = Role::create([
            'name'            => Str::slug($validated['name'], '_'),
            'display_name'    => $validated['display_name'],
            'access_level'    => $validated['access_level'],
            'primary_actions' => $actions,
            'description'     => $validated['description'] ?? null,
            'is_system'       => false,
        ]);

        return back()->with('success', "Role '{$role->display_name}' created successfully.");
    }

    /**
     * Update an existing role definition.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators can update roles.');

        $validated = $request->validate([
            'display_name'    => ['required', 'string', 'max:255'],
            'access_level'    => ['required', 'string', 'max:255'],
            'primary_actions' => ['required'],
            'description'     => ['nullable', 'string', 'max:1000'],
        ]);

        $actions = is_array($validated['primary_actions'])
            ? $validated['primary_actions']
            : array_values(array_filter(array_map('trim', explode(',', (string) $validated['primary_actions']))));

        $role->update([
            'display_name'    => $validated['display_name'],
            'access_level'    => $validated['access_level'],
            'primary_actions' => $actions,
            'description'     => $validated['description'] ?? null,
        ]);

        return back()->with('success', "Role '{$role->display_name}' updated successfully.");
    }

    /**
     * Delete a custom role (protected against deleting system roles or roles with assigned users).
     */
    public function destroy(Role $role): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators can delete roles.');

        if ($role->is_system) {
            return back()->with('error', 'Core system roles cannot be deleted.');
        }

        $assignedCount = $role->users()->count();
        if ($assignedCount > 0) {
            return back()->with('error', "Cannot delete role '{$role->display_name}' because {$assignedCount} staff member(s) are assigned to it.");
        }

        $roleName = $role->display_name;
        $role->delete();

        return back()->with('success', "Role '{$roleName}' has been deleted.");
    }
}

