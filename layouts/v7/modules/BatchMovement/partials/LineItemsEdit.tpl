{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
{assign var=LINE_ITEM_BLOCK_LABEL value="LBL_BATCHMOV_DETAILS"}
{assign var=BLOCK_FIELDS value=$RECORD_STRUCTURE.$LINE_ITEM_BLOCK_LABEL}
{assign var=BLOCK_LABEL value=$LINE_ITEM_BLOCK_LABEL}
{assign var="FINAL" value=$RELATED_BATCH}	
{*$LINEITEM_FIELDS['quantity']|@var_dump*}
							
<input type="hidden" name="totalBatchCount" id="totalBatchCount" value="1" />
	<div name='editContent'>
			{if $BLOCK_FIELDS|@count gt 0}
				<div class="fieldBlockContainer" data-block="{$BLOCK_LABEL}">
				<div class="row">
				<h4 class='fieldBlockHeader'>{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
				</div>
				<hr>
					<div class="lineitemTableContainer">
						<table class="table table-bordered" id="lineItemTab">
							<tr>
								<td><strong>{vtranslate('LBL_TOOLS',$MODULE)}</strong></td>
								<td><span class="redColor">*</span><strong>
								<strong>{vtranslate('LBL_BATCH_NAME',$MODULE)}</strong>
								</td>
								<td><strong>{vtranslate('LBL_QUANTITY',$MODULE)}</strong></td>
								<td><strong>{vtranslate('LBL_SELECT_FROM',$MODULE)}</strong></td>
								<td><strong>{vtranslate('LBL_SELECT_TO',$MODULE)}</strong></td>
							</tr>
							{if count($RELATED_BATCH) eq 0 }
							<tr id="row1" class="lineItemRow" data-row-num="1">
							{include file="partials/LineItemsContent.tpl"|@vtemplate_path:$MODULE row_no=1 data=[] IGNORE_UI_REGISTRATION=true}
							</tr>
							{/if}
							{foreach key=row_no item=data from=$RELATED_BATCH}
								<tr id="row{$row_no}" data-row-num="{$row_no}" class="lineItemRow"> 
								{include file="partials/LineItemsContent.tpl"|@vtemplate_path:$MODULE row_no=$row_no data=$data}
								</tr>
							{/foreach}
						</table>
					</div>
				</div>
			{/if}
		<br>		
		<div>			
			<div>				
				<div>
					<div class="btn-toolbar">
						<span class="btn-group">
							<button type="button" class="btn btn-default" id="addBatch" data-module-name="Batch" >
								<i class="fa fa-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_BATCH',$MODULE)}</strong>
							</button>
						</span>
					</div>
				</div>			
			</div>		
		</div>
	</div>
{/strip}
