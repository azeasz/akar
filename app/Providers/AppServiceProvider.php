<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Services\BadgeMemberService;
use App\Services\FobiApiService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register BadgeMemberService
        $this->app->singleton(BadgeMemberService::class, function ($app) {
            return new BadgeMemberService($app->make(FobiApiService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mendaftarkan observer
        User::observe(UserObserver::class);
        
        // Membuat helper directive untuk Blade
        Blade::directive('tinymce', function () {
            $apiKey = config('tinymce.api_key');
            return "<?php echo '<script src=\"https://cdn.tiny.cloud/1/$apiKey/tinymce/6/tinymce.min.js\" referrerpolicy=\"origin\"></script>'; ?>";
        });
    }
}
