<x-layouts.auth title="Sign in" variant="dark">
<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-secondary-container rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-on-secondary" style="font-variation-settings: 'FILL' 1; font-size: 20px;">hub</span>
            </div>
            <span class="text-2xl font-bold text-secondary-fixed tracking-tight">GVOS</span>
        </div>
        <p class="text-on-primary-container text-xs uppercase tracking-wider">Operations Management Platform</p>
    </div>

    {{-- Card --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-lg border border-border-subtle overflow-hidden">

        {{-- Card header accent --}}
        <div class="h-1 w-full bg-secondary"></div>

        <div class="p-card-padding">
            <h2 class="text-lg font-semibold text-on-surface mb-6">Sign in to your account</h2>

            {{-- Status (e.g. password reset success) --}}
            @if (session('status'))
                <div class="mb-4 text-sm text-status-completed bg-status-completed/10 border border-status-completed/20 rounded-lg px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-input-gap">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">
                        Email Address
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline" style="font-size: 18px;">mail</span>
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}"
                               autocomplete="username" autofocus required
                               class="w-full pl-10 pr-4 py-3 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('email') border-status-blocked @enderror"
                               placeholder="you@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs text-secondary hover:brightness-110 transition-all">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline" style="font-size: 18px;">lock</span>
                        <input id="password" type="password" name="password"
                               autocomplete="current-password" required
                               class="w-full pl-10 pr-4 py-3 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('password') border-status-blocked @enderror"
                               placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2">
                    <input id="remember" type="checkbox" name="remember"
                           class="h-4 w-4 rounded border-border-subtle text-secondary focus:ring-secondary/20">
                    <label for="remember" class="text-sm text-on-surface-variant">Keep me signed in</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary font-semibold text-sm py-3 rounded-lg transition-all shadow-md flex items-center justify-center gap-2">
                    Sign in
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                </button>
            </form>
        </div>

        {{-- Security note --}}
        <div class="px-card-padding pb-card-padding">
            <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-lg border border-border-subtle">
                <span class="material-symbols-outlined text-outline flex-shrink-0" style="font-size: 18px;">verified_user</span>
                <p class="text-[11px] text-on-surface-variant leading-relaxed">
                    Your activity on this platform is monitored for quality and compliance purposes.
                </p>
            </div>
        </div>
    </div>

    {{-- Footer links --}}
    <div class="flex justify-center gap-6 mt-6">
        <span class="text-[11px] text-on-primary-container">Privacy Policy</span>
        <span class="text-[11px] text-on-primary-container">Support</span>
    </div>
</div>
</x-layouts.auth>
