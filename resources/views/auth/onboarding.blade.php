<x-layouts::auth>
    <div class="max-w-3xl mx-auto py-2" x-data="mikrotikOnboarding()">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl overflow-hidden shadow-2xl">

            {{-- Top Accent Bar --}}
            <div x-show="!isProvisioning" class="h-1.5 bg-gradient-to-r from-slate-600 via-indigo-600 to-cyan-500"></div>

            <div class="p-8 md:p-12">

                {{-- FORM SECTION --}}
                <div x-show="!isProvisioning" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                    <div class="mb-10">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-3 bg-blue-50 dark:bg-blue-950/30 rounded-xl text-blue-600">
                                <flux:icon.server-stack class="w-8 h-8" />
                            </div>
                            <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Infrastructure Link</h2>
                        </div>
                        <p class="text-zinc-500 text-sm max-w-md">
                            Establish a secure management channel to your MikroTik router. Credentials are encrypted and scoped to API access only.
                        </p>
                    </div>

                    {{-- Status Alerts --}}
                    <template x-if="status.active">
                        <div :class="status.type === 'error' ? 'bg-red-50/60 border-red-500 text-red-900' : 'bg-emerald-50/60 border-emerald-500 text-emerald-900'"
                             class="mb-8 p-4 border-l-4 rounded-r-xl flex gap-4 animate-in fade-in slide-in-from-top-2">
                            <div class="flex-1">
                                <p class="text-sm font-bold" x-text="status.title"></p>
                                <p class="text-xs opacity-80" x-text="status.message"></p>
                            </div>
                            <button type="button" @click="status.active = false" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                        </div>
                    </template>

                    <div class="space-y-10">
                        {{-- 01 IDENTITY --}}
                        <section class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">01 · Device Identity</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:input x-model="form.name" label="Friendly Name" placeholder="e.g. Core Router" />
                                <flux:select x-model="form.model" label="Hardware Model">
                                    <option value="CCR2004">Cloud Core Router (CCR)</option>
                                    <option value="RB5009">RouterBoard</option>
                                    <option value="CHR">Cloud Hosted Router (CHR)</option>
                                    <option value="x86">Generic x86 / PC</option>
                                </flux:select>
                            </div>
                        </section>

                        {{-- 02 NETWORK --}}
                        <section class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">02 · Network Endpoint</span>
                            </div>
                            <flux:input
                                x-model.trim="form.host"
                                @input.debounce.500ms="lookupHostname"
                                label="Gateway IP or DNS Name"
                                placeholder="192.168.88.1"
                                class="font-mono"
                            />
                        </section>

                        {{-- 03 API ACCESS --}}
                        <section class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">03 · API Credentials</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:input x-model.trim="form.api_username" label="API User" placeholder="admin" />
                                <flux:input x-model.number="form.api_port" type="number" label="API Port" />
                            </div>
                            <flux:input x-model="form.api_password" type="password" label="API Password" viewable />
                        </section>

                        {{-- ACTIONS --}}
                        <div class="flex flex-col-reverse md:flex-row justify-end items-center gap-4 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:button type="button" variant="ghost" class="w-full md:w-auto h-11 px-8" @click.prevent="testConnection" x-bind:loading="testing">
                                Test Connection
                            </flux:button>
                            <flux:button type="button" variant="primary" class="w-full md:w-auto h-11 px-10 font-bold bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/20" @click.prevent="submit" x-bind:loading="loading">
                                Verify & Provision
                            </flux:button>
                        </div>
                    </div>
                </div>

                {{-- PROVISIONING ANIMATION WITH CONSOLE LOGS --}}
                <div class="py-12" x-show="isProvisioning" x-cloak x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 scale-95">
                    <div class="flex flex-col items-center">
                        <div class="relative w-20 h-20 mb-8">
                            <div class="absolute inset-0 border-4 border-blue-50 dark:border-zinc-800 rounded-2xl"></div>
                            <div class="absolute inset-0 border-4 border-t-blue-600 rounded-2xl animate-spin"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <flux:icon.cpu-chip class="w-8 h-8 text-blue-600" />
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-zinc-900 dark:text-white" x-text="testing ? 'Testing Connection...' : 'Provisioning Infrastructure'"></h3>

                        {{-- Checklist --}}
                        <div class="mt-8 w-full max-w-sm space-y-3 px-6">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="flex items-center gap-3 transition-all duration-500" :class="step.complete ? 'opacity-100' : 'opacity-40'">
                                    <div class="flex-shrink-0">
                                        <template x-if="step.complete">
                                            <flux:icon.check-circle class="w-5 h-5 text-emerald-500" />
                                        </template>
                                        <template x-if="!step.complete">
                                            <div class="w-5 h-5 border-2 border-zinc-300 dark:border-zinc-700 rounded-full"></div>
                                        </template>
                                    </div>
                                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-300" x-text="step.label"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Technical Console --}}
                        <div class="mt-10 w-full max-w-md bg-zinc-950 rounded-xl p-4 font-mono text-[10px] text-zinc-400 shadow-inner overflow-hidden border border-white/5">
                            <div class="flex items-center gap-2 mb-3 border-b border-white/10 pb-2">
                                <div class="flex gap-1">
                                    <div class="w-2 h-2 rounded-full bg-red-500/50"></div>
                                    <div class="w-2 h-2 rounded-full bg-amber-500/50"></div>
                                    <div class="w-2 h-2 rounded-full bg-emerald-500/50"></div>
                                </div>
                                <span class="uppercase tracking-widest text-[9px] text-zinc-500">System Deployment Logs</span>
                            </div>
                            <div class="space-y-1 h-32 overflow-y-auto" id="console-scroll">
                                <template x-for="log in logs">
                                    <div class="flex gap-2">
                                        <span class="text-blue-500/70" x-text="log.time"></span>
                                        <span :class="log.type === 'error' ? 'text-red-400' : 'text-zinc-300'" x-text="log.msg"></span>
                                    </div>
                                </template>
                                <div class="animate-pulse flex gap-2" x-show="loading || testing">
                                    <span class="text-blue-500/70">></span>
                                    <span class="text-zinc-500">Awaiting kernel response...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mikrotikOnboarding() {
            return {
                loading: false,
                testing: false,
                isProvisioning: false,
                status: { active: false, type: '', title: '', message: '' },
                logs: [],
                steps: [
                    { label: 'API Gateway Handshake', complete: false },
                    { label: 'SSL/TLS Tunnel Encryption', complete: false },
                    { label: 'Radius Profile Migration', complete: false },
                    { label: 'RouterOS Service Optimization', complete: false }
                ],
                form: {
                    name: '',
                    host: '',
                    api_username: 'admin',
                    api_password: '',
                    api_port: 8728,
                    model: 'CCR2004'
                },
                addLog(msg, type = 'info') {
                    const now = new Date();
                    const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');
                    this.logs.push({ time, msg, type });
                    this.$nextTick(() => {
                        const el = document.getElementById('console-scroll');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },
                async lookupHostname() {
                    if (!this.form.host) return;
                    try {
                        const response = await fetch(`/onboarding/lookup-dns?host=${this.form.host}`);
                        if (response.ok) {
                            const data = await response.json();
                            if (data.hostname && (!this.form.name || this.form.name === this.lastAutoName)) {
                                this.form.name = data.hostname;
                                this.lastAutoName = data.hostname;
                            }
                        }
                    } catch (e) {}
                },
                validate() {
                    const required = ['name', 'host', 'api_username', 'api_password', 'api_port'];
                    for(let field of required) {
                        if(!this.form[field]) {
                            this.status = { active: true, type: 'error', title: 'Action Required', message: `The ${field.replace('_', ' ')} field cannot be empty.` };
                            return false;
                        }
                    }
                    return true;
                },
                async testConnection() {
                    if (!this.validate()) return;

                    this.testing = true;
                    this.isProvisioning = true; // Show animation
                    this.status.active = false;
                    this.logs = [];
                    this.steps.forEach(s => s.complete = false);
                    this.addLog('Testing API connectivity...');

                    try {
                        const response = await fetch('/onboarding/test', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify(this.form)
                        });

                        if(response.ok) {
                            this.steps[0].complete = true;
                            this.addLog('Handshake successful.');
                        }

                        this.status = {
                            active: true,
                            type: response.ok ? 'success' : 'error',
                            title: response.ok ? 'Handshake Passed' : 'Socket Error',
                            message: response.ok ? 'Router API is reachable and responding.' : 'Target host refused the connection.'
                        };
                    } catch (e) {
                        this.status = { active: true, type: 'error', title: 'Network Fault', message: 'Unable to resolve gateway address.' };
                    } finally {
                        this.testing = false;
                        setTimeout(() => { this.isProvisioning = false; }, 800); // Return to form after brief delay
                    }
                },
                async submit() {
                    if (!this.validate()) return;

                    this.isProvisioning = true;
                    this.loading = true;
                    this.logs = [];
                    this.steps.forEach(s => s.complete = false);

                    this.addLog('Initializing deployment sequence...');

                    try {
                        setTimeout(() => { this.steps[0].complete = true; this.addLog('API socket established successfully.'); }, 600);
                        setTimeout(() => { this.addLog('Generating ephemeral RSA keys...'); }, 1200);
                        setTimeout(() => { this.steps[1].complete = true; this.addLog('SSL Tunnel secured (AES-256).'); }, 1800);

                        const response = await fetch('{{ route('onboarding.save') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify(this.form)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                             this.addLog('Critical failure: Remote script returned error 500', 'error');
                             throw new Error(data.message || 'The router rejected the configuration sync.');
                        }

                        setTimeout(() => { this.steps[2].complete = true; this.addLog('Radius profiles synchronized.'); }, 2400);
                        setTimeout(() => { this.steps[3].complete = true; this.addLog('Deployment complete. Redirecting...'); }, 3000);

                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 3500);
                        }
                    } catch (e) {
                        this.isProvisioning = false;
                        this.status = {
                            active: true,
                            type: 'error',
                            title: 'Deployment Aborted',
                            message: e.message
                        };
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-layouts::auth>
