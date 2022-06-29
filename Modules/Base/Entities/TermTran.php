<?php

namespace Modules\Base\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Base\Traits\FilterCriteria;

class TermTran extends Model
{
    use FilterCriteria;
    protected $fillable = ['title', 'lang'];

    public $timestamps = false;
}
