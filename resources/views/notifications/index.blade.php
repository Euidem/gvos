{{-- No Stitch screen - based on: workspace/index and dashboard card patterns. --}}
<x-layouts.gvos title="Notifications">
    <div class="max-w-4xl mx-auto">

        {{-- ── Page header ────────────────────────────────────────────────────── --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-primary">Notifications</h1>
                <p class="text-[12px] text-outline mt-1">Recent platform activity that needs your attention.</p>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <a href="{{ route('settings.notifications') }}"
                   class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-border-subtle bg-white px-3 py-2 text-xs font-semibold text-on-surface hover:bg-surface-container-low transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">tune</span>
                    Preferences
                </a>
                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:brightness-110 transition-all"
                                style="background:#0058be;">
                            <span class="material-symbols-outlined" style="font-size:16px;">done_all</span>
                            Mark all read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <x-portal.alert type="success" class="mb-5">{{ session('success') }}</x-portal.alert>
        @endif

        {{-- ── Notification list ───────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">

            {{-- Header strip --}}
            @php
                $unreadCount = $notifications->whereNull('read_at')->count();
            @endphp
            <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between"
                 style="background:rgba(247,249,251,1);">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">notifications</span>
                    <span class="text-xs font-semibold text-outline uppercase tracking-wider">Inbox</span>
                </div>
                @if ($unreadCount > 0)
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full text-white"
                          style="background:#0058be;">{{ $unreadCount }} unread</span>
                @endif
            </div>

            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $isUnread = is_null($notification->read_at);
                    $level = $data['level'] ?? 'info';
                    $levelColor = match ($level) {
                        'success' => '#059669',
                        'warning' => '#D97706',
                        'danger'  => '#EF4444',
                        default   => '#0058be',
                    };
                    $levelBg = $levelColor . '12';
                    $levelBorder = $levelColor . '28';
                    $levelLabel = match ($level) {
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'danger'  => 'Alert',
                        default   => 'Info',
                    };
                    $levelIcon = match ($level) {
                        'success' => 'check_circle',
                        'warning' => 'warning',
                        'danger'  => 'error',
                        default   => 'info',
                    };
                @endphp

                <div class="p-5 border-b border-border-subtle last:border-b-0 transition-colors"
                     style="{{ $isUnread ? 'background:rgba(0,88,190,0.025);' : '' }}">
                    <div class="flex items-start gap-4">

                        {{-- Level indicator dot --}}
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                             style="background:{{ $levelBg }};border:1px solid {{ $levelBorder }};">
                            <span class="material-symbols-outlined" style="font-size:16px;color:{{ $levelColor }};">{{ $levelIcon }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                @if ($isUnread)
                                    <span class="inline-flex h-2 w-2 rounded-full flex-shrink-0"
                                          style="background:#0058be;"></span>
                                @endif
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                                      style="background:{{ $levelBg }};color:{{ $levelColor }};border:1px solid {{ $levelBorder }};">
                                    {{ $levelLabel }}
                                </span>
                                <span class="text-[11px] text-outline font-mono">{{ $notification->created_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface leading-snug">
                                {{ $data['title'] ?? 'Notification' }}
                            </h3>
                            <p class="mt-1 text-sm text-on-surface-variant leading-relaxed">
                                {{ $data['message'] ?? 'You have a new GVOS notification.' }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-2 flex-shrink-0">
                            @if (! empty($data['action_url']))
                                <a href="{{ $data['action_url'] }}"
                                   class="inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white hover:brightness-110 transition-all"
                                   style="background:#0058be;">
                                    Open
                                    <span class="material-symbols-outlined" style="font-size:14px;">arrow_forward</span>
                                </a>
                            @endif
                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg border border-border-subtle bg-white px-3 py-2 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low transition-all">
                                        <span class="material-symbols-outlined" style="font-size:14px;">mark_email_read</span>
                                        Mark read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl"
                         style="background:rgba(0,88,190,0.06);">
                        <span class="material-symbols-outlined text-secondary" style="font-size:28px;">notifications</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">No notifications yet</h3>
                    <p class="mt-2 text-sm text-on-surface-variant max-w-xs mx-auto">
                        Important activity will appear here when there is something to review.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="mt-5">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-layouts.gvos>
