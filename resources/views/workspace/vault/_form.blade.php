@php
    $isEdit = isset($vaultItem);
    $selectedRoles = old('allowed_roles', $isEdit ? $vaultItem->allowedRoleValues() : []);
    $selectedUserIds = collect(old('allowed_user_ids', $isEdit ? $vaultItem->allowedUserIdValues() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="space-y-5">
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-6">
        <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">lock</span>
            Credential Details
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-on-surface mb-1">Title <span class="text-status-blocked">*</span></label>
                <input type="text"
                       name="title"
                       value="{{ old('title', $vaultItem->title ?? '') }}"
                       maxlength="255"
                       required
                       class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                       placeholder="e.g. Client WordPress Admin">
            </div>

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Category</label>
                <select name="category"
                        class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                    <option value="">Select category</option>
                    @foreach ($categoryLabels as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $vaultItem->category ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Username</label>
                <input type="text"
                       name="username"
                       value="{{ old('username', $vaultItem->username ?? '') }}"
                       maxlength="255"
                       class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                       placeholder="username or email">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-on-surface mb-1">Login URL</label>
                <input type="url"
                       name="login_url"
                       value="{{ old('login_url', $vaultItem->login_url ?? '') }}"
                       maxlength="2048"
                       class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                       placeholder="https://example.com/login">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-on-surface mb-1">
                    Secret {{ $isEdit ? '' : '*' }}
                </label>
                <input type="password"
                       name="secret_value"
                       value=""
                       maxlength="10000"
                       {{ $isEdit ? '' : 'required' }}
                       autocomplete="new-password"
                       class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                       placeholder="{{ $isEdit ? 'Leave blank to keep the current secret' : 'Enter password, API key, or credential secret' }}">
                <p class="text-[11px] text-outline mt-1">
                    {{ $isEdit ? 'Existing secret values are never prefilled. Enter a new value only when rotating the secret.' : 'Secrets are encrypted before storage and hidden from list views.' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-on-surface mb-1">Notes</label>
                <textarea name="notes"
                          rows="3"
                          maxlength="5000"
                          class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]"
                          placeholder="Optional context. Do not paste extra secrets here.">{{ old('notes', $vaultItem->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-6">
        <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">admin_panel_settings</span>
            Access Control
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Visibility <span class="text-status-blocked">*</span></label>
                <select name="visibility"
                        required
                        class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                    @foreach ($visibilityLabels as $value => $label)
                        <option value="{{ $value }}" {{ old('visibility', $vaultItem->visibility ?? 'restricted') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1">Status <span class="text-status-blocked">*</span></label>
                <select name="status"
                        required
                        class="w-full rounded-lg px-3 py-2 text-sm border border-border-subtle focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $vaultItem->status ?? 'active') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div>
                <p class="text-xs font-semibold text-on-surface mb-2">Allowed roles</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($allowedRoleOptions as $value => $label)
                        <label class="flex items-center gap-2 rounded-lg border border-border-subtle px-3 py-2 text-xs text-on-surface-variant">
                            <input type="checkbox"
                                   name="allowed_roles[]"
                                   value="{{ $value }}"
                                   class="rounded border-border-subtle"
                                   {{ in_array($value, $selectedRoles, true) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[11px] text-outline mt-2">Role grants are explicit. Talent and staff only reveal secrets when directly allowed.</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-on-surface mb-2">Allowed users</p>
                @if ($workspaceUsers->isEmpty())
                    <div class="rounded-lg border border-dashed border-border-subtle p-4 text-xs text-outline">
                        No workspace users are available for assignment yet.
                    </div>
                @else
                    <div class="max-h-56 overflow-y-auto rounded-lg border border-border-subtle divide-y divide-[#F1F5F9]">
                        @foreach ($workspaceUsers as $workspaceUser)
                            <label class="flex items-center gap-3 px-3 py-2 text-xs text-on-surface-variant">
                                <input type="checkbox"
                                       name="allowed_user_ids[]"
                                       value="{{ $workspaceUser->id }}"
                                       class="rounded border-border-subtle"
                                       {{ in_array((int) $workspaceUser->id, $selectedUserIds, true) ? 'checked' : '' }}>
                                <span>
                                    <span class="font-semibold text-on-surface">{{ $workspaceUser->name }}</span>
                                    <span class="text-outline">{{ $workspaceUser->email }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                <p class="text-[11px] text-outline mt-2">Only active workspace members, primary team, and task assignees can be selected.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ $isEdit ? route('workspace.vault.show', [$workspace, $vaultItem]) : route('workspace.vault.index', $workspace) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold border border-border-subtle text-on-surface-variant">
            Cancel
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold text-white hover:brightness-110"
                style="background-color:#0058be;">
            <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
            {{ $isEdit ? 'Save Changes' : 'Create Vault Item' }}
        </button>
    </div>
</div>
