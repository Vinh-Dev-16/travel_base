<?php

namespace Vinhdev\Travel\Contracts\Traits;



use Vinhdev\Travel\Helpers\NullObject;

trait HasNullObjectTrait
{
    /**
     * Trả về NullObject khi dữ liệu không tồn tại.
     *
     * @param $relation
     * @return mixed
     */
    public function getRelationOrNullObject($relation): mixed
    {
        return $this->$relation ?: new NullObject();
    }

    public function getAttributeOrNullObject($attribute): mixed
    {
        return $this->$attribute ?? new NullObject();
    }
}