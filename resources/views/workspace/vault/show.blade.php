<x-layouts.gvos :title="$workspace->name . ' - Vault Item'">

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-3">
            <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
            <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
            <a href="{{ route('workspace.vault.index', $workspace) }}" class="hover:text-secondary transition-colors">Password Vault</a>
            <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
            <span class="text-outline">{{ $vaultItem->title }}</span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 24px;">lock_open</span>
                    {{ $vaultItem->title }}
                </h1>
                <p class="text-[12px] text-outline mt-1">
                    {{ $workspace->workspace_code }} &middot; {{ $vaultItem->visibilityLabel() }}
                </p>
            </div>
            <div class="flex items-center gap-2 mt-1">
                @if ($canViewLogs)
                    <a href="{{ route('workspace.vault.access-logs', [$workspace, $vaultItem]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition-all"
                       style="border-color:#0058be;color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 14px;">history</span>
                        Access Logs
                    </a>
                @endif
                @if ($canManage)
                    <a href="{{ route('workspace.vault.edit', [$workspace, $vaultItem]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/20 transition-all">
                        <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                        Edit
                    </a>
                @endif
                <a href="{{ route('workspace.vault.index', $workspace) }}"
                   class="inline-flex items-center gap-1.5 text-sm text-secondary hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    Vault
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <x-portal.alert type="success" class="mb-4">{{ session('success') }}</x-portal.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Main column ──────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Metadata card --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle flex items-center gap-2"
                     style="background:rgba(247,249,251,1);">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">badge</span>
                    <h3 class="text-xs font-semibold text-outline uppercase tracking-wider">Metadata</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-1.5">Category</p>
                        <p class="text-on-surface font-medium">{{ $vaultItem->category ? $vaultItem->categoryLabel() : 'Other' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-1.5">Status</p>
                        @php $stColor = $vaultItem->status === 'active' ? '#059669' : '#64748B'; @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                              style="background:{{ $stColor }}18;color:{{ $stColor }};">
                            {{ $vaultItem->statusLabel() }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-1.5">Username</p>
                        <p class="text-on-surface font-mono text-sm">{{ $vaultItem->username ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-1.5">Login URL</p>
                        @if ($vaultItem->login_url)
                            <a href="{{ $vaultItem->login_url }}" target="_blank" rel="noopener noreferrer"
                               class="text-secondary hover:underline break-all text-sm">{{ $vaultItem->login_url }}</a>
                        @else
                            <p class="text-on-surface">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-1.5">Created by</p>
                        <p class="text-on-surface">{{ $vaultItem->createdBy->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-1.5">Last revealed</p>
                        <p class="text-on-surface">
                            {{ $vaultItem->last_revealed_at ? $vaultItem->last_revealed_at->format('d M Y H:i') : '—' }}
                            @if ($vaultItem->lastRevealedBy)
                                <span class="text-outline text-xs">by {{ $vaultItem->lastRevealedBy->name }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if ($vaultItem->notes)
                    <div class="mx-6 mb-6 pt-5 border-t border-border-subtle">
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-1.5">Notes</p>
                        <p class="text-sm text-on-surface-variant whitespace-pre-line">{{ $vaultItem->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Secret reveal card --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle flex items-center gap-2"
                     style="background:rgba(247,249,251,1);">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">key</span>
                    <h3 class="text-xs font-semibold text-outline uppercase tracking-wider">Secret</h3>
                </div>
                <div class="p-6">
                    @if ($canReveal)
                        <div class="rounded-lg border p-4 mb-5"
                             style="background:rgba(245,158,11,0.06);border-color:rgba(245,158,11,0.20);">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-status-payment-due flex-shrink-0" style="font-size: 20px;">warning</span>
                                <p class="text-xs text-on-surface-variant">
                                    Only reveal credentials when needed. Every reveal and copy is recorded for security.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-3">
                            <input id="vault-secret-output"
                                   type="password"
                                   readonly
                                   autocomplete="off"
                                   placeholder="Secret hidden"
                                   class="flex-1 rounded-lg px-3 py-2.5 text-sm border border-border-subtle bg-surface-container-low focus:outline-none font-mono">
                            <button id="vault-reveal-button"
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white hover:brightness-110 transition-all"
                                    style="background:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                Reveal
                            </button>
                            <button id="vault-copy-button"
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold border transition-all"
                                    style="border-color:#0058be;color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">content_copy</span>
                                Copy
                            </button>
                        </div>
                        <p id="vault-secret-status" class="text-xs text-outline mt-2"></p>
                    @else
                        <div class="rounded-lg border border-dashed border-border-subtle p-6 text-sm text-on-surface-variant text-center">
                            <span class="material-symbols-outlined text-outline block mb-2" style="font-size:28px;">lock</span>
                            You can view this credential's metadata, but your current access does not allow secret reveal.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Sidebar ───────────────────────────────────────────────────────── --}}
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border-subtle"
                     style="background:rgba(247,249,251,1);">
                    <h3 class="text-xs font-semibold text-outline uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-secondary" style="font-size: 14px;">groups</span>
                        Access
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-2">Allowed roles</p>
                        @if (empty($vaultItem->allowedRoleValues()))
                            <p class="text-xs text-outline">No explicit roles</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($vaultItem->allowedRoleValues() as $allowedRole)
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                          style="background:rgba(0,88,190,0.08);color:#0058be;">
                                        {{ \App\Models\WorkspaceVaultItem::allowedRoleOptions()[$allowedRole] ?? ucfirst(str_replace('_', ' ', $allowedRole)) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-outline uppercase tracking-wider mb-2">Allowed users</p>
                        @if ($allowedUsers->isEmpty())
                            <p class="text-xs text-outline">No explicit users</p>
                        @else
                            <div class="space-y-1.5">
                                @foreach ($allowedUsers as $allowedUser)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0"
                                             style="background:#0058be;">{{ strtoupper(substr($allowedUser->name, 0, 1)) }}</div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-on-surface truncate">{{ $allowedUser->name }}</p>
                                            <p class="text-[10px] text-outline truncate">{{ $allowedUser->email }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($canViewLogs)
                <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-border-subtle flex items-center justify-between"
                         style="background:rgba(247,249,251,1);">
                        <h3 class="text-xs font-semibold text-outline uppercase tracking-wider">Recent Access</h3>
                        <a href="{{ route('workspace.vault.access-logs', [$workspace, $vaultItem]) }}"
                           class="text-[11px] font-semibold hover:underline" style="color:#0058be;">View all</a>
                    </div>
                    @if ($recentLogs->isEmpty())
                        <div class="p-5 text-xs text-outline">No access events yet.</div>
                    @else
                        <div class="divide-y divide-border-subtle">
                            @foreach ($recentLogs as $log)
                                <div class="px-5 py-3">
                                    <p class="text-xs font-semibold text-on-surface">{{ $log->actionLabel() }}</p>
                                    <p class="text-[11px] text-outline mt-0.5">
                                        {{ $log->user->name ?? 'System' }} &middot; {{ $log->created_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($canReveal)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var revealUrl = @json(route('workspace.vault.reveal', [$workspace, $vaultItem]));
                var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                var output = document.getElementById('vault-secret-output');
                var status = document.getElementById('vault-secret-status');
                var revealButton = document.getElementById('vault-reveal-button');
                var copyButton = document.getElementById('vault-copy-button');

                function setBusy(isBusy) {
                    revealButton.disabled = isBusy;
                    copyButton.disabled = isBusy;
                    revealButton.classList.toggle('opacity-60', isBusy);
                    copyButton.classList.toggle('opacity-60', isBusy);
                }

                function requestSecret(action) {
                    setBusy(true);
                    status.textContent = 'Requesting access...';

                    return fetch(revealUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ action: action })
                    }).then(function (response) {
                        if (! response.ok) {
                            throw new Error('Secret could not be retrieved.');
                        }
                        return response.json();
                    }).finally(function () {
                        setBusy(false);
                    });
                }

                revealButton.addEventListener('click', function () {
                    requestSecret('revealed_secret')
                        .then(function (data) {
                            output.type = 'text';
                            output.value = data.secret;
                            status.textContent = 'Secret revealed. This access was logged.';
                        })
                        .catch(function (error) {
                            status.textContent = error.message;
                        });
                });

                copyButton.addEventListener('click', function () {
                    requestSecret('copied_secret')
                        .then(function (data) {
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                return navigator.clipboard.writeText(data.secret);
                            }

                            output.type = 'text';
                            output.value = data.secret;
                            output.select();
                            document.execCommand('copy');
                        })
                        .then(function () {
                            status.textContent = 'Secret copied. This access was logged.';
                        })
                        .catch(function (error) {
                            status.textContent = error.message;
                        });
                });
            });
        </script>
    @endif

</x-layouts.gvos>
