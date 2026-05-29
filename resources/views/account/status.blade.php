<x-layouts.auth title="Account Status" variant="light">
<div class="w-full max-w-[640px]">

    {{-- Alert Banner --}}
    <div class="bg-status-blocked/10 border-l-4 border-status-blocked p-6 flex items-start gap-4 rounded-r-xl mb-6">
        <span class="material-symbols-outlined text-status-blocked flex-shrink-0" style="font-variation-settings: 'FILL' 1; font-size: 24px;">report</span>
        <div>
            <h2 class="text-xs font-semibold text-status-blocked mb-1 uppercase tracking-wider">
                @if(auth()->user()->status === 'suspended')
                    IMMEDIATE ACTION REQUIRED
                @else
                    ACCOUNT INACTIVE
                @endif
            </h2>
            <p class="text-sm text-on-surface-variant">
                @if(auth()->user()->status === 'suspended')
                    Your workspace has been deactivated by the GVOS Security Layer. Core operations are currently offline.
                @else
                    Your account has not yet been activated. Please contact your administrator to restore access.
                @endif
            </p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-surface-container-lowest border border-border-subtle rounded-xl shadow-lg overflow-hidden">
        <div class="h-1 w-full bg-secondary"></div>

        <div class="p-card-padding">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-on-surface-variant" style="font-size: 24px;">lock_person</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-on-surface">
                        {{ auth()->user()->status === 'suspended' ? 'Account Suspended' : 'Account Inactive' }}
                    </h1>
                    <p class="text-xs text-outline uppercase tracking-wide mt-0.5">
                        Signed in as {{ auth()->user()->email }}
                    </p>
                </div>
            </div>

            @if(auth()->user()->status === 'suspended')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 bg-surface-container-low border border-border-subtle rounded-lg">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-status-payment-due" style="font-size: 18px;">credit_card</span>
                            <span class="text-xs font-semibold text-on-surface uppercase">Billing Status</span>
                        </div>
                        <p class="text-xs text-on-surface-variant">There may be a pending payment issue on your account.</p>
                    </div>
                    <div class="p-4 bg-surface-container-low border border-border-subtle rounded-lg">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-status-suspended" style="font-size: 18px;">gavel</span>
                            <span class="text-xs font-semibold text-on-surface uppercase">Security Review</span>
                        </div>
                        <p class="text-xs text-on-surface-variant">Your workspace may be undergoing a compliance review.</p>
                    </div>
                </div>
            @else
                <p class="text-sm text-on-surface-variant mb-6">
                    Your account is pending activation. Please contact the GVOS operations team or your administrator to enable access.
                </p>
            @endif

            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit"
                            class="w-full sm:w-auto px-8 py-3 bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary font-semibold text-sm rounded-lg transition-all shadow-md flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 16px;">logout</span>
                        Sign Out
                    </button>
                </form>
                <a href="mailto:support@gvos.io"
                   class="w-full sm:w-auto px-8 py-3 bg-surface-container-lowest border border-border-subtle text-on-surface font-semibold text-sm rounded-lg hover:bg-surface-container-low transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 16px;">support_agent</span>
                    Contact Support
                </a>
            </div>
        </div>

        {{-- Footer note --}}
        <div class="px-card-padding pb-card-padding">
            <div class="flex items-center gap-2 text-on-surface-variant/60 text-[11px] pt-4 border-t border-border-subtle">
                <span class="material-symbols-outlined" style="font-size: 14px;">info</span>
                Data retention is guaranteed for 30 days from deactivation. Contact GVOS support for assistance.
            </div>
        </div>
    </div>

</div>
</x-layouts.auth>
