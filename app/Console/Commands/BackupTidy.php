<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Storage;
use Carbon;

class BackupTidy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:tidy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove backup files that are over one month old.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = Storage::disk('s3')->files('backups');

        foreach($files as $file){

            $modified = Storage::disk('s3')->lastModified($file);
            $date     = Carbon\Carbon::createFromTimestampUTC($modified);

            if($date < Carbon\Carbon::now()->subMonth(1)){

                Storage::disk('s3')->delete($file);

                $this->info("Deleted " . $file);

            }

        }
        
    }
}
