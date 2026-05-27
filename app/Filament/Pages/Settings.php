<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Process;

class Settings extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';
    protected static \UnitEnum|string|null $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Pengaturan';
    protected static ?string $title = 'Pengaturan Sistem';
    protected static ?string $slug = 'settings';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'session_lifetime' => Setting::where('key', 'session_lifetime')->value('value') ?? 30,
            'multi_login' => Setting::where('key', 'multi_login')->value('value') !== '0', // Default true
        ]);
    }

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form
            ->schema([
                Section::make('Session & Keamanan')
                    ->description('Atur sesi pengguna dan keamanan login')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        TextInput::make('session_lifetime')
                            ->label('Auto Logout Session (Menit)')
                            ->numeric()
                            ->default(30)
                            ->required(),
                        Toggle::make('multi_login')
                            ->label('Izinkan Multi Login')
                            ->default(true)
                            ->helperText('Jika OFF, akun yang login di device lain otomatis logout.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function saveSettingsAction(): Action
    {
        return Action::make('saveSettings')
            ->label('Simpan Pengaturan')
            ->color('primary')
            ->action(function () {
                $data = $this->data;

                Setting::updateOrCreate(['key' => 'session_lifetime'], ['value' => $data['session_lifetime'] ?? 30]);
                Setting::updateOrCreate(['key' => 'multi_login'], ['value' => !empty($data['multi_login']) ? '1' : '0']);

                Notification::make()
                    ->title('Pengaturan berhasil disimpan')
                    ->success()
                    ->send();
            });
    }

    public function backupAction(): Action
    {
        return Action::make('backupAction')
            ->label('Backup Database')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function () {
                $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
                $path = storage_path('app/backups/' . $filename);
                
                if (!file_exists(storage_path('app/backups'))) {
                    mkdir(storage_path('app/backups'), 0755, true);
                }

                $dbUser = env('DB_USERNAME', 'root');
                $dbPass = env('DB_PASSWORD', '');
                $dbName = env('DB_DATABASE', 'queuely');
                $dbHost = env('DB_HOST', '127.0.0.1');

                $passString = $dbPass ? "-p\"$dbPass\"" : "";
                
                $mysqldumpPath = file_exists('C:\\xampp\\mysql\\bin\\mysqldump.exe') 
                    ? '"C:\\xampp\\mysql\\bin\\mysqldump.exe"' 
                    : 'mysqldump';
                
                $command = "{$mysqldumpPath} -h {$dbHost} -u {$dbUser} {$passString} {$dbName} > \"{$path}\"";
                
                $process = Process::run($command);
                
                if ($process->successful()) {
                    return response()->download($path)->deleteFileAfterSend(true);
                }
                
                Notification::make()
                    ->title('Backup gagal')
                    ->body($process->errorOutput())
                    ->danger()
                    ->send();
            });
    }

    public function restoreAction(): Action
    {
        return Action::make('restoreAction')
            ->label('Restore Database')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Restore Database')
            ->modalDescription('Database saat ini akan ditimpa seluruhnya dengan file backup. Aksi ini tidak dapat dibatalkan.')
            ->modalSubmitActionLabel('Ya, Restore Database')
            ->form([
                FileUpload::make('sql_file')
                    ->label('File SQL Backup')
                    ->acceptedFileTypes(['application/sql', 'text/plain', '.sql'])
                    ->required()
                    ->storeFiles(false)
            ])
            ->action(function (array $data) {
                // $data['sql_file'] is an UploadedFile
                $file = $data['sql_file'];
                $path = $file->getRealPath();

                $dbUser = env('DB_USERNAME', 'root');
                $dbPass = env('DB_PASSWORD', '');
                $dbName = env('DB_DATABASE', 'queuely');
                $dbHost = env('DB_HOST', '127.0.0.1');

                $passString = $dbPass ? "-p\"$dbPass\"" : "";
                
                $mysqlPath = file_exists('C:\\xampp\\mysql\\bin\\mysql.exe') 
                    ? '"C:\\xampp\\mysql\\bin\\mysql.exe"' 
                    : 'mysql';
                
                $command = "{$mysqlPath} -h {$dbHost} -u {$dbUser} {$passString} {$dbName} < \"{$path}\"";
                
                $process = Process::run($command);
                
                if ($process->successful()) {
                    Notification::make()
                        ->title('Restore berhasil')
                        ->body('Database telah berhasil dikembalikan.')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Restore gagal')
                        ->body($process->errorOutput())
                        ->danger()
                        ->send();
                }
            });
    }
}
