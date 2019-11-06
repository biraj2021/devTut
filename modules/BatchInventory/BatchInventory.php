<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
include_once 'modules/Vtiger/CRMEntity.php';
class BatchInventory extends Vtiger_CRMEntity {
	
	var $table_name = 'vtiger_batchinventory';
	var $table_index= 'batchinventoryid';
	var $customFieldTable = Array('vtiger_batchinventorycf', 'batchinventoryid');
	var $tab_name = Array('vtiger_crmentity', 'vtiger_batchinventory', 'vtiger_batchinventorycf', 'vtiger_movementrel');
	var $tab_name_index = Array(
			'vtiger_crmentity' => 'crmid',
			'vtiger_batchinventory' => 'batchinventoryid',
			'vtiger_batchinventorycf'=>'batchinventoryid',
			'vtiger_movementrel'=>'id'
			);
	
	var $new_schema = true;
	
	var $update_product_array = Array();
	
	var $column_fields = Array();
	
	var $isLineItemUpdate = true;
	
	var $list_fields = Array (
			/* Format: Field Label => Array(tablename, columnname) */
			// tablename should not have prefix 'vtiger_'
			'Prd. Mov. Name' => Array('batchinventory', 'prd_mov_name'),
			'Product Movement No' => Array('batchinventory 	', 'prd_mov_no'),
			'Date' => Array('batchinventory', 'invbatch_date'),
			'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
			/* Format: Field Label => fieldname */
			'Prd. Mov. Name' => 'prd_mov_name',
			'Product Movement No' => 'prd_mov_no',
			'Date' => 'invbatch_date',
			'Assigned To' => 'assigned_user_id'
	);
	// Make the field link to detail view
	var $list_link_field = 'prd_mov_no';        
	// For Popup listview and UI type support
	var $search_fields = Array(
			/* Format: Field Label => Array(tablename, columnname) */
			// tablename should not have prefix 'vtiger_'
			'Prd. Mov. Name' => Array('batchinventory', 'prd_mov_name'),
			'Product Movement No' => Array('batchinventory 	', 'prd_mov_no'),
			'Date' => Array('batchinventory', 'invbatch_date'),
			'Assigned To' => Array('vtiger_crmentity','assigned_user_id')
	);
	var $search_fields_name = Array (
			/* Format: Field Label => fieldname */
			'Prd. Mov. Name' => 'prd_mov_name',
			'Product Movement No' => 'prd_mov_no',
			'Date' => 'invbatch_date',
			'Assigned To' => 'assigned_user_id'
	);
	// For Popup window record selection
	var $popup_fields = Array ('prd_mov_no');
	// For Alphabetical search
	var $def_basicsearch_col = 'prd_mov_no';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'prd_mov_no';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('prd_mov_no','createdtime','modifiedtime','assigned_user_id','itemid');
	var $default_order_by = 'prd_mov_no';
	var $default_sort_order='ASC';
	function __construct(){
		global $log, $currentModule;
		$this->column_fields = getColumnFields('BatchInventory');
		$this->db = new PearDatabase();
		$this->log = $log;
	}
	/** Constructor Function for BatchInventory class
	*  This function creates an instance of LoggerManager class using getLogger method
	*  creates an instance for PearDatabase class and get values for column_fields array of BatchInventory class.
	*/
	function BatchInventory() { 
		$this->log =LoggerManager::getLogger('BatchInventory');
		$this->log->debug("Entering BatchInventory() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('BatchInventory');
		$this->log->debug("Exiting BatchInventory method ...");
		

	}
	function save_module($module){ 
		//echo"<pre>";print_r($_REQUEST);exit(0);
		if (isset($_REQUEST['REQUEST_FROM_WS']) && $_REQUEST['REQUEST_FROM_WS']) { 
			unset($_REQUEST['totalProductCount']);
		}
		//in ajax save we should not call this function, because this will delete all the existing product values
		if(isset($_REQUEST)) { 
			if($_REQUEST['action'] != 'BatchInventoryAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
					&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
					&& $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {
			//Based on the total Number of rows we will save the Movement Product Batch relationship with this entity
			//echo"<pre>";print_r($_REQUEST); die;
			saveMovementProdBatchDetails($this, 'BatchInventory');
			} 
			
		}
		//Update the currency id and the conversion rate for the sales order
		//$update_query = "update vtiger_salesorder set currency_id=?, conversion_rate=? where salesorderid=?";
		//$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		//$this->db->pquery($update_query, $update_params);
	}
	/**
	 * Function to get Contact related Products
	 * @param  integer   $id  - batchinventoryid
	 * returns related Products record in array format
	 */
	 function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) { 
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}
		$query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode,
		 		  vtiger_products.commissionrate, vtiger_products.qty_per_unit, vtiger_products.unit_price,
				  vtiger_crmentity.crmid, vtiger_crmentity.smownerid,vtiger_contactdetails.lastname
				FROM vtiger_products
				INNER JOIN vtiger_seproductsrel
					ON vtiger_seproductsrel.productid=vtiger_products.productid and vtiger_seproductsrel.setype="Contacts"
				INNER JOIN vtiger_productcf
					ON vtiger_products.productid = vtiger_productcf.productid
				INNER JOIN vtiger_crmentity
					ON vtiger_crmentity.crmid = vtiger_products.productid
				INNER JOIN vtiger_contactdetails
					ON vtiger_contactdetails.contactid = vtiger_seproductsrel.crmid
				LEFT JOIN vtiger_users
					ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			   WHERE vtiger_contactdetails.contactid = '.$id.' and vtiger_crmentity.deleted = 0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	 }
	/** Function to get activities associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedActivities() method
	 */
	function get_activities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_activities(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/Activity.php");
		$other = new Activity();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="activity_mode">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				if(getFieldVisibilityPermission('Calendar',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.contactid, vtiger_activity.*,vtiger_seactivityrel.crmid as parent_id,vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime from vtiger_activity inner join vtiger_seactivityrel on vtiger_seactivityrel.activityid=vtiger_activity.activityid inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid left join vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid where vtiger_seactivityrel.crmid=".$id." and activitytype='Task' and vtiger_crmentity.deleted=0 and (vtiger_activity.status is not NULL and vtiger_activity.status != 'Completed') and (vtiger_activity.status is not NULL and vtiger_activity.status !='Deferred')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}
	/** Function to get the activities history associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedHistory() method
	 */
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_contactdetails.lastname, vtiger_contactdetails.firstname,
			vtiger_contactdetails.contactid,vtiger_activity.*, vtiger_seactivityrel.*,
			vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime,
			vtiger_crmentity.createdtime, vtiger_crmentity.description, case when
			(vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname
			end as user_name from vtiger_activity
				inner join vtiger_seactivityrel on vtiger_seactivityrel.activityid=vtiger_activity.activityid
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
				left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid
				left join vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid
                                left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
			where activitytype='Task'
				and (vtiger_activity.status = 'Completed' or vtiger_activity.status = 'Deferred')
				and vtiger_seactivityrel.crmid=".$id."
                                and vtiger_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('SalesOrder',$query,$id);
	}


	 
		
}