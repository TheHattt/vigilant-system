<x-layouts::auth>
    <div class="flex flex-col gap-6"
         x-data="{
            email: '{{ old('email', '') }}',
            prefix: '{{ old('account_prefix', '') }}',
            error: '',
            loading: false,
            isDisabled: false,
            timer: 0,
            errorMessage: '{{ $errors->first('email') }}',
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
                    const data = await response.json();
                    if (data.prefix) {
                        this.prefix = data.prefix;
                        this.error = '';
                    } else {
                        this.prefix = '';
                        this.error = '{{ __("If account exists, we will contact you!") }}';
                    }
                } catch (e) {
                    if (e.name !== 'AbortError') this.error = '{{ __("If account exists, we will contact you!") }}';
                } finally {
                    this.loading = false;
                }
            },

            init() {
                if (this.errorMessage.includes('seconds')) {
                    let matches = this.errorMessage.match(/\d+/);
                    if (matches) this.startTimer(parseInt(matches[0]));
                }
            },

            startTimer(seconds) {
                this.isDisabled = true;
                this.timer = seconds;
                let interval = setInterval(() => {
                    this.timer--;
                    if (this.timer <= 0) {
                        this.isDisabled = false;
                        this.errorMessage = '';
                        clearInterval(interval);
                    }
                }, 1000);
            }
         }">

        <x-auth-header :title="__('Log in')" :description="__('Enter your details to access your ISP dashboard')" />

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
            @csrf

            {{-- Email --}}
            <flux:input
                x-model="email"
                @input.debounce.300ms="lookup()"
                x-bind:disabled="isDisabled"
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            {{-- Error/Prefix Slide-in Area --}}
            <div class="space-y-3">
                <div x-show="error" x-transition class="text-xs font-semibold text-rose-600 dark:text-rose-400">
                    <span x-text="error"></span>
                </div>

                <div x-show="prefix" x-transition:enter="transition ease-out duration-400" x-transition:enter-start="opacity-0 -translate-y-4">
                    <flux:input
                        x-model="prefix"
                        name="account_prefix"
                        :label="__('ISP Account Detected')"
                        type="text"
                        readonly
                        variant="filled"
                        class="font-mono text-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/20"
                    />
                </div>
            </div>

            {{-- Password --}}
            <div class="relative">
                <flux:input
                    x-bind:disabled="isDisabled"
                    name="password"
                    type="password"
                    :label="__('Password')"
                    required
                    viewable
                />
                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-xs end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Remember me')" />

            {{-- Security Lock Alert --}}
            <div x-show="isDisabled" x-transition>
                <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center gap-2 dark:bg-red-900/20 dark:border-red-900/30 dark:text-red-400">
                    <flux:icon.clock variant="micro" />
                    <span>{{ __('Security Lock:') }} Try again in <span class="font-bold" x-text="timer"></span>s.</span>
                </div>
            </div>

            <flux:button
                x-bind:disabled="isDisabled || loading"
                variant="primary"
                type="submit"
                class="w-full"
            >
                <span x-show="!isDisabled && !loading">{{ __('Log in') }}</span>
                <span x-show="loading" class="flex items-center gap-2"><flux:icon.loading variant="micro" /> {{ __('Searching...') }}</span>
                <span x-show="isDisabled">{{ __('Locked') }}</span>
            </flux:button>
        </form>

        @if (Route::has('register'))
            <div class="text-sm text-center text-zinc-600 dark:text-zinc-400">
                {{ __('New ISP?') }} <flux:link :href="route('register')" wire:navigate>{{ __('Create an account') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
