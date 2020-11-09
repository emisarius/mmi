<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Db\Adapter;

class PdoSqlite extends PdoAbstract
{

    /**
     * Ustawia domyślne parametry dla importu (długie zapytania)
     */
    public function setDefaultImportParams(): self
    {
        //w sqlite nic nie robi
        return $this;
    }

    /**
     * Tworzy połączenie z bazą danych
     */
    public function connect(): self
    {
        //pdo do zapisu
        $this->_upstreamPdo = new \PDO(
            $this->_config->driver . ':' . $this->_config->host, null, null, [\PDO::ATTR_PERSISTENT => $this->_config->persistent]
        );
        //odczyt identycznie
        $this->_downstreamPdo = $this->_upstreamPdo;
        //połączono
        $this->_connected = true;
        //włączenie funkcjonalności kluczy obcych - domyślnie
        $this->query('PRAGMA foreign_keys = ON');
        return $this;
    }

    /**
     * Wstawianie wielu rekordów
     */
    public function insertAll(string $table, array $data = []): int
    {
        //brak natywnego wsparcia sqlite, wiele insertów dokonuje się uniami selectów
        $fields = '';
        $fieldsCompleted = false;
        $values = '';
        $bind = [];
        //iteracja po danych
        foreach ($data as $row) {
            //brak wiersza
            if (empty($row)) {
                continue;
            }
            $cur = '';
            //iteracja po danych w wierszu
            foreach ($row as $key => $value) {
                //tworzenie pól
                if (!$fieldsCompleted) {
                    $fields .= $this->prepareField($key) . ', ';
                }
                $cur .= '?, ';
                $bind[] = $value;
            }
            //tworzenie zapytania
            if (!$fieldsCompleted) {
                $values .= ' SELECT ' . rtrim($cur, ', ') . "\n";
            } else {
                $values .= ' UNION SELECT ' . rtrim($cur, ', ') . "\n";
            }
            $fieldsCompleted = true;
        }
        //wykonanie wstawienia
        return $this->query('INSERT INTO ' . $this->prepareTable($table) . ' (' . rtrim($fields, ', ') . ') ' . $values, $bind)->rowCount();
    }

    /**
     * Otacza nazwę pola odpowiednimi znacznikami
     */
    protected function prepareField(string $fieldName): string
    {
        //konwersja random
        if ($fieldName == 'RAND()') {
            return 'RANDOM()';
        }
        //dla sqlite "
        if (strpos($fieldName, '"') === false) {
            //"
            return '"' . str_replace('.', '"."', $fieldName) . '"';
        }
        return $fieldName;
    }

    /**
     * Otacza nazwę tabeli odpowiednimi znacznikami
     */
    protected function prepareTable(string $tableName): string
    {
        //dla sqlite jak pola
        return $this->prepareField($tableName);
    }

    /**
     * Zwraca informację o kolumnach tabeli
     */
    public function tableInfo($tableName, $schema = null): array
    {
        //schema nie jest używane w sqlite
        return $this->_associateTableMeta($this->fetchAll('PRAGMA table_info(' . $this->prepareTable($tableName) . ')'));
    }

    /**
     * Listuje tabele w schemacie bazy danych
     */
    public function tableList(string $schema = null): array
    {
        //pobranie listy tabel
        $list = $this->fetchAll('SELECT name FROM sqlite_master WHERE type=\'table\'');
        $tables = [];
        //itaracja po tabelach
        foreach ($list as $row) {
            $tables[] = $row['name'];
        }
        return $tables;
    }

    /**
     * Tworzy konstrukcję sprawdzającą null w silniku bazy danych
     */
    protected function prepareNullCheck(string $fieldName, bool $positive = true): string
    {
        //sprawdzanie czy null lub nie null
        return $positive ? ('(' . $fieldName . ' is null OR ' . $fieldName . ' = ' . $this->quote('') . ')') : ($fieldName . ' is not null');
    }

    /**
     * Tworzy konstrukcję sprawdzającą ILIKE, jeśli dostępna w silniku
     */
    protected function prepareLike(string $fieldName): string
    {
        //ilike jak like
        return $fieldName . ' LIKE';
    }

    /**
     * Konwertuje do tabeli asocjacyjnej meta dane tabel
     * @param array $meta meta data
     * @return array
     */
    private function _associateTableMeta(array $meta): array
    {
        $associativeMeta = [];
        //iteracja po metadanych
        foreach ($meta as $column) {
            //konwersja do wspólnego formatu mmi
            $associativeMeta[$column['name']] = [
                'dataType' => $column['type'],
                'maxLength' => null,
                'null' => ($column['notnull'] == 1) ? false : true,
                'default' => $column['dflt_value']
            ];
        }
        return $associativeMeta;
    }

}
