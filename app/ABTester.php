<?php

namespace Calhoun\AB;

use Schema;
use Calhoun\AB\Models\Goal;
use Calhoun\AB\Models\Trial;
use Illuminate\Http\Request;
use Calhoun\AB\Models\Experiment;

class ABTester
{
    protected $data;
    protected $goal;
    protected $trial;
    protected $tester;
    protected $connection = 'abtester';
    protected $experiment;

    public function __construct(Experiment $experiment, Trial $trial, Goal $goal)
    {
        $this->experiment = $experiment;
        $this->trial = $trial;
        $this->goal = $goal;
    }

    public function setConnection($name)
    {
        $this->connection = $name;
        return $this;
    }

    public function flush()
    {
        $this->experiment->reset();
        $this->trial->reset();
        $this->goal->reset();
        session()->forget('experiment');
    }

    public function install()
    {
        if (! Schema::connection($this->connection)->hasTable('experiments')) {
            Schema::connection($this->connection)->create('experiments', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('nomenclature');
                $table->text('description');
                $table->string('ending');
                $table->string('starting');
                $table->string('original');
                $table->timestamps();
            });
        }

        if (! Schema::connection($this->connection)->hasTable('trials')) {
            Schema::connection($this->connection)->create('trials', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('experiment_id')->unsigned()->default(0);
                $table->integer('visitors')->unsigned()->default(0);
                $table->integer('engagement')->unsigned()->default(0);
            });
        }
        if (! Schema::connection($this->connection)->hasTable('goals')) {
            Schema::connection($this->connection)->create('goals', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('route');
                $table->integer('trial_id')->unsigned()->default(0);
                $table->integer('count')->unsigned()->default(0);
            });
        }

        collect(config("ab.experiments"))->each(function ($experiment) {
            $insertedExperiment = $this->experiment->setConnection($this->connection)->firstOrCreate([
               'name'         => $experiment['name'],
               'nomenclature' => $experiment['nomenclature'],
               'description'  => $experiment['description'],
               'original'     => $experiment['original'],
               'starting'     => $experiment['starting'],
               'ending'       => $experiment['ending']
           ]);

            collect($experiment['trials'])->each(function ($trial) use ($experiment, $insertedExperiment) {
                $this->createBladeFile($trial);

                $insertedTrial = $this->trial->setConnection($this->connection)->firstOrCreate([
                  'name'          => $trial,
                  'experiment_id' => $insertedExperiment->id
                ]);

                $this->goal->setConnection($this->connection)->insert(collect($experiment['goals'])->map(function ($route, $name) use ($insertedTrial) {
                    return [
                     'name'  => $name,
                     'route' => $route,
                     'trial_id' => $insertedTrial->id
                   ];
                })->all());
            });
        });
    }

    public function report($name)
    {
        return $this->experiment($name)->tester->trials->map(function ($trial) {
            $engagement = $trial->visitors ? ($trial->engagement / $trial->visitors * 100) : 0;
            $meta = collect([
                'Experiment' => $trial->name,
                'Visitors'   => $trial->visitors,
                'Engagement' => number_format($engagement, 2) . " % (" . $trial->engagement .")",
            ]);

            $goals = $trial->goals->flatmap(function ($goal) use ($trial) {
                $percentage = $trial->visitors ? ($goal->count / $trial->visitors * 100) : 0;
                return [$goal->name => number_format($percentage, 2) . " % ($goal->count)"];
            });

            return $meta->merge($goals);
        });
    }

    public function track($referer, $pathInfo)
    {
        if (! session('experiment')) {
            return;
        }

        $this->tester = $this->experiment->fetch(session($this->field('name')));

        $this->pageView(session($this->field('name')));

        if ($this->isRefreshed($referer, $pathInfo)) {
            return;
        }

        $this->interact(session($this->field('name')));

        $this->detectGoalCompletion($pathInfo);
    }

    public function experiment($name, $data = [])
    {
        $this->data = $data;

        session()->put($this->field('name'), $name);

        return tap($this, function ($instance) use ($name) {
            $instance->tester = $this->experiment->fetch($name);
        });
    }

    public function run()
    {
        return $this->trial();
    }

    private function trial()
    {
        $view = ($this->tester->isActive()) ? (session($this->field('trial')) ?: $this->nextTrial()) : $this->tester->original;

        if (view()->exists($view)) {
            return view($view, $this->data)->render();
        }

        throw new \Exception("The view ($view) requested, can not be found!");
    }

    private function nextTrial()
    {
        return tap($this->tester->trials()->orderBy('visitors', 'asc')->firstOrFail()->name, function ($name) {
            session()->put($this->field('trial'), $name);
            $this->pageView($name);
        });
    }

    private function pageView($trial)
    {
        if (session($this->field('pageview'))) {
            return;
        }

        session([$this->field('pageview') => $this->updateTrial($trial, function ($instance) {
            $instance->visitors++;
        })]);
    }

    private function interact($trial)
    {
        if (session($this->field('interacted'))) {
            return;
        }

        session([$this->field('interacted') => $this->updateTrial($trial, function ($instance) {
            $instance->engagement++;
        })]);
    }

    private function complete($goal)
    {
        if (session($this->field('completed'))) {
            return;
        }

        session([$this->field('completed') => $this->updateGoal($goal, function ($instance) {
            $instance->count++;
        })]);
    }

    private function isRefreshed($referer, $pathInfo)
    {
        return $referer == $pathInfo;
    }

    private function detectGoalCompletion($pathInfo)
    {
        $goal = $this->tester->trial(session($this->field('trial')))->goals->first(function ($goal) use ($pathInfo) {
            return $goal->route == $pathInfo;
        });

        if ($goal) {
            $this->complete($goal);
        }
    }

    private function updateTrial($trial, $closure)
    {
        return tap($this->tester->trials()->firstOrNew(['name' => $trial, 'experiment_id' => $this->tester->id]), $closure)->save();
    }

    private function updateGoal($goal, $closure)
    {
        return tap($this->tester->trial(session($this->field('name')))->goals()->firstOrCreate(['name' => $goal['name'], 'route' => $goal['route'], 'trial' => session($this->field('name'))]), $closure)->save();
    }

    private function createBladeFile($trial)
    {
        $path = str_replace(".", "/", $trial);
        $blade = sprintf("%s/views/%s.blade.php", resource_path(), $path);

        if (!file_exists(resource_path())) {
            return;
        }

        if (!file_exists($blade)) {
            file_put_contents($blade, $trial);
        }
    }

    private function field($name)
    {
      return sprintf("experiment.%s", $name);
    }
}
