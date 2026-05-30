<x-layouts.gvos :title="$workspace->name . ' — Chat'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Chat</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">forum</span>
                Workspace Chat
            </h2>
            <p class="text-xs text-outline mt-0.5">{{ $workspace->workspace_code }} &middot;
                @if ($seeInternal)
                    Showing public and internal messages
                @else
                    Showing public messages
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('workspace.files.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be; color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 14px;">folder</span>
                Files
            </a>
            <a href="{{ route('workspace.tasks.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#6B7280; color:#6B7280;">
                <span class="material-symbols-outlined" style="font-size: 14px;">view_kanban</span>
                Task Board
            </a>
            <a href="{{ route('workspace.show', $workspace) }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Workspace
            </a>
        </div>
    </div>

    {{-- ── Session flash ─────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#065F46;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-4 rounded-lg text-sm"
             style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ── Message list ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm mb-4 overflow-hidden">

        @if ($messages->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                     style="background-color:rgba(0,88,190,.06);">
                    <span class="material-symbols-outlined" style="font-size: 26px; color:#0058be;">forum</span>
                </div>
                <h4 class="text-sm font-semibold mb-1" style="color:#1E293B;">No messages yet</h4>
                <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                    @if ($canPost)
                        Be the first to post a message in this workspace.
                    @else
                        There are no messages in this workspace chat yet.
                    @endif
                </p>
            </div>
        @else
            <div class="divide-y divide-[#F1F5F9]">
                @foreach ($messages as $msg)
                @php
                    $isCurrentUser = (int) $msg->user_id === (int) auth()->id();
                    $isInternalMsg = $msg->isInternal();
                @endphp
                <div class="px-5 py-4 {{ $isInternalMsg ? 'bg-secondary/[0.03]' : '' }}">
                    <div class="flex items-start gap-3">
                        {{-- Avatar --}}
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5"
                             style="{{ $isInternalMsg ? 'background-color:#0058be;' : 'background-color:#2170e4;' }}">
                            {{ strtoupper(substr($msg->user->name ?? '?', 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            {{-- Header row --}}
                            <div class="flex items-center flex-wrap gap-2 mb-1">
                                <span class="text-xs font-semibold" style="color:#1E293B;">
                                    {{ $msg->user->name ?? 'Unknown' }}
                                    @if ($isCurrentUser)
                                        <span class="text-[10px] font-normal" style="color:#9CA3AF;">(you)</span>
                                    @endif
                                </span>

                                @if ($isInternalMsg)
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                          style="background:rgba(0,88,190,0.10);color:#0058be;">
                                        Internal
                                    </span>
                                @endif

                                <span class="text-[11px]" style="color:#9CA3AF;">
                                    {{ $msg->created_at->diffForHumans() }}
                                </span>

                                @if ($msg->edited_at)
                                    <span class="text-[10px] italic" style="color:#CBD5E1;">(edited)</span>
                                @endif
                            </div>

                            {{-- Message body --}}
                            <p class="text-sm whitespace-pre-wrap leading-relaxed"
                               style="color:#374151;">{{ $msg->message }}</p>
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
                                        class="text-xs flex items-center gap-0.5 transition-colors"
                                        style="color:#CBD5E1;"
                                        onmouseover="this.style.color='#DC2626'"
                                        onmouseout="this.style.color='#CBD5E1'"
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

    {{-- ── Post a message ────────────────────────────────────────────────── --}}
    @if ($canPost)
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
            <h3 class="text-sm font-bold mb-3 flex items-center gap-2" style="color:#1E293B;">
                <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">edit</span>
                Post a Message
            </h3>
            <form method="POST" action="{{ route('workspace.chat.store', $workspace) }}" class="space-y-3">
                @csrf
                <textarea name="message"
                          rows="3"
                          maxlength="5000"
                          placeholder="Type your message here…"
                          required
                          class="w-full rounded-lg px-3 py-2.5 text-sm transition-colors resize-none"
                          style="border:1px solid #CBD5E1; color:#1E293B; background:#fff; outline:none;"
                          onfocus="this.style.borderColor='#0058be'"
                          onblur="this.style.borderColor='#CBD5E1'">{{ old('message') }}</textarea>

                <div class="flex items-center justify-between">
                    @if ($seeInternal)
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="visibility" value="internal"
                                   class="rounded border-border-subtle text-secondary"
                                   {{ old('visibility') === 'internal' ? 'checked' : '' }}>
                            <span class="text-xs font-medium" style="color:#64748B;">
                                Internal message
                                <span class="text-[10px]" style="color:#94A3B8;">(visible to managers and admins only)</span>
                            </span>
                        </label>
                    @else
                        <span class="text-xs" style="color:#94A3B8;">
                            <span class="material-symbols-outlined align-middle" style="font-size:13px;">public</span>
                            Visible to all workspace members
                        </span>
                    @endif

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                            style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                        Send
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="rounded-xl px-5 py-4 text-sm"
             style="background:rgba(107,114,128,0.06);border:1px solid rgba(107,114,128,0.20);color:#6B7280;">
            <span class="material-symbols-outlined align-middle mr-1" style="font-size:16px;">visibility</span>
            You have view-only access to this workspace chat.
        </div>
    @endif

</x-layouts.gvos>
