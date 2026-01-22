<x-layouts.auth>
    <div class="max-w-2xl mx-auto py-12" x-data="mikrotikOnboarding()">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 shadow-sm">

            <div class="mb-8" x-show="!isProvisioning">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Link Infrastructure</h2>
                <p class="text-zinc-500 dark:text-zinc-400">Enter your MikroTik credentials. We will automatically configure the Radius gateway.</p>
            </div>

            <template x-if="error.active">
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30 rounded-xl flex gap-3">
                    <flux:icon.exclamation-circle class="text-red-600 shrink-0" />
                    <div>
                        <p class="text-sm font-bold text-red-900 dark:text-red-400" x-text="error.title"></p>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-1" x-text="error.message"></p>

                        <div class="mt-3 flex items-center gap-4">
                             <p class="text-[10px] uppercase tracking-wider text-red-500 font-bold" x-text="error.code"></p>
                             <template x-if="error.retryable">
                                <button @click="submit" class="text-xs font-bold text-red-700 underline decoration-red-400">Retry Connection</button>
                             </template>
                        </div>

                        <p class="mt-2 text-[10px] text-zinc-400" x-text="'Hint: ' + error.hint"></p>
                    </div>
                </div>
            </template>

            <div class="space-y-6" x-show="!isProvisioning">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input label="Friendly Name" x-model="form.name" placeholder="e.g. Main HQ Router" />
                    <flux:select label="Hardware Model" x-model="form.model">
                        <option value="CCR2004">Cloud Core (CCR)</option>
                        <option value="RB5009">RouterBoard (RB)</option>
                        <option value="CHR">Cloud Hosted (CHR)</option>
                        <option value="x86">PC / x86</option>
                    </flux:select>
                </div>

                <flux:input label="Router IP or DDNS" x-model="form.host" placeholder="1.2.3.4" />

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <flux:input label="API Username" x-model="form.api_username" />
                    </div>
                    <flux:input label="API Port" x-model="form.api_port" />
                </div>

                <flux:input type="password" label="API Password" x-model="form.api_password" viewable />

                <flux:button variant="primary" class="w-full h-12" @click="submit" :loading="loading">
                    {{ __('Verify & Auto-Provision') }}
                </flux:button>
            </div>

            <div class="py-12 text-center" x-show="isProvisioning" x-cloak>
                <div class="relative inline-flex mb-6">
                    <flux:icon.loading class="w-12 h-12 animate-spin text-primary" />
                </div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Initializing Infrastructure</h3>
                <p class="text-sm text-zinc-500 max-w-xs mx-auto mt-2">
                    We are injecting Radius configurations, setting up CoA ports, and initializing your IP pools. Please do not refresh.
                </p>
            </div>
        </div>
    </div>

    <script>
        function mikrotikOnboarding() {
            return {
                loading: false,
                isProvisioning: false,
                error: {
                    active: false,
                    title: '',
                    message: '',
                    code: '',
                    hint: '',
                    retryable: false
                },
                form: {
                    name: '',
                    host: '',
                    api_username: 'admin',
                    api_password: '',
                    api_port: 8728,
                    model: 'CCR2004',
                    os_version: 'v7'
                },
                async submit() {
                    this.loading = true;
                    this.error.active = false;

                    try {
                        const response = await fetch('{{ route('onboarding.save') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            this.error = {
                                active: true,
                                title: data.title || 'Validation Error',
                                message: data.message || 'Check your details.',
                                code: data.code || 'ERR_VALIDATION',
                                hint: data.support_hint || 'Verify credentials.',
                                retryable: data.retryable || false
                            };
                            this.loading = false;
                            return;
                        }

                        // Success flow
                        this.loading = false;
                        this.isProvisioning = true;

                        // Small delay to let the user see the success state
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2500);

                    } catch (e) {
                        this.error = {
                            active: true,
                            title: 'Network Timeout',
                            message: 'The server took too long to respond.',
                            code: 'SERVER_UNREACHABLE',
                            hint: 'Check your internet connection.',
                            retryable: true
                        };
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-layouts.auth>
