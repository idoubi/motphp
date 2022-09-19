<?php

namespace mot\component;

use Illuminate\Database\Eloquent\Model as ModelBase;
use mot\trait\SoftDeletes;

class Model extends ModelBase
{
    // use SoftDeletes;

    protected $guarded = [];
    public $timestamps = false;
    protected $dateFormat = 'U';
}
