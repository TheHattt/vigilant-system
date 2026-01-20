<x-layouts::auth>
    {{-- Container to control the maximum width --}}
    <div class="flex flex-col gap-6 mx-auto w-full max-w-3xl"
         x-data="{
            company: '{{ old('company_name', '') }}',
            {{-- Slug logic: lowercase, replaces spaces with hyphens --}}
            get slug() { return this.company.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-') },
            {{-- Prefix logic: Takes first letter of each word, max 3 chars --}}
            get prefix() {
                return this.company.split(' ').map(word => word[0]).join('').toLowerCase().slice(0, 10)
            }
         }">

        <x-auth-header :title="__('Create an account')" :description="__('Register your company and setup your admin account.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            {{-- Row 1: Admin Name & Email --}}
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                <flux:input
                    name="name"
                    :label="__('Admin Name')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Full name')"
                />

                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                />
            </div>

            {{-- Row 2: Company Name (Triggers the JS) --}}
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                <flux:input
                    x-model="company"
                    name="company_name"
                    :label="__('Company Name')"
                    type="text"
                    required
                    :placeholder="__('e.g. Talyx Company')"
                />

                {{-- Row 3: Account Prefix & System Slug --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        name="account_prefix"
                        x-bind:value="prefix"
                        :label="__('Account Prefix')"
                        type="text"
                        required
                        maxlength="10"
                        readonly
                        variant="filled"
                        class="font-mono"
                    />

                    <flux:input
                        name="slug"
                        x-bind:value="slug"
                        :label="__('System Slug')"
                        type="text"
                        readonly
                        variant="filled"
                        class="opacity-70"
                    />
                </div>
            </div>

            {{-- Row 4: Password & Confirmation --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    viewable
                />

                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    viewable
                />
            </div>

            <div class="flex items-center justify-end mt-2">
                <flux:button type="submit" variant="primary" class="w-full md:w-auto px-8">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
