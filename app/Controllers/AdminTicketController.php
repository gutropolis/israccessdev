<?php
namespace App\Controllers;

use App\Models;
use App\Middleware\Auth;
use App\Middleware\RouteMiddleware; 
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

/**
  Admin Ticket Controller
  Available Functions.
 
  
*/
class AdminTicketController extends Base 
{
	
	protected $container;
	protected $lang;
	// Class constructor
	public function __construct($container)
	{
		$this->container = $container;
		$this->lang =  $this->container->view['adminLang'];
	}
	
	 // Main function to display all tickets list
	public function tickets($request, $response) {
		
		
        $params = array( 'title' => $this->lang['left_menu_ticket_txt'],
		                  'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Tickets/tickets.twig',$params);
    }
	
	// Get ajax Tickets List
	public function getAjaxTicketsList($request, $response){
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
		
		if($isSearched){
		    $total   = Models\EventSeatCategories::with(['event'])->groupBy('event_id')->whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\EventSeatCategories::with(['event'])->groupBy('event_id')->count(); // get count 
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
		if($isSearched){
		    $seats_list = Models\EventSeatCategories::with(['event'])->groupBy('event_id')->whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			$seats_list = Models\EventSeatCategories::with(['event'])->groupBy('event_id')->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		
		$data = array();
		foreach($seats_list as $get){
			
		  	$array_data = array();
			$event_data = Models\Event::with(['city', 'auditorium'])->where('id', '=', $get['event_id'])->get();
			if( $event_data->isEmpty() ){
				$city_name = '';
				$auditorium_name = '';
			}else{
			  $city_name =  $event_data[0]['city']['name'];
			  $auditorium_name = $event_data[0]['auditorium']['name'];
			}
			
			$array_data['id']  = $get['event_id'];
            $array_data['event_name']  = htmlspecialchars_decode(strip_tags($get['event']['title']));
			$array_data['date']  = hr_date($get['event']['date']);
			$array_data['city_name']  = $city_name;
			$array_data['auditorium_name']  = $auditorium_name;
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
	
	
	// Get ticket type
	public function ticket_type($type=''){
		$ticket_type_name = '';
	  	if( isset($type) && !empty($type) ){
			if($type == 1){
				$ticket_type_name = 'Standard';
			}else if($type == 2){
				$ticket_type_name = 'Réservées';
			}else if($type == 3){
				$ticket_type_name = 'Invitations';
			}else if($type == 4){
				$ticket_type_name = 'Vendues à autre opérateur';
			}
		}else{
		  $ticket_type_name = '';	
		}
		return $ticket_type_name;
	}
	
	// Function to view ticket history of the row
	public function getTicketById($request, $response, $args){
		$id = $args['id']; // Event id
		
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		
		// Free Ticket
		$free_ticket = 'default';
		// Reserved Ticket
		$reserved_ticket = 'success';
		// Returned Ticket
		$retured_ticket = 'warning';
		
		
		// Get event data
		$event = Models\Event::find($id);
		
		if($event['seats_on_map'] == 'Y'){
			return $this->response->withHeader('Location', base_url.'/admin/events/groups/edit/'. $event['event_group_id']);
			exit;
		}else{
			
		$event_date =  strtotime(date('d-m-Y',strtotime($event['date']))); 
	    $today = strtotime(date('d-m-Y'));
		if($event_date < $today){
			
		    $is_expired = 'Y';
			$disabled = 'disabled="disabled"';
			
		}else{
			$is_expired = 'N';
			$disabled = '';
			
			
		}
         $seats_map = Models\EventSeatCategories::where('event_id','=', $id)->get();
		 
		if( $seats_map->isEmpty() ){
			$seats_list = '';
			$seats_booked_list = '';
			$seat_order = 1; 
		}else{
			$seats_list = '';
			$seats_booked_list = '';
			$i=0;
			 foreach($seats_map as $key=>$row){
				$placement = $row['placement'];
			    $counterRow = explode(',',$row['from_range']);
			    $onlick = 'removeAudSeatTicket('.$row['id'].')';
				if($row['libres']==1)
				{
					$checked = 'Yes';
				}
				else
				{
					$checked = 'No';
				}
				$seats_list .= '<table class="table table-sm m-table m-table--head-bg-brand">
									<thead class="thead-inverse">
												<tr>
													<th>
														Category Name : '.$row['seat_category'].'
													</th>
													<th>
														Seat From : '.$row['seat_row_from'].'
													</th>
													<th>
														Seat To : '.$row['seat_row_to'].'
													</th>
													<th>
														Libres : '.$checked.'
													</th>
													<th>
														Price : '.$row['category_price'].'
													</th>
												</tr>
							  </thead>
				          </table>';
			    $seats_list .= '<div id="aud_seats_div_data_'.$row['id'].'" class="col-md-12">';
				
			
			 foreach($counterRow as $keyInner=>$val){
				  $counter = time().$i.get_random_int();
				  //$key = time().get_random_int();
				  $table_id = time();
				$seats_list .= '<div class="row_new_'.($key.$val).'" id="div_row_rm_'.$table_id.'">';
				$seats_list .= '<div class="row" style="margin-bottom:20px !important">';
				$seats_list .= '<div class="col-md-2">&nbsp;</div>';
				$seats_list .= '<div class="col-md-2" style="margin-top:14px;">Row &nbsp;&nbsp; '.$counterRow[$keyInner].'</div>';
				$seats_list .= '<div class="col-md-8" style="margin-top:5px">';
						  
				$seat_rows = Models\RowSeats::where('event_seat_categories_id','=', $row['id'])
				                              ->where('row_number','=', $counterRow[$keyInner])->get();
				
					//echo 'Row #'.$counterRow[$keyInner].'<br>';	
				$seats_list .= '<table class="table table-bordered table-hover" id="tableAddRow_'.$table_id.'">';
				$seats_list .= '<thead>';
                $seats_list .= '<tr>';
				$seats_list .= '<th class="text-left">Placement </th>';
                $seats_list .= '<th class="text-left">From </th>';
                $seats_list .= '<th class="text-left">To</th>';
                $seats_list .= '<th class="text-left">Even Order?</th>';
                $seats_list .= '</tr>';
                $seats_list .= '</thead>';	
				$seats_list .= '<tbody>';	
				$j=0;				  
				 foreach($seat_rows as $innerKeyInner=>$seatRow){
					 $seat_from = $seatRow['seat_from'];
					 $seat_to = $seatRow['seat_to'];
					
					 
					 if($seat_from >  $seat_to){
						 $row_class = 'full_row'; 
					 }else{
						$row_class = ''; 
					 }
					$seat_order = $seatRow['seat_order'];
				    $placement = $seatRow['placement'];
					$ticket_row_id = $seatRow['id'];
					$operator = $seatRow['operator_id'];
					$seats_list .= '<tr id="tr_num_row_'.$seatRow['id'].'" class="'.$row_class.'">';
					$seats_list .= '<td>';
					
					//ob_start();
					if ($placement == 1){
						$seats_list .= 'Standard'; 
					}else if ($placement == 2) {
					    $seats_list .= 'Réservées';  
				    }else if ($placement == 3){ 
					     $seats_list .= 'Invitations'; 
			        }else if ($placement == 4){ 
					     $seats_list .= 'Vendues à autre opérateur'; 
			         }else{
						$seats_list .= ''; 
					 }
					 $range_seats = range($seat_from, $seat_to);
					 
					
					
					//$seats_list .= '<pre>';
					//$seats_list .= print_r($booked_seats);
					//$seats_list .= print_r($diff);
					//$seats_list .= ob_get_clean();
					//$seats_list .= $placement_txt;
					$seats_list .=' </td>';
					$seats_list .='<td>'.$seatRow['seat_from'].'';
					$seats_list .= '</td>';
					$seats_list .= '<td>';
					$seats_list .= ''.$seatRow['seat_to'].'';
					$seats_list .= '</td>';
					$seats_list .= '<td>';
					$checked = '';
					 if($seat_order == 2){
						 $checked = 'Yes';
					 }else{
						 $checked = 'No'; 
					 }
					$seats_list .= ' '.$checked.'';
					//$seats_list .= print_r($range_seats);
					$seats_list .= '</td>';
					
					$seats_list .= '</tr>';
					
					
					$j++;
					
					
				 } 
				  $seats_list .= '</tbody>';
				  $seats_list .= '</table>';
				  // Display list of tickets here
				  $seat_rows_list = Models\RowSeats::where('event_seat_categories_id','=', $row['id'])
				                              ->where('row_number','=', $counterRow[$keyInner])->get();
				 $seats_booked_list = '';				 
				  foreach($seat_rows_list as $seat_row){
					   $range_seats = range($seat_row['seat_from'], $seat_row['seat_to']); 
					   $event_seat_category_id = $seat_row['event_seat_categories_id'];
					   $row_number = $seat_row['row_number'];
					   $row_id   = $seat_row['id'];
					   // Get all sold tickets of this ticket row
					    $soldTicket = Models\OrderItems::where('product_id', '=', $id)->
					                                where('event_ticket_category_id', '=', $event_seat_category_id)
													->get();
						if($soldTicket->isEmpty() ){
							
						}else{
							$bookedSeatsArray = '';
							foreach($soldTicket as $bookedSeat){
								$bookedSeatsArray = $bookedSeat['seat_sequence'].',';
							}
							$bookedSeatsArray = explode(',', rtrim($bookedSeatsArray, ','));
							
						}
					   //$range_seats = print_r($range_seats);
					 $seats_list .= '<div class="m-demo__preview m-demo__preview--badge">';
					 foreach($range_seats as $range_seat){
						 if( !empty($bookedSeatsArray) ){
							  if(in_array($range_seat, $bookedSeatsArray) ){
								   $ticket_class = 'success';
							  }else{
								  $ticket_class = 'default';
							  }
						 }else{
							  $ticket_class = 'default';
						 }
						   $seats_list .= '<span class="m-badge m-badge--'.$ticket_class.'">'.$range_seat.'</span>';
						    //$seats_list .= $event_seat_category_id .' - '.$row_number .' - '.$row_id;
					 }
					 
					 $seats_list .= '</div>';
				  }
				  
				  //$seats_list .=  $seats_booked_list;
				  
				  $seats_list .= '</div>';
				  $seats_list .= '</div>';
				  $seats_list .= '</div>';
			 }
				  
			$seats_list .= '</div>';
		  }
			
			
			
		 }
		}
		
		
		
		
		// Get the order items data
		//$order_itmes_data = Models\OrderItems::where('order_id','=',$order['id'])->get();
		
		
		$params = array('title' => 'Event Ticket Detail',
		                'seats_list' => $seats_list
		                );
		 return $this->render($response, ADMIN_VIEW.'/Tickets/view.twig',$params);
	}
	
	
	
}
