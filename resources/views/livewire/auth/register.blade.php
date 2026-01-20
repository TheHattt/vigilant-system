<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Register your ISP company and setup your admin account.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

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
                name="company_name"
                :label="__('ISP Company Name')"
                :value="old('company_name')"
                type="text"
                required
                :placeholder="__('e.g. Fast Net')"
            />

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    name="prefix"
                    :label="__('Account Prefix')"
                    :value="old('prefix')"
                    type="text"
                    required
                    maxlength="3"
                    :placeholder="__('FNE')"
                />

                <flux:input
                    name="slug"
                    :label="__('System Slug')"
                    :value="old('slug')"
                    type="text"
                    readonly
                    variant="filled"
                    :placeholder="__('auto-generated')"
                />
            </div>

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

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

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full">
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
