<?php

class McState {

    public static function getStateByCountry($country_isocode){
        
        $sql = 'SELECT s.`id_state` AS `id_option`,s.`name`, c.`id_country`
                FROM `' . _DB_PREFIX_ . 'state` s
                JOIN `' . _DB_PREFIX_ . 'country` c 
                ON s.`id_country` = c.`id_country`
                WHERE c.`iso_code` = "'.$country_isocode.'"
                ORDER BY s.`name` ASC' ;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
}