{*
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
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize Olivier CLEMENCE for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Olivier CLEMENCE
*  @copyright 2007-2024 Olivier CLEMENCE
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Olivier CLEMENCE
*}

 		<table class="table table-striped">
	             <tr>
	            <th>{l s='Name' mod='opartindexnow'}</th>
	            <th>{l s='URLS' mod='opartindexnow'}</th>
	            <th>{l s='status' mod='opartindexnow'}</th>
	             <th>{l s='Comment' mod='opartindexnow'}</th>
	            <th>{l s='sending date' mod='opartindexnow'}</th>
	            <th></th>
	        </tr>
	        {foreach $urlssubmittedgoogle as $url}
	            <tr>
	                <td>{$url.name|escape:'htmlall':'UTF-8'}</td>
	                <td>{$url.url|escape:'htmlall':'UTF-8'}</td>
	                <td style="color:{if $url.status >= 200 && $url.status <= 300}green{else}red{/if};" data-bs-toggle="tooltip" data-bs-placement="top" title="{if $url.status == 400}Invalid format{elseif $url.status == 403}In case of key not valid (e.g. key not found, file found but key not in the file){elseif $url.status == 422}In case of URLs don’t belong to the host or the key is not matching the schema in the protocol{elseif $url.status == 429}Too Many Requests (potential Spam){/if}">{$url.status|escape:'htmlall':'UTF-8'}</td>
	                <td>{$url.commentaire|escape:'htmlall':'UTF-8'}</td>
	                <td>{$url.date_upd|escape:'htmlall':'UTF-8'|date_format:"%d/%m/%Y à %H:%M"}</td>
	                <td><input type="checkbox" name="SendIndexNowGoogle[]" value="{$url.id_google_log|escape:'htmlall':'UTF-8'}" /></td>
	            </tr>
	        {/foreach}
	        <tr class="bordered"></tr>
	        <tr >
	            <td colspan="5" class="text-right"><strong>{l s='Check all' mod='opartindexnow'}</strong></td>
	            <td>
	                <input type="checkbox" onClick="opartToggleSendGoogle(this)" />
	            </td>
	        </tr>
	    </table>