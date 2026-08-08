<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'schema',
        'settings',
        'metadata',
        'status',
        'version',
        'published_at',
        'last_saved_at',
    ];

    protected $casts = [
        'schema' => 'array',
        'settings' => 'array',
        'metadata' => 'array',
        'version' => 'integer',
        'published_at' => 'datetime',
        'last_saved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($form) {
            if (empty($form->uuid)) {
                $form->uuid = Str::uuid();
            }
            if (empty($form->settings)) {
                $form->settings = [];
            }
            if (empty($form->metadata)) {
                $form->metadata = [];
            }
        });
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function aiJobs()
    {
        return $this->hasMany(AIJob::class);
    }

    public function formVersions()
    {
        return $this->hasMany(FormVersion::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function bumpVersion(): void
    {
        $this->version = ((int) $this->version) + 1;
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
