<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - PRIMA</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="h-full bg-white font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-full">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/30 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transition-transform duration-200 lg:translate-x-0 lg:static lg:z-auto flex flex-col">

            {{-- Logo --}}
            <div class="h-14 flex items-center px-5 shrink-0">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('logo_sodeci.png') }}" alt="SODECI" class="h-7 w-auto">
                    <span class="text-slate-900 font-semibold tracking-tight">PRIMA</span>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition {{ request()->routeIs('dashboard') ? 'text-[#2DB56B] bg-[#ECFDF5]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    Tableau de bord
                </a>

                <a href="{{ route('vehicles.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition {{ request()->routeIs('vehicles.*') ? 'text-[#2DB56B] bg-[#ECFDF5]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    Véhicules
                </a>

                @if(in_array(auth()->user()->role, ['supervisor_sodeci', 'admin_sodeci']))
                <div class="pt-3 mt-3 border-t border-slate-100">
                    <p class="px-3 mb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Rapports</p>

                    <a href="{{ route('reports.regional') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition {{ request()->routeIs('reports.regional') ? 'text-[#2DB56B] bg-[#ECFDF5]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        Avancement par structure
                    </a>

                    <a href="{{ route('reports.compliance') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition {{ request()->routeIs('reports.compliance*') ? 'text-[#2DB56B] bg-[#ECFDF5]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        Conformité
                    </a>

                    <a href="{{ route('reports.agents') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition {{ request()->routeIs('reports.agents') ? 'text-[#2DB56B] bg-[#ECFDF5]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        Stats par agent
                    </a>
                </div>
                @endif

                @if(auth()->user()->isAdminSodeci())
                <div class="pt-3 mt-3 border-t border-slate-100">
                    <p class="px-3 mb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Administration</p>

                    <div x-data="{ open: {{ request()->routeIs('referentials.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 text-[13px] font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-3.75"/></svg>
                                Référentiels
                            </span>
                            <svg :class="open && 'rotate-90'" class="w-3.5 h-3.5 text-slate-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <div x-show="open" x-collapse x-cloak class="ml-7 mt-0.5 space-y-0.5 border-l border-slate-200 pl-3">
                            <a href="{{ route('referentials.brands') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.brands*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Marques</a>
                            <a href="{{ route('referentials.models') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.models*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Modèles</a>
                            <a href="{{ route('referentials.structures') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.structures*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Structures</a>
                            <a href="{{ route('referentials.insurances') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.insurances*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Assurances</a>
                            {{-- <a href="{{ route('referentials.directions') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.directions*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Directions</a> --}}
                            <a href="{{ route('referentials.vehicle-types') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.vehicle-types*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Types véhicule</a>
                            <a href="{{ route('referentials.vehicle-categories') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.vehicle-categories*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Catégories</a>
                            <a href="{{ route('referentials.fuel-types') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.fuel-types*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Carburants</a>
                            <a href="{{ route('referentials.transmissions') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.transmissions*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Transmissions</a>
                            <a href="{{ route('referentials.vehicle-statuses') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.vehicle-statuses*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Statuts</a>
                            <a href="{{ route('referentials.contract-types') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.contract-types*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Contrats</a>
                            <a href="{{ route('referentials.colors') }}" class="block px-2 py-1.5 text-[13px] {{ request()->routeIs('referentials.colors*') ? 'text-slate-900 font-medium' : 'text-slate-400 hover:text-slate-700' }}">Couleurs</a>
                        </div>
                    </div>

                    <a href="{{ route('users.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition {{ request()->routeIs('users.*') ? 'text-[#2DB56B] bg-[#ECFDF5]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        Utilisateurs
                    </a>

                    <a href="{{ route('audit.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition {{ request()->routeIs('audit.*') ? 'text-[#2DB56B] bg-[#ECFDF5]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        Journal d'audit
                    </a>
                </div>
                @endif
            </nav>

            {{-- User footer --}}
            <div class="px-4 py-3 border-t border-slate-200">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 bg-[#2DB56B] flex items-center justify-center shrink-0">
                        <span class="text-white text-[10px] font-bold">{{ auth()->user()->initials }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-slate-900 truncate">{{ auth()->user()->full_name }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->organization }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Header --}}
            <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-5 lg:px-8 shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <h1 class="text-sm font-semibold text-slate-900">@yield('header', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden sm:block text-[11px] text-slate-400 uppercase tracking-wide">{{ str_replace('_', ' ', auth()->user()->role) }}</span>

                    {{-- Notification bell --}}
                    @php
                        $unreadNotificationsCount = \App\Models\Notification::forUser(auth()->id())->unread()->count();
                    @endphp
                    <a href="{{ route('notifications.index') }}" class="relative text-slate-400 hover:text-slate-600 transition p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        @if($unreadNotificationsCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[16px] h-4 px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                        @endif
                    </a>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-slate-50 px-2 py-1.5 transition">
                            <div class="w-7 h-7 bg-[#2DB56B] flex items-center justify-center">
                                <span class="text-white text-[10px] font-bold">{{ auth()->user()->initials }}</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 py-1 z-50" x-cloak>
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-[13px] font-medium text-slate-900">{{ auth()->user()->full_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ auth()->user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50/50 transition">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Notifications --}}
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity.duration.500ms class="mx-5 lg:mx-8 mt-4" x-cloak>
                <div class="px-3 py-2.5 bg-emerald-50/50">
                    <p class="text-[13px] text-emerald-600">{{ session('success') }}</p>
                </div>
            </div>
            @endif
            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity.duration.500ms class="mx-5 lg:mx-8 mt-4" x-cloak>
                <div class="px-3 py-2.5 bg-red-50/50">
                    <p class="text-[13px] text-red-600">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            {{-- Content --}}
            <main class="flex-1 p-5 lg:p-8 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
    {{-- Loading overlay --}}
    <div id="loadingOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-white/70 backdrop-blur-sm" style="display:none;">
        <div class="flex flex-col items-center gap-4">
            <svg class="w-8 h-8 animate-spin text-[#2DB56B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-[13px] font-medium text-slate-900">Traitement en cours...</p>
        </div>
    </div>

    <script>
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName !== 'FORM') return;
            if (form.closest('[data-no-loading]')) return;
            if (form.action && form.action.includes('/logout')) return;
            if (form.method && form.method.toUpperCase() === 'GET') return;

            setTimeout(function() {
                if (!e.defaultPrevented) {
                    var overlay = document.getElementById('loadingOverlay');
                    if (overlay) overlay.style.display = 'flex';
                }
            }, 0);
        });
    </script>
    @stack('scripts')
</body>
</html>
