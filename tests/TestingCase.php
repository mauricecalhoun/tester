<?php

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestingCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.connections.abtester', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function getPackageProviders($app)
    {
      return [
        \Calhoun\AB\Providers\ABTesterServiceProvider::class,
      ];
    }
}
