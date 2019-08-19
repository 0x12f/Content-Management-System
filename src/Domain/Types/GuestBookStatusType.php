<?php

namespace Domain\Types;

use Application\Types\EnumType;

class GuestBookStatusType extends EnumType
{
    const NAME = 'GuestBookStatusType';

    const STATUS_WORK = 'work',
        STATUS_MODERATE = 'delete';

    const LIST          = [
        self::STATUS_WORK => 'Активный',
        self::STATUS_MODERATE => 'Модерируется',
    ];
}
