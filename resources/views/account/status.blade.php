<x-layouts.auth title="Account Status">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">Operations Management Platform</p>
    </div>
    <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
        <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4
            @if(auth()->user()->status === 'suspended') bg-red-50 @else bg-slate-100 @endif">
            <svg class="w-6 h-6 @if(auth()->user()->status === 'suspended') text-red-500 @else text-slate-400 @endif"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>

        @if (auth()->user()->status === 'suspended')
            <h2 class="text-lg font-semibold text-slate-800 mb-2">Account Suspended</h2>
            <p class="text-sm text-slate-500 mb-6">
                Your account has been suspended. Please contact the GVOS support team
                for more information.
            </p>
        @else
            <h2 class="text-lg font-semibold text-slate-800 mb-2">Account Inactive</h2>
            <p class="text-sm text-slate-500 mb-6">
                Your account is currently inactive. Please contact your administrator
                to reactivate access.
            </p>
        @endif

        <p class="text-xs text-slate-400 mb-6">
            Signed in as <span class="font-medium">{{ auth()->user()->email }}</span>
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors">
                Sign Out
            </button>
        </form>
    </div>
</div>
</x-layouts.auth>
