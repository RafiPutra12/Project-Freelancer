<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $table = "services";

    public function user()
    {
        return $this->belongsTo('App\User', 'id');
    }
}
