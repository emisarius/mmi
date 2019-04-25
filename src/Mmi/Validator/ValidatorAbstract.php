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
 * Abstrakcyjna klasa walidatora
 * 
 * @method self setMessage($message) ustawia własną wiadomość walidatora
 * @method string getMessage() pobiera wiadomość
 *
 * @deprecated since 3.9.0 to be removed in 4.0.0
 */
abstract class ValidatorAbstract extends \Mmi\OptionObject
{

    /**
     * Wiadomość
     * @var string
     */
    protected $_error;

    /**
     * Ustawia opcje (domyślnie wiadomość)
     * @param array $options
     * @return self
     */
    public function setOptions(array $options = [], $reset = false)
    {
        return $this->setMessage(current($options));
    }

    /**
     * Abstrakcyjna funkcja sprawdzająca poprawność wartości
     * @param mixed $value wartość
     */
    public abstract function isValid($value);

    /**
     * Pobiera błąd
     * @return string
     */
    public final function getError()
    {
        return $this->_error;
    }

    /**
     * Ustawia błąd
     * @param string $message
     * @retur boolean false
     */
    protected final function _error($message)
    {
        $this->_error = $message;
        return false;
    }

}
