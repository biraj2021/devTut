<?php
include_once 'modules/Vtiger/CRMEntity.php';

class BatchMovement extends Vtiger_CRMEntity {
        var $table_name = 'vtiger_batchmovement';
        var $table_index= 'batchmovementid';

        var $customFieldTable = Array('vtiger_batchmovementcf', 'batchmovementid');

        var $tab_name = Array('vtiger_crmentity', 'vtiger_batchmovement','vtiger_batchmovementcf' , 'vtiger_movementrel'); 

        var $tab_name_index = Array(
                'vtiger_crmentity' => 'crmid',
                'vtiger_batchmovement' => 'batchmovementid',
                'vtiger_batchmovementcf'=>'batchmovementid',
				'vtiger_movementrel'=>'id'	
               );

        var $list_fields = Array (
                /* Format: Field Label => Array(tablename, columnname) */
                // tablename should not have prefix 'vtiger_'
                'Batch Movement No.' => Array('batchmovement', 'batch_mov_number'),
                'Batch Mov. Name' => Array('batchmovement', 'batch_mov_name'),
                'Date' => Array('batchmovement', 'batch_mov_date'),
                'Assigned To' => Array('crmentity','smownerid')
        );
        var $list_fields_name = Array (
                /* Format: Field Label => fieldname */
				'Batch Movement No.' => 'batch_mov_number',
                'Batch Mov. Name' => 'batch_mov_name',
                'Date' => 'batch_mov_date',
                'Assigned To' => 'assigned_user_id'
        );

        // Make the field link to detail view
        var $list_link_field = 'batch_mov_number';

        // For Popup listview and UI type support
        var $search_fields = Array(
                /* Format: Field Label => Array(tablename, columnname) */
                // tablename should not have prefix 'vtiger_'
				'Batch Movement No.' => Array('batchmovement', 'batch_mov_number'),
                'Batch Mov. Name' => Array('batchmovement', 'batch_mov_name'),
                'Date' => Array('batchmovement', 'batch_mov_date'),
                'Assigned To' => Array('vtiger_crmentity','assigned_user_id')
        );
        var $search_fields_name = Array (
                /* Format: Field Label => fieldname */
                'Batch Movement No.' => 'batch_mov_number',
                'Batch Mov. Name' => 'batch_mov_name',
                'Date' => 'batch_mov_date',
                'Assigned To' => 'assigned_user_id'
        );

        // For Popup window record selection
        var $popup_fields = Array ('batch_mov_number');

        // For Alphabetical search
        var $def_basicsearch_col = 'batch_mov_number';

        // Column value to use on detail view record text display
        var $def_detailview_recname = 'batch_mov_number';

        // Used when enabling/disabling the mandatory fields for the module.
        // Refers to vtiger_field.fieldname values.
        var $mandatory_fields = Array('batch_mov_number','assigned_user_id');

        var $default_order_by = 'batch_mov_number';
        var $default_sort_order='ASC';
		
	function __construct(){
		global $log, $currentModule;
		$this->column_fields = getColumnFields('BatchMovement');
		$this->db = new PearDatabase();
		$this->log = $log;
		}
	/** Constructor Function for BatchMovement class
	*  This function creates an instance of LoggerManager class using getLogger method
	*  creates an instance for PearDatabase class and get values for column_fields array of BatchMovement class.
	*/
	function BatchMovement() { 
		$this->log =LoggerManager::getLogger('BatchMovement');
		$this->log->debug("Entering BatchMovement() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('BatchMovement');
		$this->log->debug("Exiting BatchMovement method ...");
		

	}
	function save_module($module){ 
		//echo"<pre>";print_r($_REQUEST);die('First');
		//in ajax save we should not call this function, because this will delete all the existing product values
		if(isset($_REQUEST)) { 
			if($_REQUEST['action'] != 'BatchMovementAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
					&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
					&& $_REQUEST['action'] != 'SaveAjax' && $_REQUEST['action'] != 'FROM_WS') {
			//Based on the total Number of rows we will save the Movement Product Batch relationship with this entity
			
			saveMovementBatchDetails($this, 'BatchMovement');
			} 
			
		}
		//Update the currency id and the conversion rate for the sales order
		//$update_query = "update vtiger_salesorder set currency_id=?, conversion_rate=? where salesorderid=?";
		//$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		//$this->db->pquery($update_query, $update_params);
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