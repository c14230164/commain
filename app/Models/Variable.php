<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    protected $fillable = ['question_id', 'name'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function respondents()
    {
        return $this->belongsToMany(Respondent::class);
    }
}
