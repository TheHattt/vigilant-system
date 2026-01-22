<x-layouts::auth>
    <div class="flex flex-col gap-6 mx-auto w-full max-w-lg"
         x-data="{
            email: '{{ old('email', '') }}',
            prefix: '{{ old('account_prefix', '') }}',
            password: '',
            remember: false,
            attemptsLeft: 3,
            error: '',
            loading: false,
            isDisabled: false,
            isSuperAdmin: false,
            timer: 0,
            controller: null,
            hasError: false,

            resetState() {
                this.prefix = '';
                this.isSuperAdmin = false;
                this.hasError = false;
                this.error = '';
            },

            async lookup() {
                if (!this.email.includes('@') || this.email.length < 5) {
                    this.resetState();
                    return;
                }

                if (this.controller) this.controller.abort();
                this.controller = new AbortController();
                this.loading = true;

                try {
                    const response = await fetch(`/auth/tenant-lookup/${encodeURIComponent(this.email)}`, {
                        signal: this.controller.signal
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.hasError = false;
                        this.error = '';

                        if (data.type === 'super_admin') {
                            this.isSuperAdmin = true;
                            this.prefix = '';
                        } else {
                            this.isSuperAdmin = false;
                            this.prefix = data.prefix || '';

                            if (!this.prefix && data.type === 'tenant_user') {
                                // Enterprise Error: Account exists but lacks environment context
                                this.error = '{{ __("Identity verified, but no active service environment was detected. Please contact your Organization Administrator.") }}';
                                this.hasError = true;
                            }
                        }
                    } else {
                        this.resetState();
                        // Professional Masking: Don't confirm if email exists or not
                        this.error = '{{ __("We couldn’t find an account matching that identity. Check your spelling or contact your IT department.") }}';
                        this.hasError = true;
                    }
                } catch (e) {
                    if (e.name !== 'AbortError') {
                        this.error = '{{ __("A secure connection to the identity provider could not be established.") }}';
                    }
                } finally {
                    this.loading = false;
                }
            },

            async submitLogin() {
                if (this.isDisabled) return;

                this.loading = true;
                this.error = '';
                this.hasError = false;

                try {
                    const response = await fetch('{{ route('login') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: this.email,
                            password: this.password,
                            remember: this.remember,
                            account_prefix: this.prefix
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        window.location.href = data.redirect || '/dashboard';
                    } else {
                        // High-Level Security Error
                        this.error = '{{ __("Authentication failed. Please verify your credentials and try again.") }}';
                        this.hasError = true;
                        this.attemptsLeft--;

                        if (this.attemptsLeft <= 0) {
                            this.error = '{{ __("Security protocol active: Excessive failed attempts. Please wait before trying again.") }}';
                            this.startTimer(60);
                        }
                    }
                } catch (e) {
                    this.error = '{{ __("The authorization server is currently unreachable. Please try again later.") }}';
                } finally {
                    this.loading = false;
                }
            },

            startTimer(seconds) {
                this.isDisabled = true;
                this.timer = seconds;
                let interval = setInterval(() => {
                    this.timer--;
                    if (this.timer <= 0) {
                        this.isDisabled = false;
                        this.attemptsLeft = 3;
                        this.error = '';
                        this.hasError = false;
                        clearInterval(interval);
                    }
                }, 1000);
            }
         }"
         x-init="if (email) lookup()">

        <x-auth-header
            :title="__('Sign in')"
            :description="__('ISP Secure Gateway')"
        />

        <form @submit.prevent="submitLogin" class="flex flex-col gap-6">
            @csrf

            <flux:input
                x-model="email"
                @input="resetState()"
                @input.debounce.500ms="lookup()"
                x-bind:readonly="isDisabled"
                name="email"
                :label="__('Work Email')"
                type="email"
                placeholder="name@company.com"
                required
                autofocus
                ::class="(email.includes('@') && !hasError) ? 'ring-1 ring-emerald-500/30 border-emerald-500/50' : (hasError ? 'ring-2 ring-rose-500/50 border-rose-500' : '')"
            />

            <div class="space-y-3">
                <div x-show="error" x-transition class="flex items-start gap-3 text-xs font-medium text-rose-700 bg-rose-50 dark:bg-rose-950/20 p-4 rounded-xl border border-rose-200 dark:border-rose-900/40">
                    <flux:icon.exclamation-triangle variant="micro" class="mt-0.5 shrink-0" />
                    <span x-text="error" class="leading-relaxed"></span>
                </div>

                <div x-show="prefix && !isSuperAdmin" x-transition class="relative group">
                    <flux:input
                        x-model="prefix"
                        name="account_prefix"
                        :label="__('Service Instance Detected')"
                        type="text"
                        readonly
                        variant="filled"
                        class="bg-zinc-100/80 dark:bg-white/5 font-mono text-blue-700 dark:text-blue-400 cursor-not-allowed border-blue-200/50"
                    />
                    <div class="absolute right-3 top-9 flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-blue-600/70 uppercase tracking-tighter">{{ __('Verified') }}</span>
                        <flux:icon.check-badge variant="micro" class="text-emerald-500" />
                    </div>
                </div>

                <div x-show="isSuperAdmin" x-transition class="flex items-center gap-2 p-3 bg-zinc-900 text-zinc-100 rounded-lg text-[10px] font-black uppercase tracking-[0.2em] shadow-xl border border-white/10">
                    <flux:icon.shield-check variant="micro" class="text-amber-400" />
                    <span>{{ __('Elevated Global Access Session') }}</span>
                </div>
            </div>

            <div class="relative p-5 bg-white dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <flux:input
                    x-model="password"
                    x-bind:disabled="isDisabled"
                    name="password"
                    type="password"
                    :label="__('Credential Phrase')"
                    required
                    viewable
                    ::class="hasError ? 'ring-2 ring-rose-500/50 border-rose-500' : ''"
                />
                <div class="mt-3 flex justify-end">
                    @if (Route::has('password.request'))
                        <flux:link x-show="!isDisabled" class="text-[11px] font-semibold text-zinc-500 hover:text-zinc-900" :href="route('password.request')" wire:navigate>
                            {{ __('Forgot your credentials?') }}
                        </flux:link>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between px-1">
                <flux:checkbox x-model="remember" x-bind:disabled="isDisabled" name="remember" :label="__('Trust this device for 30 days')" class="text-xs" />
                <template x-if="attemptsLeft < 3 && attemptsLeft > 0 && !isDisabled">
                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded-full border border-rose-200">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                        </span>
                        <span x-text="attemptsLeft + ' {{ __('ATTEMPTS REMAINING') }}'"></span>
                    </div>
                </template>
            </div>

            <div class="space-y-4">
                <div x-cloak x-show="isDisabled" x-transition>
                    <div class="p-4 bg-zinc-900 text-white rounded-xl text-xs flex items-center justify-between font-mono shadow-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <flux:icon.clock class="animate-pulse text-rose-500" />
                            <span class="tracking-widest">{{ __('SECURITY LOCK ACTIVE') }}</span>
                        </div>
                        <span class="text-rose-500 font-bold" x-text="timer + 's'"></span>
                    </div>
                </div>

                <flux:button
                    x-bind:disabled="isDisabled || loading"
                    variant="primary"
                    type="submit"
                    class="w-full h-12 shadow-lg shadow-blue-500/20 transition-all active:scale-[0.98]"
                >
                    <span x-show="!loading && !isDisabled" class="font-bold tracking-wide">{{ __('Authorize Session') }}</span>
                    <span x-show="loading" class="flex items-center gap-3 font-medium">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('Validating...') }}
                    </span>
                    <span x-show="isDisabled" class="uppercase tracking-widest text-[10px]">{{ __('System Locked') }}</span>
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="mt-4 text-xs text-center text-zinc-500 font-medium">
                {{ __('Administrative Access Only.') }}
                <template x-if="!isDisabled">
                    <flux:link :href="route('register')" wire:navigate class="text-zinc-900 dark:text-white underline underline-offset-4 decoration-zinc-300">
                        {{ __('Apply for Credentials') }}
                    </flux:link>
                </template>
            </div>
        @endif
    </div>
</x-layouts::auth>
