<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobBatch extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
    ];

    protected $appends = [
        'status',
        'progress',
    ];

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class, 'batch_id');
    }

    public function getStatusAttribute()
    {
        return $this->status();
    }

    public function getProgressAttribute()
    {
        return $this->progress();
    }

    public function status(): string
    {
        if ($this->hasFailures()) {
            return 'failed';
        }

        if ($this->completed()) {
            return 'completed';
        }

        if ($this->processing()) {
            return 'processing';
        }

        if ($this->pending()) {
            return 'pending';
        }
    }

    /**
     * Determine if the batch is pending.
     *
     * @return bool
     */
    public function pending(): bool
    {
        return !$this->cancelled_at && $this->pending_jobs > 0 && !$this->failed_jobs;
    }

    /**
     * Determine if the batch is processing.
     *
     * @return bool
     */
    public function processing(): bool
    {
        return $this->progress() >= 0 && $this->progress() <= 100;
    }

    /**
     * Get the total number of jobs that have been processed by the batch thus far.
     *
     * @return int
     */
    public function processedJobs(): int
    {
        return $this->total_jobs - $this->pending_jobs;
    }

    /**
     * Get the percentage of jobs that have been processed (between 0-100).
     *
     * @return int
     */
    public function progress(): int
    {
        return $this->total_jobs > 0 ? round(($this->processedJobs() / $this->total_jobs) * 100) : 0;
    }

    /**
     * Determine if the batch has pending jobs
     *
     * @return bool
     */
    public function hasPendingJobs(): bool
    {
        return $this->pending_jobs > 0;
    }

    /**
     * Determine if the batch has completed executing.
     *
     * @return bool
     */
    public function completed(): bool
    {
        return !is_null($this->finished_at);
    }

    /**
     * Determine if the batch has job failures.
     *
     * @return bool
     */
    public function hasFailures(): bool
    {
        return $this->failed_jobs > 0;
    }

    /**
     * Determine if all jobs failed.
     *
     * @return bool
     */
    public function failed(): bool
    {
        return $this->failed_jobs === $this->total_jobs;
    }

    /**
     * Determine if the batch has been canceled.
     *
     * @return bool
     */
    public function cancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }
}
