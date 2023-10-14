<?php

namespace Kami\Cocktail\Console\Commands;

use Illuminate\Console\Command;
use Kami\Cocktail\Import\FromV3Export;
use Illuminate\Support\Facades\Storage;

class BarImportFromV3Export extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bar:import-zip {filename?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import bars exported from another Bar Assistant instance';

    public function __construct(private FromV3Export $exporter)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var \Illuminate\Support\Facades\Storage */
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('bar-assistant'),
        ]);

        $selectedFilename = $this->argument('filename');
        if ($selectedFilename) {
            $zipFilePath = $disk->path($this->argument('filename'));
        } else {
            $existingZipFiles = collect($disk->files())->filter(function ($filepath) {
                return str_ends_with($filepath, 'zip');
            })->toArray();

            if (count($existingZipFiles) === 0) {
                $this->warn('No available files found!');

                return Command::SUCCESS;
            }

            $zipFilePath = $this->choice(
                'What is the filename that you want to import?',
                $existingZipFiles,
            );

            $zipFilePath = $disk->path($zipFilePath);
        }

        $this->info(sprintf('Checking for "%s" file...', $zipFilePath));

        if (!file_exists($zipFilePath)) {
            $this->info('File not found! Make sure the file is located in storage/ directory.');

            return Command::FAILURE;
        }

        if (!$this->confirm('This is destructive action and will overwrite any data you currently have. You should consider creating a backup of your current data. This action cannot be undone. Are you sure you want to continue?')) {
            return Command::SUCCESS;
        }

        $this->exporter->process($zipFilePath);

        return Command::SUCCESS;
    }
}