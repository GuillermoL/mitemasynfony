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


<!-- menu -->

{include file="$module_local_path/views/templates/admin/header.tpl" discoverOpartModuleLink=$discoverOpartModuleLink}


{if isset($tabkey)}
{if isset($confirmation)}<div class="alert alert-success">{$confirmation|escape:'htmlall':'UTF-8'}</div>{/if}
{if isset($erreur)}<div class="alert alert-danger">{$erreur|escape:'htmlall':'UTF-8'}</div>{/if}
<form class="form-horizontal indexnow_form tab-optiongroup" action="" method="post" enctype="multipart/form-data" name="indenow_form">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-cog"></i>{l s='Configuration' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
			<div class="form-group row">

				<div class="col-md-6">
					<label >{l s='Limit the maximum number of submitted urls stored in the database to : ' mod='opartindexnow'}</label>
				</div>
				<div class="col-md-3">
					<input type="number" name="limitlogs" id="limitlogs" {if isset($limitlogs)}value="{$limitlogs|escape:'htmlall':'UTF-8'}"{/if}/>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-6">
					<label >{l s='Global daily send limit : ' mod='opartindexnow'}</label>
				</div>
				<div class="col-md-3">
					<input type="number" name="limitsend" id="limitsend" value="{$limitsend|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
			{if isset($noindex) && $noindex}
			<div class="form-group">
					<label class="col-md-6">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
						{l s='Exclude urls in noindex' mod='opartindexnow'} <a href="https://addons.prestashop.com/fr/seo-referencement-naturel/30924-op-art-noindex-booster-votre-seo-eviter-les-penalites.html">{l s='Compatible with our noindex module.' mod='opartindexnow'}</a></span>
					</label>
					<div class="col-md-3">
						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="INDEXNOW_NOINDEX_ARGS" id="INDEXNOW_NOINDEX_ARGS_on" value="1" {if $INDEXNOW_NOINDEX_ARGS}checked{/if}>
							<label for="INDEXNOW_NOINDEX_ARGS_on">{l s='Yes' mod='opartindexnow'}</label>
							<input type="radio" name="INDEXNOW_NOINDEX_ARGS" id="INDEXNOW_NOINDEX_ARGS_off" value="0" {if !($INDEXNOW_NOINDEX_ARGS)}checked{/if}>
							<label for="INDEXNOW_NOINDEX_ARGS_off">{l s='No' mod='opartindexnow'}</label>
							<a class="slide-button btn"></a>
							</span>
					</div>
			</div>
			{/if}
			<div class="form-group">
					<label class="col-md-6">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
						{l s='Exclude products in visibility nowhere' mod='opartindexnow'}</span>
					</label>
					<div class="col-md-3">
						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="INDEXNOW_VISIBILITY_ARGS" id="INDEXNOW_VISIBILITY_ARGS_on" value="1" {if isset($INDEXNOW_VISIBILITY_ARGS) && $INDEXNOW_VISIBILITY_ARGS}checked{/if}>
							<label for="INDEXNOW_VISIBILITY_ARGS_on">{l s='Yes' mod='opartindexnow'}</label>
							<input type="radio" name="INDEXNOW_VISIBILITY_ARGS" id="INDEXNOW_VISIBILITY_ARGS_off" value="0" {if isset($INDEXNOW_VISIBILITY_ARGS) && !($INDEXNOW_VISIBILITY_ARGS)}checked{/if}>
							<label for="INDEXNOW_VISIBILITY_ARGS_off">{l s='No' mod='opartindexnow'}</label>
							<a class="slide-button btn"></a>
							</span>
					</div>
			</div>
			<div class="form-group">
					<label class="col-md-6">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
						{l s='Do you use a module to manage your products which directly modifies the information in the database (storemanager example)' mod='opartindexnow'}</span>
					</label>
					<div class="col-md-3">
						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="INDEXNOW_SEND_BY_CRON" id="INDEXNOW_SEND_BY_CRON_on" value="1" {if isset($INDEXNOW_SEND_BY_CRON) && $INDEXNOW_SEND_BY_CRON}checked{/if}>
							<label for="INDEXNOW_SEND_BY_CRON_on">{l s='Yes' mod='opartindexnow'}</label>
							<input type="radio" name="INDEXNOW_SEND_BY_CRON" id="INDEXNOW_SEND_BY_CRON_off" value="0" {if isset($INDEXNOW_SEND_BY_CRON) && !($INDEXNOW_SEND_BY_CRON)}checked{/if}>
							<label for="INDEXNOW_SEND_BY_CRON_off">{l s='No' mod='opartindexnow'}</label>
							<a class="slide-button btn"></a>
							</span>
					</div>
			</div>
			<div class="form-group" id="cronsendindexnow">
					<label class="col-md-12">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
							{l s='Set up this cron job to allow indexnow to work with these modules' mod='opartindexnow'} :<br/> <span id="cronUrlConteneurIndexdnow">{$urlcronsendindexnow|escape:'htmlall':'UTF-8'}</span>
						</span>						
						<a href="#" class="copyBtnCron" data-target="cronUrlConteneurIndexdnow">
							<span class="material-icons">content_copy</span>							
							<span class="confirmCopyBtn" >{l s='Cron url copied' mod='opartindexnow'}</span>
						</a>
					</label>
			</div>
			<div class="form-group">
					<label class="col-md-12">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
							{l s='If you make more changes than your daily sending limit, set up this cron job to send pending urls' mod='opartindexnow'} :<br/> <span id="cronUrlConteneur">{$urlcron|escape:'htmlall':'UTF-8'}</span>
						</span>						
						<a href="#" class="copyBtn" data-target="cronUrlConteneur">
							<span class="material-icons">content_copy</span>							
							<span class="confirmCopyBtn" >{l s='Cron url copied' mod='opartindexnow'}</span>
						</a>
					</label>
			</div>
		</div>
		<div class="panel-footer">
			<button type="submit" name="indexnowsendconfig" class='btn btn-default pull-right'>{l s='Save' mod='opartindexnow'}</button>
	    </div>
	</div>
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-key"></i>{l s='Security key' mod='opartindexnow'} Indexnow
		</div>
		<div class="form-wrapper">
			<div class="form-group">
				{if !$key}
				<a href="{$admin_module_url|escape:'htmlall':'UTF-8'}&action=key&generatekey=1" class="btn btn-primary text-center" title="{l s='save' mod='opartindexnow'}" name="save" id="opartIndexNowGenerateKey">
	                <i class="fas fa-key"></i>
	                {l s='Generate security key' mod='opartindexnow'}
	        	</a>
	        	{else}
	        		<p>{l s='Your security key is : ' mod='opartindexnow'}<strong>{$key|escape:'htmlall':'UTF-8'}</strong></p>
	        		<a href="{$admin_module_url|escape:'htmlall':'UTF-8'}&action=key&deletekey=1" class="btn btn-primary text-center" title="{l s='save' mod='opartindexnow'}" name="save">{l s='Delete security key' mod='opartindexnow'}</a>
	        	{/if}
			</div>
		</div>
		<div class="panel-footer">
	    </div>
	</div>
</form>
<form class="form-horizontal indexnow_form tab-optiongroup" action="" method="post" enctype="multipart/form-data" name="googleindexing_form">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-key"></i>{l s='Configuring the Google Indexing API' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
			<div class="form-group">
				<p><strong>{l s='You can read the documentation to configure your account' mod='opartindexnow'} <a id="help_form" href="#" onClick="oesfpLoadHelp('installgoogleapi')">{l s='here' mod='opartindexnow'}</a> </strong></p>
				<div class="form-group">
				 <label for="googlejson">{l s='Load the json file with the Google APi indexing key' mod='opartindexnow'}</label>
				 <input type="file" name="googlejson" id="googlejson" class="form-control-file" accept=".json"/>
				</div>
    			<textarea class="form-control"  rows="10" readonly>{if isset($googlekey)}{$googlekey|escape:'htmlall':'UTF-8'}{/if}</textarea>
			</div>
			  <div class="form-check">
			    <input type="checkbox" class="form-check-input" name="consentgoogle" id="consentgoogle" {if $consentgoogle}checked{/if}>
			    <label class="form-check-label" for="consentgoogle">{l s='I confirm that I have read the Google API usage rules' mod='opartindexnow'} <a id="help_form" href="#" onClick="oesfpLoadHelp('rulesgoogle')">{l s='(learn more)' mod='opartindexnow'}</a> </label>
			   <p>{l s='If despite the explanations provided in the documentation, you are unable to configure your Google indexing API, we offer a configuration support option.' mod='opartindexnow'}</p>
			  </div>
		</div>
		<div class="panel-footer">
			<button type="submit" name="googlesendconfig" class='btn btn-default pull-right'>{l s='Save' mod='opartindexnow'}</button>
	    </div>
	</div>
</form>
{/if}

{if isset($exclusion)}
<div class="exlcusion_form tab-optiongroup container-fluid">
	<ps-tabs class="row">
    <ps-tab-nav class="col-md-2 panel transparent">
        <li riot-tag="ps-tab-nav-item" target="tabCategory" class="list-group-item nav-optiongroup {if $selected_element == 1}active selected{/if}" onClick="oesfpLoadTab(1)">{l s='Category' mod='opartindexnow'}</li>
        <li riot-tag="ps-tab-nav-item" target="tabProducts"  class="list-group-item nav-optiongroup {if $selected_element == 2}active selected{/if}" onClick="oesfpLoadTab(2)">{l s='Products' mod='opartindexnow'}</li>
        <li riot-tag="ps-tab-nav-item" target="tabSuppliers"  class="list-group-item nav-optiongroup {if $selected_element == 3}active selected{/if}" onClick="oesfpLoadTab(3)">{l s='Suppliers' mod='opartindexnow'}</li>    
        <li riot-tag="ps-tab-nav-item" target="tabCms"  class="list-group-item nav-optiongroup {if $selected_element == 4}active selected{/if}" onClick="oesfpLoadTab(4)">{l s='Cms' mod='opartindexnow'}</li> 
        <li riot-tag="ps-tab-nav-item" target="tabManufacturers"  class="list-group-item nav-optiongroup {if $selected_element == 5}active selected{/if}" onClick="oesfpLoadTab(5)">{l s='Manufacturers' mod='opartindexnow'}</li>
        {if $prestablog && $prestablog->active == 1}
        	<li riot-tag="ps-tab-nav-item" target="tabPrestablog"  class="list-group-item nav-optiongroup {if $selected_element == 6}active selected{/if}" onClick="oesfpLoadTab(6)">{l s='Prestablog' mod='opartindexnow'}</li>
        {/if}
        {if $advancedsearch && $advancedsearch->active == 1}
        	<li riot-tag="ps-tab-nav-item" target="tabAdvancedsearch"  class="list-group-item nav-optiongroup {if $selected_element == 7}active selected{/if}" onClick="oesfpLoadTab(7)">{l s='Advanced Search' mod='opartindexnow'}</li>
        {/if}      
    </ps-tab-nav>
    <ps-tab-content class="col-md-10 panel">
    	<div class="panel-heading">
			<i class="icon-ban"></i>{if $selected_element == 1}{l s='Choose categories to exclude' mod='opartindexnow'}{elseif $selected_element == 2}{l s='Choose products to exclude' mod='opartindexnow'}{elseif $selected_element == 3}{l s='Choose suppliers to exclude' mod='opartindexnow'}{elseif $selected_element == 4}{l s='Choose CMS to exclude' mod='opartindexnow'}{elseif $selected_element == 5}{l s='Choose manufacturers to exclude' mod='opartindexnow'}{elseif $selected_element == 6}{l s='Choose articles to exclude' mod='opartindexnow'}{/if}
		</div>

		<!-- Filter -->
		<div class="panel col-md-5">
			<h3 class="active mb-2">{l s='Filter' mod='opartindexnow'}</h2>
		<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
	    	<div class="form-group">
	    		<input type="hidden" name="selected_page" value ="" id="selected_page"/>
				<input type="hidden" name="item_number" value ="{$item_number|intval}" id="item_number"/>
				<input type="hidden" name="current_page" value ="{$selected_page|intval}" id="{$selected_page|intval}"/>
		        
		    </div>
		    <div class="form-group">
		        {if $selected_element==1 || $selected_element == 2}
				  	<label class="col-md-3 labelselect">{l s='Category' mod='opartindexnow'}:</label>
				    <select class="opartindexnowselect col-md-6" name="selected_category" id="selected_category">
				        <option value="-1">---</option>
				        {$select_category_options nofilter} {* Can't escape, html given *}
				    </select>
				{/if}
	    	</div>
	    	<div class="form-group">
				  	<label class="col-md-3 labelselect">{l s='Status' mod='opartindexnow'}:</label>
				    <select class="opartindexnowselect col-md-6" name="selected_status" id="selected_name">
				    	<option value="2">{l s='All' mod='opartindexnow'}</option>
				    	<option value="1" {if $exclude_only == 1}selected{/if}>{l s='Exlude' mod='opartindexnow'}</option>
				        <option value="0" {if $noexclude_only == 1}selected{/if}>{l s='No Exlude' mod='opartindexnow'}</option>
				    </select>
	    	</div>
	    	<div class="form-group">
				  	<label class="col-md-3 labelselect">{l s='Visibility' mod='opartindexnow'}:</label>
				    <select class="opartindexnowselect col-md-6" name="selected_active" id="selected_active">
				    	<option value="2">{l s='All' mod='opartindexnow'}</option>
				    	<option value="1" {if $active == 1}selected{/if}>{l s='Active' mod='opartindexnow'}</option>
				        <option value="0" {if $notactive == 1}selected{/if}>{l s='not active' mod='opartindexnow'}</option>
				    </select>
	    	</div>
	    	<div>
	    	<button class="btn btn-default pull-right" title="{l s='Apply filter' mod='opartindexnow'}" name="applyFilter" id="applyFilter">
            <i class="process-icon-cogs"></i>
            {l s='Apply filter' mod='opartindexnow'}
	        </button>
	        <button class="btn btn-default pull-left" title="{l s='Reset filter' mod='opartindexnow'}" direction="left" name="resetFilter" icon="save" ps_value="1" id="resetFilter">
	            <i class="process-icon-reset"></i>
	            {l s='Reset filter' mod='opartindexnow'}
	        </button>
	    	</div>
	    </form>
	</div>

	    <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" style="clear:both;margin-top:2%;" >
	    	<input type="hidden" name="selected_page" value ="" id="selected_page"/>
			<input type="hidden" name="item_number" value ="{$item_number|intval}" id="item_number"/>
			<input type="hidden" name="current_page" value ="{$selected_page|intval}" id="{$selected_page|intval}"/>
	    	<input type="hidden" name="oesfp_element_type" value="{$selected_element|escape:'htmlall':'UTF-8'}"/>
	    	<input type="hidden" name="selected_category" value="{$id_selected_category|escape:'htmlall':'UTF-8'}"/>
	    	<input type="hidden" name="selected_status" value="{if $exclude_only == 1}1{elseif  $noexclude_only == 1 } 0 {else} 2 {/if}"/>
	    	<!-- Content -->
	        <div id="tabCategory" class="tab-pane{if $selected_element == 1} active{/if}">{if $selected_element == 1}{include file="$module_local_path/views/templates/admin/category.tpl"}{/if}</div>
	        <div id="tabProducts" class="tab-pane{if $selected_element == 2} active{/if}">{if $selected_element == 2}{include file="$module_local_path/views/templates/admin/product.tpl"}{/if}</div>
	        <div id="tabSuppliers" class="tab-pane{if $selected_element == 3} active{/if}">{if $selected_element == 3}{include file="$module_local_path/views/templates/admin/suppliers.tpl"}{/if}</div>        
	        <div id="tabCms" class="tab-pane{if $selected_element == 4} active{/if}">{if $selected_element == 4}{include file="$module_local_path/views/templates/admin/cms.tpl"}{/if}</div>
	        <div id="tabManufacturers" class="tab-pane{if $selected_element == 5} active{/if}">{if $selected_element == 5}{include file="$module_local_path/views/templates/admin/manufacturers.tpl"}{/if}</div>
	        {if $prestablog && $prestablog->active == 1}
	        <div id="tabPrestablog" class="tab-pane{if $selected_element == 6} active{/if}">{if $selected_element == 6}{include file="$module_local_path/views/templates/admin/prestablog.tpl"}{/if}</div>
	        {/if} 
	        {if $advancedsearch && $advancedsearch->active == 1}
	        <div id="tabAdvancedsearch" class="tab-pane{if $selected_element == 7} active{/if}">{if $selected_element == 7}{include file="$module_local_path/views/templates/admin/advancedsearch.tpl"}{/if}</div>
	        {/if}           
	        <div class="panel-footer mb-1">
	        	<div class="form-group row">
	        		
	        	<label class="oparindexnow col-lg-3 col-md-2 labelselect text-left paginationindexnow">{l s='Items per page' mod='opartindexnow'}:</label>
	        			        <select class="col-md-1" name="item_number" onChange="oniGoToItemNumber(this.value)">
		            <option value="10" {if $item_number == 10}selected="selected"{/if}>10</option>
		            <option value="25"   {if $item_number == 25}selected="selected"{/if}>25</option>
		            <option value="50"  {if $item_number == 50}selected="selected"{/if}>50</option>
		            <option value="100"   {if $item_number == 100}selected="selected"{/if}>100</option>
		            <option value="200"   {if $item_number == 200}selected="selected"{/if}>200</option>
		            <option value="500"   {if $item_number == 500}selected="selected"{/if}>500</option>
		            <option value="750"  {if $item_number == 750}selected="selected"{/if}>750</option>
		            <option value="1000"  {if $item_number == 1000}selected="selected"{/if}>1000</option>
		            <option value="1500"   {if $item_number == 1500}selected="selected"{/if}>1500</option>
		        </select>
		    	</div>
		    	<label>{l s='page' mod='opartindexnow'} : </label>
					{assign var="foo" value="0"}
					{while $foo < (ceil($totalItem/$limit))}        
					    {assign var="foo" value=$foo+1}
					    {if $foo!=$selected_page}
					        <a href="#" onclick="oniGoToPage('{$foo|intval}'); return false;">{$foo|intval}</a> 
					    {else}
					        {$foo|intval}
					    {/if}       
					    |
					{/while}
	        	
	    	</div>   
	    </form>
    </ps-tab-content>
</ps-tabs>
</div>
{/if}


{if isset($urlmanuelly)}
{if isset($confirmation)}<div class="alert alert-success">{$confirmation|escape:'htmlall':'UTF-8'}</div>{/if}
{if isset($erreur)}<div class="alert alert-danger">{$erreur|escape:'htmlall':'UTF-8'}</div>{/if}
<div class="manuelly_form tab-optiongroup  container">
	 <form class="form-horizontal" action="" method="post">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-envelope"></i> {l s='Submit a URL manually' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
				 <div class="form-group">
				    <label for="urlmanuelly">{l s='URL' mod='opartindexnow'}</label>
				    <input type="text" class="form-control" id="urlmanuelly" name="urlmanuelly" aria-describedby="emailHelp" placeholder="{l s='Enter a url' mod='opartindexnow'}">
				 </div>
				 <button type="submit" class="btn btn-primary" name="sendmanuellyurl">{l s='Send' mod='opartindexnow'}</button>
		</div>
	</div>
	</form>
</div>
{/if}

{if isset($urlssubmitted) || isset($urlssubmittedgoogle)}
<div class="soumission_form tab-optiongroup  container">
	 <form class="form-horizontal" action="" method="post">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-laptop"></i>{l s='Submitted URLs to' mod='opartindexnow'} Indexnow
		</div>
		<div>
			
			{include file="$module_local_path/views/templates/admin/submitted.tpl"}
		</div>
		<div class="panel-footer">
			<a href="{$admin_module_url|escape:'htmlall':'UTF-8'}&action=soumission&deletelist=1" class="btn btn-primary pull-left" title="{l s='Empty the list' mod='opartindexnow'}" onClick='loaderindexnow()'>{l s='Empty the list' mod='opartindexnow'}</a>
			<button type="submit" class="btn btn-primary pull-right" name="submit_url">{l s='Resend urls' mod='opartindexnow'}</button>
	    </div>
	</div>
	</form>
	<div  class="panel">
		<div class="panel-heading">
			<i class="icon-question"></i> {l s='Status explanation' mod='opartindexnow'} Indexnow
		</div>
		<table class="table  table-striped">
			 <thead>
			    <tr>
			      <th scope="col">{l s='HTTP Code' mod='opartindexnow'}</th>
			      <th scope="col">{l s='Response' mod='opartindexnow'}</th>
			      <th scope="col">{l s='Reasons' mod='opartindexnow'}</th>
			    </tr>
			  </thead>
			  <tbody>
			    <tr>
			      <th scope="row">200</th>
			      <td>Ok</td>
			      <td>{l s='URL submitted successfully' mod='opartindexnow'}</td>
			    </tr>
			     <tr>
			      <th scope="row">202</th>
			      <td>Ok</td>
			      <td>{l s='URL submitted successfully' mod='opartindexnow'}</td>
			    </tr>
			    <tr>
			      <th scope="row">400</th>
			      <td>{l s='Bad request' mod='opartindexnow'}</td>
			      <td>{l s='Invalid format' mod='opartindexnow'}</td>
			    </tr>
			    <tr>
			      <th scope="row">403</th>
			      <td>{l s='Forbidden' mod='opartindexnow'}</td>
			      <td>{l s='In case of key not valid (e.g. key not found, file found but key not in the file)' mod='opartindexnow'}</td>
			    </tr>
			      <tr>
			      <th scope="row">422</th>
			      <td>{l s='Unprocessable Entity' mod='opartindexnow'}</td>
			      <td>{l s='In case of URLs don\'t belong to the host or the key is not matching the schema in the protocol' mod='opartindexnow'}</td>
			    </tr>
			     </tr>
			      <tr>
			      <th scope="row">429</th>
			      <td>{l s='Too Many Requests' mod='opartindexnow'}</td>
			      <td>{l s='Too Many Requests (potential Spam)' mod='opartindexnow'}</td>
			    </tr>
			  </tbody>
		</table>
	</div>
		 <form class="form-horizontal" action="" method="post">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-laptop"></i>{l s='Submitted URLs to' mod='opartindexnow'} Google
		</div>
		<div>
			
			{include file="$module_local_path/views/templates/admin/submittedgoogle.tpl" }
		</div>
		<div class="panel-footer">
			<a href="{$admin_module_url|escape:'htmlall':'UTF-8'}&action=soumission&deletelistgoogle=1" class="btn btn-primary pull-left" title="{l s='Empty the list' mod='opartindexnow'}" onClick='loaderindexnow()'>{l s='Empty the list' mod='opartindexnow'}</a>
			<button type="submit" class="btn btn-primary pull-right" name="submit_url_google">{l s='Resend urls' mod='opartindexnow'}</button>
	    </div>
	</div>
	</form>
</div>
{/if}



{if isset($help)}
<div class="help_form tab-optiongroup  container indexNowHelpContainer">
	 <form class="form-horizontal" action="" method="post">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-question-sign"></i> {l s='Help' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
			<div>
				<h2>{l s='Module overview' mod='opartindexnow'}</h2>
				<iframe width="560" height="315" src="https://www.youtube.com/embed/DaWfy-PY6Vg" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
			<div>
				<h2>{l s='FAQ' mod='opartindexnow'}</h2>
				<p  class="btn btn-link" data-toggle="collapse" href="#indexnowgoogle" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='Is indexnow technology used by Google ?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="indexnowgoogle">
					<p>{l s='IndexNow is a technology invented by Bing and it is a non-proprietary technology.' mod='opartindexnow'}</p>
					<p>{l s='This means that it is free that everyone can use it and participate in its improvement.' mod='opartindexnow'}</p>
					<p>{l s='Google can therefore use it without any problem.' mod='opartindexnow'}</p>
					<p>{l s='But of course, from a marketing point of view, Google cannot rush into a technology that Bing (its main competitor) is at the initiative of.' mod='opartindexnow'}</p>
					<p>{l s='C’est pour cette raison que, pour l’instant, Google ne s’appuis pas encore sur IndexNow et continue d’utiliser l’ancienne technique du Crawl pour découvrir les pages.' mod='opartindexnow'}</p>
					<p>{l s='Yet we are convinced that Google will have no choice and will soon have to use IndexNow' mod='opartindexnow'}</p>
				</div>
				<hr/>
				<p  class="btn btn-link" data-toggle="collapse" href="#indexnownoindex" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='The indexnow module replaces the noindex?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="indexnownoindex">
					<p>{l s='The indexnow and noindex module have 2 different purposes.' mod='opartindexnow'}</p>
					<p>{l s='The indexnow module notifies search engines that use this technology that a page has been added, modified or deleted. This allows the pages to be indexed more quickly.' mod='opartindexnow'}</p>
					<p>{l s='With regard to noindex, the module allows the opposite, to deindex the pages which have little interest level referencing to avoid lowering the overall score of your site level seo.' mod='opartindexnow'}</p>
				</div>
				<hr/>
				<p  class="btn btn-link" data-toggle="collapse" href="#indexnow429" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='Why are my submitted URLs in 429?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="indexnow429">
					<p>{l s='If your URLs are 429, Indexnow assumes you\'re sending too many notifications and can\'t process all your requests. To fix this, you need to lower the daily sending limit in the module\'s configuration.' mod='opartindexnow'}</p>
					<p>{l s='However, if your URLs are frequently 429 again, it\'s likely that another site on your server is submitting many URLs to the IndexNow API and using the quota allocated to your shared IP address. This is common with websites on a shared hosting server. In this case, you can wait or contact your hosting provider.' mod='opartindexnow'}</p>
				</div>
				<hr/>
				<p  class="btn btn-link" data-toggle="collapse" href="#indexnowall" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='Why aren\'t all my URLs sent to indexnow?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="indexnowall">
					<p>{l s='First, you need to check that the URL in question is not considered excluded in our module.' mod='opartindexnow'}</p>
					<p>{l s='You also have a daily sending limit setting, so if you send more than the limit, then those URLs will not be sent.' mod='opartindexnow'}</p>
					<p>{l s='You can change this limit, but if your submitted URLs return a 429 error code, this limit is too high and indexnow considers you to be sending too many URLs in a short time.' mod='opartindexnow'}</p>
					<p>{l s='In this case, you can always set up the cron job to send the URLs that were not sent.' mod='opartindexnow'}</p>
					<p>{l s='Additionally, between each ping to indexnow, we must respect a time lapse of a few minutes to avoid being considered a spammer.' mod='opartindexnow'}</p>
					<p>{l s='So if you edit a lot of product sheets in a few minutes, it is possible that some of these products will not be sent to indexnow.' mod='opartindexnow'}</p>
					<p>{l s='Finally, if during the day you modify the same product several times, we will only send the product to indexnow the first time, always in order to avoid being considered a spammer.' mod='opartindexnow'}</p>
					<p>{l s='Enabling the cron job solves the problem by spreading the sending of updates over time.' mod='opartindexnow'}</p>
				</div>
				<hr/>
				<p  class="btn btn-link" data-toggle="collapse" href="#indexnow403" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='The URLs sent via indexnow are in 403, what should I do?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="indexnow403">
					<p>{l s='If your URLs are in 403, you must, in the module configuration, delete the key and generate a new one.' mod='opartindexnow'}</p>
					<p>{l s='Normally the next send to Indexnow should have a return code of 202.' mod='opartindexnow'}</p>
					<p>{l s='If the URLs subsequently switch back to 403, you must then reduce the number of times the module is sent to Indexnow per day in the module configuration.' mod='opartindexnow'}</p>
				</div>
				<hr/>
				<p  class="btn btn-link" data-toggle="collapse" href="#indexnowallpages" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='How do I send all my pages?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="indexnowallpages">
					<p>{l s='It is not Indexnow\'s goal to send all of your site\'s URLs.' mod='opartindexnow'}</p>
					<p>{l s='The purpose of Indexnow technology is to send new URLs, modified or deleted URLs to robots via this technology in order to speed up the processing of these changes by search engines.' mod='opartindexnow'}</p>
				</div>
					<hr/>
				<p  class="btn btn-link" data-toggle="collapse" href="#installgoogleapi" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='How to configure the Google Indexing API?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="installgoogleapi">
					<h2>{l s='Configure the Google Indexing API' mod='opartindexnow'}</h2>
					<div class="indexNowHelpNote">{l s='NOTE: If, despite our tutorial, you\'re unable to configure the Google Indexing API, we can offer a paid service to do it for you.' mod='opartindexnow'}</div>
					
					<div class="responsiveVideo">
						{* <iframe src="https://www.youtube.com/embed/tmTwCyk74oU?si=-O1H2FefcbpdXKiW" width="100%" height="100%" frameborder= "0" allowfullscreen= »allowfullscreen »></iframe> *}
						<iframe width="100%" height="100%" src="https://www.youtube.com/embed/tmTwCyk74oU?si=-O1H2FefcbpdXKiW" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
					</div>

					<h3>{l s='1) Enabling the Google Indexing API in the Cloud Console' mod='opartindexnow'}</h3>
					<p><a href="https://console.developers.google.com/" target="_blank">{l s='Sign in to Google Cloud Console.' mod='opartindexnow'}</a></p>
					<p>{l s='Once connected to Google Cloud Console, click on "API and services enabled" which is located in the left menu.' mod='opartindexnow'}</p>
					<p>{l s='Then click on the link "Library" to access the page listing the different APIs.' mod='opartindexnow'}</p>
					<p>{l s='In the search field that appears, type "Web search indexing api"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-search-bibliotheque.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='You should see a result named "Web search indexing api", click on it' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-web-search-indexing-api-result.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Then click on the "Enable" button' mod='opartindexnow'}</p>
					<p>{l s='Once the api is enabled, you will be redirected to the "Api and services enabled" page' mod='opartindexnow'}</p>

					<h3>{l s='2) Creating the service account' mod='opartindexnow'}</h3>
					<p>{l s='In the left menu, click on "Identifiants"' mod='opartindexnow'}</p>
					<p>{l s='Click on "Create credentials"' mod='opartindexnow'}</p>
					<p>{l s='Then click on "Service account"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-create-credentials.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='In the form that appears, indicate for the "Service account name" : "Module Op\'art IndexNow"' mod='opartindexnow'}</p>
					<p>{l s='And for the "Service account description" indicate : "Allows to send PrestaShop page updates to Google"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-service-account-name.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Click on "Create and continue" to go to the next step' mod='opartindexnow'}</p>
					<p>{l s='Click on "Select a role" and choose "Owner"' mod='opartindexnow'}</p>					
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-select-role.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Click on "Continue" to go to the next step' mod='opartindexnow'}</p>	
					<p>{l s='For the third step, you have nothing to fill in, simply click on "Ok"' mod='opartindexnow'}</p>
					<p>{l s='You will be redirected to the "identifiants" page where you will see in the "Service accounts" section, the service account you have created.' mod='opartindexnow'}</p>

					<h3>{l s='3) Creating a private key' mod='opartindexnow'}</h3>
					<p>{l s='Click on the pencil icon to modify this service account' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-modify-account-service.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Go to the "Keys" tab' mod='opartindexnow'}</p>
					<p>{l s='Click on "Add a key"' mod='opartindexnow'}</p>
					<p>{l s='Then click on "Create a key"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-add-key.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='A window will appear' mod='opartindexnow'}</p>	
					<p>{l s='Choose "JSON" as the key type' mod='opartindexnow'}</p>
					<p>{l s='and click on "Create"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-json-key.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Save the JSON file to your computer' mod='opartindexnow'}</p>
					<p>{l s='Important : Keep the Cloud Console tab open, you will need it for the next steps.' mod='opartindexnow'}</p>
					<p>{l s='Return to the Op\'art IndexNow module configuration' mod='opartindexnow'}</p>
					<p>{l s='Scroll down to the "Google Indexing API configuration" section' mod='opartindexnow'}</p>
					<p>{l s='Click on "Choose a file"' mod='opartindexnow'}</p>
					<p>{l s='Then select the JSON file you have saved on your computer' mod='opartindexnow'}</p>
					<p>{l s='Check the box "I confirm that I have read the Google Api usage rules"' mod='opartindexnow'}</p>
					<p>{l s='And click on "Save"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-upload-json.png" alt="" class="image img-responsive"></p>

					<h3>{l s='4) Configure the search console' mod='opartindexnow'}</h3>
					<p><a href="https://search.google.com/search-console/" target="_blank">{l s='Click here to go to Google Search Console.' mod='opartindexnow'}</a></p>
					<p>{l s='Click on the top left to choose the property that corresponds to your site' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-your-site-search-console.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Click on "Settings" in the left menu' mod='opartindexnow'}</p>
					<p>{l s='Then click on "Users and permissions"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-user-and-autorisation.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Click on "Add a user"' mod='opartindexnow'}</p>
					<p>{l s='A window will appear asking for an email address and an authorization type' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-email-and-autorisations-search-console.png" alt="" class="image img-responsive"></p>
					<p>{l s='The email address is the one that was automatically created when the service account was created in Google Cloud Console' mod='opartindexnow'}</p>
					<p>{l s='Return to the Cloud Console tab that you must have kept open which is on the service account "Module Op\'art IndexNow"' mod='opartindexnow'}</p>
					<p>{l s='Click on the "Details" tab' mod='opartindexnow'}</p>
					<p>{l s='And copy the email address that appears in the service account description' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-get-email.png" alt="" class="image img-responsive"></p>
					<br />
					<p>{l s='Return to the Google Search Console tab' mod='opartindexnow'}</p>
					<p>{l s='Paste the email address into the "Email address" field' mod='opartindexnow'}</p>
					<p>{l s='Choose "Owner" in the "Authorization type" field' mod='opartindexnow'}</p>
					<p>{l s='And click on "Add"' mod='opartindexnow'}</p>
					<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-indexing-api-fill-search-console-form.png" alt="" class="image img-responsive"></p>
					<br />
					<p><strong>{l s='CONGRATULATIONS !' mod='opartindexnow'}</strong> {l s='You have finished configuring the Google Indexing API' mod='opartindexnow'}</p>
				</div>
				<hr/>
				<p  class="btn btn-link" data-toggle="collapse" href="#rulesgoogle" role="button" aria-expanded="false" aria-controls="collapseExample"><strong>{l s='What you need to know about using the Google Indexing API?' mod='opartindexnow'}</strong></p>
				<div class="collapse" id="rulesgoogle">
					<p>{l s='Google recommends using the Indexing API ONLY for job and streaming sites. However, it works with any type of site, and many SEO experts see great results using it.' mod='opartindexnow'}</p>
					<p>{l s='We encourage you to read Google\'s documentation on the subject' mod='opartindexnow'} <a href="https://developers.google.com/search/apis/indexing-api/v3/quickstart?hl=en" target="_blank">{l s='here' mod='opartindexnow'}</a> {l s='and use this feature with full knowledge of the facts.' mod='opartindexnow'}</p>
				</div>
			</div>
		</div>
	</div>
</form>
{/if}


<script type="text/javascript">
    var admin_module_url = '{$admin_module_url|escape:'javascript':'UTF-8'}';

	$('.copyBtn').click(function() {
		copyTextToClipboard($(this))
	})

	$('.copyBtnCron').click(function() {
		copyTextToClipboard($(this))
	})


</script>