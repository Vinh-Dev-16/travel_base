<?php

namespace Vinhdev\Travel\Contracts\Lib;

interface RedisProviderContract
{
    /**
     * Trả về một instance RedisLibContract tương ứng với database mong muốn.
     */
    public function forDb(int $db): RedisLibContract;
}


