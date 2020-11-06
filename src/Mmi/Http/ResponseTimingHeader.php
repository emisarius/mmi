<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 *
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2019 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Http;

use Mmi\App\AppProfilerInterface;

/**
 * Klasa nagłówka odpowiedzi
 */
class ResponseTimingHeader
{

    const HEADER_NAME = 'Server-Timing';
    /**
     * @var AppProfilerInterface
     */
    private $profiler;

    public function __construct(AppProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * Zwrot nagłówka Server-Timing
     * @return ResponseHeader
     */
    public function getTimingHeader()
    {
        $eventGroups = [];
        //grupowanie zdażeń
        foreach ($this->profiler->get() as $event) {
            $groupName = str_replace(['Mmi'], '', substr($event['name'], 0, strpos($event['name'], ':')));
            $eventGroups[$groupName] = isset($eventGroups[$groupName]) ? $eventGroups[$groupName] + $event['elapsed'] : $event['elapsed'];
        }
        $headerValue = '';
        //budowanie wartości nagłówka Server-Timing
        foreach ($eventGroups as $groupName => $elapsed) {
            $headerValue .= str_replace('\\', '', $groupName) . ';dur=' . round(1000 * $elapsed, 2) . ',';
        }
        return (new ResponseHeader)
            ->setName(self::HEADER_NAME)
            ->setValue('App;dur=' . 1000 * $this->profiler->elapsed() . ',' . $headerValue);
    }
}
