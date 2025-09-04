<?php
/**
 * 2007-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_0($module)
{

	if (!Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_prestablog` (
     `id_prestablog` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "prestablog",
    PRIMARY KEY  (`id_prestablog`, `id_lang`, `id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;')) {
            return false;
     }


     if (!Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_advancedsearch` (
     `id_advancedsearch` int(10),
    `id_lang` int(10),
    `id_shop` int(10),
    `exclude` int(1) DEFAULT 0,
    `status` int(10),
    `date_upd` DATETIME NOT NULL,
    `type` VARCHAR(20) DEFAULT  "advancedsearch",
    PRIMARY KEY  (`id_advancedsearch`, `id_lang`, `id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;')) {
            return false;
     }


     if (!Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartindexnow_google_logs` (
     `id_google_log` int(10) NOT NULL AUTO_INCREMENT,
    `name` varchar(250),
    `status` int(10),
    `url` varchar(250),
    `commentaire` text,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_google_log`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;')) {
            return false;
     }

    $module->registerHook('actionObjectNewsClassUpdateAfter');
    $module->registerHook('actionObjectNewsClassDeleteBefore');
    $module->registerHook('actionObjectNewsClassDeleteAfter');
    $module->registerHook('actionObjectNewsClassAddAfter');

    $module->registerHook('actionObjectAdvancedSearchSeoClassUpdateAfter');
    $module->registerHook('actionObjectAdvancedSearchSeoClassDeleteBefore');
    $module->registerHook('actionObjectAdvancedSearchSeoClassDeleteAfter');
    $module->registerHook('actionObjectAdvancedSearchSeoClassAddAfter');


    $module->registerHook('actionObjectAddAfter');
    $module->registerHook('actionObjectUpdateAfter');
    $module->registerHook('actionObjectDeleteAfter');
    $module->registerHook('actionObjectDeleteBefore');

	return true;
	

}