<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("../../config.inc.php"); 

require_once("../../modules/BatchMovement/BatchMovementHandler.php");


$connection = mysql_connect($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password']) or die('Please contact biraj.sharma@jhunsinfotech.com !'); 
mysql_select_db($dbconfig['db_name'], $connection);
$THIS_DIR = dirname(__FILE__);
if (file_exists($THIS_DIR.'/date_format.php') && file_exists($THIS_DIR.'/organisation_details.php')) { 
	include("date_format.php"); include("organisation_details.php");
}
/* include autoloader */
require_once '../autoload.inc.php';
/* reference the Dompdf namespace */
use Dompdf\Dompdf;
/* instantiate and use the dompdf class */
	$dompdf = new Dompdf();
	$html_to_pdf ='';
	//$slno=1;
$html_org ='<table width="100%" border="0">
			  <tr>
				<td align="center"><img src="../../test/logo/'.$organisation_logoname.'" style="max-width:325px; max-height:90px;" alt="http://www.superironfoundry.com/wp-content/themes/sif/images/logo.jpg" /> </td>
			  </tr>
			</table>'; 
$select_type = $_REQUEST['node_type'];		
$item_id = $_REQUEST['node_id'];
$muve = new BatchMovementHandler($_REQUEST['node_type'], $_REQUEST['node_id']);	
//echo($select_type.'///'.$item_id); die;
$today=date("d M Y");
	if($select_type =='salesOrder_id'){
	$salesOrder_id = $item_id;
	} 
	if($select_type =='product_id'){
		$prod_id = $item_id;
		if($prod_id != 0 && $prod_id !=''){
			$sql_prod = "SELECT *  FROM vtiger_batch_item 
						INNER JOIN vtiger_batch ON  vtiger_batch.batchid=vtiger_batch_item.id
						INNER JOIN vtiger_crmentity AS crm ON crm.crmid = vtiger_batch_item.id
						WHERE productid = '".$prod_id."'
						AND crm.deleted = 0
						GROUP BY sequence_no";  
			$query_prod = mysql_query($sql_prod); 
			$tot_record = mysql_num_rows($query_prod); 
		} 
	} 
//This script given below for Batch reports when select by product name <Begin here>
	if($select_type =='product_id'){
		while($prd_result = mysql_fetch_assoc($query_prod)){
			$salesorderid = $prd_result['salesorderid'];
			$prd_id = $prd_result['productid'];
			$batchid = $prd_result['batchid'];
			$batcName = $prd_result['batch_id'];
			//$get_rel_batch[] = get_relatedPrd_batch($moven_id, $batch_id);
		} 
		$html_header ='<br><br><div class="col-lg-12 frt">
				<table width="100%" border="0" class="small" cellspacing="1" cellpadding="3">
					<tr>
					<th style="font-weight:bold; text-align:left;"> <h1>Batch Report</h1> </th>
					</tr>';

		$html_subHeader ='<tr>
				<th style="font-weight:bold; text-align:left;"> <h3>Sales Order Number : '.getSalesOrderNoByProduct($prod_id).' </h3> </th>
				</tr>
				<tr>
				<th style="font-weight:bold; text-align:left;"><h3>Batch Name  : '.getBatchNameByProduct($prod_id).' </h3> </th>
				</tr>
				<tr>
				<th style="font-weight:bold; text-align:left;"><h3>Date: '.$today.'</h3></th>
				</tr>';
		
		$html_body_head = '<div class="col-lg-12 batch_report">   
		<div class="table-responsive">          
			<table width="100%" border="1" class="small" cellspacing="1" cellpadding="3">
				<thead>
				 <tr>
					<!--<th width="5%">Srl No.</th>-->
					<th width="15%">Product Name</th>
					<th width="5%">Start</th>
					<th width="8%">Molding</th>
					<th width="10%">Sh.Blasting</th>
					<th width="10%">Ft.Grinding</th>
					<th width="8%">Painting</th>
					<th width="8%">Crating</th>
					<th width="8%">Warehouse</th>
					<th width="10%">Rejection</th>
					<th width="8%">Dispatch</th>
					<th width="10%">Total</th>
				  </tr>
				</thead>
				<tbody>';
		$html_body_data = $muve->showRows();
			
	} 
//This script given above for Batch reports when select by product name <End here>
$html_subHeader .='<tr>
		<th style="font-weight:bold; text-align:left;"><h3>Total Record(s): '.$muve->showTotalRecord().'</h3></th>
		</tr>
		</table></div>';	
$html_to_link ='<link rel="stylesheet" href="../src/Css/style.css">
<link rel="stylesheet" href="../src/Css/bootstrap.min.css">
<div class="container">';
$html_to_body =$html_org .$html_header .$html_subHeader. $html_body_head .$html_body_data;
$html_to_foot ='</tbody></table></div></div></div>';
$html_to_pdf = $html_to_link.$html_to_body.$html_to_foot;	

echo ($html_to_pdf);  die;
$dompdf->loadHtml($html_to_pdf);
/* set page layout in PDF */
//$dompdf->set_paper("a4", "portrait");
/* Render the HTML as PDF */
$dompdf->render();
/* Set pdf name or file name */
if($salesOrder_id){
	$recDate = date('d.m.Y').'_Batch Report_'.$salesOrder_id;
} else {
	$recDate = date('d.m.Y').'_Batch Report_'.$prod_id;	
}
$filename = $recDate . '.pdf';
/* Output the generated PDF to Browser */
$dompdf->stream($filename);
//============================================================+
// END OF FILE
//============================================================+

function getSalesOrderNoByProduct($id){ 
		$sql_n ="SELECT 
					sales.salesorder_no
				FROM vtiger_inventoryproductrel AS rel
					INNER JOIN vtiger_crmentity AS crm ON crm.crmid = rel.productid
					INNER JOIN vtiger_salesorder AS sales ON sales.salesorderid = rel.id
				WHERE crm.deleted = 0 
					AND productid =".$id;
		$select_sl=mysql_query($sql_n);
		$tot_record = mysql_num_rows($select_sl); 
		if($tot_record > 0){
			$result_salesOrder = mysql_result($select_sl, 0, 'salesorder_no');
		} else {
			$result_salesOrder = 'No Sales Order';
		}
		//print_r($result_salesOrder); die('tr');
		return $result_salesOrder;
}

function getBatchNameByProduct($id = null){ //echo $id; die; //2461
		$sql_b ="SELECT batch_id  FROM vtiger_batch_item 
		INNER JOIN vtiger_batch ON vtiger_batch.batchid=vtiger_batch_item.id
		INNER JOIN vtiger_crmentity AS crm ON crm.crmid = vtiger_batch_item.id
		WHERE productid = '".$id."' 
		AND crm.deleted = 0
		GROUP BY sequence_no";  
		$select_batch=mysql_query($sql_b); 
		$tot_record = mysql_num_rows($select_batch); 
		if($tot_record > 0){
			$result_batch_name = mysql_result($select_batch, 0, 'batch_id'); 
		} else {
			$result_batch_name = 'No Batch name';
		}
		//echo"<pre>";print_r($result_batch_name); exit(0);
		return $result_batch_name;
}

/*function get_relatedPrd_batch($moven_id, $batch_id){ 
	//2473//2457//2450//2202
	//echo $moven_id.'//'. $batch_id .'//'.$salesorderid.'//'.$productid; die;
	$sql_batch = "SELECT move.* 
					FROM vtiger_movementrel AS move
						INNER JOIN vtiger_crmentity AS crm ON crm.crmid = move.id
					WHERE move.id = '".$moven_id."' 
						AND move.itemid = '".$batch_id."'
						AND crm.deleted = 0";  
	$select_batch=mysql_query($sql_batch);
	$int = 0;
	while($result_batch=mysql_fetch_array($select_batch)){
		//echo"<pre>"; print_r($result_batch); 
		$rel_inv_item[$int]['moven_id']=$result_batch['id'];
		$rel_inv_item[$int]['quantity']=$result_batch['quantity'];
		$rel_inv_item[$int]['select_frm']=$result_batch['select_frm'];
		$rel_inv_item[$int]['select_to']=$result_batch['select_to'];
		$int++;
	} //die('rel');
	//echo"<pre>"; print_r($rel_inv_item); die;
	return $rel_inv_item; 
}*/

?>


