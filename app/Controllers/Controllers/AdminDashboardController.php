<?php
namespace App\Controllers;

use App\Models;
use App\Middleware\Auth;
use App\Middleware\RouteMiddleware; 
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

use Dompdf\Dompdf;

/**
   Admin Dashboard Controller
   CRUDs for Dashboard

*/
class AdminDashboardController extends Base 
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
		$this->container = $container;
		$this->servername = $this->container['settings']['database']['host'];
		$this->username = $this->container['settings']['database']['username'];
		$this->password = $this->container['settings']['database']['password'];
		$this->dbname = $this->container['settings']['database']['database'];
		$this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
		$this->lang =  $this->container->view['adminLang'];
	}
	
	 public function dashboard($request, $response) {
		        /*ddump($_SESSION);
				exit;*/
		        // Check if admin is logged in or Productor is logged in
				if( isProductorLogin()  ){
				  return $this->response->withStatus(200)->withHeader('Location', base_url.'/productor/dashboard'); 
				  exit;	
				}
				
				// Check if operator is logged in
				if( isOperatorLogin() ){
					 return $this->response->withStatus(200)->withHeader('Location', base_url.'/operator/dashboard'); 
				    exit;	
				}
				
				/*ddump($_SESSION);
				exit;*/
               
		 
		         // Get the maintenance state from settings
		         $is_for_maintenance = Models\Setting::where('id', '=', 1)->first()->is_for_maintenance;
				 // Get all Event Groups 
				 $mapped_Groups = Models\Eventgroup::orderBy('title',  'ASC')->get();
				 $event_groups_array = array();
				 foreach($mapped_Groups as $group){
					$event_groups_array['id'] = $group['id'];
				    $event_groups_array['title'] = trim(strip_tags(trim(clearString(htmlspecialchars_decode($group['title'])))));
				    $groups[] = $event_groups_array;
		}
			 
		 $result = $option = '';
		 $event_group_id = $event_id = $mapped_eventGroups = $events_list = '';
		 $total_seats = $total_booked_seats = $total_sale = 0;
		 $orderReportList = $event_name = '' ;
		if(isset($_REQUEST['event_group_id']) && isset($_REQUEST['event_id']) ){
			if( !empty($_REQUEST['event_group_id']) && !empty($_REQUEST['event_id']) ){
				error_reporting(0);
				$event_group_id = $_REQUEST['event_group_id'];
				$event_id = $_REQUEST['event_id'];
				// Get event Name
				$event_name = Models\Event::where('id', '=', $event_id)->first()->title;
				$event_name = trim(strip_tags(trim(clearString(htmlspecialchars_decode($event_name)))));
				// Get all ids of this event from the event_seat_categories table
				$event_categories_data = Models\EventSeatCategories::where('event_id', '=', $event_id)->get();
				$concat_ids = array();
				$event_seat_categories_id = '';
				foreach($event_categories_data as $row){
				   $event_cat_ids[] = $row['id'];	
				   $event_seat_categories_id .= $row['id'].',';
				}
				// Find sum of the total quantity of the seats for this event
				$total_seats = Models\RowSeats::whereIn('event_seat_categories_id', array(rtrim($event_seat_categories_id,',')))->sum('total_qantity');
				// Find sum of the booked seats for this event
				$total_booked_seats = Models\OrderItems::where('type_product', '=', 'event')->where('product_id', '=', $event_id)->sum('quantity');
				// Find sum of the total sale for this event
				$total_sale = Models\OrderItems::where('type_product', '=', 'event')->where('product_id', '=', $event_id)->sum('price');
				// Dispaly list of events when searched
			    $events_list = Models\Event::where('eventgroup_id', '=', $event_group_id)->get();
				// Get This Event Complete Sale report
				$orderReport = Models\OrderItems::with(['Order'])->
				               where('type_product', '=', 'event')->
							   where('product_id', '=', $event_id)->orderBy('created_on',  'DESC')->get();
				if( $orderReport->isEmpty() ){
					$orderReportList .= '<tr>
										  <th scope="row" colspan="6"> 
										  <font color="red"><center>'.$this->lang['no_data_found_txt'].'</center></font> 
										  </th>
										</tr>';
				}else{
					$iCounter = 1;
					$orderData =  array(); // Order data array
				  foreach($orderReport as $orderItem){
					  $customer_id = $orderItem['Order']['customer_id'];
					  $ticket_row_id = $orderItem['ticket_row_id'];
					  $ticket_category = $orderItem['ticket_category'];
					  $ticket_row = $orderItem['ticket_row'];
					  $seat_sequence = $orderItem['seat_sequence'];
					  $customer_data = Models\User::with('memberdata')->where('id', '=', $customer_id)->get();
					  foreach($customer_data as $getCustomer){
					     //$last_name = $getCustomer['memberdata']['last_name'];
						 $last_name = $getCustomer['name'];
						 $first_name = $getCustomer['memberdata']['first_name'];
						 $telephone = $getCustomer['memberdata']['phone_no'];
						 $email = $getCustomer['email'];
					  }
					  $placement_id = '';
					  $operator_id = '';
					  if( $ticket_row_id !== null || $ticket_row_id != ''){
						    if(is_numeric($ticket_row_id) ){
					            $placement = Models\RowSeats::where('id', '=', $ticket_row_id)->get();
								if( $placement->isEmpty() ){
									
								}else{
									foreach($placement as $rowp){
									 	$placement_id = $rowp['placement'];
										$operator_id = $rowp['operator_id'];
								    }
								}
							}else{
								$placement = '';
							}
					  }else{
						  $placement = '';
					  }
				     
				  $iCounter++; 
				    $orderData['last_name'] = $last_name; 
					$orderData['first_name'] = $first_name; 
					$orderData['telephone'] = $telephone; 
					$orderData['email'] = $email; 
					$orderData['placement_id'] = $placement_id; 
					$orderData['source'] = 'Internet'; 
					$orderData['ticket_category'] = $ticket_category; 
					$orderData['ticket_row'] = $ticket_row; 
					$orderData['seat_sequence'] = $seat_sequence; 
					$dumpData[] = $orderData;
				  }
				}
				sort($dumpData);
				foreach($dumpData as $arr){
				 $orderReportList .= '<tr>
					                      <td> '.$arr['last_name'].' </td>
										  <td>'.$arr['first_name'].'</td>
										  <td>'.$arr['telephone'].'</td>
										  <td style="display:none">'.$arr['email'].'</td>
										  <td style="display:none">'.$this->ticekt_type($arr['placement_id']).'</td>
										  <td style="display:none"> Internet</td>
					                      <td> '.$arr['ticket_category'].' </td>
										  <td> '.$arr['ticket_row'].' </td>
										  <td> '.$arr['seat_sequence'].' </td>
										</tr>'; 
				}
				
				$orderReportList .= '<tr>
					                      <td colspan="5" style="text-align:right;"><strong>'.$this->lang['dashboard_total_sale_amount_txt'].'</strong></td>
										  <td ><strong>&#x20aa;'.number_format($total_sale).'</strong></td>
										</tr>'; 
				
			}
		}
		
		/** Dashboard Stuff */
		/**
		 SECTION 1
		**/
		$total_members = $all_sale = $total_productors =  $total_events = 0; 
		// Find total member
		$total_members += Models\User::where('type', '=', 'Member')->count();
		// Find total productors
		$total_productors += Models\User::where('type', '=', 'Productor')->count();
		// Find total sale
		$all_sale += Models\Order::sum('total_amount');
		// Find total events
		$total_events += Models\Event::count();
		
		/* --------------- START SECTION 2   ---------*/
		$section_2_total_artists       = 0;
		$section_2_total_events        = 0;
		$section_2_total_orders        = 0;
		$section_2_total_system_users  = 0;
		
		// Find total Artists
		$section_2_total_artists += Models\User::where('type', '=', 'Artist')->count();
		// Find total events
		$section_2_total_events += Models\Event::count();
		// Find total orders
		$section_2_total_orders += Models\Order::count();
		// Find total system users
		$section_2_total_system_users += Models\User::where('type', '=', 'Admin')->count();
		
		/* --------------- END SECTION 2   ---------*/
		
		
		/**
		 SECTION 3
		**/
		
		// get today's orders
		$section_3_today_orders = Models\Order::with(['Customer'])->whereDate('created_on','=', [date('Y-m-d')])->get();
		$today_orders = $todayOrders = array();
		if( !$section_3_today_orders->isEmpty() ){
			foreach($section_3_today_orders as $order){
			   	 $today_orders['order_id'] =  $order['id'];
				 $today_orders['customer_name'] =  '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$order['id'].')">'.$order['Customer']['name'].'</a>';
				 $today_orders['order_amount'] =  number_format($order['total_amount']);
				 $today_orders['order_time'] =  '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$order['id'].')">'.$this->get_time_ago($order['created_on']).'</a>';
				 $todayOrders[] = $today_orders;
			}
		}
		$startOfMonth = '';
		$endOfMonth = '';
		// Get this week orders
		$this_week_dates = $this->this_week_range(date('Y-m-d'));
		$startOfWeek = $this_week_dates[0];
		$endOfWeek = $this_week_dates[1];
		$section_3_this_week_orders = Models\Order::with(['Customer'])->
		                              where('created_on', '>', $startOfMonth. ' 01:59:59')
									->where('created_on', '<=', $endOfMonth . ' 23:59:59')->get();
		$week_orders = $weekOrders = array();							  
		if( !$section_3_this_week_orders->isEmpty() ){
			foreach($section_3_this_week_orders as $order){
			   	 $week_orders['order_id'] =  $order['id'];
				 $week_orders['customer_name'] =  '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$order['id'].')">'.$order['Customer']['name'].'</a>';
				 $week_orders['order_amount'] =  number_format($order['total_amount']);
				 $week_orders['order_time'] =  '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$order['id'].')">'.$this->get_time_ago($order['created_on']).'</a>';
				 $weekOrders[] = $week_orders;
			}
		}
		
		
		// Get this month orders
		$this_month_dates = $this->this_month_range();
		$startOfMonth = $this_month_dates[0];		
		$endOfMonth = $this_month_dates[1];							  
		$section_3_this_month_orders = Models\Order::with(['Customer'])->
		                               where('created_on', '>', $startOfMonth. ' 01:59:59')
									->where('created_on', '<=', $endOfMonth . ' 23:59:59')->get();
		$month_orders = $monthOrders = array();									   							  
		if( !$section_3_this_month_orders->isEmpty() ){
			foreach($section_3_this_month_orders as $order){
			   	 $month_orders['order_id'] =  $order['id'];
				  $month_orders['customer_name'] =  '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$order['id'].')">'.$order['Customer']['name'].'</a>';
				 $month_orders['order_amount'] =  number_format($order['total_amount']);
				 $month_orders['order_time'] =  '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$order['id'].')">'.$this->get_time_ago($order['created_on']).'</a>';
				 $monthOrders[] = $month_orders;
			}
		}
		
		// Check if order is searched
		$is_order_searched = 0;
		$from_date = $to_date = $date_range_order = '';
		if( isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date']) ){
			if( isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date']) ){
			  if( isset($_REQUEST['date_range_order']) && !empty($_REQUEST['date_range_order']) ){
				  $from_date = $_REQUEST['from_date'];
				  $to_date   = $_REQUEST['to_date'];
				  $date_range_order = $_REQUEST['date_range_order'];
				  $is_order_searched = 1;
				  // 
			  }
			}
		}
		
		if(!empty($from_date) && !empty($to_date)){
		     $default_from_date = $from_date;
			 $default_to_date   = $to_date;	
		}else{
			$default_from_date = $startOfMonth;
		    $default_to_date	   = $endOfMonth;
		}
		
		/**
		 SECTION 4
		**/
		// Get last six month user registration data
		$current_month = date('n');
				
		for($i=6; $i > 0; $i--){
			$j = $i-1;
			$months_dates[] = date('Y-m', strtotime( date('Y-m-01'). "-$j month" ));
			$months[] = date('M', mktime(0,0,0,$current_month-$j,15, date('Y')));
		}
		
		$k=0;
		for($i=0; $i < 6; $i++){
				
		  $all_members[] = Models\User::where('registration_date', 'LIKE', '%'.$months_dates[$i].'%')->
			                              where('type', '=', 'Member')->
										  count();	
		  $all_productors[] = Models\User::where('registration_date', 'LIKE', '%'.$months_dates[$i].'%')->
			                              where('type', '=', 'Productor')->
										  count();
		  $all_artists[] = Models\User::where('registration_date', 'LIKE', '%'.$months_dates[$i].'%')->
			                              where('type', '=', 'Artist')->
										  count();								  	
		$k++;
		}
		
		// Start of Member data
		$section_4_chart_1_data_member = '';
		$section_4_chart_1_data_member .= "{
								name: '".$this->lang['left_menu_member_txt']."',
								data: [";
		for($j=0; $j<6; $j++){	
		   if($j==5)
			   $end = '';
			else
			 $end = ',';				
		     $section_4_chart_1_data_member .= $all_members[$j].$end;
		}
		
		$section_4_chart_1_data_member .= "]
							},";
		// End of Member data
		
		// Start of Productor data					
		$section_4_chart_1_data_productor = '';
		$section_4_chart_1_data_productor .= "{
								name: '".$this->lang['left_menu_productor_txt']."s',
								data: [";
		for($j=0; $j<6; $j++){	
		   if($j==5)
			   $end = '';
			else
			 $end = ',';				
		     $section_4_chart_1_data_productor .= $all_productors[$j].$end;
		}
		
		$section_4_chart_1_data_productor .= "]
							},";	
		// End of Productor data
		
		// Start of Artist data					
		$section_4_chart_1_data_artist = '';
		$section_4_chart_1_data_artist .= "{
								name: '".$this->lang['left_menu_artist_txt']."',
								data: [";
		for($j=0; $j<6; $j++){	
		   if($j==5)
			   $end = '';
			else
			 $end = ',';				
		     $section_4_chart_1_data_artist .= $all_artists[$j].$end;
		}
		
		$section_4_chart_1_data_artist .= "]
							}";										
		
		// End of Artist data
		
		// Chart data for user registration graph
		$section_4_chart_1_data = $section_4_chart_1_data_member. $section_4_chart_1_data_productor. $section_4_chart_1_data_artist;
		// User from and to range date for th user registration graph
		$users_from_to =  date('01/m/Y', strtotime($months[0])). ' - '. date('t/m/Y', strtotime($months[2]));
		
		// Start of displaying months on the user registration graph
		$section_4_chart_1_month_data = '';
	    
		for($j=0; $j<6; $j++){	
			if($j==5){
				   $comma_end = '';
			}else{
				 $comma_end = ',';
			}
		   $section_4_chart_1_month_data .= "'".$months[$j]."'".$comma_end;
		}
		
		// End of displaying months on the user registration graph
		
		
		// START of Yearly Sale Report
		$section_4_last_year_report_cats = '';
		$section_4_last_year_progress = '';
		for($i=12; $i > 0; $i--){
			if($j == 1)
			 $end_comma = '';
			 else
			 $end_comma = ',';
			$j = $i-1;
			
			$last_year_months_dates[] = date('Y-m', strtotime( date('Y-m-01'). "-$j month" ));
			$report_month = date('M Y', mktime(0,0,0,$current_month-$j,15, date('Y')));
			$last_year_months[] = date('M Y', mktime(0,0,0,$current_month-$j,15, date('Y')));
			$section_4_last_year_report_cats .= "'".$report_month."'". $end_comma;
			$last_year_months_date = date('Y-m', strtotime( date('Y-m-01'). "-$j month" ));
			$section_4_last_year_progress .= Models\Order::
			                                 where('created_on', 'LIKE', '%'.$last_year_months_date.'%')->
										     sum('total_amount'). $end_comma;
		}
		
		// User from and to range date for the yearly sale report graph
		$section_4_yearly_sale_report_range =  date('01/m/Y', strtotime($last_year_months[0])). ' - '. date('t/m/Y', strtotime($last_year_months[11]));
		
		// END of Yearly Sale Report
		
		
		/* =============  START  SECTION 5 ======================= */
		   
		/* =============  END    SECTION 5 ======================= */
		
		 $params = array('base_url' => base_url, 
						 'title' => $this->lang['dashboard_txt'],
						 'is_for_maintenance' => $is_for_maintenance,
						 'current_url' => $request->getUri()->getPath(),
						 'groups' => $groups,
						 'event_group_id' => $event_group_id,
						 'event_id' => $event_id,
						 'options' => $events_list,
						 'total_seats' => $total_seats,
						 'total_booked_seats' => $total_booked_seats,
						 'total_sale' => $total_sale,
						 'orderReportList' => $orderReportList,
						 'event_name' => $event_name,
						 /** Section 1 */
						 'total_members' => number_format($total_members),
						 'all_sale' => number_format($all_sale),
						 'total_productors' => number_format($total_productors),
						 'total_events' => number_format($total_events),
						 /** Section 2 */
						 'section_2_total_artists' => $section_2_total_artists,
						 'section_2_total_events' => $section_2_total_events,
						 'section_2_total_orders' => $section_2_total_orders,
						 'section_2_total_system_users' => $section_2_total_system_users,
						 /** Section 3 */
						 'section_3_today_orders' => $todayOrders,
						 'section_3_this_week_orders' => $weekOrders,
						 'section_3_this_month_orders' => $monthOrders,
						 'is_order_searched' => $is_order_searched,
						 'from_date_val' => $from_date,
						 'to_date_val' => $to_date,
						 'date_range_order_val' => $date_range_order,
						 'default_from_date' => $default_from_date,
						 'default_to_date' => $default_to_date,
						 /** SECTION 4 Chart Data*/
						 'section_4_chart_1_data' => $section_4_chart_1_data,
						 'section_4_chart_1_month_data' => $section_4_chart_1_month_data,
						 'users_from_to' => $users_from_to,
						 'section_4_last_year_report_cats' => $section_4_last_year_report_cats,
						 'section_4_last_year_progress' => $section_4_last_year_progress,
						 'section_4_yearly_sale_report_range' => $section_4_yearly_sale_report_range
						 );
        return $this->render($response, ADMIN_VIEW.'/Dashboard/dashboard.twig', $params);		
    }
	
	
	// Get group events list
	public function getGroupEventsList($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		 // Get all the events which are sold
		 $mapped_eventGroups = Models\Event::with('city')->
		                       where('eventgroup_id', '=', $id)->
							   orderBy('title', 'ASC')->
							   get();
		 $result = '';
		 foreach($mapped_eventGroups as $row){ 
			 $id = $row['id'];
			 $title = trim(strip_tags(trim(clearString(htmlspecialchars_decode($row['title'])))));
			 $city = $row['city']['name'];
			 $date = hr_date($row['date']);
			 $title = $title. ' ['.$city.'] ['.$date.']';
			 $result .= '<option value="'.$id.'">'.$title.'</option>';
		 }
		$option = '<option value="">'.$this->lang['dashboard_select_event_text'].'</option>';
		$option .= $result;
		echo $option;
		
	}
	
	
// Download Sale report as PDF
public function downloadSaleReportPDF($request, $response, $args){
	       // echo $args['event_group_id']. ' = '.$args['event_id'];
			$event_group_id = $args['event_group_id'];
			$event_id = $args['event_id'];
			 // Get event Name
			 $event_name = Models\Event::where('id', '=', $event_id)->first()->title;
			 $event_name = trim(strip_tags(trim(clearString(htmlspecialchars_decode($event_name)))));	
			$return_file_name='';
			$dateFilename=date('Ymdhis');
			$htmlcontent='
					<!doctype html>
					<html lang="en">
					<head>
					<style>
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
					</style>
					</head>			  
					<body>';
			$reportTable = 	'<h1><center>'.$this->lang['dashboard_sale_report_of_txt'].' '.$event_name.'</center></h1>';	
			$reportTable .= '<table class="table table-striped m-table table-bordered">
						  <thead>
							<tr>
							  <th>'.$this->lang['dashboard_last_name_txt'].'</th>
							  <th>'.$this->lang['dashboard_first_name_txt'].'</th>
							  <th>'.$this->lang['dashboard_telephone_txt'].'</th>
							  <th style="display:none">'.$this->lang['dashboard_email_txt'].'</th>
							  <th style="display:none">'.$this->lang['dashboard_ticket_type_txt'].'</th>
							  <th style="display:none"> '.$this->lang['dashboard_source_txt'].' </th>
							  <th> '.$this->lang['dashboard_category_txt'].'  </th>
							  <th> '.$this->lang['dashboard_row_txt'].' </th>
							  <th> '.$this->lang['dashboard_seat_txt'].' </th>
							</tr>
						  </thead>
						  <tbody>';		
			if(isset($event_group_id) && isset($event_id) ){
				if( !empty($event_group_id) && !empty($event_id) ){	
					 // Get all ids of this event from the event_seat_categories table
				     $event_categories_data = Models\EventSeatCategories::where('event_id', '=', $event_id)->get();
					 $event_seat_categories_id = '';
					foreach($event_categories_data as $row){
					   $event_cat_ids[] = $row['id'];	
					   $event_seat_categories_id .= $row['id'].',';
					}
					// Find sum of the total quantity of the seats for this event
				    $total_seats = Models\RowSeats::whereIn('event_seat_categories_id', 
				                   array(rtrim($event_seat_categories_id,',')))->sum('total_qantity');
				    // Find sum of the booked seats for this event
				    $total_booked_seats = Models\OrderItems::where('type_product', '=', 'event')
				                         ->where('product_id', '=', $event_id)->sum('quantity');
				    // Find sum of the total sale for this event
				    $total_sale = Models\OrderItems::where('type_product', '=', 'event')
				                ->where('product_id', '=', $event_id)->sum('price');						 			   
					// Dispaly list of events when searched
			        $events_list = Models\Event::where('eventgroup_id', '=', $event_group_id)->get();
				    // Get This Event Complete Sale report
				    $orderReport = Models\OrderItems::with('Order')->
				                   where('type_product', '=', 'event')->
							       where('product_id', '=', $event_id)->get();
					if( $orderReport->isEmpty() ){
					  $reportTable .= '<tr>
										  <th scope="row" colspan="6" class="error"> 
										  <font color="red"><center>'.$this->lang['no_data_found_txt'].'</center></font> 
										  </th>
										</tr>';
				    }else{
						$iCounter = 1;
						$orderData =  array();
					foreach($orderReport as $orderItem){
					  $customer_id = $orderItem['Order']['customer_id'];
					  $ticket_row_id = $orderItem['ticket_row_id'];
					  $ticket_category = $orderItem['ticket_category'];
					  $ticket_row = $orderItem['ticket_row'];
					  $seat_sequence = $orderItem['seat_sequence'];
					  $customer_data = Models\User::with('memberdata')->where('id', '=', $customer_id)->get();
					  foreach($customer_data as $getCustomer){
					     //$last_name = $getCustomer['memberdata']['last_name'];
						 $last_name = $getCustomer['name'];
						 $first_name = $getCustomer['memberdata']['first_name'];
						 $telephone = $getCustomer['memberdata']['phone_no'];
						 $email = $getCustomer['email'];
					  }
					  $placement_id = '';
					  $operator_id = '';
					  if( $ticket_row_id !== null || $ticket_row_id != ''){
						    if(is_numeric($ticket_row_id) ){
					            $placement = Models\RowSeats::where('id', '=', $ticket_row_id)->get();
								if( $placement->isEmpty() ){
								}else{
									foreach($placement as $rowp){
									 	$placement_id = $rowp['placement'];
										$operator_id = $rowp['operator_id'];
								    }
								}
							}else{
								$placement = '';
							}
					  }else{
						  $placement = '';
					  }
				      $reportTable2 = '<tr>
					                      <td> '.$last_name.' </td>
										  <td>'.$first_name.'</td>
										  <td>'.$telephone.'</td>
										  <td>'.$email.'</td>
										  <td>'.$this->ticekt_type($placement_id).'</td>
										  <td> Internet</td>
					                      <td> '.$ticket_category.' </td>
										  <td> '.$ticket_row.' </td>
										  <td> '.$seat_sequence.' </td>
										</tr>'; 
										
					$orderData['last_name'] = $last_name; 
					$orderData['first_name'] = $first_name; 
					$orderData['telephone'] = $telephone; 
					$orderData['email'] = $email; 
					$orderData['placement_id'] = $placement_id; 
					$orderData['source'] = 'Internet'; 
					$orderData['ticket_category'] = $ticket_category; 
					$orderData['ticket_row'] = $ticket_row; 
					$orderData['seat_sequence'] = $seat_sequence; 
					$dumpData[] = $orderData;						
				  $iCounter++; 
				  }
                  // Sort the array
				  sort($dumpData);
				foreach($dumpData as $arr){
				 $reportTable .= '<tr>
					                      <td> '.$arr['last_name'].' </td>
										  <td>'.$arr['first_name'].'</td>
										  <td>'.$arr['telephone'].'</td>
										  <td style="display:none">'.$arr['email'].'</td>
										  <td style="display:none">'.$this->ticekt_type($arr['placement_id']).'</td>
										  <td style="display:none"> Internet</td>
					                      <td> '.$arr['ticket_category'].' </td>
										  <td> '.$arr['ticket_row'].' </td>
										  <td> '.$arr['seat_sequence'].' </td>
										</tr>'; 
				}
				
				$reportTable .= '<tr>
					                      <td colspan="5" style="text-align:right;"><strong>'.$this->lang['dashboard_total_sale_amount_txt'].'</strong></td>
										  <td ><strong>'.number_format($total_sale).'</strong></td>
										</tr>'; 
					}
					$htmlcontent.= $reportTable;	   
				}
			}
			
			$htmlcontent.='</body></html>';
			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml($htmlcontent);
			
			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');
			//$dompdf->setPaper('A4');
			
			// Render the HTML as PDF
			$dompdf->render();
			
			// Output the generated PDF to Browser
			$filename = 'eventSaleReport_'.time();
			$dompdf->stream($filename.".pdf");
			exit; // This is very important for downloading the PDF 
	}	
	
	// Ticket Type
	public function ticekt_type($ticekt_type=''){
	   	if($ticekt_type == 1){
			return 'Standard';
		}else if($ticekt_type == 2){
			return 'Réservées';
		}else if($ticekt_type == 3){
			return  'Invitations';
		}else if($ticekt_type == 4){
			return 'Vendues à autre opérateur';
		}else{
		   return '';	
		}
	}
	
	// Get operator name
	public function ticekt_operator($operator_id=''){
		if($operator_id != ''){
			$op_name = Models\Operators::where('op_id', '=', $operator_id)->get();
		}else{
			$op_name = '';
		}
		return $op_name;
	}
	
	// This Week
	public  function this_week_range($date) {
		$ts = strtotime($date);
		$start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
		return array(date('Y-m-d', $start),
					 date('Y-m-d', strtotime('next saturday', $start)));
	}
	
	// This month
	public function this_month_range(){
	  $first_day_this_month = date('Y-m-01'); // hard-coded '01' for first day
      $last_day_this_month  = date('Y-m-t');
      return array($first_day_this_month,
					 $last_day_this_month);	
	}
	
	//Calculate number of hours between pass and now
	public function calculateHours($datetime){
	   $dayinpass = $datetime;
	   $today = time();
	   if($dayinpass == $today){
		   $hours =  'Just now';	
		}else{
			$dayinpass= strtotime($dayinpass);
		   $hours = round(abs($today-$dayinpass)/60/60).' hrs ago';
		}
		return $hours;
	}
	
	// time ago function
	public function get_time_ago( $time )
		{
			$time_difference = time() - strtotime($time);
		
			if( $time_difference < 1 ) { return ''.$this->lang['dashboard_less_second_ago_txt'].''; }
			$condition = array( 12 * 30 * 24 * 60 * 60 =>  ''.$this->lang['dashboard_year_txt'].'',
						30 * 24 * 60 * 60       =>  ''.$this->lang['dashboard_month_txt'].'',
						24 * 60 * 60            =>  ''.$this->lang['dashboard_day_txt'].'',
						60 * 60                 =>  ''.$this->lang['dashboard_hour_txt'].'',
						60                      =>  ''.$this->lang['dashboard_minute_txt'].'',
						1                       =>  ''.$this->lang['dashboard_second_txt'].''
			);
			
			foreach( $condition as $secs => $str )
			{
				$d = $time_difference / $secs;
		
				if( $d >= 1 )
				{
					$t = round( $d );
					return ' ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' '.$this->lang['dashboard_ago_txt'].'';
				}
			}
		}
		
		// Get order
		public function getOrder($request, $response, $args){
		error_reporting(0);
		$id = $args['id'];
		$order = Models\Order::find($id);
		// Get customer data asssociated to this order
		$customer = Models\User::with(['customerdata'])->where('id','=',$order['customer_id'])->get();
		// Get order detail
		$order_itmes_data = Models\OrderItems::where('order_id','=',$order['id'])->get();
		$order_itmes_list  = '';
		$total = 0;
		$sub_total = 0;
		$booking_fee = 0;
		$producer_id  = '';
		if( !$order_itmes_data->isEmpty()){
			foreach($order_itmes_data as $row){
				if($row['type_product'] != 'booking_fees'){
				 $sub_total += $row['price']*$row['quantity'];
				 $total_seat = $row['price']*$row['quantity'];
			     $event_name =   Models\Event::where('id', '=', $row['product_id'])->first()->title;
			     $order_itmes_list  .= '<tr>
									    <td>'.$event_name.'</td>
									    <td>'.$row['ticket_category'].'</td>
									    <td>'.$row['ticket_row'].'</td>
									    <td class="text-center">'.$row['seat_sequence'].'</td>
									    <td class="text-center">'.$row['price'].'</td>
									    <td class="text-center">'.$row['quantity'].'</td>
									    <td class="text-right">'.$total_seat.'</td>
								        </tr>';
				}else{
					$booking_fee += $row['price']*$row['quantity'];
				}
			  	$producer_id = $row['producer_id'];				
			}
		}else{
			 $order_itmes_list  .= '<tr class="error"><td colspan="7"><center>'.$this->lang['no_data_found_txt'].'</center></td></tr>';
		}
		
		if($producer_id == 1){
			$producer_data = Models\User::find($producer_id);
			$product_meta_data = array('full_name' => $producer_data['name'],
			                          'email' => $producer_data['email']);
		}else{
			$producer_data = Models\Productor_meta::where('user_id','=', $producer_id)->get();
			$product_meta_data = array('full_name' => $producer_data[0]['company_name'],
			                          'email' => $producer_data[0]['telephone'].'<br>'.
									  $producer_data[0]['company_number'].'<br>');
		}
		$customer_first_name = $customer[0]['customerdata']['first_name'];
		$customer_last_name = $customer[0]['customerdata']['last_name'];
		$customer_address = $customer[0]['customerdata']['address_1'];
		$customer_postcode = $customer[0]['customerdata']['postal_code'];
		$customer_city = $customer[0]['customerdata']['ville'];
		$customers_data = $customer_first_name. ' '. $customer[0]['name']. 
		                 '<br>'.$customer_address.' '.$customer_postcode.'<br>'.$customer_city; 
		//ddump($product_meta_data); exit;
		$total = $sub_total + $booking_fee;
		//ddump($order_itmes_data); exit;
		$order_date = date('F j , Y',  strtotime($order['created_on']));
		$month_name = date('n', strtotime($order['created_on']));
		$order_date = get_month_name($month_name). date(' j , Y',  strtotime($order['created_on']));
		$order_payment_type = get_payment_method($order['payment_type']);
		$customer_data = $customer[0]['customerdata'];
		$params = array( 'title' => 'Order Detail',
		                'order' => $order,
						'customer_data' => $customer[0]['customerdata'],
						'order_date' => $order_date,
						'items_list' => $order_itmes_list,
						'sub_total' => $sub_total,
						'booking_fee' => $booking_fee,
						'total' => $total,
						'producer_data' => $product_meta_data,
						'order_id' => $id);
				$table ='<div class="col-md-12">
  <div class="invoice-title">
    <h2>'.$this->lang['dashboard_invoice_txt'].'</h2>
    <h3 class="pull-right">'.$this->lang['dashboard_invoice_txt'].' # '.$order['invoice_number'].'</h3>
  </div>
  <hr>
   <table width="100%" border="0">
  <tr>
    <td style="float:left"><table width="100%" border="0" style="text-align:left">
      <tr>
        <td><strong>'.$this->lang['dashboard_billed_to_txt'].':</strong><br>
            '.$customer_first_name. ' '. $customer[0]['name'].'<br>
            '.$customer[0]['email'].'<br><br></td>
      </tr>
    </table></td>
    <td>&nbsp;</td>
    <td style="float:right"><table width="100%" border="0" style="text-align:right">
      <tr>
        <td> <strong>'.$this->lang['dashboard_shipped_to_txt'].':</strong><br>
            '.$customers_data.'</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0">
      <tr>
        <td><strong>'.$this->lang['dashboard_payment_method_txt'].':</strong><br>
			Type : '.$order_payment_type.'<br>
    		TXN ID: '.$order['payment_id'].'
      </tr>
    </table></td>
    <td>&nbsp;</td>
    <td style="float:right"><table width="100%" border="0" style="text-align:right">
      <tr>
        <td>
			<strong>'.$this->lang['dashboard_order_date_txt'].':</strong><br>
			'.$order_date.'
			<br><br>
		</td>
      </tr>
    </table></td>
  </tr>
</table>
  </div>
</div>
<div class="col-md-12">
  <div class="panel panel-default" style="text-align:left">
    <div class="panel-heading" style="text-align:left">
      <h3 class="panel-title" style="text-align:left; font-size: 1.25rem">
	  <strong>'.$this->lang['dashboard_order_summary_txt'].'</strong></h3>
    </div>
    <div class="panel-body">
      <div class="table-responsive">
        <table class="table table-condensed">
          <thead>
            <tr>
              <td><strong>'.$this->lang['dashboard_event_txt'].'</strong></td>
              <td><strong>'.$this->lang['dashboard_category_txt'].'</strong></td>
              <td><strong>'.$this->lang['dashboard_row_txt'].'</strong></td>
              <td class="text-center"><strong>'.$this->lang['dashboard_seat_txt'].'</strong></td>
              <td class="text-center"><strong>'.$this->lang['dashboard_price_txt'].'</strong></td>
              <td class="text-center"><strong>'.$this->lang['dashboard_quantity_txt'].'</strong></td>
              <td class="text-right"><strong>'.$this->lang['dashboard_totals_txt'].'</strong></td>
            </tr>
          </thead>
          <tbody>
            <!-- foreach ($order->lineItems as $line) or some such thing here -->
          '.$order_itmes_list.'
          <tr>
            <td class="thick-line"></td>
            <td class="thick-line"></td>
            <td class="thick-line"></td>
            <td class="thick-line"></td>
            <td class="thick-line"></td>
            <td class="thick-line text-center"><strong>'.$this->lang['dashboard_sub_total_txt'].'</strong></td>
            <td class="thick-line text-right">'.$sub_total.'</td>
          </tr>
          <tr>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line text-center"><strong>'.$this->lang['dashboard_booking_fee_txt'].'</strong></td>
            <td class="no-line text-right">'.$booking_fee.'</td>
          </tr>
          <tr>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line"></td>
            <td class="no-line text-center"><strong>'.$this->lang['dashboard_total_txt'].'</strong></td>
            <td class="no-line text-right">'.$total.'</td>
          </tr>
            </tbody>
        </table>
      </div>
    </div>
  </div>
  </form>
  <!--end::Form--> 
  
  <a href="javascript:void(0);" onclick="sendConfirmationEmail('.$id.', '.$order['customer_id'].')" class="btn btn-info pull-right" style="margin:0 auto"><i class="la la-mail"></i> Re-send Email </a> 
  <div id="emailMsgDiv"></div> 
</div>
<!--end::Portlet--> ';
			echo $table;
		}



	
		
	// Dashboard Orders list
	public function dashboardOrdersList($request, $response){
		
		$from_date_val = $request->getParam('post_data')['from_date_val'];
		$to_date_val = $request->getParam('post_data')['to_date_val'];	
		if(isset($from_date_val) && !empty($from_date_val) ){
			if(isset($to_date_val) && !empty($to_date_val) ){
			   $startOfMonth = $from_date_val;
			   $endOfMonth   = $to_date_val;
			}
		}else{
		    // Get this month orders
			$this_month_dates = $this->this_month_range();
			$startOfMonth = $this_month_dates[0];		
			$endOfMonth = $this_month_dates[1];	
		}
		
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
		
		$total   = Models\Order::where('created_on', '>', $startOfMonth. ' 01:59:59')
									->where('created_on', '<=', $endOfMonth . ' 23:59:59')
									 ->get()->count(); // get count 
				
		if($page == 1){
		   $offset = 0;	
		   $perpage = 0;
		}else{
		  $offset = ($page-1);	
		  $perpage = $per_page;	
		}
		if($per_page <= 1){
		  $pages = intval($total/7);
	    }else{
	      $pages = intval($total/$per_page);
		}
		if($per_page <= 1){
		   $perPageLimit = 7;	
		}else{
		   $perPageLimit = $per_page;	
		}
		// Get orders list
		$orders_list = Models\Order::with(['Customer'])
									->where('created_on', '>', $startOfMonth. ' 01:59:59')
									->where('created_on', '<=', $endOfMonth . ' 23:59:59')
									->skip($offset*$perPageLimit)->take($perPageLimit)
									->orderBy($field, $sort)->get();
		
			
		$data = array();
		// Process the orders_list array here
		foreach($orders_list as $get){
			$order_id = $get['id']; // Order id
			$event_id = Models\OrderItems::where('order_id', '=', $order_id)->
			                               where('type_product', '=', 'event')->
										   first()->
										   product_id;
			$event_name = Models\Event::where('id', '=', $event_id)->first()->title;
		  	$array_data = array();
			$array_data['id']  = '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$get['id'].')">'.$get->id.'</a>';
            $array_data['customer_name']  = '<a href="javascript:void(0)" title="'.$this->lang['dashboard_view_order_txt'].'" onClick="view_order('.$get['id'].')">'.$get['Customer']['name'].'</a>';
			$array_data['total_amount']  = $get->total_amount;
            $array_data['customer_email']  = $get['Customer']['email'];
			$array_data['event_name']  = strip_tags(htmlspecialchars_decode($event_name));
			$array_data['created_on']  =  hr_date($get->created_on);
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
		
		
    // Function to download the monthly report
	public function downloadPDF($request, $response, $args){
	  	$from_date = $args['from_date']; // Get from date
		$to_date   = $args['to_date']; // Get to date
		if( isset($from_date) && !empty($from_date) ){
		    if( isset($to_date) && !empty($to_date) ){
				$date_range = date('F j , Y',  strtotime($from_date)).' - '.date('F j , Y',  strtotime($to_date));
			    // Get all orders placed within the from and to date range
				$orders_list = Models\Order::with(['Customer'])
								->where('created_on', '>', $from_date. ' 01:59:59')
									->where('created_on', '<=', $to_date . ' 23:59:59')
								->orderBy('id', 'DESC')
								->orderBy('created_on', 'DESC')
								->get();
			  $html = '<!DOCTYPE html>
						<html>
						<head>
						<style>
						#pdf_table {
							font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
							border-collapse: collapse;
							width: 100%;
						}
						
						#pdf_table td, #pdf_table th {
							border: 1px solid #ddd;
							padding: 8px;
						}
						
						#pdf_table tr:nth-child(even){background-color: #f2f2f2;}
						
						#pdf_table tr:hover {
							background-color: #ddd;
						}
						
						#pdf_table th {
							padding-top: 12px;
							padding-bottom: 12px;
							text-align: left;
							background-color: #4CAF50;
							color: white;
						}
						</style>
						</head>
						<body>';
				$html .= '<table width="100%" ><tr>
<td style="float:left; text-align:left; font-size:20px; font-weight:bold">'.$this->lang['dashboard_order_report_txt'].'</td><td style="float:right; text-align:right; font-size:20px; font-weight:bold">'.$date_range.'</td>
</tr>
</table>';
				$html .= '<hr style="width:100%">';		
				$html .= '<table id="pdf_table">';	
				$html .= '<tr>
						 <th >'.$this->lang['dashboard_order_id_txt'].'</th>
						 <th >'.$this->lang['dashboard_invoice_txt'].' #</th>
						 <th>'.$this->lang['dashboard_customer_name_txt'].'</th>
						 <th >Email</th>
						 <th >Spectacle</th>
						 <th >'.$this->lang['dashboard_category_txt'].'</th>
						 <th >'.$this->lang['dashboard_row_txt'].'</th>
						 <th >'.$this->lang['dashboard_seat_txt'].'</th>
						 <th >'.$this->lang['dashboard_amount_txt'].'</th>
						 <th > '.$this->lang['dashboard_order_date_txt'].'</th>
					     </tr>';
			  if( $orders_list->isEmpty() ){
				  $html .= '<tr>
									<td colspan="5"><center><font color="red">'.$this->lang['no_data_found_txt'].'</font></center></td>
								  </tr>';
			  }else{
				  $total = 0;
				  foreach($orders_list as $order){
					  $order_id = $order['id']; // Order id
					  $orderItemsData = Models\OrderItems::where('order_id', '=', $order_id)->
			                               where('type_product', '=', 'event')->
										   get();
					 					   
					 $event_id = $orderItemsData[0]['product_id'];	
					 $category  = $orderItemsData[0]['ticket_category'];
					 $row       = $orderItemsData[0]['ticket_row'];	
					 $seat      = $orderItemsData[0]['seat_sequence'];											 
			         $event_name = Models\Event::where('id', '=', $event_id)->first()->title;
					 $total += $order['total_amount'];
					 $html .= '<tr>
									<td>'.$order['id'].'</td>
									<td>'.$order['invoice_number'].'</td>
									<td>'.$order['Customer']['name'].'</td>
									<td>'.$order['Customer']['email'].'</td>
									<td>'.strip_tags(htmlspecialchars_decode($event_name)).'</td>
									<td>'.$category.'</td>
									<td>'.$row.'</td>
									<td>'.$seat.'</td>
									<td>'.$order['total_amount'].'</td>
									<td>'.hr_date($order['created_on']).'</td>
								  </tr>';
				  }
				  $html .= '<tr>
									<td colspan="8" style="text-align:right">'.$this->lang['dashboard_total_txt']. ' ' .$this->lang['dashboard_amount_txt'].'</td>
									<td  style="text-align:left" colspan="2">'.$total.'</td>
								  </tr>';
			  }
			  $html .= '</table>';
			  $html .= '</body>
                        </html>';
			 
			    $dompdf = new Dompdf();
				// Load HTML content
				$dompdf->loadHtml($html);
				
				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A4', 'landscape');
				//$dompdf->setPaper('A4');
				
				// Render the HTML as PDF
				$dompdf->render();
				
				// Output the generated PDF to Browser
				$filename = 'orderMonthlyReport_'.time();
				$dompdf->stream($filename.".pdf");
				exit; // This is very important for downloading the PDF	
			  
			}
		}
		
	}
	
	// Function to download CSV of the order report
	public function downloadCSV($request, $response, $args){
		$from_date = $args['from_date']; // Get from date
		$to_date   = $args['to_date']; // Get to date
		if( isset($from_date) && !empty($from_date) ){
		    if( isset($to_date) && !empty($to_date) ){
				$date_range = date('F j , Y',  strtotime($from_date)).' - '.date('F j , Y',  strtotime($to_date));
			    // Get all orders placed within the from and to date range
				$orders_list = Models\Order::with(['Customer'])
								->where('created_on', '>', $from_date. ' 01:59:59')
									->where('created_on', '<=', $to_date . ' 23:59:59')
								->orderBy('id', 'DESC')
								->orderBy('created_on', 'DESC')
								->get();
				ob_end_clean();
		        header( 'Content-Type: text/csv' );
		        header( 'Content-Disposition: attachment;filename='.time().'.csv');
		        $fp = fopen('php://output', 'w');
				 $titles = array($this->lang['dashboard_order_id_txt'], 
			                    $this->lang['dashboard_invoice_txt'].' #',
							    $this->lang['dashboard_customer_name_txt'], 
								'Email',
								'Spectacle',
								$this->lang['dashboard_category_txt'],
						        $this->lang['dashboard_row_txt'],
						        $this->lang['dashboard_seat_txt'],
							    $this->lang['dashboard_amount_txt'],
							    $this->lang['dashboard_order_date_txt']							
							);
				// This line is very very important to overcome the issue of French Characters in the CSV			
				$titles = array_map("utf8_decode", $titles);			
		        fputcsv($fp, $titles);
				foreach ($orders_list as $row) {
					$order_id = $row['id']; // Order id
			        $orderItemsData = Models\OrderItems::where('order_id', '=', $order_id)->
			                               where('type_product', '=', 'event')->
										   get();
					 					   
					$event_id = $orderItemsData[0]['product_id'];	
					$category  = $orderItemsData[0]['ticket_category'];
					$seat_row  = $orderItemsData[0]['ticket_row'];	
					$seat      = $orderItemsData[0]['seat_sequence'];	
			        $event_name = Models\Event::where('id', '=', $event_id)->first()->title;
					$data = array($row['id'], 
					              $row['invoice_number'],
								  $row['Customer']['name'],
								  $row['Customer']['email'],
								  strip_tags(htmlspecialchars_decode($event_name)),
								  $category,
								  $seat_row,
								  $seat,
								  $row['total_amount'],
								  hr_date($row['created_on']));
					// Put the data array to the make its CSV Columns
					$data = array_map("utf8_decode", $data);		
					fputcsv($fp, $data);
				}	
			    fclose($fp);
		        $contLength = ob_get_length();
		        header( 'Content-Length: '.$contLength);
			}
		}
	}
	
	// Export to CSV
	public function export_to_csv($result)
	{
		if(!$result) return false;
		ob_end_clean();
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=pedidos.csv');
		$fp = fopen('php://output', 'w');
		$headrow = $result[0];
		fputcsv($fp, array_keys($headrow));
		foreach ($result as $data) {
			fputcsv($fp, $data);
		}
		fclose($fp);
		$contLength = ob_get_length();
		header( 'Content-Length: '.$contLength);
	}
	
	// Update titles
	public function updateTitles($request, $response){
		// Get all categories 
		$categoies = Models\Category::get();
		foreach($categoies as $cat){
			$cat_id = $cat['id']; 
			$categoryName = $cat['name'];
			$slug = str_replace(' ', '_', $categoryName);
		    $slug = str_replace('_', '-', $slug);
		    $slug = str_replace('/', '-', $slug);
		    $slug = Generate_SEO_Url($slug);
			$data = array('slug' => $slug);
			$category = Models\Category::where('id', '=', $cat_id)->update($data);	
		}
		
		// Update titles for the event group
		$evengroups = Models\Eventgroup::get();
		foreach($evengroups as $group){
		    $id = $group['id'];
			$title = $group['title'];
			$slug =  html_entity_decode($title);
			$slug =  htmlspecialchars_decode($slug);
			$slug =  strip_tags($slug);
			$raw_text =  $slug ;
			$utf8_text = $this->strip_html_tags( $slug );
			$slug = html_entity_decode( $utf8_text, ENT_QUOTES, "UTF-8" );
			$slug = $this->strip_html_tags($slug);
			$slug = clearString($slug);
		    $slug = str_replace('_', '-', $slug);
		    $slug = str_replace('/', '-', $slug);
			$slug = format_uri($slug);
		    $slug = Generate_SEO_Url($slug);
			$dataGroup = array('event_group_slug' => $slug);
	        //ddump($dataGroup);
			$groupUpdate = Models\Eventgroup::where('id', '=', $id)->update($dataGroup);		
		}
	}
	
 public function strip_html_tags( $text )
 {
  // Remove invisible content
    $text = preg_replace(
        array(
            //ADD a (') before @<head ON NEXT LINE. Why? see below
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $text );
    return strip_tags( $text );
}


  public function xls(){
	  // "phpoffice/phpspreadsheet": "1.0"
	  header('Content-type: text/csv');
	$name = $jsonArgs['startDate']." through ".$jsonArgs['endDate'].".csv";
	header('Content-Disposition: attachment; filename="'.$name.'"'); 

	// do not cache the file
	header('Pragma: no-cache');
	header('Expires: 0');

	// create a file pointer connected to the output stream
	$file = fopen('php://output', 'w');

	// send the column headers
	fputcsv($file, array('tracking_number', 'store', 'street', 'state', 'zip', 'city', 'country', 'date', 'serial_number'));
	global $fawkesEngine;
	$results = $fawkesEngine->createDateRangeReport($jsonArgs);
	
	$data = array();
	// need to do some special formatting to prevent scientific notation
	foreach ($results as $value) {
		$formattedTrackingNumber = $value['tracking_number'];
		$formattedTrackingNumber = "=".'"'.$formattedTrackingNumber.'"';
		$formattedSerialNumber = $value['serial_number'];
		$formattedSerialNumber = "=".'"'.$formattedSerialNumber.'"';
		$dataTemp = array($formattedTrackingNumber,$value['store'],$value['street'],$value['state'],$value['zip'],$value['city']
				,$value['country'],$value['received_date'],$formattedSerialNumber);
			array_push($data, $dataTemp);
	}

	// output each row of the data
	foreach ($data as $row)
	{
		fputcsv($file, $row);
	}
	fclose($file);
	exit;  
  }

// Download Sale report as CSV
public function downloadSaleReportCSV($request, $response, $args){
	       // echo $args['event_group_id']. ' = '.$args['event_id'];
			$event_group_id = $args['event_group_id'];
			$event_id = $args['event_id'];
			 // Get event Name
			 $event_name = Models\Event::where('id', '=', $event_id)->first()->title;
			 $event_name = trim(strip_tags(trim(clearString(htmlspecialchars_decode($event_name)))));	
				
			
			    ob_end_clean();
		        header( 'Content-Type: text/csv' );
		        header( 'Content-Disposition: attachment;filename='.time().'.csv');
		        $fp = fopen('php://output', 'w');	
				 $titles = array($this->lang['dashboard_last_name_txt'], 
			                    $this->lang['dashboard_first_name_txt'].' #',
							    $this->lang['dashboard_telephone_txt'], 
								$this->lang['dashboard_category_txt'],
						        $this->lang['dashboard_row_txt'],
						        $this->lang['dashboard_seat_txt']						
							);
			$titles = array_map("utf8_decode", $titles);			
		    fputcsv($fp, $titles);				  	
			if(isset($event_group_id) && isset($event_id) ){
				if( !empty($event_group_id) && !empty($event_id) ){	
					 // Get all ids of this event from the event_seat_categories table
				     $event_categories_data = Models\EventSeatCategories::where('event_id', '=', $event_id)->get();
					 $event_seat_categories_id = '';
					foreach($event_categories_data as $row){
					   $event_cat_ids[] = $row['id'];	
					   $event_seat_categories_id .= $row['id'].',';
					}
					// Find sum of the total quantity of the seats for this event
				    $total_seats = Models\RowSeats::whereIn('event_seat_categories_id', 
				                   array(rtrim($event_seat_categories_id,',')))->sum('total_qantity');
				    // Find sum of the booked seats for this event
				    $total_booked_seats = Models\OrderItems::where('type_product', '=', 'event')
				                         ->where('product_id', '=', $event_id)->sum('quantity');
				    // Find sum of the total sale for this event
				    $total_sale = Models\OrderItems::where('type_product', '=', 'event')
				                ->where('product_id', '=', $event_id)->sum('price');						 			   
					// Dispaly list of events when searched
			        $events_list = Models\Event::where('eventgroup_id', '=', $event_group_id)->get();
				    // Get This Event Complete Sale report
				    $orderReport = Models\OrderItems::with('Order')->
				                   where('type_product', '=', 'event')->
							       where('product_id', '=', $event_id)->get();
					if( $orderReport->isEmpty() ){
					   // Do not do anything
				    }else{
						$iCounter = 1;
						$orderData =  array();
					foreach($orderReport as $orderItem){
					  $customer_id = $orderItem['Order']['customer_id'];
					  $ticket_row_id = $orderItem['ticket_row_id'];
					  $ticket_category = $orderItem['ticket_category'];
					  $ticket_row = $orderItem['ticket_row'];
					  $seat_sequence = $orderItem['seat_sequence'];
					  $customer_data = Models\User::with('memberdata')->where('id', '=', $customer_id)->get();
					  foreach($customer_data as $getCustomer){
					     //$last_name = $getCustomer['memberdata']['last_name'];
						 $last_name = $getCustomer['name'];
						 $first_name = $getCustomer['memberdata']['first_name'];
						 $telephone = $getCustomer['memberdata']['phone_no'];
						 $email = $getCustomer['email'];
					  }
					  $placement_id = '';
					  $operator_id = '';
					  if( $ticket_row_id !== null || $ticket_row_id != ''){
						    if(is_numeric($ticket_row_id) ){
					            $placement = Models\RowSeats::where('id', '=', $ticket_row_id)->get();
								if( $placement->isEmpty() ){
								}else{
									foreach($placement as $rowp){
									 	$placement_id = $rowp['placement'];
										$operator_id = $rowp['operator_id'];
								    }
								}
							}else{
								$placement = '';
							}
					  }else{
						  $placement = '';
					  }
				      
										
					$orderData['last_name'] = $last_name; 
					$orderData['first_name'] = $first_name; 
					$orderData['telephone'] = $telephone; 
					$orderData['email'] = $email; 
					$orderData['placement_id'] = $placement_id; 
					$orderData['source'] = 'Internet'; 
					$orderData['ticket_category'] = $ticket_category; 
					$orderData['ticket_row'] = $ticket_row; 
					$orderData['seat_sequence'] = $seat_sequence; 
					$dumpData[] = $orderData;						
				  $iCounter++; 
				  }
                  // Sort the array
				  sort($dumpData);
				  
				foreach($dumpData as $arr){
				
					$data = array( $arr['last_name'],
										  $arr['first_name'],
										   $arr['telephone'],
										   $arr['ticket_category'],
										   $arr['ticket_row'],
										   $arr['seat_sequence']);
					// Put the data array to the make its CSV Columns
					$data = array_map("utf8_decode", $data);		
					fputcsv($fp, $data);					
				}
				fclose($fp);
		        $contLength = ob_get_length();
		        header( 'Content-Length: '.$contLength);
				
				
					}
						   
				}
			}
			
			
	}
	
	
	// Download Sale report as Excel
public function downloadSaleReportXLS($request, $response, $args){
	        // echo $args['event_group_id']. ' = '.$args['event_id'];
			$event_group_id = $args['event_group_id'];
			$event_id = $args['event_id'];
			 // Get event Name
			 $event_name = Models\Event::where('id', '=', $event_id)->first()->title;
			 $event_name = trim(strip_tags(trim(clearString(htmlspecialchars_decode($event_name)))));	
			$return_file_name='';
			$dateFilename=date('Ymdhis');
			$htmlcontent='
					<!doctype html>
					<html lang="en">
					<head>
					<style>
					table {
						font-family: arial, sans-serif;
						border-collapse: collapse;
						width: 100%;
					}				
					td, th {
						border: 1px solid #dddddd;
						text-align: left;
						padding: 8px;
						height:25px;
						border-collapse:collapse;
					}
					tr:nth-child(even) {
						background-color: #dddddd;
						border-collapse:collapse;
					}
					.error{
					  color:#f4516c;	
					}
					</style>
					</head>			  
					<body>';
			$NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';	
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTable = 	'<h1><center>'.$this->lang['dashboard_sale_report_of_txt'].' '.$event_name.'</center></h1>';	
			$reportTable .= '<table class="table table-striped m-table table-bordered">
						  <thead>
							<tr>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['dashboard_last_name_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['dashboard_first_name_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['dashboard_telephone_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['dashboard_category_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['dashboard_row_txt'].'</h3></th>
							  <th style="'.$thStyle.'"><h3>'.$this->lang['dashboard_seat_txt'].'</h3></th>
							</tr>
						  </thead>
						  <tbody>';		
			if(isset($event_group_id) && isset($event_id) ){
				if( !empty($event_group_id) && !empty($event_id) ){	
					 // Get all ids of this event from the event_seat_categories table
				     $event_categories_data = Models\EventSeatCategories::where('event_id', '=', $event_id)->get();
					 $event_seat_categories_id = '';
					foreach($event_categories_data as $row){
					   $event_cat_ids[] = $row['id'];	
					   $event_seat_categories_id .= $row['id'].',';
					}
					// Find sum of the total quantity of the seats for this event
				    $total_seats = Models\RowSeats::whereIn('event_seat_categories_id', 
				                   array(rtrim($event_seat_categories_id,',')))->sum('total_qantity');
				    // Find sum of the booked seats for this event
				    $total_booked_seats = Models\OrderItems::where('type_product', '=', 'event')
				                         ->where('product_id', '=', $event_id)->sum('quantity');
				    // Find sum of the total sale for this event
				    $total_sale = Models\OrderItems::where('type_product', '=', 'event')
				                ->where('product_id', '=', $event_id)->sum('price');						 			   
					// Dispaly list of events when searched
			        $events_list = Models\Event::where('eventgroup_id', '=', $event_group_id)->get();
				    // Get This Event Complete Sale report
				    $orderReport = Models\OrderItems::with('Order')->
				                   where('type_product', '=', 'event')->
							       where('product_id', '=', $event_id)->get();
					if( $orderReport->isEmpty() ){
					  $reportTable .= '<tr>
										  <th scope="row" colspan="6" class="error"> 
										  <font color="red"><center>'.$this->lang['no_data_found_txt'].'</center></font> 
										  </th>
										</tr>';
				    }else{
						$iCounter = 1;
						$orderData =  array();
					foreach($orderReport as $orderItem){
					  $customer_id = $orderItem['Order']['customer_id'];
					  $ticket_row_id = $orderItem['ticket_row_id'];
					  $ticket_category = $orderItem['ticket_category'];
					  $ticket_row = $orderItem['ticket_row'];
					  $seat_sequence = $orderItem['seat_sequence'];
					  $customer_data = Models\User::with('memberdata')->where('id', '=', $customer_id)->get();
					  foreach($customer_data as $getCustomer){
					     //$last_name = $getCustomer['memberdata']['last_name'];
						 $last_name = $getCustomer['name'];
						 $first_name = $getCustomer['memberdata']['first_name'];
						 $telephone = $getCustomer['memberdata']['phone_no'];
						 $email = $getCustomer['email'];
					  }
					  $placement_id = '';
					  $operator_id = '';
					  if( $ticket_row_id !== null || $ticket_row_id != ''){
						    if(is_numeric($ticket_row_id) ){
					            $placement = Models\RowSeats::where('id', '=', $ticket_row_id)->get();
								if( $placement->isEmpty() ){
								}else{
									foreach($placement as $rowp){
									 	$placement_id = $rowp['placement'];
										$operator_id = $rowp['operator_id'];
								    }
								}
							}else{
								$placement = '';
							}
					  }else{
						  $placement = '';
					  }
										
					$orderData['last_name'] = $last_name; 
					$orderData['first_name'] = $first_name; 
					$orderData['telephone'] = $telephone; 
					$orderData['email'] = $email; 
					$orderData['placement_id'] = $placement_id; 
					$orderData['source'] = 'Internet'; 
					$orderData['ticket_category'] = $ticket_category; 
					$orderData['ticket_row'] = $ticket_row; 
					$orderData['seat_sequence'] = $seat_sequence; 
					$dumpData[] = $orderData;						
				    $iCounter++; 
				  }
                // Sort the array
				sort($dumpData);
				$i=0;
				foreach($dumpData as $arr){
					if($i % 2 == 0){
					  $style = $NthStyle;	
					}else{
					  $style = $EthStyle;	
					}
				 $reportTable .= '<tr>
									  <td style="'.$style.'">'.$arr['last_name'].'</td>
									  <td style="'.$style.'">'.$arr['first_name'].'</td>
									  <td style="'.$style.'">'.$arr['telephone'].'</td>
									  <td style="'.$style.'">'.$arr['ticket_category'].'</td>
									  <td style="'.$style.'">'.$arr['ticket_row'].'</td>
									  <td style="'.$style.'">'.$arr['seat_sequence'].'</td>
								</tr>'; 
				$i++;}
				
				$reportTable .= '<tr>
					                      <td colspan="5" style="text-align:right;"><strong>'.$this->lang['dashboard_total_sale_amount_txt'].'</strong></td>
										  <td ><strong>'.number_format($total_sale).'</strong></td>
										</tr>'; 
					}
					$htmlcontent.= $reportTable;	   
				}
			}
			$htmlcontent .= '</table>';
			$htmlcontent.='</body></html>';
			$html = utf8_decode($htmlcontent);
			header("Content-Type: application/xls");    
			header("Content-Disposition: attachment; filename=".time().".xls"); 
			//header("Content-Type: application/vnd.ms-excel");
			 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			echo $html;
			exit; // This is very important for downloading the XLS 
}	      		




} // End of the class


