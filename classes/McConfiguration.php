<?php

class McConfiguration {

    public function addState($name,$id_zone,$id_country){
        $firstc = substr($name, 0, 1);
        $lastc = substr($name, -1, 1);
        $iso_code = strtoupper($firstc.$lastc);
        $sql = 'INSERT INTO  `'. _DB_PREFIX_.'state`(`id_country`,`id_zone`,`name`,`iso_code`) VALUES ("'.$id_country.'","'.$id_zone.'","'.$name.'","'.$iso_code.'")';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
    }

    public function addCity($id_state,$id_country,$id_zone,$name){
        $sql = 'INSERT INTO  `'. _DB_PREFIX_.'mccity`(`id_state`,`id_country`,`id_zone`,`name`) VALUES ("'.$id_state.'","'.$id_country.'","'.$id_zone.'","'.$name.'")';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
    }

    public static function getZoneByName($name){
        $sql = 'SELECT `id_zone`,`name` 
                FROM `' . _DB_PREFIX_ . 'zone`
                WHERE `name` = "'.$name.'"
                LIMIT 1' ;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getStateByCountry($country_isocode){
        
        $sql = 'SELECT s.`id_state` AS `id_option`,s.`name`
                FROM `' . _DB_PREFIX_ . 'state` s
                JOIN `' . _DB_PREFIX_ . 'country` c 
                ON s.`id_country` = c.`id_country`
                WHERE c.`iso_code` = "'.$country_isocode.'"
                ORDER BY s.`name` ASC' ;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getStateByName($name){
        
        $sql = 'SELECT `id_state`,`name`
                FROM `' . _DB_PREFIX_ . 'state`
                WHERE `name` = "'.$name.'"
                LIMIT 1' ;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getCountryByCode($isocode){
        
        $sql = 'SELECT `iso_code`,`id_country`
                FROM `' . _DB_PREFIX_ . 'country`
                WHERE `iso_code` = "'.$isocode.'"
                AND `active` = 1
                LIMIT 1' ;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
}