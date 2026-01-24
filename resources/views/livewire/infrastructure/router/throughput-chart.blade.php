<div class="h-full w-full flex flex-col"
     x-data="throughputChart"
     x-init="initChart()">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xs font-semibold text-white uppercase tracking-wider">Live Throughput</h3>
            <div class="flex gap-3 mt-1">
                <span class="text-[10px] text-zinc-500 uppercase font-bold">Session:</span>
                <span class="text-[10px] text-emerald-500 ">↓ <span x-text="formatBytes(totalRx)"></span></span>
                <span class="text-[10px] text-indigo-500 ">↑ <span x-text="formatBytes(totalTx)"></span></span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <select
                wire:model.live="interface"
                x-on:change="resetChart()"
                class="bg-zinc-950 border border-zinc-800 text-zinc-300 text-[10px] rounded-md px-2 py-1 focus:ring-1 focus:ring-zinc-700 outline-none"
            >
                @foreach($interfaces as $iface)
                    <option value="{{ $iface }}">{{ $iface }}</option>
                @endforeach
            </select>

            <div class="flex gap-3 border-l border-zinc-800 pl-4">
                <div class="flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[10px] text-zinc-400 " x-text="formatSpeed(currentRx)"></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    <span class="text-[10px] text-zinc-400 " x-text="formatSpeed(currentTx)"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 relative min-h-[220px] w-full">
        <canvas id="trafficChart" x-ref="canvas"></canvas>
    </div>

    @script
    <script>
        Alpine.data('throughputChart', () => ({
            chart: null,
            currentRx: 0,
            currentTx: 0,
            totalRx: 0, // Accumulated Bytes
            totalTx: 0, // Accumulated Bytes
            lastPollTime: Date.now(),

            initChart() {
                const ctx = this.$refs.canvas.getContext('2d');

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: Array(30).fill(''),
                        datasets: [
                            {
                                label: 'RX',
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                data: Array(30).fill(0),
                                fill: true,
                                tension: 0.4,
                                borderWidth: 1.5,
                                pointRadius: 0
                            },
                            {
                                label: 'TX',
                                borderColor: '#6366f1',
                                data: Array(30).fill(0),
                                fill: false,
                                tension: 0.4,
                                borderWidth: 1.5,
                                pointRadius: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 0 },
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#71717a', font: { size: 9 }, callback: (val) => this.formatSpeed(val) }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });

                setInterval(async () => {
                    const data = await $wire.getTraffic();
                    const now = Date.now();
                    const secondsPassed = (now - this.lastPollTime) / 1000;

                    this.currentRx = data.rx;
                    this.currentTx = data.tx;

                    // Logic: bits per second * seconds / 8 = Bytes
                    this.totalRx += (data.rx * secondsPassed) / 8;
                    this.totalTx += (data.tx * secondsPassed) / 8;

                    this.lastPollTime = now;
                    this.updateChart(data.rx, data.tx);
                }, 2000);
            },

            resetChart() {
                this.chart.data.datasets.forEach(d => d.data = Array(30).fill(0));
                this.chart.update();
                this.totalRx = 0;
                this.totalTx = 0;
            },

            updateChart(rx, tx) {
                this.chart.data.datasets[0].data.shift();
                this.chart.data.datasets[0].data.push(rx);
                this.chart.data.datasets[1].data.shift();
                this.chart.data.datasets[1].data.push(tx);
                this.chart.update('none');
            },

            formatSpeed(bits) {
                if (bits >= 1000000) return (bits / 1000000).toFixed(1) + ' Mb';
                if (bits >= 1000) return (bits / 1000).toFixed(1) + ' Kb';
                return bits + ' b';
            },

            formatBytes(bytes) {
                if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                return (bytes / 1024).toFixed(1) + ' KB';
            }
        }))
    </script>
    @endscript
</div>
