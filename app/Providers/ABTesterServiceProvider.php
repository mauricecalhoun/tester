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
            'database' => $this->databaseFiles(),
            'prefix'   => '',
        ];

        config(['database.connections.abtester' => $config]);

        $this->commands($this->commands);

        Route::aliasMiddleware('abtest', ABTesting::class);
    }

    private function databaseFiles()
    {
      $path = database_path('abtester.sqlite');

      if(!file_exists($path))
      {
        return base_path("vendor/mauricecalhoun/tester/app/Database/database.sqlite");
      }

      return $path;
    }
  }
