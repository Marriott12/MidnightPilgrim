<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider - Core Application Services Registration
 * 
 * Registers all discipline system services and their dependencies.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Archive Enforcement Service (no dependencies)
        $this->app->singleton(\App\Services\ArchiveEnforcementService::class, function ($app) {
            return new \App\Services\ArchiveEnforcementService();
        });

        // Constraint Validation Service (no dependencies)
        $this->app->singleton(\App\Services\ConstraintValidationService::class, function ($app) {
            return new \App\Services\ConstraintValidationService();
        });

        // Discipline Contract Service (depends on Archive & Constraint services)
        $this->app->singleton(\App\Services\DisciplineContractService::class, function ($app) {
            return new \App\Services\DisciplineContractService(
                $app->make(\App\Services\ArchiveEnforcementService::class),
                $app->make(\App\Services\ConstraintValidationService::class)
            );
        });

        // Discipline Notification Service (no dependencies)
        $this->app->singleton(\App\Services\DisciplineNotificationService::class, function ($app) {
            return new \App\Services\DisciplineNotificationService();
        });

        // Pattern Tracking Service (if not already registered)
        if (!$this->app->bound(\App\Services\PatternTrackingService::class)) {
            $this->app->singleton(\App\Services\PatternTrackingService::class);
        }

        // Conversational Engine Service (if not already registered)
        if (!$this->app->bound(\App\Services\ConversationalEngineService::class)) {
            $this->app->singleton(\App\Services\ConversationalEngineService::class);
        }

        // Emotional Pattern Engine Service (if not already registered)
        if (!$this->app->bound(\App\Services\EmotionalPatternEngineService::class)) {
            $this->app->singleton(\App\Services\EmotionalPatternEngineService::class);
        }

        // Narrative Continuity Engine Service (if not already registered)
        if (!$this->app->bound(\App\Services\NarrativeContinuityEngineService::class)) {
            $this->app->singleton(\App\Services\NarrativeContinuityEngineService::class);
        }

        // Feature Button Service (if not already registered)
        if (!$this->app->bound(\App\Services\FeatureButtonService::class)) {
            $this->app->singleton(\App\Services\FeatureButtonService::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
