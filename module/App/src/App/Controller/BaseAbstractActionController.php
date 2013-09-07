<?php

namespace App\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;

class BaseAbstractActionController extends AbstractActionController implements LoggerAwareInterface
{
    protected $logger;

    /*************Helper****************/
    public function isMobile($request)
    {
        $isMobile = false;
        $requestHeaders  = $request->getHeaders();
        if($requestHeaders->has('x-client-identifier'))
        {
            $xIdentifier = $requestHeaders->get('x-client-identifier')->getFieldValue();
            if($xIdentifier == 'Mobile')
            {
                $isMobile = true;
            }
        }
        return $isMobile;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
