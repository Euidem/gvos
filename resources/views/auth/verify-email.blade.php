<x-layouts.auth title="Verify Email">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
    </div>
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-3">Verify your email</h2>
        <p class="text-sm text-slate-500 mb-6">
            A verification link has been sent to your email address. Please check your inbox.
        </p>
        @if (session('status') === 'verification-link-sent')
            <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                A new verification link has been sent.
            </div>
        @endif
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors mb-3">
                Resend verification email
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-sm text-slate-500 hover:text-slate-700 transition-colors">
                Sign out
            </button>
        </form>
    </div>
</div>
</x-layouts.auth>
