<?php

use Calhoun\AB\Models\Goal;
use Calhoun\AB\Models\Trial;
use Calhoun\AB\Models\Experiment;
use Calhoun\AB\ABTester as Tester;

class ABTester extends TestingCase
{
    protected $goal;
    protected $trial;
    protected $experiment;

    public function setUp()
    {
      parent::setUp();

      $this->stubbed();

      $this->tester = app()->make(Tester::class);
      $this->goal = app()->make(Goal::class);
      $this->trial = app()->make(Trial::class);
      $this->experiment = app()->make(Experiment::class);

    }

   /**
   * @test
   */
    public function can_install_experiment()
    {
        $this->tester->install();
        $test = $this->experiment->fetch('form_something');

        $this->assertEquals($test->name, 'Form');
        $this->assertEquals($test->nomenclature, 'form_something');
        $this->assertEquals($test->original, 'welcome');
        $this->assertCount(4, $test->trials);
        $this->assertCount(1, $test->trial('short')->goals);
    }

    /**
    * @test
    */
     public function can_purge_experiments()
     {
         $this->tester->install();
         $test = $this->experiment->fetch('form_something');
         $this->assertEquals($test->name, 'Form');
         $this->tester->flush();
         $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
         $this->experiment->fetch('form_something');
     }

     /**
     * @test
     */
     public function can_track_experiment()
     {
       $this->tester->install();
       experiment("form_something");

       $this->assertTrue(session()->get('experiment.pageview'));
       $this->assertFalse(session()->has('experiment.interacted'));

       track('/', '/test');

       $this->assertTrue(session()->get('experiment.pageview'));
       $this->assertTrue(session()->has('experiment.interacted'));
       $this->assertTrue(session()->get('experiment.interacted'));
       $this->assertFalse(session()->has('experiment.completed'));

       track('/', '/finish');

       $this->assertTrue(session()->get('experiment.pageview'));
       $this->assertTrue(session()->get('experiment.interacted'));
       $this->assertTrue(session()->get('experiment.completed'));

       $trial = report('form_something')->first();
       $this->assertEquals($trial->get('Visitors'), 1);
       $this->assertEquals($trial->get('Engagement'), "100.00 % (1)");
       $this->assertEquals($trial->get('Finish'), "100.00 % (1)");
     }

    private function stubbed()
    {
      $stubbed =  [
        [
            'name'          => 'Form',
            'ending'        => '06/23/17',
            'starting'      => '06/16/17',
            'original'      => 'welcome',
            'nomenclature'  => 'form_something',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            'trials'        => ['short', 'long', 'tall', 'wide'],
            'goals'         => [
              'Finish' => '/finish'
            ]
          ]
      ];

      config(['ab.experiments' => $stubbed]);
    }
}
