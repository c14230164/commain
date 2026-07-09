<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['project_id', 'order', 'text'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function variables()
    {
        return $this->hasMany(Variable::class);
    }
}
