<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $sessionLifetime = \App\Models\Setting::where('key', 'session_lifetime')->value('value');
                if ($sessionLifetime) {
                    config(['session.lifetime' => (int) $sessionLifetime]);
                }
            }
        } catch (\Exception $e) {
            // Ignore if DB is not ready
        }

        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Auth\Events\Login $event) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $multiLogin = \App\Models\Setting::where('key', 'multi_login')->value('value');
                    
                    // If multi_login is '0' (OFF), delete other sessions
                    if ($multiLogin === '0' && config('session.driver') === 'database') {
                        \Illuminate\Support\Facades\DB::table('sessions')
                            ->where('user_id', $event->user->getAuthIdentifier())
                            ->where('id', '!=', \Illuminate\Support\Facades\Session::getId())
                            ->delete();
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
        });
    }
}
