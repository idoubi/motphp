<?php

namespace mot\trait;

trait SoftDeletes
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new SoftDeletingScope);
    }
}
