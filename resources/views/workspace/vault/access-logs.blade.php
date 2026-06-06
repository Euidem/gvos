<x-layouts.gvos :title="$workspace->name . ' - Vault Access Logs'">

    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.vault.index', $workspace) }}" class="hover:text-secondary transition-colors">Password Vault</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.vault.show', [$workspace, $vaultItem]) }}" class="hover:text-secondary transition-colors">{{ $vaultItem->title }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Access Logs</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">history</span>
                Vault Access Logs
            </h2>
            <p class="text-xs text-outline mt-0.5">{{ $vaultItem->title }} &middot; Metadata only</p>
        </div>
        <a href="{{ route('workspace.vault.show', [$workspace, $vaultItem]) }}"
           class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
            Vault Item
        </a>
    </div>

    <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
        @if ($logs->isEmpty())
            <div class="p-12 text-center">
                <p class="text-sm text-outline">No access logs yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Action</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">User</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">IP Address</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">User Agent</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background:rgba(0,88,190,0.08);color:#0058be;">
                                        {{ $log->actionLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant">
                                    {{ $log->user->name ?? 'System' }}
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant max-w-md">
                                    {{ Str::limit($log->user_agent ?: '-', 90) }}
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant whitespace-nowrap">
                                    {{ $log->created_at->format('d M Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="px-4 py-3 border-t border-border-subtle">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>

</x-layouts.gvos>
