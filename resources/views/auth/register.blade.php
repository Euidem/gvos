<x-layouts.auth title="Register">
<!-- GVOS UI Fidelity v2 active -->
<div class="w-full max-w-sm">

    {{-- GVOS Logo --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-10 h-10 bg-secondary-container rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-on-secondary" style="font-variation-settings:'FILL' 1;font-size:20px;">hub</span>
        </div>
        <div>
            <span class="text-xl font-bold text-secondary-fixed tracking-tight block">GVOS</span>
            <span class="text-xs text-on-primary-container">Operations Management Platform</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-border-subtle">
        {{-- Blue accent bar --}}
        <div class="h-1 w-full bg-secondary"></div>

        <div class="px-8 py-8 text-center">
            <div class="w-12 h-12 bg-surface-container-low rounded-xl flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-outline" style="font-size:24px;">person_off</span>
            </div>
            <h2 class="text-base font-semibold text-on-surface mb-2">Registration is not available</h2>
            <p class="text-sm text-on-surface-variant mb-6">
                GVOS accounts are created by administrators only.
                If you have been invited, please check your email for a sign-in link.
            </p>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary text-sm font-semibold px-6 py-2.5 rounded-lg transition-all shadow-sm">
                <span class="material-symbols-outlined" style="font-size:16px;">login</span>
                Go to Sign In
            </a>
        </div>
    </div>

</div>
</x-layouts.auth>
