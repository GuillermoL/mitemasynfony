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

<nav class="productTabs" >
<a class="btn btn-primary discoverAllOpartModulesBtn" href="{$discoverOpartModuleLink|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Discover all our modules' mod='opartindexnow'}</a>
	<ul class="tab list-group" id="navindexnow">
		<li><a id="indexnow_form" href="#" class="list-group-item nav-optiongroup" onClick="oesfpLoadKey()" >{l s='Security key' mod='opartindexnow'}</a></li>
		<li><a id="exlcusion_form" href="#"  class="list-group-item nav-optiongroup" onClick="oesfpLoadExclusion()">{l s='Exclusion' mod='opartindexnow'}</a></li>
		<li><a id="manuelly_form" href="#"  class="list-group-item nav-optiongroup" onClick="oesfpLoadSoumissionManuelly()">{l s='Submit a URL manually' mod='opartindexnow'}</a></li>
		<li><a id="soumission_form" href="#"  class="list-group-item nav-optiongroup" onClick="oesfpLoadSoumission()">{l s='Submitted URLs' mod='opartindexnow'}</a></li>
		<li><a id="help_form" href="#"  class="list-group-item nav-optiongroup" onClick="oesfpLoadHelp()">{l s='Help' mod='opartindexnow'}</a></li>
	</ul>
</nav>

<img id="loader" class="loader" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" />
