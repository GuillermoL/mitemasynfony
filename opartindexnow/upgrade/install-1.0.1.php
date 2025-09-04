<?php
/**
 * 2007-2012 Olivier CLEMENCE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to Olivier CLEMENCE so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Olivier CLEMENCE to newer
 * versions in the future. If you wish to customize Olivier CLEMENCE for your
 * needs please refer to Olivier CLEMENCE for more information.
 *
 * @author    Olivier CLEMENCE
 * @copyright 2007-2012 Olivier CLEMENCE
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Olivier CLEMENCE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_1()
{

	$sql = array();

	$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'opartindexnow_products`
        ADD COLUMN `type` VARCHAR(20) DEFAULT  "product"';


    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'opartindexnow_categories`
        ADD COLUMN `type` VARCHAR(20) DEFAULT  "category" ';


    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'opartindexnow_suppliers`
        ADD COLUMN `type` VARCHAR(20) DEFAULT "supplier" ';

    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'opartindexnow_cms`
        ADD COLUMN `type` VARCHAR(20) DEFAULT  "cms"';


     $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'opartindexnow_manufacturers`
        ADD COLUMN `type` VARCHAR(20) DEFAULT  "manufacturer"';



    foreach ($sql as $query) {
	    if (Db::getInstance()->execute($query) == false) {
	        return false;
	    }
	}

}