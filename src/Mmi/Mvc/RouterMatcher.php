<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 *
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Mvc;

/**
 * Klasa dopasowująca routy
 */
class RouterMatcher
{
    //zmienne tymczasowe
    protected $_tmpMatches;
    protected $_tmpKey;
    protected $_tmpDefault;

    /**
     * Stosuje istniejące trasy dla danego url
     * @param \Mmi\Mvc\RouterConfigRoute $route
     * @param string $url URL
     * @return array
     */
    public function tryRouteForUrl(\Mmi\Mvc\RouterConfigRoute $route, $url)
    {
        $params = [];
        $matches = [];
        $matched = false;
        //sprawdzenie statyczne
        if ($route->pattern == $url) {
            return [
                'matched' => true,
                'params' => array_merge($route->default, $route->replace),
                'url' => trim(substr($url, strlen($route->pattern)), ' /')
            ];
        }
        //dopasowanie wyrażeniem regularnym
        if ($this->_isPatternRegular($route->pattern) && preg_match($route->pattern, $url, $matches)) {
            $this->_tmpMatches = $matches;
            $this->_tmpDefault = $route->default;
            $matched = true;
            foreach ($route->replace as $key => $value) {
                $this->_tmpKey = $key;
                $params[$key] = preg_replace_callback('/\$([0-9]+)/', ['\Mmi\Mvc\RouterMatcher', '_routeMatch'], $value);
                $params[$key] = preg_replace('/\|[a-z]+/', '', $params[$key]);
            }
            $url = trim(substr($url, strlen($matches[0])), ' /');
        }
        return [
            'matched' => $matched,
            'params' => $params,
            'url' => $url
        ];
    }

    /**
     * Stosuje trasę dla tablicy parametrów (np. z żądania)
     * @param \Mmi\Mvc\RouterConfigRoute $route
     * @param array $params parametry
     * @return array
     */
    public function tryRouteForParams(\Mmi\Mvc\RouterConfigRoute $route, array $params)
    {
        $matches = [];
        $matched = [];
        $applied = true;
        $url = '';
        $replace = array_merge($route->default, $route->replace);
        //ustawianie domyślnego kontrolera i akcji, jeśli brak w parametrach
        $params['controller'] = isset($params['controller']) ? $params['controller'] : 'index';
        $params['action'] = isset($params['action']) ? $params['action'] : 'index';
        //ustawianie domyślnego kontrolera i akcji, jeśli brak w roucie
        $replace['controller'] = isset($replace['controller']) ? $replace['controller'] : 'index';
        $replace['action'] = isset($replace['action']) ? $replace['action'] : 'index';
        //iteracja po roucie
        foreach ($replace as $key => $value) {
            if (is_array($value) && isset($params[$key]) && $value == $params[$key]) {
                $matched[$key] = true;
                continue;
            } elseif (is_array($value)) {
                $applied = false;
                $matched = [];
                break;
            }
            if ((preg_match('/\$([0-9]+)(\|[a-z]+)?/', $value, $mt) && isset($params[$key]))) {
                if (count($mt) > 2) {
                    throw new \Exception('Filters no longer supported');
                }
                $matches[$value] = $params[$key];
            } elseif (!isset($params[$key]) || $params[$key] != $value) {
                $applied = false;
                $matched = [];
                break;
            }
            $matched[$key] = true;
        }
        if ($applied) {
            $pattern = str_replace(['\\', '?'], '', trim($route->pattern, '/^$'));
            $url = preg_replace('/(\(.[^\)]+\))/', '(#)', $pattern);
            foreach ($matches as $match) {
                if (is_array($match)) {
                    $match = trim(implode(';', $match), ';');
                }
                $url = preg_replace('/\(\#\)/', $match, $url, 1);
            }
        }
        return [
            'applied' => $applied,
            'matched' => $matched,
            'url' => $url
        ];
    }

    /**
     * Sprawdzenie czy pattern to wyrażenie regularne
     * @param string $pattern pattern
     * @return bool
     */
    protected function _isPatternRegular($pattern)
    {
        return substr($pattern, 0, 1) == '/' && (substr($pattern, -1) == '/' || substr($pattern, -2) == '/i');
    }

    /**
     * Callback dla zmieniania rout
     * @param array $matches dopasowania
     * @return mixed
     * @throws \Mmi\Mvc\MvcException
     */
    protected function _routeMatch($matches)
    {
        if (isset($this->_tmpMatches[$matches[1]])) {
            return $this->_tmpMatches[$matches[1]];
        }
        if (isset($this->_tmpDefault[$this->_tmpKey])) {
            return $this->_tmpDefault[$this->_tmpKey];
        }
        throw new \Mmi\Mvc\MvcException('Router failed due to invalid route definition - no default param for key: ' . $this->_tmpKey);
    }
}
