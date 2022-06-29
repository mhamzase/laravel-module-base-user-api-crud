<?php

namespace Modules\Base\Database\Seeders;

use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;
use Waavi\Translation\Models\Language;
use Waavi\Translation\Models\Translation;

class TranslationsSeeder extends Seeder {
    private $languages = [];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Language::query()->truncate();
        Translation::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        //For each active Language
        $this->languages = config('translator.available_locales');
        if(!$this->languages) {
            $this->languages = ['en'];
        }

        $this->languages();
        $this->loadCSV();
    }

    protected function languages() {
        foreach($this->languages as $lang) {
            Language::create([
                'locale'    =>  $lang,
                'name'    =>  $lang,
            ]);
        }

    }

    private function loadCSV() {
        $csv = Reader::createFromPath(storage_path('translations.csv'), 'r');

        $csv->setHeaderOffset(0); //set the CSV header offset

        //get 25 records starting from the 11th row
        $stmt = (new Statement());

        $records = $stmt->process($csv);

        foreach ($records as $record) {
            foreach($this->languages as $lang) {
                if(!isset($record[$lang])) {
                    continue;
                }

                try {
                    Translation::create([
                        'locale' => $lang,
                        'namespace'  => '*',
                        'group'  => isset($record['module']) ? $record['module'] : 'base',
                        'item'   => $record['slug'],
                        'text'   => $record[$lang],
                    ]);

                } catch (QueryException $exception) {
                    //Do nothing for now
                }

            }
        }
    }
}
