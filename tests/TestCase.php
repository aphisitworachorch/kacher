<?php

namespace Aphisitworachorch\Kacher\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Aphisitworachorch\Kacher\KacherServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application   $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        //
    }
}
