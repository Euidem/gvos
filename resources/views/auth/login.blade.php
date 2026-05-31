<x-layouts.auth title="Sign in" variant="split">
{{--
    UI Batch 1b — Login page aligned with Stitch login_gvos_1
    Stitch source: design-reference/stitch_gvos_operations_platform/login_gvos_1/code.html
--}}
<main class="flex min-h-screen w-full">

    {{-- ── LEFT PANEL: Form (45% desktop, full-width mobile) ─────────────── --}}
    {{-- Stitch: w-full lg:w-[45%], bg-surface-container-lowest (#fff), px-gutter --}}
    <section class="flex flex-col justify-center items-center w-full lg:w-[45%] px-gutter relative z-10"
             style="background-color:#ffffff">
        <div class="w-full max-w-[400px] flex flex-col space-y-8">

            {{-- ── Brand header (Stitch: security icon + GVOS headline + subtitle) --}}
            <header class="flex flex-col space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-secondary rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white"
                              style="font-variation-settings: 'FILL' 1; font-size: 20px;">security</span>
                    </div>
                    <h1 class="font-headline-md text-headline-md font-black text-secondary">GVOS</h1>
                </div>
                <div class="space-y-1">
                    <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">Welcome Back</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Secure workspaces for managed virtual talent
                    </p>
                </div>
            </header>

            {{-- ── Session status (e.g. password reset success) ──────────────── --}}
            @if (session('status'))
                <div class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
                     style="background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.25);color:#065F46;">
                    <span class="material-symbols-outlined flex-shrink-0"
                          style="font-size: 18px; font-variation-settings: 'FILL' 1;">check_circle</span>
                    {{ session('status') }}
                </div>
            @endif

            {{-- ── Login form ───────────────────────────────────────────────── --}}
            <form method="POST" action="{{ route('login') }}"
                  class="flex flex-col space-y-input-gap">
                @csrf

                {{-- Business Email --}}
                <div class="space-y-2">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider"
                           for="email">
                        Business Email
                    </label>
                    <div class="relative group">
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}"
                               autocomplete="username" autofocus required
                               placeholder="name@company.com"
                               class="w-full h-12 bg-surface border border-border-subtle rounded-lg px-4
                                      font-body-md text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      transition-all
                                      @error('email') border-status-blocked @enderror">
                    </div>
                    @error('email')
                        <p class="font-body-sm text-body-sm text-status-blocked flex items-center gap-1.5 mt-1">
                            <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Security Key (password) --}}
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider"
                               for="password">
                            Security Key
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="font-label-md text-label-md text-secondary hover:underline transition-all">
                                Reset Access
                            </a>
                        @endif
                    </div>
                    {{-- Password field with show/hide toggle (Stitch: visibility icon button) --}}
                    <div class="relative group">
                        <input id="password" type="password" name="password"
                               autocomplete="current-password" required
                               placeholder="••••••••"
                               class="w-full h-12 bg-surface border border-border-subtle rounded-lg px-4 pr-12
                                      font-body-md text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      transition-all
                                      @error('password') border-status-blocked @enderror">
                        {{-- Show/hide toggle --}}
                        <button type="button"
                                id="toggle-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors"
                                aria-label="Toggle password visibility"
                                onclick="
                                    const p = document.getElementById('password');
                                    const i = document.getElementById('pw-toggle-icon');
                                    if (p.type === 'password') {
                                        p.type = 'text';
                                        i.textContent = 'visibility_off';
                                    } else {
                                        p.type = 'password';
                                        i.textContent = 'visibility';
                                    }
                                ">
                            <span class="material-symbols-outlined" id="pw-toggle-icon" style="font-size:20px;">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="font-body-sm text-body-sm text-status-blocked flex items-center gap-1.5 mt-1">
                            <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember me — Stitch: "Persistent session for 24 hours" --}}
                <div class="flex items-center space-x-3 pt-2">
                    <input id="remember" type="checkbox" name="remember"
                           class="w-4 h-4 rounded border-border-subtle text-secondary focus:ring-secondary">
                    <label class="font-body-sm text-body-sm text-on-surface-variant" for="remember">
                        Persistent session for 24 hours
                    </label>
                </div>

                {{-- Initialize Session button (Stitch: bg-secondary h-12 rounded-lg arrow_forward icon) --}}
                <button type="submit"
                        class="w-full h-12 bg-secondary text-white font-label-md text-label-md rounded-lg
                               flex items-center justify-center space-x-2
                               hover:brightness-110 active:scale-[0.98] transition-all shadow-sm">
                    <span>Initialize Session</span>
                    <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
                </button>

            </form>

            {{-- ── Security note footer (Stitch: verified_user icon + AES-256 note) ── --}}
            <footer class="pt-8 border-t border-border-subtle">
                <div class="flex items-start space-x-3 bg-surface-container-low p-4 rounded-xl">
                    <span class="material-symbols-outlined text-status-active flex-shrink-0"
                          style="font-size: 20px; font-variation-settings: 'FILL' 1;">verified_user</span>
                    <div class="space-y-1">
                        <p class="font-label-md text-label-md text-on-surface">Security Protocol Active</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                            You are accessing a GVOS Enterprise Node. All sessions are encrypted
                            and monitored for compliance standards.
                        </p>
                    </div>
                </div>
                <div class="mt-8 flex justify-between items-center text-outline">
                    <p class="font-label-md text-label-md">© {{ date('Y') }} GVOS Enterprise Ops</p>
                    <div class="flex space-x-4">
                        <span class="font-label-md text-label-md">Legal</span>
                        <span class="font-label-md text-label-md">Support</span>
                    </div>
                </div>
            </footer>

        </div>
    </section>

    {{-- ── RIGHT PANEL: Decorative security visual (55% desktop, hidden mobile) ── --}}
    {{-- Stitch: hidden lg:flex, bg-sidebar-bg (#0B0F19) with glass cards --}}
    <section class="hidden lg:flex lg:w-[55%] relative overflow-hidden flex-col justify-center items-center"
             style="background-color:#0B0F19">

        {{-- Dot grid overlay (CSS only — no external images) --}}
        <div class="absolute inset-0 pointer-events-none opacity-30"
             style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
                    background-size: 32px 32px;"></div>

        {{-- Subtle radial glow from center --}}
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at 50% 50%, rgba(0,88,190,0.15) 0%, transparent 70%);"></div>

        {{-- Content --}}
        <div class="relative z-20 flex flex-col justify-center items-center h-full w-full p-16 text-center">
            <div class="space-y-6 max-w-sm w-full">

                {{-- Floating Card 1: Node Distribution --}}
                <div class="login-glass border border-white/10 rounded-2xl p-card-padding text-left shadow-2xl animate-float-slow">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                             style="background-color:rgba(173,198,255,0.2)">
                            <span class="material-symbols-outlined"
                                  style="color:#adc6ff; font-size:22px;">hub</span>
                        </div>
                        <div>
                            <p class="font-label-md text-label-md uppercase"
                               style="color:#adc6ff;">Node Distribution</p>
                            <p class="font-headline-md text-headline-md text-white">Global Access Hub</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="h-2 w-full rounded-full overflow-hidden" style="background:rgba(255,255,255,0.05)">
                            <div class="h-full rounded-full" style="width:75%;background:#adc6ff;"></div>
                        </div>
                        <div class="flex justify-between font-mono-sm text-mono-sm" style="color:rgba(255,255,255,0.5)">
                            <span>LHR-4: Active</span>
                            <span>99.9% Uptime</span>
                        </div>
                    </div>
                </div>

                {{-- Floating Card 2: Security Audit (offset right per Stitch) --}}
                <div class="login-glass border border-white/10 rounded-2xl p-card-padding text-left shadow-2xl
                            ml-12 animate-float-delayed">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                             style="background-color:rgba(16,185,129,0.2)">
                            <span class="material-symbols-outlined"
                                  style="color:#10B981; font-size:22px;">fingerprint</span>
                        </div>
                        <div>
                            <p class="font-label-md text-label-md uppercase"
                               style="color:#10B981;">Security Audit</p>
                            <p class="font-headline-md text-headline-md text-white">Access Verified</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom decorative bar --}}
            <div class="absolute bottom-12 left-12 right-12 flex justify-between items-end">
                <div class="text-left">
                    <p class="font-headline-lg text-headline-lg select-none"
                       style="color:rgba(255,255,255,0.12);letter-spacing:-0.02em;">PRECISION</p>
                    <p class="font-headline-md text-headline-md" style="color:#adc6ff;">
                        Operational Excellence
                    </p>
                </div>
                {{-- User count / team indicator --}}
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                        <div class="w-9 h-9 rounded-full border-2 flex items-center justify-center text-xs font-bold text-white"
                             style="background-color:#2170e4;border-color:#0B0F19;">RM</div>
                        <div class="w-9 h-9 rounded-full border-2 flex items-center justify-center text-xs font-bold text-white"
                             style="background-color:#059669;border-color:#0B0F19;">SC</div>
                        <div class="w-9 h-9 rounded-full border-2 flex items-center justify-center text-[10px] font-bold text-white"
                             style="background-color:#0058be;border-color:#0B0F19;">+2.4k</div>
                    </div>
                    <p class="font-label-md text-label-md" style="color:rgba(255,255,255,0.5);">
                        Active users
                    </p>
                </div>
            </div>
        </div>

    </section>

</main>

{{-- Subtle slide-in animation for form panel (Stitch micro-interaction) --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const formPanel = document.querySelector('main > section:first-child');
    if (formPanel) {
        formPanel.style.opacity = '0';
        formPanel.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            formPanel.style.transition = 'all 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
            formPanel.style.opacity = '1';
            formPanel.style.transform = 'translateX(0)';
        }, 80);
    }
});
</script>

</x-layouts.auth>
