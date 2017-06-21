<?php

namespace Calhoun\AB\Models;

class Experiment extends BaseModel
{
    public $timestamps = true;

    protected $fillable = ['name', 'nomenclature', 'description', 'starting', 'ending', 'original'];

    public function trial($name)
    {
        return $this->trials()->whereName($name)->firstOrFail();
    }

    public function trials()
    {
        return $this->hasMany(Trial::class);
    }

    public function isActive()
    {
        $now = time();
        $start = strtotime($this->starting);
        $end = strtotime($this->ending);

        return ($now >= $start && $now <= $end);
    }

    public function isNotActive()
    {
        return !$this->isActive();
    }

    public function fetch($name)
    {
        return $this->whereNomenclature($name)->firstOrFail();
    }
}
