<x-layouts.gvos :title="$workspace->name . ' — Chat'">

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-3">
            <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
            <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
            <span>Chat</span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 24px;">forum</span>
                    Workspace Chat
                </h1>
                <p class="text-[12px] text-outline mt-1">
                    {{ $workspace->workspace_code }}
                    @if ($seeInternal)
                        &middot; Showing public &amp; internal messages
                    @else
                        &middot; Public messages
                    @endif
                    &middot; {{ $messages->count() }} message{{ $messages->count() !== 1 ? 's' : '' }}
                </p>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <a href="{{ route('workspace.files.index', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                   style="border-color:#0058be;color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">folder</span>
                    Files
                </a>
                <a href="{{ route('workspace.tasks.index', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/20 transition-all">
                    <span class="material-symbols-outlined" style="font-size: 14px;">view_kanban</span>
                    Tasks
                </a>
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="inline-flex items-center gap-1.5 text-sm text-secondary hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    Workspace
                </a>
            </div>
        </div>
    </div>

    {{-- ── Session flash ──────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <x-portal.alert type="success" class="mb-4">{{ session('success') }}</x-portal.alert>
    @endif
    @if ($errors->any())
        <x-portal.alert type="error" class="mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </x-portal.alert>
    @endif

    {{-- ── Message list ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm mb-4 overflow-hidden">

        {{-- Header strip --}}
        <div class="px-5 py-3.5 border-b border-border-subtle flex items-center gap-2"
             style="background:rgba(247,249,251,1);">
            <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">chat</span>
            <span class="text-xs font-semibold text-outline uppercase tracking-wider">Messages</span>
            @if ($messages->count() > 0)
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1"
                      style="background:rgba(0,88,190,0.08);color:#0058be;">{{ $messages->count() }}</span>
            @endif
        </div>

        @if ($messages->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined" style="font-size: 26px;color:#0058be;">forum</span>
                </div>
                <h4 class="text-sm font-semibold text-on-surface mb-1">No messages yet</h4>
                <p class="text-xs text-outline max-w-xs mx-auto">
                    @if ($canPost)
                        Be the first to post a message in this workspace.
                    @else
                        There are no messages in this workspace chat yet.
                    @endif
                </p>
            </div>
        @else
            <div class="divide-y divide-border-subtle">
                @foreach ($messages as $msg)
                @php
                    $isCurrentUser = (int) $msg->user_id === (int) auth()->id();
                    $isInternalMsg = $msg->isInternal();
                @endphp
                <div class="px-5 py-4 transition-colors hover:bg-surface-container-low {{ $isInternalMsg ? '' : '' }}"
                     style="{{ $isInternalMsg ? 'background:rgba(0,88,190,0.025);' : '' }}">
                    <div class="flex items-start gap-3">
                        {{-- Avatar --}}
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 mt-0.5"
                             style="{{ $isInternalMsg ? 'background:#0058be;' : 'background:#2170e4;' }}">
                            {{ strtoupper(substr($msg->user->name ?? '?', 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center flex-wrap gap-2 mb-1.5">
                                <span class="text-xs font-semibold text-on-surface">
                                    {{ $msg->user->name ?? 'Unknown' }}
                                    @if ($isCurrentUser)
                                        <span class="text-[10px] font-normal text-outline">(you)</span>
                                    @endif
                                </span>

                                @if ($isInternalMsg)
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                          style="background:rgba(0,88,190,0.10);color:#0058be;">Internal</span>
                                @endif

                                <span class="text-[11px] text-outline">{{ $msg->created_at->diffForHumans() }}</span>

                                @if ($msg->edited_at)
                                    <span class="text-[10px] italic text-outline">(edited)</span>
                                @endif
                            </div>

                            <p class="text-sm whitespace-pre-wrap leading-relaxed text-on-surface-variant">{{ $msg->message }}</p>
                        </div>

                        {{-- Delete action --}}
                        @php
                            $canDeleteMsg = $isCurrentUser
                                || in_array($role, ['admin', 'workspace_admin', 'manager'], true);
                        @endphp
                        @if ($canDeleteMsg)
                            <form method="POST"
                                  action="{{ route('workspace.chat.destroy', [$workspace, $msg]) }}"
                                  class="flex-shrink-0"
                                  onsubmit="return confirm('Delete this message?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-0.5 px-2 py-1 rounded-lg text-xs transition-colors text-outline hover:text-status-blocked hover:bg-status-blocked/5"
                                        title="Delete message">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif

    </div>

    {{-- ── Post a message ────────────────────────────────────────────────────── --}}
    @if ($canPost)
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-border-subtle"
                 style="background:rgba(247,249,251,1);">
                <h3 class="text-xs font-semibold text-outline uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 14px;">edit</span>
                    Post a Message
                </h3>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('workspace.chat.store', $workspace) }}" class="space-y-3">
                    @csrf
                    <textarea name="message"
                              rows="3"
                              maxlength="5000"
                              placeholder="Type your message here…"
                              required
                              class="w-full rounded-lg px-4 py-3 text-sm border border-border-subtle bg-white text-on-surface transition-colors resize-none focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary">{{ old('message') }}</textarea>

                    <div class="flex items-center justify-between gap-4">
                        @if ($seeInternal)
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="visibility" value="internal"
                                       class="rounded border-border-subtle text-secondary"
                                       {{ old('visibility') === 'internal' ? 'checked' : '' }}>
                                <span class="text-xs font-medium text-on-surface-variant">
                                    Internal
                                    <span class="text-[10px] text-outline">(managers &amp; admins only)</span>
                                </span>
                            </label>
                        @else
                            <span class="text-xs text-outline flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size:13px;">public</span>
                                Visible to all workspace members
                            </span>
                        @endif

                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                                style="background:#0058be;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="rounded-xl px-5 py-4 text-sm text-on-surface-variant border border-border-subtle"
             style="background:rgba(107,114,128,0.04);">
            <span class="material-symbols-outlined align-middle mr-1" style="font-size:16px;">visibility</span>
            You have view-only access to this workspace chat.
        </div>
    @endif

</x-layouts.gvos>
