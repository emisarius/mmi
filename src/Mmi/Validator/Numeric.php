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
 * Walidator numeryczny
 *
 * @deprecated since 3.9.0 to be removed in 4.0.0
 */
class Numeric extends ValidatorAbstract
{

    /**
     * Treść wiadomości
     */
    const INVALID = 'validator.numeric.message';

    /**
     * Walidacja liczb
     * @param mixed $value wartość
     * @return boolean
     */
    public function isValid($value)
    {
        //błąd
        if (!is_numeric($value)) {
            return $this->_error(self::INVALID);
        }
        return true;
    }

}
