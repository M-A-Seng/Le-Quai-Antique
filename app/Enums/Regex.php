<?php

namespace App\Enums;

 /**
 * - Date = Y-m-d
 * - Time = H:i(:s)
 * - TimeStrict = H:i:s
 * - DateTime = Y-m-d H:i(:s)
 * - DateTimeTz = Y-m-d H:i:sP
 * - IsoAtom = Y-m-d\TH:i:s(.u)P
 * - PhoneN = international/local phone number
 * - Password = 1 lowerC, 1 upperC, 1 number, 1 specialChar, >8 char
 **/
enum Regex: string
{
    case Date = '/^\d{4}-\d{2}-\d{2}$/';
    case Time = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
    case TimeStrict = '/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/';
    case DateTime = '/^\d{4}-\d{2}-\d{2} ([01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
    case DateTimeTz = '/^\d{4}-\d{2}-\d{2} ([01]\d|2[0-3]):[0-5]\d:[0-5]\d[+-](?:0\d|1\d|2[0-3]):[0-5]\d$/';
    case IsoAtom = '/^\d{4}-\d{2}-\d{2}T([01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:\.\d+)?(?:Z|[+-](?:0\d|1\d|2[0-3]):[0-5]\d)$/';
    case PhoneN = '/^(\+?[1-9]{1}[0-9\s\-]{6,15}|0[0-9\s\-]{6,15})$/';
    case Password = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/';
}