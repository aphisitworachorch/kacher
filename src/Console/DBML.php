<?php

namespace Aphisitworachorch\Kacher\Console;

use Aphisitworachorch\Kacher\Controller\DBMLController;
use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;

class DBML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbml:list {--custom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Artisan Database Lister';

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
        $artisan = $db->getDatabaseTable ("artisan");
        $bar = $this->output->createProgressBar (count($artisan));
        $bar->start ();
        foreach($artisan as $data){
            $this->table(["table","columns","foreign_key","indexes"],[$data]);
            $this->info("\n");
        }
        $bar->finish();

        return 0;
    }
}
