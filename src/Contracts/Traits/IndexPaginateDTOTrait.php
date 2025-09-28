<?php

namespace Vinhdev\Travel\Contracts\Traits;

trait IndexPaginateDTOTrait
{
    protected int $limit;
    protected int $page;
    protected string $keyWord;

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setKeyWord(string $keyWord): void
    {
        $this->keyWord = $keyWord;
    }

    public function getKeyWord(): string
    {
        return $this->keyWord;
    }

    public function isKeyWordExist(): bool
    {
        return !empty($this->keyWord);
    }
}