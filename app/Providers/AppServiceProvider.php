<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Notifikasi;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Locale Indonesia untuk Carbon
        Carbon::setLocale('id');

        // Paginator pakai Bootstrap (atau Tailwind bila diubah)
        Paginator::defaultView('vendor.pagination.bootstrap-5');

        // Share notifikasi unread ke semua view
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $userId = auth()->user()->getKey();

                $notifUnread = Notifikasi::where('user_id', $userId)
                    ->where('is_read', false)
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get();

                $notifCount = Notifikasi::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $view->with(compact('notifUnread', 'notifCount'));
            }
        });
    }
}
