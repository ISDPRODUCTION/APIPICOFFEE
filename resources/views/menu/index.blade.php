@extends('layouts.app')

@section('title', 'Menu Management')

@section('content')
<div class="p-4 md:p-6">
    <div class="flex items-center justify-between mb-6 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-[#1C1917] truncate">Menu Management</h1>
            <span class="text-sm text-[#78716C] whitespace-nowrap">({{ $products->total() }} Items)</span>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <button onclick="categoryModule.openAddModal()"
                    class="hidden sm:flex items-center gap-2 px-3 md:px-4 py-2.5 bg-stone-800 hover:bg-stone-900 text-white rounded-2xl text-sm font-semibold transition-colors">
                Tambah Kategori
            </button>
            <button onclick="menuModule.openAddModal()"
                    class="flex items-center gap-2 px-3 md:px-4 py-2.5 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v8M8 12h8"/>
                </svg>
                <span class="hidden sm:inline">Tambah Menu</span>
                <span class="sm:hidden">Tambah</span>
            </button>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
        <div class="w-full sm:w-auto sm:flex-1 sm:max-w-sm relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#78716C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" id="menu-search" value="{{ $search }}" placeholder="Search menu items..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm bg-white rounded-2xl border border-stone-200 focus:ring-2 focus:ring-primary/30 outline-none">
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto sm:ml-auto overflow-x-auto">
            <div id="category-container" class="flex gap-2 flex-nowrap">
            </div>
            <div class="w-px h-6 bg-stone-300 mx-1 flex-shrink-0"></div>
            <div id="category-pagination" class="flex gap-1 flex-shrink-0">
            </div>
        </div>
    </div>

    {{-- Mobile: tambah kategori button --}}
    <div class="sm:hidden mb-4">
        <button onclick="categoryModule.openAddModal()"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-stone-800 hover:bg-stone-900 text-white rounded-2xl text-sm font-semibold transition-colors">
            Tambah Kategori
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        {{-- Mobile card view --}}
        <div class="block md:hidden divide-y divide-stone-100">
            @forelse($products as $product)
            <div class="p-4 hover:bg-stone-50 transition-colors">
                <div class="flex items-center gap-3">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                        class="w-14 h-14 rounded-xl object-cover bg-stone-100 flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#1C1917] truncate">{{ $product->name }}</p>
                        <p class="text-xs text-[#78716C]">ID: {{ $product->sku }}</p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="px-2 py-0.5 bg-stone-100 text-[#78716C] text-xs font-medium rounded-lg capitalize">
                                {{ $product->category->name ?? '-' }}
                            </span>
                            <span class="text-xs font-bold text-primary">{{ $product->formatted_price }}</span>
                            <span class="flex items-center gap-1 text-xs font-medium
                                        {{ $product->stock > 10 ? 'text-green-600' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $product->stock }} pcs
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5 flex-shrink-0">
                        <button onclick="menuModule.openEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->category->slug ?? '' }}', {{ $product->price }}, {{ $product->stock }}, '{{ $product->image_url }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button onclick="menuModule.openDeleteModal({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="py-16 text-center text-[#78716C]">No products found.</div>
            @endforelse
        </div>

        {{-- Desktop table view --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="border-b border-stone-100">
                        <th class="text-left py-4 px-6 text-xs font-bold text-[#78716C] uppercase tracking-wider w-16">Image</th>
                        <th class="text-left py-4 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Product Name</th>
                        <th class="text-left py-4 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Category</th>
                        <th class="text-left py-4 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Price</th>
                        <th class="text-left py-4 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Stock</th>
                        <th class="text-right py-4 px-6 text-xs font-bold text-[#78716C] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="py-4 px-6">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                class="w-12 h-12 rounded-xl object-cover bg-stone-100">
                        </td>
                        <td class="py-4 px-4">
                            <p class="text-sm font-semibold text-[#1C1917]">{{ $product->name }}</p>
                            <p class="text-xs text-[#78716C]">ID: {{ $product->sku }}</p>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 bg-stone-100 text-[#78716C] text-xs font-medium rounded-lg capitalize">
                                {{ $product->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="text-sm font-semibold text-[#1C1917]">{{ $product->formatted_price }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="flex items-center gap-1.5 text-sm font-medium
                                        {{ $product->stock > 10 ? 'text-green-600' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $product->stock }} pcs
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="menuModule.openEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->category->slug ?? '' }}', {{ $product->price }}, {{ $product->stock }}, '{{ $product->image_url }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="menuModule.openDeleteModal({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center text-[#78716C]">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 md:px-6 py-4 border-t border-stone-100 flex items-center justify-between gap-3 flex-wrap">
            <p class="text-sm text-[#78716C]">Showing {{ $products->firstItem() }} – {{ $products->lastItem() }} of {{ $products->total() }} items</p>
            <div class="flex items-center gap-1">
                @if($products->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-full text-stone-300 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $products->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-full text-[#78716C] hover:bg-stone-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif

                @for($i = 1; $i <= min($products->lastPage(), 5); $i++)
                <a href="{{ $products->url($i) }}"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-semibold transition-colors
                            {{ $products->currentPage() == $i ? 'bg-primary text-white' : 'text-[#78716C] hover:bg-stone-100' }}">
                    {{ $i }}
                </a>
                @endfor

                @if(!$products->onLastPage())
                <a href="{{ $products->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-full text-[#78716C] hover:bg-stone-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals (tidak diubah) --}}
<div id="add-category-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="categoryModule.closeAddModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">
        <div class="p-6">
            <div class="flex items-start justify-between mb-1">
                <div>
                    <h2 class="text-xl font-bold text-[#1C1917]">Tambah Kategori</h2>
                </div>
                <button onclick="categoryModule.closeAddModal()" class="text-stone-400 hover:text-[#1C1917] mt-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="add-category-form" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Nama Kategori</label>
                    <input type="text" name="name" placeholder="Isi nama kategori" required
                            class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="categoryModule.closeAddModal()"
                            class="flex-1 py-3 text-sm font-semibold text-[#78716C] hover:text-[#1C1917] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 bg-stone-800 hover:bg-stone-900 text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-stone-200">
                        Tambah Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="add-menu-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="menuModule.closeAddModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10">
        <div class="p-6">
            <div class="flex items-start justify-between mb-1">
                <div>
                    <h2 class="text-xl font-bold text-[#1C1917]">Add New Menu</h2>
                    <p class="text-sm text-[#78716C]">Provide the details for the new product catalog.</p>
                </div>
                <button onclick="menuModule.closeAddModal()" class="text-stone-400 hover:text-[#1C1917] mt-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="add-menu-form" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Product Name</label>
                    <input type="text" name="name" placeholder="Isi nama menu"
                            class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Category</label>
                        <select name="category" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none appearance-none bg-white">
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Price</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-primary">Rp</span>
                            <input type="number" name="price" placeholder="25.000"
                                    class="w-full pl-10 pr-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Initial Stock</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="stock" value="0" min="0"
                                class="flex-1 px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                        <button type="button" onclick="this.previousElementSibling.value=Math.max(0,+this.previousElementSibling.value-1)"
                                class="w-10 h-10 flex items-center justify-center rounded-xl border border-stone-200 text-[#78716C] hover:border-stone-300 text-lg font-light">−</button>
                        <button type="button" onclick="this.previousElementSibling.previousElementSibling.previousElementSibling.value=+this.previousElementSibling.previousElementSibling.previousElementSibling.value+1"
                                class="w-10 h-10 flex items-center justify-center rounded-xl border border-stone-200 text-[#78716C] hover:border-stone-300 text-lg font-light">+</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Product Image</label>
                    <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-stone-200 rounded-2xl cursor-pointer hover:border-primary/50 hover:bg-orange-50/30 transition-all bg-stone-50/50">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mb-2">
                                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-[#1C1917]">Click to upload or drag and drop</p>
                            <p class="text-xs text-[#78716C]">JPG, PNG up to 5MB</p>
                        </div>
                        <input type="file" name="image" accept="image/*" class="hidden" onchange="menuModule.previewImage(this)">
                    </label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="menuModule.closeAddModal()"
                            class="flex-1 py-3 text-sm font-semibold text-[#78716C] hover:text-[#1C1917] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                        Tambah Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-menu-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="menuModule.closeEditModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10">
        <div class="p-6">
            <div class="flex items-start justify-between mb-1">
                <div>
                    <h2 class="text-xl font-bold text-[#1C1917]">Edit Menu</h2>
                    <p class="text-sm text-[#78716C]">Update the details for the existing product catalog.</p>
                </div>
                <button onclick="menuModule.closeEditModal()" class="text-stone-400 hover:text-[#1C1917] mt-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-menu-form" class="mt-5">
                @csrf
                @method('POST')
                <input type="hidden" name="_productId" id="edit-product-id">
                <div class="flex gap-4 mb-4">
                    <div class="flex flex-col items-center gap-2">
                        <img id="edit-product-image" src="" alt="Product" class="w-28 h-28 rounded-xl object-cover bg-stone-100">
                        <button type="button" onclick="document.getElementById('edit-image-input').click()" class="text-xs font-semibold text-primary flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Change Image
                        </button>
                        <button type="button" onclick="menuModule.removeEditImage()" class="text-xs font-semibold text-red-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Remove
                        </button>
                        <input type="file" id="edit-image-input" name="image" accept="image/*" class="hidden" onchange="menuModule.previewEditImage(this)">
                    </div>
                    <div class="flex-1 space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Product Name</label>
                            <input type="text" id="edit-name" name="name"
                                    class="w-full px-4 py-2.5 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Category</label>
                            <select id="edit-category" name="category"
                                    class="w-full px-4 py-2.5 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none bg-white">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Price</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-[#78716C]">Rp</span>
                            <input type="number" id="edit-price" name="price"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Initial Stock</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="document.getElementById('edit-stock').value=Math.max(0,+document.getElementById('edit-stock').value-1)"
                                    class="w-9 h-9 flex items-center justify-center rounded-xl border border-stone-200 text-[#78716C] text-lg font-light">−</button>
                            <input type="number" id="edit-stock" name="stock" min="0"
                                    class="w-16 text-center px-2 py-2.5 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                            <button type="button" onclick="document.getElementById('edit-stock').value=+document.getElementById('edit-stock').value+1"
                                    class="w-9 h-9 flex items-center justify-center rounded-xl border border-stone-200 text-[#78716C] text-lg font-light">+</button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="menuModule.closeEditModal()"
                            class="flex-1 py-3 text-sm font-semibold text-[#78716C] hover:text-[#1C1917] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="menuModule.closeDeleteModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-8 text-center">
        <div class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-[#1C1917] mb-3">Hapus Menu</h2>
        <p id="delete-confirm-text" class="text-sm text-[#78716C] mb-6"></p>
        <input type="hidden" id="delete-product-id">
        <div class="flex gap-3">
            <button onclick="menuModule.closeDeleteModal()"
                    class="flex-1 py-3 text-sm font-semibold border-2 border-stone-200 rounded-2xl text-[#78716C] hover:border-stone-300 transition-colors">
                Batal
            </button>
            <button onclick="menuModule.confirmDelete()"
                    class="flex-1 py-3 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors">
                Hapus
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
window.categoryModule = {
    currentPage: 1,
    currentCategory: '{{ $category ?? "all" }}',
    search: '{{ $search ?? "" }}',

    async loadCategories(page = 1) {
        try {
            const [res, allRes] = await Promise.all([
                fetch(`/categories/api?page=${page}`),
                fetch(`/categories/api?per_page=999`)
            ]);
            const data = await res.json();
            const allData = await allRes.json();
            
            this.currentPage = data.current_page;
            this.renderCategories(data.data);
            this.renderPagination(data);
            this.populateSelects(allData.data);
        } catch (e) {
            console.error("Failed to load categories", e);
        }
    },

    renderCategories(categories) {
        const container = document.getElementById('category-container');
        if (!container) return;
        let html = `<a href="/menu?category=all&search=${this.search}" class="px-3 py-1.5 text-sm font-semibold rounded-lg transition-colors whitespace-nowrap ${this.currentCategory == 'all' ? 'text-primary border-b-2 border-primary' : 'text-[#78716C] hover:text-[#1C1917]'}">All</a>`;
        categories.forEach(cat => {
            const isActive = this.currentCategory == cat.slug;
            html += `<a href="/menu?category=${cat.slug}&search=${this.search}" class="px-3 py-1.5 text-sm font-semibold rounded-lg transition-colors whitespace-nowrap ${isActive ? 'text-primary border-b-2 border-primary' : 'text-[#78716C] hover:text-[#1C1917]'}">${cat.name}</a>`;
        });
        container.innerHTML = html;
    },

    renderPagination(data) {
        const container = document.getElementById('category-pagination');
        if (!container) return;
        let html = '';
        if (data.prev_page_url) {
            html += `<button onclick="categoryModule.loadCategories(${data.current_page - 1})" class="p-1 rounded bg-stone-100 text-stone-600 hover:bg-stone-200"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg></button>`;
        }
        if (data.next_page_url) {
            html += `<button onclick="categoryModule.loadCategories(${data.current_page + 1})" class="p-1 rounded bg-stone-100 text-stone-600 hover:bg-stone-200"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg></button>`;
        }
        container.innerHTML = html;
    },

    populateSelects(categories) {
        const addSelect = document.querySelector('select[name="category"]');
        const editSelect = document.getElementById('edit-category');
        if (!addSelect || !editSelect) return;
        let options = '<option value="">Select category</option>';
        categories.forEach(cat => {
            options += `<option value="${cat.slug}">${cat.name}</option>`;
        });
        const currentAddVal = addSelect.value;
        const currentEditVal = editSelect.value;
        addSelect.innerHTML = options;
        editSelect.innerHTML = options;
        if (currentAddVal) addSelect.value = currentAddVal;
        if (currentEditVal) editSelect.value = currentEditVal;
    },

    openAddModal() { document.getElementById('add-category-modal').classList.remove('hidden'); },
    closeAddModal() {
        document.getElementById('add-category-modal').classList.add('hidden');
        document.getElementById('add-category-form').reset();
    }
};

window.menuModule = {
    openAddModal() { document.getElementById('add-menu-modal').classList.remove('hidden'); },
    closeAddModal() {
        document.getElementById('add-menu-modal').classList.add('hidden');
        document.getElementById('add-menu-form').reset();
    },
    openEditModal(id, name, category, price, stock, image) {
        document.getElementById('edit-product-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-category').value = category;
        document.getElementById('edit-price').value = price;
        document.getElementById('edit-stock').value = stock;
        document.getElementById('edit-product-image').src = image;
        document.getElementById('edit-menu-modal').classList.remove('hidden');
    },
    closeEditModal() { document.getElementById('edit-menu-modal').classList.add('hidden'); },
    previewEditImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => { document.getElementById('edit-product-image').src = e.target.result; };
            reader.readAsDataURL(input.files[0]);
        }
    },
    removeEditImage() {
        document.getElementById('edit-product-image').src = 'https://placehold.co/400x400/FFF7ED/F97316?text=No+Image';
        document.getElementById('edit-image-input').value = '';
    },
    openDeleteModal(id, name) {
        document.getElementById('delete-product-id').value = id;
        document.getElementById('delete-confirm-text').innerHTML = `Apakah Anda yakin ingin menghapus menu <strong>'${name}'</strong>?`;
        document.getElementById('delete-modal').classList.remove('hidden');
    },
    closeDeleteModal() { document.getElementById('delete-modal').classList.add('hidden'); },
    async confirmDelete() {
        const id = document.getElementById('delete-product-id').value;
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const res = await fetch(`/menu/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) navigatorModule.navigate(window.location.href);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    categoryModule.loadCategories();

    // Wire up search input with debounce
    const searchInput = document.getElementById('menu-search');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchValue = this.value.trim();
                const currentCategory = categoryModule.currentCategory || 'all';
                const params = new URLSearchParams();
                params.set('category', currentCategory);
                if (searchValue) {
                    params.set('search', searchValue);
                }
                window.location.href = '/menu?' + params.toString();
            }, 400);
        });

        // Also allow Enter key to search immediately
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(debounceTimer);
                const searchValue = this.value.trim();
                const currentCategory = categoryModule.currentCategory || 'all';
                const params = new URLSearchParams();
                params.set('category', currentCategory);
                if (searchValue) {
                    params.set('search', searchValue);
                }
                window.location.href = '/menu?' + params.toString();
            }
        });
    }
});

document.getElementById('add-category-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const res = await fetch('/categories', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: new FormData(this)
    });
    const data = await res.json();
    if (data.success) { categoryModule.closeAddModal(); categoryModule.loadCategories(categoryModule.currentPage); }
    else alert('Error: ' + (data.message || 'Gagal menambah kategori'));
});

document.getElementById('add-menu-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const res = await fetch('{{ route("menu.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: new FormData(this)
    });
    const data = await res.json();
    if (data.success) navigatorModule.navigate(window.location.href);
    else alert('Error: ' + (data.message || 'Gagal menambah menu'));
});

document.getElementById('edit-menu-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('edit-product-id').value;
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const formData = new FormData(this);
    formData.append('_method', 'POST');
    const res = await fetch(`/menu/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: formData
    });
    const data = await res.json();
    if (data.success) navigatorModule.navigate(window.location.href);
    else alert('Error: ' + (data.message || 'Gagal update menu'));
});
</script>
@endpush