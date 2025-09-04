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

class OpartIndexNowCronSendIndexnowModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        if (!Tools::isPHPCLI()) {
            if (Tools::substr(Tools::encrypt('opartindexnow/cronsendindexnow'), 0, 10) != Tools::getValue('token') || !Module::isEnabled('opartindexnow')) {
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

        $limit = Configuration::get('INDEXNOW_LIMITSEND') - $senday;

        $languages = Language::getLanguages(true,$this->context->shop->id,true);

        $lists = [];
        

        foreach ($languages as  $language) {
            $lists[] = $module->getProducts($this->context->language->id,0,$limit,'id_product','ASC',false,null,null,1,0,false,date("Y-m-d"));

            $lists[] = $module->getCategories($this->context->language->id,0,$limit,'id_category','ASC',false,null,null,1,0,false,date("Y-m-d"));

            $lists[] = $module->getSuppliers($this->context->language->id,0,$limit,'id_supplier','ASC',null,null,1,0,false,date("Y-m-d"));

            $lists[] = $module->getManufacturers($this->context->language->id,0,$limit,'id_manufacturer','ASC',null,null,1,0,false,date("Y-m-d"));





            $links = [];
            foreach ($lists as $item) {  
                foreach ($item['rows'] as $value) {
                    if($value['exclude'] != 1){
                        if (array_key_exists('id_product', $value)) {
                            $links[] = $this->context->link->getProductLink($value['id_product'],null,null,null,$language,$this->context->shop->id);
                        } elseif(array_key_exists('id_category', $value)){
                            $links[] = $this->context->link->getCategoryLink($value['id_category'],null,$language,$this->context->shop->id); 
                        }
                        elseif(array_key_exists('id_supplier', $value)){
                            $links[] = $this->context->link->getSupplierLink($value['id_supplier'],null,$language,$this->context->shop->id); 
                        }
                        elseif(array_key_exists('id_manufacturer', $value)){
                            $links[] = $this->context->link->getManufacturerLink($value['id_manufacturer'],null,$language,$this->context->shop->id); 
                        }
                    }
                }            
               
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
                foreach ($item['rows'] as $value) {
                    if($value['exclude'] != 1){

                         $tableName = '';
                    $fieldName = '';

                    if (array_key_exists('id_category', $value)) {
                        $tableName = 'categories';
                        $fieldName = 'id_category';
                        $id = $value['id_category'];
                        $type[]  = 'category';
                        $url = $this->context->link->getCategoryLink($value['id_category'],null,$language,$this->context->shop->id);
                    }

                    if (array_key_exists('id_product', $value)) {
                        $tableName = 'products';
                        $fieldName = 'id_product';
                        $id = $value['id_product'];
                        $type[]  = 'product';
                        $url = $this->context->link->getProductLink($value['id_product'],null,null,null,$language,$this->context->shop->id);
                    }

                      if (array_key_exists('id_supplier', $value)) {
                        $tableName = 'suppliers';
                        $fieldName = 'id_supplier';
                        $type[]  = 'supplier';
                        $id = $value['id_supplier'];
                        $url = $this->context->link->getSupplierLink($value['id_supplier'],null,$language,$this->context->shop->id);

                    }


                    if (array_key_exists('id_manufacturer', $value)) {
                        $tableName = 'manufacturers';
                        $fieldName = 'id_manufacturer';
                        $type[] = 'manufacturer';
                        $id = $value['id_manufacturer'];
                        $url = $this->context->link->getManufacturerLink($value['id_manufacturer'],null,$language,$this->context->shop->id);
                    }

                      
                         $sql =
                                     'INSERT INTO `' .bqSQL(_DB_PREFIX_ . 'opartindexnow_' . $tableName) . '`
                                         (`' . bqSQL($fieldName) . '`, id_lang, id_shop,status,date_upd)
                                     VALUES (
                                         ' . (int) $id . ',
                                         ' . (int) $context->language->id . ',
                                         ' . (int) $context->shop->id . ',
                                         ' . (int) $reponse . ',
                                         "' . date("Y-m-d H:i:s") . '"
                                     ) ON DUPLICATE KEY UPDATE
                                          status = ' . (int) $reponse.',
                                          date_upd = "' .date("Y-m-d H:i:s").'"';
                             $result = Db::getInstance()->Execute($sql);


                            $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                        (name, status, url,date_upd)
                                    VALUES (
                                        "' . pSQL($value['name']) . '",
                                        ' .     (int)$reponse . ',
                                        "' . htmlentities($url). '",
                                        "' .date("Y-m-d H:i:s"). '")';
                            $result = Db::getInstance()->Execute($sql);
                    }
                   

              }     
                    
            }
        $lists = [];
            
        }
       

          

            

    
    }
    

    }
        die;
    }
}
