<?php

namespace Vinhdev\Travel\Contracts\DTO;

use MongoDB\BSON\ObjectId;

trait GetUserInformationDTOTrait
{
    private ObjectId $userId;

    private string $userName;

    public function getUserId(): ObjectId
    {
        return $this->userId;
    }

    public function setUserId(ObjectId $userId): void
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