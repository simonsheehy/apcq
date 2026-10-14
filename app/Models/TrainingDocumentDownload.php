<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrainingDocumentDownload extends Model
{
    protected $fillable = [
        'employee_id',
        'training_id',
        'media_id',
        'download_count',
        'first_downloaded_at',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'download_count' => 'integer',
            'first_downloaded_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public static function record(Employee $employee, Training $training, Media $document): self
    {
        $download = static::firstOrNew([
            'employee_id' => $employee->id,
            'media_id' => $document->id,
        ], [
            'training_id' => $training->id,
            'first_downloaded_at' => now(),
        ]);

        $download->download_count++;
        $download->last_downloaded_at = now();
        $download->save();

        return $download;
    }
}
