<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    /**
     * List workspaces the authenticated user may access.
     *
     * Admins see every workspace.
     * All other users see workspaces where they:
     *   – have an active member row, OR
     *   – are the primary_manager_id, OR
     *   – are the primary_talent_id, OR
     *   – are assigned to at least one task in the workspace.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $eager = ['primaryManager', 'primaryTalent', 'trial', 'leadRequest'];

        if ($user->hasAnyRole(['super_admin', 'operations_admin'])) {
            // Admins see the full list
            $workspaces = Workspace::with($eager)
                ->orderByDesc('created_at')
                ->get();
        } else {
            // All non-admin access paths grouped in a single where() closure
            // so the orWhere clauses do not bleed into outer query scope.
            $workspaces = Workspace::with($eager)
                ->where(function ($q) use ($user) {
                    $q->whereHas('members', function ($mq) use ($user) {
                        $mq->where('user_id', $user->id)->where('status', 'active');
                    })
                    ->orWhere('primary_manager_id', $user->id)
                    ->orWhere('primary_talent_id', $user->id)
                    ->orWhereHas('tasks', function ($tq) use ($user) {
                        $tq->where('assigned_to_user_id', $user->id);
                    });
                })
                ->orderByDesc('created_at')
                ->get();
        }

        return view('workspace.index', compact('workspaces'));
    }

    /**
     * Show a single workspace — delegates access check to the model helper
     * so all access paths (admin, primary team, member, assigned task) are
     * evaluated consistently.
     */
    public function show(Request $request, Workspace $workspace)
    {
        $user = $request->user();

        if (! $workspace->userHasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        $workspace->load(['primaryManager', 'primaryTalent', 'activeMembers.user', 'trial', 'leadRequest', 'company']);

        $role     = $workspace->resolveUserWorkspaceRole($user);
        $internal = in_array($role, ['admin', 'workspace_admin', 'manager'], true);
        $isClient = in_array($role, ['client_admin', 'client_staff', 'client'], true);

        /*
         * Phase 28: the overview aggregates were previously ~15 ad-hoc count()
         * queries inside the Blade view. They are resolved here instead, in one
         * grouped query plus a handful of bounded lookups, and only the figures
         * the current role is allowed to see are computed.
         */
        $taskCounts = $workspace->tasks()
            ->reorder()
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // "Needs attention" items, deep-linked from the overview.
        $needsAttention = collect();

        if ($internal) {
            $needsAttention = $workspace->tasks()
                ->reorder()
                ->whereIn('status', ['blocked', 'submitted'])
                ->with('assignedTo:id,name')
                ->orderByRaw("FIELD(status,'blocked','submitted')")
                ->limit(6)
                ->get();
        } elseif ($isClient) {
            $needsAttention = $workspace->tasks()
                ->reorder()
                ->where('status', 'submitted')
                ->with('assignedTo:id,name')
                ->limit(6)
                ->get();
        } elseif ($role === 'talent' || $role === 'assigned_user') {
            $needsAttention = $workspace->tasks()
                ->reorder()
                ->where('assigned_to_user_id', $user->id)
                ->whereIn('status', ['blocked', 'revision_requested', 'in_progress', 'pending'])
                ->orderByRaw("FIELD(status,'blocked','revision_requested','in_progress','pending')")
                ->limit(6)
                ->get();
        }

        $latestReport = $role === 'observer' ? null : $workspace->weeklyReports()
            ->when($isClient, fn ($q) => $q->where('status', 'published'))
            ->first();

        $latestMessages = $workspace->messages()
            ->reorder()
            ->when(! $internal, fn ($q) => $q->where('visibility', 'public'))
            ->with('user:id,name')
            ->latest()
            ->limit(3)
            ->get();

        return view('workspace.show', [
            'workspace'      => $workspace,
            'role'           => $role,
            'internal'       => $internal,
            'isClient'       => $isClient,
            'taskCounts'     => $taskCounts,
            'needsAttention' => $needsAttention,
            'latestReport'   => $latestReport,
            'latestMessages' => $latestMessages,
            'canCreateTask'  => $workspace->userCanCreateTasks($user),
        ]);
    }
}
