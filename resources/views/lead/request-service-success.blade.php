<x-layouts.auth title="Request Received">
<!-- GVOS UI Fidelity v2 active -->
<div class="w-full max-w-md">

    {{-- Branding --}}
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

        {{-- Top accent stripe --}}
        <div class="h-1.5 bg-secondary"></div>

        <div class="px-8 py-8 text-center">

            {{-- Success icon --}}
            <div class="w-16 h-16 bg-status-active/10 rounded-full flex items-center justify-center mx-auto mb-5">
                <div class="w-12 h-12 bg-status-active/20 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-status-active" style="font-variation-settings:'FILL' 1;font-size:24px;">check_circle</span>
                </div>
            </div>

            {{-- Heading --}}
            <h2 class="text-2xl font-bold text-on-surface mb-2">We've got your request!</h2>
            <p class="text-sm text-on-surface-variant leading-relaxed mb-7">
                Thank you for reaching out. The GVOS team has received your service request
                and will review your details carefully.
            </p>

            {{-- What happens next --}}
            <div class="bg-surface-container-low border border-border-subtle rounded-xl px-5 py-5 text-left mb-7">
                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-4">What happens next</p>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-secondary text-on-secondary text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                        <div>
                            <p class="text-sm font-semibold text-on-surface">Check your email</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">A confirmation has been sent to the address you provided.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-secondary/10 text-secondary text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                        <div>
                            <p class="text-sm font-semibold text-on-surface">We review your needs</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">Our team typically responds within 1–2 business days.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-secondary/10 text-secondary text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                        <div>
                            <p class="text-sm font-semibold text-on-surface">We prepare your estimate</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">Based on your role, hours, and budget preferences.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-secondary/10 text-secondary text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">4</div>
                        <div>
                            <p class="text-sm font-semibold text-on-surface">If it fits — a one-day trial</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">No commitment required until you're fully satisfied.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-3">
                <a href="{{ route('login') }}"
                   class="flex items-center justify-center gap-2 w-full bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary text-sm font-semibold px-6 py-3 rounded-xl transition-all shadow-sm">
                    <span class="material-symbols-outlined" style="font-size:16px;">login</span>
                    Sign In to GVOS
                </a>
                <a href="{{ route('lead.request-service') }}"
                   class="block w-full text-center bg-surface-container-low hover:bg-surface-container text-on-surface-variant text-sm font-medium px-6 py-3 rounded-xl transition-colors">
                    Submit Another Request
                </a>
            </div>

        </div>
    </div>

    <p class="text-center text-xs text-on-primary-container mt-6">
        Have questions? Contact the GVOS support team via your onboarding email.
    </p>

</div>
</x-layouts.auth>
