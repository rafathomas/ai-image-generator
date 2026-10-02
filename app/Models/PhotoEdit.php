<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PhotoEdit extends Model
{
    use HasFactory;

    protected $fillable = ['source_path', 'reference_path', 'prompt', 'status', 'result_path', 'error_message'];

    protected $appends = ['source_url', 'reference_url', 'result_url'];

    public function getSourceUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->source_path);
    }

    public function getReferenceUrlAttribute(): ?string
    {
        return $this->reference_path ? Storage::disk('public')->url($this->reference_path) : null;
    }

    public function getResultUrlAttribute(): ?string
    {
        return $this->result_path ? Storage::disk('public')->url($this->result_path) : null;
    }
}
