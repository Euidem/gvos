<x-layouts.public title="Workspace Invitation">
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl border border-border-subtle shadow-card p-8">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background-color:rgba(0,88,190,.08);">
                <span class="material-symbols-outlined text-secondary" style="font-size: 24px;">outgoing_mail</span>
            </div>

            <h1 class="text-2xl font-bold text-on-surface">Workspace Invitation</h1>
            <p class="text-sm text-on-surface-variant mt-2">
                You have been invited to join {{ $invitation->workspace?->name ?? 'a GVOS workspace' }} as
                {{ \App\Models\WorkspaceMember::roleLabels()[$invitation->workspace_role] ?? ucfirst(str_replace('_', ' ', $invitation->workspace_role)) }}.
            </p>

            @if ($errors->any())
                <div class="bg-status-blocked/10 border border-status-blocked/20 rounded-xl px-4 py-3 mt-5 text-sm text-status-blocked">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mt-6 rounded-xl border border-border-subtle bg-surface-container-low p-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-outline">Email</span>
                    <span class="font-semibold text-on-surface text-right">{{ $invitation->email }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-outline">Status</span>
                    <span class="font-semibold text-on-surface">{{ \App\Models\WorkspaceInvitation::statusLabels()[$invitation->status] ?? ucfirst($invitation->status) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-outline">Expires</span>
                    <span class="font-semibold text-on-surface">{{ $invitation->expires_at?->format('d M Y H:i') ?? 'Not set' }}</span>
                </div>
            </div>

            @if ($invitation->isPending())
                @auth
                    <form method="POST" action="{{ route('workspace.invitations.accept', $invitation->token) }}" class="mt-6">
                        @csrf
                        <button class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg text-sm font-semibold text-white hover:brightness-110" style="background-color:#0058be">
                            <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                            Accept Invitation
                        </button>
                    </form>
                @else
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg text-sm font-semibold text-white hover:brightness-110" style="background-color:#0058be">
                            <span class="material-symbols-outlined" style="font-size: 16px;">login</span>
                            Sign In to Accept
                        </a>
                        <p class="text-xs text-outline text-center">
                            If you do not have an account yet, contact your workspace admin to activate your GVOS account.
                        </p>
                    </div>
                @endauth
            @else
                <p class="text-sm text-outline mt-6">
                    This invitation cannot be accepted. Contact your workspace admin if you need a new invitation.
                </p>
            @endif
        </div>
    </div>
</x-layouts.public>
