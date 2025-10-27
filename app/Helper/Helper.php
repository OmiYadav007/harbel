<?php

namespace App\Helper;

use stdClass;
use Symfony\Component\HttpFoundation\Response as HttpResponse;


class Helper
{
   
 public  static function getTranType($type)
    {
        $data = [
            'Add' => 'Add Money',
            'AddAmount' => 'Add Amount',
            'PANEL_COMMISSION' => 'Panel Refer Commission',
            'GAME_WIN' => 'Game Win',
            'GAME_JOIN' => 'Game Join',
            'GAME_CREATE' => 'Game Create',
            'PANEL_PURCHASE' => 'Panel Purchased',
            'GAME_CANCELLED' => 'Game Cancelled',
        ];

        return $data[$type] ?? $type;
    }
 public  static function getTranFlag($type)
    {
        $data = [
            'Add' => '+',
            'AddAmount' => '+',
            'PANEL_COMMISSION' => '+',
            'GAME_WIN' => '+',
            'GAME_JOIN' => '-',
            'GAME_CREATE' => '-',
            'PANEL_PURCHASE' => '-',
            'GAME_CANCELLED' => '+',
        ];

        return $data[$type] ?? $type;
    }
   
}
