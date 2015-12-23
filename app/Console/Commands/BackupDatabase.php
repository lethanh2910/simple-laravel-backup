<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon;
use Storage;
use DB;
use Mail;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run backup (mysqldump) on database and upload to S3';

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
        $filename   = "backup-" . Carbon\Carbon::now()->format('Y-m-d_H-i-s') . ".sql.gz";
        $return_var = NULL;
        $output     = NULL;
        $command    = "mysqldump --user=" . env('DB_USERNAME') ." --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "  | gzip > " . storage_path() . "/" . $filename;

        exec($command, $output, $return_var);

        if(!$return_var){

            $getFile = Storage::disk('local')->get($filename);

            Storage::disk('s3')->put("backups/" .  $filename, $getFile);

            Storage::disk('local')->delete($filename);

        }else{

            Mail::raw('There has been an error backing up the database.', function ($message) {
                $message->to("richard@example.com", "Rich")->subject("Backup Error");
            });

        }

        
    }
}
