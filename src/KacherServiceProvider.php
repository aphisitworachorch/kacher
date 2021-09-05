<?php

namespace Aphisitworachorch\Kacher;

use Aphisitworachorch\Console\Commands\DBML;
use Aphisitworachorch\Console\Commands\DBMLParse;
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
