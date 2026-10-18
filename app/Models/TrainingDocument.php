<?php

namespace App\Models;

use Database\Factories\TrainingDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrainingDocument extends Model implements HasMedia
{
    /** @use HasFactory<TrainingDocumentFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'training_id',
        'title',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(TrainingDocumentDownload::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')
            ->singleFile()
            ->useDisk('local');
    }

    public function file(): ?Media
    {
        return $this->getFirstMedia('file');
    }
}
