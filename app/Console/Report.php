<?php

namespace Calhoun\AB\Console;

use Calhoun\AB\ABTester;
use Illuminate\Console\Command;
use Calhoun\AB\Models\Experiment;

class Report extends Command
{
    protected $signature = 'ab:create-report';

    protected $description = 'Create An AB Test Report';

    protected $config = [];

    protected $tester;

    protected $experiment;

    public function __construct(ABTester $tester, Experiment $experiment)
    {
        parent::__construct();
        $this->tester = $tester;
        $this->experiment = $experiment;
    }

    public function handle()
    {
        $experiments = $this->experiment->get()->keyBy('nomenclature');
        $choices = $experiments->pluck('name', 'nomenclature');
        $choice = $choices->keys()->first();
        $name = $this->choice('Choose an experiment', $choices->all(), $choice);
        $experiment = $experiments->get($name);

        $data = $this->tester->report($name);
        $headers = $data->first()->keys();

        $this->line('<info>' . $experiment->name . '</info>: ' .  $experiment->description);
        $this->line('<info>Duration</info>: ' .  $experiment->starting . ' - ' .  $experiment->ending);

        $this->table($headers->toArray(), $data->toArray());
    }
}
