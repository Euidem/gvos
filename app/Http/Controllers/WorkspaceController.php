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

        $workspace->load(['primaryManager', 'primaryTalent', 'activeMembers.user', 'trial', 'leadRequest']);

        return view('workspace.show', compact('workspace'));
    }
}
