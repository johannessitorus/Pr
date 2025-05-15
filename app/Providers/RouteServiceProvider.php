<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard'; // Sesuaikan ini dengan rute dashboard Anda

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers'; // Ini biasanya sudah tidak diperlukan/dihapus di Laravel versi baru
                                                      // karena Laravel 8+ menggunakan syntax FQCN (Fully Qualified Class Name)
                                                      // untuk controller di file rute.
                                                      // Contoh: use App\Http\Controllers\MyController; Route::get('/', [MyController::class, 'index']);

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                // ->namespace($this->namespace) // Tidak diperlukan lagi jika menggunakan FQCN untuk controller
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Contoh jika Anda ingin menambahkan rate limiter untuk login
        // RateLimiter::for('login', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->input('email') . $request->ip());
        // });
    }
}
