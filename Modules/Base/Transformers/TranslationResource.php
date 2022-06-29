<?php

namespace Modules\Base\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray( $request ) {
        return [
            'id'      => $this->id,
            'phrase'      => $this->text,
            'slug'        => $this->item,
            'lang'        => $this->locale,
            'module_name' => $this->group,
            'unstable'    => $this->unstable,
            'locked'      => $this->locked,
        ];
    }
}
