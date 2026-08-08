<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiJob extends Model
{
    protected $fillable = [
        'form_id',
        'kind',
        'prompt',
        'response',
        'diff',
        'model',
        'status',
        'error_message',
    ];

    protected $casts = [
        'diff' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
