<?php

namespace Modules\Base\Console;

use Modules\Base\Database\Seeders\PhraseSeeder;
use Modules\Base\Database\Seeders\TranslationsSeeder;
use Modules\Base\Entities\Phrase;
use Modules\Base\Helpers\WebSeed;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CsvTranslations extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'whf:csv-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load Translations from csv file';

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
        $seeder = new WebSeed;
        $seeder->call(TranslationsSeeder::class);
    }
}
