<?php

namespace App\Http\Controllers;

use App\Models\JobItem;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of laundry services in the catalog.
     */
    public function index()
    {
        $services = Service::withCount('items')
            ->orderBy('name')
            ->get();

        return view('services.index', compact('services'));
    }

    /**
     * Store a newly created service in the catalog.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can add services to the catalog.');
        }

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'price'            => ['required', 'numeric', 'min:350'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ]);

        $priceInCents = (int) round(((float) $validated['price']) * 100);

        $payload = [
            'name'             => trim($validated['name']),
            'price_in_cents'   => $priceInCents,
            'duration_minutes' => (int) ($validated['duration_minutes'] ?? 45),
            'description'      => !empty($validated['description']) ? trim($validated['description']) : null,
        ];

        if (Schema::hasColumn('services', 'price')) {
            $payload['price'] = (float) $validated['price'];
        }
        if (Schema::hasColumn('services', 'price_cents')) {
            $payload['price_cents'] = $priceInCents;
        }
        if (Schema::hasColumn('services', 'slug')) {
            $payload['slug'] = Str::slug($validated['name']);
        }
        if (Schema::hasColumn('services', 'is_active')) {
            $payload['is_active'] = true;
        }

        Service::create($payload);

        return redirect()->route('services.index')->with('success', "Service \"{$validated['name']}\" added successfully at KSh " . number_format($validated['price'], 2) . " (VAT Inclusive).");
    }

    /**
     * Update the specified service in the catalog.
     */
    public function update(Request $request, Service $service)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can modify catalog services.');
        }

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'price'            => ['required', 'numeric', 'min:350'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ]);

        $priceInCents = (int) round(((float) $validated['price']) * 100);

        $payload = [
            'name'             => trim($validated['name']),
            'price_in_cents'   => $priceInCents,
            'duration_minutes' => (int) ($validated['duration_minutes'] ?? $service->duration_minutes ?? 45),
            'description'      => !empty($validated['description']) ? trim($validated['description']) : null,
        ];

        if (Schema::hasColumn('services', 'price')) {
            $payload['price'] = (float) $validated['price'];
        }
        if (Schema::hasColumn('services', 'price_cents')) {
            $payload['price_cents'] = $priceInCents;
        }

        $service->update($payload);

        return redirect()->route('services.index')->with('success', "Service \"{$service->name}\" updated successfully.");
    }

    /**
     * Remove the specified service from the catalog.
     */
    public function destroy(Service $service)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can delete catalog services.');
        }

        // Check if service is referenced in existing customer orders
        $itemsCount = JobItem::where('service_id', $service->id)->count();

        if ($itemsCount > 0) {
            if (Schema::hasColumn('services', 'is_active')) {
                $service->update(['is_active' => false]);
                return redirect()->route('services.index')->with('success', "Service \"{$service->name}\" was deactivated because it is linked to {$itemsCount} historical order(s).");
            }

            return redirect()->route('services.index')->with('error', "Cannot delete \"{$service->name}\" because it is linked to {$itemsCount} existing laundry order(s). Historical orders require this record for receipt reprints.");
        }

        $serviceName = $service->name;
        $service->delete();

        return redirect()->route('services.index')->with('success', "Service \"{$serviceName}\" removed from the catalog.");
    }
}

