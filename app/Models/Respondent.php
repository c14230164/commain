<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respondent extends Model
{
    protected $fillable = ['project_id', 'paper_id', 'castor_name'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function variables()
    {
        return $this->belongsToMany(Variable::class);
    }
}
