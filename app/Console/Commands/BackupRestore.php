<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class BackupRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore Database backup';

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

        $i = 0;
        foreach($files as $file){

            $filename[$i]['file'] = $file;
            $i++;

        }

        $headers = ['File Name'];
        $this->table($headers, $filename);

        $backupFilename = $this->ask('Which file would you like to restore?');

        $getBackupFile  = Storage::disk('s3')->get($backupFilename);

        $backupFilename  = explode("/", $backupFilename);

        Storage::disk('local')->put($backupFilename[1], $getBackupFile);

        $mime = Storage::mimeType($backupFilename[1]);

        if($mime == "application/x-gzip"){

            $command = "zcat " . storage_path() . "/" . $backupFilename[1] . " | mysql --user=" . env('DB_USERNAME') ." --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "";

        }elseif($mime == "text/plain"){

            $command = "mysql --user=" . env('DB_USERNAME') ." --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . " < " . storage_path() . "/" . $backupFilename[1] . "";

        }else{

            $this->error("File is not gzip or plain text");
            Storage::disk('local')->delete($backupFilename[1]);
            return false;

        }
        
        if ($this->confirm("Are you sure you want to restore the database? [y|N]")) {
            
            $returnVar  = NULL;
            $output     = NULL;
            exec($command, $output, $returnVar);

            Storage::disk('local')->delete($backupFilename[1]);

            if(!$returnVar){

                $this->info('Database Restored');

            }else{

                $this->error($returnVar);   

            }

        }
    }
}
