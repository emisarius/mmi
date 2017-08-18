<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi;

/**
 * Kontroler powitalny
 */
class IndexController extends Mvc\Controller
{

    /**
     * Akcja główna
     */
    public function indexAction()
    {
        
    }

    /**
     * Akcja błędu
     */
    public function errorAction()
    {
        //pobranie response
        $this->getResponse()
            //404
            ->setCodeNotFound();
    }

}
