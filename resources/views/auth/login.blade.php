<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - RIMA</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-white">

    <div class="min-h-full flex flex-col">

        {{-- Header --}}
        <header class="h-14 flex items-center justify-center w-full shrink-0">
            <div class="flex items-center justify-between w-full max-w-5xl border-b border-slate-100 h-full px-6">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('logo_sodeci.png') }}" alt="SODECI" class="h-8 w-auto">
                    <span class="text-slate-900 font-semibold tracking-tight">RIMA</span>
                </div>
                <span class="text-[11px] text-slate-400 tracking-wide uppercase">Back-office SODECI</span>
            </div>
        </header>

        {{-- Content --}}
        <div class="flex-1 flex items-center justify-center px-6">
            <div class="w-full max-w-[360px] -mt-10">

                <div class="text-center mb-10">
                    <div class="flex justify-center mb-5">
                        <img src="{{ asset('logo_sodeci.png') }}" alt="SODECI" class="h-16 w-auto">
                    </div>
                    <h1 class="text-4xl font-bold text-slate-900">Connexion</h1>
                    <p class="text-slate-400 text-sm mt-1.5">Entrez vos identifiants pour continuer</p>
                </div>

                @if($errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity.duration.500ms class="mb-6 px-3 py-2.5 bg-red-50/50">
                    @foreach($errors->all() as $error)
                    <p class="text-[13px] text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4" novalidate
                    x-data="{ errors: {}, submit(e) {
                        this.errors = {};
                        const u = this.$refs.username.value.trim();
                        const p = this.$refs.password.value.trim();
                        if (!u) this.errors.username = 'Veuillez entrer votre identifiant.';
                        if (!p) this.errors.password = 'Veuillez entrer votre mot de passe.';
                        if (Object.keys(this.errors).length) { e.preventDefault(); return; }
                    } }"
                    @submit="submit($event)">
                    @csrf

                    <div>
                        <label for="username" class="block text-[13px] font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Identifiant</label>
                        <input type="text" name="username" id="username" x-ref="username" value="{{ old('username') }}" autofocus
                            class="filter-input w-full text-sm"
                            :class="errors.username ? 'border-b-red-400 focus:border-b-red-400' : ''"
                            @input="delete errors.username"
                            placeholder="ex: exemple@gmail.com">
                        <p x-show="errors.username" x-text="errors.username" class="text-[12px] text-red-500 mt-1.5"></p>
                    </div>

                    <div>
                        <label for="password" class="block text-[13px] font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Mot de passe</label>
                        <input type="password" name="password" id="password" x-ref="password"
                            class="filter-input w-full text-sm"
                            :class="errors.password ? 'border-b-red-400 focus:border-b-red-400' : ''"
                            @input="delete errors.password"
                            placeholder="Entrez votre mot de passe">
                        <p x-show="errors.password" x-text="errors.password" class="text-[12px] text-red-500 mt-1.5"></p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="remember" id="remember" class="w-3.5 h-3.5 border-slate-300 text-[#2DB56B] focus:ring-0 focus:ring-offset-0">
                            <label for="remember" class="text-[13px] text-slate-500 select-none">Se souvenir de moi</label>
                        </div>
                        <a href="#" class="text-[13px] text-slate-500 hover:text-slate-900 underline transition">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="w-full h-10 bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-sm font-medium rounded-full transition">
                        Continuer
                    </button>
                </form>
            </div>
        </div>

        {{-- Footer --}}
        <footer class="h-12 flex items-center justify-center px-6 shrink-0">
            <span class="text-[11px] text-slate-300">&copy; {{ date('Y') }} SODECI — RIMA v1.4</span>
        </footer>
    </div>

</body>
</html>
