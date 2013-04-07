<?php

/**
 * Recipe IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class CookController extends AbstractActionController {

    public function indexAction() {
        $result = new JsonModel(array(
            'some' => 'some value',
            'success'=>true,
        ));

        return $result;
    }

    public function getmainAction() {
        $tophot_img = '';
        $topnew_img = '';


    }


}
