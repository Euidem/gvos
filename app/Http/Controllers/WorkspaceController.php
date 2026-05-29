<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    /**
     * List all workspaces the authenticated user is a member of
     * (or all workspaces for admins / managers with broader access).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $workspaces = Workspace::with(['primaryManager', 'primaryTalent', 'trial', 'leadRequest'])
            ->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'active');
            })
            ->orWhere('primary_manager_id', $user->id)
            ->orWhere('primary_talent_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('workspace.index', compact('workspaces'));
    }

    /**
     * Show a single workspace — only if the user is a member or primary team.
     */
    public function show(Request $request, Workspace $workspace)
    {
        $user = $request->user();

        $isMember = $workspace->members()->where('user_id', $user->id)->where('status', 'active')->exists();
        $isPrimary = in_array($user->id, [$workspace->primary_manager_id, $workspace->primary_talent_id]);

        if (! $isMember && ! $isPrimary) {
            abort(403, 'You do not have access to this workspace.');
        }

        $workspace->load(['primaryManager', 'primaryTalent', 'activeMembers.user', 'trial', 'leadRequest']);

        return view('workspace.show', compact('workspace'));
    }
}
