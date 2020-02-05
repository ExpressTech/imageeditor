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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if !$exif_imagetype}
	<p class="alert alert-warning">{l s='exif_imagetype function not found. Unable to detect image type, disabling MOZjpeg support. ' mod='etimageeditor'} <a href="http://stackoverflow.com/questions/23978360/php-fatal-error-call-to-undefined-function-exif-imagetype" target="_blank">more</a></p>
{/if}

<div class="panel">
	
	

	<h3><i class="icon icon-tags"></i> {l s='Image Optimization' mod='etimageeditor'}</h3>
	<p><strong>{l s='Cron URL' mod='etimageeditor'}</strong></p>
	<p>{$cron_url|escape:'htmlall':'UTF-8'}</p>
	<p><strong>{l s='Statistics' mod='etimageeditor'}</strong></p>
	<p>
	{l s='Total Images' mod='etimageeditor'} : {$images_count|escape:'htmlall':'UTF-8'}<br>
	{l s='Images Processed' mod='etimageeditor'} : {$images_processed|escape:'htmlall':'UTF-8'}<br>
	{l s='Avg Compression' mod='etimageeditor'} : {$avg_compression|escape:'htmlall':'UTF-8'}%<br>
	{l s='Space Saved' mod='etimageeditor'} : {$space_saved|escape:'htmlall':'UTF-8'} KB<br>

	</p>
</div>