<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = "projects";


    public function projects(){
    	return $this->belongsTo('App\User', 'userid', 'id');
    }
    
    protected $fillable = [
        'userid', 'descriptions', 'budget', 'type', 'status', 'projectname','created_at','updated_at'
    ];
}
