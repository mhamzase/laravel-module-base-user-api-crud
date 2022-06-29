<?php


namespace Modules\User\Extend\Entities;


use Modules\Entity\Entities\Address;

class Person extends ProxyPerson
{
    /**
     * @return mixed
     */
    function address()
    {
        return $this->morphOne(Address::class, 'model');
    }
}
