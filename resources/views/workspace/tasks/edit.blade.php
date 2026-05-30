<x-layouts.gvos title="Edit Task">

    {{-- ── Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-6">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.tasks.index', $workspace) }}" class="hover:text-secondary transition-colors">Task Board</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.tasks.show', [$workspace, $task]) }}" class="hover:text-secondary transition-colors font-mono">{{ $task->task_code }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Edit</span>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
            <div class="h-1 w-full" style="background-color:#0058be"></div>

            <div class="p-8">
                <h2 class="text-lg font-bold text-[#191c1e] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#0058be]" style="font-size: 20px;">edit_note</span>
                    Edit Task
                </h2>
                <p class="text-xs text-outline mb-6 font-mono">{{ $task->task_code }}</p>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-status-blocked/10 border border-status-blocked/20 rounded-lg">
                        <p class="text-sm font-semibold text-status-blocked mb-2">Please fix the following:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-xs text-status-blocked">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('workspace.tasks.update', [$workspace, $task]) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Title --}}
                    <div>
                        <label for="title" class="block text-xs font-semibold text-[#45464d] uppercase tracking-wider mb-1.5">
                            Task Title <span class="text-status-blocked">*</span>
                        </label>
                        <input id="title" name="title" type="text"
                               value="{{ old('title', $task->title) }}"
                               required maxlength="255"
                               class="w-full px-4 py-3 border border-[#E2E8F0] rounded-lg text-sm text-[#191c1e] focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] @error('title') border-status-blocked @enderror">
                        @error('title')
                            <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-xs font-semibold text-[#45464d] uppercase tracking-wider mb-1.5">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="5"
                                  class="w-full px-4 py-3 border border-[#E2E8F0] rounded-lg text-sm text-[#191c1e] focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] resize-y @error('description') border-status-blocked @enderror">{{ old('description', $task->description) }}</textarea>
                    </div>

                    {{-- Assign to + Priority --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="assigned_to_user_id" class="block text-xs font-semibold text-[#45464d] uppercase tracking-wider mb-1.5">
                                Assign To
                            </label>
                            <select id="assigned_to_user_id" name="assigned_to_user_id"
                                    class="w-full px-4 py-3 border border-[#E2E8F0] rounded-lg text-sm text-[#191c1e] focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] bg-white">
                                <option value="">— Unassigned —</option>
                                @foreach ($members as $member)
                                    @if ($member->user)
                                        <option value="{{ $member->user->id }}"
                                                {{ old('assigned_to_user_id', $task->assigned_to_user_id) == $member->user->id ? 'selected' : '' }}>
                                            {{ $member->user->name }} ({{ ucfirst($member->role) }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="priority" class="block text-xs font-semibold text-[#45464d] uppercase tracking-wider mb-1.5">
                                Priority <span class="text-status-blocked">*</span>
                            </label>
                            <select id="priority" name="priority" required
                                    class="w-full px-4 py-3 border border-[#E2E8F0] rounded-lg text-sm text-[#191c1e] focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] bg-white">
                                @foreach (\App\Models\WorkspaceTask::priorityLabels() as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('priority', $task->priority) === $val ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Due date --}}
                    <div>
                        <label for="due_date" class="block text-xs font-semibold text-[#45464d] uppercase tracking-wider mb-1.5">
                            Due Date
                        </label>
                        <input id="due_date" name="due_date" type="date"
                               value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-3 border border-[#E2E8F0] rounded-lg text-sm text-[#191c1e] focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be]">
                    </div>

                    {{-- Internal notes (admin/manager only) --}}
                    @if ($isAdminOrManager)
                    <div>
                        <label for="internal_notes" class="block text-xs font-semibold text-[#45464d] uppercase tracking-wider mb-1.5">
                            Internal Notes
                            <span class="normal-case font-normal text-outline ml-1">(admins and managers only)</span>
                        </label>
                        <textarea id="internal_notes" name="internal_notes" rows="3"
                                  class="w-full px-4 py-3 border border-[#E2E8F0] rounded-lg text-sm text-[#191c1e] focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] resize-y">{{ old('internal_notes', $task->internal_notes) }}</textarea>
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-all shadow-sm flex items-center gap-2"
                                style="background-color:#0058be">
                            <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                            Save Changes
                        </button>
                        <a href="{{ route('workspace.tasks.show', [$workspace, $task]) }}"
                           class="px-6 py-3 rounded-lg text-sm font-semibold text-[#45464d] bg-[#F8FAFC] border border-[#E2E8F0] hover:bg-[#eceef0] transition-all">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layouts.gvos>
