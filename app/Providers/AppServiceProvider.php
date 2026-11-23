<?php

namespace App\Providers;

use App\Services\Skills\AiSkillExtractor;
use App\Services\Skills\KeywordSkillExtractor;
use App\Services\Skills\SkillExtractor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SkillExtractor::class, function ($app) {
            return config('jobmatch.extractor') === 'ai'
                ? $app->make(AiSkillExtractor::class)
                : $app->make(KeywordSkillExtractor::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
