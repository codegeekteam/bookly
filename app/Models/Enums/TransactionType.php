<?php

namespace App\Models\Enums;

enum TransactionType: string
{
    case IN = 'in';
    case OUT = 'out';
}
