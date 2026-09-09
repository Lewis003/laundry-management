<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    /**
     * Retrieve paginated customers with GET-based query filters using ->when().
     */
    public function getFilteredCustomers(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->withCount('orders')
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['sort']), function ($query) use ($filters) {
                if ($filters['sort'] === 'name_asc') {
                    $query->orderBy('name', 'asc');
                } elseif ($filters['sort'] === 'orders_desc') {
                    $query->orderBy('orders_count', 'desc');
                } else {
                    $query->latest();
                }
            }, function ($query) {
                $query->latest();
            })
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new customer with sanitized phone.
     */
    public function createCustomer(array $data): Customer
    {
        $data['phone'] = trim($data['phone']);
        return Customer::create($data);
    }

    /**
     * Update an existing customer profile.
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $data['phone'] = trim($data['phone']);
        $customer->update($data);
        return $customer->fresh();
    }

    /**
     * Delete a customer record (only if no existing orders exist).
     */
    public function deleteCustomer(Customer $customer): void
    {
        if ($customer->orders()->exists()) {
            throw new \DomainException('Cannot delete customer with existing order history.');
        }

        $customer->delete();
    }
}
