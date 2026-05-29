<x-layouts.auth title="Request Received">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">Operations Management Platform</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
        <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-xl font-bold text-slate-800 mb-3">Request Received</h2>

        <p class="text-sm text-slate-600 leading-relaxed mb-4">
            Thank you for submitting your service request. The GVOS team will review your
            information and respond with next steps, including a price estimate and trial details.
        </p>

        <p class="text-sm text-slate-500 leading-relaxed mb-6">
            Please check your email for a confirmation. Response times are typically
            within 1–2 business days.
        </p>

        <div class="space-y-3">
            <a href="{{ route('lead.request-service') }}"
               class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors">
                Submit Another Request
            </a>
            <a href="{{ route('login') }}"
               class="block w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-6 py-2.5 rounded-lg transition-colors">
                Sign In to GVOS
            </a>
        </div>
    </div>
</div>
</x-layouts.auth>
