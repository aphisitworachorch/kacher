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
    protected $signature = 'dbml:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database to DBML';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     * @throws \Exception
     */
    public function handle(): int
    {
        $db = new DBMLController();
        $artisan = $db->parseToDBML ();
        $database = env('DB_DATABASE');
        $rand = Str::random (8);
        $path = "dbml/";
        $fileName = $path."dbml_{$database}_".$rand.".txt";
        Storage::put($fileName,$artisan);
        $getPath = Storage::path($fileName);
        $this->info("Created ! File Path : ".$getPath);
        return 0;
    }
}