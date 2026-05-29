<x-layouts.public title="Request a Service">
<div class="w-full max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">Operations Management Platform</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

        {{-- Card header --}}
        <div class="bg-indigo-600 px-8 py-6">
            <h1 class="text-xl font-bold text-white">Request a Service</h1>
            <p class="text-indigo-200 text-sm mt-1">
                Tell us about your requirements and our team will review your request and respond with next steps.
            </p>
        </div>

        <form method="POST" action="{{ route('lead.request-service.store') }}" class="px-8 py-8 space-y-8">
            @csrf

            {{-- Global errors --}}
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                <p class="text-sm font-semibold text-red-700 mb-1">Please correct the following:</p>
                <ul class="text-sm text-red-600 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Section 1: Personal Details --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Your Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1">First Name <span class="text-red-500">*</span></label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}"
                               required maxlength="100"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('first_name') border-red-400 @else border-slate-300 @enderror">
                        @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}"
                               required maxlength="100"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('last_name') border-red-400 @else border-slate-300 @enderror">
                        @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               required maxlength="255"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @else border-slate-300 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                               maxlength="50"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-slate-700 mb-1">Country</label>
                        <input id="country" type="text" name="country" value="{{ old('country') }}"
                               maxlength="100" placeholder="e.g. Nigeria"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-slate-700 mb-1">City</label>
                        <input id="city" type="text" name="city" value="{{ old('city') }}"
                               maxlength="100"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="timezone" class="block text-sm font-medium text-slate-700 mb-1">Your Timezone</label>
                        <select id="timezone" name="timezone"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Select timezone —</option>
                            @foreach ($timezones as $tz => $label)
                                <option value="{{ $tz }}" {{ old('timezone') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section 2: Account Type --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Account Type</h2>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="client_type" value="individual"
                               {{ old('client_type', 'individual') === 'individual' ? 'checked' : '' }}
                               class="sr-only" onchange="toggleBusinessFields(this)">
                        <div class="border-2 rounded-xl px-5 py-4 text-center transition-all client-type-card {{ old('client_type', 'individual') === 'individual' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200' }}">
                            <p class="font-semibold text-slate-800 text-sm">Individual</p>
                            <p class="text-xs text-slate-500 mt-0.5">I need support for myself</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="client_type" value="business"
                               {{ old('client_type') === 'business' ? 'checked' : '' }}
                               class="sr-only" onchange="toggleBusinessFields(this)">
                        <div class="border-2 rounded-xl px-5 py-4 text-center transition-all client-type-card {{ old('client_type') === 'business' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200' }}">
                            <p class="font-semibold text-slate-800 text-sm">Business</p>
                            <p class="text-xs text-slate-500 mt-0.5">I represent a company</p>
                        </div>
                    </label>
                </div>

                {{-- Business fields (shown only when client_type = business) --}}
                <div id="business-fields" class="{{ old('client_type') === 'business' ? '' : 'hidden' }} grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
                               maxlength="255"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="company_website" class="block text-sm font-medium text-slate-700 mb-1">Company Website</label>
                        <input id="company_website" type="url" name="company_website" value="{{ old('company_website') }}"
                               maxlength="255" placeholder="https://"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="company_email_domain" class="block text-sm font-medium text-slate-700 mb-1">Company Email Domain</label>
                        <input id="company_email_domain" type="text" name="company_email_domain" value="{{ old('company_email_domain') }}"
                               maxlength="255" placeholder="e.g. acme.com"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-slate-400 mt-1">Used to match staff email addresses during onboarding.</p>
                    </div>
                </div>
            </div>

            {{-- Section 3: Service Requirements --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Service Requirements</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="role_needed" class="block text-sm font-medium text-slate-700 mb-1">Role Needed</label>
                        <select id="role_needed" name="role_needed"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                onchange="toggleOtherRole(this)">
                            <option value="">— Select a role —</option>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role_needed') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="other-role-field" class="{{ old('role_needed') === 'other' ? '' : 'hidden' }}">
                        <label for="role_needed_other" class="block text-sm font-medium text-slate-700 mb-1">Please specify</label>
                        <input id="role_needed_other" type="text" name="role_needed_other" value="{{ old('role_needed_other') }}"
                               maxlength="255"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="estimated_hours_per_week" class="block text-sm font-medium text-slate-700 mb-1">Estimated Hours / Week</label>
                        <input id="estimated_hours_per_week" type="number" name="estimated_hours_per_week"
                               value="{{ old('estimated_hours_per_week') }}" min="1" max="168"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="preferred_start_date" class="block text-sm font-medium text-slate-700 mb-1">Preferred Start Date</label>
                        <input id="preferred_start_date" type="date" name="preferred_start_date"
                               value="{{ old('preferred_start_date') }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="preferred_work_schedule" class="block text-sm font-medium text-slate-700 mb-1">Preferred Work Schedule</label>
                        <input id="preferred_work_schedule" type="text" name="preferred_work_schedule"
                               value="{{ old('preferred_work_schedule') }}" maxlength="255"
                               placeholder="e.g. Mon–Fri 9am–5pm, Flexible, etc."
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="required_skills" class="block text-sm font-medium text-slate-700 mb-1">Required Skills</label>
                        <input id="required_skills" type="text" name="required_skills"
                               value="{{ old('required_skills') }}" maxlength="1000"
                               placeholder="e.g. Calendar management, Excel, social media scheduling"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="work_description" class="block text-sm font-medium text-slate-700 mb-1">Work Description</label>
                        <textarea id="work_description" name="work_description" rows="4"
                                  maxlength="5000" placeholder="Describe the tasks and responsibilities you need support with..."
                                  class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y">{{ old('work_description') }}</textarea>
                        <p class="text-xs text-slate-400 mt-1">Maximum 5,000 characters.</p>
                    </div>
                </div>
            </div>

            {{-- Section 4: Budget & Source --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Budget &amp; Additional Info</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="budget_range" class="block text-sm font-medium text-slate-700 mb-1">Monthly Budget Range</label>
                        <select id="budget_range" name="budget_range"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Select a range —</option>
                            @foreach ($budgetRanges as $value => $label)
                                <option value="{{ $value }}" {{ old('budget_range') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="source" class="block text-sm font-medium text-slate-700 mb-1">How did you hear about GVOS?</label>
                        <input id="source" type="text" name="source" value="{{ old('source') }}"
                               maxlength="255" placeholder="e.g. Google, referral, LinkedIn"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-3 rounded-xl transition-colors">
                    Submit Service Request
                </button>
                <p class="text-center text-xs text-slate-400 mt-4 leading-relaxed">
                    By submitting this form, you acknowledge that your information will be reviewed
                    by the GVOS team and used to process your service request.
                    Activity on this platform is monitored for quality and compliance purposes.
                </p>
            </div>
        </form>
    </div>

    <p class="text-center text-xs text-slate-500 mt-6">
        Already have an account?
        <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300">Sign in here</a>
    </p>
</div>

<script>
    // Toggle business fields based on client_type selection
    function toggleBusinessFields(radio) {
        const businessFields = document.getElementById('business-fields');
        const cards = document.querySelectorAll('.client-type-card');
        cards.forEach(c => {
            c.classList.remove('border-indigo-500', 'bg-indigo-50');
            c.classList.add('border-slate-200');
        });
        radio.closest('label').querySelector('.client-type-card')
            .classList.add('border-indigo-500', 'bg-indigo-50');
        radio.closest('label').querySelector('.client-type-card')
            .classList.remove('border-slate-200');
        businessFields.classList.toggle('hidden', radio.value !== 'business');
    }

    // Toggle "other role" text field
    function toggleOtherRole(select) {
        document.getElementById('other-role-field').classList.toggle('hidden', select.value !== 'other');
    }

    // Init on page load (for back-navigation with old values)
    document.addEventListener('DOMContentLoaded', function() {
        const checkedType = document.querySelector('input[name="client_type"]:checked');
        if (checkedType) {
            toggleBusinessFields(checkedType);
        }
        const roleSelect = document.getElementById('role_needed');
        if (roleSelect && roleSelect.value === 'other') {
            document.getElementById('other-role-field').classList.remove('hidden');
        }
    });
</script>
</x-layouts.public>
