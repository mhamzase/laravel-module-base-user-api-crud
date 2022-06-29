<?php

namespace Modules\Base\Entities;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Base\Traits\FilterCriteria;
use Modules\Base\Traits\HasTranslation;
use Waavi\Translation\Models\Translation;

class TranslationCustom extends Translation
{
    use FilterCriteria;
}
