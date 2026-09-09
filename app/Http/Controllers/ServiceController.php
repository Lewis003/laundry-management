<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(
        protected ServiceService $serviceService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category', 'status']);
        $services = $this->serviceService->getFilteredServices($filters, 10);

        return view('services.index', compact('services', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->canManageServices(), 403, 'Only Managers and Admins can create or modify services.');

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'price'            => ['required', 'numeric', 'min:1'], // Entered in KSh, service converts to cents
            'category'         => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['category'] = $validated['category'] ?? 'wash_fold';
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 60;

        $service = $this->serviceService->createService($validated);

        return redirect()
            ->route('services.index')
            ->with('success', "Service '{$service->name}' added to catalog.");
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        abort_unless(auth()->user()->canManageServices(), 403, 'Only Managers and Admins can create or modify services.');

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'price'            => ['required', 'numeric', 'min:1'],
            'category'         => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'is_active'        => ['nullable', 'boolean'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['category'] = $validated['category'] ?? $service->category ?? 'wash_fold';
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : ($service->is_active ?? true);

        $this->serviceService->updateService($service, $validated);

        return redirect()
            ->route('services.index')
            ->with('success', "Service '{$service->name}' updated.");
    }

    public function destroy(Service $service): RedirectResponse
    {
        abort_unless(auth()->user()->canManageServices(), 403, 'Only Managers and Admins can delete services.');

        $name = $service->name;
        $this->serviceService->deleteService($service);

        return redirect()
            ->route('services.index')
            ->with('success', "Service '{$name}' removed from catalog.");
    }
}
