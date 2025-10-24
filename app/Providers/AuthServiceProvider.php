<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate; 
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // --- INI KODE UNTUK ROLE ADMIN ANDA ---
        // Mendefinisikan Gate 'access-admin-areas'
        // Gate ini akan 'true' HANYA JIKA role user yang login adalah 'admin'
        Gate::define('access-admin-areas', function ($user) {
            return $user->role === 'admin';
        });
        // --- BATAS KODE ---
    }
}