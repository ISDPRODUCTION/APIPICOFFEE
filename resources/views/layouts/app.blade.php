<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Support\BusinessSettings::businessName() }} – @yield('title', 'POS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#F97316', hover: '#EA580C' },
                        background: '#F5F5F4',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #F5F5F4; }
        .nav-active { background: #FFF7ED; color: #F97316; }
        .nav-active svg { stroke: #F97316; }
        [x-cloak] { display: none !important; }

        /* Smooth page transitions for client-side navigation */
        main {
            transition: opacity 0.12s ease;
        }

        /* Dashboard: toolbar fix, hanya daftar menu yang scroll */
        #pos-page { isolation: isolate; }
        #pos-scroll-area {
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Sidebar transition */
        #sidebar {
            transition: transform 0.3s ease;
        }
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0; left: 0;
                height: 100%;
                z-index: 50;
                transform: translateX(-100%);
            }
            #sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
    <script>
    window.__userTheme = {
        color: '{{ auth()->user()->theme_color ?? "#F97316" }}',
        darkMode: {{ (auth()->user()->dark_mode ?? false) ? 'true' : 'false' }}
    };
    </script>
    <script src="{{ asset('js/store/themeStore.js') }}"></script>
    <script>
    if (window.themeStore && window.__userTheme) {
        themeStore.applyTheme(window.__userTheme.color, window.__userTheme.darkMode);
    }
    </script>
    @stack('styles')
</head>
<body class="bg-[#F5F5F4] text-[#1C1917]">

@php
    $userRole = auth()->user()->role ?? 'cashier';
    $isManager = $userRole === 'manager';
    $isAdmin   = in_array($userRole, ['admin', 'manager', 'supervisor']);
    $showMenuManagement = in_array($userRole, ['admin', 'manager', 'supervisor']);
    $showPosDashboard = $userRole !== 'admin';
    [$brandMain, $brandAccent] = \App\Support\BusinessSettings::brandParts();
@endphp

{{-- Mobile sidebar overlay --}}
<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-40 md:hidden" onclick="closeSidebar()"></div>

<div class="flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    <aside id="sidebar" class="w-60 bg-white border-r border-stone-200 flex flex-col flex-shrink-0 h-full">
        {{-- Logo + close button (mobile) --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-stone-100">
            <div class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center bg-orange-50 flex-shrink-0">
                @php $logoUrl = \App\Support\BusinessSettings::logoUrl(); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-cover">
                @else
                    <svg class="w-5 h-5 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                @endif
            </div>
            <span id="app-brand-name" class="font-bold text-[#1C1917] text-lg leading-none flex-1 truncate">
                <span id="app-brand-main">{{ $brandMain }}</span>@if($brandAccent) <span id="app-brand-accent" class="text-[#F97316]">{{ $brandAccent }}</span>@endif
            </span>
            {{-- Close btn (mobile only) --}}
            <button onclick="closeSidebar()" class="md:hidden w-7 h-7 flex items-center justify-center rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            <p class="text-xs font-semibold text-[#78716C] uppercase tracking-widest px-3 mb-3">Main Menu</p>

            @if($showPosDashboard)
            <a href="{{ route('pos.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('pos.*') ? 'nav-active' : 'text-[#78716C] hover:bg-stone-100' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>
            @endif

            @if($showMenuManagement)
            <a href="{{ route('menu.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('menu.*') ? 'nav-active' : 'text-[#78716C] hover:bg-stone-100' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                Menu Management
            </a>
            @endif

            @if($isAdmin)
            <a href="{{ route('reports.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'nav-active' : 'text-[#78716C] hover:bg-stone-100' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Sales Report
            </a>
            @endif

            <a href="{{ route('settings.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('settings.*') ? 'nav-active' : 'text-[#78716C] hover:bg-stone-100' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                Settings
            </a>
        </nav>

        {{-- Shift Status --}}
        <div id="shift-status-card" class="mx-3 mb-4 p-3 bg-orange-50 rounded-2xl cursor-pointer hover:bg-orange-100 transition-colors" onclick="uiStore.toggleProfile()">
            <p class="shift-label text-xs font-bold text-[#F97316] uppercase tracking-wider">Shift Status</p>
            <p class="shift-user-name text-sm font-semibold text-[#1C1917] mt-1">{{ ucfirst(auth()->user()->role) }}: {{ auth()->user()->name }}</p>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="w-2 h-2 bg-green-500 rounded-full inline-block"></span>
                <span class="shift-user-meta text-xs text-[#78716C]">{{ auth()->user()->shift_status }}</span>
            </div>
        </div>
    </aside>

    {{-- MAIN AREA --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        {{-- TOP NAV --}}
        <header class="h-14 bg-white border-b border-stone-200 flex items-center px-4 gap-3 flex-shrink-0">

            {{-- Hamburger (mobile only) --}}
            <button onclick="openSidebar()" class="md:hidden w-9 h-9 flex items-center justify-center rounded-xl text-stone-500 hover:bg-stone-100 transition-colors flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex-1 min-w-0"></div>

            {{-- Right side --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="relative">
                    <button onclick="uiStore.toggleProfile()"
                            class="flex items-center gap-2 text-sm font-medium text-[#1C1917] hover:text-[#F97316] transition-colors">
                        <svg class="w-5 h-5 text-[#78716C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden sm:inline">Account</span>
                    </button>

                    {{-- Dropdown --}}
                    <div id="profile-dropdown"
                        class="hidden absolute right-0 top-10 w-56 bg-white rounded-2xl shadow-xl border border-stone-100 py-2 z-50 origin-top-right"
                        style="opacity:0; transform: scale(0.95); transition: opacity 150ms, transform 150ms;">
                        <div class="px-4 py-3 border-b border-stone-100">
                            <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-[#78716C]">{{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                        <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1C1917] hover:bg-stone-50">
                            <svg class="w-4 h-4 text-[#78716C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile Settings
                        </a>
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1C1917] hover:bg-stone-50">
                            <svg class="w-4 h-4 text-[#78716C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            Settings
                        </a>
                        <div class="border-t border-stone-100 mt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="header-cart-wrap" class="{{ request()->routeIs('pos.*') ? '' : 'hidden' }}">
                    <button id="cart-toggle-btn"
                            class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center relative hover:bg-orange-200 transition-colors">
                        <svg class="w-5 h-5 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5h15M17 21a1 1 0 100-2 1 1 0 000 2zM7 21a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                        <span id="cart-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-[#F97316] rounded-full text-white text-[9px] font-bold flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main id="app-main" class="flex-1 relative {{ request()->routeIs('pos.*') ? 'overflow-hidden' : 'overflow-y-auto' }}">
            @yield('content')
        </main>
    </div>
</div>

<div id="dropdown-overlay" class="hidden fixed inset-0 z-40" onclick="uiStore.toggleProfile()"></div>

@stack('scripts')
<script src="{{ asset('js/store/uiStore.js') }}"></script>
<script src="{{ asset('js/store/cartStore.js') }}"></script>
<script src="{{ asset('js/services/apiService.js') }}"></script>
<script src="{{ asset('js/modules/dragModule.js') }}"></script>
<script src="{{ asset('js/modules/cartModule.js') }}"></script>
<script src="{{ asset('js/main.js') }}"></script>
<script src="{{ asset('js/modules/navigatorModule.js') }}"></script>
<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
</body>
</html>