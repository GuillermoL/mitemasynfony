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


  <table class="opartindexnowAdminTable table table-striped">
        <tr>
            <th>{l s='Id' mod='opartindexnow'}</th>
            <th>{l s='Name' mod='opartindexnow'}</th>
            <th></th>
            <th>{l s='Exclude' mod='opartindexnow'}</th>
        </tr>
        {foreach $suppliers as $supplier}
            <tr onClick="opartToggleExcludeSupplier({$supplier.id_supplier|intval})">
                <td>{$supplier.id_supplier|intval}<input type="hidden" name="listItem[]" value="{$supplier.id_supplier|intval}" /></td>
                <td for="NoIndexNow[{$supplier.id_supplier|intval}]">{$supplier.name|escape:'htmlall':'UTF-8'}</td>
                <td></td>
                <td><input type="checkbox" name="NoIndexNow[{$supplier.id_supplier|intval}]" id="SupplierIndexNow[{$supplier.id_supplier|intval}]" class="nocheckbox" value="{$supplier.id_supplier|intval}" {if $supplier.exclude == 1}checked="checked"{/if} /></td>
            </tr>
        {/foreach}
        <tr class="bordered"></tr>
       <tr onClick="opartAllCheck()">
            <td colspan="3"><strong>{l s='Check all' mod='opartindexnow'}</strong></td>
            <td>
                <input type="checkbox" id="allcheck" class="noallcheckbox" onClick="Stop()" />
            </td>
        </tr>
        <tr><td colspan="3"></td>
            <td><button type="submit" class="btn btn-primary pull-left" name="submit_exclusion">{l s='Save' mod='opartindexnow'}</button></td></tr>
    </table>