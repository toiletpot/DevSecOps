<?php

declare(strict_types=1);

namespace Kami\Cocktail\Jobs;

use Illuminate\Bus\Queueable;
use Kami\Cocktail\Models\Bar;
use Kami\Cocktail\Models\User;
use Illuminate\Queue\SerializesModels;
use Kami\Cocktail\Import\FromLocalData;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\WithoutRelations;

class SetupBar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        #[WithoutRelations]
        private readonly Bar $bar,
        #[WithoutRelations]
        private readonly User $user,
        private array $barOptions = []
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(FromLocalData $import): void
    {
        $import->process($this->bar, $this->user, $this->barOptions);
    }
}
