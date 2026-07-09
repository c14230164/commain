<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'question_count'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function respondents()
    {
        return $this->hasMany(Respondent::class);
    }
}
