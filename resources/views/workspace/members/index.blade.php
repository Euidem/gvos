<x-layouts.gvos :title="$workspace->name . ' Members'">
    {{-- No dedicated Stitch screen - based on: workspace_settings_gvos and workspace_monitoring_gvos --}}
    <div class="max-w-6xl mx-auto space-y-6">
        @php
            $roleLabels = \App\Models\WorkspaceMember::roleLabels();
            $activeMembers = $workspace->activeMembers;
            $managerCount = $activeMembers->whereIn('role', ['manager', 'workspace_admin'])->count()
                + ($workspace->primaryManager ? 1 : 0);
            $talentCount = $activeMembers->where('role', 'talent')->count()
                + ($workspace->primaryTalent ? 1 : 0);
            $clientCount = $activeMembers->whereIn('role', ['client_admin', 'client_staff', 'client'])->count();
            $pendingInviteCount = $invitations->where('status', 'pending')->count();
        @endphp

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">Workspace Members</h2>
                <p class="text-sm text-on-surface-variant mt-1">{{ $workspace->name }} &middot; {{ $workspace->workspace_code }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant bg-white hover:border-secondary/20">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    Workspace
                </a>
                @if ($canInvite)
                    <a href="{{ route('workspace.members.invite', $workspace) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white hover:brightness-110"
                       style="background-color:#0058be">
                        <span class="material-symbols-outlined" style="font-size: 16px;">person_add</span>
                        Invite
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="bg-status-active/10 border border-status-active/20 rounded-xl px-5 py-4 text-sm font-medium text-status-active">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-status-blocked/10 border border-status-blocked/20 rounded-xl px-5 py-4">
                <p class="text-sm font-semibold text-status-blocked">Please check the member form.</p>
                <ul class="mt-2 text-xs text-status-blocked space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-5">
                <p class="text-xs font-semibold text-outline uppercase">Active Members</p>
                <p class="text-2xl font-bold text-on-surface mt-1">{{ $activeMembers->count() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-5">
                <p class="text-xs font-semibold text-outline uppercase">Managers</p>
                <p class="text-2xl font-bold text-on-surface mt-1">{{ $managerCount }}</p>
            </div>
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-5">
                <p class="text-xs font-semibold text-outline uppercase">Talent</p>
                <p class="text-2xl font-bold text-on-surface mt-1">{{ $talentCount }}</p>
            </div>
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-5">
                <p class="text-xs font-semibold text-outline uppercase">Client Team</p>
                <p class="text-2xl font-bold text-on-surface mt-1">{{ $clientCount }}</p>
            </div>
        </div>

        @if ($canManageMembers)
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
                <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">group_add</span>
                    Add Existing User
                </h3>
                <form method="POST" action="{{ route('workspace.members.store', $workspace) }}" class="grid grid-cols-1 lg:grid-cols-[1fr_220px_auto] gap-3">
                    @csrf
                    <select name="user_id" required class="rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20">
                        <option value="">Select user</option>
                        @foreach ($availableUsers as $availableUser)
                            <option value="{{ $availableUser->id }}" @selected(old('user_id') == $availableUser->id)>
                                {{ $availableUser->name }} ({{ $availableUser->email }})
                            </option>
                        @endforeach
                    </select>
                    <select name="workspace_role" required class="rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20">
                        @foreach ($allowedRoles as $allowedRole)
                            <option value="{{ $allowedRole }}" @selected(old('workspace_role') === $allowedRole)>
                                {{ $roleLabels[$allowedRole] ?? ucfirst(str_replace('_', ' ', $allowedRole)) }}
                            </option>
                        @endforeach
                    </select>
                    <button class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white hover:brightness-110" style="background-color:#0058be">
                        <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                        Add
                    </button>
                </form>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between">
                <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">people</span>
                    Active Team
                </h3>
                <span class="text-xs text-outline">{{ $activeMembers->count() }} active</span>
            </div>

            @if ($activeMembers->isEmpty())
                <div class="p-8 text-sm text-outline text-center">No active members yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-xs font-semibold text-outline uppercase">
                            <tr>
                                <th class="text-left px-6 py-3">Member</th>
                                <th class="text-left px-6 py-3">Platform Role</th>
                                <th class="text-left px-6 py-3">Workspace Role</th>
                                <th class="text-left px-6 py-3">Added</th>
                                <th class="text-left px-6 py-3">Last Activity</th>
                                @if ($canManageMembers)
                                    <th class="text-right px-6 py-3">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach ($activeMembers as $member)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-on-surface">{{ $member->user->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-outline">{{ $member->user->email ?? '' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant">
                                        {{ $member->user?->getRoleNames()->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', ') ?: 'No platform role' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border border-secondary/20 bg-secondary/5 text-secondary">
                                            {{ $member->roleLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant">{{ $member->joined_at?->format('d M Y') ?? 'Not recorded' }}</td>
                                    <td class="px-6 py-4 text-on-surface-variant">{{ $member->user?->updated_at?->diffForHumans() ?? 'Not available' }}</td>
                                    @if ($canManageMembers)
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-end gap-2">
                                                <form method="POST" action="{{ route('workspace.members.update', [$workspace, $member]) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="workspace_role" class="rounded-lg border-border-subtle text-xs focus:border-secondary focus:ring-secondary/20">
                                                        @foreach ($allowedRoles as $allowedRole)
                                                            <option value="{{ $allowedRole }}" @selected($member->role === $allowedRole)>
                                                                {{ $roleLabels[$allowedRole] ?? ucfirst(str_replace('_', ' ', $allowedRole)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/20">
                                                        Update
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('workspace.members.deactivate', [$workspace, $member]) }}" onsubmit="return confirm('Deactivate this workspace member?');">
                                                    @csrf
                                                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-status-blocked/20 text-status-blocked hover:bg-status-blocked/10">
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($canInvite)
            <div class="bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between">
                    <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">outgoing_mail</span>
                        Invitations
                    </h3>
                    <span class="text-xs text-outline">{{ $pendingInviteCount }} pending</span>
                </div>
                @if ($invitations->isEmpty())
                    <div class="p-8 text-sm text-outline text-center">No invitations have been created for this workspace.</div>
                @else
                    <div class="divide-y divide-border-subtle">
                        @foreach ($invitations as $invitation)
                            <div class="px-6 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="font-semibold text-on-surface">{{ $invitation->name ?: $invitation->email }}</p>
                                    <p class="text-xs text-outline">{{ $invitation->email }} &middot; {{ $roleLabels[$invitation->workspace_role] ?? $invitation->workspace_role }}</p>
                                    <p class="text-xs text-outline mt-1">Invited by {{ $invitation->inviter?->name ?? 'System' }} &middot; {{ $invitation->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border
                                        {{ match($invitation->status) {
                                            'pending' => 'bg-status-payment-due/10 text-status-payment-due border-status-payment-due/20',
                                            'accepted' => 'bg-status-active/10 text-status-active border-status-active/20',
                                            'revoked', 'expired' => 'bg-status-blocked/10 text-status-blocked border-status-blocked/20',
                                            default => 'bg-surface-container-low text-outline border-border-subtle',
                                        } }}">
                                        {{ \App\Models\WorkspaceInvitation::statusLabels()[$invitation->status] ?? ucfirst($invitation->status) }}
                                    </span>
                                    @if ($invitation->status === 'pending')
                                        <form method="POST" action="{{ route('workspace.members.invitations.resend', [$workspace, $invitation]) }}">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/20">Resend</button>
                                        </form>
                                        <form method="POST" action="{{ route('workspace.members.invitations.revoke', [$workspace, $invitation]) }}" onsubmit="return confirm('Revoke this invitation?');">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-status-blocked/20 text-status-blocked hover:bg-status-blocked/10">Revoke</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.gvos>
