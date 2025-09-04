<?php
/**
* 2007-2017 PrestaShop
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
*  @author    Olivier CLEMENCE <manit4c@gmail.com>
*  @copyright Op'art
*  @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_products` (
    `id_product` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "product",
    PRIMARY KEY  (`id_product`, `id_lang`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_categories` (
    `id_category` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "category",
    PRIMARY KEY  (`id_category`, `id_lang`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_suppliers` (
    `id_supplier` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "supplier",
    PRIMARY KEY  (`id_supplier`, `id_lang`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_cms` (
    `id_cms` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "cms",
    PRIMARY KEY  (`id_cms`, `id_lang`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_prestablog` (
    `id_prestablog` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "prestablog",
    PRIMARY KEY  (`id_prestablog`, `id_lang`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_advancedsearch` (
     `id_advancedsearch` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "advancedsearch",
    PRIMARY KEY  (`id_advancedsearch`, `id_lang`, `id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';




$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_manufacturers` (
    `id_manufacturer` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "manufacturer",
    PRIMARY KEY  (`id_manufacturer`, `id_lang`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_logs` (
    `id_log` int(10) NOT NULL AUTO_INCREMENT,
    `name` varchar(250),
    `status` int(10),
    `url` varchar(250),
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_log`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_google_logs` (
    `id_google_log` int(10) NOT NULL AUTO_INCREMENT,
    `name` varchar(250),
    `status` int(10),
    `url` varchar(250),
    `date_upd` DATETIME NOT NULL,
    `commentaire` text,
    PRIMARY KEY  (`id_google_log`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_waiting` (
    `id_waiting` int(10) NOT NULL AUTO_INCREMENT,
    `name` varchar(250),
    `type` varchar(250),
    `id` int(10),
    `url` varchar(250),
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_waiting`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}