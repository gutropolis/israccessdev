<?php
namespace App\Controllers;

use App\Models;
use App\Tools\Auth;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

use Dompdf\Dompdf;

/**
   Admin Producdtor Dashboard Controller
   Available Functions.
   1. dashboard
   
   
*/

class AdminProductorDashboardController extends Base 
{
	
	protected $container;
	protected $lang;
	// Class constructor 
	public function __construct($container)
	{
		$this->container = $container;
		$this->lang =  $this->container->view['adminLang'];
	}
	
	 public function dashboard($request, $response) {
		 $producer_id = $_SESSION['adminId'];
		 $mapped_Groups = Models\Eventgroup::where('status', '=', 1)->where('producer_id', '=', $producer_id)
		                  ->orderBy('title',  'ASC')->get();
				 $event_groups_array = array();
				 foreach($mapped_Groups as $group){
					$event_groups_array['id'] = $group['id'];
				    $event_groups_array['title'] = trim(strip_tags(trim(clearString(htmlspecialchars_decode($group['title'])))));
				    $groups[] = $event_groups_array;
		} 
		if( $groups ) {
		   sort($groups);
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
										  <th scope="row" colspan="9"> 
										  <font color="red"><center>No data found.</center></font> 
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
					     $last_name = $getCustomer['memberdata']['last_name'];
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
										  <td>'.$arr['email'].'</td>
										  <td>'.$this->ticekt_type($arr['placement_id']).'</td>
										  <td> Internet</td>
					                      <td> '.$arr['ticket_category'].' </td>
										  <td> '.$arr['ticket_row'].' </td>
										  <td> '.$arr['seat_sequence'].' </td>
										</tr>'; 
				}
				
			}
		}
		
		
		$params = array( 'title' => 'Productor Dashboard',
		                 'current_url' => $request->getUri()->getPath(),
						 'groups' => $groups,
						 'event_group_id' => $event_group_id,
						 'event_id' => $event_id,
						 'options' => $events_list,
						 'total_seats' => $total_seats,
						 'total_booked_seats' => $total_booked_seats,
						 'total_sale' => $total_sale,
						 'orderReportList' => $orderReportList,
						 'event_name' => $event_name
						 );
        return $this->render($response, ADMIN_VIEW.'/ProductorArea/pr_dashboard.twig',$params);
	 }
	 
	 // Get my all event groups
	 public function getMyEvents(){
		 
		   
	 }
	 
	 // Get group events list
	public function myEvents($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		 // Get all the events which are sold
		 $mapped_eventGroups = Models\Event::where('eventgroup_id', '=', $id)->orderBy('title', 'ASC')->get();
		 $result = '';
		 foreach($mapped_eventGroups as $row){ 
			 $id = $row['id'];
			 $title = trim(strip_tags(trim(clearString(htmlspecialchars_decode($row['title'])))));
			 $result .= '<option value="'.$id.'">'.$title.'</option>';
		 }
		$option = '<option value="">Select Event</option>';
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
			$reportTable = 	'<h1><center>Sale Report of '.$event_name.'</center></h1>';	
			$reportTable .= '<table class="table table-striped m-table table-bordered">
						  <thead>
							<tr>
							  <th>Nom</th>
							  <th>Prénom</th>
							  <th>Téléphone</th>
							  <th>Mail</th>
							  <th>Ticket Type</th>
							  <th> Source </th>
							  <th> Catégorie  </th>
							  <th> Rang </th>
							  <th> Siège </th>
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
										  <th scope="row" colspan="9" class="error"> 
										  <font color="red"><center>No data found.</center></font> 
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
					     $last_name = $getCustomer['memberdata']['last_name'];
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
				  
				  sort($dumpData);
				 
				foreach($dumpData as $arr){
				 $reportTable .= '<tr>
					                      <td> '.$arr['last_name'].' </td>
										  <td>'.$arr['first_name'].'</td>
										  <td>'.$arr['telephone'].'</td>
										  <td>'.$arr['email'].'</td>
										  <td>'.$this->ticekt_type($arr['placement_id']).'</td>
										  <td> Internet</td>
					                      <td> '.$arr['ticket_category'].' </td>
										  <td> '.$arr['ticket_row'].' </td>
										  <td> '.$arr['seat_sequence'].' </td>
										</tr>'; 
				}
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
	
	
	
	
}
