<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Contract; // ← PERBAIKI INI!
use App\Observers\ContractObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

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
    public function boot()
{
    // Helper untuk mendapatkan route yang tepat
    view()->composer('*', function ($view) {
        $view->with('getDocumentRoute', function($contract) {
            // Coba route surat dulu
            if ($contract->contract_type === 'surat') {
                try {
                    // Cek apakah route surat.show ada
                    if (Route::has('surat.show')) {
                        return route('surat.show', $contract);
                    }
                } catch (\Exception $e) {
                    // Fallback ke contract.show
                    return route('contracts.show', $contract);
                }
            }
            
            // Untuk contract dengan dynamic workflow
            if ($contract->workflow_type === 'dynamic' && $contract->currentStage()) {
                try {
                    if (Route::has('review-stages.show')) {
                        return route('review-stages.show', [$contract, $contract->currentStage()]);
                    }
                } catch (\Exception $e) {
                    return route('contracts.show', $contract);
                }
            }

            if (config('app.env') === 'production') {
                URL::forceScheme('https');
            }
            
            // Default ke contract.show
            return route('contracts.show', $contract);
        });
    });
}
}
