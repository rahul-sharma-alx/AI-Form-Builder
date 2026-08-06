<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiJob extends Model
{
    protected $fillable = [
        'form_id',
        'prompt',
        'response',
        'model',
        'status',
        'error_message',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
