<x-layouts.gvos title="Welcome to GVOS">
@php $user = auth()->user(); $profile = $user->profile; @endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">Your GVOS account is being set up</p>
        </div>
        <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full font-medium">
            Active Lead
        </span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 px-8 py-10 text-center max-w-lg mx-auto mb-8">
        <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-800 mb-2">Onboarding in progress</h3>
        <p class="text-sm text-slate-500 mb-6">
            Our team is reviewing your information and will be in touch shortly to complete your onboarding and assign your workspace.
        </p>
        <a href="{{ route('profile.show') }}"
           class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
            Complete My Profile
        </a>
    </div>

    <p class="text-xs text-slate-400 text-center">
        Questions? Contact the GVOS support team via your onboarding email.
    </p>

</x-layouts.gvos>
