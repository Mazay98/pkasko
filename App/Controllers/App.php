<?php

namespace App;

use App\Parser;


class App
{
    public static function run()
    {
        define("URI", "https://book24.ru");
        define("URI_CATEGORY", "https://book24.ru/catalog/programmirovanie-1361/");
//        define("URI", "https://book24.ru/catalog/nekhudozhestvennaya-literatura-1345/");
        (new Parser());
    }
}