<?php

namespace Modules\Base\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Base\Traits\FilterCriteria;

class Phrase extends Model
{
    use FilterCriteria;
    protected $fillable = ['phrase', 'slug', 'module_name', 'lang', 'type_id', 'category_id'];

    public $timestamps = false;
}
