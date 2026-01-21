<x-layouts::auth>
    <div class="flex flex-col gap-6"
         x-data="{
            email: '{{ old('email', '') }}',
            prefix: '{{ old('account_prefix', '') }}',
            attemptsLeft: 3,
            error: '',
            loading: false,
            isDisabled: false,
            timer: 0,
            controller: null,

            async lookup() {
                if (!this.email.includes('@') || this.email.length < 5) {
                    this.prefix = '';
                    this.error = '';
                    return;
                }

                if (this.controller) this.controller.abort();
                this.controller = new AbortController();

                this.loading = true;
                try {
                    const response = await fetch(`/auth/tenant-lookup/${encodeURIComponent(this.email)}`, {
                        signal: this.controller.signal
                    });

                    if (response.status === 429) {
                        const retryAfter = response.headers.get('Retry-After') || 60;
                        this.error = 'Too many attempts.';
                        this.startTimer(parseInt(retryAfter));
                        return;
                    }

                    const data = await response.json();

                    if (data.prefix) {
                        this.prefix = data.prefix;
                        this.error = '';
                    } else {
                        this.prefix = '';
                        this.error = '{{ __("If account exists, we will contact you.") }}';
                    }
                } catch (e) {
                    if (e.name !== 'AbortError') {
                        this.error = '{{ __("If account exists, we will contact you.") }}';
                    }
                } finally {
                    this.loading = false;
                }
            },

            init() {
                @if($errors->has('email'))
                    const serverError = '{{ $errors->first('email') }}';
                    this.error = serverError;

                    const match = serverError.match(/\d+/);
                    if (match && serverError.includes('seconds')) {
                        this.startTimer(parseInt(match[0]));
                    }
                @endif

                if (this.email) {
                    this.lookup();
                }
            },

            startTimer(seconds) {
                this.isDisabled = true;
                this.timer = seconds;
                let interval = setInterval(() => {
                    this.timer--;
                    if (this.timer <= 0) {
                        this.isDisabled = false;
                        this.error = '';
                        this.attemptsLeft = 3;
                        clearInterval(interval);
                    }
                }, 1000);
            }
         }"
         x-init="init()">

        <x-auth-header :title="__('Log in')" :description="__('Enter your details to access your ISP dashboard')" />

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                x-model="email"
                @input.debounce.500ms="lookup()"
                x-bind:readonly="isDisabled"
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                :messages="$errors->first('email')"
            />

            <div class="space-y-2">
                <div x-show="error" x-transition class="text-xs font-semibold text-rose-600">
                    <span x-text="error"></span>
                </div>

                <div x-show="prefix" x-transition>
                    <flux:input
                        x-model="prefix"
                        name="account_prefix"
                        :label="__('ISP Account Detected')"
                        type="text"
                        readonly
                        variant="filled"
                        class="bg-zinc-50 dark:bg-white/5"
                    />
                </div>
            </div>

            <div class="relative">
                <flux:input
                    x-bind:disabled="isDisabled"
                    name="password"
                    type="password"
                    :label="__('Password')"
                    required
                    viewable
                />

                <div class="absolute top-0 end-0">
                    @if (Route::has('password.request'))
                        <template x-if="!isDisabled">
                            <flux:link class="text-xs" :href="route('password.request')" wire:navigate>
                                {{ __('Forgot?') }}
                            </flux:link>
                        </template>
                        <template x-if="isDisabled">
                            <span class="text-xs text-zinc-400 cursor-not-allowed">{{ __('Forgot?') }}</span>
                        </template>
                    @endif
                </div>
            </div>

            <flux:checkbox x-bind:disabled="isDisabled" name="remember" :label="__('Remember me')" />

            {{-- UX: Remaining attempts warning --}}
            <template x-if="attemptsLeft < 3 && attemptsLeft > 0 && !isDisabled">
                <div class="text-xs text-amber-600 font-medium flex items-center gap-1">
                    <flux:icon.exclamation-triangle variant="micro" />
                    <span>{{ __('Careful, you have') }} <span x-text="attemptsLeft"></span> {{ __('attempts remaining.') }}</span>
                </div>
            </template>

            {{-- Lockout UI --}}
            <div x-cloak x-show="isDisabled" x-transition>
                <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center gap-2 dark:bg-red-900/20 dark:border-red-900/30 dark:text-red-400">
                    <flux:icon.lock-closed variant="micro" />
                    <span>{{ __('Security Lock:') }} Try again in <span x-text="timer"></span>s.</span>
                </div>
            </div>

            <flux:button
                x-bind:disabled="isDisabled || loading"
                variant="primary"
                type="submit"
                class="w-full"
            >
                <span x-show="!isDisabled && !loading">{{ __('Log in') }}</span>
                <span x-show="loading">{{ __('Identifying...') }}</span>
                <span x-show="isDisabled">{{ __('System Locked') }}</span>
            </flux:button>
        </form>

        @if (Route::has('register'))
            <div class="text-sm text-center text-zinc-600 dark:text-zinc-400">
                {{ __('New ISP?') }}
                <template x-if="!isDisabled">
                    <flux:link :href="route('register')" wire:navigate>{{ __('Create an account') }}</flux:link>
                </template>
                <template x-if="isDisabled">
                    <span class="text-zinc-400 cursor-not-allowed">{{ __('Create an account') }}</span>
                </template>
            </div>
        @endif
    </div>
</x-layouts::auth>
