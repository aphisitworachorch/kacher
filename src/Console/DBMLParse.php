<?php

namespace Aphisitworachorch\Kacher\Console;

use Aphisitworachorch\Kacher\Controller\DBMLController;
use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DBMLParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbml:parse {--dbdocs} {--custom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Artisan Parse Database Schema to DBML';

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
     * @return int
     * @throws Exception
     */
    public function handle()
    {
        $custom_type = null;
        if($this->option ("custom") != null){
            $file = json_decode(file_get_contents(storage_path() . "/app/custom_type.json"), true);
            $custom_type = $file;
        }
        $db = new DBMLController($custom_type);
        $artisan = $db->parseToDBML ();
        $database = env('DB_DATABASE');
        $rand = Str::random (8);
        $path = "dbml";
        $fileName = "{$path}/dbml_{$database}_".$rand.".txt";
        Storage::put($fileName,$artisan);
        $getPath = Storage::path($fileName);
        $this->info("Created ! File Path : ".$getPath);
        if($this->option ("dbdocs") != null){
            $this->warn ("Please Install dbdocs (npm install -g dbdocs) before run command");
            $this->info("Now you can run with command : dbdocs build $getPath --project=$database --password=$rand");
        }
        return 0;
    }
}
