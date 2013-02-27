<?php

namespace MyUser\Controller;

use ZfcUser\Controller\UserController as BaseUserController;

class UserController extends BaseUserController
{
  public function indexAction()
  {
    $result =  parent::indexAction();
    return $result;
  }
}
