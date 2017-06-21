<?php

namespace Calhoun\AB\Console;

use Calhoun\AB\ABTester;
use Illuminate\Console\Command;

class Create extends Command
{
    protected $signature = 'ab:create-experiment';

    protected $description = 'Create An AB Test Experiment';

    protected $config = [];

    protected $tester;

    public function __construct(ABTester $tester)
    {
        parent::__construct();
        $this->tester = $tester;
    }

    public function handle()
    {
        $config['name'] = $this->ask('What is your experiment name?');
        $config['nomenclature'] = $this->ask('What is your experiment nomenclature (slug)?');
        $config['description'] = $this->ask('Just a description of the experiment.');
        $config['starting'] = $this->ask('What is the starting date? eg.(mm/dd/yy)');
        $config['ending'] = $this->ask('What is the ending date? eg.(mm/dd/yy)');

        while ($this->confirm('Would you like to add a trial (blade)?')) {
            $config['trials'][] = $this->ask('Enter the path to your trial (blade) eg.(test.trial)');
        }

        while ($this->confirm('Would you like to add a goal?')) {
            $label = $this->ask('Enter the name of your goal');
            $route = $this->ask('Enter the uri route to your goal');
            $config['goals'][$label] = $route;
        }

        $config['original'] = $this->ask('What was the original path to your blade file?');

        config(['ab.experiments' => [$config]]);

        $this->tester->install();

        $this->info("Your experiment has been created");
        dump($config);
    }
}
