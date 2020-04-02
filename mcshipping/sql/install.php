<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$sql = array();

//Création de la table du module
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mcshipping` (
    `id_mcshipping` int(11) NOT NULL AUTO_INCREMENT,
    `type_livraison` varchar(100) NOT NULL,
    `id_state` int(10) UNSIGNED NOT NULL,
    `is_meuble` tinyint(1) UNSIGNED NOT NULL DEFAULT "0",
    `petit` int(11) UNSIGNED NOT NULL DEFAULT "0",
    `moyen` int(11) UNSIGNED NOT NULL DEFAULT "0",
    `grand` int(11) UNSIGNED NOT NULL DEFAULT "0",
    `id_employee` int(11) UNSIGNED NOT NULL,
    `created_at` timestamp NULL,
    `updated_at` timestamp NULL,
    PRIMARY KEY  (`id_mcshipping`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

//Création de la table langue du module pour les traductions
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mcshipping_lang` (
    `id_mcshipping` int(11) NOT NULL,
    `id_lang` int(11) UNSIGNED,
    `name` varchar(255) NOT NULL,
    `description` text NULL,
    PRIMARY KEY  (`id_mcshipping`,`id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

//Création de la table des villes
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mccity` (
    `id_mccity` int(11) NOT NULL AUTO_INCREMENT,
    `id_state` int(10) UNSIGNED NOT NULL,
    `id_country` int(10) UNSIGNED NOT NULL DEFAULT 32,
    `id_zone` int(10) UNSIGNED NOT NULL DEFAULT 4,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY  (`id_mccity`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

//Ajout des champs id_city, id_state à la table adresse
$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'address`
    ADD `id_city` int(10) NOT NULL';

//Ajout de la valeur par défaut du champ alias de la table adresse
$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'address` ALTER alias SET DEFAULT "Mon adresse" ';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
