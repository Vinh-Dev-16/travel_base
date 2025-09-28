<?php

namespace Vinhdev\Travel\Contracts\Lib;

use Illuminate\Redis\Connections\Connection as RedisConnection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class RedisLib
{
    protected RedisConnection $redis;
    protected int $currentDb = 0;
    
    // Lưu trữ các connection riêng biệt cho từng database
    private static array $connections = [];

    public function __construct($db = 0)
    {
        $connectionName = $this->getConnectionName($db);
        
        // Sử dụng connection pool để tránh tạo quá nhiều connections
        if (!isset(self::$connections[$db])) {
            self::$connections[$db] = Redis::connection($connectionName);
        }
        
        $this->redis = self::$connections[$db];
        $this->currentDb = $db;
    }

    /**
     * Lấy tên connection dựa trên database number
     */
    private function getConnectionName($db): string
    {
        return match($db) {
            1 => 'cache',         // DB_CACHE
            2 => 'queue',         // DB_OTP và QUEUE  
            5 => 'authentication', // DB_AUTHENTICATION
            10 => 'background',    // DB_BACKGROUND
            default => 'default'   // Các database khác
        };
    }

    public function set($key, $value, $expiration = null)
    {
        if ($expiration) {
            return $this->redis->setex($key, $expiration, $value);
        }
        return $this->redis->set($key, $value);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function delete($key)
    {
        return $this->redis->del($key);
    }

    public function enqueue($queue, $value)
    {
        return $this->redis->rpush($queue, $value);
    }

    public function dequeue($queue)
    {
        return $this->redis->lpop($queue);
    }

    public function addToListStart(string $queue, $value)
    {
        return $this->redis->lpush($queue, $value);
    }

    public function limitListSize(string $queue, int $maxSize): void
    {
        $this->redis->ltrim($queue, 0, $maxSize - 1);
    }
    public function getList(string $queue, int $start = 0, int $end = -1)
    {
        return $this->redis->lrange($queue, $start, $end);
    }

    public function incrementSortedSetScore(string $key, string $member, float $increment = 1.0): void
    {
        $this->redis->zincrby($key, $increment, $member);
    }

    public function getListItemAt(string $queue, int $index)
    {
        return $this->redis->lindex($queue, $index);
    }


    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    public function pushToBackground(string $key, string $value, string $dataType): void
    {
       $data = [
         'id' => Str::uuid()->toString(),
         'dataType' => $dataType,
         'data' => $value,
       ];

         $this->redis->rpush($key, json_encode($data));
    }

    public function keys(string $key)
    {
        $cursor = 0;
        $keys = [];

        do {
            $result = $this->redis->scan($cursor, 'MATCH', $key);
            $cursor = $result[0];
            $keys = array_merge($keys, $result[1]);
        } while ($cursor != 0);

        return $keys;
    }
}