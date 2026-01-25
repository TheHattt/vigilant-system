<div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 shadow-2xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-sm font-bold text-zinc-200 uppercase tracking-widest">Live Throughput</h3>
            <p class="text-[10px] text-zinc-500 font-mono">Interface: {{ $interface }} (Mbps)</p>
        </div>
        <div class="flex gap-4">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-[10px] text-zinc-400 font-bold uppercase">RX: {{ $rx }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                <span class="text-[10px] text-zinc-400 font-bold uppercase">TX: {{ $tx }}</span>
            </div>
        </div>
    </div>

    {{-- Chart Container --}}
    <div id="traffic-chart" style="min-height: 200px;" x-data="trafficChart" x-init="initChart()" x-on:stats-updated.window="updateData($event.detail)">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('trafficChart', () => ({
                chart: null,
                rxData: new Array(20).fill(0),
                txData: new Array(20).fill(0),

                initChart() {
                    const options = {
                        series: [
                            { name: 'Download (RX)', data: this.rxData },
                            { name: 'Upload (TX)', data: this.txData }
                        ],
                        chart: {
                            type: 'area',
                            height: 200,
                            animations: { enabled: true, easing: 'linear', dynamicAnimation: { speed: 1000 } },
                            toolbar: { show: false },
                            sparkline: { enabled: true }
                        },
                        colors: ['#10b981', '#6366f1'],
                        stroke: { curve: 'smooth', width: 2 },
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0 } },
                        tooltip: { theme: 'dark' }
                    };

                    this.chart = new ApexCharts(document.querySelector("#traffic-chart"), options);
                    this.chart.render();
                },

                updateData(stats) {
                    this.rxData.push(stats.rx);
                    this.txData.push(stats.tx);

                    if (this.rxData.length > 20) this.rxData.shift();
                    if (this.txData.length > 20) this.txData.shift();

                    this.chart.updateSeries([
                        { data: this.rxData },
                        { data: this.txData }
                    ]);
                }
            }))
        });
    </script>
</div>
