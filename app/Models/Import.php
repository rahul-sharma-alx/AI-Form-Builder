<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = [
        'form_id',
        'file_name',
        'file_path',
        'type',
        'status',
        'parsed_schema',
        'error_message',
    ];

    protected $casts = [
        'parsed_schema' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
