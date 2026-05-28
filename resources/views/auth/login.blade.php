<x-layouts.auth title="Sign in">
<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">GetVirtual Operations System</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-6">Sign in to your account</h2>

        {{-- Status (e.g. password reset success) --}}
        @if (session('status'))
            <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                    Email address
                </label>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}"
                       autocomplete="username" autofocus required
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                              @error('email') border-red-400 @enderror"
                       placeholder="you@example.com">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs text-indigo-600 hover:text-indigo-500">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <input id="password" type="password" name="password"
                       autocomplete="current-password" required
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                              @error('password') border-red-400 @enderror"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center gap-2">
                <input id="remember" type="checkbox" name="remember"
                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="remember" class="text-sm text-slate-600">Keep me signed in</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors">
                Sign in
            </button>
        </form>
    </div>

    {{-- Monitoring notice --}}
    <p class="text-center text-xs text-slate-500 mt-6 px-4 leading-relaxed">
        By signing in, you acknowledge that your activity on this platform
        is tracked and monitored by GetVirtual.
    </p>
</div>
</x-layouts.auth>
