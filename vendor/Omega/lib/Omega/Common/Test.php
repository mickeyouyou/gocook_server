<?php

/**
 * This file is part of the Omega package.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */
 
 namespace Omega\Common;
 
 class Test
{
  protected static $test = 'This is Test!';
  public static function test2()
  {
    echo self::$test;
  }
}