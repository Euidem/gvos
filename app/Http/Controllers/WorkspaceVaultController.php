<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceVaultAccessLog;
use App\Models\WorkspaceVaultItem;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkspaceVaultController extends Controller
{
    private function requireAccess(Request $request, Workspace $workspace): string
    {
        $role = $workspace->resolveUserWorkspaceRole($request->user());

        if (! WorkspaceVaultItem::canUseVaultRole($role)) {
            abort(403, 'You do not have access to this vault.');
        }

        return $role;
    }

    private function authorizeBelongsToWorkspace(Workspace $workspace, WorkspaceVaultItem $vaultItem): void
    {
        if ((int) $vaultItem->workspace_id !== (int) $workspace->id) {
            abort(404, 'Vault item not found in this workspace.');
        }
    }

    private function workspaceUsers(Workspace $workspace)
    {
        $memberIds = $workspace->activeMembers()->pluck('user_id');
        $primaryIds = collect([$workspace->primary_manager_id, $workspace->primary_talent_id])->filter();
        $taskAssigneeIds = $workspace->tasks()
            ->whereNotNull('assigned_to_user_id')
            ->pluck('assigned_to_user_id');

        $userIds = $memberIds
            ->merge($primaryIds)
            ->merge($taskAssigneeIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function validateAllowedUserIds(Workspace $workspace, array $userIds): array
    {
        $userIds = collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($userIds)) {
            return [];
        }

        $validIds = $this->workspaceUsers($workspace)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $invalidIds = array_values(array_diff($userIds, $validIds));

        if (! empty($invalidIds)) {
            throw ValidationException::withMessages([
                'allowed_user_ids' => 'Allowed users must belong to this workspace.',
            ]);
        }

        return $userIds;
    }

    private function validateVaultItem(Request $request, Workspace $workspace, ?WorkspaceVaultItem $vaultItem = null): array
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'category'           => 'nullable|string|max:100',
            'login_url'          => 'nullable|url|max:2048',
            'username'           => 'nullable|string|max:255',
            'secret_value'       => ($vaultItem ? 'nullable' : 'required') . '|string|max:10000',
            'notes'              => 'nullable|string|max:5000',
            'visibility'         => 'required|in:' . implode(',', array_keys(WorkspaceVaultItem::visibilityLabels())),
            'status'             => 'required|in:' . implode(',', array_keys(WorkspaceVaultItem::statusLabels())),
            'allowed_roles'      => 'nullable|array',
            'allowed_roles.*'    => 'in:' . implode(',', array_keys(WorkspaceVaultItem::allowedRoleOptions())),
            'allowed_user_ids'   => 'nullable|array',
            'allowed_user_ids.*' => 'integer',
        ]);

        $validated['allowed_roles'] = collect($validated['allowed_roles'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $validated['allowed_user_ids'] = $this->validateAllowedUserIds(
            $workspace,
            $validated['allowed_user_ids'] ?? [],
        );

        if ($vaultItem && blank($validated['secret_value'] ?? null)) {
            unset($validated['secret_value']);
        }

        return $validated;
    }

    public function index(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        $items = WorkspaceVaultItem::queryForUser($workspace, $user, $role)
            ->with(['createdBy', 'lastRevealedBy'])
            ->orderByRaw("status = 'archived' asc")
            ->orderBy('title')
            ->get();

        $canCreate = WorkspaceVaultItem::canCreateForRole($role);

        return view('workspace.vault.index', compact('workspace', 'items', 'role', 'canCreate'));
    }

    public function create(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! WorkspaceVaultItem::canCreateForRole($role)) {
            abort(403, 'You cannot create vault items in this workspace.');
        }

        $workspaceUsers = $this->workspaceUsers($workspace);
        $visibilityLabels = WorkspaceVaultItem::visibilityLabels();
        $statusLabels = WorkspaceVaultItem::statusLabels();
        $categoryLabels = WorkspaceVaultItem::categoryLabels();
        $allowedRoleOptions = WorkspaceVaultItem::allowedRoleOptions();

        return view('workspace.vault.create', compact(
            'workspace',
            'workspaceUsers',
            'visibilityLabels',
            'statusLabels',
            'categoryLabels',
            'allowedRoleOptions',
            'role',
        ));
    }

    public function store(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! WorkspaceVaultItem::canCreateForRole($role)) {
            abort(403, 'You cannot create vault items in this workspace.');
        }

        $validated = $this->validateVaultItem($request, $workspace);

        $vaultItem = WorkspaceVaultItem::create(array_merge($validated, [
            'workspace_id' => $workspace->id,
            'created_by'   => $request->user()->id,
            'updated_by'   => $request->user()->id,
        ]));

        WorkspaceVaultAccessLog::record($vaultItem, $request->user(), 'created', $request, [
            'source' => 'portal',
        ]);

        AuditLogger::workspaceVaultItemCreated($vaultItem, [
            'workspace_code' => $workspace->workspace_code,
            'source' => 'portal',
        ]);

        return redirect()
            ->route('workspace.vault.show', [$workspace, $vaultItem])
            ->with('success', 'Vault item created.');
    }

    public function show(Request $request, Workspace $workspace, WorkspaceVaultItem $vaultItem)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        $this->authorizeBelongsToWorkspace($workspace, $vaultItem);

        if (! $vaultItem->canViewMetadata($user, $role)) {
            abort(403, 'You cannot view this vault item.');
        }

        $vaultItem->load(['createdBy', 'updatedBy', 'lastRevealedBy']);

        WorkspaceVaultAccessLog::record($vaultItem, $user, 'viewed_metadata', $request, [
            'source' => 'portal',
        ]);

        $canReveal = $vaultItem->canReveal($user, $role);
        $canManage = $vaultItem->canManage($user, $role);
        $canViewLogs = $vaultItem->canViewAccessLogs($user, $role);
        $allowedUsers = User::query()
            ->whereIn('id', $vaultItem->allowedUserIdValues())
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $recentLogs = $canViewLogs
            ? $vaultItem->accessLogs()->with('user')->limit(5)->get()
            : collect();

        return view('workspace.vault.show', compact(
            'workspace',
            'vaultItem',
            'role',
            'canReveal',
            'canManage',
            'canViewLogs',
            'allowedUsers',
            'recentLogs',
        ));
    }

    public function edit(Request $request, Workspace $workspace, WorkspaceVaultItem $vaultItem)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        $this->authorizeBelongsToWorkspace($workspace, $vaultItem);

        if (! $vaultItem->canManage($user, $role)) {
            abort(403, 'You cannot edit this vault item.');
        }

        $workspaceUsers = $this->workspaceUsers($workspace);
        $visibilityLabels = WorkspaceVaultItem::visibilityLabels();
        $statusLabels = WorkspaceVaultItem::statusLabels();
        $categoryLabels = WorkspaceVaultItem::categoryLabels();
        $allowedRoleOptions = WorkspaceVaultItem::allowedRoleOptions();

        return view('workspace.vault.edit', compact(
            'workspace',
            'vaultItem',
            'workspaceUsers',
            'visibilityLabels',
            'statusLabels',
            'categoryLabels',
            'allowedRoleOptions',
            'role',
        ));
    }

    public function update(Request $request, Workspace $workspace, WorkspaceVaultItem $vaultItem)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        $this->authorizeBelongsToWorkspace($workspace, $vaultItem);

        if (! $vaultItem->canManage($user, $role)) {
            abort(403, 'You cannot edit this vault item.');
        }

        $beforeStatus = $vaultItem->status;
        $validated = $this->validateVaultItem($request, $workspace, $vaultItem);
        $secretRotated = array_key_exists('secret_value', $validated);

        $vaultItem->fill($validated);
        $vaultItem->updated_by = $user->id;
        $vaultItem->save();

        $action = $beforeStatus === 'archived' && $vaultItem->status === 'active' ? 'restored' : 'updated';

        WorkspaceVaultAccessLog::record($vaultItem, $user, $action, $request, [
            'source' => 'portal',
            'secret_rotated' => $secretRotated,
        ]);

        if ($action === 'restored') {
            AuditLogger::workspaceVaultItemRestored($vaultItem, [
                'workspace_code' => $workspace->workspace_code,
                'source' => 'portal',
            ]);
        } else {
            AuditLogger::workspaceVaultItemUpdated($vaultItem, [
                'workspace_code' => $workspace->workspace_code,
                'source' => 'portal',
                'secret_rotated' => $secretRotated,
            ]);
        }

        return redirect()
            ->route('workspace.vault.show', [$workspace, $vaultItem])
            ->with('success', 'Vault item updated.');
    }

    public function reveal(Request $request, Workspace $workspace, WorkspaceVaultItem $vaultItem)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        $this->authorizeBelongsToWorkspace($workspace, $vaultItem);

        if (! $vaultItem->canReveal($user, $role)) {
            abort(403, 'You cannot reveal this secret.');
        }

        $validated = $request->validate([
            'action' => 'nullable|in:revealed_secret,copied_secret',
        ]);

        $action = $validated['action'] ?? 'revealed_secret';

        $vaultItem->forceFill([
            'last_revealed_at' => now(),
            'last_revealed_by' => $user->id,
        ])->save();

        WorkspaceVaultAccessLog::record($vaultItem, $user, $action, $request, [
            'source' => 'portal',
        ]);

        AuditLogger::workspaceVaultSecretRevealed($vaultItem, [
            'workspace_code' => $workspace->workspace_code,
            'source' => 'portal',
            'vault_action' => $action,
        ]);

        return response()->json([
            'secret' => $vaultItem->secret_value,
            'revealed_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function archive(Request $request, Workspace $workspace, WorkspaceVaultItem $vaultItem)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        $this->authorizeBelongsToWorkspace($workspace, $vaultItem);

        if (! $vaultItem->canManage($user, $role)) {
            abort(403, 'You cannot archive this vault item.');
        }

        $vaultItem->update([
            'status' => 'archived',
            'updated_by' => $user->id,
        ]);

        WorkspaceVaultAccessLog::record($vaultItem, $user, 'archived', $request, [
            'source' => 'portal',
        ]);

        AuditLogger::workspaceVaultItemArchived($vaultItem, [
            'workspace_code' => $workspace->workspace_code,
            'source' => 'portal',
        ]);

        return redirect()
            ->route('workspace.vault.index', $workspace)
            ->with('success', 'Vault item archived.');
    }

    public function accessLogs(Request $request, Workspace $workspace, WorkspaceVaultItem $vaultItem)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        $this->authorizeBelongsToWorkspace($workspace, $vaultItem);

        if (! $vaultItem->canViewAccessLogs($user, $role)) {
            abort(403, 'You cannot view access logs for this vault item.');
        }

        WorkspaceVaultAccessLog::record($vaultItem, $user, 'viewed_logs', $request, [
            'source' => 'portal',
        ]);

        AuditLogger::workspaceVaultAccessLogsViewed($vaultItem, [
            'workspace_code' => $workspace->workspace_code,
            'source' => 'portal',
        ]);

        $logs = $vaultItem->accessLogs()
            ->with('user')
            ->paginate(25);

        return view('workspace.vault.access-logs', compact('workspace', 'vaultItem', 'logs', 'role'));
    }
}
