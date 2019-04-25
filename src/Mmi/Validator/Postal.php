<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Validator;

/**
 * Walidator kodu pocztowego
 *
 * @deprecated since 3.9.0 to be removed in 4.0.0
 */
class Postal extends ValidatorAbstract
{

    /**
     * Komunikat błędnego kodu
     */
    const INVALID = 'validator.postal.message';

    /**
     * Sprawdza czy tekst jest e-mailem
     * @param string $value
     * @return boolean
     */
    public function isValid($value)
    {
        //błąd
        if (preg_match('/^[0-9]{2}-[0-9]{3}$/', $value)) {
            return true;
        }
        return $this->_error(self::INVALID);
    }

}
