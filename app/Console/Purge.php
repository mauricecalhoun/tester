<?php

namespace Calhoun\AB\Console;

use Calhoun\AB\ABTester;
use Illuminate\Console\Command;

class Purge extends Command
{
    protected $signature = 'ab:purge-experiments';

    protected $description = 'Flush All AB Test Experiments';

    protected $config = [];

    protected $tester;

    public function __construct(ABTester $tester)
    {
        parent::__construct();
        $this->tester = $tester;
    }

    public function handle()
    {
      $this->tester->flush();
      $this->info("All experiments has been purged");
    }
}
