<?php

namespace App\Providers;

use App\Contracts\Filters\PipelineInterface;
use App\Filters\CategoryFilter;
use App\Filters\DescriptionFilter;
use App\Filters\EavFilter;
use App\Filters\IsRemoteFilter;
use App\Filters\JobTypeFilter;
use App\Filters\LanguageFilter;
use App\Filters\LocationFilter;
use App\Filters\SalaryRangeFilter;
use App\Filters\StatusFilter;
use App\Filters\TitleFilter;
use App\Services\Filters\LogicalFilterPipeline;
use Illuminate\Support\ServiceProvider;

class FilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PipelineInterface::class, function ($app) {
            $pipeline = new LogicalFilterPipeline;

            // Register all filters
            $pipeline->addFilter(new StatusFilter);
            $pipeline->addFilter(new JobTypeFilter);
            $pipeline->addFilter(new IsRemoteFilter);
            $pipeline->addFilter(new LanguageFilter);
            $pipeline->addFilter(new LocationFilter);
            $pipeline->addFilter(new CategoryFilter);
            $pipeline->addFilter(new TitleFilter);
            $pipeline->addFilter(new DescriptionFilter);
            $pipeline->addFilter(new SalaryRangeFilter);

            if (class_exists(EavFilter::class)) {
                $pipeline->addFilter(new EavFilter);
            }

            return $pipeline;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
