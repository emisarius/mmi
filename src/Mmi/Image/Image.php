<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Image;

use Mmi\App\FrontController;

/**
 * Klasa obróbki obrazów
 */
class Image
{

    //minimalna długość binariów
    const BINARY_MIN_LENGTH = 1024;

    /**
     * Konwertuje string, lub binaria do zasobu GD
     * @param mixed $input
     * @return resource
     */
    public static function inputToResource($input)
    {
        //jeśli resource zwrot
        if (gettype($input) == 'resource') {
            return $input;
        }
        //jeśli krótki content zakłada że to ścieżka pliku
        return imagecreatefromstring((strlen($input) < self::BINARY_MIN_LENGTH) ? file_get_contents($input) : $input);
    }

    /**
     * Skaluje i przycina obrazek tak aby pasował do podanych wymiarów, zachowuje proporcje
     * @param mixed $input wejście
     * @param int $x wysokość do której chcemy przeskalować obrazek
     * @param int $y szerokość do której chcemy przeskalować obrazek
     * @return resource obrazek
     */
    public static function scaleCrop($input, $x, $y)
    {
        //wczytanie zasobu
        $resource = self::inputToResource($input);
        //badanie rozmiaru obrazu
        $width = imagesx($resource);
        $height = imagesy($resource);
        //obliczanie skali
        $scale = max($y / $height, $x / $width);
        //obliczanie zeskalowanych wymiarów
        $sx = ceil($width * $scale);
        $sy = ceil($height * $scale);
        //cropowanie zeskalowanego obrazu
        return self::crop(self::scale($resource, $sx, $sy), abs($sx - $x) / 2, abs($sy - $y) / 2, $x, $y);
    }

    /**
     * Skaluje obrazek o podany procent zachowując proporcje
     * @param mixed $input wejście
     * @param int $percent procent o jaki ma być zmieniony rozmiar obrazka
     * @return resource obrazek
     */
    public static function scaleProportional($input, $percent)
    {
        //brak zasobu
        $resource = self::inputToResource($input);
        //badanie rozmiarów obrazu
        $width = imagesx($resource);
        $height = imagesy($resource);
        //obliczanie rozmiarów po skalowaniu
        $sx = round($width * $percent / 100);
        $sy = round($height * $percent / 100);
        //tworzenie tymczasowego zasobu
        $tmp = imagecreatetruecolor($sx, $sy);
        //zapis przeźroczystości
        self::_saveAlpha($tmp);
        //skalowanie
        imagecopyresampled($tmp, $resource, 0, 0, 0, 0, $sx, $sy, $width, $height);
        return $tmp;
    }

    /**
     * Skaluje obrazek proporcjonalnie do podanych maksymalnych wymiarów
     * @param mixed $input wejście
     * @param int $maxDimX wysokość do której chcemy przeskalować obrazek
     * @param int $maxDimY szerokość do której chcemy przeskalować obrazek
     * @return resource obrazek
     */
    public static function scale($input, $maxDimX = null, $maxDimY = null)
    {
        //brak zasobu
        $resource = self::inputToResource($input);
        //brak skali
        if (!$maxDimX && !$maxDimY) {
            return $resource;
        }
        //badanie rozmiaru obrazu
        $width = imagesx($resource);
        $height = imagesy($resource);
        //obliczanie współczynnika X
        $ratioX = 100 * $maxDimX / $width;
        //obliczanie współczynnika Y
        $ratioY = 100 * $maxDimY / $height;
        //nie skalujemy w górę
        if ($ratioX > 100 || $ratioY > 100) {
            return $resource;
        }
        //nie podano długości
        if (null === $maxDimX) {
            //skalowanie do maksymalnego X-a
            return self::scaleProportional($resource, $ratioY);
        }
        //nie podano wysokości
        if (null === $maxDimY) {
            //skalowanie do maksymalnego X-a
            return self::scaleProportional($resource, $ratioX);
        }
        //skalowanie proporcjonalne ze współczynnikiem X
        if ($ratioX < $ratioY) {
            return self::scaleProportional($resource, $ratioX);
        }
        //skalowanie proporcjonalne ze współczynnikiem Y
        return self::scaleProportional($resource, $ratioY);
    }

    /**
     * Pomniejsza obrazek do danej szerokości zachowując proporcje, nie powiększa obrazka.
     * @param mixed $input wejście
     * @param int $maxDim szerokość do której chcemy pomniejszyć obrazek
     * @return resource obrazek
     */
    public static function scalex($input, $maxDim)
    {
        return self::scale($input, $maxDim);
    }

    /**
     * Pomniejsza obrazek do danej wysokości zachowując proporcje, nie powiększa obrazka.
     * @param mixed $input wejście
     * @param int $maxDim wysokość do której chcemy pomniejszyć obrazek
     * @return resource obrazek
     */
    public static function scaley($input, $maxDim)
    {
        return self::scale($input, null, $maxDim);
    }

    /**
     * Pomniejsza proporcjonalnie obrazek by zmieścił się w kwadracie maxDim x maxDim
     * @param mixed $input wejście
     * @param int $maxDim wysokość do której chcemy pomniejszyć obrazek
     * @return resource obrazek
     */
    public static function scaleMax($input, $maxDim)
    {
        //brak zasobu
        $resource = self::inputToResource($input);
        //badanie rozmiarów obrazu
        $width = imagesx($resource);
        $height = imagesy($resource);
        //skala już prawidłowa
        if ($width <= $maxDim && $height <= $maxDim) {
            return $resource;
        }
        //obrazek jest horyzontalny
        if ($width > $height) {
            return self::scalex($resource, $maxDim);
        }
        //obrazek jest wertykalny
        return self::scaley($resource, $maxDim);
    }

    /**
     * Wycina fragment obrazka z punktu x,y o danej długości i wysokości
     * @param mixed $input wejście
     * @param int $x współrzędna x
     * @param int $y współrzędna y
     * @param int $newWidth nowa Szerokość
     * @param int $newHeight nowa Wysokość
     * @return resource obrazek
     */
    public static function crop($input, $x, $y, $newWidth, $newHeight)
    {
        //brak zasobu
        $resource = self::inputToResource($input);
        //obliczanie nowej szerokości
        if (imagesx($resource) < $newWidth + $x) {
            $newWidth = imagesx($resource) - $x;
        }
        //obliczanie nowej wysokości
        if (imagesy($resource) < $newHeight + $y) {
            $newHeight = imagesy($resource) - $y;
        }
        //wycinanie obrazka
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        //zapis przeźroczystości
        self::_saveAlpha($destination);
        //przycinanie
        imagecopy($destination, $resource, 0, 0, $x, $y, $newWidth, $newHeight);
        //zwrot
        return $destination;
    }

    /**
     * Optymalizacja palety (256 kolorów z dithieringiem)
     */
    public static function optimizePalette($input)
    {
        //zamiana koloru w alfę
        imagecolortransparent($input, imagecolorexactalpha($input, 0, 0, 0, 127));
        $width = imagesx($input);
        $height = imagesy($input);
        //zczytanie prawie przeźroczystych pikseli (niektóre PNG i GIF mają wartości 126 zamiast 127)
        $transparentPixels = [];
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                //125+ = całkowita przeźroczystość
                if (125 < ((imagecolorat($input, $x, $y) & 0x7F000000) >> 24)) {
                    $transparentPixels[] = ['x' => $x, 'y' => $y];
                }
            }
        }
        //optymalizacja palety z dithieringiem
        imagetruecolortopalette($input, true, 256);
        //wczytanie przeźroczystości z palety
        $alphabg = imagecolorclosestalpha($input, 0, 0, 0, 127);
        //podmiana przeźroczystych pikseli
        foreach ($transparentPixels as $pixel) {
            imagesetpixel($input, $pixel['x'], $pixel['y'], $alphabg);
        }
    }


    /**
     * Zachowanie alphy
     * @param resource $imgRes
     */
    private static function _saveAlpha($imgRes)
    {
        imagealphablending($imgRes, false);
        imagesavealpha($imgRes, true);
    }

}
