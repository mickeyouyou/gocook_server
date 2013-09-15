<?php

/**
 * This file is part of the GoCook project.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace App\Lib;

final class CommonDef
{
    private function __construct() {}

    // app
    const APP_ID = 1;
    const APP_KEY = "DAB578EC-6C01-4180-939A-37E6BE8A81AF";
    const APP_IV = "117A5C0F";

    // m6 cmd
    const REGISTER_CMD = 1;
    const AUTH_CMD = 2;
    const SEARCH_CMD = 3;
    const ORDER_CMD = 4;
    const HIS_ORDERS_CMD = 5;

    // m6 server
    const M6SERVER = 'http://o.m6fresh.com/ws/app.ashx';

    const USER_COLLECT_COUNT = 0;
    const USER_DISH_COUNT = 1;
    const USER_RECIPE_COUNT = 2;
    const USER_FOLLOWING_COUNT = 3;
    const USER_FOLLOWED_COUNT = 4;
}
