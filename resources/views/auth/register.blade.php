<x-layouts.auth title="Register">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">GetVirtual Operations System</p>
    </div>
    <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Registration is not available</h2>
        <p class="text-sm text-slate-500 mb-6">
            GVOS accounts are created by GetVirtual administrators only.
            If you have been invited, please check your email for a sign-in link.
        </p>
        <a href="{{ route('login') }}"
           class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors">
            Go to sign in
        </a>
    </div>
</div>
</x-layouts.auth>
