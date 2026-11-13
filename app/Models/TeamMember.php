<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    /** Cadrages proposés dans l'admin : valeur CSS object-position => libellé */
    public const FOCUS = [
        '50% 10%' => 'Haut de la photo',
        '50% 25%' => 'Visage (par défaut)',
        '50% 50%' => 'Centre',
        '50% 75%' => 'Bas de la photo',
    ];

    protected $fillable = ['name', 'role', 'note', 'photo', 'focus', 'position', 'is_visible'];

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

    public function photoUrl(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        // Les photos livrées avec le projet sont dans public/images, les autres dans le disque public
        return str_starts_with($this->photo, 'images/')
            ? asset($this->photo)
            : asset('storage/'.$this->photo);
    }

    public function deletePhotoFile(): void
    {
        if ($this->photo && ! str_starts_with($this->photo, 'images/')) {
            Storage::disk('public')->delete($this->photo);
        }
    }
}
