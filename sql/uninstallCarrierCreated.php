<?php

$sql = array();


$sql[] = 'UPDATE `'._DB_PREFIX_.'carrier`
            SET `deleted` = 1 
            WHERE `deleted` = 0
            AND `external_module_name` = `id_mcshipping`';

$sql[] = 'DELETE FROM `'._DB_PREFIX_.'carrier_zone`
            WHERE `id_carrier` 
            IN (
                SELECT `id_carrier` 
                FROM `bm_carrier` 
                WHERE `name` 
                IN ("Livraison Standard","Livraison Monconfort","Livraison Express") 
                AND `deleted` = 0)';