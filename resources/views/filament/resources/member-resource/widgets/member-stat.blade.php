<x-filament::widget>
    <div>
        <div class="grid gap-4 filament-stats md:grid-cols-6 xl:grid-cols-4">
            <div class="relative p-4 bg-indigo-500 shadow rounded-2xl filament-stats-card filament-stats-overview-widget-card">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-white dark:text-gray-200">
                        Total Transaksi
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-2xl text-white">
                            {{ $transactions->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative p-4 shadow bg-violet-500 rounded-2xl filament-stats-card filament-stats-overview-widget-card">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-white dark:text-gray-200">
                        Total Produk Terjual
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-2xl text-white">
                            {{ $transactions->sum('qty') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative p-4 bg-purple-500 shadow rounded-2xl filament-stats-card filament-stats-overview-widget-card">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-white dark:text-gray-200">
                        Total Profit
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-2xl text-white">
                            Rp. {{ number_format($transactions->sum('profit_price'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative p-4 shadow bg-fuchsia-500 rounded-2xl filament-stats-card filament-stats-overview-widget-card">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-white dark:text-gray-200">
                        Total Belanja
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-2xl text-white">
                            Rp. {{ number_format($transactions->sum('subtotal_after_discount'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-filament::widget>
