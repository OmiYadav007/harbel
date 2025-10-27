<?php

namespace App\Helper;

use stdClass;
use Symfony\Component\HttpFoundation\Response as HttpResponse;


class Pagination
{
   
// if (!function_exists('getDataArray')) {
 public  static function getDataArray()
    {
        return [
            '10' => '10',
            '20' => '20',
            '30' => '30',
            '40' => '40',
        ];
    }
// }
   
}
