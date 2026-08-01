{{-- No Stitch screen - based on: profile/edit and dashboard card patterns. --}}
<x-layouts.gvos title="Notification Settings">
    <div class="max-w-4xl mx-auto">

        {{-- ── Page header ────────────────────────────────────────────────── --}}
        {{-- Phase 28 bugfix: this block previously called route('profile.edit'),
             which is not a defined route (only profile.show / profile.update
             exist). Every request to this page threw RouteNotFoundException and
             returned HTTP 500. The eyebrow now links to the real profile route. --}}
        <x-portal.page-header
            title="Notification Settings"
            subtitle="Choose which GVOS updates appear in the app, and which are also emailed to you."
            eyebrow="Notifications"
            :eyebrow-href="route('notifications.index')" />

        <div class="space-y-5">

        @if (session('success'))
            <div class="rounded-xl border border-status-active/20 bg-status-active/10 px-4 py-3 font-body-sm text-body-sm text-status-completed">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('settings.notifications.update') }}" class="bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden">
            @csrf
            @method('PUT')

            <div class="hidden md:grid grid-cols-[1fr_140px_140px] gap-4 border-b border-border-subtle bg-surface-container-low px-6 py-4">
                <span class="font-label-md text-label-md uppercase text-outline tracking-wider">Notification</span>
                <span class="font-label-md text-label-md uppercase text-outline tracking-wider text-center">In app</span>
                <span class="font-label-md text-label-md uppercase text-outline tracking-wider text-center">Email</span>
            </div>

            <div class="divide-y divide-border-subtle">
                @foreach ($definitions as $key => $definition)
                    @php
                        $preference = $preferences->get($key);
                        $inApp = old("preferences.$key.in_app_enabled", $preference?->in_app_enabled ?? true);
                        $email = old("preferences.$key.email_enabled", $preference?->email_enabled ?? \App\Models\UserNotificationPreference::defaultEmailEnabled($key));
                    @endphp

                    <div class="grid gap-4 px-6 py-5 md:grid-cols-[1fr_140px_140px] md:items-center">
                        <div>
                            <h3 class="font-headline-md text-[18px] leading-6 text-on-surface">
                                {{ $definition['label'] }}
                            </h3>
                            <p class="mt-1 font-body-sm text-body-sm text-on-surface-variant">
                                {{ $definition['description'] }}
                            </p>
                        </div>

                        <label class="flex items-center justify-between gap-3 rounded-lg border border-border-subtle bg-surface-container-lowest px-4 py-3 md:justify-center md:border-0 md:bg-transparent md:p-0">
                            <span class="font-label-md text-label-md text-on-surface md:hidden">In app</span>
                            <input type="hidden" name="preferences[{{ $key }}][in_app_enabled]" value="0">
                            <input type="checkbox"
                                   name="preferences[{{ $key }}][in_app_enabled]"
                                   value="1"
                                   @checked((bool) $inApp)
                                   class="rounded border-outline text-secondary focus:ring-secondary">
                        </label>

                        <label class="flex items-center justify-between gap-3 rounded-lg border border-border-subtle bg-surface-container-lowest px-4 py-3 md:justify-center md:border-0 md:bg-transparent md:p-0">
                            <span class="font-label-md text-label-md text-on-surface md:hidden">Email</span>
                            <input type="hidden" name="preferences[{{ $key }}][email_enabled]" value="0">
                            <input type="checkbox"
                                   name="preferences[{{ $key }}][email_enabled]"
                                   value="1"
                                   @checked((bool) $email)
                                   class="rounded border-outline text-secondary focus:ring-secondary">
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t border-border-subtle bg-surface-container-low px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                    Email delivery uses the configured Laravel mail settings.
                </p>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-secondary px-5 py-2.5 font-label-md text-label-md text-on-secondary hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                    Save Preferences
                </button>
            </div>
        </form>
    </div>{{-- /space-y-5 --}}
</div>{{-- /max-w-4xl --}}
</x-layouts.gvos>
