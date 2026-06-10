<x-layouts.gvos :title="$workspace->name . ' Invitation'">
    {{-- No dedicated Stitch screen - based on: workspace_settings_gvos --}}
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">Invite Member</h2>
                <p class="text-sm text-on-surface-variant mt-1">{{ $workspace->name }} &middot; email delivery depends on configured mail settings.</p>
            </div>
            <a href="{{ route('workspace.members.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant bg-white hover:border-secondary/20">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Members
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-status-blocked/10 border border-status-blocked/20 rounded-xl px-5 py-4">
                <p class="text-sm font-semibold text-status-blocked">Please check the invitation form.</p>
                <ul class="mt-2 text-xs text-status-blocked space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('workspace.members.invite.store', $workspace) }}" class="bg-white rounded-xl border border-border-subtle shadow-card p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20">
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Workspace Role</label>
                    <select name="workspace_role" required class="w-full rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20">
                        @foreach ($allowedRoles as $allowedRole)
                            <option value="{{ $allowedRole }}" @selected(old('workspace_role') === $allowedRole)>
                                {{ \App\Models\WorkspaceMember::roleLabels()[$allowedRole] ?? ucfirst(str_replace('_', ' ', $allowedRole)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Platform Role</label>
                    <select name="platform_role" class="w-full rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20">
                        <option value="">Not assigned yet</option>
                        @foreach (\App\Models\WorkspaceInvitation::platformRoleLabels() as $platformRole => $label)
                            <option value="{{ $platformRole }}" @selected(old('platform_role') === $platformRole)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="text-xs text-outline">
                New invitees who do not have a GVOS account will be prompted to create one directly from the invitation link. Existing users will see a sign-in prompt.
            </p>
            <div class="flex justify-end">
                <button class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white hover:brightness-110" style="background-color:#0058be">
                    <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                    Send Invitation
                </button>
            </div>
        </form>
    </div>
</x-layouts.gvos>
