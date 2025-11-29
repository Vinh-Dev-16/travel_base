<?php

namespace Vinhdev\Travel\Contracts\DTO\SQL;
interface IndexPaginateDTOSQLInterface
{
    public function setLimit(int $limit): void;

    public function getLimit(): int;

    public function setPage(int $page): void;

    public function getPage(): int;

    public function setKeyWord(string $keyWord): void;

    public function getKeyWord(): string;
}