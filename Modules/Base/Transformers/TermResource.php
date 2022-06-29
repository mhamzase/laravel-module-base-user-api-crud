<?php

namespace Modules\Base\Transformers;

//use Illuminate\Http\Resources\Json\Resource;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\FormGenerator\Repositories\ProductRepository;

class TermResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $term = [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'parent_id' => $this->parent_id,
            'extra' => $this->extra,
            'module_name' => $this->module_name,
            'parent' =>  $this->whenLoaded('parent'),
            'children' => TermResource::collection($this->whenLoaded('children')),
//            'bank_is_required' => true,
//            'options'   => [
//                [
//                    'id'    =>  1,
//                    'description'   =>  'alsdkjfaldskfj',
//                    'order'   =>  5,
//                    'bank_answer_id'    =>  2343,
//                    'bank_answer_type'    =>  234,
//                    'bank_is_required'    =>  1,
//                ],
//                [
//                    'id'    =>  2,
//                    'description'   =>  'aaaaaaa',
//                    'order'   =>  50,
//                    'bank_answer_id'    =>  2222,
//                    'bank_answer_type'    =>  22,
//                    'bank_is_required'    =>  0,
//                ],
//                [
//                    'id'    =>  3,
//                    'description'   =>  'cccccccccc',
//                    'order'   =>  5,
//                    'bank_answer_id'    =>  3333,
//                    'bank_answer_type'    =>  33,
//                    'bank_is_required'    =>  1,
//                ]
//            ]
//            'parent' => !empty($this->parent)? new TermResource($this->parent):[],
//            'children' => $this->when(!$this->whenLoaded('parent'), TermResource::collection($this->whenLoaded('children')))
        ];


        if(isset($this->depth))
        {
            $term['depth'] = $this->depth;
        }

        return $term;

    }
}
