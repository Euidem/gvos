<x-layouts.gvos :title="$workspace->name . ' — Files'">

@php
    $currentUserId = (int) auth()->id();
    $canManage = in_array($role, ['admin', 'workspace_admin', 'manager'], true);
@endphp

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Files</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">folder_open</span>
                File Library
            </h2>
            <p class="text-xs text-outline mt-0.5">{{ $workspace->workspace_code }} &middot; {{ $files->count() }} file{{ $files->count() !== 1 ? 's' : '' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('workspace.chat.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be; color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 14px;">forum</span>
                Chat
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Upload form (left panel, 1/3) ──────────────────────────────── --}}
        @if ($canUpload)
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5 sticky top-6">
                <h3 class="text-sm font-bold mb-4 flex items-center gap-2" style="color:#1E293B;">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">upload_file</span>
                    Upload File
                </h3>
                <form method="POST"
                      action="{{ route('workspace.files.store', $workspace) }}"
                      enctype="multipart/form-data"
                      class="space-y-3">
                    @csrf

                    {{-- File input --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:#374151;">
                            File <span style="color:#DC2626;">*</span>
                        </label>
                        <input type="file"
                               name="file"
                               required
                               accept="{{ implode(',', array_map(fn($m) => '.' . $m, $allowedMimes)) }}"
                               class="w-full text-xs rounded-lg border px-3 py-2"
                               style="border-color:#CBD5E1; color:#374151; background:#fff;">
                        <p class="text-[10px] mt-1" style="color:#9CA3AF;">
                            Max 10 MB · {{ implode(', ', array_map('strtoupper', $allowedMimes)) }}
                        </p>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:#374151;">Title <span style="color:#9CA3AF;">(optional)</span></label>
                        <input type="text"
                               name="title"
                               value="{{ old('title') }}"
                               maxlength="255"
                               placeholder="e.g. Project Brief v2"
                               class="w-full rounded-lg px-3 py-2 text-sm transition-colors"
                               style="border:1px solid #CBD5E1; color:#1E293B; background:#fff; outline:none;"
                               onfocus="this.style.borderColor='#0058be'"
                               onblur="this.style.borderColor='#CBD5E1'">
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:#374151;">Category</label>
                        <select name="category"
                                class="w-full rounded-lg px-3 py-2 text-sm transition-colors"
                                style="border:1px solid #CBD5E1; color:#1E293B; background:#fff; outline:none;"
                                onfocus="this.style.borderColor='#0058be'"
                                onblur="this.style.borderColor='#CBD5E1'">
                            @foreach ($categoryLabels as $value => $label)
                                <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:#374151;">Description <span style="color:#9CA3AF;">(optional)</span></label>
                        <textarea name="description"
                                  rows="2"
                                  maxlength="2000"
                                  placeholder="Brief note about this file…"
                                  class="w-full rounded-lg px-3 py-2 text-sm resize-none transition-colors"
                                  style="border:1px solid #CBD5E1; color:#1E293B; background:#fff; outline:none;"
                                  onfocus="this.style.borderColor='#0058be'"
                                  onblur="this.style.borderColor='#CBD5E1'">{{ old('description') }}</textarea>
                    </div>

                    {{-- Internal toggle (admin / manager / workspace_admin only) --}}
                    @if ($seeInternal)
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox"
                                   name="visibility"
                                   value="internal"
                                   {{ old('visibility') === 'internal' ? 'checked' : '' }}
                                   class="rounded border-border-subtle">
                            <span class="text-xs font-medium" style="color:#64748B;">
                                Internal file
                                <span class="text-[10px]" style="color:#94A3B8;">(managers/admins only)</span>
                            </span>
                        </label>
                    @endif

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110 mt-1"
                            style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">upload</span>
                        Upload
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- ── File list (right panel, 2/3) ─────────────────────────────── --}}
        <div class="{{ $canUpload ? 'lg:col-span-2' : 'lg:col-span-3' }}">

            @if ($files->isEmpty())
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-12 text-center">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                         style="background-color:rgba(0,88,190,.06);">
                        <span class="material-symbols-outlined" style="font-size: 26px; color:#0058be;">folder_open</span>
                    </div>
                    <h4 class="text-sm font-semibold mb-1" style="color:#1E293B;">No files yet</h4>
                    <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                        @if ($canUpload)
                            Upload the first file using the form on the left.
                        @else
                            No files have been shared in this workspace yet.
                        @endif
                    </p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($files as $file)
                    @php
                        $canDeleteFile = $canManage || (int) $file->uploaded_by_user_id === $currentUserId;
                    @endphp
                    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"
                         style="{{ $file->isInternal() ? 'border-left: 3px solid #0058be;' : '' }}">
                        <div class="flex items-start gap-3">

                            {{-- File type icon --}}
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background-color:rgba(0,88,190,.06);">
                                <span class="material-symbols-outlined" style="font-size: 20px; color:#0058be;">{{ $file->typeIcon() }}</span>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold truncate" style="color:#1E293B;">
                                            {{ $file->title ?: $file->original_filename }}
                                        </p>
                                        @if ($file->title && $file->title !== $file->original_filename)
                                            <p class="text-[11px] truncate" style="color:#9CA3AF;">{{ $file->original_filename }}</p>
                                        @endif
                                    </div>
                                    {{-- Visibility badge --}}
                                    @if ($file->isInternal())
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded flex-shrink-0"
                                              style="background:rgba(0,88,190,0.10);color:#0058be;">
                                            Internal
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded flex-shrink-0"
                                              style="background:rgba(5,150,105,0.10);color:#059669;">
                                            Public
                                        </span>
                                    @endif
                                </div>

                                {{-- Meta row --}}
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                    {{-- Category --}}
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded"
                                          style="background:#F1F5F9;color:#64748B;">
                                        {{ $file->categoryLabel() }}
                                    </span>
                                    {{-- Size --}}
                                    <span class="text-[11px]" style="color:#9CA3AF;">{{ $file->formattedSize() }}</span>
                                    {{-- Uploaded by --}}
                                    <span class="text-[11px]" style="color:#9CA3AF;">
                                        <span class="material-symbols-outlined align-middle" style="font-size:11px;">person</span>
                                        {{ $file->uploadedBy->name ?? 'Unknown' }}
                                    </span>
                                    {{-- Date --}}
                                    <span class="text-[11px]" style="color:#9CA3AF;">
                                        <span class="material-symbols-outlined align-middle" style="font-size:11px;">calendar_today</span>
                                        {{ $file->created_at->format('d M Y') }}
                                    </span>
                                    {{-- Downloads --}}
                                    @if ($file->downloads_count > 0)
                                        <span class="text-[11px]" style="color:#9CA3AF;">
                                            <span class="material-symbols-outlined align-middle" style="font-size:11px;">download</span>
                                            {{ $file->downloads_count }}
                                        </span>
                                    @endif
                                </div>

                                @if ($file->description)
                                    <p class="text-xs mt-1.5 leading-relaxed" style="color:#6B7280;">
                                        {{ Str::limit($file->description, 120) }}
                                    </p>
                                @endif

                                {{-- Task attachment link --}}
                                @if ($file->workspace_task_id)
                                    @php $linkedTask = $file->task; @endphp
                                    @if ($linkedTask)
                                        <p class="text-[11px] mt-1" style="color:#9CA3AF;">
                                            <span class="material-symbols-outlined align-middle" style="font-size:11px;">task</span>
                                            Attached to:
                                            <a href="{{ route('workspace.tasks.show', [$workspace, $linkedTask]) }}"
                                               class="hover:underline font-medium" style="color:#0058be;">
                                                {{ $linkedTask->task_code }} — {{ Str::limit($linkedTask->title, 40) }}
                                            </a>
                                        </p>
                                    @endif
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ route('workspace.files.download', [$workspace, $file]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-all hover:brightness-110"
                                   style="background-color:#0058be;">
                                    <span class="material-symbols-outlined" style="font-size: 13px;">download</span>
                                    Download
                                </a>

                                @if ($canDeleteFile)
                                    <form method="POST"
                                          action="{{ route('workspace.files.destroy', [$workspace, $file]) }}"
                                          onsubmit="return confirm('Delete this file?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all border"
                                                style="border-color:#FECACA;color:#DC2626;background:#FFF5F5;"
                                                onmouseover="this.style.background='#FEE2E2'"
                                                onmouseout="this.style.background='#FFF5F5'">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">delete</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</x-layouts.gvos>
