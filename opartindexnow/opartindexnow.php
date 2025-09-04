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
 * to Olivier CLEMENCEso we can send you a copy immediately.
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

require_once(_PS_MODULE_DIR_ . 'opartindexnow/classes/GoogleIndexingService.php');

class OpartIndexNow extends Module
{
    private $html = '';
    private $selected_element;
    private $post_error;

    public function __construct()
    {
        $this->name          = 'opartindexnow';
        $this->tab           = 'seo';
        $this->version       = '2.0.11';
        $this->author        = 'Op\'Art';
        $this->module_key    = "90a2dba23fa0f50dea6716290a039120";

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IndexNow for prestashop');
        $this->description = $this->l('Index your pages faster with technology Indexnow');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->confirmUninstall = $this->l('Are you sure you wan\'t uninstal this module ?');
        $this->selected_element = 1;
    }



    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        Configuration::updateValue('INDEXNOW_LIMITSEND', 30);
        include(dirname(__FILE__) . '/sql/install.php');
        return parent::install()
            && $this->registerHook('actionObjectProductDeleteBefore')
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionObjectCategoryDeleteBefore')
            && $this->registerHook('actionCategoryDelete')
            && $this->registerHook('actionCategoryUpdate')
            && $this->registerHook('actionCategoryAdd')
            && $this->registerHook('actionObjectSupplierUpdateAfter')
            && $this->registerHook('actionObjectSupplierDeleteAfter')
            && $this->registerHook('actionObjectSupplierDeleteBefore')
            && $this->registerHook('actionObjectSupplierAddAfter')
            && $this->registerHook('actionCmsUpdate')
            && $this->registerHook('actionCmsDelete')
            && $this->registerHook('actionCmsAdd')
            && $this->registerHook('actionManufacturerUpdate')
            && $this->registerHook('actionManufacturerDelete')
            && $this->registerHook('actionManufacturerAdd')
            && $this->registerHook('actionObjectManufacturerDeleteBefore')
            && $this->registerHook('actionObjectManufacturerDeleteAfter')
            && $this->registerHook('actionObjectManufacturerAddAfter')
            && $this->registerHook('actionObjectManufacturerUpdateAfter')
            && $this->registerHook('displayAdminAfterHeader')
            && $this->registerHook('actionObjectNewsClassUpdateAfter')
            && $this->registerHook('actionObjectNewsClassDeleteBefore')
            && $this->registerHook('actionObjectNewsClassDeleteAfter')
            && $this->registerHook('actionObjectNewsClassAddAfter')
            && $this->registerHook('actionObjectAdvancedSearchSeoClassUpdateAfter')
            && $this->registerHook('actionObjectAdvancedSearchSeoClassDeleteBefore')
            && $this->registerHook('actionObjectAdvancedSearchSeoClassDeleteAfter')
            && $this->registerHook('actionObjectAdvancedSearchSeoClassAddAfter')
            && $this->registerHook('actionObjectAddAfter')
            && $this->registerHook('actionObjectUpdateAfter')
            && $this->registerHook('actionObjectDeleteAfter')
            && $this->registerHook('actionObjectDeleteBefore');
    }


    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');
        if (Configuration::get('OPARTINDEXNOW_KEY')) {

            unlink(_PS_CORE_DIR_ . "/" . Configuration::get('OPARTINDEXNOW_KEY') . '.txt');
            Configuration::deleteByName('OPARTINDEXNOW_KEY');
            Configuration::deleteByName('INDEXNOW_LIMITLOGS');
            Configuration::deleteByName('INDEXNOW_SEND_BY_CRON');
            Configuration::deleteByName('INDEXNOW_NOINDEX_ARGS');
            Configuration::deleteByName('INDEXNOW_VISIBILITY_ARGS');
            Configuration::deleteByName('INDEXNOW_LIMITSEND');
            Configuration::deleteByName('GOOGLE_INDEXING_KEY');
            Configuration::deleteByName('GOOGLE_INDEXING_CONSENT');
            
            
        }

        return parent::uninstall();
    }


    private function countCategories()
    {
        $sql = 'SELECT COUNT(id_category) FROM `' . _DB_PREFIX_ . 'opartindexnow_categories`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }



    public function getCategories(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $id_category = false,
        $excluded = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false,
        $date = null
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                c.id_category,
                c.active, cl.name,
                cl.link_rewrite,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'category` c
                ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (
                    c.`id_category` = cl.`id_category` '
            . Shop::addSqlRestrictionOnLang('cl') . '
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_categories` oin
                ON (
                    oin.`id_category` = c.`id_category` '
            . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = cl.id_lang
                )
            WHERE cl.`id_lang` = ' . (int) $id_lang
            . ($id_category ? ' AND c.`id_parent` = ' . (int) $id_category : '')
            . ($excluded ? ' AND oin.`exclude` = 1' : '')
            . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
            . ($active ? ' AND c.`active` = 1' : '')
            . ($notactive ? ' AND c.`active` = 0' : '')
            . ($date ? ' AND c.`date_upd` LIKE "'.$date.'%"' : '')
            . ($status ? ' AND oin.`status` > 0' : '')
            . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? ' ORDER BY `' . $order_by. '` ' . $order_way : '')
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');


        $result = array();
        $result['rows']      = Db::getInstance()->executeS($sql);
        $result['totalItem'] = Db::getInstance()->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }

    public function getProducts(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $id_category = false,
        $excluded = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false,
        $date = null
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                p.id_product,
                product_shop.active,
                pl.name,
                pl.link_rewrite,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (
                    p.`id_product` = pl.`id_product` '
            . Shop::addSqlRestrictionOnLang('pl') . '
                )'
            . ($id_category ? ' LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c
                ON (
                    c.`id_product` = p.`id_product`
                )' : '') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_products` oin
                ON (
                    oin.`id_product` = p.`id_product`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = pl.id_lang
                )
            WHERE pl.`id_lang` = ' . (int) $id_lang
            . ($id_category ? ' AND c.`id_category` = ' . (int) $id_category : '')
            . ($excluded ? ' AND oin.`exclude` = 1' : '')
            . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
            . ($active ? ' AND p.`active` = 1' : '')
            . ($notactive ? ' AND p.`active` = 0' : '')
            . ($date ? ' AND p.`date_upd` LIKE "'.$date.'%"' : '')
            . ($status ? ' AND oin.`status` > 0' : '')
            . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? ' ORDER BY `' . $order_by. '` ' . $order_way : '')
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');



        $result = array();
        $result['rows']      = Db::getInstance()->executeS($sql);
        $result['totalItem'] = Db::getInstance()->executeS('SELECT FOUND_ROWS() AS totalItem');



        return $result;
    }


    private function countProducts()
    {
        $sql = 'SELECT COUNT(id_product) FROM `' . _DB_PREFIX_ . 'opartindexnow_products`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public function getSuppliers(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false,
        $date = null
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                s.id_supplier,
                s.active,
                s.name,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'supplier` s
            ' . Shop::addSqlAssociation('supplier', 's') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'supplier_lang` sl
                ON (
                    s.`id_supplier` = sl.`id_supplier`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_suppliers` oin
                ON (
                    oin.`id_supplier` = s.`id_supplier`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = sl.id_lang
                )
            WHERE sl.`id_lang` = ' . (int) $id_lang
            . ($exclude ? ' AND oin.`exclude` = 1' : '')
            . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
            . ($active ? ' AND s.`active` = 1' : '')
            . ($notactive ? ' AND s.`active` = 0' : '')
            . ($date ? ' AND s.`date_upd` LIKE "'.$date.'%"' : '')
            . ($status ? ' AND oin.`status` > 0' : '')
            . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? ' ORDER BY `' . $order_by. '` ' . $order_way : '')
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');


        $result = array();
        $result['rows']      = Db::getInstance()->executeS($sql);
        $result['totalItem'] = Db::getInstance()->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }

    private function countSuppliers()
    {
        $sql = 'SELECT COUNT(id_supplier) FROM `' . _DB_PREFIX_ . 'opartindexnow_suppliers`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }


    public function getCms(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false
    ) {
        if (version_compare(_PS_VERSION_, '1.6.0.11') <= 0) {
            $sql =
                'SELECT
                    SQL_CALC_FOUND_ROWS
                    c.id_cms,
                    c.active,
                    c.indexation,
                    cl.meta_title as name,
                    cl.link_rewrite,
                    oin.exclude,
                    oin.status,
                    oin.date_upd,
                    oin.type
                FROM `' . _DB_PREFIX_ . 'cms` c
                ' . Shop::addSqlAssociation('cms', 'c') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'cms_lang` cl
                    ON (
                        c.`id_cms` = cl.`id_cms`
                    )
                LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_cms` oin
                    ON (
                        oin.`id_cms` = c.`id_cms`
                        ' . Shop::addSqlRestriction(false, 'oin') . '
                        AND oin.id_lang = ' . (int) $id_lang . '
                    )
                WHERE cl.`id_lang` = ' . (int) $id_lang
                . ($exclude ? ' AND oin.`exclude` = 1' : '')
                . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                . ($active ? ' AND c.`active` = 1' : '')
                . ($notactive ? ' AND c.`active` = 0' : '')
                . ($status ? ' AND oin.`status` > 0' : '') . '
                GROUP BY c.id_cms '
                . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? 'ORDER BY `' . $order_by. '` ' . $order_way : '')
                . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        } else {
            $sql =
                'SELECT
                    SQL_CALC_FOUND_ROWS
                    c.id_cms,
                    c.active,
                    c.indexation,
                    cl.meta_title as name,
                    cl.link_rewrite,
                    oin.exclude,
                    oin.status,
                    oin.date_upd,
                    oin.type
                FROM `' . _DB_PREFIX_ . 'cms` c
                ' . Shop::addSqlAssociation('cms', 'c') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'cms_lang` cl
                    ON (
                        c.`id_cms` = cl.`id_cms`
                        ' . Shop::addSqlRestrictionOnLang('cl') . '
                    )
                LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_cms` oin
                    ON (
                        oin.`id_cms` = c.`id_cms`
                        ' . Shop::addSqlRestriction(false, 'oin') . '
                        AND oin.id_lang = ' . (int) $id_lang . '
                    )
                WHERE cl.`id_lang` = ' . (int) $id_lang
                . ($exclude ? ' AND oin.`exclude` = 1' : '')
                . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                . ($active ? ' AND c.`active` = 1' : '')
                . ($notactive ? ' AND c.`active` = 0' : '')
                . ($status ? ' AND oin.`status` > 0' : '') . '
                GROUP BY c.id_cms '
                . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? 'ORDER BY `' . $order_by. '` ' . $order_way : '')
                . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        }


        $result = array();
        $result['rows']      = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }

    private function countCms()
    {
        $sql = 'SELECT COUNT(id_cms) FROM `' . _DB_PREFIX_ . 'opartindexnow_cms`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }




    public function getManufacturers(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false,
        $date = null
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                m.id_manufacturer,
                m.active,
                m.name,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'manufacturer` m
            ' . Shop::addSqlAssociation('manufacturer', 'm') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer_lang` ml
                ON (
                    m.`id_manufacturer` = ml.`id_manufacturer`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_manufacturers` oin
                ON (
                    oin.`id_manufacturer` = m.`id_manufacturer`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = ' . (int) $id_lang . '
                )
            WHERE ml.id_lang = ' . (int) $id_lang
            . ($exclude ? ' AND oin.`exclude` = 1' : '')
            . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
            . ($active ? ' AND m.`active` = 1' : '')
            . ($notactive ? ' AND m.`active` = 0' : '')
            . ($date ? ' AND m.`date_upd` LIKE "'.$date.'%"' : '')
            . ($status ? ' AND oin.`status` > 0' : '')
            . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? ' ORDER BY `' . $order_by. '` ' . $order_way : '')
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $result = array();
        $result['rows']      = Db::getInstance()->executeS($sql);
        $result['totalItem'] = Db::getInstance()->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }


    private function countManufacturers()
    {
        $sql = 'SELECT COUNT(id_manufacturer) FROM `' . _DB_PREFIX_ . 'opartindexnow_manufacturers`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

     private function countPrestablog()
    {
        $sql = 'SELECT COUNT(id_prestablog_news) FROM `' . _DB_PREFIX_ . 'prestablog_news`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }


     public function getPrestablog(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false,
        $date = null
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                p.id_prestablog_news,
                p.actif,
                pl.title,
                pl.link_rewrite,
                pl.id_lang,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'prestablog_news` p
            ' . Shop::addSqlAssociation('prestablog_news', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_news_lang` pl
                ON (
                    p.`id_prestablog_news` = pl.`id_prestablog_news`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_prestablog` oin
                ON (
                    oin.`id_prestablog` = p.`id_prestablog_news`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = ' . (int) $id_lang . '
                )
            WHERE pl.id_lang = ' . (int) $id_lang
            . ($exclude ? ' AND oin.`exclude` = 1' : '')
            . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
            . ($active ? ' AND p.`actif` = 1' : '')
            . ($notactive ? ' AND p.`actif` = 0' : '')
            . ($date ? ' AND p.`date_modification` LIKE "'.$date.'%"' : '')
            . ($status ? ' AND oin.`status` > 0' : '')
            . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? ' ORDER BY `' . $order_by. '` ' . $order_way : '')
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $result = array();
        $result['rows']      = Db::getInstance()->executeS($sql);
        $result['totalItem'] = Db::getInstance()->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }



     private function countAdvancedSearch()
    {
        $sql = 'SELECT COUNT(id_seo) FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }


     public function getAdvancedSearch(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $status = false,
        $date = null
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                pm.id_seo,
                pm.title,
                pm.seo_url,
                pm.id_lang,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang` pm
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_advancedsearch` oin
                ON (
                    oin.`id_advancedsearch` = pm.`id_seo`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = ' . (int) $id_lang . '
                )
            WHERE pm.id_lang = ' . (int) $id_lang
            . ($exclude ? ' AND oin.`exclude` = 1' : '')
            . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
            . ($date ? ' AND p.`date_modification` LIKE "'.$date.'%"' : '')
            . ($status ? ' AND oin.`status` > 0' : '')
            . (Validate::isOrderWay($order_way) && Validate::isOrderBy($order_by) ? ' ORDER BY `' . $order_by. '` ' . $order_way : '')
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $result = array();
        $result['rows']      = Db::getInstance()->executeS($sql);
        $result['totalItem'] = Db::getInstance()->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }

    public function getArticlePrestablog($id_prestablog){

        $article = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'prestablog_news pn
            LEFT JOIN '._DB_PREFIX_.'prestablog_news_lang pnl ON pnl.id_prestablog_news = pn.id_prestablog_news and id_lang = '.(int)$this->context->language->id.' WHERE pn.id_prestablog_news = '.(int)$id_prestablog);

        return $article;

    }

    public function prestablogUrl($params)
    {

        if (Configuration::get('prestablog_urlblog') == false) {
            $base_url_blog = 'blog';
        } else {
            $base_url_blog = Configuration::get('prestablog_urlblog');
        }
        $param = null;
        $ok_rewrite = '';
        $ok_rewrite_id = '';
        $ok_rewrite_do = '';
        $ok_rewrite_cat = '';
        $ok_rewrite_categorie = '';
        $ok_rewrite_au = '';
        $ok_rewrite_page = '';
        $ok_rewrite_titre = '';
        $ok_rewrite_seo = '';
        $ok_rewrite_year = '';
        $ok_rewrite_month = '';
        $ko_rewrite = '';
        $ko_rewrite_id = '';
        $ko_rewrite_do = '';
        $ko_rewrite_cat = '';
        $ko_rewrite_au = '';
        $ko_rewrite_page = '';
        $ko_rewrite_year = '';
        $ko_rewrite_month = '';
         if (isset($params['id_prestablog_news']) && $params['id_prestablog_news'] != '') {
            $ko_rewrite_id = '&id=' . $params['id_prestablog_news'];
            $ok_rewrite_id = '-n' . $params['id_prestablog_news'];
            ++$param;
        }
        
        if (isset($params['link_rewrite']) && $params['link_rewrite'] != '') {
            $ok_rewrite_seo = $params['link_rewrite'];
            $ok_rewrite_titre = '';
            ++$param;
        }
        $ok_rewrite = $base_url_blog . '/' . $ok_rewrite_do . $ok_rewrite_categorie . $ok_rewrite_page;
            $ok_rewrite .= $ok_rewrite_year . $ok_rewrite_month . $ok_rewrite_titre . $ok_rewrite_seo;
            $ok_rewrite .= $ok_rewrite_cat . $ok_rewrite_id;
            $ok_rewrite .= $ok_rewrite_au;
            $ko_rewrite = '?fc=module&module=prestablog&controller=blog&' . ltrim(
                $ko_rewrite_do . $ko_rewrite_id . $ko_rewrite_cat . $ko_rewrite_au . $ko_rewrite_page . $ko_rewrite_year . $ko_rewrite_month,
                '&'
            );

            $shop = Context::getContext()->shop;
            $module_shop_domain = (Tools::usingSecureMode() ? 'https://' : 'http://') . $shop->domain_ssl . $shop->getBaseURI();

            $getlanglink = "";
            if (!Configuration::get('PS_REWRITING_SETTINGS') || Language::countActiveLanguages() <= 1) {
            $getlanglink = '';
            }
            else{
                 $getlanglink = Language::getIsoById((int) $params['id_lang']) . '/';
            }
            
        if ((int) Configuration::get('PS_REWRITING_SETTINGS') && (int) Configuration::get('prestablog_rewrite_actif')) {
            return $module_shop_domain . $getlanglink . $ok_rewrite;
        } else {
            return $module_shop_domain . $getlanglink . $ko_rewrite;
        }
    }



    public function getUrlSubmitted()
    {

        if (Configuration::get('INDEXNOW_LIMITLOGS') > 0) {

            $count = Db::getInstance()->getValue('SELECT COUNT(id_log) FROM `' . _DB_PREFIX_ . 'opartindexnow_logs`');

            if ($count > Configuration::get('INDEXNOW_LIMITLOGS')) {
                $nombre = $count - Configuration::get('INDEXNOW_LIMITLOGS');
                
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` ORDER BY id_log ASC LIMIT ' . (int)$nombre);
            }
        }

        // Modif multiboutique
        if (Shop::isFeatureActive() &&  Shop::getContext() != 4) {
            $base_url = $this->context->link->getBaseLink($this->context->shop->id);
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE URL LIKE "%' . pSQL($base_url) . '%" ORDER BY date_upd DESC';
        } else {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` ORDER BY date_upd DESC';
        }


        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }



    public function getUrlSubmittedGoogle()
    {

        if (Configuration::get('INDEXNOW_LIMITLOGS') > 0) {

            $count = Db::getInstance()->getValue('SELECT COUNT(id_google_log) FROM `' . _DB_PREFIX_ . 'opartindexnow_google_logs`');

            if ($count > Configuration::get('INDEXNOW_LIMITLOGS')) {
                $nombre = $count - Configuration::get('INDEXNOW_LIMITLOGS');
                
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'opartindexnow_google_logs` ORDER BY id_google_log ASC LIMIT ' . (int)$nombre);
            }
        }

        // Modif multiboutique
        if (Shop::isFeatureActive() &&  Shop::getContext() != 4) {
            $base_url = $this->context->link->getBaseLink($this->context->shop->id);
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_google_logs` WHERE URL LIKE "%' . pSQL($base_url) . '%" ORDER BY date_upd DESC';
        } else {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_google_logs` ORDER BY date_upd DESC';
        }


        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }


    private function getLog($id)
    {


        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE id_log = ' . (int)$id;

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    private function getLogGoogle($id)
    {


        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_google_logs` WHERE id_google_log = ' . (int)$id;

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }


    private function getLastlog()
    {


        $sql = 'SELECT status FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` ORDER BY id_log DESC';


        return  Db::getInstance()->getValue($sql);
    }


    private function checkUrlgetLogs($url)
    {

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE url = "' . pSQL($url) . '" and date_upd like "' . date("Y-m-d") . '%"';
        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public function countSend($date)
    {

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE  date_upd like "' . pSQL($date) . '%"';
        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public function lastSend()
    {

        $sql = 'SELECT date_upd FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` ORDER BY id_log DESC';
        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }





    //function to send on API indexNow
    public function SendIndexNow($listitem, $items, $types)
    {

 $reponse = [];

        if (Shop::isFeatureActive() && Shop::getContext() === Shop::CONTEXT_ALL){
              $shopIds = Shop::getShops(true, null, true);

              foreach ($shopIds as  $id) {
                  $key =  Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id);
                  

                  $ch = null;

                    if ($key) {
            foreach ($types as $type) {
                if ($type == 'category') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getCategoryLink($item,null,null,null,$id);
                            if($linkcms != false && !empty(trim($linkcms))){
                                $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_exec($ch);
                                $status = curl_getinfo($ch);
                                $reponse[$item] = $status['http_code'];
                            }
                        }
                    }
                }

                if ($type == 'product') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $languages = Language::getLanguages(true,$id);
                            foreach($languages as $language){
                                $linkcms = $this->context->link->getProductLink($item,null,null,null,$language['id_lang'],$id);
                                if($linkcms != false && !empty(trim($linkcms))){
                                    $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_exec($ch);
                                $status = curl_getinfo($ch);
                                $reponse[$item] = $status['http_code'];
                                }
                                
                            }
                            
                        }
                    }
                }


                if ($type == 'supplier') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getSupplierLink($item,null,null,$id);
                            if($linkcms != false && !empty(trim($linkcms))){
                                $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_exec($ch);
                                $status = curl_getinfo($ch);
                                $reponse[$item] = $status['http_code'];
                            }
                        }
                    }
                }


                if ($type == 'cms') {

                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getCMSLink($item,null,null,null,$id);
                           if($linkcms != false && !empty(trim($linkcms))){
                                $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_exec($ch);
                                $status = curl_getinfo($ch);
                                $reponse[$item] = $status['http_code'];
                            }
                        }
                    }
                }


                if ($type == 'manufacturer') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getManufacturerLink($item,null,null,$id);
                           if($linkcms != false && !empty(trim($linkcms))){
                                $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_exec($ch);
                                $status = curl_getinfo($ch);
                                $reponse[$item] = $status['http_code'];
                            }
                        }
                    }
                }
            }

            
        }
               } 
               return $reponse;
        }
        else{

            $key =  Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id);
            $reponse = [];


        $ch = null;
        if ($key) {
            foreach ($types as $type) {
                if ($type == 'category') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getCategoryLink($item);
                            $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch);
                            $status = curl_getinfo($ch);
                            $reponse[$item] = $status['http_code'];
                        }
                    }
                }

                if ($type == 'product') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $languages = Language::getLanguages(true,$this->context->shop->id);
                            foreach($languages as $language){
                                $linkcms = $this->context->link->getProductLink($item,null,null,null,$language['id_lang']);
                                $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_exec($ch);
                                $status = curl_getinfo($ch);
                                $reponse[$item] = $status['http_code'];
                            }
                            
                        }
                    }
                }


                if ($type == 'supplier') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getSupplierLink($item);
                            $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch);
                            $status = curl_getinfo($ch);
                            $reponse[$item] = $status['http_code'];
                        }
                    }
                }


                if ($type == 'cms') {

                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getCMSLink($item);
                            $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch);
                            $status = curl_getinfo($ch);
                            $reponse[$item] = $status['http_code'];
                        }
                    }
                }


                if ($type == 'manufacturer') {
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $linkcms = $this->context->link->getManufacturerLink($item);
                            $ch = curl_init('https://www.bing.com/indexnow?url=' . $linkcms . '&key=' . $key);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch);
                            $status = curl_getinfo($ch);
                            $reponse[$item] = $status['http_code'];
                        }
                    }
                }

                if($type == "prestablog"){
                    foreach ($listitem as $item) {
                        if (empty($items[$item])) {
                            $ch = curl_init('https://www.bing.com/indexnow?url=' . $url . '&key=' . $key);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch);
                            $status = curl_getinfo($ch);
                            $reponse[$item] = $status['http_code'];
                        }
                    }
                }
            }

            return $reponse;
        }

        }
        
    }

    public function ResendItem($item)
    {

        $key =  Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id);
        if ($key) {
            $ch = curl_init('https://www.bing.com/indexnow?url=' . $item['url'] . '&key=' . $key);
            curl_exec($ch);
            $status = curl_getinfo($ch);
            $reponse = $status['http_code'];

            $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                            (name, status, url,date_upd)
                        VALUES (
                            "' . pSQL($item['name']) . '",
                            ' .     pSQL($reponse) . ',
                            "' .  pSQL($item['url']) . '",
                            "' . date("Y-m-d H:i:s") . '")';
            $result = Db::getInstance()->Execute($sql);
        }
    }


      public function ResendItemGoogle($item)
    {

        $indexing = new GoogleIndexingService();
        $result = $indexing->notifyUrl($item['url'], 'URL_UPDATED');


                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }


            if($status != 0){
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                            (name, status, url,commentaire,date_upd)
                        VALUES (
                            "' . pSQL($item['name']) . '",
                            ' .     (int)$status . ',
                            "' .  pSQL($item['url']) . '",
                            "' .  pSQL($commentaire) . '",
                            "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql); 
            }
           
    }


    public function postProcess()
    {


        $id_lang = $this->context->language->id;

        //enregistrer les configurations
        if (Tools::isSubmit('indexnowsendconfig')) {
            if (Tools::getValue('limitsend') > 0) {
                if(Tools::getValue('limitsend') < 200 ){
                    Configuration::updateValue('INDEXNOW_LIMITSEND', Tools::getValue('limitsend'));
                }
                else{

                    $erreur = $this->l('Google allows you to send a maximum of 200 URLs.');

                    $this->context->smarty->assign('erreur', $erreur);

                }
                
            } else {

                $erreur = $this->l('The daily send limit must be greater than 0');

                $this->context->smarty->assign('erreur', $erreur);
            }
            if (Tools::getValue('limitlogs')) {
                Configuration::updateValue('INDEXNOW_LIMITLOGS', Tools::getValue('limitlogs'));
            }
            Configuration::updateValue('INDEXNOW_NOINDEX_ARGS', Tools::getValue('INDEXNOW_NOINDEX_ARGS'));
            Configuration::updateValue('INDEXNOW_VISIBILITY_ARGS', Tools::getValue('INDEXNOW_VISIBILITY_ARGS'));
            Configuration::updateValue('INDEXNOW_SEND_BY_CRON', Tools::getValue('INDEXNOW_SEND_BY_CRON'));

            //si oui, alors on met les urls en noindex en exclusion
            if (Tools::getValue('INDEXNOW_NOINDEX_ARGS') == 1) {
                $this->getItemsnoIndex();
            }

            if (Tools::getValue('INDEXNOW_VISIBILITY_ARGS') == 1) {
                $this->getItemsVisibilty();
            }

            $confirmation = $this->l('The data has been saved');

            $this->context->smarty->assign('confirmation', $confirmation);
        }

        //save config google
          if(Tools::isSubmit('googlesendconfig')){

            Configuration::UpdateValue('GOOGLE_INDEXING_CONSENT', Tools::getValue('consentgoogle'));


            if (isset($_FILES['googlejson']) && $_FILES['googlejson']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['googlejson']['tmp_name'];
            $fileName = $_FILES['googlejson']['name'];
            $fileType = mime_content_type($fileTmpPath);


            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if ($fileExtension !== 'json') {
                $this->errors[] = $this->l('Only JSON files are allowed.');
                 $this->context->smarty->assign(array(
                            'errors' => $this->errors,
                ));
            }  else {
                    $jsonContent = file_get_contents($fileTmpPath);
                    json_decode($jsonContent);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->errors[] = $this->l('Uploaded file is not valid JSON.');
                        $this->context->smarty->assign(array(
                            'errors' => $this->errors,
                        ));
                    } else {
                        Configuration::UpdateValue('GOOGLE_INDEXING_KEY', $jsonContent);
                        

                        
                        $destination = _PS_CORE_DIR_ . '/service_account_file.json';
                        if (!move_uploaded_file($fileTmpPath, $destination)) {
                            $this->errors[] = $this->l('Failed to move uploaded file.');
                            $this->context->smarty->assign(array(
                            'errors' => $this->errors,
                            ));
                                Tools::dieObject('test5',false);
                        } else {
                           $confirmation = $this->l('The data has been saved');
                           $this->context->smarty->assign(array(
                            'confirmation' => $confirmation,
                            'googlekey' => Tools::getValue('googlejson')
                            ));
                        }

            

                        
                    }
                }
            }

        }


        if(Tools::isSubmit('sendmanuellyurl')){

            if(Validate::isAbsoluteUrl(Tools::getValue('urlmanuelly'))){

                $item['url'] = Tools::getValue('urlmanuelly');
                $item['name'] = $this->l('manual url');

                $this->ResendItem($item);
                $this->ResendItemGoogle($item);

                $confirmation = $this->l('The url was sent to indexnow');
                $this->context->smarty->assign('confirmation', $confirmation);

            }
            else{
                $erreur = $this->l('Please enter a valid address');
                $this->context->smarty->assign('erreur', $erreur);
            }


            
        }


        //vider la list
        if (Tools::getValue('deletelist')) {

            Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'opartindexnow_logs');
        }


        //vider la list google
        if (Tools::getValue('deletelistgoogle')) {

            Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'opartindexnow_google_logs');
        }


        //formulaire exclusion
        if (Tools::getValue('listItem')) {

            $excludeValue  = Tools::getValue('NoIndexNow');
            $listItem = Tools::getValue('listItem');
            $type = [];


            $sql           = '';
            if (Tools::getIsset('oesfp_element_type')) {
                $this->selected_element = Tools::getValue('oesfp_element_type');
            }


            /* if (empty($listItem) || count($listItem) == 0) {
                return false;
            } */

            if (count($listItem) == 0) {
                return false;
            }

            $tableName="";
            $fieldName="";

            if ($this->selected_element == 1) {
                $tableName = 'categories';
                $fieldName = 'id_category';
                $type[]  = 'category';
            }

            if ($this->selected_element == 2) {
                $tableName = 'products';
                $fieldName = 'id_product';
                $type[]  = 'product';
            }

            if ($this->selected_element == 3) {
                $tableName = 'suppliers';
                $fieldName = 'id_supplier';
                $type[]  = 'supplier';
            }

            if ($this->selected_element == 4) {
                $tableName = 'cms';
                $fieldName = 'id_cms';
                $type[]  = 'cms';
            }


            if ($this->selected_element == 5) {
                $tableName = 'manufacturers';
                $fieldName = 'id_manufacturer';
                $type[] = 'manufacturer';
            }

            if ($this->selected_element == 6) {
                $tableName = 'prestablog';
                $fieldName = 'id_prestablog';
                $type[] = 'prestablog';
            }

            if ($this->selected_element == 7) {
                $tableName = 'advancedsearch';
                $fieldName = 'id_advancedsearch';
                $type[] = 'advancedsearch';
            }



            foreach ($listItem as $id_item) {
                $isExclude  = (isset($excludeValue[$id_item])) ? 1 : 0;

                $sql =
                    'INSERT INTO `' . bqSQL(_DB_PREFIX_ . 'opartindexnow_' . $tableName) . '`
                                    (`' . bqSQL($fieldName) . '`, id_lang, id_shop, exclude,status,date_upd)
                                VALUES (
                                    ' . (int) $id_item . ',
                                    ' . (int) $id_lang . ',
                                    ' . (int) $this->context->shop->id . ',
                                    ' . (int) $isExclude . ',
                                    0,
                                    "' . date("Y-m-d H:i:s") . '"
                                ) ON DUPLICATE KEY UPDATE
                                    exclude = ' . (int) $isExclude . '';
                /* if (!db::getInstance()->execute($sql)) { */
                if (!Db::getInstance()->execute($sql)) {
                    $this->post_error[] = sprintf(
                        $this->l('An error occured while updating the %s configuration for %s = %s'),
                        $tableName,
                        $fieldName,
                        $id_item
                    );
                }
            }
        }

        //formulaire de renvoie
        if (Tools::getValue('SendIndexNow')) {
            $ids  = Tools::getValue('SendIndexNow');
            foreach ($ids as  $id) {
                $log = $this->getLog($id);
                $this->ResendItem($log);
            }

            /* $module_obj = new OpartIndexnow(); */
            $module_obj = new OpartIndexNow();
            $redirection = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $module_obj->name . '&tab_module=' . $module_obj->tab . '&module_name=' . $module_obj->name . '&token='
                . Tools::getAdminTokenLite('AdminModules') . '&action=soumission';
            Tools::redirect($redirection);
        }


         if (Tools::getValue('SendIndexNowGoogle')) {
            $ids  = Tools::getValue('SendIndexNowGoogle');
            foreach ($ids as  $id) {
                $log = $this->getLogGoogle($id);
                $this->ResendItemGoogle($log);
            }

            $module_obj = new OpartIndexNow();
            $redirection = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $module_obj->name . '&tab_module=' . $module_obj->tab . '&module_name=' . $module_obj->name . '&token='
                . Tools::getAdminTokenLite('AdminModules') . '&action=soumission';
            Tools::redirect($redirection);
        }
    }

    public function getItemsnoIndex()
    {

        $elements = [];
        $i = 0;

        $elements['products'] = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'opartnoindex_products');
        $elements['categories'] = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'opartnoindex_categories');
        $elements['suppliers'] = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'opartnoindex_suppliers');
        $elements['cms'] = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'cms');
        foreach ($elements['cms'] as $cms) {
            $elements['cms'][$i]['id_lang'] = $this->context->language->id;
            $elements['cms'][$i]['id_shop'] = $this->context->shop->id;
            if ($cms['indexation'] == 0) {
                $elements['cms'][$i]['no_index'] = 1;
            } else {
                $elements['cms'][$i]['no_index'] = 0;
            }
            $i++;
        }

        $elements['manufacturers'] = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'opartnoindex_manufacturers');



        foreach ($elements as $key => $element) {
            foreach ($element as $value) {
                switch ($key) {
                    case 'products':
                        $fieldName = 'id_product';
                        break;
                    case 'categories':
                        $fieldName = 'id_category';
                        break;
                    case 'suppliers':
                        $fieldName = 'id_supplier';
                        break;
                    case 'cms':
                        $fieldName = 'id_cms';
                        break;
                    case 'manufacturers':
                        $fieldName = 'id_manufacturer';
                        break;
                }


                $sql =
                    'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_' . $key . '
                            (' . $fieldName . ', id_lang, id_shop, exclude,status,date_upd)
                        VALUES (
                            ' . (int) $value[$fieldName] . ',
                            ' . (int) $value['id_lang'] . ',
                            ' . (int) $value['id_shop'] . ',
                            ' . (int) $value['no_index'] . ',
                            0,
                            "' . date("Y-m-d H:i:s") . '"
                        ) ON DUPLICATE KEY UPDATE
                            exclude = ' . (int) $value['id_lang'] . '';


                /* if (!db::getInstance()->execute($sql)) { */
                if (!Db::getInstance()->execute($sql)) {
                    $this->post_error[] = sprintf(
                        $this->l('An error occured while updating the %s configuration for %s = %s'),
                        $key,
                        $fieldName,
                        $value[$fieldName]
                    );
                }
            }
        }
    }

    public function getItemsVisibilty()
    {
        /* $products = db::getInstance()->executeS('SELECT id_product,id_shop FROM ' . _DB_PREFIX_ . 'product_shop  WHERE visibility = "none"'); */
        $products = Db::getInstance()->executeS('SELECT id_product,id_shop FROM ' . _DB_PREFIX_ . 'product_shop  WHERE visibility = "none"');
        foreach ($products as $product) {
            $sql =
                'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_products
                            (id_product, id_lang, id_shop, exclude,status,date_upd)
                        VALUES (
                            ' . (int) $product['id_product'] . ',
                            ' . (int) $this->context->language->id . ',
                            ' . (int) $product['id_shop'] . ',
                            1,
                            0,
                            "' . date("Y-m-d H:i:s") . '"
                        ) ON DUPLICATE KEY UPDATE
                            exclude = 1';



            /* if (!db::getInstance()->execute($sql)) { */
            if (!Db::getInstance()->execute($sql)) {
                $this->post_error[] = sprintf(
                    $this->l('An error occured while updating the %s configuration for %s = %s'),
                    'opartindexnow_products',
                    'id_product',
                    $product['id_product']
                );
            }
        }
    }


    public function getContent()
    {




        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        $smarty  = $this->context->smarty;


        if (Tools::isSubmit('submit_exclusion') || Tools::isSubmit('submit_url') || Tools::isSubmit('submit_url_google') || Tools::getValue('deletelist')  == 1 || Tools::getValue('deletelistgoogle')  == 1 || Tools::isSubmit('indexnowsendconfig') || Tools::isSubmit('sendmanuellyurl') || Tools::isSubmit('googlesendconfig')) {

            $this->postProcess();
        }


        //genère la clé
        //page exclusion 
        if (Tools::getValue('action') == "key") {



            if (Tools::getValue('generatekey')) {

                $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-';
                $var_size = Tools::strlen($string);
                $key = "";
                for ($x = 0; $x < 128; $x++) {
                    $key .= $string[rand(0, $var_size - 1)];
                }

                Configuration::UpdateValue('OPARTINDEXNOW_KEY', $key);

                $content = $key;
                $fp = fopen(_PS_CORE_DIR_ . "/" . $key . ".txt", "wb");
                fwrite($fp, $content);
                fclose($fp);
            }

            //supp la clé
            if (Tools::getValue('deletekey')) {

                unlink(_PS_CORE_DIR_ . "/" . Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) . '.txt');
                Configuration::deleteByName('OPARTINDEXNOW_KEY');
            }

            $smarty->assign('tabkey', 'tabkey');
        }


        $key = Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id);
        $googlekey = Configuration::get('GOOGLE_INDEXING_KEY', null, null, $this->context->shop->id);
        $consentgoogle = Configuration::get('GOOGLE_INDEXING_CONSENT', null, null, $this->context->shop->id);
        
        $admin_module_url =
            'index.php?controller=AdminModules&configure='
            . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');


        //page exclusion 
        if (Tools::getValue('action') == "exclusion" || Tools::getValue('oesfp_element_type')) {
            // Get item number form filter
            if (Tools::getIsset('item_number') && !empty(Tools::getValue('item_number'))) {
                $item_number = Tools::getValue('item_number');
            } else {
                $item_number = 25;
            }



            if (Tools::getIsset('oesfp_element_type')) {
                $this->selected_element = Tools::getValue('oesfp_element_type');
            }

            //filter
            $start                = 0;
            $limit                = $item_number;
            $selected_page        = 1;
            $id_selected_category = false;
            $excluded         = false;
            $noexcluded        = false;
            $active         = false;
            $notactive        = false;

            //check current page
            if (Tools::getIsset('selected_page') && Tools::getValue('selected_page') != 0) {
                $selected_page = Tools::getValue('selected_page');
            }


            if (Tools::getIsset('selected_category')) {
                $id_selected_category =
                    (Tools::getValue('selected_category') == '-1') ? false : Tools::getValue('selected_category');
            }

            if (Tools::getIsset('selected_status')) {
                if (Tools::getValue('selected_status') == 1) {
                    $excluded = 1;
                } elseif (Tools::getValue('selected_status') == 0) {
                    $noexcluded = 1;
                }
            }

            if (Tools::getIsset('selected_active')) {
                if (Tools::getValue('selected_active') == 1) {
                    $active = 1;
                } elseif (Tools::getValue('selected_active') == 0) {
                    $notactive = 1;
                }
            }





            if($selected_page == ""){
                $start = (1 - 1) * $limit;
            }
            else{
               $start = ($selected_page - 1) * $limit; 
            }
            
            $tree                    = $this->getCategoryTree($this->context->language->id);
            $select_category_options = $this->nested2select($tree, $id_selected_category, '');

            $res = [];

            //load category
            if ($this->selected_element == 1) {
                $nb = $this->countCategories();
                if ($nb == 0 &&  $noexcluded == 1) {
                    $res = $this->getCategories(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_category',
                        'ASC',
                        $id_selected_category
                    );
                } else {

                    $res = $this->getCategories(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_category',
                        'ASC',
                        $id_selected_category,
                        $excluded,
                        $noexcluded,
                        $active,
                        $notactive
                    );
                }

                $categories = $res['rows'];
                $linkClass  = new Link;

                foreach ($categories as &$category) {
                    $category['link'] = $linkClass->getCategoryLink($category['id_category']);
                }

                $smarty->assign('categories', $categories);
            }

            //load product list
            if ($this->selected_element == 2) {

                $nb = $this->countProducts();
                if ($nb == 0 &&  $noexcluded == 1) {
                    $res = $this->getProducts(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_product',
                        'ASC',
                        $id_selected_category
                    );
                } else {
                    $res = $this->getProducts(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_product',
                        'ASC',
                        $id_selected_category,
                        $excluded,
                        $noexcluded,
                        $active,
                        $notactive
                    );
                }



                $products  = $res['rows'];


                $linkClass = new Link;

                foreach ($products as &$product) {
                    $product['id_cover']   = Product::getCover($product['id_product']);
                    $product['cover_link'] = $linkClass->getImageLink(
                        $product['link_rewrite'],
                        $product['id_cover']['id_image']
                    );
                    $product['link']       = $linkClass->getProductLink($product['id_product']);
                }

                $smarty->assign('products', $products);
            }


            //load supplier list
            if ($this->selected_element == 3) {
                $nb = $this->countSuppliers();
                if ($nb == 0 &&  $noexcluded == 1) {
                    $res = $this->getSuppliers(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_supplier',
                        'ASC'
                    );
                } else {
                    $res = $this->getSuppliers(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_supplier',
                        'ASC',
                        $excluded,
                        $noexcluded,
                        $active,
                        $notactive
                    );
                }



                $suppliers = $res['rows'];
                $linkClass = new Link;

                foreach ($suppliers as &$supplier) {
                    $supplier['link'] = $linkClass->getSupplierLink($supplier['id_supplier']);
                }

                $smarty->assign('suppliers', $suppliers);
            }


            //load cms list
            if ($this->selected_element == 4) {
                $nb = $this->countCms();
                if ($nb == 0  &&  $noexcluded == 1) {
                    $res = $this->getCms(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_cms',
                        'ASC'
                    );
                } else {
                    $res = $this->getCms(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_cms',
                        'ASC',
                        $excluded,
                        $noexcluded,
                        $active,
                        $notactive
                    );
                }


                $cmss      = $res['rows'];
                $linkClass = new Link;

                foreach ($cmss as &$cms) {
                    $cms['no_index'] = ($cms['indexation'] == 0) ? 1 : 0;
                    $cms['link']     = $linkClass->getCMSLink($cms['id_cms'], $cms['link_rewrite']);
                }

                $smarty->assign('cmss', $cmss);
            }


            //load manufacturer list
            if ($this->selected_element == 5) {
                $nb = $this->countManufacturers();
                if ($nb == 0  &&  $noexcluded == 1) {

                    $res = $this->getManufacturers(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_manufacturer',
                        'ASC'
                    );
                } else {
                    $res = $this->getManufacturers(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_manufacturer',
                        'ASC',
                        $excluded,
                        $noexcluded,
                        $active,
                        $notactive
                    );
                }

                $manufacturers = $res['rows'];
                $linkClass     = new Link;

                foreach ($manufacturers as &$manufacturer) {
                    $manufacturer['link'] = $linkClass->getManufacturerLink($manufacturer['id_manufacturer']);
                }

                $smarty->assign('manufacturers', $manufacturers);
            }


            //load prestablog list
            if ($this->selected_element == 6) {
                $nb = $this->countPrestablog();
                if ($nb == 0  &&  $noexcluded == 1) {

                    $res = $this->getPrestablog(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_prestablog_news',
                        'ASC'
                    );
                } else {
                    $res = $this->getPrestablog(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_prestablog_news',
                        'ASC',
                        $excluded,
                        $noexcluded,
                        $active,
                        $notactive
                    );
                }

                $articles = $res['rows'];

                foreach ($articles as &$article) {
                    $article['link'] = $this->prestablogUrl($article);
                }




                $smarty->assign('articles', $articles);
            }


            if ($this->selected_element == 7) {
                $nb = $this->countAdvancedSearch();
                if ($nb == 0  &&  $noexcluded == 1) {

                    $res = $this->getAdvancedSearch(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_seo',
                        'ASC'
                    );
                } else {
                    $res = $this->getAdvancedSearch(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_seo',
                        'ASC',
                        $excluded,
                        $noexcluded
                    );
                }

                $articles = $res['rows'];


                foreach ($articles as &$seo) {
                     $params = array(
                        'id_seo' => $seo['id_seo'],
                        'seo_url' => $seo['seo_url'],
                    );
                    $seo['link'] = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', $params);
                }




                $smarty->assign('articles', $articles);
            }

            $prestablog = Module::getInstanceByName('prestablog');
            $advancedsearch = Module::getInstanceByName('pm_advancedsearch4');


            $smarty->assign(array(
                'item_number' => $item_number,
                'limit' => $limit,
                'selected_page' => $selected_page,
                'select_category_options' => $select_category_options,
                'noexclude_only' => $noexcluded,
                'exclude_only' => $excluded,
                'active' => $active,
                'notactive' => $notactive,
                'exclusion' => 'exclusion',
                'totalItem' => $res['totalItem'][0]['totalItem'],
                'id_selected_category' => $id_selected_category,
                'prestablog' => $prestablog,
                'advancedsearch' => $advancedsearch,



            ));
        }


        //load Urls submitted
        if (Tools::getValue('action') == "soumission") {

            $urlssubmitted = $this->getUrlSubmitted();
            $smarty->assign('urlssubmitted', $urlssubmitted);

            $urlssubmittedgoogle = $this->getUrlSubmittedGoogle();
            $smarty->assign('urlssubmittedgoogle', $urlssubmittedgoogle);
        }

        if (Tools::getValue('action') == "manuelly") {

            $urlmanuelly = true;
            $smarty->assign('urlmanuelly', $urlmanuelly);
        }

        if (Tools::getValue('action') == "help") {

            $smarty->assign('help', true);
        }

        if (empty(Tools::getValue('action')) && empty(Tools::getValue('oesfp_element_type'))) {

            $smarty->assign('tabkey', 'tabkey');
        }

        //recupère les valeurs de configuration
        if (Configuration::get('INDEXNOW_LIMITLOGS')) {
            $smarty->assign('limitlogs', Configuration::get('INDEXNOW_LIMITLOGS'));
        }


        
        $smarty->assign('INDEXNOW_SEND_BY_CRON', Configuration::get('INDEXNOW_SEND_BY_CRON'));

        $smarty->assign('INDEXNOW_VISIBILITY_ARGS', Configuration::get('INDEXNOW_VISIBILITY_ARGS'));

        if (Module::getInstanceByName('opartnoindex') && Module::getInstanceByName('opartnoindex')->active == 1) {
            $smarty->assign(array(
                'noindex' => 1,
                'INDEXNOW_NOINDEX_ARGS' => Configuration::get('INDEXNOW_NOINDEX_ARGS')
            ));
        }

        $moduleUrl = $this->context->link->getModuleLink('opartindexnow', 'cron');
        $moduleUrlCron = $this->context->link->getModuleLink('opartindexnow', 'cronsendindexnow');

        $lang = $this->context->language->iso_code;
        $discoverOpartModuleLink = ($lang == 'fr') ? 
            'https://prestashop.pxf.io/y21BPD' :
            'https://prestashop.pxf.io/qz0V1Y';


        $smarty->assign(array(
            'module_local_path' => $this->local_path,
            'admin_module_url' => $admin_module_url,
            'key' => $key,
            'googlekey' => $googlekey,
            'selected_element' => $this->selected_element,
            'limitsend' => Configuration::get('INDEXNOW_LIMITSEND'),
            'urlcron' => $moduleUrl . '?token=' . Tools::substr(Tools::encrypt('opartindexnow/cron'), 0, 10) . '&id_shop=' . $this->context->shop->id,
            'urlcronsendindexnow' => $moduleUrlCron . '?token=' . Tools::substr(Tools::encrypt('opartindexnow/cronsendindexnow'), 0, 10) . '&id_shop=' . $this->context->shop->id,
            'consentgoogle' => $consentgoogle,
            'discoverOpartModuleLink' => $discoverOpartModuleLink
        ));

        $this->html = '';
        $this->html .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');

        return $this->html;
    }


    private function getItemExclude($id, $table, $key)
    {

        /* $sql = 'SELECT exclude FROM ' . _DB_PREFIX_ . 'opartindexnow_' . $table . ' WHERE id_shop = ' . $this->context->shop->id . ' AND id_lang = ' . $this->context->language->id . ' AND ' . $key . ' = ' . $id; */
        $sql = 'SELECT exclude FROM `' . bqSQL(_DB_PREFIX_ . 'opartindexnow_' . $table) . '` WHERE id_shop = ' . (int)$this->context->shop->id . ' AND id_lang = ' . (int)$this->context->language->id . ' AND `' . bqSQL($key) . '` = ' . (int)$id;

        /* $exclude = db::getInstance()->getvalue($sql); */
        $exclude = Db::getInstance()->getvalue($sql);

        return $exclude;
    }

    public function getListsWainting($senday)
    {

        $domain = pSQL(Tools::getShopDomain());

        $limit = Configuration::get('INDEXNOW_LIMITSEND') - $senday;

          Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'opartindexnow_waiting` WHERE id_waiting NOT IN (
            SELECT * FROM (
                SELECT MIN(id_waiting)
                FROM `'._DB_PREFIX_.'opartindexnow_waiting`
                GROUP BY url
            ) AS keep_ids
        );');

        $result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'opartindexnow_waiting     WHERE url LIKE "%' . $domain . '%" GROUP BY url LIMIT 0,' . (int)$limit);

        return $result;
    }




    public function hookActionProductUpdate($params)
    {



        if (!isset($params['product'])) {
            return;
        }

        $id_product = (int) $params['product']->id;
        $exclude = $this->getItemExclude($id_product, 'products', 'id_product');

        $listitem = array($id_product);
        $type = array('product');

        $url = $this->context->link->getProductLink($id_product);
        $nb = $this->checkUrlgetLogs($url);


        if ($exclude != 1 && $params['product']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));

            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_UPDATED');

                if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }




                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_product])) ? $reponse[$id_product] : 403;

                $sql =
                    'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_products
                                            (id_product, id_lang, id_shop,status,date_upd)
                                        VALUES (
                                            ' . (int) $id_product . ',
                                            ' . (int) $this->context->language->id . ',
                                            ' . (int) $this->context->shop->id . ',
                                            ' .  (int) $reponse . ',
                                            "' . date("Y-m-d H:i:s") . '"
                                        ) ON DUPLICATE KEY UPDATE
                                            status = ' . (int) $reponse . ',
                                            date_upd = "' . date("Y-m-d H:i:s") . '"';
                $result = Db::getInstance()->Execute($sql);

                $languages = Language::getLanguages(true,$this->context->shop->id);
                foreach ($languages as $language) {
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . pSQL($params['product']->name[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' .  htmlentities($this->context->link->getProductLink($id_product,null,null,null,$language['id_lang'])) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);

                    if($status != 0){
                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                "' . pSQL($params['product']->name[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                "' .  htmlentities($this->context->link->getProductLink($id_product,null,null,null,$language['id_lang'])) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                    }
                }
                
            } else {


                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['product']->name[$this->context->language->id]) . '",
                                "product",
                                ' . (int)$id_product . ',
                                "' .  htmlentities($this->context->link->getProductLink($id_product)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }

    public function hookactionObjectProductDeleteBefore($params)
    {


        if (!isset($params['object'])) {
            return;
        }

        $id_product = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_product, 'products', 'id_product');

        $url = $this->context->link->getProductLink($id_product);
        $nb = $this->checkUrlgetLogs($url);



        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id)  && $nb < 1) {
            Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE', $this->context->link->getProductLink($params['object']->id));
        }
    }



    public function hookActionProductDelete($params)
    {
        if (!isset($params['product'])) {
            return;
        }

        $id_product = (int) $params['product']->id;
        $exclude = $this->getItemExclude($id_product, 'products', 'id_product');

        $url = $this->context->link->getProductLink($id_product);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_product);
        $type = array('product');

        if ($exclude != 1 && $params['product']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_DELETED');

                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }


                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_product])) ? $reponse[$id_product] : 403;


                Db::getInstance()->delete(
                    'opartindexnow_products',
                    '`id_product` = ' . (int) $id_product . ' AND `id_shop`=' . (int) $this->context->shop->id
                );

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['product']->name[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . date("Y-m-d H:i:s") . '")';

                $result = Db::getInstance()->Execute($sql);

                    if($status != 0){
                        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                "' . pSQL($params['product']->name[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                               "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                    }
            } else {

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                            "' . htmlentities($params['product']->name[$this->context->language->id]) . '",
                                "product",
                                ' . (int)$id_product . ',
                                "' .  htmlentities($this->context->link->getProductLink($id_product)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }


    public function hookActionCategoryUpdate($params)
    {


        if (!isset($params['category'])) {
            return;
        }

        $id_category = (int) $params['category']->id;
        $exclude = $this->getItemExclude($id_category, 'categories', 'id_category');

        $listitem = array($id_category);
        $type = array('category');


        $url = $this->context->link->getCategoryLink($id_category);
        $nb = $this->checkUrlgetLogs($url);



        if ($exclude != 1 && $params['category']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_category])) ? $reponse[$id_category] : 403;

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_UPDATED');

                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }




        
                $sql =
                                'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_categories
                                            (id_category, id_lang, id_shop,status,date_upd)
                                        VALUES (
                                            ' . (int) $id_category . ',
                                            ' . (int) $this->context->language->id . ',
                                            ' . (int) $this->context->shop->id . ',
                                            ' .  (int)$reponse . ',
                                            "' . date("Y-m-d H:i:s") . '"
                                        ) ON DUPLICATE KEY UPDATE
                                            status = ' . (int) $reponse . ',
                                            date_upd = "' . date("Y-m-d H:i:s") . '"';
                $result = Db::getInstance()->Execute($sql);


                
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['category']->name[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' .  htmlentities($this->context->link->getCategoryLink($id_category)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);


                if($status != 0){
                 $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                               "' . htmlentities($params['category']->name[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                "' .  htmlentities($this->context->link->getCategoryLink($id_category)) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }

            } else {
                
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['category']->name[$this->context->language->id]) . '",
                                "category",
                                ' . (int)$id_category . ',
                                "' .  htmlentities($this->context->link->getCategoryLink($id_category)) . '",
                                "' . date("Y-m-d H:i:s") . '")';

                $result = Db::getInstance()->Execute($sql);
            }
        }
    }

    public function hookActionCategoryAdd($params)
    {
        return $this->hookActionCategoryUpdate($params);
    }

    public function hookactionObjectCategoryDeleteBefore($params)
    {


        if (!isset($params['object'])) {
            return;
        }

        $id_category = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_category, 'categories', 'id_category');

        $url = $this->context->link->getCategoryLink($id_category);
        $nb = $this->checkUrlgetLogs($url);

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE', $this->context->link->getCategoryLink($params['object']->id));
        }
    }




    public function hookActionCategoryDelete($params)
    {

        if (!isset($params['category'])) {
            return;
        }

        $id_category = (int) $params['category']->id;
        $exclude = $this->getItemExclude($id_category, 'categories', 'id_category');

        $url = $this->context->link->getCategoryLink($id_category);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_category);
        $type = array('category');


        if ($exclude != 1 && $params['category']->active == 1  && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_category])) ? $reponse[$id_category] : 403;
                Db::getInstance()->delete(
                    'opartindexnow_categories',
                    '`id_category` = ' . (int) $id_category . ' AND `id_shop`=' . (int) $this->context->shop->id
                );

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_DELETED');

                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }



                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['category']->name[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                 $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                               "' . htmlentities($params['category']->name[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

      
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['category']->name[$this->context->language->id]) . '",
                                "category",
                                ' . (int)$id_category . ',
                                "' .  htmlentities($this->context->link->getCategoryLink($id_category)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }

    public function hookActionSupplierUpdate($params){
        return $this->hookActionObjectSupplierUpdateAfter($params);
    }


    public function hookActionObjectSupplierUpdateAfter($params)
    {


        if (!isset($params['object'])) {
            return;
        }

        $id_supplier = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_supplier, 'suppliers', 'id_supplier');

        $url = $this->context->link->getSupplierLink($id_supplier);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_supplier);
        $type = array('supplier');

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_supplier])) ? $reponse[$id_supplier] : 403;


                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_UPDATED');


                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }





                    $sql =
                                'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_suppliers
                                            (id_supplier, id_lang, id_shop,status,date_upd)
                                        VALUES (
                                            ' . (int) $id_supplier . ',
                                            ' . (int) $this->context->language->id . ',
                                            ' . (int) $this->context->shop->id . ',
                                            ' .  (int)$reponse . ',
                                            "' . date("Y-m-d H:i:s") . '"
                                        ) ON DUPLICATE KEY UPDATE
                                            status = ' . (int) $reponse . ',
                                            date_upd = "' . date("Y-m-d H:i:s") . '"';
                $result = Db::getInstance()->Execute($sql);

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                ' .     (int)$reponse . ',
                                "' .  htmlentities($this->context->link->getSupplierLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                 $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                ' .     (int)$status . ',
                                 "' .  htmlentities($this->context->link->getSupplierLink($params['object']->id)) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

        
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                "supplier",
                                ' . (int)$id_supplier . ',
                                "' .  htmlentities($this->context->link->getSupplierLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }


    public function hookactionObjectSupplierDeleteBefore($params)
    {


        if (!isset($params['object'])) {
            return;
        }

        $id_supplier = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_supplier, 'suppliers', 'id_supplier');

        $url = $this->context->link->getSupplierLink($id_supplier);
        $nb = $this->checkUrlgetLogs($url);

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE', $this->context->link->getSupplierLink($params['object']->id));
        }
    }


     public function hookActionSupplierDelete($params){
        return $this->hookActionObjectSupplierDeleteAfter($params);
    }


    public function hookActionObjectSupplierDeleteAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        $id_supplier = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_supplier, 'suppliers', 'id_supplier');

        $url = $this->context->link->getSupplierLink($id_supplier);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_supplier);
        $type = array('supplier');

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_supplier])) ? $reponse[$id_supplier] : 403;

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_DELETED');

                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }


                Db::getInstance()->delete(
                    'opartindexnow_suppliers',
                    '`id_supplier` = ' . (int) $id_supplier . ' AND `id_shop`=' . (int) $this->context->shop->id
                );


                
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . date("Y-m-d H:i:s") . '")';

                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                    (name, status, url,commentaire,date_upd)
                                VALUES (
                                    "' . htmlentities($params['object']->name) . '",
                                    ' .     (int)$status . ',
                                    "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                    "' . pSQL($commentaire) . '",
                                    "' . date("Y-m-d H:i:s") . '")';
                                $result = Db::getInstance()->Execute($sql);
                }

            } else {

    
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                "supplier",
                                ' . (int)$id_supplier . ',
                                "' .  htmlentities($this->context->link->getSupplierLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }


     public function hookActionSupplierAdd($params){
        return $this->hookactionObjectSupplierAddAfter($params);
    }

    public function hookactionObjectSupplierAddAfter($params)
    {
        return $this->hookActionObjectSupplierUpdateAfter($params);
    }

    public function hookActionCmsUpdate($params){
        return $this->hookActionObjectCmsUpdateAfter($params);
    }




    public function hookActionObjectCmsUpdateAfter($params)
    {

        if (!isset($params['object'])) {
            return;
        }


        $id_cms = (int) $params['object']->id;

        $exclude = $this->getItemExclude($id_cms, 'cms', 'id_cms');

        $url = $this->context->link->getCmsLink($id_cms);
        $nb = $this->checkUrlgetLogs($url);


        $listitem = array($id_cms);
        $type = array('cms');

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_cms])) ? $reponse[$id_cms] : 403;

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_UPDATED');

                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }



                $sql =
                                'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_cms
                                            (id_cms, id_lang, id_shop,status,date_upd)
                                        VALUES (
                                            ' . (int) $id_cms . ',
                                            ' . (int) $this->context->language->id . ',
                                            ' . (int) $this->context->shop->id . ',
                                            ' .  (int)$reponse . ',
                                            "' . date("Y-m-d H:i:s") . '"
                                        ) ON DUPLICATE KEY UPDATE
                                            status = ' . (int) $reponse . ',
                                            date_upd = "' . date("Y-m-d H:i:s") . '"';
                $result = Db::getInstance()->Execute($sql);


                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->meta_title[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' .  htmlentities($this->context->link->getCmsLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                 $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->meta_title[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                "' .  htmlentities($this->context->link->getCmsLink($params['object']->id)) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->meta_title[$this->context->language->id]) . '",
                                "cms",
                                ' . (int)$id_cms . ',
                                "' .  htmlentities($this->context->link->getCmsLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }

    public function hookActionCmsDelete($params){
        return $this->hookactionObjectCmsDeleteAfter($params);
    }

    public function hookactionObjectCmsDeleteBefore($params)
    {


        if (!isset($params['object'])) {
            return;
        }

        $id_cms = (int) $params['object']->id;

        $exclude = $this->getItemExclude($id_cms, 'cms', 'id_cms');

        $url = $this->context->link->getCmsLink($id_cms);
        $nb = $this->checkUrlgetLogs($url);

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE', $this->context->link->getCmsLink($params['object']->id));
        }
    }




    public function hookactionObjectCmsDeleteAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        $id_cms = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_cms, 'cms', 'id_cms');

        $url = $this->context->link->getCmsLink($id_cms);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_cms);
        $type = array('cms');

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_cms])) ? $reponse[$id_cms] : 403;


                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_DELETED');

                  if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }





                Db::getInstance()->delete(
                    'opartindexnow_cms',
                    '`id_cms` = ' . (int) $id_cms . ' AND `id_shop`=' . (int) $this->context->shop->id
                );



                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->meta_title[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . date("Y-m-d H:i:s") . '")';

                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->meta_title[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                 "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->meta_title[$this->context->language->id]) . '",
                                "cms",
                                ' . (int)$id_cms . ',
                                "' .  htmlentities($this->context->link->getCmsLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }

     public function hookActionCmsAdd($params){
        return $this->hookactionObjectCmsAddAfter($params);
    }

    public function hookactionObjectCmsAddAfter($params)
    {
        return $this->hookActionObjectCmsUpdateAfter($params);
    }


    public function hookActionManufacturerUpdate($params){
        return $this->hookactionObjectManufacturerUpdateAfter($params);
    }


    public function hookactionObjectManufacturerUpdateAfter($params)
    {

        if (!isset($params['object'])) {
            return;
        }

        $id_manufacturer = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_manufacturer, 'manufacturers', 'id_manufacturer');

        $url = $this->context->link->getManufacturerLink($id_manufacturer);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_manufacturer);
        $type = array('manufacturer');

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }


            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_manufacturer])) ? $reponse[$id_manufacturer] : 403;



                $sql =
                                'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_manufacturers
                                            (id_manufacturer, id_lang, id_shop,status,date_upd)
                                        VALUES (
                                            ' . (int) $id_manufacturer . ',
                                            ' . (int) $this->context->language->id . ',
                                            ' . (int) $this->context->shop->id . ',
                                            ' .  (int)$reponse . ',
                                            "' . date("Y-m-d H:i:s") . '"
                                        ) ON DUPLICATE KEY UPDATE
                                            status = ' . (int) $reponse . ',
                                            date_upd = "' . date("Y-m-d H:i:s") . '"';
                $result = Db::getInstance()->Execute($sql);

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                ' .     (int)$reponse . ',
                                "' .  htmlentities($this->context->link->getManufacturerLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                  "' . htmlentities($params['object']->name) . '",
                                ' .     (int)$reponse . ',
                                 "' .  htmlentities($this->context->link->getManufacturerLink($params['object']->id)) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
            } else {

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                "manufacturer",
                                ' . (int)$id_manufacturer . ',
                                "' .  htmlentities($this->context->link->getManufacturerLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }


    public function hookactionObjectManufacturerDeleteBefore($params)
    {


        if (!isset($params['object'])) {
            return;
        }

        $id_manufacturer = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_manufacturer, 'manufacturers', 'id_manufacturer');

        $url = $this->context->link->getManufacturerLink($id_manufacturer);
        $nb = $this->checkUrlgetLogs($url);

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE', $this->context->link->getManufacturerLink($params['object']->id));
        }
    }

        public function hookActionManufacturerDelete($params){
        return $this->hookactionObjectManufacturerDeleteAfter($params);
    }



    public function hookactionObjectManufacturerDeleteAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        $id_manufacturer = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_manufacturer, 'manufacturers', 'id_manufacturer');

        $url = $this->context->link->getManufacturerLink($id_manufacturer);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_manufacturer);
        $type = array('manufacturer');

        if ($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_manufacturer])) ? $reponse[$id_manufacturer] : 403;

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_DELETED');

                  if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }

                Db::getInstance()->delete(
                    'opartindexnow_manufacturers',
                    '`id_manufacturer` = ' . (int) $id_manufacturer . ' AND `id_shop`=' . (int) $this->context->shop->id
                );


                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . date("Y-m-d H:i:s") . '")';

                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                  "' . htmlentities($params['object']->name) . '",
                                ' .     (int)$status . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

      
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->name) . '",
                                "manufacturer",
                                ' . (int)$id_manufacturer . ',
                                "' .  htmlentities($this->context->link->getManufacturerLink($params['object']->id)) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }

      public function hookActionManufacturerAdd($params){
        return $this->hookactionObjectManufacturerAddAfter($params);
    }
    

    public function hookactionObjectManufacturerAddAfter($params)
    {
        return $this->hookactionObjectManufacturerUpdateAfter($params);
    }

     public function hookactionObjectNewsClassAddAfter($params)
    {
        return $this->hookactionObjectNewsClassUpdateAfter($params);
    }



     public function hookactionObjectNewsClassUpdateAfter($params){

        if (!isset($params['object'])) {
            return;
        }

        $id_prestablog = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_prestablog, 'prestablog', 'id_prestablog');

        $article = $this->getArticlePrestablog($id_prestablog);
        

        $url = $this->prestablogUrl($article);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_prestablog);
        $type = array('prestablog');


        if ($exclude != 1 && $params['object']->actif == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }


            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type,$url);
                $reponse  = (isset($reponse[$id_prestablog])) ? $reponse[$id_prestablog] : 403;


                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_UPDATED');

                  if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }



                $sql =
                                'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_prestablog
                                            (id_prestablog, id_lang, id_shop,status,date_upd)
                                        VALUES (
                                            ' . (int) $id_prestablog . ',
                                            ' . (int) $this->context->language->id . ',
                                            ' . (int) $this->context->shop->id . ',
                                            ' .  (int)$reponse . ',
                                            "' . date("Y-m-d H:i:s") . '"
                                        ) ON DUPLICATE KEY UPDATE
                                            status = ' . (int) $reponse . ',
                                            date_upd = "' . date("Y-m-d H:i:s") . '"';
                $result = Db::getInstance()->Execute($sql);

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' .  htmlentities($url) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                  "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                "' .  htmlentities($url) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                "prestablog",
                                ' . (int)$id_prestablog . ',
                                "' .  htmlentities($url) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }


     public function hookactionObjectNewsClassDeleteBefore($params)
    {


        if (!isset($params['object'])) {
            return;
        }

        $id_prestablog = (int) $params['object']->id;
         $exclude = $this->getItemExclude($id_prestablog, 'prestablog', 'id_prestablog');

        $article = $this->getArticlePrestablog($id_prestablog);
        $url = $this->prestablogUrl($article);

        $nb = $this->checkUrlgetLogs($url);

        if ($exclude != 1 && $params['object']->actif == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id)) {
            Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE', $url);
        }
    }





    public function hookactionObjectNewsClassDeleteAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        $id_prestablog = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_prestablog, 'prestablog', 'id_prestablog');

        $article = $this->getArticlePrestablog($id_prestablog);
        $url = $this->prestablogUrl($article);
        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_prestablog);
        $type = array('prestablog');


        if ($exclude != 1 && $params['object']->actif == 1 && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id)) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type,$url);
                $reponse  = (isset($reponse[$id_prestablog])) ? $reponse[$id_prestablog] : 403;

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_DELETED');

                if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }

                Db::getInstance()->delete(
                    'opartindexnow_prestablog',
                    '`id_prestablog` = ' . (int) $id_prestablog . ' AND `id_shop`=' . (int) $this->context->shop->id
                );


                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . date("Y-m-d H:i:s") . '")';


                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                 "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                               "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

      
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                "prestablog",
                                ' . (int)$id_prestablog . ',
                                "' .  htmlentities($url) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }

    public function hookactionObjectUpdateAfter($params){

        $classname = get_class($params['object']);

        if($classname =='AdvancedSearch\Models\Seo'){
            return $this->hookactionObjectAdvancedSearchSeoClassUpdateAfter($params);
        }
        
    }

    public function hookactionObjectAddAfter($params){

        $classname = get_class($params['object']);

        if($classname =='AdvancedSearch\Models\Seo'){
            return $this->hookactionObjectAdvancedSearchSeoClassUpdateAfter($params);
        }
        
    }

     public function hookactionObjectDeleteBefore($params){

        $classname = get_class($params['object']);

        if($classname =='AdvancedSearch\Models\Seo'){
            return $this->hookactionObjectAdvancedSearchSeoClassDeleteBefore($params);
        }
        
    }

     public function hookactionObjectDeleteAfter($params){

        $classname = get_class($params['object']);

        if($classname =='AdvancedSearch\Models\Seo'){
            return $this->hookactionObjectAdvancedSearchSeoClassDeleteAfter($params);
        }
        
    }


     public function hookactionObjectAdvancedSearchSeoClassAddAfter($params)
    {
        return $this->hookactionObjectAdvancedSearchSeoClassUpdateAfter($params);
    }



     public function hookactionObjectAdvancedSearchSeoClassUpdateAfter($params){


        if (!isset($params['object'])) {
            return;
        }

        $id_advancedsearch = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_advancedsearch, 'advancedsearch', 'id_advancedsearch');

         $paramsurl = array(
            'id_seo' => $params['object']->id_seo,
             'seo_url' =>$params['object']->seo_url,
        );
        
        $url = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', $paramsurl);
        

        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_advancedsearch);
        $type = array('advancedsearch');




        if ($exclude != 1  && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id) && $nb < 1) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }


            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_advancedsearch])) ? $reponse[$id_advancedsearch] : 403;

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_UPDATED');

                 if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }

                $sql =
                                'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_advancedsearch
                                            (id_advancedsearch, id_lang, id_shop,status,date_upd)
                                        VALUES (
                                            ' . (int) $id_advancedsearch . ',
                                            ' . (int) $this->context->language->id . ',
                                            ' . (int) $this->context->shop->id . ',
                                            ' .  (int)$reponse . ',
                                            "' . date("Y-m-d H:i:s") . '"
                                        ) ON DUPLICATE KEY UPDATE
                                            status = ' . (int) $reponse . ',
                                            date_upd = "' . date("Y-m-d H:i:s") . '"';
                $result = Db::getInstance()->Execute($sql);

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                "' .  htmlentities($url) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                 "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' .  htmlentities($url) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                "advancedsearch",
                                ' . (int)$id_advancedsearch . ',
                                "' .  htmlentities($url) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }


     public function hookactionObjectAdvancedSearchSeoClassDeleteBefore($params)
    {


        if (!isset($params['object'])) {
            return;
        }

       $id_advancedsearch = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_advancedsearch, 'advancedsearch', 'id_advancedsearch');

         $paramsurl = array(
            'id_seo' => $params['object']->id_seo,
             'seo_url' =>$params['object']->seo_url,
        );
        
        $url = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', $paramsurl);
        

        $nb = $this->checkUrlgetLogs($url);

        if ($exclude != 1  && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id)) {
            Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE', $url);
        }
    }





    public function hookactionObjectAdvancedSearchSeoClassDeleteAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

       $id_advancedsearch = (int) $params['object']->id;
        $exclude = $this->getItemExclude($id_advancedsearch, 'advancedsearch', 'id_advancedsearch');

         $paramsurl = array(
            'id_seo' => $params['object']->id_seo,
             'seo_url' =>$params['object']->seo_url,
        );
        
        $url = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', $paramsurl);
        

        $nb = $this->checkUrlgetLogs($url);

        $listitem = array($id_advancedsearch);
        $type = array('advancedsearch');


        if ($exclude != 1  && Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id)) {
            $sendday = $this->countSend(date("Y-m-d"));
            $lastping = new DateTime($this->lastSend());

            if ($sendday > 0) {
                $now =  new DateTime('now');
                $interval = $lastping->diff($now);
                $minute = $interval->format('%i');
            } else {
                $minute = 2;
            }

            if ($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1) {
                $items = [];
                $reponse = $this->SendIndexNow($listitem, $items, $type);
                $reponse  = (isset($reponse[$id_advancedsearch])) ? $reponse[$id_advancedsearch] : 403;

                $indexing = new GoogleIndexingService();
                $result = $indexing->notifyUrl($url, 'URL_DELETED');

                  if (isset($result['urlNotificationMetadata'])) {
                    $status = 202;
                    $commentaire = $this->l('Submitted to Google Indexing API');
                } elseif (isset($result['error'])) {
                    $status = (int)$result['error']['code'];
                    $commentaire = '[ERROR] '.$result['error']['message'];
                } else {
                    $status = 0;
                }


                Db::getInstance()->delete(
                    'opartindexnow_advancedsearch',
                    '`id_advancedsearch` = ' . (int) $id_advancedsearch . ' AND `id_shop`=' . (int) $this->context->shop->id
                );


                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$reponse . ',
                                "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . date("Y-m-d H:i:s") . '")';


                $result = Db::getInstance()->Execute($sql);

                if($status != 0){
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_google_logs
                                (name, status, url,commentaire,date_upd)
                            VALUES (
                                 "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                ' .     (int)$status . ',
                                  "' . htmlentities(Configuration::get('OPARTINDEXNOW_URL_DELETE')) . '",
                                "' . pSQL($commentaire) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                            $result = Db::getInstance()->Execute($sql);
                }
            } else {

      
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . htmlentities($params['object']->title[$this->context->language->id]) . '",
                                "advancedsearch",
                                ' . (int)$id_advancedsearch . ',
                                "' .  htmlentities($url) . '",
                                "' . date("Y-m-d H:i:s") . '")';
                $result = Db::getInstance()->Execute($sql);
            }
        }
    }


    public function hookdisplayAdminAfterHeader()
    {
        if (!Configuration::get('OPARTINDEXNOW_KEY', null, null, $this->context->shop->id)) {
            /* $module_obj = new OpartIndexnow(); */
            $module_obj = new OpartIndexNow();
            $redirection = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $module_obj->name . '&tab_module=' . $module_obj->tab . '&module_name=' . $module_obj->name . '&token='
                . Tools::getAdminTokenLite('AdminModules');
            $this->smarty->assign('redirection', $redirection);
            return $this->display(__FILE__, 'notif.tpl');
        }
        if ($this->active && $this->getLastlog() == "403") {
            /* $module_obj = new OpartIndexnow(); */
            $module_obj = new OpartIndexNow();
            $redirection = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $module_obj->name . '&tab_module=' . $module_obj->tab . '&module_name=' . $module_obj->name . '&token='
                . Tools::getAdminTokenLite('AdminModules');
            $this->smarty->assign('redirection', $redirection);
            return $this->display(__FILE__, 'notifstatus.tpl');
        }
    }





    public function getCategoryTree($id_lang)
    {
        $sql = 'SELECT c.id_category, c.id_parent, cl.name
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . '
            WHERE 1 ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '');

        $result  = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $dataset = array();
        foreach ($result as $value) {
            $dataset[$value['id_category']] = $value;

            if (!is_numeric($value['id_parent']) || $value['id_parent'] == 0) {
                $dataset[$value['id_category']]['id_parent'] = null;
            }
        }

        $tree = array();
        foreach ($dataset as $id => &$node) {
            if ($node['id_parent'] === null || $node['id_parent'] == '' || !is_numeric($node['id_parent'])) {
                $tree[$id] = &$node;
            } else {
                if (!isset($dataset[$node['id_parent']]['children'])) {
                    $dataset[$node['id_parent']]['children'] = array();
                }

                $dataset[$node['id_parent']]['children'][$id] = &$node;
            }
        }

        return $tree;
    }


    public function nested2select($data, $selected_category, $spaces = '')
    {
        $result = array();
        if (sizeof($data) > 0) {
            foreach ($data as $entry) {
                $selected = ($entry['id_category'] == $selected_category) ? 'selected="selected"' : '';
                $child =
                    (isset($entry['children'])) ? $this->nested2select(
                        $entry['children'],
                        $selected_category,
                        $spaces . '&nbsp;'
                    ) : '';
                $result[] = sprintf(
                    '<option value="%s" ' . $selected . '>' . $spaces . '%s </option>%s',
                    $entry['id_category'],
                    $entry['name'],
                    $child
                );
            }
        }

        return implode($result);
    }
}
