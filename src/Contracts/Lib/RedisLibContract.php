<?php

namespace Vinhdev\Travel\Contracts\Lib;

interface RedisLibContract
{
    public function set(string $key, $value, int $ttl = 3600): bool;

    public function get(string $key);

    public function delete(string $key): bool;

    public function exists(string $key): bool;
    public function enqueue(string $queue, $value): int;
    public function dequeue(string $queue);
    public function addToListStart(string $queue, $value): int;
    public function limitListSize(string $queue, int $maxSize): void;
    public function getList(string $queue, int $start = 0, int $end = -1);
    public function incrementSortedSetScore(string $key, string $member, float $increment = 1.0): void;
    public function getListItemAt(string $queue, int $index);
    public function pushToBackground(string $key, string $value, string $dataType): void;
    public function keys(string $key);

}