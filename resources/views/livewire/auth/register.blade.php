<x-layouts::auth>
    <div class="flex flex-col gap-8 mx-auto w-full max-w-3xl"
         x-data="{
            company: '{{ old('company_name', '') }}',
            name: '{{ old('name', '') }}',
            email: '{{ old('email', '') }}',
            password: '',
            password_confirmation: '',
            loading: false,
            errors: {},

            get slug() {
                return this.company.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-')
            },

            get prefix() {
                if (!this.company) return '';
                let words = this.company.trim().split(/\s+/);
                if (words.length >= 2) {
                    return (words[0][0] + words[1][0] + words[words.length-1].slice(-1)).toLowerCase();
                }
                return this.company.slice(0, 3).toLowerCase();
            },

            async submit() {
                this.loading = true;
                this.errors = {};

                try {
                    const response = await fetch('{{ route('register.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.name,
                            email: this.email,
                            company_name: this.company,
                            account_prefix: this.prefix,
                            slug: this.slug,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // High-speed redirect to dashboard
                        window.location.href = data.redirect || '/dashboard';
                    } else {
                        // Capture Laravel Validation errors
                        this.errors = data.errors || {};
                    }
                } catch (e) {
                    console.error('Provisioning Error:', e);
                } finally {
                    this.loading = false;
                }
            }
         }">

        <x-auth-header
            :title="__('Onboard Your Organization')"
            :description="__('Initialize your dedicated ISP management instance and administrative credentials.')"
        />

        <form @submit.prevent="submit" class="flex flex-col gap-8">
            @csrf

            {{-- Section 1: Identity Ownership --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-zinc-400">
                    <flux:icon.user-circle variant="micro" />
                    <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Principal Administrator') }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <flux:input x-model="name" name="name" :label="__('Full Legal Name')" placeholder="John Doe" required />
                        <p x-show="errors.name" x-text="errors.name[0]" class="text-[10px] text-rose-600 font-bold"></p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <flux:input x-model="email" name="email" :label="__('Corporate Email Address')" type="email" placeholder="admin@company.com" required />
                        <p x-show="errors.email" x-text="errors.email[0]" class="text-[10px] text-rose-600 font-bold"></p>
                    </div>
                </div>
            </div>

            {{-- Section 2: Organization Provisioning --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-zinc-400">
                    <flux:icon.building-office variant="micro" />
                    <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Organization Details') }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="flex flex-col gap-1">
                        <flux:input x-model="company" name="company_name" :label="__('Registered Company Name')" placeholder="e.g. Global Net Solutions" required />
                        <p x-show="errors.company_name" x-text="errors.company_name[0]" class="text-[10px] text-rose-600 font-bold"></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-blue-50/50 dark:bg-blue-950/10 border border-blue-100 dark:border-blue-900/30 rounded-2xl">
                        <flux:input name="account_prefix" x-bind:value="prefix" :label="__('Tenant Identifier (ID)')" readonly variant="filled" class="font-mono font-bold text-blue-700 dark:text-blue-400" />
                        <flux:input name="slug" x-bind:value="slug" :label="__('Deployment URL Slug')" readonly variant="filled" class="opacity-80 font-mono text-zinc-600 dark:text-zinc-400" />
                    </div>
                </div>
            </div>

            {{-- Section 3: Security Policy (Layout Fix Applied) --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-zinc-400">
                    <flux:icon.shield-check variant="micro" />
                    <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Security Protocol') }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 p-5 bg-zinc-50 dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                    {{-- Reserved space prevents inputs jumping when errors appear --}}
                    <div class="min-h-[100px] flex flex-col gap-1">
                        <flux:input x-model="password" name="password" :label="__('Master Password')" type="password" required viewable />
                        <p x-show="errors.password" x-text="errors.password[0]" class="text-[10px] text-rose-600 font-bold leading-tight"></p>
                    </div>

                    <div class="min-h-[100px] flex flex-col gap-1">
                        <flux:input x-model="password_confirmation" name="password_confirmation" :label="__('Verify Credentials')" type="password" required viewable />
                        <p x-show="errors.password_confirmation" x-text="errors.password_confirmation[0]" class="text-[10px] text-rose-600 font-bold leading-tight"></p>
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-[10px] text-zinc-500 leading-relaxed">
                            {{ __('By initializing, you agree to the Enterprise Service Level Agreement (SLA). Use 10+ characters with mixed symbols for master credentials.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Action Controller --}}
            <div class="flex flex-col gap-4">
                <flux:button
                    x-bind:disabled="loading"
                    @click="submit"
                    variant="primary"
                    class="w-full h-14 shadow-xl shadow-blue-500/20 text-lg font-bold"
                >
                    <span x-show="!loading">{{ __('Provision Environment') }}</span>
                    <span x-show="loading" class="flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('Allocating Resources...') }}
                    </span>
                </flux:button>

                <div class="text-center">
                    <flux:link :href="route('login')" wire:navigate class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors">
                        {{ __('Return to Secure Gateway') }}
                    </flux:link>
                </div>
            </div>
        </form>
    </div>
</x-layouts::auth>
