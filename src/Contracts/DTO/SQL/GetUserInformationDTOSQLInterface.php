<?php

namespace Vinhdev\Travel\Contracts\DTO\SQL;

use MongoDB\BSON\ObjectId;

interface GetUserInformationDTOSQLInterface
{
    public function getUserId(): string;

    public function getUserName(): string;

    public function setUserId(string $id): void;

    public function setUserName(string $name): void;
}