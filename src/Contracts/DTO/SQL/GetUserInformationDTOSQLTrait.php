<?php

namespace Vinhdev\Travel\Contracts\DTO\SQL;

use MongoDB\BSON\string;

trait GetUserInformationDTOSQLTrait
{
    private string $userId;

    private string $userName;

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

}