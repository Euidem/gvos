<x-layouts.gvos :title="$workspace->name . ' — Password Vault'">

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-3">
            <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
            <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
            <span>Password Vault</span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 24px;">lock</span>
                    Password Vault
                </h1>
                <p class="text-[12px] text-outline mt-1">
                    {{ $workspace->workspace_code }}
                    &middot; {{ $items->count() }} visible item{{ $items->count() === 1 ? '' : 's' }}
                </p>
            </div>
            <div class="flex items-center gap-2 mt-1">
                @if ($canCreate)
                    <a href="{{ route('workspace.vault.create', $workspace) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                       style="background:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                        New Item
                    </a>
                @endif
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="inline-flex items-center gap-1.5 text-sm text-secondary hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    Workspace
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <x-portal.alert type="success" class="mb-4">{{ session('success') }}</x-portal.alert>
    @endif

    {{-- ── Security notice ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 mb-5">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(0,88,190,0.06);">
                <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">verified_user</span>
            </div>
            <div>
                <p class="text-sm font-semibold text-on-surface">Secrets are hidden by default</p>
                <p class="text-xs text-on-surface-variant mt-1">
                    This list shows credential metadata only. Secret values can be revealed from the item page when your role is allowed, and every reveal or copy is logged.
                </p>
            </div>
        </div>
    </div>

    {{-- ── Vault table ──────────────────────────────────────────────────────── --}}
    @if ($items->isEmpty())
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-12 text-center">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                 style="background:rgba(0,88,190,0.06);">
                <span class="material-symbols-outlined text-secondary" style="font-size: 26px;">lock</span>
            </div>
            <h4 class="text-sm font-semibold text-on-surface mb-1">No vault items visible</h4>
            <p class="text-xs text-outline max-w-xs mx-auto">
                @if ($canCreate)
                    Add the first encrypted credential for this workspace.
                @else
                    No credentials have been assigned to you in this workspace.
                @endif
            </p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-border-subtle flex items-center justify-between"
                 style="background:rgba(247,249,251,1);">
                <span class="text-xs font-semibold text-outline uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 14px;">key</span>
                    Credentials
                </span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                      style="background:rgba(0,88,190,0.08);color:#0058be;">{{ $items->count() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Title</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Category</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Username</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Visibility</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Status</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Last Revealed</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-outline"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @foreach ($items as $item)
                            @php
                                $canManageItem = $item->canManage(auth()->user(), $role);
                                $statusColor = $item->status === 'active' ? '#059669' : '#64748B';
                            @endphp
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('workspace.vault.show', [$workspace, $item]) }}"
                                       class="font-semibold text-on-surface hover:text-secondary transition-colors hover:underline">
                                        {{ $item->title }}
                                    </a>
                                    @if ($item->login_url)
                                        <p class="text-[11px] text-outline truncate max-w-xs mt-0.5">{{ $item->login_url }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-on-surface-variant">
                                    {{ $item->category ? $item->categoryLabel() : 'Other' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-on-surface-variant font-mono">
                                    {{ $item->username ?: '—' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background:rgba(0,88,190,0.08);color:#0058be;">
                                        {{ $item->visibilityLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background:{{ $statusColor }}18;color:{{ $statusColor }};">
                                        {{ $item->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-on-surface-variant whitespace-nowrap">
                                    {{ $item->last_revealed_at ? $item->last_revealed_at->format('d M Y H:i') : '—' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('workspace.vault.show', [$workspace, $item]) }}"
                                           class="text-xs font-semibold hover:underline" style="color:#0058be;">View</a>
                                        @if ($canManageItem)
                                            <a href="{{ route('workspace.vault.edit', [$workspace, $item]) }}"
                                               class="text-xs font-semibold hover:underline text-on-surface-variant">Edit</a>
                                            @if ($item->isActive())
                                                <form method="POST"
                                                      action="{{ route('workspace.vault.archive', [$workspace, $item]) }}"
                                                      onsubmit="return confirm('Archive this vault item?')">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold text-status-blocked hover:underline">
                                                        Archive
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-layouts.gvos>
