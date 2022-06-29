<?php


namespace Modules\Base\Traits;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasTranslation {

    protected static $fillableT = [];
    protected static $transableInstance = false;
    protected static $transableEntity = __CLASS__ . 'Tran';

    public function __construct(array $attributes = array()) {
        parent::__construct($attributes);

        $this->with[] = 'translations';
        self::$transableInstance = new self::$transableEntity;

        self::$fillableT = self::$transableInstance->getFillable();
//        $this->appends = array_merge(self::$fillableT, $this->appends);
    }

    /**
     * Creating Generic Expected relation for translatable trait
     * @return mixed
     */
    public function translations() {
        return $this->hasMany(self::$transableEntity);
    }

    public static function createT($data) {
        return self::translationUpdateOrCreate($data);
    }

    public function updateT($data) {
        return self::translationUpdateOrCreate($data, $this);
    }

    public function getAttribute($attribute)
    {
        $transableInstance = new self::$transableEntity;
        $fillable = $transableInstance->getFillable();

        if(in_array($attribute, $fillable)) {
            $translations = $this->translations->keyBy('lang');
            $currentLang = App::getLocale();
            $defaultLang = config('app.fallback_locale');
            $attributeVal = '';

            //Try to get current language translaction or default
            if(isset($translations[$currentLang])) {
                $attributeVal = $translations[$currentLang]->$attribute;
            } elseif(isset($translations[$defaultLang])) {
                $attributeVal = $translations[$defaultLang]->$attribute;
            }

            return $attributeVal;
        }

        return parent::getAttribute($attribute);
    }

    protected static function translationUpdateOrCreate($data, $parentItem = false) {
        $transableInstance = new self::$transableEntity;
        $fillable = $transableInstance->getFillable();

        $translableData = [];
        foreach($fillable as $column) {
            if(isset($data[$column])) {
                $translableData[$column] = $data[$column];
                unset($data[$column]);
            }
        }

        if(!isset($translableData['lang'])) {
            $translableData['lang'] = App::getLocale();
        }


        DB::beginTransaction();
        try {
            if(!$parentItem) {
                $parentItem = self::create($data);
            } else {
                $parentItem->update($data);
            }



            $relCol = Str::singular($parentItem->getTable()) . '_id';
            $translableData[$relCol] = $parentItem->id;

            //Check if we have entry for this language already
            //Update or create entry
            $checkData = [
                'lang'  =>  $translableData['lang'],
                $relCol =>  $parentItem->id
            ];

//            $transableInstance::updateOrCreate($checkData, $translableData);
            $parentItem->translations()->updateOrCreate($checkData, $translableData);
            // all good
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            $parentItem = false;
        }

        return $parentItem;
    }
}
