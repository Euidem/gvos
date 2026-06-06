<x-layouts.gvos :title="$workspace->name . ' - New Vault Item'">

    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.vault.index', $workspace) }}" class="hover:text-secondary transition-colors">Password Vault</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>New Item</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">add_moderator</span>
                New Vault Item
            </h2>
            <p class="text-xs text-outline mt-0.5">{{ $workspace->workspace_code }} &middot; Add an encrypted credential</p>
        </div>
        <a href="{{ route('workspace.vault.index', $workspace) }}"
           class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
            Vault
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 rounded-lg text-sm"
             style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('workspace.vault.store', $workspace) }}">
        @csrf
        @include('workspace.vault._form')
    </form>

</x-layouts.gvos>
