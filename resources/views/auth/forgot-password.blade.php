<x-layouts.auth title="Reset Password" variant="light">
<div class="w-full max-w-[480px]">

    {{-- Card --}}
    <div class="bg-surface-container-lowest border border-border-subtle rounded-xl shadow-lg overflow-hidden">

        {{-- Visual header --}}
        <div class="h-40 relative bg-sidebar-bg flex items-center justify-center overflow-hidden">
            <div class="relative z-10 bg-white/10 backdrop-blur-md rounded-full p-6 border border-white/20">
                <span class="material-symbols-outlined text-secondary-fixed" style="font-size: 36px; font-variation-settings: 'FILL' 0;">lock_reset</span>
            </div>
        </div>

        <div class="p-card-padding flex flex-col gap-6">
            <div class="text-center flex flex-col gap-2">
                <h1 class="text-xl font-semibold text-on-surface">Forgot Password?</h1>
                <p class="text-sm text-on-surface-variant">
                    Enter your registered email address and we'll send you a secure link to reset your credentials.
                </p>
            </div>

            @if (session('status'))
                <div class="text-sm text-status-completed bg-status-completed/10 border border-status-completed/20 rounded-lg px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-input-gap">
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="email" class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">
                        Email Address
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 18px;">mail</span>
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}" required autofocus
                               class="w-full pl-12 pr-4 py-3 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('email') border-status-blocked @enderror"
                               placeholder="you@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary font-semibold text-sm py-3 rounded-lg transition-all shadow-md flex items-center justify-center gap-2">
                    Send Reset Link
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                </button>
            </form>

            <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-lg border border-border-subtle">
                <span class="material-symbols-outlined text-outline flex-shrink-0" style="font-size: 18px;">verified_user</span>
                <p class="text-[11px] text-on-surface-variant">
                    Secured with GVOS Advanced Enterprise Security (AES-256).
                </p>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-secondary hover:brightness-110 transition-all">
                    Back to sign in
                </a>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="flex justify-center gap-8 mt-6">
        <span class="text-[11px] text-outline">Privacy Policy</span>
        <span class="text-[11px] text-outline">Security Center</span>
        <span class="text-[11px] text-outline">Support</span>
    </div>
</div>
</x-layouts.auth>
