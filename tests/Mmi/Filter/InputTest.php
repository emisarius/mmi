<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Test\Filter;

use Mmi\Filter\Input;

class InputTest extends \PHPUnit\Framework\TestCase
{

    public function testFilter()
    {
        foreach (['<script>xxx</script>', 'złoto coto<div><h1>', '</head xxx <script'] as $text) {
            $this->assertEquals(htmlspecialchars($text), (new Input)->filter($text));
        }
    }

}
