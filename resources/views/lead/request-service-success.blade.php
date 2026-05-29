<x-layouts.auth title="Request Received">
<div class="w-full max-w-md">

    {{-- Branding --}}
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">Operations Management Platform</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

        {{-- Top accent stripe --}}
        <div class="h-1.5 bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-500"></div>

        <div class="px-8 py-8 text-center">

            {{-- Success icon --}}
            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-5">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            {{-- Heading --}}
            <h2 class="text-2xl font-bold text-slate-800 mb-2">We've got your request!</h2>
            <p class="text-sm text-slate-500 leading-relaxed mb-7">
                Thank you for reaching out. The GVOS team has received your service request
                and will review your details carefully.
            </p>

            {{-- What happens next --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl px-5 py-5 text-left mb-7">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">What happens next</p>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-emerald-500 text-white text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Check your email</p>
                            <p class="text-xs text-slate-500 mt-0.5">A confirmation has been sent to the address you provided.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-indigo-100 text-indigo-600 text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">We review your needs</p>
                            <p class="text-xs text-slate-500 mt-0.5">Our team typically responds within 1–2 business days.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-indigo-100 text-indigo-600 text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">We prepare your estimate</p>
                            <p class="text-xs text-slate-500 mt-0.5">Based on your role, hours, and budget preferences.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-indigo-100 text-indigo-600 text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">4</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">If it fits — a one-day trial</p>
                            <p class="text-xs text-slate-500 mt-0.5">No commitment required until you're fully satisfied.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-3">
                <a href="{{ route('login') }}"
                   class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-3 rounded-xl transition-colors">
                    Sign In to GVOS
                </a>
                <a href="{{ route('lead.request-service') }}"
                   class="block w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium px-6 py-3 rounded-xl transition-colors">
                    Submit Another Request
                </a>
            </div>

        </div>
    </div>

    <p class="text-center text-xs text-slate-500 mt-6">
        Have questions? Contact the GVOS support team via your onboarding email.
    </p>

</div>
</x-layouts.auth>
