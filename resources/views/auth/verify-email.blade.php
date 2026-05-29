<x-layouts.auth title="Verify Email">
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
                <span class="material-symbols-outlined text-secondary" style="font-size:24px;">mark_email_unread</span>
            </div>
            <h2 class="text-lg font-bold text-on-surface mb-2 text-center">Verify your email</h2>
            <p class="text-sm text-on-surface-variant mb-6 text-center">
                A verification link has been sent to your email address. Please check your inbox.
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="mb-5 bg-status-active/10 border border-status-active/20 rounded-lg px-4 py-3 text-sm text-status-active flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
                    A new verification link has been sent.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                @csrf
                <button type="submit"
                        class="w-full bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary font-semibold text-sm py-2.5 rounded-lg transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                    Resend verification email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-sm text-on-surface-variant hover:text-on-surface transition-colors py-2 text-center">
                    Sign out
                </button>
            </form>
        </div>
    </div>

</div>
</x-layouts.auth>
