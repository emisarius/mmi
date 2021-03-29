<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 *
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Form\Element;

/**
 * Element checkbox
 */
class Checkbox extends ElementAbstract
{

    //szablon pola
    const TEMPLATE_FIELD = 'mmi/form/element/checkbox';

    /**
     * Konstruktor ustawia kolejność i opcje
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setRenderingOrder(['fetchBegin', 'fetchField', 'fetchLabel', 'fetchDescription', 'fetchErrors', 'fetchEnd'])
            ->setLabelPostfix('')
            ->setValue(true);
    }

    /**
     * Ustawia zaznaczenie
     * @return \Mmi\Form\Element\Checkbox
     */
    public function setChecked($checked = true)
    {
        return $checked ? $this->setOption('checked', '') : $this->unsetOption('checked');
    }

    /**
     * Czy zaznaczone
     * @return \Mmi\Form\Element\Checkbox
     */
    public function isChecked()
    {
        return $this->issetOption('checked');
    }

}
