<?php

namespace Vinhdev\Travel\Contracts\DTO;

use MongoDB\BSON\ObjectId;

interface GetUserInformationDTOInterface
{
    public function getUserId(): ObjectId;

    public function getUserName(): string;

    public function setUserId(ObjectId $id): void;

    public function setUserName(string $name): void;
}