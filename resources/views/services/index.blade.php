<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- Alerts -->
        @if(session('success'))
        <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-xl text-xs font-bold flex items-center justify-between shadow-sm">
            <span>✓ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-900 font-black">&times;</button>
        </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Services & Pricing Catalog</h1>
                <p class="text-xs text-slate-500 font-medium">Standard wash, dry clean, duvet, and pressing prices (stored in integer cents)</p>
            </div>
            <button onclick="document.getElementById('addServiceModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Add New Service
            </button>
        </div>

        <!-- GET-based Filter Bar (Eloquent ->when pattern) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <form method="GET" action="{{ route('services.index') }}" class="p-4 bg-slate-50/60 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search service name..."
                           class="bg-white border border-slate-300 rounded-lg p-2 text-xs w-full sm:w-64 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">

                    <select name="category" class="bg-white border border-slate-300 rounded-lg p-2 text-xs font-medium focus:ring-emerald-500 shadow-sm">
                        <option value="">All Categories</option>
                        <option value="wash_fold" {{ request('category') === 'wash_fold' ? 'selected' : '' }}>Wash & Fold</option>
                        <option value="dry_clean" {{ request('category') === 'dry_clean' ? 'selected' : '' }}>Dry Clean</option>
                        <option value="duvets" {{ request('category') === 'duvets' ? 'selected' : '' }}>Duvets & Bedding</option>
                        <option value="ironing" {{ request('category') === 'ironing' ? 'selected' : '' }}>Ironing Only</option>
                    </select>

                    <select name="status" class="bg-white border border-slate-300 rounded-lg p-2 text-xs font-medium focus:ring-emerald-500 shadow-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('services.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-900 font-semibold">
                        Reset
                    </a>
                    @endif
                </div>
                <div class="text-slate-500 font-mono text-[11px]">
                    Total: {{ $services->total() }} Services Listed
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-4 w-12">#</th>
                            <th class="py-3.5 px-4">Service Name</th>
                            <th class="py-3.5 px-4">Category</th>
                            <th class="py-3.5 px-4 text-right">Standard Price</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($services as $index => $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-slate-400 font-mono">{{ $services->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $item->name }}
                                @if($item->description)
                                <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $item->category) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900 text-sm">
                                KSh {{ $item->formatted_price }}
                                <span class="text-[10px] text-slate-400 block font-normal font-mono">({{ $item->price_cents }} cents)</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" onclick="openEditModal({{ json_encode($item) }})" class="font-bold text-blue-600 hover:text-blue-800">
                                        Edit
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <form method="POST" action="{{ route('services.destroy', $item->id) }}" onsubmit="return confirm('Remove service {{ $item->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 font-medium">No services found. Click "+ Add New Service" above to add services.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if($services->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $services->links() }}
            </div>
            @endif
        </div>

    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-bold text-slate-900">Add New Service</h3>
                <button onclick="document.getElementById('addServiceModal').classList.add('hidden')" class="text-slate-400 font-bold text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('services.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Service Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Duvet Wash & Dry (King Size)" class="w-full rounded-lg border-slate-300 p-2.5">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Category *</label>
                        <select name="category" class="w-full rounded-lg border-slate-300 p-2.5">
                            <option value="wash_fold">Wash & Fold</option>
                            <option value="dry_clean">Dry Clean</option>
                            <option value="duvets">Duvets & Bedding</option>
                            <option value="ironing">Ironing Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Price (KSh) *</label>
                        <input type="number" step="0.50" min="1" name="price" required placeholder="850.00" class="w-full rounded-lg border-slate-300 p-2.5 font-mono">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Description / Notes</label>
                    <textarea name="description" rows="2" placeholder="Turnaround time, machine instructions..." class="w-full rounded-lg border-slate-300 p-2.5"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="document.getElementById('addServiceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 font-bold rounded-lg text-white">Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="editServiceModal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-bold text-slate-900">Edit Service</h3>
                <button onclick="document.getElementById('editServiceModal').classList.add('hidden')" class="text-slate-400 font-bold text-xl">&times;</button>
            </div>
            <form id="editServiceForm" method="POST" action="" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Service Name *</label>
                    <input type="text" id="edit_name" name="name" required class="w-full rounded-lg border-slate-300 p-2.5">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Category *</label>
                        <select id="edit_category" name="category" class="w-full rounded-lg border-slate-300 p-2.5">
                            <option value="wash_fold">Wash & Fold</option>
                            <option value="dry_clean">Dry Clean</option>
                            <option value="duvets">Duvets & Bedding</option>
                            <option value="ironing">Ironing Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Price (KSh) *</label>
                        <input type="number" step="0.50" min="1" id="edit_price" name="price" required class="w-full rounded-lg border-slate-300 p-2.5 font-mono">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select id="edit_is_active" name="is_active" class="w-full rounded-lg border-slate-300 p-2.5">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Description</label>
                    <textarea id="edit_description" name="description" rows="2" class="w-full rounded-lg border-slate-300 p-2.5"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="document.getElementById('editServiceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 font-bold rounded-lg text-slate-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 font-bold rounded-lg text-white">Update Service</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(service) {
            document.getElementById('editServiceForm').action = '/services/' + service.id;
            document.getElementById('edit_name').value = service.name;
            document.getElementById('edit_category').value = service.category;
            document.getElementById('edit_price').value = (service.price_cents / 100).toFixed(2);
            document.getElementById('edit_is_active').value = service.is_active ? '1' : '0';
            document.getElementById('edit_description').value = service.description || '';
            document.getElementById('editServiceModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
