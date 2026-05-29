<x-layouts.auth title="Confirm Password">
<!-- GVOS UI Fidelity v2 active -->
<div class="w-full max-w-sm">

    {{-- GVOS Logo --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-10 h-10 bg-secondary-container rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-on-secondary" style="font-variation-settings:'FILL' 1;font-size:20px;">hub</span>
        </div>
        <span class="text-xl font-bold text-secondary-fixed tracking-tight">GVOS</span>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-border-subtle">
        {{-- Blue accent bar --}}
        <div class="h-1 w-full bg-secondary"></div>

        <div class="px-8 py-8">
            <div class="w-12 h-12 bg-secondary/5 rounded-xl flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-secondary" style="font-size:24px;">lock</span>
            </div>
            <h2 class="text-lg font-bold text-on-surface mb-2 text-center">Confirm your password</h2>
            <p class="text-sm text-on-surface-variant mb-6 text-center">
                This is a secure area. Please confirm your password before continuing.
            </p>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline" style="font-size:18px;">lock</span>
                        <input type="password" name="password" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                      focus:outline-none focus:ring-2 focus:border-secondary
                                      @error('password') border-status-blocked @enderror">
                    </div>
                    @error('password') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="w-full bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary font-semibold text-sm py-2.5 rounded-lg transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:16px;">verified_user</span>
                    Confirm Password
                </button>
            </form>
        </div>
    </div>

    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="text-sm text-secondary-fixed hover:text-white transition-colors flex items-center justify-center gap-1">
            <span class="material-symbols-outlined" style="font-size:14px;">arrow_back</span>
            Back to sign in
        </a>
    </div>

</div>
</x-layouts.auth>
