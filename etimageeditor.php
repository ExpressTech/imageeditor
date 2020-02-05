<?php
/**
* 2007-2015 PrestaShop.
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
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Etimageeditor extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'etimageeditor';
        $this->tab = 'administration';
        $this->version = '1.1.7';
        $this->author = 'Express Tech';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '6905193f31e8f52098e21152eb296bbd';

        parent::__construct();

        $this->displayName = $this->l('Express Image Manager and Editor');
        $this->description = $this->l('Feature rich and fastest in-place image manager for Prestashop.');
    }

    public function install()
    {
        Configuration::updateValue('ET_EI_IMG_REPLACE', 0);
        Configuration::updateValue('ET_EI_IMG_MOZJPEG', 1);
        Configuration::updateValue('ET_EI_IMG_QUALITY', 90);

        include dirname(__FILE__).'/sql/install.php';

        return parent::install();
    }

    public function uninstall()
    {
        include dirname(__FILE__).'/sql/uninstall.php';

        return parent::uninstall();
    }

    private function showETAd()
    {
        if (Tools::getIsset('close_et_ad')) {
            Configuration::updateValue('EXPRESSTECH_AD'.md5($this->name.$this->version), 'hide');
        }

        if (Configuration::get('EXPRESSTECH_AD'.md5($this->name.$this->version)) !== 'hide') {
            $protocol = strpos(Tools::strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === false ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $script = $_SERVER['SCRIPT_NAME'];
            $params = $_SERVER['QUERY_STRING'];

            $url = $protocol.'://'.$host.$script.'?'.$params;

            $query = parse_url($url, PHP_URL_QUERY);

            $url .= '&close_et_ad=1';

            $this->context->smarty->assign(
                array(
                    'EXPRESSTECH_MODULE_NAME' => $this->name,
                    'EXPRESSTECH_MODULE_URL' => $url,
                )
            );

            if (file_exists($this->local_path.'views/templates/admin/etad_banner.tpl')) {
                return $this->context->smarty->fetch($this->local_path.'views/templates/admin/etad_banner.tpl');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    private function showETToolbarAd()
    {
        $this->context->smarty->assign(
            array(
                'EXPRESSTECH_MODULE_NAME' => $this->name,
            )
        );

        if (file_exists($this->local_path.'views/templates/admin/etad_banner.tpl')) {
            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/etad_toolbar.tpl');
        } else {
            return '';
        }
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        $lang_code = $this->context->language->iso_code;
        $image_upload_url = $this->context->link->getAdminLink('AdminProducts').'&ajax=1&id_product=';
        $image_upload_url_suffix = '&action=addProductImage';

        $adminproducts_id = Tab::getIdFromClassName('AdminProducts');
        $adminproducts_token = Tools::getAdminToken('AdminProducts'.(int) $adminproducts_id.(int) $this->context->employee->id);

        $this->context->controller->addJS($this->_path.'libraries/list.min.js');
        $this->context->controller->addJS($this->_path.'libraries/jquery.form.js');
        $this->context->controller->addJS($this->_path.'libraries/progressbar.min.js');
        $this->context->controller->addJS($this->_path.'libraries/jquery.lazyload.min.js');
        if (Tools::getShopProtocol() == 'https://') {
            $this->context->controller->addJS('https://dme0ih8comzn4.cloudfront.net/imaging/v3/editor.js');
        } else {
            $this->context->controller->addJS('http://feather.aviary.com/imaging/v3/editor.js');
        }
        $this->context->controller->addCSS($this->_path.'views/css/back.css');

        $this->context->smarty->assign(
            array(
                'et_editor_lang' => $lang_code,
                'et_shop_url' => $this->context->shop->getBaseURL(),
                'et_id_shop' => $this->context->shop->id,
                'et_upload_image_url' => $image_upload_url,
                'et_upload_image_url_suffix' => $image_upload_url_suffix,
                'et_img_root' => $this->context->shop->getBaseURL(true).'img/p/',
                'et_adminproducts_token' => $adminproducts_token,
                // 'et_ad_url' => $this->showETAd(),
            )
        );

        $products = Product::getProducts((int) $this->context->language->id, 0, 0, 'id_product', 'ASC');

        $gallery_images = array();
        $smarty_out = array();

        foreach ($products as $product) {
            $product_name = $product['name'];
            $product_ref = $product['reference'];
            $id_product = $product['id_product'];
            $p = new Product($id_product);
            $images = $p->getImages((int) $this->context->cookie->id_lang);
            // var_dump($images);

            $p_images = array();

            foreach ($images as $image) {
                $img = new Image($image['id_image']);
                $img_path = $this->context->shop->getBaseURL(true).'img/p/'.$img->getExistingImgPath().'.jpg';
                $p_images[] = array('id_image' => $image['id_image'], 'path' => $img_path, 'cover' => $img->cover);
            }

            $cover = Image::getCover($id_product);

            $img = new Image($cover['id_image']);
            $cover_path = $this->context->shop->getBaseURL(true).'img/p/'.$img->getExistingImgPath().'.jpg';

            $cover_link = $this->context->link->getImageLink($product['link_rewrite'], $cover['id_image'], ImageType::getFormatedName('thickbox'));

            $smarty_out[] = array(
                'id_product' => $id_product,
                'cover_link' => $cover_link,
                'product_name' => $product_name,
                'product_ref' => $product_ref,
                'p_images' => Tools::jsonEncode($p_images),
            );

            $gallery_images[$id_product] = $p_images;
        }

        $output = '';

        if (((bool) Tools::isSubmit('submitEtimageeditorModule')) == true) {
            if (($error = $this->postProcess()) === true) {
                $output .= $this->displayConfirmation($this->l('The settings have been updated.'));
            } else {
                $output .= $error;
            }
            
        }

        $this->context->smarty->assign('product_images', $smarty_out);
        $this->context->smarty->assign('mod_settings', $this->getSettings());
        $this->context->smarty->assign('gallery_images', Tools::jsonEncode($gallery_images));

        $this->context->controller->addJS($this->_path.'views/js/back.js');

        return $output.$this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
    }

    /**
     * Load the configuration form.
     */
    public function getSettings()
    {
        /*
         * If values have been submitted in the form, process.
         */

        if (function_exists('exif_imagetype')) {
            // Configuration::updateValue('ET_EI_IMG_MOZJPEG', true);
            $this->context->smarty->assign('exif_imagetype', true);
        } else {
            Configuration::updateValue('ET_EI_IMG_MOZJPEG', false);
            $this->context->smarty->assign('exif_imagetype', false);
        }

        $module_url = Tools::getProtocol(Tools::usingSecureMode()).$_SERVER['HTTP_HOST'].$this->getPathUri();
        $cron_url = $module_url.'opt.php'.'?token='.Tools::substr(Tools::encrypt('etimageeditor/index'), 0, 10).'&id_shop='.$this->context->shop->id;

        $images = Image::getAllImages();

        $images_count = count($images);

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT COUNT(`id_image`) AS count , AVG(percent) as avg_compression, SUM(src_size) - SUM(dest_size) AS space_saved FROM `'._DB_PREFIX_.'et_image_manager`');
        // echo Db::getInstance()->getMsgError();

        $images_processed = $res['count'];
        $avg_compression = $res['avg_compression'];
        $space_saved = $res['space_saved'] / 1000;

        $this->context->smarty->assign(
            array(
                'cron_url' => $cron_url,
                'images_count' => $images_count,
                'images_processed' => $images_processed,
                'avg_compression' => round($avg_compression),
                'space_saved' => $space_saved,
            )
        );

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/settings.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEtimageeditorModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $mozjpeg_disabled = true;
        if (function_exists('exif_imagetype')) {
            $mozjpeg_disabled = false;
        }

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Optimization Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Replace Original Image'),
                        'name' => 'ET_EI_IMG_REPLACE',
                        'is_bool' => true,
                        'desc' => $this->l('Cron job will replace the original image file or else just regenerates thumbnails'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable mozjpeg'),
                        'name' => 'ET_EI_IMG_MOZJPEG',
                        'is_bool' => true,
                        'disabled' => $mozjpeg_disabled,
                        'desc' => $this->l('Use mozjpeg.codelove.de for JPEG images and resmush.it for PNG images. Disable this to use resmush.it only!'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        // 'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Optimization quality'),
                        'name' => 'ET_EI_IMG_QUALITY',
                        'label' => $this->l('Quality'),
                    ),
                    // array(
                    //     // 'col' => 3
                    //     'type' => 'text',
                    //     'name' => 'ET_EI_IMG_PROCESSED',
                    //     'label' => $this->l('Images Processed'),
                    // ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'ET_EI_IMG_QUALITY' => Configuration::get('ET_EI_IMG_QUALITY'),
            'ET_EI_IMG_REPLACE' => Configuration::get('ET_EI_IMG_REPLACE'),
            'ET_EI_IMG_MOZJPEG' => Configuration::get('ET_EI_IMG_MOZJPEG'),

        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if ($key == 'ET_EI_IMG_QUALITY' && !is_numeric(Tools::getValue($key))) {
                return $this->displayError($this->l('Image Quality accepts only numeric input'));
            }
            
            Configuration::updateValue($key, Tools::getValue($key));
        }

        return true;

    }
}
