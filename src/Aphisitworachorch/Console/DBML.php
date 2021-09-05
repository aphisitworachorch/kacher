<?php

namespace Aphisitworachorch\Console;

use Aphisitworachorch\Controller\DBMLController;
use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;

class DBML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbml:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database Lister';

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
        $db = new DBMLController();
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
