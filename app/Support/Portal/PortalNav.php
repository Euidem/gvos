<?php

namespace App\Support\Portal;

use App\Models\User;
use App\Models\Workspace;

/**
 * Phase 28 — Role-aware navigation resolver for the non-admin GVOS portal.
 *
 * Single source of truth for:
 *   – which sidebar items each platform role sees;
 *   – which workspace tabs each *workspace* role sees.
 *
 * This class only decides what is *shown*. It never grants access: every
 * destination is still guarded by its own controller. Hiding a link the user
 * cannot use is a usability decision, not a security control.
 *
 * The workspace-role → tab matrix mirrors the real gates in the workspace
 * controllers, so navigation can never lead to a 403:
 *   – Time      : WorkspaceTimeLogController        aborts for `observer`
 *   – Reports   : WorkspaceWeeklyReportController   aborts for `observer`
 *   – Billing   : WorkspaceBillingController        allows admin/workspace_admin/
 *                 manager/client_admin/client_staff/client only (talent + observer excluded)
 *   – Vault     : WorkspaceVaultController          aborts unless canUseVaultRole()
 *                 (excludes `none` and `observer`)
 *   – Team      : WorkspaceMemberController::index  allows any workspace member
 */
class PortalNav
{
    /** Per-request memo so the shell does not re-query on every partial. */
    private static array $memo = [];

    // ── Platform role → navigation profile ───────────────────────────────────

    public static function profileFor(User $user): string
    {
        return match ($user->getGvosRoleName()) {
            'super_admin', 'operations_admin' => 'admin',
            'line_manager'                    => 'manager',
            'talent'                          => 'talent',
            'individual_client'               => 'client',
            'business_client_admin'           => 'business_admin',
            'business_client_staff'           => 'staff',
            'active_lead'                     => 'lead',
            default                           => 'staff',
        };
    }

    /** Short, human label for the role — used in the sidebar and dashboard eyebrow. */
    public static function roleLabel(User $user): string
    {
        return match ($user->getGvosRoleName()) {
            'super_admin'           => 'Super Admin',
            'operations_admin'      => 'Operations Admin',
            'line_manager'          => 'Line Manager',
            'talent'                => 'Talent',
            'individual_client'     => 'Client',
            'business_client_admin' => 'Account Admin',
            'business_client_staff' => 'Team Member',
            'active_lead'           => 'Prospective Client',
            default                 => 'Member',
        };
    }

    /** The user's dashboard URL (never hard-coded in views). */
    public static function homeUrl(User $user): string
    {
        return url($user->getDashboardRoute());
    }

    /**
     * The workspace used to resolve workspace-scoped sidebar links.
     * Returns null when the user belongs to no workspace.
     */
    public static function primaryWorkspace(User $user): ?Workspace
    {
        $key = 'ws:' . $user->id;

        if (! array_key_exists($key, self::$memo)) {
            self::$memo[$key] = $user->primaryWorkspace();
        }

        return self::$memo[$key];
    }

    /**
     * The user's effective role inside their primary workspace.
     *
     * The sidebar must respect this, not just the platform role: a
     * `business_client_staff` user who is an `observer` in their workspace is
     * blocked (403) from time logs, reports and the vault. Gating on the
     * platform role alone would put a 403 in the navigation.
     */
    public static function primaryWorkspaceRole(User $user): ?string
    {
        $workspace = self::primaryWorkspace($user);

        if (! $workspace) {
            return null;
        }

        $key = 'wsrole:' . $user->id;

        if (! array_key_exists($key, self::$memo)) {
            self::$memo[$key] = $workspace->resolveUserWorkspaceRole($user);
        }

        return self::$memo[$key];
    }

    /** How many active workspaces the user can reach. */
    public static function workspaceCount(User $user): int
    {
        $key = 'wsc:' . $user->id;

        if (! array_key_exists($key, self::$memo)) {
            self::$memo[$key] = Workspace::query()
                ->where(function ($q) use ($user) {
                    $q->where('primary_manager_id', $user->id)
                      ->orWhere('primary_talent_id', $user->id)
                      ->orWhereHas('members', fn ($m) => $m
                          ->where('user_id', $user->id)
                          ->where('status', 'active'));
                })
                ->whereIn('status', ['pending', 'active', 'paused'])
                ->count();
        }

        return self::$memo[$key];
    }

    // ── Sidebar ──────────────────────────────────────────────────────────────

    /**
     * Build the sidebar as ordered groups.
     *
     * @return array<int, array{label: ?string, items: array<int, array>}>
     */
    public static function sidebar(User $user): array
    {
        $profile   = self::profileFor($user);
        $workspace = self::primaryWorkspace($user);

        /*
         * Workspace-scoped destination.
         *
         * These always resolve to the user's primary workspace, even when they
         * belong to several. Falling back to the workspace list would give
         * Time / Messages / Files / Vault the *same* href as "Workspaces" —
         * exactly the mislabelled-duplicate-link problem this phase set out to
         * remove. Once inside, the workspace tab bar makes the context obvious
         * and lets the user switch module; the Workspaces item switches project.
         */
        $wsRole = self::primaryWorkspaceRole($user);

        /*
         * A workspace-scoped item is emitted only when the user's *workspace*
         * role can actually open that page, mirroring the controller gates in
         * workspaceTabs(). This is what stops an observer being offered Time,
         * Reports or Vault links that would return 403.
         */
        $ws = function (string $route) use ($workspace, $wsRole) {
            if (! $workspace || ! $wsRole || $wsRole === 'none') {
                return null;
            }

            $internal = in_array($wsRole, ['admin', 'workspace_admin', 'manager'], true);
            $client   = in_array($wsRole, ['client_admin', 'client_staff', 'client'], true);

            $allowed = match ($route) {
                'workspace.time-logs.index',
                'workspace.reports.index'  => $wsRole !== 'observer',
                'workspace.billing.index'  => $internal || $client,
                'workspace.vault.index'    => $internal
                    || in_array($wsRole, ['talent', 'client_admin', 'assigned_user'], true),
                default                    => true,
            };

            return $allowed ? route($route, $workspace) : null;
        };

        $home = [
            'label' => match ($profile) {
                'manager'                    => 'Command Center',
                'client', 'business_admin'   => 'Overview',
                'staff'                      => 'Overview',
                'lead'                       => 'My Request',
                default                      => 'Home',
            },
            'icon'   => 'home',
            'href'   => self::homeUrl($user),
            'active' => request()->is('*/dashboard') || request()->is('/'),
        ];

        // ── Lead: deliberately narrow funnel ─────────────────────────────────
        if ($profile === 'lead') {
            $items = [$home];

            if ($workspace) {
                $items[] = [
                    'label'  => 'My Trial Workspace',
                    'icon'   => 'workspaces',
                    'href'   => route('workspace.show', $workspace),
                    'active' => request()->routeIs('workspace.*'),
                ];
            }

            return [
                ['label' => null, 'items' => $items],
                ['label' => 'Account', 'items' => [
                    self::item('Notifications', 'notifications', route('notifications.index'), request()->routeIs('notifications.*')),
                    self::item('Profile', 'person', route('profile.show'), request()->routeIs('profile.*')),
                ]],
            ];
        }

        // ── Work group ───────────────────────────────────────────────────────
        $work = [];

        if ($profile === 'talent') {
            $work[] = self::item('My Work', 'checklist', $ws('workspace.tasks.index'), request()->routeIs('workspace.tasks.*'));
        }

        // Review Queue anchors into the manager dashboard — no new route.
        // Admins are excluded: their dashboard is the Filament panel.
        if ($profile === 'manager') {
            $work[] = self::item('Review Queue', 'rate_review', self::homeUrl($user) . '#review-queue', false);
        }

        $work[] = self::item('Workspaces', 'workspaces', route('workspace.index'),
            request()->routeIs('workspace.index') || request()->routeIs('workspace.show'));

        if (in_array($profile, ['talent', 'manager', 'admin'], true)) {
            $work[] = self::item('Time', 'schedule', $ws('workspace.time-logs.index'), request()->routeIs('workspace.time-logs.*'));
        }

        if (in_array($profile, ['manager', 'admin', 'client', 'business_admin', 'staff'], true)) {
            $work[] = self::item('Reports', 'summarize', $ws('workspace.reports.index'), request()->routeIs('workspace.reports.*'));
        }

        // ── Communication group ──────────────────────────────────────────────
        $comms = [
            self::item('Messages', 'forum', $ws('workspace.chat.index'), request()->routeIs('workspace.chat.*')),
            self::item('Files', 'folder_open', $ws('workspace.files.index'), request()->routeIs('workspace.files.*')),
            self::item('Notifications', 'notifications', route('notifications.index'), request()->routeIs('notifications.*')),
        ];

        // ── Account group ────────────────────────────────────────────────────
        $account = [];

        if (in_array($profile, ['client', 'business_admin'], true)) {
            $account[] = self::item('Billing', 'receipt_long', $ws('workspace.billing.index'), request()->routeIs('workspace.billing.*'));
        }

        if ($profile === 'business_admin') {
            $account[] = self::item('Team Access', 'group', $ws('workspace.members.index'), request()->routeIs('workspace.members.*'));
        }

        if (in_array($profile, ['talent', 'manager', 'admin', 'client', 'business_admin'], true)) {
            $account[] = self::item('Vault', 'lock', $ws('workspace.vault.index'), request()->routeIs('workspace.vault.*'));
        }

        $account[] = self::item('Profile', 'person', route('profile.show'), request()->routeIs('profile.*'));

        return array_values(array_filter([
            ['label' => null,            'items' => [$home]],
            ['label' => 'Work',          'items' => array_values(array_filter($work))],
            ['label' => 'Communication', 'items' => array_values(array_filter($comms))],
            ['label' => 'Account',       'items' => array_values(array_filter($account))],
        ], fn ($group) => $group['items'] !== []));
    }

    /** Build a sidebar item; returns null when the destination is unavailable. */
    private static function item(string $label, string $icon, ?string $href, bool $active): ?array
    {
        if ($href === null) {
            return null;
        }

        return compact('label', 'icon', 'href', 'active');
    }

    // ── Workspace tabs ───────────────────────────────────────────────────────

    /**
     * Workspace sub-navigation for a given workspace role.
     *
     * Only tabs whose controller would allow the request are returned, so a tab
     * can never lead to a 403.
     *
     * @return array<int, array{label:string, icon:string, href:string, active:bool}>
     */
    public static function workspaceTabs(Workspace $workspace, string $role): array
    {
        $internal = in_array($role, ['admin', 'workspace_admin', 'manager'], true);
        $isClient = in_array($role, ['client_admin', 'client_staff', 'client'], true);
        $observer = $role === 'observer';

        $tabs = [
            ['Overview',  'dashboard',   'workspace.show',            true],
            ['Tasks',     'task_alt',    'workspace.tasks.index',     true],
            ['Messages',  'forum',       'workspace.chat.index',      true],
            ['Files',     'folder_open', 'workspace.files.index',     true],
            // Observers are blocked from time logs and reports by the controllers.
            ['Time',      'schedule',    'workspace.time-logs.index', ! $observer],
            ['Reports',   'summarize',   'workspace.reports.index',   ! $observer],
            // WorkspaceMemberController::requireMemberPageAccess() excludes observers.
            ['Team',      'group',       'workspace.members.index',   ! $observer],
            // Talent and observers have no billing access.
            ['Billing',   'receipt_long', 'workspace.billing.index',  $internal || $isClient],
            // Vault excludes observers; client staff see it only via the account admin.
            ['Vault',     'lock',        'workspace.vault.index',
                $internal || $role === 'talent' || $role === 'client_admin' || $role === 'assigned_user'],
        ];

        $out = [];

        foreach ($tabs as [$label, $icon, $route, $visible]) {
            if (! $visible) {
                continue;
            }

            $out[] = [
                'label'  => $label,
                'icon'   => $icon,
                'href'   => route($route, $workspace),
                'active' => $route === 'workspace.show'
                    ? request()->routeIs('workspace.show')
                    : request()->routeIs(str_replace('.index', '', $route) . '.*'),
            ];
        }

        return $out;
    }
}
