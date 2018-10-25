<?php
namespace App\Controllers;

use App\Models;
use App\Tools\Auth;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

use Dompdf\Dompdf;

/**
   Admin Operator Dashboard Controller
   Available Functions.
   1. dashboard
   
   
*/

class AdminOperatorDashboardController extends Base 
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
		 $operator_id = $_SESSION['adminId']; // Operator ID
		 /* ------------    START STATISTICS SECTION 1 -----------------------------*/
		$all_events_count = $all_seats_count = $all_sale_count = 0;
		$this_month_sale_amount = $last_month_sale_amount = $last_two_months_sale_amount = 0;
		$this_month_events = $last_month_events = $last_two_months_events = 0;
		/* ------------    START STATISTICS SECTION 1 -----------------------------*/
		
		 $total_seats = $total_sold_seats = $total_sale = 0;
		 // Get all those seat rows where operator is = $operator_id
		 $operatorSeatsList = Models\RowSeats::selectRaw('seat_from,seat_to,event_seat_categories_id')->
		                         where('operator_id', '=', $operator_id)
								->groupBy('event_seat_categories_id')->get();
		 
		 
		if( !$operatorSeatsList->isEmpty() ){
			 $vent_seat_cats_ids_list = '';
			 foreach($operatorSeatsList as $rs){
				 $all_seats_count += sizeof(range($rs['seat_from'],$rs['seat_to']));
				 $vent_seat_cats_ids_list .= $rs['event_seat_categories_id'].',';
			 }
			
			 $event_cat_ids_list = explode(',', $vent_seat_cats_ids_list);
			 // Now get all the Events
			$op_events = Models\EventSeatCategories::
			                     select ('*')
								->whereIn('id', $event_cat_ids_list)
								->groupBy('event_id')
								->get();
								
								
						  
			if( !$op_events->isEmpty() ){
			  $evnt_ids_list = '';
			  foreach($op_events as $rr){
			     $evnt_ids_list .= $rr->event_id.',';	
			  }
			  // Get all events 
			  $myEventsList = Models\Event::whereIn('id', explode(',', rtrim($evnt_ids_list, ',') ))->get();
			  // Find event counts
			  $all_events_count = Models\EventSeatCategories::
								whereIn('id', $event_cat_ids_list)
								->groupBy('event_id')
								->count();
			}
		}
		
							  					  
		/* ------------    END STATISTICS SECTION 1 -------------------------------*/
		
		
		
		
		$params = array( 'title' => $this->lang['operator_txt'] . ' '. $this->lang['left_menu_dashboard_txt'],
		                 'current_url' => $request->getUri()->getPath(),
						 'total_seats' => $total_seats,
						 'total_sold_seats' => $total_sold_seats,
						 'total_sale' => $total_sale,
						 /* Statistics Section data */
						 'all_events_count' => $all_events_count,
						 'all_seats_count' => $all_seats_count,
						 'all_sale_count' => $all_sale_count,
						 
						 );
        return $this->render($response, ADMIN_VIEW.'/OperatorArea/op_dashboard.twig',$params);
	 }
	 
	 
	 
	 
	
	
	
	// This month
	public function this_month_range(){
	  $first_day_this_month = date('Y-m-01'); // hard-coded '01' for first day
      $last_day_this_month  = date('Y-m-t');
      return array($first_day_this_month,
					 $last_day_this_month);	
	}
	
	// Last Month
	public function last_month_range(){
	  $first_day_this_month = date('Y-m-01', strtotime('-1 month')); // hard-coded '01' for first day
      $last_day_this_month  = date('Y-m-t', strtotime('-1 month')); 
      return array($first_day_this_month,
					 $last_day_this_month);	
	}
	
	// Last Two Months
	public function last_two_month_range(){
	  $first_day_this_month = date('Y-m-01', strtotime('-2 month')); // hard-coded '01' for first day
      $last_day_this_month  = date('Y-m-t', strtotime('-2 month')); 
      return array($first_day_this_month,
					 $last_day_this_month);	
	}
	
	
	
	
	// Update logged in user profile
	public function getProfile($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];
		
        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		 // Get data of the user
		$loggedInUser = Models\Operators::find($id);
		
		if ($loggedInUser) {
            echo json_encode($loggedInUser);
        }
  }
  
    // Update logged in user
	public function updateProfile($request, $response){
	   $isError = false;
	   $op_id   = $request->getParam('id');
	   $op_fullname   = $request->getParam('op_fullname');
	   $op_fname   = $request->getParam('op_fname');
	   $op_lname = $request->getParam('op_lname');
	   $op_email = $request->getParam('op_email');
	   $op_phone = $request->getParam('op_phone');
	   $op_emailExist = Models\Operators::where('op_email', '=', $op_email)->where('op_id', '!=', $op_id)->first();
	   if(empty($op_fullname)){
		   $isError = true;
		   echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter your full name'));
		   exit();
	   }elseif(empty($op_fname)){
		   $isError = true;
		   echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter your first name'));
		   exit();
	   }elseif(empty($op_lname)){
		   $isError = true;
		   echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter your last name'));
		   exit();
	   }else  if( empty($op_email) ){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                    'message' => 'Please enter your email'));
		 exit();
	   }else  if(!isValidEmail($op_email)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                    'message' => 'Please enter valid email.'));
		 exit();
	   }else if(isValidEmail($op_email) && $op_emailExist){
		   $isError = true;
		    echo json_encode(array("status" => 'duplicate', 
		                  'message' => 'Email (<strong>'.$op_email. '</strong>) already exist.'));
		   exit();	   
	   }else{
		   $isError = false;   
	   }
	   
	   
	   if(!$isError ){
		   // update to users table
		   $data = array('op_fullname' => $op_fullname,
		                 'op_fname' => $op_fname,
						 'op_lname' => $op_lname,
						 'op_email' => $op_email,
						 'op_phone' => $op_phone);		 
		   $opUpdate = Models\Operators::where('op_id', '=', $op_id)->update($data);
		   // Now get this user latest updated data and put in session
		   $operator = Models\Operators::where('op_id', '=', $op_id)->first();
		  
		   // Overwrite session values here
			$_SESSION['isAdmin'] = 'Okay';
			$_SESSION['adminId'] = $operator->op_id;
			$_SESSION['adminName'] = $operator->op_fullname;
			$_SESSION['adminEmail'] = $operator->op_email;
			$_SESSION['isOperator'] = 'Okay';
			
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	   }
	}
	
	// Update logged in user password
	public function changePassword($request, $response){
	   $isError = false;
	   $id   = $request->getParam('id');
	   $current_pass   = $request->getParam('current_pass');
	   $new_pass   = $request->getParam('new_pass');
	   $new_pass_confirm = $request->getParam('new_pass_confirm');
	   if(empty($current_pass)){
		   $isError = true;
		   echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter your current password.'));
		   exit();
	   }elseif(empty($new_pass)){
		   $isError = true;
		   echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter your new password.'));
		   exit();
	   }else  if( empty($new_pass_confirm) ){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                    'message' => 'Please confirm your new password.'));
		 exit();
	   }else if($new_pass <>  $new_pass_confirm){
		   $isError = true;
		    echo json_encode(array("status" => 'error', 
		                  'message' => 'New and confirm password must be same.'));
		   exit();	   
	   }else{
		   $AuthObj = new  Auth();
		   $auth = $AuthObj->checkOperatorPassword($id,$current_pass);
		   if($auth == true ) {
			   // update to operatos table
			   $new_password = $AuthObj->changePassword($new_pass_confirm);
			   $data = array('password' => $new_password);	 
			   $opUser = Models\Operators::where('op_id', '=', $id)->update($data);
			    return $response
					->withHeader('Content-type','application/json')
					->write(json_encode(array('status' => TRUE)));
		   }else{
		     echo json_encode(array("status" => 'error', 
		                           'message' => 'Current password did not found in the system.'));
		      exit();	  
		   }
	   }
	   
	}






} // End of class
