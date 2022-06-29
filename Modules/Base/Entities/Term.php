<?php

namespace Modules\Base\Entities;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Base\Traits\FilterCriteria;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Base\Traits\HasTranslation;

class Term extends Model
{
    use FilterCriteria,NodeTrait, HasTranslation;

    protected $table = 'terms';

    protected $fillable = ['slug', 'parent_id', 'extra', 'module_name'];

    public $timestamps = false;

    protected $casts = [
        'extra' =>  'array'
    ];

    protected $attributes = [
        'module_name' => 'Base'
    ];


    public function scopeBySlug($query, $slug) {
        return $query->where('slug',$slug);
    }

    public function scopeChildrenBySlug($query, $slug) {
        $parent = $query->bySlug($slug)->first();
        if($parent) {
            return $query->bySlug($slug)->first()->children();
        }
        return [];
    }
}
