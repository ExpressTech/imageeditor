{*
* 2007-2015 PrestaShop
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
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script> 
//vars
var et_editor_lang = '{$et_editor_lang|escape:"htmlall":"UTF-8"}';
var et_shop_url = '{$et_shop_url|escape:"url":"UTF-8"}';
var et_id_shop = '{$et_id_shop|escape:"htmlall":"UTF-8"}';
var et_upload_image_url = '{$et_upload_image_url|escape:"url":"UTF-8"}';
var et_upload_image_url_suffix = '{$et_upload_image_url_suffix|escape:"url":"UTF-8"}';
var et_img_root = '{$et_img_root|escape:"url":"UTF-8"}';
var et_adminproducts_token = '{$et_adminproducts_token|escape:"htmlall":"UTF-8"}';
var ie_gallery_images  = JSON.parse(decodeURIComponent('{$gallery_images|escape:"url":"UTF-8"}'));

//lang
var et_lang = {}; 
et_lang["AREYOUSURE"] = "{l s='Are you sure?' mod='etimageeditor' }";

</script>

<div class="ie_wrapper">
    <div class="ie_left_pane" id="ie_left_pane">
        <div class="ie_search_txt" >
            <form class="form-horizontal" onsubmit="return false;">
                <div class="input-group">
                    <input class="search" type="text" id="ie_search_txt" placeholder="{l s='Search by Name or Reference' mod='etimageeditor'}" >
                    <span class="input-group-addon ie_search_right_tools">
                        <button type="submit" class="btn btn-primary btn-sm" id="ie_module_settings">
                            <span class="icon-cog"></span>
                        </button>
                    </span>
                </div>
            </form>
        </div>

        <div class="list" id="ie_product_list">

            {foreach from=$product_images item=img}

            <div class="ie_item_wrapper" id="{$img['id_product']|escape:'html':'UTF-8'}"">
                <img class="ie_item_img" data-original="{$img['cover_link']|escape:'html':'UTF-8'}"/>
                <span class="ie_item_txt">{$img['product_name']|escape:'html':'UTF-8'}</span>
                <span class="ie_item_ref ie_item_hidden">{$img['product_ref']|escape:'html':'UTF-8'}</span>
                <span class="ie_item_hidden">{$img['p_images']|escape:'html':'UTF-8'}</span>

                <span class="ie_product_tools pull-right">
                    <span id="" class="icon-plus-circle  ie_add_product_image" title="{l s='Add a new image to this product' mod='etimageeditor' }"></span>
                    <span class="icon-external-link ie_product_link" title="{l s='Preview' mod='etimageeditor' }"></span>
                </span>

            </div>


            {/foreach}

        </div>
    </div>

    <div class="ie_right_pane">
        <div id="ie_settings">{$mod_settings|escape:'url':'UTF-8'}</div>
        <div class="ie_gallery_wrapper row"></div>
    </div>
</div>

<div class="clearfloat"></div>

<form id="ie_image_upload_form" method="post" enctype="multipart/form-data">
    <input id="ie_image_file" name="file[]" type="file" style="width:0px;height:0px;" multiple="multiple" />
</form>
<br><br>
