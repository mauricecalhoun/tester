<?php

namespace Calhoun\AB\Providers;

use Route;
use Calhoun\AB\Console\Purge;
use Calhoun\AB\Console\Create;
use Calhoun\AB\Console\Report;
use Calhoun\AB\Middleware\ABTesting;
use Illuminate\Support\ServiceProvider;

class ABTesterServiceProvider extends ServiceProvider
{
    protected $commands = [
      Create::class,
      Report::class,
      Purge::class,
  ];

    public function register()
    {
        $config = [
            'driver'   => 'sqlite',
            'database' => base_path("vendor/mauricecalhoun/tester/app/Database/database.sqlite"),
            'prefix'   => '',
        ];

        config(['database.connections.abtester' => $config]);

        $this->commands($this->commands);

        Route::aliasMiddleware('abtest', ABTesting::class);        
    }
}
