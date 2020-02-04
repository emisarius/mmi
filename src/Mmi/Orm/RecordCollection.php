<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Orm;

/**
 * Klasa kolekcji rekordów
 *
 * @deprecated since 3.8 to be removed in 4.0
 */
class RecordCollection extends \ArrayObject
{

    /**
     * Kasuje całą kolekcję obiektów
     * @return integer ilość usuniętych obiektów
     */
    public function delete()
    {
        $i = 0;
        foreach ($this as $ar) {
            $ar->delete();
            $i++;
        }
        return $i;
    }

    /**
     * Zwraca kolekcję w postaci tablicy
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this as $key => $record) {
            $array[$key] = $record->toArray();
        }
        return $array;
    }

    /**
     * Zwraca kolekcję w postaci tablicy obiektów
     * @return \Mmi\Orm\RecordCollection
     */
    public function toObjectArray()
    {
        $array = [];
        foreach ($this as $key => $record) {
            $array[$key] = $record;
        }
        return $array;
    }

    /**
     * Zwraca kolekcję w postaci JSON
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Klonuje całą kolekcję
     */
    public function __clone()
    {
        foreach ($this as $key => $record) {
            $this[$key] = clone $record;
        }
    }

}
