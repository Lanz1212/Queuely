<x-filament-panels::page>
    <form wire:submit="saveSettings" class="space-y-4">
        {{ $this->form }}

        <div class="mt-4 text-left">
            {{ $this->saveSettingsAction }}
        </div>
    </form>

    <div class="mt-8">
        <x-filament::section icon="heroicon-o-server" icon-color="primary">
            <x-slot name="heading">
                Backup & Restore Database
            </x-slot>
            <x-slot name="description">
                Amankan data sistem dengan melakukan backup atau kembalikan data dari file SQL.
            </x-slot>

            <div class="divide-y divide-gray-200 dark:divide-white/10">
                <!-- Backup -->
                <div class="flex items-center justify-between py-6">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">Backup Database</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Unduh seluruh data database saat ini ke dalam file .sql.</p>
                    </div>
                    <div>
                        {{ $this->backupAction }}
                    </div>
                </div>
                
                <!-- Restore -->
                <div class="flex items-center justify-between py-6">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">Restore Database</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kembalikan data dari file backup .sql. Peringatan: Data saat ini akan ditimpa!</p>
                    </div>
                    <div>
                        {{ $this->restoreAction }}
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
