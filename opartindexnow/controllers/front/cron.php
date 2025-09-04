<?php
/**
 * 2007-2022 Olivier CLEMENCE
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
 * @copyright 2007-2022 Olivier CLEMENCE
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Olivier CLEMENCE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OpartIndexNowCronModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        if (!Tools::isPHPCLI()) {
            if (Tools::substr(Tools::encrypt('opartindexnow/cron'), 0, 10) != Tools::getValue('token') || !Module::isEnabled('opartindexnow')) {
                die('Bad token');
            }
        }
    }

    public function initContent()
    {
        parent::initContent();

        $module = Module::getInstanceByName('opartindexnow');

if($module->active){

    $sendday = $module->countSend(date("Y-m-d"));



    if($sendday < Configuration::get('INDEXNOW_LIMITSEND')){
        $lists = $module->getListsWainting($sendday);

        $context = Context::getContext();

        $links = [];
        foreach ($lists as $item) {
            $links[] = $item['url'];
        }


          
          $data = array(
            'host' => Tools::getShopDomain(),
            'key' => Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id),
            'keyLocation' => $this->context->shop->getBaseURL(true).Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id).'.txt',
            'urlList' => $links
        );


          $data_string = json_encode($data);

          $url = 'https://www.bing.com/indexnow';

          $ch = curl_init($url);

            $headers = array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string)
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $status = curl_getinfo($ch);
            $reponse = $status['http_code'];

        
        foreach ($lists as $item) {
            $tableName = '';
            $fieldName = '';

            if ($item['type'] == "category") {
                $tableName = 'categories';
                $fieldName = 'id_category';
                $type[]  = 'category';
            }

            if ($item['type'] == "product") {
                $tableName = 'products';
                $fieldName = 'id_product';
                $type[]  = 'product';
            }

              if ($item['type'] == "supplier") {
                $tableName = 'suppliers';
                $fieldName = 'id_supplier';
                $type[]  = 'supplier';
            }

            if ($item['type'] == "cms") {
                $tableName = 'cms';
                $fieldName = 'id_cms';
                $type[]  = 'cms';
            }


            if ($item['type'] == "manufacturer") {
                $tableName = 'manufacturers';
                $fieldName = 'id_manufacturer';
                $type[] = 'manufacturer';
            }

              /* $sql =
                        'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_' . $tableName . '
                            (' . $fieldName . ', id_lang, id_shop,status,date_upd)
                        VALUES (
                            ' . (int) $item['id'] . ',
                            ' . (int) $context->language->id . ',
                            ' . (int) $context->shop->id . ',
                            ' . (int) $reponse . ',
                            "' . date("Y-m-d H:i:s") . '"
                        ) ON DUPLICATE KEY UPDATE
                             status = ' . (int) $reponse.',
                             date_upd = "' .date("Y-m-d H:i:s").'"'; */
                 $sql =
                             'INSERT INTO `' .bqSQL(_DB_PREFIX_ . 'opartindexnow_' . $tableName) . '`
                                 (`' . bqSQL($fieldName) . '`, id_lang, id_shop,status,date_upd)
                             VALUES (
                                 ' . (int) $item['id'] . ',
                                 ' . (int) $context->language->id . ',
                                 ' . (int) $context->shop->id . ',
                                 ' . (int) $reponse . ',
                                 "' . date("Y-m-d H:i:s") . '"
                             ) ON DUPLICATE KEY UPDATE
                                  status = ' . (int) $reponse.',
                                  date_upd = "' .date("Y-m-d H:i:s").'"';
                     $result = Db::getInstance()->Execute($sql);

                     /* $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $item['type'] . '",
                                ' .     $reponse . ',
                                "' . $item['url']. '",
                                "' .date("Y-m-d H:i:s"). '")'; */

                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($item['type']) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities($item['url']). '",
                                "' .date("Y-m-d H:i:s"). '")';
                    $result = Db::getInstance()->Execute($sql);


                     /* $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                WHERE id_waiting = '.$item['id_waiting']; */
                    
                    $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                WHERE id_waiting = '.(int)$item['id_waiting'];
                    $result = Db::getInstance()->Execute($sql);
        }
    }
    

    }
        die;
    }
}
