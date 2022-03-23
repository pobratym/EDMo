<?php

/**
 *  HOW TO USE
 * ============
 *
 * ## Class interface
 *
 * $file_name = 'release_v0.1.0.sql';
 *
 * $queries_count = \WebXID\EDMo\DB\Migration::init(__DIR__ . '/../../../configs/db_release_dumps/')
 *         ->collectUpgrades($file_name)
 *         ->execute();
 *
 *
 * ## DB Dump format
 *
 * ---region Install
 * #sql queries, with `--` separated
 * CREATE TABLE `company1` (
 *     `companyid` int(11) NOT NULL AUTO_INCREMENT,
 *     `name` varchar(64) NOT NULL,
 *     `email` varchar(255) DEFAULT NULL,
 *     `api_token` varchar(64) DEFAULT NULL,
 *     PRIMARY KEY (`companyid`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * --
 * CREATE TABLE `company2` (
 *     `companyid` int(11) NOT NULL AUTO_INCREMENT,
 *     `name` varchar(64) NOT NULL,
 *     `email` varchar(255) DEFAULT NULL,
 *     `api_token` varchar(64) DEFAULT NULL,
 *     PRIMARY KEY (`companyid`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * ---endregion
 *
 * ---region Upgrade
 * #1 sql queries, with `--` separated
 *
 * SELECT ...
 * FROM ...
 * WHERE ...;
 * --
 * ALTER TABLE ...;
 * --
 * UPDATE ...
 * WHERE ...;
 *
 * --
 *
 * #2 sql queries with `--` separated
 *
 * --
 *
 * #3 sql queries with `--` separated
 * ---endregion
 *
 **/

namespace WebXID\EDMo\DB;

use WebXID\EDMo\DB;

/**
 * @deprecated The class is not completed
 *
 * Class Migration
 *
 * @package WebXID\EDMo\DB
 */
class Migration
{
    private static $dump_route = null;

    private $current_files_list = [];
    private $queries_list = [];

    #region Builder

    /**
     * @param string $dump_route
     *
     * @return Migration
     */
    public static function init($dump_route)
    {
        if (empty($dump_route) || !is_string($dump_route) || !is_dir($dump_route)) {
            throw new \InvalidArgumentException('Invalid $dump_route');
        }

        if (substr($dump_route, -1) === '\\' || substr($dump_route, -1) === '/') {
            $dump_route = substr($dump_route, 0, -1);
        }

        self::$dump_route = $dump_route;

        return new static();
    }

    #endregion

    #region Interface methods

    /**
     * @param string $sql_file_name
     *
     * @return $this
     */
    public function collectUpgrades($sql_file_name)
    {
        if (!is_string($sql_file_name) || empty($sql_file_name)) {
            throw new \InvalidArgumentException('Invalid $sql_file_name');
        }

        $this->setFile($sql_file_name);
        $this->collectSQLQueries();

        return $this;
    }

    /**
     * @return int - count of executed queries blocks
     */
    public function execute()
    {
        $db = DB::beginTransaction();

        $queries_count = 0;

        try {
            foreach ($this->queries_list as $file_name => $queries) {
                foreach ($queries as $query_line_number => $query) {
                    $queries_count ++;

                    $db->query($query)
                        ->execute();
                }
            }

            $db->commitTransaction();
        } catch (\Exception $e) {
            $db->rollbackTransaction();

            $error =
                $e->getMessage() . "\n" .
                "\n" .
                "=== DETAILS ===\n" .
                "- At: " . self::$dump_route . "{$file_name} : {$query_line_number}\n" .
                "- Query:\n" .
                "```\n" .
                $query . "\n".
                "```";

            throw new \RuntimeException($e->getMessage(), 0, $e);
        }

        return $queries_count;
    }

    #endregion

    #region Setters

    private function setFile($file_name)
    {
        if (!is_string($file_name)) {
            throw new \InvalidArgumentException('Invalid $file_name');
        }

        if (!is_file(self::$dump_route . '/' . $file_name)) {
            throw new \RuntimeException('File does not exist. File route: ' . self::$dump_route . '/' . $file_name);
        }

        $this->current_files_list[] = self::$dump_route . '/' . $file_name;
    }

    #endregion

    #region Helpers

    private function collectFiles()
    {
        foreach (glob(self::$dump_route . "/*.sql") as $filename) {
            if (is_file($filename)) {
                $this->current_files_list[] = $filename;
            }
        }
    }

    private function collectSQLQueries()
    {
        foreach ($this->current_files_list as $file) {
            $file_queries = file_get_contents($file);


            $textAr = explode("\n", $file_queries);
            $upgrade = [];
            $query_numbers = 1;
            $line_numbers = 1;

            foreach ($textAr as $k => $line) {
                $line = trim($line);
                $line_numbers ++;

                if (empty($line) || substr($line, 0, 2) == '--' || $line[0] == '#') {
                    $query_numbers = $line_numbers;

                    continue;
                }

                if (!isset($upgrade[$query_numbers])) {
                    $upgrade[$query_numbers] = '';
                }

                $upgrade[$query_numbers] .= "{$line}\n";
            }

            if (!empty($upgrade)) {
                $this->queries_list[$file] = $upgrade;
            }
        }
    }

    #endregion
}
