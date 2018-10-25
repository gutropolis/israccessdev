<?php
namespace App\Controllers;

use App\Models;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;
use Dompdf\Dompdf; // Include for downloading PDF
/**
  Admin order Controller
  Available Functions.
  1. orders
  2. getAjaxOrdersList
  3. getOrderById
  4. deleteOrderById
*/

class AdminOrderController extends Base 
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
		error_reporting(0);
		$this->container = $container;
		$this->servername = $this->container['settings']['database']['host'];
		$this->username = $this->container['settings']['database']['username'];
		$this->password = $this->container['settings']['database']['password'];
		$this->dbname = $this->container['settings']['database']['database'];
		$this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
		$this->lang =  $this->container->view['adminLang'];
	}
	
	 // Main function to display all orders list
	public function orders($request, $response) {
		
		$event_group_id_val = $event_id_val = $category_id_val = $row_id_val = '';
		$events = $seat_categories = $category_rows = array();
		// Get all Event Groups 
		 $mapped_Groups = Models\Eventgroup::where('status', '=', 1)->orderBy('title',  'ASC')->get();
		 $event_groups_array = array();
		 foreach($mapped_Groups as $group){
			$event_groups_array['id'] = $group['id'];
			$event_groups_array['title'] = trim(strip_tags(trim(clearString(htmlspecialchars_decode($group['title'])))));
			$groups[] = $event_groups_array;
		}
		
		if( isset($_GET['event_group_id']) && !empty($_GET['event_group_id']) ) {
			if( isset($_GET['event_group_id']) && !empty($_GET['event_group_id']) ) {
				if( isset($_GET['event_group_id']) /*&& !empty($_GET['event_group_id'])*/ ) {
					if( isset($_GET['event_group_id']) /*&& !empty($_GET['event_group_id'])*/ ) {
						$event_group_id_val = $_GET['event_group_id'];
						$event_id_val = $_GET['event_id'];
						// Get all events of the selected event group 
						 $mapped_events = Models\Event::with('city')->
										    where('eventgroup_id', '=', $event_group_id_val)->
										    orderBy('title', 'ASC')->
										    get();
						$event_groups_array = array();					
						 foreach($mapped_events as $rowEE){ 
								$id = $rowEE['id'];
								$title = trim(strip_tags(trim(clearString(htmlspecialchars_decode($rowEE['title'])))));
								$city = $rowEE['city']['name'];
								$date = hr_date($rowEE['date']);
								$title = $title. ' ['.$city.'] ['.$date.']';
								$event_groups_array['id'] = $id;
								$event_groups_array['title'] = $title;
								$events[] = $event_groups_array;
							}
							
							// Get all events of the selected event group 
							 $mapped_eventsCats = Models\EventSeatCategories::
												where('event_id', '=', $event_id_val)->
												orderBy('seat_category', 'ASC')->
												get();
							$event_Cats = array();					
							 foreach($mapped_eventsCats as $rowC){ 
									$event_Cats['id'] = $rowC['id'];
									$event_Cats['title'] = $rowC['seat_category'];
								    $seat_categories[] = $event_Cats;
							}
							
							// Get all events of the selected event group 
							 $mapped_eventsCats = Models\RowSeats::
												where('event_seat_categories_id', '=', $category_id_val)->
												orderBy('row_number', 'ASC')->
												get();
							  $event_RR = array();					
							 foreach($mapped_eventsCats as $rowR){ 
									$event_RR['id'] = $rowR['id'];
									$event_RR['title'] = $rowR['row_number'];
								    $category_rows[] = $event_RR;
							}
							
						}
					}
				}
				//exit;
		}else{
			$event_group_id_val = $event_id_val = $category_id_val = $row_id_val = '';
			$events = $seat_categories = $category_rows = array();
		}
		// Set order_filtered
		if(isset($_GET['search_keyword']) && !empty($_GET['search_keyword']) ){
			$order_filtered = 'Y';
			$search_keyword = $_GET['search_keyword'];
		}else{
		    $order_filtered = 'N';
			$search_keyword = '';
		}
        $params = array( 'title' => $this->lang['order_all_txt'],
		                 'current_url' => $request->getUri()->getPath(),
						 'event_groups' => $groups,
						 'events' => $events,
						 'seat_categories' => $seat_categories,
						 'category_rows' => $category_rows,
						 'order_filtered' => $order_filtered,
						 'search_keyword' => $search_keyword,
						 'event_group_id_val' => $event_group_id_val,
						 'event_id_val' => $event_id_val,
						 'category_id_val' => $category_id_val,
						 'row_id_val' => $row_id_val);
					 
        return $this->render($response, ADMIN_VIEW.'/Order/orders.twig',$params);
		
		
    }
	
	// Ajax Orders list 
	public function getAjaxOrdersList($request, $response){
		$isFiltered = false;
		$event_id_val = $request->getParam('post_data')['event_id'];	
		if(isset($event_id_val) && !empty($event_id_val) ){
			        $event_id = $event_id_val;
					$isFiltered = true;
		}else{
		    // Get this month orders
			$isFiltered = false;
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
				 $prefix = ''; // check prefix
			 foreach($fields as $field){
			    if(isset($request->getParam('query')['generalSearch'])) {
				     $conditions[] = "$field LIKE '%" . ($request->getParam('query')['generalSearch']) . "%'";
				  }
			   }
			 }
		 }
		 // Set the query to empty
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
		
		if($isFiltered){
			// Check if the category id is not empty
			$category_condition = '';
			// Check if the row id is not empty
			$row_condition = '';
			$sqlCountQuery = "SELECT count(OI.id) as cnt FROM `orders` AS O INNER JOIN `orderitems` AS OI 
			               ON O.`id` = OI.`order_id` INNER JOIN `users` AS U ON O.`customer_id` = U.`id` 
						   WHERE OI.product_id=".$event_id." 
						   $category_condition
						   $row_condition
						   ORDER BY O.`id`";
			if ($resultCount = mysqli_query($this->conn,$sqlCountQuery))
			  {
				  $rowDD = mysqli_fetch_assoc($resultCount);
				  $total   = $rowDD['cnt']; // get count  
			  }
		    
		}else{
			$total   = Models\Order::get()->count(); // get count 
		}
		
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
		if($isFiltered){
			// Check if the category is is not empty
			$category_condition = '';
			// check if the row id is not empty
			$row_condition = '';
			// Get data query
			 $sqlQuery = "SELECT 
						  U.name AS customer_name,
						  O.*,
						  OI.event_ticket_category_id,
						  OI.ticket_row_id,
						  OI.seat_sequence 
						FROM
						  `orders` AS O 
						  INNER JOIN `orderitems` AS OI 
							ON O.`id` = OI.`order_id` 
						  INNER JOIN `users` AS U 
							ON O.`customer_id` = U.`id`
							WHERE OI.product_id=".$event_id." 
							$category_condition
							$row_condition
						ORDER BY O.`id` DESC
						LIMIT ".($offset*$perPageLimit).", ".$perPageLimit." ";
			//echo $sqlQuery;
			if ($result_data = mysqli_query($this->conn,$sqlQuery))
			  {
				  $data = array();
			     while($get = mysqli_fetch_object($result_data)){
					 $array_data = array();
					$array_data['id']  = $get->id;
					// Get Seat Sequence here
					$orderitem = Models\OrderItems::where('order_id', $get->id)->where('type_product', 'event')->get();
					$array_data['customer_name']  = $get->customer_name; // Customer Name
					$array_data['total_amount']  = $get->total_amount; // Total Amount
					$array_data['payment_type']  = $get->payment_type; // Payment Type
					$array_data['seat_category']  = $orderitem[0]->ticket_category; // Ticket Category
					$array_data['seat_row']  = $orderitem[0]->ticket_row; // Ticket Row
					$array_data['seat_sequence']  = $orderitem[0]->seat_sequence; // seat sequence
					$array_data['created_on']  =  hr_date($get->created_on); // created on data
					$array_data['customer_id']  = $get->customer_id; // customer id
					$data[] = $array_data;
				 }
			}
				   
		}else{
			// Get the orders list using the Customer relationship defined in Order Model
			$orders_list = Models\Order::with(['Customer'])
			                   ->skip($offset*$perPageLimit)->take($perPageLimit)
							   ->orderBy($field, $sort)->get();
		    $data = array();
			foreach($orders_list as $get){
				$array_data = array();
				$array_data['id']  = $get->id;
				// Get Seat Sequence here
				$orderitem = Models\OrderItems::where('order_id', $get->id)->where('type_product', 'event')->get();
				$array_data['customer_name']  = $get['Customer']['name'];
				$array_data['total_amount']  = $get->total_amount;
				$array_data['payment_type']  = $get->payment_type;
				$array_data['seat_category']  = $orderitem[0]->ticket_category;
				$array_data['seat_row']  = $orderitem[0]->ticket_row;
				$array_data['seat_sequence']  = $orderitem[0]->seat_sequence;
				$array_data['created_on']  =  hr_date($get->created_on);
				$array_data['customer_id']  = $get['Customer']['id'];
				$data[] = $array_data;
			}					   
		}
			
		// Prepare the necessary params	
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
	
	// Ajax Orders Filtered list 
	public function getAjaxOrdersListFilter($request, $response){
		
		$isFiltered = false;
		$search_keyword = $request->getParam('post_data')['search_keyword'];	
						
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
		 // Set the query to empty
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
		
			$sqlCountQuery = "SELECT COUNT(O.id ) as cnt FROM `orders` AS O INNER JOIN  `users` AS U ON O.`customer_id` = U.`id` 
						   WHERE U.name LIKE '%".$search_keyword."%'
                           
						   ORDER BY O.`id`";
			if ($resultCount = mysqli_query($this->conn,$sqlCountQuery))
			  {
				  $rowDD = mysqli_fetch_assoc($resultCount);
				  $total   = $rowDD['cnt']; // get count  
			  }else{
				$total = 0;   
			  }
		    		
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
		
			 $sqlQuery = "SELECT 
						  U.name AS customer_name,
						  O.*,
						  OI.event_ticket_category_id,
						  OI.ticket_row_id,
						  OI.seat_sequence 
						FROM
						  `orders` AS O 
						  INNER JOIN `orderitems` AS OI 
							ON O.`id` = OI.`order_id` 
						  INNER JOIN `users` AS U 
							ON O.`customer_id` = U.`id`
							WHERE U.name LIKE '%".$search_keyword."%'
                            GROUP BY O.id
						ORDER BY O.`id` DESC
						LIMIT ".($offset*$perPageLimit).", ".$perPageLimit." ";
			//echo $sqlQuery;
			if ($result_data = mysqli_query($this->conn,$sqlQuery))
			  {
				  $data = array();
			     while($get = mysqli_fetch_object($result_data)){
					 $array_data = array();
					$array_data['id']  = $get->id;
					// Get Seat Sequence here
					$orderitem = Models\OrderItems::where('order_id', $get->id)->where('type_product', 'event')->get();
					$array_data['customer_name']  = $get->customer_name; // Customer Name
					$array_data['total_amount']  = $get->total_amount; // Total Amount
					$array_data['payment_type']  = $get->payment_type; // Payment Type
					$array_data['seat_category']  = $orderitem[0]->ticket_category; // Ticket Category
					$array_data['seat_row']  = $orderitem[0]->ticket_row; // Ticket Row
					$array_data['seat_sequence']  = $orderitem[0]->seat_sequence; // seat sequence
					$array_data['created_on']  =  hr_date($get->created_on); // created on data
					$array_data['customer_id']  = $get->customer_id; // customer id
					$data[] = $array_data;
				 }
			}
				   
		
		// Prepare the necessary params	
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
	
	
	// Get Order by id
	public function getOrderById($request, $response, $args){
		$id = $args['id'];
		
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$order = Models\Order::find($id);
		// Get customer data asssociated to this order
		$customer = Models\User::with(['customerdata'])->where('id','=',$order['customer_id'])->get();
		// Get order detail
		$order_itmes_data = Models\OrderItems::where('order_id','=',$order['id'])->get();

		$order_itmes_list  = '';
		$sub_total = 0;
		$booking_fee = 0;
		if( !$order_itmes_data->isEmpty()){
		
			foreach($order_itmes_data as $row){
				if($row['type_product'] != 'booking_fees'){
				 $sub_total += $row['price']*$row['quantity'];
				 $total_seat = $row['quantity']*$row['price'];
			     $event_name =   Models\Event::where('id', '=', $row['product_id'])->first()->title;
				 $seat_ids_sequence = $row['seat_ids_sequence'];
				 // Check if seat_ids_sequence has a comma
				 if (strpos($seat_ids_sequence, ',') !== false) {
					 $seat_ids_sequence = explode(',', $seat_ids_sequence);
					 $seat_sequence = explode(',', $row['seat_sequence']);
					 $totalSeats = count($seat_sequence);
					 $seatCounter = 1;
					 foreach($seat_ids_sequence as $key=>$seat_id){
						$sqlCat = "SELECT 
								  ESC.seat_category AS category_name,
								  ECRS.row_number,
								  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
								  C.name AS city_name 
								FROM
								  `event_seat_categories` AS ESC 
								  INNER JOIN `event_category_row_seats` AS ECRS 
									ON ESC.`id` = ECRS.`event_seat_categories_id` 
								  INNER JOIN `events` AS E 
									ON E.id = ESC.`event_id` 
								  INNER JOIN `cities` AS C 
									ON E.`city_id` = C.`id` 
								WHERE ECRS.`id` =".$seat_id."";
						$resultCat = mysqli_query($this->conn, $sqlCat);
						$seatCat = mysqli_fetch_assoc($resultCat);
						$categoryName = $seatCat['category_name'];	// Seat Category name	
						$rowNumber    = $seatCat['row_number'];
						$eventPlace    = $seatCat['city_name'];
						$eventDate    = $seatCat['event_date'];
						$order_itmes_list  .= '<tr>
									<td>'.$event_name.'<br><small><i class="fa 	fa-calendar"></i> '.$eventDate.' <i class="fa fa-map-marker"></i> '.$eventPlace.'</small></td>
									<td>'.$categoryName.'</td>
									<td>'.$rowNumber.'</td>
									<td class="text-center">'.$seat_sequence[$key].'</td>
									<td class="text-center">'.$row['price'].'</td>
									<td class="text-center">'.($seatCounter).'</td>
									<td class="text-right">'.$seatCounter*$row['price'].'</td>
								</tr>';	 
					 }
				 }else{
					  $sqlCat = "SELECT 
								  ESC.seat_category AS category_name,
								  ECRS.row_number,
								  DATE_FORMAT(E.date, '%d/%m/%Y') AS event_date,
								  C.name AS city_name 
								FROM
								  `event_seat_categories` AS ESC 
								  INNER JOIN `event_category_row_seats` AS ECRS 
									ON ESC.`id` = ECRS.`event_seat_categories_id` 
								  INNER JOIN `events` AS E 
									ON E.id = ESC.`event_id` 
								  INNER JOIN `cities` AS C 
									ON E.`city_id` = C.`id` 
								WHERE ECRS.`id` =".$seat_ids_sequence."";
						$resultCat = mysqli_query($this->conn, $sqlCat);
						$seatCat = mysqli_fetch_assoc($resultCat);
						$categoryName = $seatCat['category_name'];	// Seat Category name	
						$rowNumber    = $seatCat['row_number'];
						$eventPlace    = $seatCat['city_name'];
						$eventDate    = $seatCat['event_date'];
					 // Get the place and date 
					 $order_itmes_list  .= '<tr>
									<td>'.$event_name.'<br><small><i class="fa 	fa-calendar"></i> '.$eventDate.' <i class="fa fa-map-marker"></i> '.$eventPlace.'</small></td>
									<td>'.$categoryName.'</td>
									<td>'.$rowNumber.'</td>
									<td class="text-center">'.$row['seat_sequence'].'</td>
									<td class="text-center">'.$row['price'].'</td>
									<td class="text-center">'.$row['quantity'].'</td>
									<td class="text-right">'.$total_seat.'</td>
								</tr>';
				 }
			     
				}else{
					$booking_fee += $row['price']*$row['quantity'];
				}
			  	$producer_id = $row['producer_id'];				
			}		
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
		$customer_data = $customer_first_name. ' '. $customer[0]['name']. 
		                 '<br>'.$customer_address.' '.$customer_postcode.'<br>'.$customer_city; 
						 
		//ddump($product_meta_data); exit;
		$total = $sub_total + $booking_fee;
		//ddump($order_itmes_data); exit;
		$order_date = date('F j , Y',  strtotime($order['created_on']));
		$month_name = date('n', strtotime($order['created_on']));
		$order_date = get_month_name($month_name). date(' j , Y',  strtotime($order['created_on']));
		$order_payment_type = get_payment_method($order['payment_type']);
		
		// get next user id
        $next = Models\Order::where('id', '>', $id)->min('id');
	   
	   // get previous user id
        $previous = Models\Order::where('id', '<', $id)->max('id');
		
		
		
		// This is for the new seat management system
		$orderItmesData = Models\OrderItems::where('order_id','=',$order['id'])->whereRaw('type_product="event"')->get();
		$record_id = $orderItmesData[0]->id;
		$event_id = $orderItmesData[0]->product_id;
		$category_id = $orderItmesData[0]->event_ticket_category_id;
		$row_id = $orderItmesData[0]->ticket_row_id;
		$seat_ids_sequence = $orderItmesData[0]->seat_ids_sequence;
		// Get seat quantity
		$total_seat_quantity = $orderItmesData[0]->seat_qty;
			
		// Get all seats of this event 
		$sqlCatRows = "SELECT ESC.`event_id`, ESC.id AS category_id, ESC.seat_category, R.id as row_seat_id,
		                R.row_number FROM 
						`event_seat_categories` AS ESC INNER JOIN `row_seats` AS R 
						ON
						ESC.id=R.event_seat_categories_id
						WHERE ESC.`event_id`=".$event_id."";
		
		$resultCatRows = mysqli_query($this->conn, $sqlCatRows);
		
	    $num_rows = mysqli_num_rows($resultCatRows);
		if($num_rows > 0){
			$available_seats = '<div class="form-group m-form__group">
		                        <div class="row">
								<form id="customer_change_seats_frm" accept-charset="UTF-8">';
								
			while($row = mysqli_fetch_assoc($resultCatRows)){
				
				// These two divs are very very important
				$available_seats .= '<div class="form-group m-form__group">';
				$available_seats .= ' <div class="row" style="margin-left: -30px;margin-right: -17px;">';
				$available_seats .= '<div class="col-md-12"> 
									 <div class="row row_seat_class" >
													<div class="col-md-6">
														'.$this->lang['seat_map_category_name_txt'].' : <strong>'.$row['seat_category'].'</strong>
													</div>
													<div class="col-md-6 text-left">
														'.$this->lang['seat_map_row_txt'].' : <strong> '.$row['row_number'].'</strong>
													</div>
												</div>
									</div>';
				// Get all available seats of this category and row
				$sqlEmptySeats = "SELECT 
								  ECRS.id,
								  ECRS.seat_number,
								  ECRS.`status` 
								FROM
								  `event_category_row_seats` AS ECRS 
								WHERE ECRS.`event_id` = ".$event_id." 
								  AND ECRS.`status` <> 'B' 
								  AND ECRS.`event_seat_categories_id` = ".$row['category_id']." 
								  AND ECRS.`row_seats_id` = ".$row['row_seat_id']." ORDER BY LENGTH(ECRS.seat_number) ASC ";
				$resultEmptySeats = mysqli_query($this->conn, $sqlEmptySeats);
				$seat_num_rows = mysqli_num_rows($resultEmptySeats);
				if($seat_num_rows > 0){
					while($seat = mysqli_fetch_assoc($resultEmptySeats)){
						$available_seats .= $this->seatBox($seat['seat_number'], $seat['id'], $event_id);
					}
				}else{
				   $available_seats .= '<span class="col-md-12" style="color:red"><center>'.$this->lang['order_no_seats_txt'].'.</center></span>';
				}
				$available_seats .= '</div>';
				$available_seats .= '</div>';		
						  
			}
			$available_seats .= '<hr><div class="row" style="margin-left: 15px;margin-top: -13px;">
								   <p ><span class="error checked_counter">0</span><span  style="color:#000">/'.$total_seat_quantity.' '.$this->lang['order_seat_selected_txt'].'</span></p> 
								</div>
								<div class="row col-md-4" style="margin-left: 0px;margin-top: -6px;margin-bottom: 17px;width: 80%;">
							       <textarea class="form-control" id="m_autosize_1" rows="3" name="seat_changed_reason">Client asked</textarea>
								</div>
								<div class="form-item form-type-textfield form-item-count-checked-checkboxes">
								 <input type="hidden" name="event_id" value="'.$event_id.'">
								 <input type="hidden" name="order_id" value="'.$id.'">
								 <input type="hidden" name="customer_id" value="'.$order['customer_id'].'">
								 <input type="hidden" name="record_id"  value="'.$record_id.'">
								 <input type="hidden" name="seat_ids_sequence" value="'.$seat_ids_sequence.'">
								 <input type="hidden" id="selected_ids" name="selected_seats_ids" value="0" size="60" maxlength="100" class="form-text required selected_ids" />
								 <button type="button"  id="btn_save_seats_change" onclick="changeSeatForCustomer()" class="btn btn-info pull-right" style="margin:0 auto;float:  left;margin-left: 12px;"><i class="fa fa-save"></i> '.$this->lang['order_save_seats_txt'].' </button> 
								</div>   
							  </form> 
							</div>';
				$available_seats .= '</div>';
		}else{
			$available_seats = '';
		}
		// Get Event Date
		$event_datetime =   Models\Event::where('id', '=', $event_id)->first()->date;
		$event_date =  strtotime(date('d-m-Y',strtotime($event_datetime))); 
	    $today = strtotime(date('d-m-Y'));
		if($event_date < $today){
		    $is_event_expired = 'Y';
		}else{
			$is_event_expired = 'N';
		}
		
		$refund_section = '<div class="form-group m-form__group">
		                        <div class="row">
								  <div class="col-md-12">
								    <div class="col-md-6 pull-left"></div>
									<div class="col-md-6 pull-right">
									 <form id="customer_refund_frm" accept-charset="UTF-8" class="row" style="width:100%">
										<div class="row" style="margin-left: 0px;margin-top: -6px;margin-bottom: 17px;width: 100%;">
										   <textarea class="form-control" id="m_autosize_2" rows="3" name="order_refund_reason">Client asked to refund</textarea>
										</div>
										<div class="form-item">
										 <input type="hidden" name="event_id" value="'.$event_id.'">
										 <input type="hidden" name="order_id" value="'.$id.'">
										 <input type="hidden" name="record_id"  value="'.$record_id.'">
										 <button type="button"  id="btn_save_seats" onclick="refundOrder()" class="btn btn-info pull-right" style="margin:0 auto;float:  left;margin-left: 0px;"><i class="fa fa-save"></i> '.$this->lang['order_refund_txt'].' </button> 
										</div>   
									  </form> 
									</div>
								  </div>
							    </div>
							   </div>';
		
		// Check for the Promo Code
		$sqlCoupon = "SELECT C.discount_amount FROM `coupons` AS C LEFT JOIN
													`coupon_history` AS CH 
													ON
													C.`id`=CH.`coupon_id`
													WHERE CH.`order_id`=".$id."";
		$resultCoupon = mysqli_query($this->conn, $sqlCoupon);
		if($rowCoupon = mysqli_fetch_assoc($resultCoupon)){
		    $discount_amount = $rowCoupon['discount_amount'];	
		}else{
			$discount_amount = 0.00;
		}
		$params = array( 'title' => $this->lang['order_detail_txt'],
		                'order' => $order,
						'customer_data' => $customer_data,
						'customer_email' => $customer_first_name. ' '. $customer[0]['name'] . '<br>'.$customer[0]['email'],
						'order_date' => $order_date,
						'items_list' => $order_itmes_list,
						'sub_total' => $sub_total,
						'booking_fee' => $booking_fee,
						'total' => ($total-$discount_amount),
						'producer_data' => $product_meta_data,
						'order_id' => $id,
						'order_payment_type' => $order_payment_type,
						'customer_id' => $order['customer_id'],
						'next' => $next,
						'previous' => $previous,
						'is_event_expired' => $is_event_expired,
						'available_seats' => $available_seats,
						'refund_section' => $refund_section,
						'total_seat_quantity' => $total_seat_quantity,
						'order_status' => $order['status'],
						'discount_amount' => ($discount_amount));
						
        return $this->render($response, ADMIN_VIEW.'/Order/view.twig',$params);
		
  }
  
  // Function to display available seats
  public function seatBox($seat_number, $seat_id, $event_id){
	    $seat_Box  = '<div class="col-md-1" style="margin-left:  25px;">';
	    $seat_Box .= '<label class="m-option">';
	    $seat_Box .= '<label class="m-checkbox m-checkbox--air m-checkbox--state-brand">';
		$seat_Box .= '<input type="checkbox"  value="'.$seat_id.'" class="checkbox"  >';
		$seat_Box .= '<div class="m-demo__preview m-demo__preview--badge" style="cursor:pointer" >';
		$seat_Box .= '<span class="m-badge m-badge--success">'.$seat_number.'</span>';
		$seat_Box .= '</div>';
		$seat_Box .= '<span></span>'; 
		$seat_Box .= '</label>';
		$seat_Box .= '</div>';  
	 return $seat_Box;		
  }
  
  
	
	// Delete order by id
	public function deleteOrderById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];
        // Check validation
        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// First Delete all its order items
		//$delete = Models\Order::find($id)->delete();
		return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Function to download Order as PDF
	public function downloadOrderReportPDF($request, $response, $args){
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
				 $total_seat = $row['quantity']*$row['price'];
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
		// Set the array of the order
		$params = array( 'title' => $this->lang['order_detail_txt'],
		                'order' => $order,
						'customer_data' => $customer_data,
						'order_date' => $order_date,
						'items_list' => $order_itmes_list,
						'sub_total' => $sub_total,
						'booking_fee' => $booking_fee,
						'total' => $total,
						'producer_data' => $product_meta_data,
						'order_id' => $id);
	
	  $html = '<!doctype html>
					<html lang="en">
					<head>
				<style>
				.col-md-12 {
					flex: 0 0 100%;
					max-width: 100%;
				}
				.container {
					width: 100%;
					padding-right: 15px;
					padding-left: 15px;
					margin-right: auto;
					margin-left: auto;
				}
				.container {
					max-width: 1140px;
				}
				.m-form .m-form__section.m-form__section--first, .m-form .m-form__section:first-child {
					margin-top: 0;
				}
				.m-form .m-form__section {
					color: #7b7e8a;
				}
				.row {
					display: flex;
					flex-wrap: wrap;
					/*margin-right: -15px;
					margin-left: -15px;*/
				}
				.col-xl-12 {
					flex: 0 0 100%;
					max-width: 100%;
				}
				.m-form .m-form__section {
					margin: 40px 0 40px 0;
				}
				.m-form .m-form__section {
					color: #7b7e8a;
				}
				.m-form .m-form__section {
					font-size: 1.2rem;
					font-weight: 500;
				}
				.m-form .m-form__section {
					margin: 40px 0 40px 0;
				}
				   .invoice-title h2, .invoice-title h3 {
					display: inline-block !important;
				}			
				.table > tbody > tr > .no-line {
					border-top: none !important;
				}
				
				.table > thead > tr > .no-line {
					border-bottom: none !important;
				}
				
				.table > tbody > tr > .thick-line {
					border-top: 1px solid !important;
				}
				
				.table td, .table th {
					padding: 0.45rem !important;
					vertical-align: top !important;
					border-top: 1px solid #f4f5f8 !important;
				}
				
				.panel-default {
					border-color: #ddd;
				}
				
				.panel-default>.panel-heading {
					color: #333;
					background-color: #f5f5f5;
					border-color: #ddd;
				}
				
				.panel {
					margin-bottom: 20px;
					background-color: #fff;
					border: 1px solid #ddd;
					border-radius: 4px;
					-webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
					box-shadow: 0 1px 1px rgba(0,0,0,.05);
				}
				
				.panel-default>.panel-heading {
					color: #333;
					background-color: #f5f5f5;
					border-color: #ddd;
				}
				
				.panel-heading {
					padding: 4px 15px;
					border-bottom: 1px solid transparent;
					border-top-right-radius: 3px;
					border-top-left-radius: 3px;
					padding-top: 12px;
				}
				.panel-default>.panel-heading {
					color: #333;
					background-color: #f5f5f5;
					border-color: #ddd;
				} 
				.panel-body {
					padding: 15px;
					padding-bottom: 0px;
				} 
				.error{
				  color:#f4516c;	
				}
				b, strong {
					font-weight: 700;
				}
				/*.table-responsive {
					display: block;
					width: 100%;
					overflow-x: auto;
					-webkit-overflow-scrolling: touch;
					-ms-overflow-style: -ms-autohiding-scrollbar;
				}*/
				.table {
					width: 100%;
					max-width: 100%;
					margin-bottom: 1rem;
					background-color: transparent;
				}
				
				table {
					border-collapse: collapse;
				}
				
				.col-md-6 {
					flex: 0 0 50%;
					max-width: 50%;
				}
				
				.pull-right {
					float: right;
				}
				
				.text-right {
					text-align: right!important;
				}
				
				.h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
					margin-bottom: .5rem;
					font-family: inherit;
					font-weight: 500;
					line-height: 1.2;
					color: inherit;
				}
				
				.panel-default>.panel-heading {
					color: #333;
					background-color: #f5f5f5;
					border-color: #ddd;
				}
				
				.panel-heading {
					padding: 1px 15px;
					border-bottom: 1px solid transparent;
					border-top-right-radius: 3px;
					border-top-left-radius: 3px;
				}
				.panel {
					margin-bottom: 20px;
					background-color: #fff;
					border: 1px solid #ddd;
					border-radius: 4px;
					-webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
					box-shadow: 0 1px 1px rgba(0,0,0,.05);
				}
				.invoice-title h2, .invoice-title h3 {
					display: inline-block;
				}
		    </style>
			</head>			  
			<body>
			<!-- BEGIN: Subheader -->
			<!-- END: Subheader -->
			<div class="col-md-12">
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
				  <h3 class="panel-title" style="text-align:left"><strong>'.$this->lang['dashboard_order_summary_txt'].'</strong></h3>
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
			</div>
			<!--end::Portlet--> 
			<!--begin::Portlet--> 
			<!--end::Portlet--> 
			</body>
			</html>';
			
			//echo $html; exit;
			
			
			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml($html);
			
			// (Optional) Setup the paper size and orientation
			//$dompdf->setPaper('A4', 'landscape');
			$dompdf->setPaper('A4');
			
			// Render the HTML as PDF
			$dompdf->render();
			
			// Output the generated PDF to Browser
			$filename = 'orderReport_'.$order['invoice_number'].'_'.time();
			$dompdf->stream($filename.".pdf");
			exit; // This is very important for downloading the PDF				
		
	}
	
	
	// Get group events list
	public function getEventsList($request, $response, $args){
		$id = $args['id']; // Event Group ID
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
	
	// Get event seat categories list
	public function getEventCategoriesList($request, $response, $args){
		$id = $args['id']; // Event ID
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		 // Get all the events which are sold
		 $mapped_eventCats = Models\EventSeatCategories::
										    where('event_id', '=', $id)->
										    orderBy('seat_category', 'ASC')->
										    get();
		 $result = '';
		 foreach($mapped_eventCats as $row){ 
			 $id = $row['id'];
			 $title = trim($row['seat_category']);
			 $result .= '<option value="'.$id.'">'.$title.'</option>';
		 }
		$option = '<option value="">Select Category</option>';
		$option .= $result;
		echo $option;
		
	}
	
	// Get event seat category rows list
	public function getEventCategoryRowsList($request, $response, $args){
		$id = $args['id']; // Event Seat Category ID
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		 // Get all the events which are sold
		 $mapped_eventCatsRows = Models\RowSeats::
										    where('event_seat_categories_id', '=', $id)->
										    orderBy('row_number', 'ASC')->
										    get();
		 $result = '';
		 foreach($mapped_eventCatsRows as $row){ 
			 $id = $row['id'];
			 $title = trim($row['row_number']);
			 $result .= '<option value="'.$id.'">'.$title.'</option>';
		 }
		$option = '<option value="">Select Row</option>';
		$option .= $result;
		echo $option;
		
	}
	
	// Function to download the Event Order CSV
	public function downloadEventCSV($request, $response, $args){
		
		$id = $args['id']; // Event ID
		
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		
		$sqlQuery = "SELECT C.id AS categoryid,C.event_id,C.seat_category,R.id AS rowid,O.id AS order_id ,
					O.id AS order_id ,R.row_number,O.seat_from,O.seat_to,O.seat_sequence,Um.first_name,
					U.name,U.email,Um.id AS customer_id
					FROM orders ORD INNER JOIN
					orderitems O   ON    Ord.id=O.order_id
					INNER JOIN row_seats R  ON O.ticket_row_id =R.id 
					INNER JOIN event_seat_categories  C ON R.event_seat_categories_id =C.id
					LEFT JOIN user_meta Um ON Ord.customer_id=Um.user_id
					LEFT JOIN users U ON Um.user_id=U.id
					WHERE C.event_id='".$id."'";

		if ($resultData = mysqli_query($this->conn,$sqlQuery))
		 {
			ob_end_clean();
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment;filename='.time().'.csv');
			$fp = fopen('php://output', 'w');
			// output data of each row
			$titles = array('Category ID',
							'Event ID',
							'Seat Category',
							'Row ID',
							'Order ID',
							'Row Number',
							'Seat From',
							'Seat To',
							'Seat Sequence',
							'First Name',
							'Name',
							'Email Address',
							'Customer ID'
							);
			// This line is very very important to overcome the issue of French Characters in the CSV			
			$titles = array_map("utf8_decode", $titles);			
			fputcsv($fp, $titles);
		  // Fetch one and one row
		  while ($row = mysqli_fetch_assoc($resultData))
		  {
			   $data = array($row['categoryid'], 
						  $row['event_id'],
						  $row['seat_category'],
						  $row['rowid'],
						  $row['order_id'],
						  $row['row_number'],
						  $row['seat_from'],
						  $row['seat_to'],
						  $row['seat_sequence'],
						  $row['first_name'],
						  $row['name'],
						  $row['email'],
						  $row['customer_id']
						  );
			// Put the data array to the make its CSV Columns
			$data = array_map("utf8_decode", $data);		
			fputcsv($fp, $data);
		  }
		  fclose($fp);
		  $contLength = ob_get_length();
		  header( 'Content-Length: '.$contLength);
		}
		
	}
	
	// Change Seats Options
	public function getEventSeatCategoriesList($request, $response, $args){
		$order_id = $args['order_id']; // Get the requested order id
		// Get Event ID from the order items tables
		$eventId = Models\OrderItems::where('order_id', '=', $order_id)->whereRaw('type_product="event"')->first()->product_id;
		// Now get all seat categories
		$seatCategories = Models\EventSeatCategories::where('event_id', '=', $eventId)
		                  ->groupBy('id')->orderBy('seat_category', 'ASC')->get();
		$availableCategories = '';
		foreach($seatCategories as $seatCat){
		$availableCategories .= '<h3 class="popup_heading">Seat Category: '.$seatCat['seat_category'].'</h3>
							  <div class="m-scrollable" data-scrollbar-shown="true" data-scrollable="true" data-max-height="200">
							  <table id="seat_changed">
							  <tr>
								<th width="126" style="text-align:left">Select Category</th>
								<th width="150" style="text-align:left">
								<select class="form-control" onChange="getCategorySeats(this)" >
								<option value="">N/A</option>';
								foreach($seatCategories as $seatCat){
								 $availableCategories .= '<option value="'.$seatCat['id'].'">'.$seatCat['seat_category'].'</option>';
								}
							    $availableCategories .= '</select> 
								</th>
							  </tr>
							  <tbody id="seat_changed_data">
							  </tbody>
							</table>
							</div>';
		}
							
		return json_encode(array('html' => $availableCategories));					
	}
	
	// Get available Seats
	public function getAvailableSeats($request, $response, $args){
	    $seatsList  = '<div class="form-group m-form__group">';
	    $seatsList .= '<div class="row">';
		$seat_number = 10; 
		$seatCircle  = '<div class="col-md-1" style="margin-left:  25px;">';
		$seatCircle .= '<label class="m-option">';
		$seatCircle .= '<label class="m-checkbox">';
		$seatCircle .= '<input type="checkbox">';
		$seatCircle .= '<div class="m-demo__preview m-demo__preview--badge" style="cursor:pointer" >';
		$seatCircle .= '<span class="m-badge m-badge--success">'.$seat_number.'</span>';
		$seatCircle .= '</div>';
		$seatCircle .= '<span></span>'; // This is important for the checkbox 
		$seatCircle .= '</label>';
		$seatCircle .= '</label>';
		$seatCircle .= '</div>';
		$seatsList .= '</div>';
		$seatsList .= '</div>';	
		return $seatsList;
	}
	
	// Change Seat
	public function changeSeats($request, $response, $args){
		// Event id in the order items table with the name of product_id
       $event_id =  $request->getParam('event_id'); 	
	   // Current order id in the order items table which needs to be evaluated
	   $order_id =  $request->getParam('order_id'); 
	   // Customer id of the order who has booked this order	
	   $customer_id =  $request->getParam('customer_id'); 
	   	// Primary key of the order items table
	   $record_id =  $request->getParam('record_id'); 
	    // This is the current seat ids sequence of the current order which will be replace with new id sequence
	   $oldSeatIds =  $request->getParam('seat_ids_sequence'); 	
	    // these are new seat ids which needs to be replaced
	   $newSeatIds =  $request->getParam('selected_seats_ids'); 	
	   // Get the changed reason
	   $seat_changed_reason =  $request->getParam('seat_changed_reason'); 	
	   // Check if there is a comma 
	   if (strpos($_REQUEST['seat_ids_sequence'], ',') !== false) {
		     $selectedIds = explode(',',$newSeatIds);
			 $oldSeatIds = explode(',', $oldSeatIds);
			 $newSeatIds = explode(',', $newSeatIds);
			 foreach($newSeatIds as $key => $NewID){
				 $old_seat_id = $oldSeatIds[$key];
				 $new_seat_id = $NewID;
				 $this->changeReservedSeat($old_seat_id, $new_seat_id, $customer_id, $seat_changed_reason);
			 }
		}else{
				$selectedIds = $newSeatIds;
				$old_seat_id = $oldSeatIds;
				$new_seat_id = $newSeatIds;
				$this->changeReservedSeat($old_seat_id, $new_seat_id, $customer_id, $seat_changed_reason);
		}
		
		// First get all the new seat(s)
		$newSeatsList = Models\EventCategoryRowSeat::whereIn('id', $selectedIds)->
		                selectRaw('GROUP_CONCAT(seat_number) as seat_sequence')->
						selectRaw('GROUP_CONCAT(id) as seat_sequence_ids')->
						get();
		$seat_sequence = $newSeatsList[0]->seat_sequence;	
		$seat_ids_sequence = $newSeatsList[0]->seat_sequence_ids;	
				
	   // Now replace the seats in the orderitems
	   $newSeatsArray = array('seat_sequence' => $seat_sequence, 'seat_ids_sequence' => $seat_ids_sequence);
	   $updateNewData = Models\OrderItems::where('id', '=', $record_id)->
			                                                update($newSeatsArray);	
															
	  // Now release the old seats
	  
	   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	  													
	}
	
	// Change the seat
	public function changeReservedSeat($old_seat_id, $new_seat_id, $customer_id, $seat_changed_reason){
		     // First Book the new seat
			 $newSeatData = Models\EventCategoryRowSeat::where('id', $new_seat_id)->first();
			 
			 $udpateNewSeat = array('customer_id' => $customer_id, 
			                        'status' => 'B', 
									'booked_datetime' => date('Y-m-d H:i:s'));
			 // Update data in the new table
			 $updateSeatTable = Models\EventCategoryRowSeat::where('id', '=', $new_seat_id)->
			                                                update($udpateNewSeat);	
			// Now release the old seat
			$updateOldSeat = array('customer_id' => 0,
			                       'status' => 'R');																	
			// Update data in the new table and make this seat release
			$updateSeatTable = Models\EventCategoryRowSeat::where('id', '=', $old_seat_id)->
			                                                update($updateOldSeat);	
															
			// Now Get the Old Seat data
			$oldSeatData = Models\EventCategoryRowSeat::where('id', $old_seat_id)->first();												
			// Now Keep the log history
			 $keepLog = new Models\SeatLogHistory;
			 $keepLog->seat_id = $old_seat_id; // Old Seat ID
		     $keepLog->customer_id = $customer_id;
		     $keepLog->seat_number = $newSeatData['seat_number'];
			 $keepLog->changed_by_id = $_SESSION['adminId']; // The logged in user ID
			 $keepLog->changed_returned_date = date('Y-m-d H:i:s');
			 $keepLog->changed_returned_reason = $seat_changed_reason;
			 $keepLog->log_type = 'Changed';
			 $keepLog->save();
	}
	
	
	
	// Refund order
	public function refundOrder($request, $response){
       // Refund Reason
       $refund_reason = $request->getParam('order_refund_reason');
	   $event_id      = $request->getParam('event_id');
	   $order_id      = $request->getParam('order_id');
	   $record_id     = $request->getParam('record_id'); // order items table Primary Key
	   // Get seat sequence from the order items
	   $seatData = Models\OrderItems::where('id', '=', $record_id)->get();
	   $seat_sequence = $seatData[0]->seat_sequence;
	   $seat_ids_sequence = $seatData[0]->seat_ids_sequence;
	   
	   // Get order table data
	   $order = Models\Order::find($order_id);
	   $customer_id = $order['customer_id']; // Get the customer id
	   
	   // Check if there is a comma 
	   if (strpos($seat_ids_sequence, ',') !== false) {
		    $seat_ids_sequence = explode(',', $seat_ids_sequence);
			 foreach($seat_ids_sequence as $key => $seatId){
				 $this->makeSeatAvailable($seatId,$customer_id,$refund_reason);
			 }
		}else{
				$seatId = $seat_ids_sequence;
				$this->makeSeatAvailable($seatId,$customer_id,$refund_reason);
		}
		
		
		// Now update the order table
		$updateOrder = array('status' => 'R');
		$updateOrderTable = Models\Order::where('id', '=', $order_id)->
			                              update($updateOrder);
		 return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));								  
	   
	}
	
	// Make the seat Available for the next reservation
	public function makeSeatAvailable($seatId,$customer_id,$refund_reason){
	   $seatId = $seatId; // Seat ID
	   $updateSeatArray = array('customer_id' => 0, 'status' => 'R');	
	   $updateSeatsTable = Models\EventCategoryRowSeat::where('id', '=', $seatId)->
			                                            update($updateSeatArray);
	   // Get Seat Number
	   $seat_number = Models\EventCategoryRowSeat::where('id', $seatId)->first()->seat_number;	
	   // Now Keep the log history
	  $keepLog = new Models\SeatLogHistory;
	  $keepLog->seat_id = $seatId; // Old Seat ID
	  $keepLog->customer_id = $customer_id;
	  $keepLog->seat_number = $seat_number;
	  $keepLog->changed_by_id = $_SESSION['adminId']; // The logged in user ID
	  $keepLog->changed_returned_date = date('Y-m-d H:i:s');
	  $keepLog->changed_returned_reason = $refund_reason;
	  $keepLog->log_type = 'Refunded';
	  $keepLog->save();	
																
	}
	
	
	
	
}
