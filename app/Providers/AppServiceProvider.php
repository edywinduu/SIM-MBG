<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\View;
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
        // Berlaku untuk SEMUA file blade (*), atau khusus 'student.*'
        View::composer('*', function ($view) {
            $now = Carbon::now('Asia/Makassar');
            $cutoff = Carbon::createFromTimeString(config('mbg.cutoff_time', '20:00'), 'Asia/Makassar');

            $view->with([
                'serverTime' => $now,
                'isCutoffExpired' => $now->greaterThanOrEqualTo($cutoff),
                'cutoffRemainingSeconds' => max(0, $now->diffInSeconds($cutoff, false)),
            ]);
        });
    }
}
