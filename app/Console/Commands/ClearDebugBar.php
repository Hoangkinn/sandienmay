<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearDebugBar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('debugbar');

        if (!is_dir($path)) {
            $this->info('Debugbar log directory does not exist.');
            return;
        }

        $files = glob($path . '/*.json');

        foreach ($files as $file) {
            @unlink($file);
        }

        $this->info(count($files) . ' debugbar files deleted.');
    }
}
