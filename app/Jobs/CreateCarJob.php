<?php

namespace App\Jobs;

use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * CreateCarJob is an async job for creating cars via the queue.
 *
 * This job is dispatched to the Redis queue when creating a car asynchronously.
 * It encapsulates the logic of persisting a new car to the database, decoupling
 * the HTTP request from the actual database operation. Jobs can be retried on failure
 * and include tracking information for monitoring async operations.
 *
 * Job Flow:
 * 1. Job is dispatched from CarController::store with payload and UUID tracking ID
 * 2. Job is queued to the 'cars' Redis queue
 * 3. Horizon supervisor picks up job and processes via handle()
 * 4. Car is persisted to database, logged with tracking ID
 * 5. Client can poll API with tracking ID to check job status (if implemented)
 *
 * Configuration:
 * - Tries: 3 (job retried up to 3 times on failure)
 * - Queue: Dispatched via Queue::cars helper
 * - Serialization: Uses SerializesModels for safe model passing
 */
class CreateCarJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var int Maximum number of attempts before the job fails permanently */
    public $tries = 3;

    /** @var string Unique identifier for tracking this async job (UUID) */
    public $trackingId;

    /** @var array Car attributes to be created (name, make, model, year) */
    public $payload;

    /**
     * Construct a CreateCarJob with car data and tracking ID.
     *
     * The constructor accepts the car creation payload and a unique tracking UUID.
     * The tracking ID enables clients to monitor the async job status. The payload
     * contains only fillable car attributes (name, make, model, year).
     *
     * @param array $payload Car attributes to create (name, make, model, year)
     * @param string $trackingId Unique UUID for tracking this job execution
     */
    public function __construct(array $payload, string $trackingId)
    {
        $this->payload = $payload;
        $this->trackingId = $trackingId;
    }

    /**
     * Handle job execution by creating the car in the database.
     *
     * Creates a new Car model with the payload attributes and persists it to the database.
     * Logs successful creation with the tracking ID and car ID for monitoring async operations.
     * If this method throws an exception, the job is automatically retried (up to $tries attempts).
     * On final failure, the failed() method is called.
     */
    public function handle(): void
    {
        $car = Car::create($this->payload);
        Log::info('CreateCarJob: created car', ['id' => $car->id, 'tracking' => $this->trackingId]);
    }

    /**
     * Handle job failure after all retry attempts are exhausted.
     *
     * Called when the handle() method throws an exception and all retry attempts
     * (defined by $tries) have been consumed. Logs the failure with tracking ID
     * and error message for debugging and monitoring purposes.
     * This method allows graceful cleanup or notification of failed async operations.
     *
     * @param Throwable $exception The exception that caused the final failure
     */
    public function failed(Throwable $exception): void
    {
        Log::error('CreateCarJob failed', ['tracking' => $this->trackingId, 'error' => $exception->getMessage()]);
    }
}
