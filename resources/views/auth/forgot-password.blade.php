<x-layouts.auth title="Reset Password">
<div class="w-full max-w-sm">

    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">GetVirtual Operations System</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-2">Reset your password</h2>
        <p class="text-sm text-slate-500 mb-6">
            Enter your email and we will send you a password reset link.
        </p>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       required autofocus
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors">
                Send reset link
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Back to sign in
            </a>
        </div>
    </div>
</div>
</x-layouts.auth>
