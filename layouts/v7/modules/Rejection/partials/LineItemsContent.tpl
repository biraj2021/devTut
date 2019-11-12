{*********************************************************************************** 
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	{assign var="deleted" value="deleted"|cat:$row_no}
    {assign var="entityType" value=$data.$entityIdentifier}
    {assign var="entityIdentifier" value="entityType"|cat:$row_no}
    {assign var="prd_mov_select_frm" value="prd_mov_select_frm"|cat:$row_no}
    {assign var="prd_mov_select_to" value="prd_mov_select_to"|cat:$row_no}
	{assign var="hdnProductId" value="hdnProductId"|cat:$row_no}
	{assign var="productId" value=$data[$hdnProductId]}
    {assign var="batchName" value="batchName"|cat:$row_no}
    {assign var="qty" value="qty"|cat:$row_no}
    {assign var="iten_no" value="iten_no"|cat:$row_no}
	{assign var="rej_reason" value="rej_reason"|cat:$row_no}
	{assign var="image" value="productImage"|cat:$row_no}
{*debug*}
{*$data.$entityIdentifier|@var_dump*}
	
	<td style="text-align:center;">		
	<i class="fa fa-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>	
		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>		
		<input type="hidden" class="rowNumber" value="{$row_no}" />	
	</td>
	<td>			
	<!-- Batch Re-Ordering Feature Code Addition ends -->			
	<div class="itemNameDiv form-inline">				
		<div class="row">					
				<div class="col-lg-10">
				<div class="input-group" style="width:100%">
					<input type="text" id="{$batchName}" name="{$batchName}" value="{$data.$batchName}" class="batchName form-control {if $row_no neq 0} autoComplete {/if} " placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
											   data-rule-required=true {if !empty($data.$productName)} disabled="disabled" {/if}>
				{if !$data.$productDeleted}
					<span class="input-group-addon cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
						<i class="fa fa-times-circle"></i>
					</span>
				{/if}														
					<input type="hidden" id="{$hdnBatchId}" name="{$hdnBatchId}" value="{$data.$hdnBatchId}" class="selectedModuleId"/>							
					<input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$data.$entityIdentifier}" class="lineItemType"/>							
					<div class="col-lg-2">
						<span class="lineItemPopup cursorPointer" data-popup="BatchPopup" title="{vtranslate('Batch',$MODULE)}" data-module-name="Batch" data-field-name="batch_id">{Vtiger_Module_Model::getModuleIconPath('Batch')}</span>
					</div>						
				</div>					
			</div>				
		</div>
	</div>
	</td>	
	<td>		
	<input id="{$qty}" name="{$qty}" type="text" class="qty smallInputBox" value="{$data.$qty}"/>
	</td>
	<td>		
	<input id="" name="{$iten_no}" type="text" class="smallInputBox" value="{$data.$iten_no}"/>
	</td>
	<td>
		<div align="left">
			<select id="{$rej_reason}" name="{$rej_reason}" data-field-name="rej_reason" 
			data-fieldtype="picklist" 
			class="select inputElement lineItem" type="picklist">
				<option value="">Select an option</option>
				<option value="1"
				{if trim(decode_html($data.$rej_reason)) eq trim('1')} selected 
				{/if}>
				1</option>
				<option value="2"
				{if trim(decode_html($data.$rej_reason)) eq trim('2')} selected 
				{/if}>
				2</option>
				<option value="3"
				{if trim(decode_html($data.$rej_reason)) eq trim('3')} selected 
				{/if}>
				3</option>
			</select>
		</div>
	</td>
	<td>
		<div align="left">
			<select id="{$prd_mov_select_frm}" name="{$prd_mov_select_frm}" data-fieldname="prd_mov_select_frm" data-fieldtype="picklist" class="select inputElement lineItem" type="picklist">
				<option value="">Select an Option</option>
				<option value="Shot blasting"
				{if trim(decode_html($data.$prd_mov_select_frm)) eq trim('Shot blasting')} selected 
				{/if}>
				Shot blasting</option>
				<option value="Fettling Grinding"
				{if trim(decode_html($data.$prd_mov_select_frm)) eq trim('Fettling Grinding')} selected 
				{/if}>
				Fettling Grinding</option>
				<option value="Painting"
				{if trim(decode_html($data.$prd_mov_select_frm)) eq trim('Painting')} selected 
				{/if}>
				Painting</option>
				<option value="Crating"
				{if trim(decode_html($data.$prd_mov_select_frm)) eq trim('Crating')} selected 
				{/if}>
				Crating</option>
				<option value="Warehouse"
				{if trim(decode_html($data.$prd_mov_select_frm)) eq trim('Warehouse')} selected 
				{/if}>
				Warehouse</option>
			</select>
		</div>
	</td>		
	<td>
		<div align="left">
			<select id="{$prd_mov_select_to}" name="{$prd_mov_select_to}" data-fieldname="{$prd_mov_select_to}" data-fieldtype="picklist" class="select inputElement lineItem" type="picklist">
				<option
				{if trim(decode_html($data.$prd_mov_select_to)) eq trim('Rejection')} selected 
				{/if}>
				Rejection</option>
			</select>
		</div>
	</td>
{/strip}