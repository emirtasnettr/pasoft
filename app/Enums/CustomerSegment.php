<?php

namespace App\Enums;

enum CustomerSegment: string
{
    case Vip = 'vip';
    case Active = 'active';
    case Passive = 'passive';
    case Potential = 'potential';
}
