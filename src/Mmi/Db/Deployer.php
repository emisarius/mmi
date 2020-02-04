<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Db;

use \Mmi\Orm;

/**
 * Klasa wdrożeń incrementali bazy danych
 *
 * @deprecated since 3.8 to be removed in 4.0
 */
class Deployer
{

    /**
     * Metoda uruchamiająca
     * @throws DbException
     */
    public function deploy()
    {
        //wyłączenie bufora lokalnego
        \App\Registry::$config->localCache->active = false;
        //wyłączenie bufora aplikacji
        \App\Registry::$config->cache->active = false;
        //inicjalizacja tablicy inkrementali
        $incrementals = [];
        //iteracja po modułach aplikacji
        foreach (\Mmi\Mvc\StructureParser::getModules() as $module) {
            //moduł nie zawiera incrementali
            if (!file_exists($module . '/Resource/incremental/' . \App\Registry::$config->db->driver)) {
                continue;
            }
            //iteracja po incrementalach
            foreach (glob($module . '/Resource/incremental/' . \App\Registry::$config->db->driver . '/*.sql') as $file) {
                //dodawanie incrementala do tablicy
                $incrementals[basename($file)] = $file;
            }
        }
        //sortowanie plików po nazwach
        ksort($incrementals);
        //przywracanie incrementali
        foreach ($incrementals as $incremental) {
            //importowanie incrementala
            $this->_importIncremental($incremental);
            //flush wyniku na ekran
            flush();
        }
    }

    /**
     * Importuje pojedynczy plik
     * @param string $file
     * @throws \Mmi\App\KernelException
     */
    protected function _importIncremental($file)
    {
        //nazwa pliku
        $baseFileName = basename($file);
        //hash pliku
        $md5file = md5_file($file);
        //ustawianie domyślnych parametrów importu
        \App\Registry::$db->setDefaultImportParams();
        //pobranie rekordu
        try {
            $dc = (new Orm\ChangelogQuery)->whereFilename()->equals(basename($file))->findFirst();
        } catch (\Exception $e) {
            echo 'INITIAL IMPORT.' . "\n";
            $dc = null;
        }
        //restore istnieje md5 zgodne
        if ($dc && $dc->md5 == $md5file) {
            echo 'INCREMENTAL PRESENT: ' . $baseFileName . "\n";
            return;
        }
        //restore istnieje md5 niezgodne - plik się zmienił - przerwanie importu
        if ($dc) {
            throw new \Mmi\App\KernelException('INVALID MD5: ' . $baseFileName . ' --- VALID: ' . $md5file . ' --- IMPORT TERMINATED!\n');
        }
        //informacja na ekran przed importem aby bylo wiadomo który
        echo 'RESTORE INCREMENTAL: ' . $baseFileName . "\n";
        //import danych
        $this->_importSql($file);
        //resetowanie struktur tabeli
        \Mmi\Orm\DbConnector::resetTableStructures();
        //brak restore - zakłada nowy rekord
        $newDc = new Orm\ChangelogRecord;
        //zapis informacji o incrementalu
        $newDc->filename = $baseFileName;
        //wstawienie md5
        $newDc->md5 = $md5file;
        //zapis rekordu
        $newDc->save();
    }

    /**
     * Import pliku sql
     * @param string $fileName nazwa pliku
     */
    protected function _importSql($fileName)
    {
        //rozbicie zapytań po średniku i końcu linii
        foreach (explode(';' . PHP_EOL, file_get_contents($fileName)) as $query) {
            //wykonanie zapytania
            $this->_performQuery($query);
        }
    }

    /**
     * Wykonanie pojedynczego zapytania
     * @param string $query
     */
    protected function _performQuery($query)
    {
        //brak query
        if (!trim($query)) {
            return;
        }
        //start transakcji
        \App\Registry::$db->beginTransaction();
        //quera jeśli błędna rollback i die, jeśli poprawna commit
        try {
            //wykonanie zapytania
            \App\Registry::$db->query($query);
            //commit
            \App\Registry::$db->commit();
        } catch (\Mmi\Db\DbException $e) {
            //rollback
            \App\Registry::$db->rollBack();
            throw $e;
        }
    }

}
