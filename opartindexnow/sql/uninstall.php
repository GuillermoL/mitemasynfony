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

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_products`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_categories`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_suppliers`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_cms`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_manufacturers`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_prestablog`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_advancedsearch`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_logs`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_google_logs`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartindexnow_waiting`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
