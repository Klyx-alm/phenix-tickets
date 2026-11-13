<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    protected $fillable = [
        'title', 'source_url', 'tiktok_id', 'type', 'cover', 'file', 'position', 'is_visible',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_visible' => 'boolean',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function coverUrl(): ?string
    {
        return $this->cover ? asset('storage/'.$this->cover) : null;
    }

    public function hasFile(): bool
    {
        return (bool) $this->file;
    }

    public function fileUrl(): ?string
    {
        return $this->file ? asset('storage/'.$this->file) : null;
    }

    /** Lecteur TikTok intégré (c'est l'adresse que génère le code d'intégration officiel de TikTok) */
    public function embedUrl(): string
    {
        return 'https://www.tiktok.com/embed/v2/'.$this->tiktok_id;
    }

    public function deleteCoverFile(): void
    {
        if ($this->cover) {
            Storage::disk('public')->delete($this->cover);
        }
    }

    public function deleteVideoFile(): void
    {
        if ($this->file) {
            Storage::disk('public')->delete($this->file);
        }
    }
}
