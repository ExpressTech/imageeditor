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

include dirname(__FILE__).'/../../config/config.inc.php';
include dirname(__FILE__).'/libraries/functions.php';

$api_method = null;

if (defined('__ET_API_INC__')) {
    //included
} elseif (Tools::getIsset('method')) {
    $api_method = Tools::getValue('method');
} else {
    die('Invalid Input');
}

if (defined('IMAGETYPE_UNKNOWN')) {
    define('IMAGETYPE_UNKNOWN', 0);
}

function getValue($var, $params)
{
    if (defined('__ET_API_INC__')) {
        if (!$params) {
            return null;
        }

        return $params[$var] ? $params[$var] : null;
    } else {
        return Tools::getValue($var);
    }
}

function api($api_method, $params = null)
{
    $context = Context::getContext();

    switch ($api_method) {

        case 'saveImage':
            $id_image = getValue('id_image', $params);
            $image = getValue('image', $params);
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $image = img_decode($image);
            $img = new Image($id_image);
            $image_path = _PS_PROD_IMG_DIR_.$img->getExistingImgPath().'.jpg';
            file_put_contents($image_path, $image);

            return array($image_path);
            break;
        case 'regenerateThumbnails':
            $id_image = getValue('id_image', $params);
            $dir = _PS_PROD_IMG_DIR_;
            // foreach (Image::getAllImages() as $image) {
            $imageObj = new Image($id_image);
            $existing_img = $dir.$imageObj->getExistingImgPath();
            $existing_img_w_ext = $dir.$imageObj->getExistingImgPath().'.jpg';
            // var_dump($imageObj->image_format);exit;
            // Getting format generation
            $type = 'products';
            $formats = ImageType::getImagesTypes($type);
            $errors = array();

            $generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');
            // var_dump($formats)

            //generating thumbails
            if (file_exists($existing_img_w_ext) && filesize($existing_img_w_ext)) {
                foreach ($formats as $imageType) {
                    // if (!file_exists($dir.$imageObj->getExistingImgPath().'-'.Tools::stripslashes($imageType['name']).'.jpg')) {

                    if (!ImageManager::resize($existing_img.'.jpg', $existing_img.'-'.Tools::stripslashes($imageType['name']).'.jpg', (int) $imageType['width'], (int) $imageType['height'], $imageObj->image_format)) {
                        $errors[] = sprintf(Tools::displayError('Original image is corrupt (%s) for product ID %2$d or bad permission on folder'), $existing_img, (int) $imageObj->id_product);
                    }

                    if ($generate_hight_dpi_images) {
                        if (!ImageManager::resize($existing_img, $dir.$imageObj->getExistingImgPath().'-'.Tools::stripslashes($imageType['name']).'2x.jpg', (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $imageObj->image_format)) {
                            $errors[] = sprintf(Tools::displayError('Original image is corrupt (%s) for product ID %2$d or bad permission on folder'), $existing_img, (int) $imageObj->id_product);
                        }
                    }
                    // }
                }
            } else {
                $errors[] = sprintf(Tools::displayError('Original image is missing or empty (%1$s) for product ID %2$d'), $existing_img, (int) $imageObj->id_product);
            }

            return array(1);

            // var_dump($errors);

            //TODO : Watermark generation.

            // if (time() - $this->start_time > $this->max_execution_time - 4) { // stop 4 seconds before the tiemout, just enough time to process the end of the page on a slow server
            //     return 'timeout';
            // }
            // }
            break;
        case 'optimizeImage':
            $id_image = getValue('id_image', $params);
            $replace_orig = getValue('replace_orig', $params);
            $use_mozjpeg = getValue('use_mozjpeg', $params);
            $quality = getValue('quality', $params); //getValue('quality');
            // echo $quality;return;
            $img = new Image($id_image);
            $image_path = _PS_PROD_IMG_DIR_.$img->getExistingImgPath().'.jpg';
            if (function_exists('exif_imagetype')) {
                $image_type = exif_imagetype($image_path);
            } else {
                $image_type = IMAGETYPE_UNKNOWN;
            }

            $api = '';
            $post = '';
            // echo $image_path;
            // echo $image_type;exit;
            if ($image_type === IMAGETYPE_JPEG && $use_mozjpeg) {
                $api = 'mozjpeg.codelove.de';
                $url = 'https://mozjpeg.codelove.de/upload.php?quality='.$quality.'&option=lossy';
                $post = array('file' => '@'.$image_path);
            } elseif ($image_type === IMAGETYPE_PNG) {
                $api = 'resmush.it';
                $url = 'http://api.resmush.it/ws.php?qlty='.$quality;
                $post = array('files' => '@'.$image_path);
            } else {
                $api = 'resmush.it';
                $url = 'http://api.resmush.it/ws.php?qlty='.$quality;
                $post = array('files' => '@'.$image_path);
            }
            // echo $url;exit;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $data = curl_exec($ch);
            curl_close($ch);
            $o = Tools::jsonDecode($data, true);

            //neutralize api outputs
            if ($api == 'mozjpeg.codelove.de') {
                // $o['src'] = '';
                $o['dest'] = 'https://mozjpeg.codelove.de/'.$o['file'];
                $o['dest_size'] = $o['newsize'];
                $o['src_size'] = $o['oldsize'];
                if ($o['newsize'] == 0 || $o['src_size'] === $o['dest_size']) {
                    $o['percent'] = 0;
                } else {
                    $o['percent'] = (int) round(100 * (($o['src_size'] - $o['dest_size']) / $o['src_size']));
                }

                if ($o['newsize'] == 0) {
                    $o['error'] = true;
                }
            }

            if (!isset($o['error'])) {
                //update image

                if ($o['dest_size'] >= $o['src_size']) {
                    $o['error'] = 1;
                    $o['error_long'] = 'New size greater than or equal to old size. Skipped.';
                } else {
                    if (!$replace_orig) {
                        //take backup of the orig file
                        $tmp_path = dirname(__FILE__).'/tmp/'.$img->id.'.jpg';
                        rename($image_path, $tmp_path);
                    }

                    $dest = Tools::file_get_contents($o['dest']);
                    //write the new compressed file into original location
                    if (file_put_contents($image_path, $dest) === false) {
                        $o['error'] = 1;
                        $o['error_long'] = 'Permission error on '.$id_image.'.jpg';
                    } else {
                        //generate thumbnails
                        api('regenerateThumbnails', array('id_image' => $id_image));
                    }

                    //put the orig back
                    if (!$replace_orig) {
                        rename($tmp_path, $image_path);
                    }
                }
            } else {
                if ($api == 'mozjpeg.codelove.de') {
                    $o['error_long'] = 'Invalid file or some other error.';
                }
            }

            $o['api'] = $api;
            // var_dump($o);
            return $o;

            break;
        case 'getImagePath':
            $id_image = getValue('id_image', $params);
            $img = new Image($id_image);
            $img_path = $context->shop->getBaseURL(true).'img/p/'.$img->getExistingImgPath().'.jpg';

            return array($img_path);
            break;

        default:
            break;
    }
}

if ($api_method) {
    echo Tools::jsonEncode(api($api_method));
}
