<?php

namespace App\Http\Controllers;

use App\Models\UserNotificationPreference;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function settings(Request $request)
    {
        $preferences = $request->user()
            ->notificationPreferences()
            ->get()
            ->keyBy('notification_key');

        $definitions = UserNotificationPreference::DEFINITIONS;

        return view('settings.notifications', compact('preferences', 'definitions'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'array',
            'preferences.*.in_app_enabled' => 'nullable|boolean',
            'preferences.*.email_enabled' => 'nullable|boolean',
        ]);

        $input = $validated['preferences'] ?? [];
        $changes = [];

        foreach (UserNotificationPreference::keys() as $key) {
            $values = $input[$key] ?? [];

            $preference = $request->user()->notificationPreferences()->firstOrNew([
                'notification_key' => $key,
            ]);

            $before = [
                'in_app_enabled' => $preference->exists
                    ? (bool) $preference->in_app_enabled
                    : UserNotificationPreference::defaultInAppEnabled($key),
                'email_enabled' => $preference->exists
                    ? (bool) $preference->email_enabled
                    : UserNotificationPreference::defaultEmailEnabled($key),
            ];

            $preference->fill([
                'in_app_enabled' => (bool) ($values['in_app_enabled'] ?? false),
                'email_enabled' => (bool) ($values['email_enabled'] ?? false),
            ]);
            $preference->save();

            $after = [
                'in_app_enabled' => (bool) $preference->in_app_enabled,
                'email_enabled' => (bool) $preference->email_enabled,
            ];

            if ($before !== $after) {
                $changes[$key] = ['from' => $before, 'to' => $after];
            }
        }

        AuditLogger::notificationPreferencesUpdated($request->user(), [
            'changed_keys' => array_keys($changes),
            'changes' => $changes,
        ]);

        return redirect()
            ->route('settings.notifications')
            ->with('success', 'Notification preferences updated.');
    }
}
