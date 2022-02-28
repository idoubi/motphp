<?php

namespace mot\component;

use Illuminate\Database\Eloquent\Model as ModelBase;

class Model extends ModelBase
{
    protected $guarded = [];
    public $timestamps = false;
}
