<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Mvc;

class Router
{

    /**
     * Konfiguracja
     * @var \Mmi\Mvc\RouterConfig
     */
    private $_config;

    /**
     * Domyślny język
     * @var string
     */
    private $_defaultLanguage;

    /**
     * Konstruktor routera
     * @param \Mmi\Mvc\RouterConfig $config
     * @param string $defaultLanguage domyślny język
     */
    public function __construct(\Mmi\Mvc\RouterConfig $config, $defaultLanguage = null)
    {
        $this->_config = $config;
        $this->_defaultLanguage = $defaultLanguage;
    }

    /**
     * Pobiera konfigurację routera
     * @return \Mmi\Mvc\RouterConfig
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Pobiera rout
     * @return array
     */
    public function getRoutes()
    {
        //zwrot zarejestrowanych rout
        return $this->_config->getRoutes();
    }

    /**
     * Dekoduje URL na parametry żądania zgodnie z wczytanymi trasami
     * @param string $url URL
     * @return array
     */
    public function decodeUrl($url)
    {
        //parsowanie url'a
        $parsedUrl = parse_url($url);
        //inicjalizacja pustych parametrów
        $params = [];
        //parsowanie query string (GET)
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $params);
        }
        //domyślne parametry
        $params['controller'] = isset($params['controller']) ? $params['controller'] : 'index';
        $params['action'] = isset($params['action']) ? $params['action'] : 'index';
        //jeśli aplikacja jest językowa
        if ($this->_defaultLanguage) {
            $params['lang'] = isset($params['lang']) ? $params['lang'] : $this->_defaultLanguage;
        }
        //jeśli nieustawiony moduł, url nie jest analizowany
        if (isset($params['module'])) {
            return $params;
        }
        //domyślny moduł
        $params['module'] = isset($params['module']) ? $params['module'] : 'mmi';
        //filtrowanie URL
        $filteredUrl = html_entity_decode(trim($parsedUrl['path'], '/ '), ENT_HTML401 | ENT_HTML5 | ENT_QUOTES, 'UTF-8');
        //próba aplikacji rout
        foreach ($this->getRoutes() as $route) {
            /* @var $route \Mmi\Mvc\RouterConfigRoute */
            $result = (new RouterMatcher)->tryRouteForUrl($route, $filteredUrl);
            //dopasowano routę
            if ($result['matched']) {
                //łączenie parametrów
                $params = array_merge($params, $result['params']);
                break;
            }
        }
        //zwrot parametrów
        return $params;
    }

    /**
     * Koduje parametry na URL zgodnie z wczytanymi trasami
     * @param array $params parametry
     * @return string
     */
    public function encodeUrl(array $params = [])
    {
        //pusty url
        $url = '';
        //aplikacja rout
        foreach ($this->getRoutes() as $route) {
            /* @var $route \Mmi\Mvc\RouterConfigRoute */
            $result = (new RouterMatcher)->tryRouteForParams($route, array_merge($route->default, $params));
            //dopasowano routę
            if ($result['applied']) {
                $url = '/' . $result['url'];
                $matched = $result['matched'];
                break;
            }
        }
        //czyszczenie dopasowanych z routy
        foreach (isset($matched) ? $matched : [] as $match => $value) {
            unset($params[$match]);
        }
        //usuwanie modułu jeśli mmi
        if (isset($params['module']) && $params['module'] == 'mmi') {
            unset($params['module']);
        }
        //usuwanie kontrolera jeśli index
        if (isset($params['controller']) && $params['controller'] == 'index') {
            unset($params['controller']);
        }
        //usuwanie akcji jeśli index
        if (isset($params['action']) && $params['action'] == 'index') {
            unset($params['action']);
        }
        //jeśli puste parametry
        if (empty($params)) {
            return $url;
        }
        //budowanie zapytania
        return $url . '/?' . http_build_query($params);
    }

}
