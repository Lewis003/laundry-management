<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->canManageCustomers(), 403, 'Wash bay operators are not authorized to access customers.');

        $filters = $request->only(['search', 'sort']);
        $customers = $this->customerService->getFilteredCustomers($filters);

        return view('customers.index', compact('customers', 'filters'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->canManageCustomers(), 403, 'Wash bay operators are not authorized to create customers.');

        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->canManageCustomers(), 403, 'Wash bay operators are not authorized to create customers.');

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = $this->customerService->createCustomer($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', "Customer {$customer->name} registered successfully.");
    }

    public function show(Customer $customer): View
    {
        abort_unless(auth()->user()->canManageCustomers(), 403, 'Laundry operators cannot view customer profiles.');

        $customer->load(['orders.payments', 'orders.items.service']);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        abort_unless(auth()->user()->canManageCustomers(), 403, 'Wash bay operators are not authorized to edit customers.');

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless(auth()->user()->canManageCustomers(), 403, 'Wash bay operators are not authorized to update customers.');

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->customerService->updateCustomer($customer, $validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer profile updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        abort_unless(auth()->user()->canManageCustomers(), 403, 'Wash bay operators are not authorized to delete customers.');

        try {
            $this->customerService->deleteCustomer($customer);
            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer record deleted.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
