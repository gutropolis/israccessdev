<?php
namespace App\Controllers;

use App\Models\User;
use App\Models;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

use Dompdf\Dompdf;
/*
*  Admin Reports Controller
*  
1.  General data
2.  By event (accounting approach)
3.  By event (sales approach)
4.  By Productor
5.  CulturAccess

*/
class AdminReportsController extends Base 
{
	protected $container;
	protected $lang;
	protected $servername;
	protected $username;
	protected $password;
	protected $dbname;
	protected $conn;
	public function __construct($container)
	{
		set_time_limit(0);
		$this->container = $container;
		$this->servername = $this->container['settings']['database']['host'];
		$this->username = $this->container['settings']['database']['username'];
		$this->password = $this->container['settings']['database']['password'];
		$this->dbname = $this->container['settings']['database']['database'];
		$this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
		$this->lang =  $this->container->view['adminLang'];
	}
	
	// Reports Dashboard
	public function reports($request, $response) {
        $params = array( 'title' => 'Reports Dashboard',
						  'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Reports/reports_dashboard.twig',$params);
    }
	
	// General data Report
	public function general_data_report($request, $response) {
        $params = array( 'title' => 'General Data Report',
						  'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Reports/general_data_report.twig',$params);
    }
	
	// Download General data Report as Excel
    public function downloadGeneralDataReportXLS($request, $response, $args){
	       // Get the required parameters
		   $event_group_id = $args['event_group_id'];
		   $event_id = $args['event_id'];
		   $from_date_val = $args['from_date'];
		   $to_date_val = $args['to_date'];
		   ddump($_REQUEST);
		   if($event_id > 0){
		      $where_clause = " AND OI.product_id=".$event_id." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
		   }else{
		     // Get all events comma separated
			 $sql_ids = "SELECT GROUP_CONCAT(id) AS event_ids FROM `events` WHERE `eventgroup_id`=".$event_group_id." ";
			 $resultQuery = mysqli_query($this->conn, $sql_ids);
			 $result = mysqli_fetch_assoc($resultQuery);
			 $event_ids = $result['event_ids'];
			 if($event_ids != ''){
			    $where_clause = " AND OI.product_id IN(".$event_ids.") AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }else{
			    $where_clause = " AND O.id < 1 AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }
		   }
		   
			$htmlcontent='<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
    <html>
        <head><meta http-equiv=\"Content-type\" content=\"text/html;charset=utf-8\" /></head>
        <body>';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';	
			$ROStyle = 'background-color:#d93025; color:#fff;';
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = 	'<h1><center>'.$this->lang['report_general_data_txt'].'</center></h1>';	
			$reportTable .= '<table border="1">
						  <thead>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_id_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_last_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_email_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_date_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_city_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_seat_number_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_category_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_row_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_seats_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_date_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_time_txt']).'</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
				$sqlReport = "SELECT 
							  O.id AS order_id,
							  UM.last_name AS user_last_name,
							  U.name AS user_name,
							  U.email,
							  E.title AS event_name,
							  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
							  C.name AS city_name,
							  O.total_amount,
							  OI.seat_sequence,
							  OI.ticket_category,
							  OI.ticket_row,
							  OI.seat_qty,
							  O.status AS order_status,
							  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
							  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
							FROM
							  `orders` AS O 
							  INNER JOIN `orderitems` AS OI 
								ON O.`id` = OI.`order_id` 
							  INNER JOIN `users` AS U 
								ON U.id = O.`customer_id` 
							  LEFT JOIN `user_meta` AS UM 
								ON UM.`user_id` = O.`customer_id` 
							  INNER JOIN `events` AS E 
								ON E.`id` = OI.`product_id` 
							  INNER JOIN `cities` AS C 
								ON E.`city_id` = C.`id` 
								WHERE O.status <> 'R' $where_clause
								GROUP BY OI.order_id 
                                ORDER BY OI.created_on DESC  ";	
								
			$resultQuery = mysqli_query($this->conn, $sqlReport);
			$i=0;
			while($row = mysqli_fetch_assoc($resultQuery)){
				//mysqli_set_charset($this->conn,"utf8");
				//array_map("utf8_decode", $row);
				  if($i % 2 == 0){
					  $style = $NthStyle;	
					}else{
					  $style = $EthStyle;	
					}
					$order_status = $row['order_status'];
					if($order_status == 'R'){
					   $style = $ROStyle;
					}else{
					   $style =  $style;
					}
				$reportTable .= '<tr>
									  <td style="'.$style.'">'.$row['order_id'].'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap($row['user_last_name'],10).'</td>
									  <td style="'.$style.'">'.$row['user_name'].'</td>
									  <td style="'.$style.'">'.$row['email'].'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap(trim(strip_tags(trim(clearString(htmlspecialchars_decode($row['event_name'],10)))))).'</td>
									  <td style="'.$style.'">'.$row['event_date'].'</td>
									  <td style="'.$style.'">'.$row['city_name'].'</td>
									  <td style="'.$style.'">'.$row['total_amount'].'</td>
									  <td style="'.$style.'">'. $this->smart_wordwrap($row['seat_sequence'],10).'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap($row['ticket_category'],10).'</td>
									  <td style="'.$style.'">'.$row['ticket_row'].'</td>
									  <td style="'.$style.'">'.$row['seat_qty'].'</td>
									  <td style="'.$style.'">'.$row['order_date'].'</td>
									  <td style="'.$style.'">'.$row['order_time'].'</td>
								</tr>'; 	 	
			}
			$htmlcontent.= $reportTable;	   
			
			$htmlcontent .= '</table></body></html>';
			$html = $htmlcontent;
			//header("Content-Type: application/xls");
			header( "Content-type: application/vnd.ms-excel; charset=UTF-8" ); 
			//header("Content-type: application/vnd.ms-excel");
			$file_name = 'GeneralDataReport_'.time();   
			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			//header("Content-Type: application/vnd.ms-excel");
			 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			//echo "\xEF\xBB\xBF";
			//echo pack("CCC",0xef,0xbb,0xbf);
            //header('Content-type: application/x-msdownload; charset=utf-16');

			echo $html;
			exit; // This is very important for downloading the XLS 
											  
    }
	
	
	
	// By event (accounting approach) Report
	public function accounting_report($request, $response) {
        $params = array( 'title' => 'By event (accounting approach) Report',
						  'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Reports/accounting_report.twig',$params);
    }
	
	// Download By event (accounting approach) Report as Excel
    public function downloadAccountingReportXLS($request, $response, $args){
	// Get the required parameters
		   $event_group_id = $args['event_group_id'];
		   $event_id = $args['event_id'];
		   $from_date_val = $args['from_date'];
		   $to_date_val = $args['to_date'];
		  
		   
		$htmlcontent='<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
    <html>
        <head><meta http-equiv=\"Content-type\" content=\"text/html;charset=utf-8\" /></head>
        <body>';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';	
			$ROStyle = 'background-color:#d93025; color:#fff;';
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = '';
			$reportTable .= 	'<h1 style="background-color:#fff2cb; color:#000"><center>'.$this->lang['report_by_event_accounting_approach_txt'].'</center></h1>';	
			$sqlEvents = "SELECT 
						  E.id AS event_id,
						  E.title AS event_name,
						  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
						  C.name AS city_name 
						FROM
						  `events` AS E 
						  INNER JOIN `cities` AS C 
							ON E.`city_id` = C.`id`
							INNER JOIN `orderitems` AS OI  ON E.`id`=OI.`product_id` 
							 GROUP BY E.`id`
						ORDER BY E.date DESC ";
			$resultQuery = mysqli_query($this->conn, $sqlEvents);
			
			while($Event = mysqli_fetch_assoc($resultQuery)){
			if($event_id > 0){
		      $where_clause = " AND OI.product_id=".$event_id." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
		   }else{
		     // Get all events comma separated
			 $sql_ids = "SELECT GROUP_CONCAT(id) AS event_ids FROM `events` WHERE `eventgroup_id`=".$event_group_id." ";
			 $resultQuery = mysqli_query($this->conn, $sql_ids);
			 $result = mysqli_fetch_assoc($resultQuery);
			 $event_ids = $result['event_ids'];
			 if($event_ids != ''){
			    $where_clause = " AND OI.product_id IN(".$event_ids.") AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }else{
			    $where_clause = " AND OI.`product_id`=".$Event['event_id']." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }
		   }
			     $reportTable .= '<table border="1">
						  <thead>
						  <tr>
							  <th style=" text-align:center; background-color:#fff2cb; color:#000 " colspan="9" align="center"><h3>'.trim(strip_tags(trim(clearString(htmlspecialchars_decode($Event['event_name']))))).' - '.$Event['event_date'].' -'.$Event['city_name'].'</h3></th>
							</tr>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_id_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_invoice_id_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_last_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_date_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_booking_fee_total_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_comission_fee_total_txt']).'</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
					$sqlReport = "SELECT O.id AS order_id, O.invoice_number, UM.last_name AS user_last_name, U.name AS user_name, DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,O.status AS order_status,
								E.title AS event_name,O.total_amount, (E.booking_fee  * OI.`seat_qty`) AS booking_fee, OI.`product_id`,E.`commission_fee`
								FROM `orders` AS O INNER JOIN `orderitems` AS OI 
								ON
								 O.`id`=OI.`order_id`
								 INNER JOIN `users` AS U ON U.id=O.`customer_id` LEFT JOIN `user_meta` AS UM ON UM.`user_id`=O.`customer_id`
								 INNER JOIN `events` AS E ON E.id=OI.`product_id` 
								 WHERE  
                                  O.status <> 'R' $where_clause
								 ORDER BY O.`created_on` DESC  ";	
					$resultQueryRe = mysqli_query($this->conn, $sqlReport);
					$num_rows = mysqli_num_rows($resultQueryRe);
					if($num_rows < 1){
						$reportTable .= '<tr>
												  <td  colspan="9" style="color:red; text-align:center">'.$this->lang['no_data_found_txt'].'</td>
											</tr>'; 
					}else{
						$i=0;
						while($row = mysqli_fetch_assoc($resultQueryRe)){
							  if($i % 2 == 0){
								  $style = $NthStyle;	
								}else{
								  $style = $EthStyle;	
								}
								$order_status = $row['order_status'];
								if($order_status == 'R'){
								   $style = $ROStyle;
								}else{
								   $style =  $style;
								}
							$reportTable .= '<tr>
												  <td style="'.$style.'">'.$row['order_id'].'</td>
												  <td style="'.$style.'">'.$row['invoice_number'].'</td>
												  <td style="'.$style.'">'.$this->smart_wordwrap($row['user_last_name'],20).'</td>
												  <td style="'.$style.'">'.$row['user_name'].'</td>
												  <td style="'.$style.'">'.$row['order_date'].'</td>
												  <td style="'.$style.'">'.$this->smart_wordwrap($row['event_name'],50).'</td>
												  <td style="'.$style.'">'.$row['total_amount'].'</td>
												  <td style="'.$style.'">'.$row['booking_fee'].'</td>
												  <td style="'.$style.'">'.$row['commission_fee'].'</td>
											</tr>'; 	
						}
					}
					
					$reportTable .= '
			        </tbody></table><br><br>';
				  
			}
			
			$htmlcontent .= $reportTable.'</body></html>';
			
			$html = ($htmlcontent);
			//echo $html; exit;
			$file_name = 'AccountingReport_'.time();      
			header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );  
			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			echo $html;
			exit; // This is very important for downloading the XLS 
	}
	
	// By event (sales approach)
	public function sales_report($request, $response) {
        $params = array( 'title' => 'By event (sales approach)',
						  'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Reports/sales_report.twig',$params);
    }
	
	// Download By event (sales approach) Report as Excel
    public function downloadSalesReportXLS($request, $response, $args){
	// Get the required parameters
		   $event_group_id = $args['event_group_id'];
		   $event_id = $args['event_id'];
		   $from_date_val = $args['from_date'];
		   $to_date_val = $args['to_date'];
		   
		$htmlcontent='<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
    <html>
        <head><meta http-equiv=\"Content-type\" content=\"text/html;charset=utf-8\" /></head>
        <body>';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';	
			$ROStyle = 'background-color:#d93025; color:#fff;';
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = '';
			$reportTable .= 	'<h1 style="background-color:#fff2cb; color:#000"><center>'.$this->lang['report_by_event_sale_approach_txt'].'</center></h1>';	
			$sqlEvents = "SELECT 
						  E.id AS event_id,
						  E.title AS event_name,
						  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
						  C.name AS city_name 
						FROM
						  `events` AS E 
						  INNER JOIN `cities` AS C 
							ON E.`city_id` = C.`id`
							INNER JOIN `orderitems` AS OI  ON E.`id`=OI.`product_id` 
							 GROUP BY E.`id`
						ORDER BY E.date DESC ";
			$resultQuery = mysqli_query($this->conn, $sqlEvents);
			
			while($Event = mysqli_fetch_assoc($resultQuery)){
			if($event_id > 0){
		      $where_clause = " AND OI.product_id=".$event_id." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
		   }else{
		     // Get all events comma separated
			 $sql_ids = "SELECT GROUP_CONCAT(id) AS event_ids FROM `events` WHERE `eventgroup_id`=".$event_group_id." ";
			 $resultQuery = mysqli_query($this->conn, $sql_ids);
			 $result = mysqli_fetch_assoc($resultQuery);
			 $event_ids = $result['event_ids'];
			 if($event_ids != ''){
			    $where_clause = " AND OI.product_id IN(".$event_ids.") AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }else{
			    $where_clause = " AND OI.`product_id`=".$Event['event_id']." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }
		   }
			     $reportTable .= '<table border="1">
						  <thead>
						  <tr>
							  <th style=" text-align:center; background-color:#fff2cb; color:#000 " colspan="7" align="center"><h3>'.trim(strip_tags(trim(clearString(htmlspecialchars_decode($Event['event_name']))))).' - '.$Event['event_date'].' -'.$Event['city_name'].'</h3></th>
							</tr>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_last_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_phone_number_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_category_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_row_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_seats_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_amount_txt']).'</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
					$sqlReport = "SELECT O.id AS order_id, O.invoice_number, UM.last_name AS user_last_name,   UM.`phone_no`, U.name AS user_name, DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,O.status AS order_status,
								  E.title AS event_name,O.total_amount, (E.booking_fee  * OI.`seat_qty`) AS booking_fee, OI.`product_id`,E.`commission_fee`,OI.seat_sequence,
								  OI.ticket_category,
								  OI.ticket_row,
								  OI.seat_qty,
								  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
								  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
								FROM `orders` AS O INNER JOIN `orderitems` AS OI 
								ON
								 O.`id`=OI.`order_id`
								 INNER JOIN `users` AS U ON U.id=O.`customer_id` LEFT JOIN `user_meta` AS UM ON UM.`user_id`=O.`customer_id`
								 INNER JOIN `events` AS E ON E.id=OI.`product_id` 
								 WHERE  O.status <> 'R' $where_clause
								 ORDER BY O.`created_on` DESC  ";	
					$resultQueryRe = mysqli_query($this->conn, $sqlReport);
					$num_rows = mysqli_num_rows($resultQueryRe);
					if($num_rows < 1){
						$reportTable .= '<tr>
												  <td  colspan="7" style="color:red; text-align:center">'.$this->lang['no_data_found_txt'].'</td>
											</tr>'; 
					}else{
						$i=0;
						while($row = mysqli_fetch_assoc($resultQueryRe)){
							mysqli_set_charset($this->conn,"utf8");
							  if($i % 2 == 0){
								  $style = $NthStyle;	
								}else{
								  $style = $EthStyle;	
								}
								$order_status = $row['order_status'];
								if($order_status == 'R'){
								   $style = $ROStyle;
								}else{
								   $style =  $style;
								}
							$reportTable .= '<tr>
												  <td style="'.$style.'">'.$this->smart_wordwrap($row['user_last_name'],20).'</td>
												  <td style="'.$style.'">'.$row['user_name'].'</td>
												  <td style="'.$style.'">'.$row['phone_no'].'</td>
												  <td style="'.$style.'">'.$row['ticket_category'].'</td>
												  <td style="'.$style.'">'.$row['ticket_row'].'</td>
												  <td style="'.$style.'">'.$row['seat_qty'].'</td>
												  <td style="'.$style.'">'.$row['total_amount'].'</td>
											</tr>'; 	
						}
					}
					
					$reportTable .= '
			        </tbody></table><br><br>';
				  
			}
			
			$htmlcontent .= $reportTable.'</body></html>';
			
			$html = ($htmlcontent);
			//echo $html; exit;
			$file_name = 'SalesReport_'.time();    
			header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );  
			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			echo $html;
			exit; // This is very important for downloading the XLS 
	}
	
	// By Productor Report
	public function by_productor_report($request, $response) {
        $params = array( 'title' => 'By Productor Report',
						  'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Reports/by_productor_report.twig',$params);
    }
	
	// Download By Productor Report Report as Excel
    public function downloadByProductorReportXLS($request, $response, $args){
	// Get the required parameters
		   $event_group_id = $args['event_group_id'];
		   $event_id = $args['event_id'];
		   $from_date_val = $args['from_date'];
		   $to_date_val = $args['to_date'];
		   
		$htmlcontent='<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
    <html>
        <head><meta http-equiv=\"Content-type\" content=\"text/html;charset=utf-8\" /></head>
        <body>';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';
			$ROStyle = 'background-color:#d93025; color:#fff;';	
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = '';
			$reportTable .= 	'<h1 style="background-color:#fff2cb; color:#000"><center>'.$this->lang['report_by_productore_txt'].'</center></h1>';	
			$sqlEvents = "SELECT 
						  E.id AS event_id,
						  E.title AS event_name,
						  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
						  C.name AS city_name 
						FROM
						  `events` AS E 
						  INNER JOIN `cities` AS C 
							ON E.`city_id` = C.`id`
							INNER JOIN `orderitems` AS OI  ON E.`id`=OI.`product_id` 
							WHERE OI.`producer_id` > 0 
							 GROUP BY E.`id`
						ORDER BY E.date DESC ";
			$resultQuery = mysqli_query($this->conn, $sqlEvents);
			
			while($Event = mysqli_fetch_assoc($resultQuery)){
			if($event_id > 0){
		      $where_clause = " AND OI.product_id=".$event_id." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
		   }else{
		     // Get all events comma separated
			 $sql_ids = "SELECT GROUP_CONCAT(id) AS event_ids FROM `events` WHERE `eventgroup_id`=".$event_group_id." ";
			 $resultQuery = mysqli_query($this->conn, $sql_ids);
			 $result = mysqli_fetch_assoc($resultQuery);
			 $event_ids = $result['event_ids'];
			 if($event_ids != ''){
			    $where_clause = " AND OI.product_id IN(".$event_ids.") AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }else{
			    $where_clause = " AND OI.`product_id`=".$Event['event_id']." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }
		   }
			     $reportTable .= '<table border="1">
						  <thead>
						  <tr>
							  <th style=" text-align:center; background-color:#fff2cb; color:#000 " colspan="12" align="center"><h3>'.trim(strip_tags(trim(clearString(htmlspecialchars_decode($Event['event_name']))))).' - '.$Event['event_date'].' -'.($Event['city_name']).'</h3></th>
							</tr>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_user_last_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_user_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['admin_email_label_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_category_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_total_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_date_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_number_of_seats_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_average_unit_price_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_total_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_amount_culturaccess_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_amount_productor_txt']).'</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
					$sqlReport = "SELECT 
								  O.id AS order_id,
								  O.invoice_number,
								  UM.last_name AS user_last_name,
								  UM.`phone_no`,
								  U.name AS user_name,
								  U.email,
								  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
								  E.title AS event_name,
								  O.total_amount,
								  (E.booking_fee * OI.`seat_qty`) AS booking_fee,
								  OI.`product_id`,
								  E.`commission_fee`,
								  OI.seat_sequence,
								  OI.ticket_category,
								  OI.ticket_row,
								  OI.seat_qty,
								  O.status AS order_status,
								  (OI.price/OI.seat_qty) AS average_unit_price,
								  (OI.price - (E.booking_fee * OI.seat_qty)) AS culturaccess_amount,
								  ((OI.price - (E.booking_fee * OI.seat_qty)) * E.commission_fee) AS productor_amount,
								  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
								  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
								FROM
								  `orders` AS O 
								  INNER JOIN `orderitems` AS OI 
									ON O.`id` = OI.`order_id` 
								  INNER JOIN `users` AS U 
									ON U.id = O.`customer_id` 
								  LEFT JOIN `user_meta` AS UM 
									ON UM.`user_id` = O.`customer_id` 
								  INNER JOIN `events` AS E 
									ON E.id = OI.`product_id` 
								WHERE  O.status <> 'R' $where_clause
								ORDER BY O.`created_on` DESC  ";	
					$resultQueryRe = mysqli_query($this->conn, $sqlReport);
					$num_rows = mysqli_num_rows($resultQueryRe);
					if($num_rows < 1){
						$reportTable .= '<tr>
												  <td  colspan="12" style="color:red; text-align:center">'.$this->lang['no_data_found_txt'].'</td>
											</tr>'; 
					}else{
						$i=0;
						while($row = mysqli_fetch_assoc($resultQueryRe)){
							  if($i % 2 == 0){
								  $style = $NthStyle;	
								}else{
								  $style = $EthStyle;	
								}
								$order_status = $row['order_status'];
								if($order_status == 'R'){
								   $style = $ROStyle;
								}else{
								   $style =  $style;
								}
							$reportTable .= '<tr>
												  <td style="'.$style.'">'.$this->smart_wordwrap($row['user_last_name'],20).'</td>
												  <td style="'.$style.'">'.$row['user_name'].'</td>
												  <td style="'.$style.'">'.$row['email'].'</td>
												  <td style="'.$style.'">'.$this->smart_wordwrap(trim(strip_tags(trim(clearString(htmlspecialchars_decode($row['event_name'],10)))))).'</td>
												  <td style="'.$style.'">'.$row['ticket_category'].'</td>
												  <td style="'.$style.'">'.$row['total_amount'].'</td>
												  <td style="'.$style.'">'.hr_date($row['order_date']).'</td>
												  <td style="'.$style.'">'.$row['seat_qty'].'</td>
												  <td style="'.$style.'">'.$row['average_unit_price'].'</td>
												  <td style="'.$style.'">'.$row['total_amount'].'</td>
												  <td style="'.$style.'">'.$row['culturaccess_amount'].'</td>
												  <td style="'.$style.'">'.$row['productor_amount'].'</td>
											</tr>'; 	
						}
					}
					
					$reportTable .= '
			        </tbody></table><br><br>';
				  
			}
			
			$htmlcontent .= $reportTable.'</body></html>';
			
			$html = ($htmlcontent);
			//echo $html; exit;
			$file_name = 'ProductorReport_'.time();   
			header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );  
			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			echo $html;
			exit; // This is very important for downloading the XLS 
	}
	
	// CulturAccess Report
	public function culturaccess_report($request, $response) {
        $params = array( 'title' => 'CulturAccess Report',
						  'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Reports/culturaccess_report.twig',$params);
    }
	
	
	// Download CultureAccess Report as Excel
    public function downloadCulturaccessReportXLS($request, $response, $args){
	// Get the required parameters
		   $event_group_id = $args['event_group_id'];
		   $event_id = $args['event_id'];
		   $from_date_val = $args['from_date'];
		   $to_date_val = $args['to_date'];
		   
		 $htmlcontent='<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
    <html>
        <head><meta http-equiv=\"Content-type\" content=\"text/html;charset=utf-8\" /></head>
        <body>';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';	
			$ROStyle = 'background-color:#d93025; color:#fff;';
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = '';
			$reportTable .= 	'<h1 style="background-color:#fff2cb; color:#000"><center>'.$this->lang['report_by_culturAccess_txt'].'</center></h1>';	
			$sqlEvents = "SELECT 
						  E.id AS event_id,
						  E.title AS event_name,
						  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
						  C.name AS city_name 
						FROM
						  `events` AS E 
						  INNER JOIN `cities` AS C 
							ON E.`city_id` = C.`id`
							INNER JOIN `orderitems` AS OI  ON E.`id`=OI.`product_id` 
							WHERE OI.`producer_id` > 0 
							 GROUP BY E.`id`
						ORDER BY E.date DESC ";
			$resultQuery = mysqli_query($this->conn, $sqlEvents);
			
			while($Event = mysqli_fetch_assoc($resultQuery)){
			if($event_id > 0){
		      $where_clause = " AND OI.product_id=".$event_id." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
		   }else{
		     // Get all events comma separated
			 $sql_ids = "SELECT GROUP_CONCAT(id) AS event_ids FROM `events` WHERE `eventgroup_id`=".$event_group_id." ";
			 $resultQuery = mysqli_query($this->conn, $sql_ids);
			 $result = mysqli_fetch_assoc($resultQuery);
			 $event_ids = $result['event_ids'];
			 if($event_ids != ''){
			    $where_clause = " AND OI.product_id IN(".$event_ids.") AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }else{
			    $where_clause = " AND OI.`product_id`=".$Event['event_id']." AND DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val." '";
			 }
		   }
			     $reportTable .= '<table border="1">
						  <thead>
						  <tr>
							  <th style=" text-align:center; background-color:#fff2cb; color:#000 " colspan="15" align="center"><h3>'.trim(strip_tags(trim(clearString(htmlspecialchars_decode($Event['event_name']))))).' - '.$Event['event_date'].' -'.($Event['city_name']).'</h3></th>
							</tr>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_user_last_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_user_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['admin_email_label_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_date_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_category_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_row_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_seats_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_average_unit_price_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_number_of_seats_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_total_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_booking_fee_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_total_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_amount_culturaccess_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_amount_productor_txt']).'</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
					$sqlReport = "SELECT 
								  O.id AS order_id,
								  O.invoice_number,
								  UM.last_name AS user_last_name,
								  UM.`phone_no`,
								  U.name AS user_name,
								  U.email,
								  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
								  E.title AS event_name,
								  O.total_amount,
								  (E.booking_fee * OI.`seat_qty`) AS booking_fee,
								  OI.`product_id`,
								  E.`commission_fee`,
								  E.booking_fee AS booking_fee_amount,
								  OI.seat_sequence,
								  OI.ticket_category,
								  OI.ticket_row,
								  OI.seat_qty,
								  O.status AS order_status,
								  (OI.price - (E.booking_fee * OI.seat_qty)) AS total_amount_without_booking_fee,
								  (OI.price/OI.seat_qty) AS average_unit_price,
								  (OI.price - (E.booking_fee * OI.seat_qty))  AS culturaccess_amount,
								  ((OI.price - (E.booking_fee * OI.seat_qty))) AS productor_amount,
								  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
								  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
								FROM
								  `orders` AS O 
								  INNER JOIN `orderitems` AS OI 
									ON O.`id` = OI.`order_id` 
								  INNER JOIN `users` AS U 
									ON U.id = O.`customer_id` 
								  LEFT JOIN `user_meta` AS UM 
									ON UM.`user_id` = O.`customer_id` 
								  INNER JOIN `events` AS E 
									ON E.id = OI.`product_id` 
								WHERE   O.status <> 'R'  $where_clause
								ORDER BY O.`created_on` DESC  ";	
					$resultQueryRe = mysqli_query($this->conn, $sqlReport);
					$num_rows = mysqli_num_rows($resultQueryRe);
					if($num_rows < 1){
						$reportTable .= '<tr>
												  <td  colspan="15" style="color:red; text-align:center">'.$this->lang['no_data_found_txt'].'</td>
											</tr>'; 
					}else{
						$i=0;
						while($row = mysqli_fetch_assoc($resultQueryRe)){
							  if($i % 2 == 0){
								  $style = $NthStyle;	
								}else{
								  $style = $EthStyle;	
								}
								$order_status = $row['order_status'];
								if($order_status == 'R'){
								   $style = $ROStyle;
								}else{
								   $style =  $style;
								}
						   $commission_fee = $row['commission_fee'];
						   if($commission_fee > 0){		
						     $culturaccess_amount = ($row['culturaccess_amount']/100) * $commission_fee;		
						   }else{
							 $culturaccess_amount = $row['culturaccess_amount'];		  
						   }
							$reportTable .= '<tr>
												  <td style="'.$style.'">'.$this->smart_wordwrap($row['user_last_name'],20).'</td>
												  <td style="'.$style.'">'.$row['user_name'].'</td>
												  <td style="'.$style.'">'.$row['email'].'</td>
												  <td style="'.$style.'">'.$this->smart_wordwrap(trim(strip_tags(trim(clearString(htmlspecialchars_decode($row['event_name'],10)))))).'</td>
												  <td style="'.$style.'">'.hr_date($row['order_date']).'</td>
												  <td style="'.$style.'">'.$row['ticket_category'].'</td>
												  <td style="'.$style.'">'.$row['ticket_row'].'</td>
												  <td style="'.$style.'">'.$row['seat_sequence'].'</td>
												  <td style="'.$style.'">'.$row['average_unit_price'].'</td>
												  <td style="'.$style.'">'.$row['seat_qty'].'</td>
												  <td style="'.$style.'">'.$row['total_amount'].'</td>
												  <td style="'.$style.'">'.$row['booking_fee'].'</td>
												 <td style="'.$style.'">'.$row['total_amount_without_booking_fee'].'</td>
												  <td style="'.$style.'">'.$culturaccess_amount.'</td>
												  <td style="'.$style.'">'.$row['productor_amount'].'</td>
											</tr>'; 	
						}
					}
					
					$reportTable .= '
			        </tbody></table><br><br>';
				  
			}
			
			$htmlcontent .= $reportTable. '</body></html>';
			
			$html = ($htmlcontent);
			//echo $html; exit; 
			$file_name = 'CulturaccessReport_'.time();    
			header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );  
			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			echo $html;
			exit; // This is very important for downloading the XLS 
	}
	
	
	public function smart_wordwrap($string, $width = 75, $break = "<br>\n") {
    // split on problem words over the line length
		$pattern = sprintf('/([^ ]{%d,})/', $width);
		$output = '';
		$words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	
		foreach ($words as $word) {
			if (false !== strpos($word, ' ')) {
				// normal behaviour, rebuild the string
				$output .= $word;
			} else {
				// work out how many characters would be on the current line
				$wrapped = explode($break, wordwrap($output, $width, $break));
				$count = $width - (strlen(end($wrapped)) % $width);
	
				// fill the current line and add a break
				$output .= substr($word, 0, $count) . $break;
	
				// wrap any remaining characters from the problem word
				$output .= wordwrap(substr($word, $count), $width, $break, true);
			}
		}
	
		// wrap the final output
		return wordwrap($output, $width, $break);
	}
	
	
	// getGeneralReport
	public function getGeneralReport($request, $response){
		
		$from_date_val = ($request->getParam('post_data')['from_date_val']);
		$to_date_val = ($request->getParam('post_data')['to_date_val']);	
		
		
		
		$isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('name', 'status');
			if(isset($request->getParam('query')['Status'])) {
				   $drpSearch = true;
				   $customSearch  .= " status = ".$request->getParam('query')['Status']." ";
			 }
			 if(isset($request->getParam('query')['generalSearch'])) {
				 $prefix = '';
			 foreach($fields as $field){
			    if(isset($request->getParam('query')['generalSearch'])) {
				     $conditions[] = "$field LIKE '%" . ($request->getParam('query')['generalSearch']) . "%'";
				  }
			   }
			 }
		 }
		 $query = '';
		 if(count($conditions) > 0) {
            $query .=  '('. implode (' OR ', $conditions). ')'; 
         }
		if($drpSearch){
			 if($query <> ''){
				 $whereData =  $query. ' AND '. $customSearch;
			 }else{
				 $whereData = $customSearch;
			 }
		}else{
			$whereData = $query;
		}
        
		// Look for sorting if any 
		$sort  = !empty($request->getParam('sort')['sort']) ? $request->getParam('sort')['sort'] : 'DESC';
        $field = !empty($request->getParam('sort')['field']) ? $request->getParam('sort')['field'] : 'id';
		
		$page     = $request->getParam('pagination')['page'];
		if( !empty($request->getParam('pagination')['pages']) ){
		  $pages    = $request->getParam('pagination')['pages'];
		}
		
		$per_page = $request->getParam('pagination')['perpage'];
		$total = 0; 
		$sqlTotal = "SELECT 
			 COUNT(O.id) AS total_record
			FROM
			  `orders` AS O 
			  INNER JOIN `users` AS U 
				ON U.id = O.`customer_id` 
			  WHERE 1 AND (DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val."')
			";
	    	
		$resultTotalQuery = mysqli_query($this->conn, $sqlTotal);
		$row = mysqli_fetch_assoc($resultTotalQuery);
		$total   = $row['total_record'];
			
		if($page == 1){
		   $offset = 0;	
		   $perpage = 0;
		}else{
		  $offset = ($page-1);	
		  $perpage = $per_page;	
		}
		if($per_page <= 1){
		  $pages = intval($total/10);
	    }else{
	      $pages = intval($total/$per_page);
		}
		if($per_page <= 1){
		   $perPageLimit = 10;	
		}else{
		   $perPageLimit = $per_page;	
		}
		$sqlReport = "SELECT 
					  O.id AS order_id,
					  UM.last_name AS user_last_name,
					  U.name AS user_name,
					  U.email,
					  E.title AS event_name,
					  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
					  C.name AS city_name,
					  O.total_amount,
					  OI.seat_sequence,
					  OI.ticket_category,
					  OI.ticket_row,
					  OI.seat_qty,
					  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
					  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
					FROM
					  `orders` AS O 
					  INNER JOIN `orderitems` AS OI 
						ON O.`id` = OI.`order_id` 
					  INNER JOIN `users` AS U 
						ON U.id = O.`customer_id` 
					  LEFT JOIN `user_meta` AS UM 
						ON UM.`user_id` = O.`customer_id` 
					  INNER JOIN `events` AS E 
						ON E.`id` = OI.`product_id` 
					  INNER JOIN `cities` AS C 
						ON E.`city_id` = C.`id` 
					  WHERE 1 AND (DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val."')
					  AND O.status <> 'R'
					GROUP BY OI.order_id 
					ORDER BY OI.created_on DESC LIMIT ".($offset*$perPageLimit).", ".($perPageLimit)." ";	
		     //echo $sqlReport;			
			$resultQuery = mysqli_query($this->conn, $sqlReport);
		//$data = array();
		while($row = mysqli_fetch_object($resultQuery)){
			//ddump($row);
		  	$array_data = array();
			$array_data['order_id']  = $row->order_id;
            $array_data['user_last_name']  = $row->user_last_name;
			$array_data['user_name']  = $row->user_name;
            $array_data['email']  = $row->email;
			$array_data['event_name']  = $this->htmltoflash(trim(strip_tags(trim(clearString(htmlspecialchars_decode($row->event_name,10))))));
			$array_data['event_date']  =  $row->event_date;
			$array_data['event_city']  =  $this->htmltoflash($row->city_name);
			$array_data['total_amount']  =  $row->total_amount;
			$array_data['seat_sequence']  =  $row->seat_sequence;
			$array_data['ticket_category']  =  $row->ticket_category;
			$array_data['ticket_row']  =  $row->ticket_row;
			$array_data['seat_qty']  =  $row->seat_qty;
			$array_data['order_date']  =  $row->order_date;
			$array_data['order_time']  =  $row->order_time;
			$data[] = $array_data;
		}

		$meta = array("page" => $page,
						"pages" => $pages,
						"perpage" => $perPageLimit,
						"total" => $total,
						"sort" => $sort,
						"field" => $field
					);
					
		$output = array(
						"meta" => $meta,
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	
	// getGeneralReportCustomer
	public function getGeneralReportCustomer($request, $response){
		
		$from_date_val = ($request->getParam('post_data')['from_date_val']);
		$to_date_val = ($request->getParam('post_data')['to_date_val']);	
		$customer_id = $request->getParam('post_data')['customer_id'];	
		
		
		$isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('name', 'status');
			if(isset($request->getParam('query')['Status'])) {
				   $drpSearch = true;
				   $customSearch  .= " status = ".$request->getParam('query')['Status']." ";
			 }
			 if(isset($request->getParam('query')['generalSearch'])) {
				 $prefix = '';
			 foreach($fields as $field){
			    if(isset($request->getParam('query')['generalSearch'])) {
				     $conditions[] = "$field LIKE '%" . ($request->getParam('query')['generalSearch']) . "%'";
				  }
			   }
			 }
		 }
		 $query = '';
		 if(count($conditions) > 0) {
            $query .=  '('. implode (' OR ', $conditions). ')'; 
         }
		if($drpSearch){
			 if($query <> ''){
				 $whereData =  $query. ' AND '. $customSearch;
			 }else{
				 $whereData = $customSearch;
			 }
		}else{
			$whereData = $query;
		}
        
		// Look for sorting if any 
		$sort  = !empty($request->getParam('sort')['sort']) ? $request->getParam('sort')['sort'] : 'DESC';
        $field = !empty($request->getParam('sort')['field']) ? $request->getParam('sort')['field'] : 'id';
		
		$page     = $request->getParam('pagination')['page'];
		if( !empty($request->getParam('pagination')['pages']) ){
		  $pages    = $request->getParam('pagination')['pages'];
		}
		
		$per_page = $request->getParam('pagination')['perpage'];
		$total = 0; 
		$sqlTotal = "SELECT 
			 COUNT(O.id) AS total_record
			FROM
			  `orders` AS O 
			  INNER JOIN `users` AS U 
				ON U.id = O.`customer_id` 
			  WHERE   O.`customer_id`=".$customer_id." AND (DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val."')
			";
	    	
		$resultTotalQuery = mysqli_query($this->conn, $sqlTotal);
		$row = mysqli_fetch_assoc($resultTotalQuery);
		$total   = $row['total_record'];
			
		if($page == 1){
		   $offset = 0;	
		   $perpage = 0;
		}else{
		  $offset = ($page-1);	
		  $perpage = $per_page;	
		}
		if($per_page <= 1){
		  $pages = intval($total/10);
	    }else{
	      $pages = intval($total/$per_page);
		}
		if($per_page <= 1){
		   $perPageLimit = 10;	
		}else{
		   $perPageLimit = $per_page;	
		}
		$sqlReport = "SELECT 
					  O.id AS order_id,
					  UM.last_name AS user_last_name,
					  U.name AS user_name,
					  U.email,
					  E.title AS event_name,
					  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
					  C.name AS city_name,
					  O.total_amount,
					  OI.seat_sequence,
					  OI.ticket_category,
					  OI.ticket_row,
					  OI.seat_qty,
					  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
					  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
					FROM
					  `orders` AS O 
					  INNER JOIN `orderitems` AS OI 
						ON O.`id` = OI.`order_id` 
					  INNER JOIN `users` AS U 
						ON U.id = O.`customer_id` 
					  LEFT JOIN `user_meta` AS UM 
						ON UM.`user_id` = O.`customer_id` 
					  INNER JOIN `events` AS E 
						ON E.`id` = OI.`product_id` 
					  INNER JOIN `cities` AS C 
						ON E.`city_id` = C.`id` 
					  WHERE   O.`customer_id`=".$customer_id." AND (DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val."')
					  AND O.status <> 'R'
					GROUP BY OI.order_id 
					ORDER BY OI.created_on DESC LIMIT ".($offset*$perPageLimit).", ".($perPageLimit)." ";	
		     ///echo $sqlReport;			
			$resultQuery = mysqli_query($this->conn, $sqlReport);
		
		
		while($row22 = mysqli_fetch_object($resultQuery)){
		  $datelist[] = $row22;	
		}
		$data = array();
		//ddump($ddd);
		// Process the orders_list array here
		foreach($datelist as $row ){
		  	$array_data = array();
			$array_data['order_id']  = $row->order_id;
            $array_data['user_last_name']  = $row->user_last_name;
			$array_data['user_name']  = $row->user_name;
            $array_data['email']  = $row->email;
			$array_data['event_name']  = $this->htmltoflash(trim(strip_tags(trim(clearString(htmlspecialchars_decode($row->event_name,10))))));
			$array_data['event_date']  =  $row->event_date;
			$array_data['event_city']  =  $this->htmltoflash($row->city_name);
			$array_data['total_amount']  =  $row->total_amount;
			$array_data['seat_sequence']  =  $row->seat_sequence;
			$array_data['ticket_category']  =  $row->ticket_category;
			$array_data['ticket_row']  =  $row->ticket_row;
			$array_data['seat_qty']  =  $row->seat_qty;
			$array_data['order_date']  =  $row->order_date;
			$array_data['order_time']  =  $row->order_time;
			$data[] = $array_data;
		}

		$meta = array("page" => $page,
						"pages" => $pages,
						"perpage" => $perPageLimit,
						"total" => $total,
						"sort" => $sort,
						"field" => $field
					);
					
		$output = array(
						"meta" => $meta,
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	
	// getGeneralReportCSV
    public function getGeneralReportCSV($request, $response, $args){
		    $from_date_val = ($args['from_date']);
		    $to_date_val = ($args['to_date']);
			$customer_id = $args['customer_id'];
			$search_by_customer_query =  '';
			$customer_name = ' Report ';		
		   if($customer_id != 0){
			$sqlName = "SELECT CONCAT(U.name , ' ', UM.last_name) AS customer_name FROM `users` AS U LEFT JOIN `user_meta` AS UM
ON U.`id`=UM.`user_id` WHERE U.`id`=".$customer_id."";
           $resultName = mysqli_query($this->conn, $sqlName);
		   $rowName = mysqli_fetch_assoc($resultName);	
			$customer_name = 'Report of <strong>'.$customer_name.'</strong> '.$rowName['customer_name'];
			  $search_by_customer_query = " AND O.`customer_id`=".$customer_id."";
			}
			$htmlcontent='<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
    <html>
        <head><meta http-equiv=\"Content-type\" content=\"text/html;charset=utf-8\" /></head>
        <body>';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';
			$ROStyle = 'background-color:#d93025; color:#fff;';	
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = 	'<h1><center>'.$customer_name.' From '.hr_date($from_date_val).' - '.hr_date($to_date_val).'</center></h1>';
			$reportTable .= '<table border="1" >
						  <thead>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_id_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_last_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_email_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_date_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_event_city_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_amount_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_seat_number_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_category_name_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_row_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_seats_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_date_txt']).'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.utf8_decode($this->lang['report_order_time_txt']).'</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
				$sqlReport = "SELECT 
							  O.id AS order_id,
							  UM.last_name AS user_last_name,
							  U.name AS user_name,
							  U.email,
							  E.title AS event_name,
							  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
							  C.name AS city_name,
							  O.total_amount,
							  OI.seat_sequence,
							  OI.ticket_category,
							  OI.ticket_row,
							  OI.seat_qty,
							  O.status AS order_status,
							  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
							  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
							FROM
							  `orders` AS O 
							  INNER JOIN `orderitems` AS OI 
								ON O.`id` = OI.`order_id` 
							  INNER JOIN `users` AS U 
								ON U.id = O.`customer_id` 
							  LEFT JOIN `user_meta` AS UM 
								ON UM.`user_id` = O.`customer_id` 
							  INNER JOIN `events` AS E 
								ON E.`id` = OI.`product_id` 
							  INNER JOIN `cities` AS C 
								ON E.`city_id` = C.`id` 
								WHERE 1  $search_by_customer_query AND (DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val."')
								AND O.status <> 'R'
								GROUP BY OI.order_id 
                                ORDER BY OI.created_on DESC  ";	
							
			$resultQuery = mysqli_query($this->conn, $sqlReport);
			$i=0;
			while($row = mysqli_fetch_assoc($resultQuery)){
				  if($i % 2 == 0){
					  $style = $NthStyle;	
					}else{
					  $style = $EthStyle;	
					}
					$order_status = $row['order_status'];
					if($order_status == 'R'){
					   $style = $ROStyle;
					}else{
					   $style =  $style;
					}
				$reportTable .= '<tr>
									  <td style="'.$style.'">'.$row['order_id'].'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap($row['user_last_name'],10).'</td>
									  <td style="'.$style.'">'.$row['user_name'].'</td>
									  <td style="'.$style.'">'.$row['email'].'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap(trim(strip_tags(trim(clearString(htmlspecialchars_decode($row['event_name'],10)))))).'</td>
									  <td style="'.$style.'">'.$row['event_date'].'</td>
									  <td style="'.$style.'">'.$row['city_name'].'</td>
									  <td style="'.$style.'">'.$row['total_amount'].'</td>
									  <td style="'.$style.'">'. $this->smart_wordwrap($row['seat_sequence'],10).'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap($row['ticket_category'],10).'</td>
									  <td style="'.$style.'">'.$row['ticket_row'].'</td>
									  <td style="'.$style.'">'.$row['seat_qty'].'</td>
									  <td style="'.$style.'">'.$row['order_date'].'</td>
									  <td style="'.$style.'">'.$row['order_time'].'</td>
								</tr>'; 	 	
			}
			$htmlcontent.= $reportTable;	   
			
			$htmlcontent .= '</table></body></html>';
			$html = $htmlcontent;
			
			//header("Content-Type: application/xls"); 
			$file_name = 'CustomerGeneralDataReport_'.time();   
			header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );  
			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			

			echo $html;
			exit; // This is very important for downloading the XLS 
											  
    }
	
	
	// getGeneralReportPDF
    public function getGeneralReportPDF($request, $response, $args){
		    $from_date_val = ($args['from_date']);
		    $to_date_val = ($args['to_date']);	
			
		    $customer_id = $args['customer_id'];
			$search_by_customer_query = '';
			$customer_name = ' Report ';	
			if($customer_id !=  0){
			$sqlName = "SELECT CONCAT(U.name , ' ', UM.last_name) AS customer_name FROM `users` AS U LEFT JOIN `user_meta` AS UM
ON U.`id`=UM.`user_id` WHERE U.`id`=".$customer_id."";
           $resultName = mysqli_query($this->conn, $sqlName);
		   $rowName = mysqli_fetch_assoc($resultName);	
			$customer_name = 'Report of <strong>'.$customer_name.'</strong> '.$rowName['customer_name'];
			  $search_by_customer_query = " AND O.`customer_id`=".$customer_id."";
			}
			$htmlcontent='';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';	
			$ROStyle = 'background-color:#d93025; color:#fff;';
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = 	'<h1><center> '.$customer_name.'  From '.hr_date($from_date_val).' - '.hr_date($to_date_val).'</center></h1>';
			$reportTable .= '<style>
					table {
						font-family: arial, sans-serif;
						border-collapse: collapse;
						width: 100%;
					}				
					td, th {
						border: 1px solid #dddddd;
						text-align: left;
						padding: 8px;
					}
					tr:nth-child(even) {
						background-color: #dddddd;
					}
					.error{
					  color:#f4516c;	
					}
					</style>';	
			$reportTable .= '<table class="table table-striped m-table table-bordered" style="border-collapse:collapse; font-size:10px">
						  <thead>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_order_id_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_last_name_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_name_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_email_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_event_name_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_event_date_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_event_city_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_order_amount_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_seat_number_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_category_name_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_row_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_seats_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_order_date_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['report_order_time_txt'].'</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
				$sqlReport = "SELECT 
							  O.id AS order_id,
							  UM.last_name AS user_last_name,
							  U.name AS user_name,
							  U.email,
							  E.title AS event_name,
							  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
							  C.name AS city_name,
							  O.total_amount,
							  OI.seat_sequence,
							  OI.ticket_category,
							  OI.ticket_row,
							  OI.seat_qty,
							  O.status AS order_status,
							  DATE_FORMAT(O.created_on, '%d/%m/%Y') AS order_date,
							  DATE_FORMAT(O.created_on, '%H:%i') AS order_time 
							FROM
							  `orders` AS O 
							  INNER JOIN `orderitems` AS OI 
								ON O.`id` = OI.`order_id` 
							  INNER JOIN `users` AS U 
								ON U.id = O.`customer_id` 
							  LEFT JOIN `user_meta` AS UM 
								ON UM.`user_id` = O.`customer_id` 
							  INNER JOIN `events` AS E 
								ON E.`id` = OI.`product_id` 
							  INNER JOIN `cities` AS C 
								ON E.`city_id` = C.`id` 
								WHERE 1 $search_by_customer_query AND (DATE(O.created_on) BETWEEN '".$from_date_val."' AND '".$to_date_val."')
								AND O.status <> 'R'
								GROUP BY OI.order_id 
                                ORDER BY OI.created_on DESC  ";	
								//echo $sqlReport; exit;
			$resultQuery = mysqli_query($this->conn, $sqlReport);
			$i=0;
			while($row = mysqli_fetch_assoc($resultQuery)){
				  if($i % 2 == 0){
					  $style = $NthStyle;	
					}else{
					  $style = $EthStyle;	
					}
					$order_status = $row['order_status'];
					if($order_status == 'R'){
					   $style = $ROStyle;
					}else{
					   $style =  $style;
					}
				$reportTable .= '<tr>
									  <td style="'.$style.'">'.$row['order_id'].'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap($row['user_last_name'],10).'</td>
									  <td style="'.$style.'">'.$row['user_name'].'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap($row['email'],15).'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap(trim(strip_tags(trim(clearString(htmlspecialchars_decode($row['event_name'],10)))))).'</td>
									  <td style="'.$style.'">'.$row['event_date'].'</td>
									  <td style="'.$style.'">'.$this->htmltoflash($row['city_name']).'</td>
									  <td style="'.$style.'">'.$row['total_amount'].'</td>
									  <td style="'.$style.'">'. $this->smart_wordwrap($row['seat_sequence'],10).'</td>
									  <td style="'.$style.'">'.$this->smart_wordwrap($row['ticket_category'],10).'</td>
									  <td style="'.$style.'">'.$row['ticket_row'].'</td>
									  <td style="'.$style.'">'.$row['seat_qty'].'</td>
									  <td style="'.$style.'">'.$row['order_date'].'</td>
									  <td style="'.$style.'">'.$row['order_time'].'</td>
								</tr>'; 	 	
			}
			$htmlcontent.= $reportTable;	   
			
			$htmlcontent .= '</table>';
			$html = $htmlcontent;
			$dompdf = new Dompdf();
			$dompdf->set_option("isPhpEnabled", true);

           

			$dompdf->load_html($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			#Esto es lo que imprime en el PDF el numero de paginas
			$canvas = $dompdf->get_canvas();
			$footer = $canvas->open_object();
			$w = $canvas->get_width();
			$h = $canvas->get_height();
			$canvas->page_text($w-110,$h-40,"Página {PAGE_NUM} de {PAGE_COUNT}", 'helvetica',12);
			$canvas->page_text($w-810,$h-40,"Culture Access © 2018 - Tel. 0733 - 202 - 400", 'helvetica',12);
			
			$canvas->close_object();
			$canvas->add_object($footer,"all");
			$filename = 'eventSaleReport_'.time();
			$dompdf->stream($filename.".pdf");
			
			 #Liberamos 
			unset($dompdf);
			exit;
			 }
	
	public	function htmltoflash($htmlstr)
	{
	  return str_replace("&lt;br /&gt;","\n",
		str_replace("<","&lt;",
		  str_replace(">","&gt;",
			mb_convert_encoding(html_entity_decode($htmlstr),
			"UTF-8","ISO-8859-1"))));
	}
	public function convertToUTF8($content) { 
	//return mb_convert_encoding($content,'utf-16','utf-8');
    if(!mb_check_encoding($content, 'UTF-8') 
        OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) { 

        $content = mb_convert_encoding($content, 'UTF-8'); 

        if (mb_check_encoding($content, 'UTF-8')) { 
            // log('Converted to UTF-8'); 
        } else { 
            // log('Could not converted to UTF-8'); 
        } 
    } 
    return $content; 
}
	
	
	
	
	
	
}
