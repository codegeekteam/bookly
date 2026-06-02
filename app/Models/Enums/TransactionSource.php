<?php  

namespace App\Models\Enums;

enum TransactionSource: string
{
    //case DEPOSIT = 'deposit';
    case REFUND = 'refund';
    case GOODWILL = 'goodwill';
    case PURCHASE = 'purchase';
    case WITHDRAWAL = 'withdrawal';
    case BOOKING = 'booking';
}