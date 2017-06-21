<?php

namespace Calhoun\AB\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class BaseModel extends Eloquent
{
    public $timestamps = false;

    protected $connection = 'abtester';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    public function reset()
    {
        $this->query()->truncate();
    }
}
