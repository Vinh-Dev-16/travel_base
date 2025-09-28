<?php

namespace Vinhdev\Travel\Contracts\Enums;

enum SoftDelete : int
{
    case ACTIVE = 0;
    case DELETED = 1;

}