<?php

namespace Aphisitworachorch\Kacher;

use Aphisitworachorch\Kacher\Console\DBML;
use Aphisitworachorch\Kacher\Console\DBMLParse;
use Aphisitworachorch\Kacher\Controller\DBMLController;
use Illuminate\Support\ServiceProvider;

class KacherServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton (DBMLController::class);
        if($this->app->runningInConsole ()){
            $this->commands ([
                DBML::class,
                DBMLParse::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
