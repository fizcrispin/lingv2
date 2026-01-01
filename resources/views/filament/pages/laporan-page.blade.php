<x-filament-panels::page>
    <x-filament::section collapsible>
        <x-slot name="heading">
            Filter Data
        </x-slot>
        {{ $this->form }}
    </x-filament::section>

    <div class="space-y-6">

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-presentation-chart-line" class="w-5 h-5 text-gray-500" />
                    <span>Ringkasan Eksekutif</span>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5">
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Kategori</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Indikator</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">Pendaftaran</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">Total Pendaftar</td>
                            <td class="px-6 py-4 font-bold text-right text-gray-950 dark:text-white font-mono tabular-nums text-lg">
                                {{ number_format($pendaftaran_count, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">Ekspedisi</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">Rata-rata Waktu Tunggu</td>
                            <td class="px-6 py-4 font-bold text-right text-warning-600 font-mono tabular-nums text-lg">
                                {{ $avg_verifikasi_time }}
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">Keuangan</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">Total Pendapatan (Lunas)</td>
                            <td class="px-6 py-4 font-bold text-right text-success-600 font-mono tabular-nums text-lg">
                                <span class="text-xs text-gray-400 font-sans mr-1">Rp</span>{{ number_format($total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">Keuangan</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">Invoice Belum Lunas</td>
                            <td class="px-6 py-4 font-bold text-right text-danger-600 font-mono tabular-nums text-lg">
                                {{ number_format($total_unpaid, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">Keuangan</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">Invoice Lunas (Jumlah)</td>
                            <td class="px-6 py-4 font-bold text-right text-primary-600 font-mono tabular-nums text-lg">
                                {{ number_format($total_paid, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <x-filament::section>
                <x-slot name="heading">Status Ekspedisi</x-slot>
                <x-slot name="headerEnd">
                     <div class="text-xs text-gray-400 font-medium">Real-time</div>
                </x-slot>

                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2.5 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Status</th>
                                <th class="px-4 py-2.5 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                            @foreach($ekspedisi_stats as $stat => $count)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">
                                    {{-- Opsional: Badge warna warni berdasarkan status --}}
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                        {{ $stat }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold text-right text-gray-950 dark:text-white font-mono tabular-nums">
                                    {{ $count }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>    

            <x-filament::section>
                <x-slot name="heading">Metode Pembayaran</x-slot>
                
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2.5 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Metode</th>
                                <th class="px-4 py-2.5 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">Total Inv.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                            @forelse($payment_method_stats as $method => $count)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $method ?: 'Lainnya' }}</td>
                                <td class="px-4 py-3 font-bold text-right text-gray-950 dark:text-white font-mono tabular-nums">{{ $count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 italic">Data tidak tersedia</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">Distribusi Jenis Sampel</x-slot>
                
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2.5 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Jenis Sampel</th>
                                <th class="px-4 py-2.5 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                            @foreach($jenis_sampel_stats as $jenis => $count)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200 truncate max-w-xs" title="{{ $jenis }}">{{ $jenis }}</td>
                                <td class="px-4 py-3 font-bold text-right text-gray-950 dark:text-white font-mono tabular-nums">{{ $count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Top 10 Parameter</x-slot>
                <x-slot name="headerEnd">
                     <x-filament::badge color="info">Terbanyak</x-filament::badge>
                </x-slot>

                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2.5 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Parameter</th>
                                <th class="px-4 py-2.5 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                            @foreach($parameter_stats as $param => $count)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200 font-medium group-hover:text-primary-600 transition-colors">
                                    {{ $param }}
                                </td>
                                <td class="px-4 py-3 font-bold text-right text-gray-950 dark:text-white font-mono tabular-nums">
                                    {{ $count }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>