<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BaseAbstractActionController extends AbstractActionController
{
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
}
