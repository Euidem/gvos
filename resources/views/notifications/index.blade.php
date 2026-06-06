{{-- No Stitch screen - based on: workspace/index and dashboard card patterns. --}}
<x-layouts.gvos title="Notifications">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-primary">Notifications</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Recent platform activity that needs your attention.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('settings.notifications') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-border-subtle bg-white px-4 py-2 font-label-md text-label-md text-on-surface hover:bg-surface-container-low transition-all">
                    <span class="material-symbols-outlined" style="font-size:18px;">tune</span>
                    Preferences
                </a>

                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2 font-label-md text-label-md text-on-secondary hover:brightness-110 transition-all">
                            <span class="material-symbols-outlined" style="font-size:18px;">done_all</span>
                            Mark all read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-status-active/20 bg-status-active/10 px-4 py-3 font-body-sm text-body-sm text-status-completed">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $isUnread = is_null($notification->read_at);
                    $level = $data['level'] ?? 'info';
                    $levelClass = match ($level) {
                        'success' => 'bg-status-active/10 text-status-completed border-status-active/20',
                        'warning' => 'bg-status-payment-due/10 text-status-payment-due border-status-payment-due/20',
                        'danger' => 'bg-status-blocked/10 text-status-blocked border-status-blocked/20',
                        default => 'bg-secondary/10 text-secondary border-secondary/20',
                    };
                @endphp

                <div class="p-5 border-b border-border-subtle last:border-b-0 {{ $isUnread ? 'bg-secondary/5' : 'bg-white' }}">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isUnread)
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-secondary"></span>
                                @endif
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 font-label-md text-label-md {{ $levelClass }}">
                                    {{ ucfirst($level) }}
                                </span>
                                <span class="font-mono-sm text-mono-sm text-outline">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </span>
                            </div>

                            <h3 class="mt-3 font-headline-md text-headline-md text-on-surface">
                                {{ $data['title'] ?? 'Notification' }}
                            </h3>
                            <p class="mt-2 font-body-sm text-body-sm text-on-surface-variant">
                                {{ $data['message'] ?? 'You have a new GVOS notification.' }}
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row md:flex-col gap-2 md:min-w-[150px]">
                            @if (! empty($data['action_url']))
                                <a href="{{ $data['action_url'] }}"
                                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2 font-label-md text-label-md text-on-secondary hover:brightness-110 transition-all">
                                    Open
                                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                                </a>
                            @endif

                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-border-subtle bg-white px-4 py-2 font-label-md text-label-md text-on-surface hover:bg-surface-container-low transition-all">
                                        <span class="material-symbols-outlined" style="font-size:16px;">mark_email_read</span>
                                        Mark read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                        <span class="material-symbols-outlined" style="font-size:28px;">notifications</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">No notifications yet</h3>
                    <p class="mt-2 font-body-sm text-body-sm text-on-surface-variant">
                        Important activity will appear here when there is something to review.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div>
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-layouts.gvos>
