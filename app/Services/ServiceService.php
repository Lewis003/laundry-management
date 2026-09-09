<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceService
{
    /**
     * Retrieve paginated services with GET-based query filters using ->when().
     */
    public function getFilteredServices(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Service::query()
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['category']), function ($query) use ($filters) {
                $query->where('category', $filters['category']);
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->orderBy('category', 'asc')
            ->orderBy('price_cents', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a service converting monetary input to pure integer cents.
     */
    public function createService(array $data): Service
    {
        $priceCents = isset($data['price_cents'])
            ? (int) $data['price_cents']
            : (int) round(((float) $data['price']) * 100);

        return Service::create([
            'name'             => trim($data['name']),
            'price_cents'      => $priceCents,
            'price_in_cents'   => $priceCents,
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'category'         => $data['category'] ?? 'wash_fold',
            'is_active'        => $data['is_active'] ?? true,
            'description'      => $data['description'] ?? null,
        ]);
    }

    /**
     * Update service details and price in integer cents.
     */
    public function updateService(Service $service, array $data): Service
    {
        if (isset($data['price'])) {
            $data['price_cents'] = (int) round(((float) $data['price']) * 100);
            unset($data['price']);
        }

        $service->update($data);
        return $service->fresh();
    }

    /**
     * Delete a service.
     */
    public function deleteService(Service $service): void
    {
        $service->delete();
    }
}
