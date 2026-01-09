<x-filament-panels::page>
    <x-filament::section collapsible>
        <x-slot name="heading">
            Filter Data
        </x-slot>
        {{ $this->form }}
    </x-filament::section>

    <div class="space-y-6">

        <!-- Stat Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Pendaftar -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-primary-50 dark:bg-primary-900/20">
                        <x-filament::icon icon="heroicon-o-users" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendaftar</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">
                            {{ number_format($pendaftaran_count, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-success-50 dark:bg-success-900/20">
                        <x-filament::icon icon="heroicon-o-currency-dollar" class="w-6 h-6 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Estimasi Pendapatan</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">
                            <span class="text-sm font-normal text-gray-400">Rp</span>{{ number_format($total_revenue, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Invoice Status Grid (Combined for compactness) -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                 <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <x-filament::icon icon="heroicon-o-document-check" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Inv. Lunas / Pending</p>
                         <div class="flex items-baseline gap-2 mt-1">
                            <p class="text-3xl font-bold text-gray-950 dark:text-white">{{ $total_paid }}</p>
                            <span class="text-sm text-gray-400">/</span>
                            <p class="text-xl font-semibold text-danger-500">{{ $total_unpaid }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operational Metric -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-orange-50 dark:bg-orange-900/20">
                        <x-filament::icon icon="heroicon-o-clock" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg. Waktu Tunggu</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">
                            {{ $avg_verifikasi_time }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

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

    <div class="mt-6">
        <x-filament::section collapsible>
            <x-slot name="heading">Statistik Input Laboratorium</x-slot>
            <x-slot name="description">Monitoring data input hasil berdasarkan tanggal</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                 <!-- Summary Cards -->
                 <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-100 dark:border-primary-800">
                     <p class="text-sm font-medium text-primary-600 dark:text-primary-400">Total Sampel Selesai Input</p>
                     <p class="text-2xl font-bold text-primary-700 dark:text-primary-300 mt-1">
                         {{ number_format($lab_input_stats['total_sampel_input'] ?? 0, 0, ',', '.') }}
                     </p>
                 </div>
                 <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-xl border border-success-100 dark:border-success-800">
                     <p class="text-sm font-medium text-success-600 dark:text-success-400">Total Parameter Selesai Input</p>
                     <p class="text-2xl font-bold text-success-700 dark:text-success-300 mt-1">
                         {{ number_format($lab_input_stats['total_parameter_input'] ?? 0, 0, ',', '.') }}
                     </p>
                 </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Jenis Sampel Progress -->
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <div class="bg-gray-50 dark:bg-white/5 px-4 py-2 border-b border-gray-200 dark:border-white/10">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Progress Jenis Sampel</h3>
                    </div>
                    <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                        <thead class="bg-white dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 font-medium text-gray-500">Jenis Sampel</th>
                                <th class="px-4 py-2 font-medium text-right text-success-600">Sudah</th>
                                <th class="px-4 py-2 font-medium text-right text-danger-600">Belum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                             @php
                                $allJenis = array_unique(array_merge(
                                    array_keys($lab_input_stats['jenis_sampel_sudah'] ?? []),
                                    array_keys($lab_input_stats['jenis_sampel_belum'] ?? [])
                                ));
                             @endphp
                             @foreach($allJenis as $j)
                             <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                 <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $j }}</td>
                                 <td class="px-4 py-2 text-right font-bold text-success-600">
                                     {{ $lab_input_stats['jenis_sampel_sudah'][$j] ?? 0 }}
                                 </td>
                                 <td class="px-4 py-2 text-right font-bold text-danger-600">
                                     {{ $lab_input_stats['jenis_sampel_belum'][$j] ?? 0 }}
                                 </td>
                             </tr>
                             @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Parameter Progress -->
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <div class="bg-gray-50 dark:bg-white/5 px-4 py-2 border-b border-gray-200 dark:border-white/10">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Progress Parameter (Top 20)</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto custom-scrollbar">
                        <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                            <thead class="bg-white dark:bg-gray-900 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 font-medium text-gray-500 bg-white dark:bg-gray-900">Parameter</th>
                                    <th class="px-4 py-2 font-medium text-right text-success-600 bg-white dark:bg-gray-900">Sudah</th>
                                    <th class="px-4 py-2 font-medium text-right text-danger-600 bg-white dark:bg-gray-900">Belum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                                @php
                                $allParams = array_unique(array_merge(
                                    array_keys($lab_input_stats['parameter_sudah'] ?? []),
                                    array_keys($lab_input_stats['parameter_belum'] ?? [])
                                ));
                                sort($allParams); // Sort by name alphabetically
                                @endphp
                                @foreach($allParams as $p)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $p }}</td>
                                    <td class="px-4 py-2 text-right font-bold text-success-600">
                                        {{ $lab_input_stats['parameter_sudah'][$p] ?? 0 }}
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold text-danger-600">
                                        {{ $lab_input_stats['parameter_belum'][$p] ?? 0 }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                     </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Old Sections --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <x-filament::section>
             <x-slot name="heading">Distribusi Jenis Sampel (Total)</x-slot>
             {{-- Keep original table --}}
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
             <x-slot name="heading">Statistik Parameter (Total)</x-slot>
             {{-- Keep original table --}}
             <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                <div class="max-h-96 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5 relative">
                        <thead class="bg-gray-50 dark:bg-white/5 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-2.5 text-xs font-medium text-gray-500 uppercase dark:text-gray-400 bg-gray-50 dark:bg-gray-900/90 backdrop-blur">Parameter</th>
                                <th class="px-4 py-2.5 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400 bg-gray-50 dark:bg-gray-900/90 backdrop-blur">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                            @forelse($parameter_stats as $param => $count)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200 font-medium group-hover:text-primary-600 transition-colors">
                                    {{ $param }}
                                </td>
                                <td class="px-4 py-3 font-bold text-right text-gray-950 dark:text-white font-mono tabular-nums">
                                    {{ $count }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 italic">Tidak ada data parameter</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>