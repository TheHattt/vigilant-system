<x-layouts.auth>
    <div class="max-w-2xl mx-auto py-12" x-data="mikrotikOnboarding()">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 shadow-sm">

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Link Infrastructure</h2>
                <p class="text-zinc-500 dark:text-zinc-400">Connect your MikroTik core router to initialize the AAA Radius gateway.</p>
            </div>

            <template x-if="errorMessage">
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex gap-3">
                    <flux:icon.exclamation-circle class="text-red-600" />
                    <div>
                        <p class="text-sm font-bold text-red-900" x-text="errorTitle"></p>
                        <p class="text-xs text-red-700" x-text="errorMessage"></p>
                        <p class="mt-2 text-[10px] uppercase tracking-wider text-red-500 font-semibold" x-text="errorHint"></p>
                    </div>
                </div>
            </template>

            <div class="space-y-6" x-show="!isProvisioning">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Router Label" x-model="form.name" placeholder="e.g. Core Edge 1" />
                    <flux:select label="Model" x-model="form.model">
                        <option value="CCR2004">CCR2004</option>
                        <option value="RB5009">RB5009</option>
                        <option value="CHR">Cloud Hosted (CHR)</option>
                    </flux:select>
                </div>

                <flux:input label="IP Address / Hostname" x-model="form.host" placeholder="1.2.3.4" />

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <flux:input label="API Username" x-model="form.api_username" />
                    </div>
                    <flux:input label="Port" x-model="form.api_port" />
                </div>

                <flux:input type="password" label="API Password" x-model="form.api_password" viewable />

                <flux:button variant="primary" class="w-full" @click="submit" x-bind:disabled="loading">
                    <span x-show="!loading">Verify & Provision Radius</span>
                    <flux:spacer x-show="loading" />
                    <span x-show="loading">Testing Connection...</span>
                </flux:button>
            </div>

            <div class="py-12 text-center" x-show="isProvisioning">
                <flux:icon.loading class="mx-auto mb-4 animate-spin text-primary" />
                <h3 class="text-lg font-medium">Provisioning Infrastructure</h3>
                <p class="text-sm text-zinc-500">Injecting Radius configurations and initializing IP pools...</p>
            </div>
        </div>
    </div>

    <script>
        function mikrotikOnboarding() {
            return {
                loading: false,
                isProvisioning: false,
                errorMessage: '',
                errorTitle: '',
                errorHint: '',
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
                    this.errorMessage = '';

                    try {
                        let response = await fetch('{{ route('onboarding.save') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.form)
                        });

                        let data = await response.json();

                        if (!response.ok) {
                            this.errorTitle = data.title || 'Error';
                            this.errorMessage = data.message || 'Something went wrong';
                            this.errorHint = data.support_hint || '';
                            this.loading = false;
                            return;
                        }

                        this.isProvisioning = true;
                        setTimeout(() => window.location.href = data.redirect, 2000);

                    } catch (e) {
                        this.errorTitle = "Network Error";
                        this.errorMessage = "Could not reach the server.";
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-layouts.auth>
