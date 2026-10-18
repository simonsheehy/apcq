<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingDocumentDownload extends Model
{
    protected $fillable = [
        'employee_id',
        'training_id',
        'training_document_id',
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

    public function document(): BelongsTo
    {
        return $this->belongsTo(TrainingDocument::class, 'training_document_id');
    }

    public static function record(Employee $employee, TrainingDocument $document): self
    {
        $download = static::createOrFirst([
            'employee_id' => $employee->id,
            'training_document_id' => $document->id,
        ], [
            'training_id' => $document->training_id,
            'first_downloaded_at' => now(),
            'last_downloaded_at' => now(),
        ]);

        $download->increment('download_count', 1, ['last_downloaded_at' => now()]);

        return $download;
    }
}
