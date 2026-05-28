<x-layouts.auth title="Confirm Password">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
    </div>
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-3">Confirm your password</h2>
        <p class="text-sm text-slate-500 mb-6">
            This is a secure area. Please confirm your password before continuing.
        </p>
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required autofocus
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors">
                Confirm
            </button>
        </form>
    </div>
</div>
</x-layouts.auth>
