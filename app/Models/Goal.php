<?php

namespace Calhoun\AB\Models;

class Goal extends BaseModel
{
    protected $fillable = ['name', 'route', 'trial_id', 'count'];

    protected $casts = [
    'trial_id' => 'integer',
    'count' => 'integer'
  ];
}
