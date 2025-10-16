<?php

namespace Vinhdev\Travel\Contracts\Lib;

class RedisProvider implements RedisProviderContract
{
    /** @var array<int, RedisLibContract> */
    private array $dbToClient = [];

    public function forDb(int $db): RedisLibContract
    {
        if (!isset($this->dbToClient[$db])) {
            $this->dbToClient[$db] = new RedisLib($db);
        }
        return $this->dbToClient[$db];
    }
}


