<?php

namespace Calhoun\AB\Models;

class Trial extends BaseModel
{
    protected $fillable = ['name', 'visitors', 'engagement', 'experiment_id'];

    protected $casts = [
    'visitors' => 'integer',
    'engagement' => 'integer',
    'experiment_id' => 'integer'
  ];

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function fetch($name)
    {
        return $this->whereNomenclature($name)->firstOrFail();
    }
}
