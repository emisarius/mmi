<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 *
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Http\ResponseDebugger;

use Mmi\App\App;
use Mmi\App\AppProfilerInterface;
use Mmi\Db\DbInterface;
use Mmi\Http\RequestServer;

/**
 * Klasa danych częściowych panelu deweloperskiego
 */
class Part
{
    /**
     * Zmienne środowiskowe
     * @return string
     */
    public static function getServerHtml(RequestServer $requestServer)
    {
        return '<p style="margin: 0; padding: 0;">Connection: <b>' . $requestServer->serverAddress . ':' . $requestServer->serverPort . '</b> <---> <b>' . $requestServer->remoteAddress . ':' . $requestServer->remotePort . '</b></p>' .
            '<p style="margin: 0; padding: 0;">Browser: <b>' . substr($requestServer->httpUserAgent, 0, 93) . '</b></p>' .
            '<p style="margin: 0; padding: 0;">PHP: <b>' . phpversion() . ' (' . php_sapi_name() . ', ' . php_uname('s') . ' ' . php_uname('m') . ': ' . php_uname('n') . ')</b></p>' .
            '<p style="margin: 0; padding: 0;">Path: <b>' . $requestServer->scriptFileName . '</b></p>';
    }

    /**
     * Konfiguracja PHP
     * @return string
     */
    public static function getConfigHtml()
    {
        return '<p style="margin: 0; padding: 0;">Include path: <b>' . ini_get('include_path') . '</b></p>' .
            '<p style="margin: 0; padding: 0;">Memory limit: <b>' . ini_get('memory_limit') . '</b></p>' .
            '<p style="margin: 0; padding: 0;">Uploads allowed: <b>' . ((ini_get('file_uploads') == 1) ? 'yes' : 'no') . '</b></p>' .
            '<p style="margin: 0; padding: 0;">Upload maximal size: <b>' . ini_get('upload_max_filesize') . '</b></p>' .
            '<p style="margin: 0; padding: 0;">Upload directory: <b>' . ((ini_get('upload_tmp_dir')) ? ini_get('upload_tmp_dir') : 'system default') . '</b></p>' .
            '<p style="margin: 0; padding: 0;">POST maximal size: <b>' . ini_get('post_max_size') . '</b></p>';
    }

    /**
     * Profiler
     * @return string
     */
    public static function getProfilerHtml(AppProfilerInterface $profiler)
    {
        $percentSum = 0;
        $html = '';
        //pętla po profilerze
        foreach ($profiler->get() as $event) {
            $percentSum += $event['percent'];
            $html .= '<div style="color: #' . self::_colorifyPercent($event['percent']) . '"><div style="float: left; width: 450px; margin-right: 20px; font-size: 10px; word-wrap: break-word; white-space: pre-wrap;">' . $event['name'] . '</div><div style="float: left; width: 60px;"><b>' . round($event['elapsed'], 4) . 's</b></div><div style="float: left; width: 60px;"><b>' . round($event['percent'], 2) . '%</b></div><div style="float: left;"><b>' . round($percentSum, 2) . '%</b></div></div><div style="clear: both"></div>';
        }
        return $html;
    }

    /**
     * Profiler DB
     * @return string
     */
    public static function getDbProfilerHtml()
    {
        $percentSum = 0;
        $html = '';
        $profilerData = App::$di->get(DbInterface::class) && App::$di->get(DbInterface::class)->getProfiler() ? App::$di->get(DbInterface::class)->getProfiler()->get() : [];
        //brak zapytań
        if (!count($profilerData)) {
            return 'No SQL queries';
        }
        //pętla po profilerze
        foreach ($profilerData as $index => $event) {
            $percentSum += $event['percent'];
            $html .= '<div style="color: #' . self::_colorifyPercent($event['percent']) . '"><div style="float: left; width: 450px; margin-right: 20px; font-size: 10px; word-wrap: break-word; white-space: pre-wrap;">' . ($index + 1) . '. '. $event['sql'] . '</div><div style="float: left; width: 60px;"><b>' . round($event['elapsed'], 4) . 's</b></div><div style="float: left; width: 60px;"><b>' . round($event['percent'], 2) . '%</b></div><div style="float: left;"><b>' . round($percentSum, 2) . '%</b></div></div><div style="clear: both"></div>';
        }
        return $html;
    }

    /**
     * Kolorowanie wartości procentowej (0-100) w odcieniach czerwieni
     * @param integer $percent
     * @return string hex koloru
     */
    protected static function _colorifyPercent($percent)
    {
        $boost = round($percent) * 15;
        return dechex(($boost > 255) ? 255 : $boost) . '2222';
    }
}
