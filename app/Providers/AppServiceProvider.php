<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\Notifikasi;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Locale Indonesia untuk Carbon
        Carbon::setLocale('id');

        // Paginator pakai template custom (bukan Bootstrap — app tidak load Bootstrap CSS)
        Paginator::defaultView('pagination::default');

        // Paksa HTTPS di Production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Share notifikasi unread ke semua view (CACHE 5 MENIT)
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $userId = auth()->user()->getKey();

                $notifUnread = cache()->remember("notif.unread.{$userId}", 300, function () use ($userId) {
                    return Notifikasi::where('user_id', $userId)
                        ->where('is_read', false)
                        ->orderByDesc('created_at')
                        ->take(5)
                        ->get();
                });

                $notifCount = cache()->remember("notif.count.{$userId}", 300, function () use ($userId) {
                    return Notifikasi::where('user_id', $userId)
                        ->where('is_read', false)
                        ->count();
                });

                $view->with(compact('notifUnread', 'notifCount'));
            }
        });
    }
}
