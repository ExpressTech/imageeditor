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

ob_start();

include dirname(__FILE__).'/../../config/config.inc.php';
include dirname(__FILE__).'/etimageeditor.php';
define('__ET_API_INC__', true);

include dirname(__FILE__).'/api.php';

if (Tools::substr(Tools::encrypt('etimageeditor/index'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('etimageeditor')) {
    die('Bad token');
}

function nl()
{
    if (PHP_SAPI === 'cli') {
        return PHP_EOL;
    } else {
        return '<BR/>';
    }
}

//find images which are not optmized
$replace_orig = Configuration::get('ET_EI_IMG_REPLACE');
// echo $replace_orig;exit;
$use_mozjpeg = Configuration::get('ET_EI_IMG_MOZJPEG');
// echo $use_mozjpeg;exit;
$quality = Configuration::get('ET_EI_IMG_QUALITY');
// echo $quality;exit;
$images = Image::getAllImages();
foreach ($images as $image) {
    $id_image = $image['id_image'];

    $img_db_row = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'et_image_manager` WHERE id_image = '.(int) $id_image.' and error = 0');

    if ($img_db_row) {
        //this image is completed
        continue;
    }

    $result = api(
        'optimizeImage',
        array('id_image' => $id_image, 'replace_orig' => $replace_orig, 'quality' => $quality, 'use_mozjpeg' => $use_mozjpeg)
    );

    $error = 0;
    $status = '';

    if ($result['error']) {
        $error = 1;
        $status = $result['error_long'];
        // $result['src_size'] = 0;
        // $result['dest_size'] = 0;
        // $result['percent'] = 0;
    }

    Db::getInstance()->execute('REPLACE INTO `'._DB_PREFIX_.'et_image_manager` (id_image, src_size, dest_size, percent, error, status, api) VALUES ('.(int) $id_image.', '.(int) $result['src_size'].', '.(int) $result['dest_size'].', '.(int) $result['percent'].', '.(int) $result['error'].', "'.pSQL($status).'", "'.pSQL($result['api']).'")');

    echo $id_image.'.jpg'.' Size : '.$result['src_size'].' Compression : '.$result['percent'].' Error : '.($error ? $status : 'No').nl();

    echo Db::getInstance()->getMsgError();

    ob_flush();
    // if((int) $id_image == 10) {
    // 	break;
    // }
    // var_dump($result);
    // break;
}
