<?php

namespace Vinhdev\Travel\Helpers;

class NullObject implements \JsonSerializable
{
    public function __get($property)
    {
        return '';
    }
    /**
     * Trả về chuỗi rỗng khi gọi bất kỳ phương thức nào.
     */
    public function __call($method, $parameters)
    {
        return '';
    }

    public function __toString(): string
    {
        return '';
    }

    public function jsonSerialize(): mixed
    {
        return '';
    }
}