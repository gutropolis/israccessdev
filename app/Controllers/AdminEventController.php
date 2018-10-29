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
  Admin Event Controller
  CRUDs for Events
  Available Functions
  1. events
  2. getAjaxEventsList
  3. saveEvent
  4. updateEvent
  5. getEventById
  6. deleteEventPictureById
  7. deleteEventById
  8. editEventById
  9. updateEventStatus
  10. uploadEventImages
  11.  eventsDontMiss
  12. ajaxDontMissEventsList
  13. eventsOfDay
  14. ajaxEventsOfDayList
  15. saveEventMultipleTimes
  16. deleteEventTimeById
  17. saveEventMultipleTickets
  18. saveEventRoles
  19. deleteEventTicketById
  20. deleteEventRoleById
  21. saveAudEventSeats
  22. makeJsonForFront
  23. saveEventSeatTicketsData
  24. updateEventSeatTicketsData
  25. eventMapPage
  26. getAjaxEventMapList
  27. eventMapAdd
  28. eventMapEdit
  29. saveEventSeatTicketMap
  30. updateEventSeatTicketMap
  31. deleteEventSeatById
  32. deleteEventSeatRowById
  33. deleteRowSeatById
  34. updateRowSeatsTable
  35. saveNewRowSeatsTable
  36. saveRowSeatsTable
  
*/

class AdminEventController extends Base 
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
	
	 // Main function
	public function events($request, $response) {
        $params = array( 'title' => 'All Events',
		                 'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Event/events.twig',$params);
    }
	
	// Ajax Events list
	public function getAjaxEventsList($request, $response){
		$event_group_id = $request->getParam('post_data')['event_group_id'];
		$isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('title', 'date');
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
		    $total   = Models\Event::with(['city', 'auditorium'])->where('eventgroup_id', '=', $event_group_id)->whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\Event::with(['city', 'auditorium'])->where('eventgroup_id', '=', $event_group_id)->count(); // get count 
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
		    $events_of_day_list = Models\Event::with(['city', 'auditorium'])->where('eventgroup_id', '=', $event_group_id)
			->whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			
			$events_of_day_list = Models\Event::with(['city', 'auditorium'])->where('eventgroup_id', '=', $event_group_id)
			->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		
		$data = array();
		foreach($events_of_day_list as $get){
			$event_group_title = ($get['eventgroup']['title'] == '') ? 'click to edit' : $get['eventgroup']['title'];
            
		  	$array_data = array();
			$title = ($get['title'] == '') ? 'click to edit' : $get['title'];
			$array_data['id']  = $get['id'];
			if($_SESSION['is_event_edit'] == 'Y'){
              $array_data['title']  = '<a href="javascript:void(0);" title="Edit Event" onclick="edit_event('.$get['id'].')">'.$title.'</a>';
			}else{
			  $array_data['title']  = '<a href="javascript:void(0);" title="Edit Event" >'.$title.'</a>';
			}
			$array_data['date']  = hr_date($get['date']);
			$array_data['city_name']  = $get['city']['name'];
			$array_data['auditorium_name']  = $get['auditorium']['name'];
			/*$array_data['group_name']  =  '<a href="javascript:void(0);" title="View Event Group" onclick="edit('.$get['eventgroup']['id'].')">'.$event_group_title.'</a>';*/
			$array_data['status']  = $get['status'];
			$array_data['seats_on_map']  = $get['seats_on_map'];
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
	
	// Save Event
	public function saveEvent($request, $response){
	   $isError = false;
	   $section =  $request->getParam('section'); 	 
	   $eventgroup_id =  $request->getParam('eventgroup_id'); 	 
	   $title_event =  $request->getParam('title_event');
	   $date_event =  $request->getParam('date_event');
	   $city_id =  $request->getParam('city_id');
	   $auditorium_id =  $request->getParam('auditorium_id');
	   $description =  $request->getParam('description');
	   $artist_name =  $request->getParam('artist_name');
	   $author_name =  $request->getParam('author_name');
	   $productor_name =  $request->getParam('productor_name');
	   $director_name =  $request->getParam('director_name');
	   $contributor_name =  $request->getParam('contributor_name');
	   $contributor_description =  $request->getParam('contributor_description');
	   $show_in_section =  $request->getParam('show_in_section');
	   $display_order =  $request->getParam('display_order');
	   $seats_on_map = $request->getParam('seats_on_map');
	   $status = $request->getParam('status');
	   $booking_fee = $request->getParam('booking_fee');
	   $event_ticket_type = $request->getParam('event_ticket_type');
	   $commission_fee = $request->getParam('commission_fee');
	   $eventExist = Models\Event::where('title', '=', $title_event)->first();
	    if(empty($title_event)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event title.'));
		 exit();	   
		 
	   }else if(empty($city_id)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event city.'));
		 exit();	   
	   }else if(empty($auditorium_id)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event auditorium.'));
		 exit();	   
	   }else{
		  $isError = false;
	   }

	   if( !$isError ){
		   $display_order = ($display_order == '') ? 0 : $display_order;
		   $booking_fee = ($booking_fee == '') ? 0 : $booking_fee;
		   $commission_fee = ($commission_fee == '') ? 0.00 : $commission_fee;
		  // Save to events table
		   $event = new Models\Event;
		   $event->title = $title_event;
		   $event->date = mysql_date($date_event);
		   $event->created_at = date('Y-m-d H:i:s');
		   $event->updated_at = date('Y-m-d H:i:s');
		   $event->eventgroup_id = $eventgroup_id;
		   $event->city_id = $city_id;
		   $event->auditorium_id = $auditorium_id;
		   $event->artist_name = $artist_name;
		   $event->author_name = $author_name;
		   $event->productor_name = $productor_name;
		   $event->director_name = $director_name;
		   $event->description = htmlspecialchars($description);
		   $event->contributor_name = $contributor_name;
		   $event->contributor_description = htmlspecialchars($contributor_description);
		   $event->status = $status;
		   $event->section = $section;
		   $event->seats_on_map = $seats_on_map;
		   $event->event_ticket_type = $event_ticket_type;
		   $event->booking_fee = $booking_fee;
		   $event->display_order = $display_order;
		   $event->adv_image = $this->uploadEventAds();
		   $event->commission_fee = $commission_fee;


		   $event->save();	
		   
		   $event_id = $event->id; // Event id after save
		   if($event_id)
		   $this->uploadEventImages($event_id); // Upload event images
		   $this->saveEventMultipleTimes($event_id); // Save multiple event timings
		   $this->saveEventMultipleTickets($event_id, $seats_on_map);  // Save multiple event tickets
		   $this->saveEventRoles($event_id); // Save to event roles table
		   $this->saveAudEventSeats($event_id, $auditorium_id, $seats_on_map); // Save auditorium manual seats.

		   	if($event->seats_on_map == 'Y'){

			   	//load auditorium skeletton from auditorium 
			   		$map = Models\Auditorium::where('id', $auditorium_id)->first()->auditorium_map;

			   		// Save the Digital Map
			   		$event->auditorium_seats_map = $map;   		

	                $ev_aud_map = Models\EventAuditoriumMap::where('event_id', $id)->first()->id;

	                if($ev_aud_map){
	                    
	                }else {

	                    $new_ev_aud_map = new Models\EventAuditoriumMap();
	                    $new_ev_aud_map->event_id= $event_id;
	                    $new_ev_aud_map->auditorium_map = $map;
	                    $new_ev_aud_map->save();
	                }


			   		$digitalMapTablePKID = Models\EventAuditoriumMap::where('event_id', '=', $event_id)->first()->id;

			   		if($digitalMapTablePKID){

			   			$event = Models\EventAuditoriumMap::where('id', '=', $digitalMapTablePKID)->update(array('auditorium_key' => '', 'auditorium_map' =>$map));

			   		}else {

			   		   $aud = new Models\EventAuditoriumMap;

					   $aud->event_id = $event_id;

					   $aud->auditorium_key = '';

				       $aud->auditorium_map  = $map;

					   $aud->save();
			   		}
			   	}


			  
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 
	   }
	}
	
	
	
	// Update Event
	public function updateEvent($request, $response){
		
		 $isError = false;
	   $id =  $request->getParam('id_event_edit'); 	
	   $eventgroup_id = $request->getParam('eventgroup_id_edit'); 	
	   $section =  $request->getParam('section'); 	
	   $title_event =  $request->getParam('title_event');
	   $date_event =  $request->getParam('date_event');
	   $city_id =  $request->getParam('city_id');
	   $auditorium_id =  $request->getParam('auditorium_id');
	   $description =  $request->getParam('description');
	   $artist_name =  $request->getParam('artist_name');
	   $author_name =  $request->getParam('author_name');
	   $productor_name =  $request->getParam('productor_name');
	   $director_name =  $request->getParam('director_name');
	   $contributor_name =  $request->getParam('contributor_name');
	   $contributor_description =  $request->getParam('contributor_description');
	   $show_in_section =  $request->getParam('show_in_section');
	   $display_order =  $request->getParam('display_order');
	   $seats_on_map = $request->getParam('seats_on_map_edit');
	   $status = $request->getParam('status');
	   $booking_fee = $request->getParam('booking_fee');
	   $event_ticket_type = $request->getParam('event_ticket_type');
	   $commission_fee = $request->getParam('commission_fee');
	   
	   $eventExist = Models\Event::where('title', '=', $title_event)->where('id', '!=', $id)->first();
	    if(empty($title_event)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event title.'));
		 exit();	   
	   }else if(empty($city_id)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event city.'));
		 exit();	   
	   }else if(empty($auditorium_id)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event auditorium.'));
		 exit();	   
	   }else{
		  $isError = false;
	   }
	   
	   if( !$isError ){
		   $display_order = ($display_order == '') ? 0 : $display_order;
		   $booking_fee = ($booking_fee == '') ? 0 : $booking_fee;
		   $commission_fee = ($commission_fee == '') ? 0.00 : $commission_fee;
		  // update events table

		    if($seats_on_map == 'Y'){
                //load auditorium skeletton from auditorium 
                $map = Models\Auditorium::where('id', $auditorium_id)->first()->auditorium_map;
               //update event_auditorium_map table
                Models\EventAuditoriumMap::where('event_id', $id)->update(array('auditorium_map' => $map));

		  	    $data = array('title' => $title_event,
		                   'date' => mysql_date($date_event),
		                   'updated_at' => date('Y-m-d H:i:s'),
						   'eventgroup_id' => $eventgroup_id,
						   'city_id' => $city_id,
						   'auditorium_id' => $auditorium_id,
						   'artist_name' => $artist_name,
						   'author_name' => $author_name,
						   'productor_name' => $productor_name,
						   'director_name' => $director_name,
						   'description' => $description,
						   'contributor_name' => $contributor_name,
						   'contributor_description' => htmlspecialchars($contributor_description),
						   'section' => $section,
						   'status' => $status,
						   'event_ticket_type' => $event_ticket_type,
						   'seats_on_map' => $seats_on_map,
						   'booking_fee' => $booking_fee,
						   'display_order' => $display_order,
						   'adv_image' => $this->uploadEventAdsUpdate(),
						   'commission_fee' => $commission_fee,
		  	    		   'auditorium_seats_map' => $map );
			}else {
					$data = array('title' => $title_event,
		                   'date' => mysql_date($date_event),
		                   'updated_at' => date('Y-m-d H:i:s'),
						   'eventgroup_id' => $eventgroup_id,
						   'city_id' => $city_id,
						   'auditorium_id' => $auditorium_id,
						   'artist_name' => $artist_name,
						   'author_name' => $author_name,
						   'productor_name' => $productor_name,
						   'director_name' => $director_name,
						   'description' => $description,
						   'contributor_name' => $contributor_name,
						   'contributor_description' => htmlspecialchars($contributor_description),
						   'section' => $section,
						   'status' => $status,
						   'event_ticket_type' => $event_ticket_type,
						   'seats_on_map' => $seats_on_map,
						   'booking_fee' => $booking_fee,
						   'display_order' => $display_order,
						   'adv_image' => $this->uploadEventAdsUpdate(),
						   'commission_fee' => $commission_fee);

		}

		   $event = Models\Event::where('id', '=', $id)->update($data);	
		   
		   $event_id = $id; // Event id 
		   if($event_id)
		   $this->uploadEventImages($event_id); // Upload event images
		   $this->saveEventMultipleTimes($event_id); // Save multiple event timings
		   $this->saveEventMultipleTickets($event_id, $seats_on_map);  // Save multiple event tickets
		   $this->saveEventRoles($event_id); // Save to event roles table
		   $this->saveAudEventSeats($event_id, $auditorium_id, $seats_on_map); // Save auditorium manual seats.
		   
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 
	   }
	}
	
	// Get Event  by id
	public function getEventById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$event = Models\Event::with(['city', 'auditorium'])->find($id);
		$event_pics = Models\Eventpicture::where('event_id', '=', $id)->get();
		$event_times = Models\EventTime::where('event_id', '=', $id)->get();
		$event_tickets = Models\EventTicket::where('event_id', '=', $id)->get();
		$event_roles = Models\EventRole::where('event_id', '=', $id)->get();
		if ($event) {
			$event['auditorium_name'] = $event['auditorium']['name'];
			$event['city_name'] =  $event['city']['name'];
			$event['section'] =   '';
			$event['date_e'] = hr_date($event['date']);
			$event['file_web_path'] = EVENT_ADS_WEB_PATH;
			$event['event_adv_img'] =   $event['adv_image'];
			
			//$event['description'] = html_entity_decode(htmlspecialchars_decode($event['description']), ENT_NOQUOTES); 
			$event['description'] = htmlspecialchars_decode($event['description']); 
			$event['status'] =  ($event['status'] == 1) ? 'Active' : 'Inactive';
			$images_list = array();
			if( !empty($event_pics) ){
				$path = EVENT_WEB_PATH.'/';
				foreach($event_pics as $row){
				  	$images_list[] = '<li class="hide_li_'.$row['id'].'"><img src="'.$path.$row['event_img'].'" class="img-thumbnail img-responsive inline-block" alt="Responsive image" height="200px" width="200px" style="height:200px" /><a  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill " onclick="remove_pic('.$row['id'].')" style="color:#fff"><i class="la la-trash-o"></i> Delete </a></li>';
				}
				$event['pics'] =  $images_list;
			}else{
			     $event['pics'] =  'No picture found.';
			}
			$timesArray = array();
			if( !empty($event_times) ){
				$i=0;
				 foreach($event_times as $time){
					 $timesArray[] = '<tr id="eventTime_'.$time['id'].'"><td class="col-sm-4" style="width: 39% !important;text-align:  left;padding-top: 20px;">Event Time</td><td class="col-sm-3"><input type="text" class="form-control time_picker m_inputmask_time_o" disabled id="event_time_'.$i.'" value="'.hrTime($time['event_time']).'" /></td><td><a  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill " onclick="remove_Time('.$time['id'].')" style="color:#fff"><i class="la la-trash-o"></i> Delete </a></td></tr>';
				$i++; }
			}
			
			$event['times'] =  $timesArray;
			
			// tickets Array
			$ticketsArray = array();
			if( !empty($event_tickets) ){
				$i=0;
				 foreach($event_tickets as $ticket){
					 $ticketsArray[] = '<tr id="eventTicket_'.$ticket['id'].'"><td class="col-sm-4" style="width: 20% !important;text-align:  left;padding-top: 20px;">Event Tickets</td><td  style="width:40%"><input type="text" class="form-control"  value="'.$ticket['ticket_type'].'" id="ticket_type_'.$ticket['id'].'"  disabled /></td><td  style="width: 20% !important;"><input type="text" class="form-control"  id="per_ticket_price_'.$ticket['id'].'"  value="'.$ticket['per_ticket_price'].'" disabled /></td><td  style="width:  20% !important;"><input type="text" class="form-control"  id="total_quantity_'.$ticket['id'].'"  value="'.$ticket['total_quantity'].'" disabled /></td><td><a  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill " onclick="remove_Ticket('.$ticket['id'].')" style="color:#fff"><i class="la la-trash-o"></i> Delete </a></td></tr>';
				$i++; }
			}
			// Roles Array
			$rolesArray = array();
			if( !empty($event_roles) ){
				$i=0;
				 foreach($event_roles as $role){
					 $rolesArray[] = '<tr id="eventRole_'.$role['id'].'"><td  style="width:40%">'.$role['role_label'].'</td><td  style="width: 80% !important;">'.$role['role_name'].'</td><td><a  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill " onclick="remove_Role('.$role['id'].')" style="color:#fff"><i class="la la-trash-o"></i> Delete </a></td></tr>';
				$i++; }
			}
			$event['tickets'] =  $ticketsArray;
			$event['roles'] =  $rolesArray;
            echo json_encode($event);
        }	
	}
	
	// Delete Event Picture
	public function deleteEventPictureById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// get this image name
		$pictureExist = Models\Eventpicture::where('id', '=', $id)->first()->event_img;
	   if($pictureExist){
		   // Unlink the picture
		   @unlink(EVENT_ROOT_PATH.'/'.$pictureExist);
	   }
		$delete = Models\Eventpicture::find($id)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Delete Event By Id
	public function deleteEventById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];
        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		/*
		// get this image name
		$eventPictures = Models\Eventpicture::where('event_id', '=', $id)->get();
	   if( !empty($eventPictures) ){
		   foreach($eventPictures  as $row):
		     // Unlink the picture
		     @unlink(EVENT_ROOT_PATH.'/'.$row['event_img']);
		   endforeach;
		  // Now Delete from Event Images
		   $delete = Models\Eventpicture::where('event_id', '=', $id)->delete();
	   }
		$delete = Models\Event::find($id)->delete();
		*/
		$data = array('status' => 2);
		$event = Models\Event::where('id', '=', $id)->update($data);	
	}
	
	// Get Event  by id for Edit
	public function editEventById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$auditoriums =  Models\Auditorium::orderBy('name','ASC')->get();
		$cities_list =  Models\City::orderBy('name','ASC')->where('status', '=' ,1)->get();
		$event = Models\Event::with(['city', 'auditorium'])->find($id);
		$event_pics = Models\Eventpicture::where('event_id', '=', $id)->get();
		$event_times = Models\EventTime::where('event_id', '=', $id)->get();
		$auditorium_id = Models\Event::where('id', '=', $id)->first()->auditorium_id;
		/*$event_tickets = Models\EventSeatCategories::where('event_id', '=', $id)
		                                             ->where('auditorium_id', '=', $auditorium_id)
													 ->where('status', '=', 1)
													 ->orderBy('id', 'DESC')
													 ->get();*/
		//ddump($event_tickets); 
		$event_roles = Models\EventRole::where('event_id', '=', $id)->get();
		if ($event) {
			$event['date_e'] = hr_date($event['date']);
			$event['file_web_path'] = EVENT_ADS_WEB_PATH;
			$event['event_adv_img'] =   $event['adv_image'];
			$images_list = array();
			if( !empty($event_pics) ){
				$path = EVENT_WEB_PATH.'/';
				foreach($event_pics as $row){
				  	$images_list[] = '<li class="hide_li_'.$row['id'].'"><img src="'.$path.$row['event_img'].'" class=" img-responsive inline-block" alt="Responsive image" height="200px" width="200px" style="height:200px" /><a  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill " onclick="remove_pic('.$row['id'].')" style="color:#fff"><i class="la la-trash-o"></i> Delete </a></li>';
				}
			}
			$timesArray = array();
			if( !empty($event_times) ){
				$i=0;
				 foreach($event_times as $time){
					 $timesArray[] = '<tr id="eventTime_'.$time['id'].'"><td class="col-sm-4" style="width: 25% !important;text-align:  left;padding-top: 20px;">Event Time</td><td class="col-sm-3"><input type="text" class="form-control time_picker m_inputmask_time_o" name="event_time_old['.$i.']" id="event_time_'.$i.'" value="'.hrTime($time['event_time']).'"  placeholder="Select time" /><input type="hidden" name="event_time_old_id['.$i.']" value="'.$time['id'].'"></td><td><a  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill " onclick="remove_Time('.$time['id'].')" style="color:#fff"><i class="la la-trash-o"></i> Delete </a></td></tr>';
				$i++; }
			}
			
			/*if( $event_tickets->isEmpty() ){
				$ticketsArray[] = '<div class="row" style="padding-left:120px; color:red">No data found</div>';
			}else{
			   $ticketsArray = array();
			   $date_available = $date_expiry  = '';
				if( !empty($event_tickets) ){
					$i=0;$j=1;
					 foreach($event_tickets as $row){
						 if($i == 0){
							$date_available = hr_date($row['stock_available_date']);
							$date_expiry    = hr_date($row['stock_expiry_date']); 
						 }
						 $ticketsArray[] = '<div class="row" style="margin-top:10px">
					  <div class="col-md-1">'.$j.'</div>
					  <div class="col-md-5">'.$row['seat_category'].'</div>
					  <div class="col-md-3"><input type="text" class="form-control number_price_only" 
					  name="event_category_price[]" placeholder="Enter price" maxlength="4" value="'.$row['category_price'].'"></div>
					  <input type="hidden" name="row_id[]" value="'.$row['id'].'">
					  </div>';
					$i++; $j++;
					}
					$ticketsArray[] = '<div class="row" style="margin-top:10px">
					  <div class="col-md-3" style="margin-top:6px;">Select available date</div>
					  <div class="col-md-3"><input type="text" class="form-control m_date" name="stock_available_date" placeholder="Available" value="'.$date_available.'" ></div>
					  <div class="col-md-3" style="margin-top:6px;">Select expiry date</div>
					  <div class="col-md-3"><input type="text" class="form-control m_date" name="stock_expiry_date" placeholder="Expiry" value="'.$date_expiry.'"></div>
					  </div>';
				}else{
					$ticketsArray[] = '<div class="row" style="padding-left:120px; color:red">No data found</div>';
				}
			}*/
			// Event roles
			$rolesArray = array();
			if( !empty($event_roles) ){
				$i=0;
				 foreach($event_roles as $role){
					 $rolesArray[] = '<tr id="eventRole_'.$role['id'].'"><td  style="width:40%"><input type="text" class="form-control" required name="event_role_label_old['.$i.']" value="'.$role['role_label'].'" id="event_role_label_old_'.$i.'"  placeholder="Role label" /></td><td  style="width: 60% !important;"><input type="text" class="form-control" required name="event_role_name_old['.$i.']" id="event_role_name_'.$i.'"  value="'.$role['role_name'].'" placeholder="Role name" /></td><td><input type="hidden" name="event_role_label_old_id['.$i.']" value="'.$role['id'].'"><a  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill " onclick="remove_Role('.$role['id'].')" style="color:#fff"><i class="la la-trash-o"></i> Delete </a></td></tr>';
				$i++; }
				
			}
			$event['description'] = htmlspecialchars_decode($event['description']);
			$ticketsArray = '';
			$params = array('event' => $event,
			                'event_images' => $images_list,
							'cities_list' => $cities_list,
							'auditoriums' => $auditoriums,
							'times' => $timesArray,
							'tickets' => $ticketsArray,
							'roles' => $rolesArray);
            echo json_encode($params);
        }	
	}
	
	// Update Event status to Active or Inactive
	public function updateEventStatus($request, $response, $args){
		  $id = $args['id'];
		  $status = $args['status'];
		  $new_status = ($status == 1) ? 0 : 1;
		  $data = array('status' => $new_status);
		   $event = Models\Event::where('id', '=', $id)->update($data);	
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 	
	}

	
	// Save event images
	public function uploadEventImages($event_id){
		if(isset($_FILES) && !empty($_FILES) ) {
			$total = count($_FILES['event_img']['name']);
			$uploads_dir = EVENT_ROOT_PATH.'/';
			$validextensions = allowedExtensions(); 
			for($i=0; $i < $total; $i++) {
				//Get the temp file path
                $file = $_FILES['event_img']['tmp_name'][$i];
				if($file <> ''){
				$ext = explode('.', basename($_FILES['event_img']['name'][$i]));   // Explode file name from dot(.)
				$file_extension = end($ext); // Store extensions in the variable.
				$event_img_name = md5(uniqid()) . "." . $ext[count($ext) - 1];
				$target_path = $uploads_dir . $event_img_name;
				if(in_array($file_extension, $validextensions)) {
					list($width, $height, $type, $attr) = getimagesize($file);
					if($width>200 || $height>200)
					{
						 smart_resize_image($_FILES['event_img']['name'][$i], null,  THUMB_WEIGHT+100, THUMB_HEIGHT+100 , false , $target_path , false , false ,100 );
						 // Save to event_images
						   $event_pic = new Models\Eventpicture;
						   $event_pic->event_id = $event_id;
						   $event_pic->event_img = $event_img_name;
						   $event_pic->save();	
					}else{
					  if (move_uploaded_file($_FILES['event_img']['tmp_name'][$i], $target_path)) {
						 // Save to event_images
						   $event_pic = new Models\Eventpicture;
						   $event_pic->event_id = $event_id;
						   $event_pic->event_img = $event_img_name;
						   $event_pic->save();	
					  }
					}
				}
			}
		  }
		}	
	}
	
	
	// Save event Ads image
	public function uploadEventAds(){
		if(isset($_FILES) && !empty($_FILES) ) {
			$uploads_dir = EVENT_ADS_ROOT_PATH.'/';
			$validextensions = allowedExtensions(); 
			
				//Get the temp file path
                $file = $_FILES['event_adv_img']['tmp_name'];
				if($file <> ''){
				$ext = explode('.', basename($_FILES['event_adv_img']['name']));   // Explode file name from dot(.)
				$file_extension = end($ext); // Store extensions in the variable.
				$event_img_name = md5(uniqid()) . "." . $ext[count($ext) - 1];
				$target_path = $uploads_dir . $event_img_name;
				if(in_array($file_extension, $validextensions)) {
					  list($width, $height, $type, $attr) = getimagesize($file);
					  if (move_uploaded_file($_FILES['event_adv_img']['tmp_name'], $target_path)) {
						 // Save to event_images
						  return $event_img_name;
					  }
				}
			}
		  
		}else{
		  return '';	
		}
			
	}
	
	// Update event Ads image
	public function uploadEventAdsUpdate(){
		if(isset($_FILES) && !empty($_FILES) ) {
			$event_adv_img_old = $_REQUEST['event_adv_img_old'];
			$uploads_dir = EVENT_ADS_ROOT_PATH.'/';
			$validextensions = allowedExtensions(); 
				//Get the temp file path
                $file = $_FILES['event_adv_img']['tmp_name'];
				if($file <> ''){
				$ext = explode('.', basename($_FILES['event_adv_img']['name']));   // Explode file name from dot(.)
				$file_extension = end($ext); // Store extensions in the variable.
				$event_img_name = md5(uniqid()) . "." . $ext[count($ext) - 1];
				$target_path = $uploads_dir . $event_img_name;
				if(in_array($file_extension, $validextensions)) {
					  list($width, $height, $type, $attr) = getimagesize($file);
					  if (move_uploaded_file($_FILES['event_adv_img']['tmp_name'], $target_path)) {
						  // Delete old images
						   if($event_adv_img_old <> ''){
								 @unlink(EVENT_ADS_ROOT_PATH.'/'.$event_adv_img_old);
						   }
						   
						 // Save to event_images
						  return $event_img_name;
					  }
				}
			}else{
				return $event_adv_img_old;
			}
			
		}else{
		  return $event_adv_img_old;	
		}
			
	}
	// Dont Miss function
	public function eventsDontMiss($request, $response) {
        $params = array( 'title' => 'All Dont Miss Events',
		                 'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Event/dont_miss_events.twig',$params);
    }
	
	// Ajax Dont Miss Events list
	public function ajaxDontMissEventsList($request, $response){
		$isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('title', 'date_begin');
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
		    $total   = Models\Event::where('show_in_section','=',3)->whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\Event::where('show_in_section','=',3)->count(); // get count 
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
			
		    $events_list = Models\Event::with(['city', 'auditorium', 'eventgroup'])->where('show_in_section','=',3)
			->whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			
			$events_list = Models\Event::with(['city', 'auditorium', 'eventgroup'])->where('show_in_section','=',3)
			->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		$data = array();
		foreach($events_list as $get){
		  	$array_data = array();
			$title = ($get['title'] == '') ? 'click to edit' : $get['title'];
			$array_data['id']  = $get['id'];
            $array_data['title']  = '<a href="javascript:void(0);" title="Edit Event" onclick="edit('.$get['id'].')">'.$title.'</a>';
			$array_data['date']  = hr_date($get['date']);
			$array_data['city_name']  = $get['city']['name'];
			$array_data['auditorium_name']  = $get['category']['name'];
			$array_data['status']  = $get['status'];
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
	
	// Event of day function
	public function eventsOfDay($request, $response) {
        $params = array( 'title' => 'All Events Of Day',
		                 'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Event/events_of_day.twig',$params);
    }
	
	// Ajax Events of Day list
	public function ajaxEventsOfDayList($request, $response){
		$dateformate = strtotime(date('Y-m-d'));  
	    $today = date('Y-m-d', $dateformate);
		$From = $today." 00:00:00";
		$To   = $today." 23:59:59";
		$isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('title', 'date');
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
		    $total   = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereBetween('date', [$From, $To])->whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereBetween('date', [$From, $To])->count(); // get count 
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
		    $events_of_day_list = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereBetween('date', [$From, $To])
			->whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			
			$events_of_day_list = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereBetween('date', [$From, $To])
			->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		
		$data = array();
		foreach($events_of_day_list as $get){
			$event_group_title = ($get['eventgroup']['title'] == '') ? 'click to edit' : $get['eventgroup']['title'];
		  	$array_data = array();
			$title = ($get['title'] == '') ? 'click to edit' : $get['title'];
			$array_data['id']  = $get['id'];
            $array_data['title']  = '<a href="javascript:void(0);" title="View Event" onclick="view('.$get['id'].')">'.$title.'</a>';
			$array_data['date']  = hr_date($get['date']);
			$array_data['city_name']  = $get['city']['name'];
			$array_data['auditorium_name']  = $get['auditorium']['name'];
			$array_data['group_name']  =  '<a href="javascript:void(0);" title="View Event Group" onclick="edit('.$get['eventgroup']['id'].')">'.$event_group_title.'</a>';
			$array_data['status']  = $get['status'];
			
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
	
	// Save event multiple timings
	public function saveEventMultipleTimes($event_id){
		  if( isset($_REQUEST['event_time']) && !empty($_REQUEST['event_time']) ){
			  foreach($_REQUEST['event_time'] as $key=>$time):
			     if( !empty($time) ){
				  // Save to event_times
				    $eventTime = new Models\EventTime;
					$eventTime->event_id = $event_id;
					$eventTime->event_time = mysqlTime($time);
					$eventTime->save();
				  }
			  endforeach;
		  }
		  if( isset($_REQUEST['event_time_old']) && !empty($_REQUEST['event_time_old']) ){
			  foreach($_REQUEST['event_time_old'] as $key=>$time):
			     if( !empty($time) ){
					 $id = $_REQUEST['event_time_old_id'][$key];
				  // Update to event_times
					$data = array('event_id' => $event_id,
					             'event_time' => mysqlTime($_REQUEST['event_time_old'][$key]));
					$eventTimeEdit = Models\EventTime::where('id', '=', $id)->update($data);	
				  }
			  endforeach;
		  }
		   
	}
	
	// Delete Event Time
	public function deleteEventTimeById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$delete = Models\EventTime::find($id)->delete();		
	}
	
	// Save event multiple tickets
	public function saveEventMultipleTickets($event_id, $seats_on_map){
		// If $seats_on_map variable is Y then it means disable manual seats for this event else save manual seats
		if($seats_on_map == 'Y'){
			// It means this auditorium has map so delete its data from event_tickets as ticket map will be used for this.
			$delete = Models\EventTicket::where('event_id', '=', $event_id)->delete();
		}else{
			// As this auditorium has no map so save its tickets
		  if( isset($_REQUEST['ticket_type']) && !empty($_REQUEST['ticket_type']) ){
			  foreach($_REQUEST['ticket_type'] as $key=>$ticket):
			     if( !empty($ticket) ){
				  // Save to event_tickets
				    $eventTicket = new Models\EventTicket;
					$eventTicket->event_id = $event_id;
					$eventTicket->ticket_type = $ticket;
					$eventTicket->per_ticket_price = $_REQUEST['per_ticket_price'][$key];
					$eventTicket->total_quantity = $_REQUEST['total_quantity'][$key];
					$eventTicket->save();
				  }
			  endforeach;
		  }
		  
		   if( isset($_REQUEST['ticket_type_old']) && !empty($_REQUEST['ticket_type_old']) ){
			  foreach($_REQUEST['ticket_type_old'] as $key=>$ticket):
			     if( !empty($ticket) ){
					 $id = $_REQUEST['event_ticket_old_id'][$key];
				    // Update to event_tickets
					$data = array('event_id' => $event_id,
					             'ticket_type' => $ticket ,
								 'per_ticket_price' => $_REQUEST['per_ticket_price_old'][$key],
								 'total_quantity' => $_REQUEST['total_quantity_old'][$key] 
								 );
					$eventTicket = Models\EventTicket::where('id', '=', $id)->update($data);	
				  }
			  endforeach;
		  }
		}  
	}
	
	
	// Save event roles
	public function saveEventRoles($event_id){
			// As this auditorium has no map so save its tickets
		  if( isset($_REQUEST['event_role_label']) && !empty($_REQUEST['event_role_label']) ){
			  foreach($_REQUEST['event_role_label'] as $key=>$role):
			     if( !empty($role) ){
				  // Save to event_roles
				    $eventRole = new Models\EventRole;
					$eventRole->event_id = $event_id;
					$eventRole->role_label = $role;
					$eventRole->role_name = $_REQUEST['event_role_name'][$key];
					$eventRole->save();
					
				  }
			  endforeach;
		  }
		  
		   if( isset($_REQUEST['event_role_label_old']) && !empty($_REQUEST['event_role_label_old']) ){
			  foreach($_REQUEST['event_role_label_old'] as $key=>$role):
			     if( !empty($role) ){
					 $id = $_REQUEST['event_role_label_old_id'][$key];
				    // Update to event_roles
					$data = array('event_id' => $event_id,
					             'role_label' => $role ,
								 'role_name' => $_REQUEST['event_role_name_old'][$key] 
								 );
					$eventRole = Models\EventRole::where('id', '=', $id)->update($data);	
				  }
			  endforeach;
		  } 
	}
	
	
	// Delete Event Ticket
	public function deleteEventTicketById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$delete = Models\EventTicket::find($id)->delete();		
	}
	
	// Function Delete Event Role
	public function deleteEventRoleById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$delete = Models\EventRole::find($id)->delete();		
	}
	
	
	// save event auditorium seats tickets
	public function saveAudEventSeats($event_id, $auditorium_id, $seats_on_map){
		if($seats_on_map == 'Y'){
		  // Delete from the table
		  $delete = Models\EventSeatCategories::where('auditorium_id', '=' , $auditorium_id)
		                           ->where('event_id', '=' , $event_id)
								   ->where('status', '=' , 1)
								   ->where('auditorium_id', '=' , $auditorium_id)
								   ->delete();	
		}else{
			// First get all the data of this auditorium + this event
			$seats_map = Models\EventSeatCategories::where('auditorium_id','=', $auditorium_id)
			                                           ->where('event_id','=', $event_id)
													   ->where('status', '=' , 1)
													   ->get();
			if( $seats_map->isEmpty() ){
				 $exist = 0;
			}else{
				$exist = 1;
			}
			// Get auditorium seat tickets 
			$seats_map = Models\AuditoriumSeatCategory::where('auditorium_id','=', $auditorium_id)->get();
			
			if(isset($_REQUEST['event_category_price']) && !empty($_REQUEST['event_category_price']) ){
				  foreach($_REQUEST['event_category_price'] as $key=>$category_price){
					   $range = range($seats_map[$key]['seat_row_from'], $seats_map[$key]['seat_row_to']);
					   $json_data = $seats_map[$key]['seat_rows_json'];
					   if($exist == 0){
						   $audESC = new Models\EventSeatCategories;  
						   $audESC->event_id        = $event_id;
						   $audESC->auditorium_id   = $auditorium_id;
						   $audESC->seat_category   = $seats_map[$key]['seat_category'];
						   $audESC->seat_row_from   = $seats_map[$key]['seat_row_from'];
						   $audESC->seat_row_to     = $seats_map[$key]['seat_row_to'];
						   $audESC->seat_rows_json  = $seats_map[$key]['seat_rows_json'];
						   $audESC->category_price  = $category_price;
						   $audESC->total_qantity   = $seats_map[$key]['total_qantity'];
						   $audESC->from_range      = $seats_map[$key]['from_range'];
						   $audESC->stock_available_date = mysql_date($_REQUEST['stock_available_date']);
						   $audESC->stock_expiry_date = mysql_date($_REQUEST['stock_expiry_date']);
						   $audESC->status = 1;
						   $audESC->net_total_quantity = 0;
						   $audESC->seat_json_for_front = $this->makeJsonForFront($range,$json_data);
						   $audESC->save(); 
					   }else{
						   $id = $_REQUEST['row_id'][$key];
						   $data = array( 'category_price' => $category_price,
						                  'seat_json_for_front' => $this->makeJsonForFront($range,$json_data),
										  'stock_available_date' => mysql_date($_REQUEST['stock_available_date']),
										  'stock_expiry_date' => mysql_date($_REQUEST['stock_expiry_date']) );
						   $update = Models\EventSeatCategories::where('id', '=', $id)->update($data);
					   }
				  }
			}
		}
	}
	
  
  // Prepare data for front end
  public function makeJsonForFront($range,$json_data){
	  $unserialized = unserialize($json_data);
	  $result = array();
	  foreach($unserialized as $key=>$row){
 	          $result[$range[$key]] = array('slider_range_from_value' => $row['slider_range_from_value'],
	                                       'slider_range_to_value' => $row['slider_range_to_value'] );
      } 
     return serialize($result);
  }
  
  // Save auditorium seats tickets
	public function saveEventSeatTicketsData($event_id){
		//ddump($_REQUEST); exit;
		$created_date = date('Y-m-d H:i:s');
		if( isset($_REQUEST['seat_category']) && !empty($_REQUEST['seat_category']) ){
			  $total_qantity = 0 ;
			  $i=0;
			  $j=0;
			  foreach($_REQUEST['seat_category'] as $key=>$seat_category):
			      $category_price = $_REQUEST['category_price'][$key]; 
				  $first_char = $_REQUEST['seat_row_from'][$key]; 
				  $second_char = $_REQUEST['seat_row_to'][$key]; 
			      $seat_rows_json = array();  
				  $loop_limit = sizeof($_REQUEST['seat_category']);
				  $total_qantity = 0;
				  foreach($_REQUEST['slider_range_'.$key] as $innerKey => $slider_range_name){
					  $total_qantity += sizeOfnumbers($slider_range_name);
					  $seat_rows_json[] = array('slider_range_from_value' => rangeFrom($slider_range_name),
											   'slider_range_to_value'   => rangeTo($slider_range_name));
				  }
				   $range = range(''.$first_char.'', ''.$second_char.'');
				   $json_data = serialize($seat_rows_json);
				   $seat_order = $_REQUEST['seat_order'];
				   if(isset($seat_order) && !empty($seat_order)) {
					   $seat_order = 2;
				   }else{
					  $seat_order = 1; 
				   }
				   $auditorium_id = $_REQUEST['auditorium_id'];
				   $audSTC = new Models\EventSeatCategories;
				   $audSTC->auditorium_id = $auditorium_id;
				   $audSTC->event_id = $event_id;
				   $audSTC->seat_category = $seat_category;
				   $audSTC->seat_row_from = $first_char;
				   $audSTC->seat_row_to = $second_char;
				   $audSTC->seat_from_val = $first_char;
				   $audSTC->seat_to_val = $second_char;
				   $audSTC->seat_rows_json = $json_data;
				   $audSTC->category_price = $category_price;
				   $audSTC->total_qantity = $total_qantity;
				   $audSTC->stock_available_date = mysql_date($_REQUEST['stock_available_date']);
				   $audSTC->stock_expiry_date = mysql_date($_REQUEST['stock_expiry_date']);
				   $audSTC->from_range  = join(',',range(''.$first_char.'', ''.$second_char.''));
				   $audSTC->seat_json_for_front = $this->makeJsonForFront($range,$json_data);
				   $audSTC->seat_order = $seat_order;
				   $audSTC->created_date = $created_date;
				   $audSTC->save(); 
			  $i++;
			  endforeach;
			  
		}
	}
	
	// Update auditorium seats tickets
	public function updateEventSeatTicketsData($event_id){
		
		
		$created_date = date('Y-m-d H:i:s');
		if( isset($_REQUEST['seat_category']) && !empty($_REQUEST['seat_category']) ){
			  $total_qantity = 0 ;
			  $i=0;
			  $j=0;
			  foreach($_REQUEST['seat_category'] as $key=>$seat_category):
			      $category_price = $_REQUEST['category_price'][$key]; 
				  $first_char = $_REQUEST['seat_row_from'][$key]; 
				  $second_char = $_REQUEST['seat_row_to'][$key]; 
			      $seat_rows_json = array();  
				  $loop_limit = sizeof($_REQUEST['seat_category']);
				  $total_qantity = 0;
				  foreach($_REQUEST['slider_range_'.$key] as $innerKey => $slider_range_name){
					  $total_qantity += sizeOfnumbers($slider_range_name);
					  $seat_rows_json[] = array('slider_range_from_value' => rangeFrom($slider_range_name),
											   'slider_range_to_value'   => rangeTo($slider_range_name));
				  }
				  // range slider
				   $range = range(''.$first_char.'', ''.$second_char.'');
				   $json_data = serialize($seat_rows_json);
				   $seat_order = $_REQUEST['seat_order'];
				   if(isset($seat_order) && !empty($seat_order)) {
					   $seat_order = 2;
				   }else{
					  $seat_order = 1; 
				   }
				   // If seat order is 2 it means oven
				   if($seat_order == 2){
					   $total_qantity = intval($total_qantity/2);
				   }
				   // Get the auditorium id
				   $auditorium_id = $_REQUEST['auditorium_id'];
				   $audSTC = new Models\EventSeatCategories;
				   $audSTC->auditorium_id = $auditorium_id;
				   $audSTC->event_id = $event_id;
				   $audSTC->seat_category = $seat_category;
				   $audSTC->seat_row_from = $first_char;
				   $audSTC->seat_row_to = $second_char;
				   $audSTC->seat_rows_json = $json_data;
				   $audSTC->category_price = $category_price;
				   $audSTC->total_qantity = $total_qantity;
				   $audSTC->from_range  = join(',',range(''.$first_char.'', ''.$second_char.''));
				   $audSTC->seat_json_for_front = $this->makeJsonForFront($range,$json_data);
				   $audSTC->seat_order = $seat_order;
				   $audSTC->save(); 
			  $i++;
			  endforeach;
			  
		}
		if( isset($_REQUEST['seat_category_old']) && !empty($_REQUEST['seat_category_old']) ){
			  $total_qantity = 0 ;
			  $i=0;
			  $j=0;
			 
			  foreach($_REQUEST['seat_category_old'] as $key=>$seat_category):
			      $pk_id = $_REQUEST['audSeatRow_id'][$key];
			      $category_price = $_REQUEST['category_price_old'][$key]; 
				  $first_char = $_REQUEST['seat_row_from_old'][$key]; 
				  $second_char = $_REQUEST['seat_row_to_old'][$key]; 
			      $seat_rows_json = array();  
				  $loop_limit = sizeof($_REQUEST['seat_category_old']);
				  $total_qantity = 0;
				  foreach($_REQUEST['slider_range_old_'.$pk_id] as $innerKey => $slider_range_name){
					  $total_qantity += sizeOfnumbers($slider_range_name);
					  $seat_rows_json[] = array('slider_range_from_value' => rangeFrom($slider_range_name),
											   'slider_range_to_value'   => rangeTo($slider_range_name));
				  }
				  
				  $range = range(''.$first_char.'', ''.$second_char.'');
				   $json_data = serialize($seat_rows_json);
				   $seat_order = $_REQUEST['seat_order'];
				   if(isset($seat_order) && !empty($seat_order)) {
					   $seat_order = 2;
				   }else{
					  $seat_order = 1; 
				   }
				   if($seat_order == 2){
					   $total_qantity = intval($total_qantity/2);
				   }
				  $data = array(
				                'seat_category' => $seat_category, 
							     'seat_rows_json' => serialize($seat_rows_json),
							     'category_price' => $category_price,
							     'total_qantity' => $total_qantity,
								 'seat_json_for_front' => $this->makeJsonForFront($range,$json_data),
							     'from_range' => join(',',range(''.$first_char.'', ''.$second_char.'')),
								 'seat_order' => $seat_order
							  ); 
				   // Update Event roles			  
				   $eventRole = Models\EventSeatCategories::where('id', '=', $pk_id)->update($data);	
			  $i++;
			  endforeach;
			  
		}
	}
	
	public function eventMapPage($request, $response, $args) {
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		
        $params = array( 'title' => 'Event Manual Seats',
		                 'current_url' => $request->getUri()->getPath(),
						 'event_id' => $id);
        return $this->render($response, ADMIN_VIEW.'/Event/event_map_page.twig',$params);
    }
	// Ajax Events of Day list
	public function getAjaxEventMapList($request, $response){
		$event_id = $request->getParam('post_data')['event_id']; 
	    // Look for sorting if any 
		$sort  = !empty($request->getParam('sort')['sort']) ? $request->getParam('sort')['sort'] : 'DESC';
        $field = !empty($request->getParam('sort')['field']) ? $request->getParam('sort')['field'] : 'id';
		$page     = $request->getParam('pagination')['page'];
		if( !empty($request->getParam('pagination')['pages']) ){
		  $pages    = $request->getParam('pagination')['pages'];
		}
		$per_page = $request->getParam('pagination')['perpage'];
		
		$total   = Models\EventSeatCategories::with(['event'])->where('event_id','=', $event_id)
			->groupBy('created_date')->count(); // get count 
		
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
		 
		
			$events_map_list = Models\EventSeatCategories::with(['event'])->where('event_id','=', $event_id)
			->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)
			 ->groupBy('created_date')->get();
			 
		foreach($events_map_list as $get){
			
			$remaining_seats = $get['total_qantity'] - $get['net_total_quantity'];
		  	$array_data = array();
			$title = $get['event']['title'];
			$array_data['id']  = $get['id'];
            $array_data['title']  = '<a href="javascript:void(0);" title="View Event" onclick="view('.$get['event']['id'].')">'.$title.'</a>';
			$array_data['stock_available_date']  = hr_date($get['stock_available_date']);
			$array_data['stock_expiry_date']  = hr_date($get['stock_expiry_date']);
			$array_data['total_qantity']  = $get['total_qantity'];
			$array_data['remaining_seats'] = $remaining_seats;
			$array_data['status']  = $get['status'];
			
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
	
	// Add event map page
	public function eventMapAdd($request, $response, $args) {
		$id = $args['id']; // Event id
        $validations = [
            v::intVal()->validate($id)
        ];
		// Check for validation
        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// Get event seat category by id
		$event_data = Models\EventSeatCategories::find($id);
		
		$event = Models\Event::find($id);
		
		if($event_data['seats_on_map'] == 'Y'){
			return $this->response->withHeader('Location', base_url.'/admin/events/groups/edit/'. $event['event_group_id']);
			exit;
		 }else{
		   $params = array( 'title' => 'Add map for event',
		                    'data' => $event);
           return $this->render($response, ADMIN_VIEW.'/Event/add_map.twig',$params);
		 }
      }
	
	// Edit event map page
	public function eventMapEdit($request, $response, $args){
		$id = $args['id']; // Event id
		$event_table_pk_id = $id;
        $validations = [
            v::intVal()->validate($id)
        ];
		
        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }

		 
		//$event_data = Models\EventSeatCategories::where('event_id', '=', $id);
		
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
		 
		 $onlick_no = 'dontremoveAudSeatTicket()';

		if( $seats_map->isEmpty() ){
			$seats_list = '';
			$seat_order = 1; 
		}else{
			$seats_list = '';
			$i=0;
			 foreach($seats_map as $key=>$row){
				$placement = $row['placement'];
				$event_seat_category_id_is = $row['id'];
			    /*$explode_range = explode(',',$row['from_range']);
				$arrlength = count($explode_range);
				for($x = 0; $x < $arrlength; $x++) {
					$counterRow[] = $explode_range[$x];
					
				}*/
				$array = array_filter(array_map('trim', explode(',', $row['from_range'])));
				asort($array);
				$counterSorted = implode(',', $array);
				$counterRow = explode(',',$counterSorted);
                
			    $onlick = 'removeAudSeatTicket('.$row['id'].')';
			    $seats_list .= '<div id="aud_seats_div_data_'.$row['id'].'" class="col-md-12">';
				$seats_list .= '<div class="row" style="padding-top:10px">';
                $seats_list .= '<div class="col-md-2">'.$this->lang['seat_map_category_name_txt'].'</div>';
                $seats_list .= '<div class="col-md-2">';
                $seats_list .= '<input type="text" class="form-control" name="seat_category_old['.$key.']" id="seat_category_old_'.$key.'" placeholder="'.$this->lang['seat_map_cat_placeholder_txt'].'" value="'.$row['seat_category'].'">';
				$seats_list .= '<input type="hidden" name="seat_category_id_old['.$key.']" value="'.$row['id'].'">';
                $seats_list .= '</div>';
                $seats_list .= '<div class="col-md-2">';
                $seats_list .= '<input type="text" style="width: 128px;" maxlength="3" class="form-control"  id="seat_row_from_old_'.$key.'" placeholder="'.$this->lang['seat_map_row_placeholder_txt'].'" value="'.$row['seat_row_from'].'" disabled>';
                $seats_list .= '</div>';
                $seats_list .= '<div class="col-md-2">';
                $seats_list .= '<input type="text" style="width: 128px;" maxlength="3" class="form-control range_to"  id="seat_row_to_old_'.$key.'" placeholder="'.$this->lang['seat_map_to_placeholder_txt'].'" value="'.$row['seat_row_to'].'"  disabled>';
                $seats_list .= '</div>';
				// Check for libres
				if($row['libres']==1)
				{
					$checked = 'checked';
					$style   = 'display:block';
				}
				else
				{
					$checked = '';
					$style   = 'display:none';
				}
					
				$seats_list .= '<div class="col-md-2"><span class="libres">'.$this->lang['seat_map_libres_txt'].' </span><input class="attribute_check_old" data-id="['.$key.']" type="checkbox" '.$checked.' name="libres_old['.$key.']" value="1" id="Libres'.$key.'"> </div>'; 
				 
                $seats_list .= '<div class="col-md-2">';
				// Check if any of the seat is reserved in this category
				$orderCatIdExist = Models\OrderItems::where('event_ticket_category_id','=', $row['id'])->first()->event_ticket_category_id;
                if($orderCatIdExist){
				  $seats_list .= '<div  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill" onclick="'.$onlick_no.'"> <span> <i class="la la-trash-o"></i> <span> '.$this->lang['seat_map_delete_txt'].' </span> </span> </div>';	
				}else{
				  $seats_list .= '<div  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill" onclick="'.$onlick.'"> <span> <i class="la la-trash-o"></i> <span> '.$this->lang['seat_map_delete_txt'].' </span> </span> </div>';
				}
				$seats_list .= '</div>';
                $seats_list .= '</div>';
			 // Loop through the rows
			 foreach($counterRow as $keyInner=>$val){
				  $counter = time().$i.get_random_int();
				  //$key = time().get_random_int();
				  $table_id = time().get_random_int(3);
				$seats_list .= '<div class="row_new_'.($key.$val).'" id="div_row_rm_'.$table_id.'">';
				$seats_list .= '<div class="row" style="margin-bottom:20px !important">';
				$seats_list .= '<div class="col-md-2">&nbsp;</div>';
				$seats_list .= '<div class="col-md-2" style="margin-top:14px;">'.$this->lang['seat_map_row_txt'].' &nbsp;&nbsp; '.$counterRow[$keyInner].'</div>';
				$seats_list .= '<div class="col-md-7" style="margin-top:5px">';
						  
				$seat_rows = Models\RowSeats::where('event_seat_categories_id','=', $row['id'])
				                              ->where('row_number','=', $counterRow[$keyInner])->get();
				
					//echo 'Row #'.$counterRow[$keyInner].'<br>';	
				$seats_list .= '<table class="table table-bordered " id="tableAddRow_'.$table_id.'">';
				$seats_list .= '<thead>';
                $seats_list .= '<tr style="display:none">';
				$seats_list .= '<th class="text-left">'.$this->lang['seat_map_placement_txt'].'</th>';
                $seats_list .= '<th class="text-left">'.$this->lang['seat_map_from_txt'].' </th>';
                $seats_list .= '<th class="text-left">'.$this->lang['seat_map_to_txt'].'</th>';
                $seats_list .= '<th class="text-left">'.$this->lang['seat_map_even_order_txt'].'?</th>';
                $seats_list .= '<th style="width:10px;">';
				// Check if any of the seat is reserved in this category
				/*$orderCatIdExist = Models\OrderItems::where('event_ticket_category_id','=', $row['id'])->first()->event_ticket_category_id;
                if($orderCatIdExist){
				     $seats_list .= '<span class="la la-remove default_c" id="addBtn_'.$row['id'].'"  onclick="'.$onlick_no.'"> </span> ';	
				}else{
					 $seats_list .= '<span class="la la-remove default_c" id="addBtn_'.$row['id'].'" 
				   onclick="del_row('.$key.','.$keyInner.',\''.$val.'\','.$counter.',\''.$counterRow[$keyInner].'\','.$table_id.')"></span>';
				}*/
				$seats_list .= '<span class="fa fa-th-large" id="addBtn_'.$row['id'].'" > </span> ';	
				
			    $seats_list .= '</th>';
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
					$onclickRow = 'removeAudSeatRow('.$seatRow['id'].')';
					$seat_order = $seatRow['seat_order'];
				    $placement = $seatRow['placement'];
					$operator = $seatRow['operator_id'];
					$seats_list .= '<input type="hidden" class="row_number_old_id_new_class_'.$key.'_'.$keyInner.'" value="'.$seatRow['id'].'">';
					$seats_list .= '<input type="hidden" class="row_seat_id_old_'.$key.'_'.$keyInner.'" value="'.$seatRow['id'].'">';
					$seats_list .= '<tr id="tr_num_row_'.$seatRow['id'].'" class="'.$row_class.'" style="display:none">';
					$seats_list .= '<td>';
					$seats_list .= '<select id="Placement['.$key.']['.$keyInner.'][]" data-id="['.$key.']['.$keyInner.']['.$j.']" class="Placement form-control" name="Placement_old['.$key.']['.$keyInner.']['.$j.']" disabled>';
					ob_start();
					?>
					 <option value="1" <?php if ($placement == 1) echo 'selected' ?> >Standard</option>
					 <option value="2" <?php if ($placement == 2) echo 'selected' ?> >Réservées</option>
					 <option value="3" <?php if ($placement == 3) echo 'selected' ?> >Invitations</option>
					 <option value="4" <?php if ($placement == 4) echo 'selected' ?>>Vendues à autre opérateur</option>
					<?php
					$seats_list .= ob_get_clean();	
					if($operator != '')
					{
						$seats_list .='</select> <span id="operator['.$key.']['.$keyInner.'][]"> ';
					    ob_start();	
						$lists= Models\Operators::get();
		        ?>
                 <span class="text-left">Select Operator</span>
                 <select name="operatorsSelection_old[<?=$key?>][<?=$keyInner?>][<?=$j?>]" id="operatorsSelection[<?=$key?>][<?=$keyInner?>][<?=$j?>]" class="form-control" disabled>
                <?php  foreach($lists as $list) { ?>
                 <option <?php if ($operator == $list['op_id']) echo 'selected' ?> value="<?=$list['op_id']?>"><?=$list['op_fname']?></option>
                <?php } ?>
                 </select>
                 </span> 
                </td>
					<?php
					$seats_list .= ob_get_clean();
					}
					else{
						$seats_list .='</select> <span id="operator['.$key.']['.$keyInner.'][]"></span> </td>';
					}
					$seats_list .='<td><input type="text" name="from_value_old['.$key.']['.$keyInner.'][]" placeholder="'.$this->lang['seat_map_row_placeholder_txt'].'" class="form-control" value="'.$seatRow['seat_from'].'" disabled>';
					$seats_list .= '</td>';
					$seats_list .= '<td>';
					$seats_list .= '<input type="text" name="to_value_old['.$key.']['.$keyInner.'][]" placeholder="'.$this->lang['seat_map_to_placeholder_txt'].'" class="form-control" value="'.$seatRow['seat_to'].'"  disabled>';
					$seats_list .= '</td>';
					$seats_list .= '<td>';
					$checked = '';
					 if($seat_order == 2){
						 $checked = 'checked="checked"';
					 }
					$seats_list .= '<input type="checkbox" name="seat_order_old['.$key.']['.$keyInner.']['.$j.']" class="form-control"  value="2" '.$checked.' disabled>';
					$seats_list .= '<input type="hidden" name="row_number_old['.$key.']['.$keyInner.'][]" value="'.$counterRow[$keyInner].'">';
					$seats_list .= '<input type="hidden" name="row_seat_id_old['.$key.']['.$keyInner.'][]" value="'.$seatRow['id'].'">';
					$seats_list .= '</td>';
					$seats_list .= '<td>';
					/*$orderRowIdExist = Models\OrderItems::where('ticket_row_id','=', $seatRow['id'])->first()->ticket_row_id;
					if($orderRowIdExist){
						$seats_list .= '<span class="la la-minus  default_c trRemove" id="addBtnRemove_'.$row['id'].'" onClick="'.$onlick_no.'"></span>';
					}else{
						$seats_list .= '<span class="la la-minus  default_c trRemove" id="addBtnRemove_'.$row['id'].'" onClick="'.$onclickRow.'"></span>';
					}*/
					$seats_list .= '<span class="fa fa-th-large" id="addBtnRemove_'.$row['id'].'" ></span>';
					$seats_list .= '</td>';
					$seats_list .= '</tr>';
					$j++;
					
					// this is to show the booked seats and the free seats
					$seats_list .= '<tr>';
					$seats_list .= '<td colspan="5" align="left">';
					$table_seat_id = 'tabl_seat_'.$table_id;
					$seats_list .= '<table class="seats_tbl" id="'.$table_seat_id.'">
								  <tr>
									<th align="center" style="text-align:center"><span style="display:none">'.$this->lang['seat_map_seat_number_txt'].'</span>#</th>
									<th><i class="fa fa-wheelchair"></i> '.$this->lang['seat_map_placement_txt'].'</th>
									<th><i class="fa fa-user-circle"></i> '.$this->lang['seat_map_customer_txt'].'</th>
									<th><i class="fa fa-calendar-check-o"></i> '.$this->lang['seat_map_date_txt'].'</th>
									<th>&#x20aa;</th>
									<th>'.$this->lang['seat_map_status_txt'].'</th>
								  </tr>';
								  
			    // Loop through the table
					
				$sqlSeatsQuery = "SELECT U.name AS customer_name, CAST(ECRS.seat_number AS SIGNED) AS seat_number, ECRS.* FROM 
									event_category_row_seats AS ECRS LEFT JOIN users AS U 
									ON ECRS.customer_id=U.id WHERE  ECRS.event_seat_categories_id=".$row['id']." AND ECRS.row_seats_id=".$seatRow['id']."
									ORDER BY seat_number ASC";
				$resultSeatsQuery = mysqli_query($this->conn, $sqlSeatsQuery);
				
				// Loop throguh seat rows list
				//foreach($seat_rows_list as $rowSeat){
				while($rowSeat = mysqli_fetch_assoc($resultSeatsQuery)){
				$laset_insert_id = $rowSeat['id'];	
				$event_seat_cat_id = $rowSeat['event_seat_categories_id'];	
				$row_seats_id = $rowSeat['row_seats_id'];	
				$seatStatus  = $rowSeat['status'];
                 // Get all data here
				 if($rowSeat['customer_id'] > 0 ){
					// Get the customer first name as well
					$first_Name = Models\Usermeta::where('user_id', '=', $rowSeat['customer_id'])->first()->first_name; 	
				   $customer_name = $first_Name. ' '.$rowSeat['customer_name'];
				 }else{
				   $customer_name = '';
				 }
				 
				 $booked_datetime = $rowSeat['booked_datetime'];
				 if($seatStatus == 'B'){ 
					 $booked_datetime = date('d/m/Y H:i',  strtotime($booked_datetime));
				 }else{
					 $booked_datetime = '';
				 }
				 /*
				 $refund_datetime = $rowSeat['refund_datetime'];
				 if($seatStatus == 'R' ){
					 $refund_datetime = date('d/m/Y H:i', strtotime($refund_datetime));
				 }else{
					 $refund_datetime= '';
				 }
				 
				 if($seatStatus == 'C'){
					 $refund_datetime = date('d/m/Y H:i', strtotime($booked_datetime));
				 }else{
					 $refund_datetime= '';
				 }*/
				 
				 $seat_number = $rowSeat['seat_number'];
				 $last_seat_number = $seat_number;
				 $seatType    = $this->seatType($rowSeat['placement']);	
				 
				 $seatPriceVal  = $rowSeat['seat_price'];
				 if($seatStatus == 'B'){ // booked
				    $datetime = $booked_datetime; 
				    $ticket_class = 'danger';
					$statusIcon = 'fa fa-ban';
					$ticketStatus = '<a href="javascript:void(0)" class="btn btn-danger m-btn btn-sm 	m-btn m-btn--icon seat_btn" style="cursor:default">
															<span>
																<i class="fa fa-ban"></i>
																<span>
																	'.$this->lang['seat_map_booked_txt'].'
																</span>
															</span>
														</a>';
				 }else if($seatStatus == 'R'){ // Returned OR Refunded
				     $datetime = $refund_datetime; 
				     $ticket_class = 'orange';
					 $statusIcon = 'fa fa-close';
					 $ticketStatus = '<a href="javascript:void(0)" class="btn btn-warning m-btn btn-sm 	m-btn m-btn--icon seat_btn" style="cursor:default">
															<span>
																<i class="fa fa-close"></i>
																<span>
																	'.$this->lang['seat_map_refunded_txt'].'
																</span>
															</span>
														</a>';
				 }else if($seatStatus == 'C'){ // Changed
				     $datetime = $refund_datetime; 
				     $ticket_class = 'accent';
					 $statusIcon = 'fa fa-close';
					 $ticketStatus = '<a href="javascript:void(0)" class="btn btn-accent m-btn btn-sm 	m-btn m-btn--icon seat_btn" style="cursor:default">
															<span>
																<i class="fa fa-close"></i>
																<span>
																	'.$this->lang['seat_map_changed_txt'].'
																</span>
															</span>
														</a>';
				 
				}else{
					if($rowSeat['placement'] == 2){
						$ticket_class = 'reserve'; // Available/Empty
					    $btnStyle = 'btn-reserve';
						$style_color = 'cursor:default;height: 29px;border: 1px solid #ffdd99;background-color:  #ffdd99;color:  #fff;';
					}else{
						$ticket_class = 'success'; // Available/Empty
					    $btnStyle = 'btn-success';
						$style_color = '';
					}
					 $datetime = '';
					 $statusIcon = 'fa fa-clock-o';
					 $ticketStatus = '<a href="javascript:void(0)" class="btn '.$btnStyle.' btn-sm m-btn 	m-btn m-btn--icon " style="cursor:default; '.$style_color.'">
															<span>
																<i class="fa fa-clock-o"></i>
																<span>
																	'.$this->lang['seat_map_available_txt'].'
																</span>
															</span>
														</a>';
				 }
				 $rem_href = $edit_href = '';
				 if($seatStatus != 'B'){ // booked
				   // Edit Link 
					$edit_href = '<a href="javascript:void(0);"  onclick="editThisRow('.$laset_insert_id.')" style="color:blue;"><i class="fa fa-edit" style="font-size:25px"></i></a>';
					// Delete / Remove Link
					$rem_href =  '<a href="javascript:void(0);" onClick="removeSeat('.$laset_insert_id.')" class="error" style="margin-left:44px;" ><i class="fa fa-times-circle-o" style="font-size:25px; margin-top:4px"></i></a>';	 
					$td_class = 'seat_type_class_'.$row_seats_id; 
				 }else{
					$td_class = '';
				 }
				 $seatCircle  = '<div class="m-demo__preview m-demo__preview--badge" style="cursor:pointer" onClick="view_seat_popup('.$laset_insert_id.')">';	
				 $seatCircle .= '<span class="m-badge m-badge--'.$ticket_class.'">'.$seat_number.'</span>';	
				 $seatCircle .= '</div>';
				 
				
				 
				 				  		  
				$seats_list .= '<tr id="tabl_id_row_'.$laset_insert_id.'" >
					<td>'.$seatCircle.'</td>
					<td class="'.$td_class.'">'.$seatType.'</td>
					<td>'.$customer_name.'</td>
					<td>'.$datetime.'</td>
					<td>'.$seatPriceVal.'</td>
					<td style="display:none"><a href="#" class="btn btn-'.$ticket_class.' m-btn m-btn--icon btn-lg m-btn--icon-only">
						<i class="'.$statusIcon.'"></i>
					</a></a></td>
				  <td>'.$ticketStatus.'<br>'.$edit_href.' '.$rem_href.'</td>	
				  </tr>';
				  $last_seat_number = $seat_number;
				}
				
				// Add new row
				//$seats_list .= '';
				
				$seats_list .= '</table>';
				
				// To add new row
				$seats_list .= '<span id="newFreshRow_'.$table_seat_id.'"></span>';
				
				 
				$add_new_seat_table = '';
				$add_multiple_seats_table = '';
				if(empty($last_seat_number) || $last_seat_number = '' ){
					$seat_number_latest = 1;
				}else{
				    $seat_number_latest = ($last_seat_number+1);	
				}
				if( mysqli_num_rows($resultSeatsQuery) > 0 ){
					 // Add New Row
					 $add_new_seat_table_Row = '<table style="float:left; margin-left:-11px;" id="btn_'.$table_seat_id.'">';
				     $add_new_seat_table_Row .= '<tr>';
					 $add_new_seat_table_Row .= '<td  align="right" style="border: none !important;">';
					 $add_new_seat_table_Row .= '<a href="javascript:void(0);" class="btn btn-warning m-btn m-btn--custom m-btn--icon" onClick="addNewSeatRow(\''.$table_seat_id.'\', \''.$id.'\', \''.$row['id'].'\',\''.$seatRow['id'].'\')">';
				     $add_new_seat_table_Row .= '<span><i class="fa fa-plus"></i> <span>Add Row</span></span>';
					 $add_new_seat_table_Row .= '</a>';
					 $add_new_seat_table_Row .= '</td>';
					 $add_new_seat_table_Row .= '</tr>';
					 $add_new_seat_table_Row .= '</table>';
					 $seats_list .= $add_new_seat_table_Row;
					 // Add Multiple Seats
					 $add_multiple_seats_table .= '<table style="float:left;margin-left: 35px;" id="btnMultiple_'.$table_seat_id.'">';
					 $add_multiple_seats_table .= '<tr>';
					 $add_multiple_seats_table .= '<td  align="right" style="border: none !important;">';
					 $add_multiple_seats_table .= '<a href="javascript:void(0);" class="btn btn-primary m-btn m-btn--custom m-btn--icon" onClick="addMultipleSeatsRowFresh(\''.$table_seat_id.'\', \''.$id.'\', \''.$row['id'].'\',\''.$seatRow['id'].'\',\''.($seat_number+1).'\')">';
					 $add_multiple_seats_table .= '<span><i class="fa fa-plus"></i> <span>Add Multiple Seats</span></span>';
					 $add_multiple_seats_table .= '</a>';
					 $add_multiple_seats_table .= '</td>';
					 $add_multiple_seats_table .= '</tr>';
					 $add_multiple_seats_table .= '</table>';
					 $seats_list .= $add_multiple_seats_table;
					 // Add New Seat
				     $add_new_seat_table .= '<table style="float:right;margin-right:-10px;">';
				     $add_new_seat_table .= '<tr>';
					 $add_new_seat_table .= '<td  align="right" style="border: none !important;">';
					 $add_new_seat_table .= '<a href="javascript:void(0);" class="btn btn-primary m-btn m-btn--custom m-btn--icon" onClick="addSeatRow(\''.$table_seat_id.'\', \''.$id.'\', \''.$row['id'].'\',\''.$seatRow['id'].'\')">';
				     $add_new_seat_table .= '<span><i class="fa fa-plus"></i> <span>'.$this->lang['seat_map_add_seat_txt'].'</span></span>';
					 $add_new_seat_table .= '</a>';
					 $add_new_seat_table .= '</td>';
					 $add_new_seat_table .= '</tr>';
					 $add_new_seat_table .= '</table>';
					 $seats_list .= $add_new_seat_table;
				  }else{
					  $seat_number_latest = 1;
					 // Add New Row
					 $add_new_seat_table_Row = '<table style="float:left; margin-left:-11px;" id="btn_'.$table_seat_id.'">';
				     $add_new_seat_table_Row .= '<tr>';
					 $add_new_seat_table_Row .= '<td  align="right" style="border: none !important;">';
					 $add_new_seat_table_Row .= '<a href="javascript:void(0);" class="btn btn-warning m-btn m-btn--custom m-btn--icon" onClick="addNewSeatRow(\''.$table_seat_id.'\', \''.$id.'\', \''.$row['id'].'\',\''.$seatRow['id'].'\')">';
				     $add_new_seat_table_Row .= '<span><i class="fa fa-plus"></i> <span>Add Row</span></span>';
					 $add_new_seat_table_Row .= '</a>';
					 $add_new_seat_table_Row .= '</td>';
					 $add_new_seat_table_Row .= '</tr>';
					 $add_new_seat_table_Row .= '</table>';
					 $seats_list .= $add_new_seat_table_Row;
					 // Add Multiple Seats
					 $add_multiple_seats_table .= '<table style="float:left;margin-left: 35px;">';
					 $add_multiple_seats_table .= '<tr>';
					 $add_multiple_seats_table .= '<td  align="right" style="border: none !important;">';
					 $add_multiple_seats_table .= '<a href="javascript:void(0);" class="btn btn-primary m-btn m-btn--custom m-btn--icon" onClick="addMultipleSeatsRowFresh(\''.$table_seat_id.'\', \''.$id.'\', \''.$row['id'].'\',\''.$seatRow['id'].'\',\''.($seat_number_latest).'\')">';
					 $add_multiple_seats_table .= '<span><i class="fa fa-plus"></i> <span>Add Multiple Seats</span></span>';
					 $add_multiple_seats_table .= '</a>';
					 $add_multiple_seats_table .= '</td>';
					 $add_multiple_seats_table .= '</tr>';
					 $add_multiple_seats_table .= '</table>';
					 $seats_list .= $add_multiple_seats_table;
					 // Add New Seat
					 $add_new_seat_table .= '<table style="float:right;margin-right:-10px;" id="btnMultiple_'.$table_seat_id.'">';
				     $add_new_seat_table .= '<tr>';
					 $add_new_seat_table .= '<td  align="right" style="border: none !important;">';
					 $add_new_seat_table .= '<a href="javascript:void(0);" class="btn btn-primary m-btn m-btn--custom m-btn--icon" onClick="addSeatRowFresh( \''.$id.'\', \''.$row['id'].'\',\''.$seatRow['id'].'\')">';
				     $add_new_seat_table .= '<span><i class="fa fa-plus"></i> <span>'.$this->lang['seat_map_add_seat_txt'].'</span></span>';
					 $add_new_seat_table .= '</a>';
					 $add_new_seat_table .= '</td>';
					 $add_new_seat_table .= '</tr>';
					 $add_new_seat_table .= '</table>';
					 $seats_list .= $add_new_seat_table;
				  }
				   
					$seats_list .= '</td>';
					$seats_list .= '</tr>';
					
				 }
				 
				  $seats_list .= '</tbody>';
				  $seats_list .= '</table>';
				  $seats_list .= '</div>';
				  $seats_list .= '</div>';
				  $seats_list .= '</div>';
			 }
			 
			$seats_list .= '<div class="row">
							 
							  <div class="col-md-2">&nbsp;</div>
							  <div class="col-md-2" style="margin-top:14px;">'.$this->lang['seat_map_cat_price_txt'].'</div>
							  <div class="col-md-3">
								<input type="text" class="form-control" name="category_price_old['.$key.']" placeholder="'.$this->lang['seat_map_cat_price_txt'].'" value="'.$row['category_price'].'">
							  </div>';
							  			  
			$seats_list .= '</div>';

			$seats_list .= '</div>';
		 }
			
			
			
		}
	  //exit;
	      // Get all categories of this Event
	      $eventCategories = Models\EventSeatCategories::where('event_id','=', $id)->get();
		  $params = array( 'title' => $this->lang['seat_map_set_map_txt'],
		                    'data' => $event,
							'seats_list' => $seats_list,
							'seat_order' => $seat_order,
							'is_expired' => $is_expired,
							'categories_list' => $eventCategories);
          return $this->render($response, ADMIN_VIEW.'/Event/edit_map.twig',$params);
		}
	}
	
	// Save event map
	public function saveEventSeatTicketMap($request, $response){
		  $event_id   = $request->getParam('id');
		  $data = array('status' => 0);
		  $eventRole = Models\EventSeatCategories::where('event_id', '=', $event_id)->update($data);	
		  $this->saveEventSeatTicketsData($event_id);
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	}
	
	// Update event map
	public function updateEventSeatTicketMap($request, $response){
		  $event_id   = $request->getParam('id');
		  $auditorium_id =  Models\Event::where('id', '=', $event_id)->first()->auditorium_id;
		  //ddump($_REQUEST); exit;
		  
		  $this->saveRowSeatsTable($event_id, $auditorium_id);
		  $this->updateRowSeatsTable();
		  //$this->updateEventSeatTicketsData($event_id);
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	}
	
	// Delete Event seat
	public function deleteEventSeatById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// echo $created_date. ' == '.$event_id; exit;
		 $delete = Models\RowSeats::where('event_seat_categories_id','=',$id)->delete();
		 $delete = Models\EventSeatCategories::find($id)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Delete Event seat row
	public function deleteEventSeatRowById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// echo $created_date. ' == '.$event_id; exit;
		 $delete = Models\RowSeats::find($id)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Delete seat row
	public function deleteRowSeatById($request, $response, $args){
		$id = $args['id'];
		$row_number = $args['row_number'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$event_seat_categories_id =  Models\RowSeats::where('id', '=', $id)->first()->event_seat_categories_id;
		// get the range
		 $from_range =  Models\EventSeatCategories::where('id', '=', $event_seat_categories_id)->first()->from_range;
		
		 $from_range_exp = explode(',', $from_range);
		 $from_range_new = '';
		 foreach($from_range_exp as $exp){
			if($exp !=  $row_number){
			  $from_range_new .= $exp.',';	
			}
		 }
		 $from_range_update = rtrim($from_range_new,','); 
		 
		 if($from_range_update == ''){
			 $catDel = Models\EventSeatCategories::where('id', '=', $event_seat_categories_id)->delete();	
			 $delete = Models\RowSeats::where('event_seat_categories_id', '=',$event_seat_categories_id)->delete();
		 }else{
		   $data = array('from_range' => $from_range_update);
		   $eventCats = Models\EventSeatCategories::where('id', '=', $event_seat_categories_id)->update($data);	
		 }
		 		 
		 $delete = Models\RowSeats::where('event_seat_categories_id', '=',$event_seat_categories_id)
		                          ->where('row_number', '=',$row_number)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
  
    // Update Rows Seats Table Data
    public function updateRowSeatsTable(){
	 $update_old = 'N';
	  if( isset($_REQUEST['seat_category_old']) && !empty($_REQUEST['seat_category_old']) ){
			 $m=0;
			  foreach($_REQUEST['seat_category_old'] as $key=>$seat_category):
				  //print_r($libres);
			      $category_price    = $_REQUEST['category_price_old'][$key]; 
				  $seat_category_id  = $_REQUEST['seat_category_id_old'][$key]; 
				  //$total_seats_old   = $_REQUEST['total_seats_old'][$key];
				  $libres =  $_REQUEST['libres_old'][$key];
				  if(isset($libres) && !empty($libres)) {
					 $libres = 1;
					 //$total_seats_old = $total_seats_old;
				  }else{
					 $libres = 0; 
					//$total_seats_old = 0;
				  }	 
				  $total_seats_old = 0;   
				   $seat_category = $seat_category;
				   $update_seat_category_table = array('seat_category' => $seat_category,
													   'libres' => $libres,
													   /*'total_seats' => $total_seats_old,*/
				                                       'category_price' => $category_price);	
													  
				   //	ddump($update_seat_category_table);								  
				   $updateSeatCats = Models\EventSeatCategories::where('id', '=', $seat_category_id)
				                     ->update($update_seat_category_table);	
				   $total_qantity = 0;
				   $k=0;
				   if($update_old == 'Y'){
					 foreach($_REQUEST['from_value_old'][$key] as $innerkey=>$val){
						   $row_number = $_REQUEST['row_number_old'][$key][$innerkey];
						   $row_seat_id =  $_REQUEST['row_seat_id_old'][$key][$innerkey];
						   					   
						   $seat_order_old = $_REQUEST['seat_order_old'][$key];
						   //ddump($seat_order_old);
						   $from = $val;
						   $to   = $_REQUEST['to_value_old'][$key][$innerkey];
						  if( !empty($from) && !empty($to) ){
							$total_qantity = findQuantity($from, $to);
							$l=0;
							foreach($row_number as $innerKey1=>$val){
								$l=$l+1;
							 $event_seat_categories_id = $row_seat_id;
							 $row_number = $val;
							 $seat_from = $from[$innerKey1];
							 $seat_to = $to[$innerKey1];
							 $total_qantity = findQuantity($from[$innerKey1], $to[$innerKey1]);
							 $seat_order_new = $seat_order_old[$innerkey][$innerKey1];
							 
							 $placement_drop_down = $_REQUEST['Placement_old'][$key][$innerkey][$innerKey1];
							 // Check if the placment drop down has beens selected
							 if( isset($placement_drop_down) && !empty($placement_drop_down) ){
							      $placement = $_REQUEST['Placement_old'][$key][$innerkey][$innerKey1]; 
							 }else{
							      $placement = $_REQUEST['Placement_old'][$key][$innerkey][$innerKey1];
							 }
							
							 $operator_drop_down = $_REQUEST['operatorsSelection_old'][$key][$innerkey][$innerKey1];
							 // Check if operator drop down is selected
							 if( isset($operator_drop_down) && !empty($operator_drop_down) )
							 {
								$operator = $_REQUEST['operatorsSelection_old'][$key][$innerkey][$innerKey1];
							 }
							 else
							 {
								$operator = "";
							 }
							 
							  //print_r($_REQUEST['operatorsSelection']);
							 if(isset($seat_order_new) && !empty($seat_order_new)) {
								 $seat_order = 2;
							  }else{
								 $seat_order = 1; 
							  }
							  // Check if seat order is 2 it means the seat order is even apply the rules
							  if($seat_order == 2){
							    $total_qantity = intval($total_qantity/2);
							  }
							  
							 $row_seat_id = $row_seat_id; // Row Seat ID
							 
							 $update_row_seats_table = array('row_number' => $row_number,
															/*'seat_from' => $seat_from,*/
															'seat_to' => $seat_to,
															'seat_to_val' => $seat_to,
															'operator_id' => $operator,
															'placement' => $placement,
															'total_qantity' => $total_qantity,
															'seat_order' => $seat_order);
							
							//print_r($update_row_seats_table);							
							 							
							 $updateSeatRows = Models\RowSeats::where('id', '=', $row_seat_id[$innerKey1])
				                     ->update($update_row_seats_table);			 
							}
						  }
				   $k++; }
	              }
				   
				   
				   // Check if new data is posted then save them
				   if( isset($_REQUEST['from_value_old_new']) && !empty($_REQUEST['from_value_old_new']) ){
				      
					  foreach($_REQUEST['from_value_old_new'][$key] as $innerkey=>$val){
						  $row_number = $_REQUEST['row_number_old_new'][$key][$innerkey];
						  $seat_order = $_REQUEST['seat_order_old_new'][$key][$innerkey];
						  $row_seat_id =  $_REQUEST['row_seat_id_old_new'][$key][$innerkey];
						  
						  
						  $from = $val;
						  $to   = $_REQUEST['to_value_old_new'][$key][$innerkey];
						  if( !empty($from) && !empty($to) ){
							 
							 $total_qantity = 0;
							 $l=0;
							foreach($row_number as $innerKey1=>$valD){
							
							$seat_order = $seat_order[$innerKey1];	
							if(isset($seat_order) && !empty($seat_order)) {
								 $seat_order = 2;
							  }else{
								 $seat_order = 1; 
							  }
							 
							 $total_qantity = findQuantity($from[$innerKey1], $to[$innerKey1]);
							 if($seat_order == 2){
							   $total_qantity = intval($total_qantity/2);
							 }
							  $placement = $_REQUEST['placement'][$key][$innerkey];
							 if($_REQUEST['operator'][$key][$innerkey]!="")
							 {
								$operator= $_REQUEST['operatorsSelection'][$key][$innerkey][$l-1];
							 }
							 else
							 {
								$operator= "";
							 }
							 //print_r($_REQUEST['Placement'][$key][$innerkey]);
							 // if($_REQUEST['Placement'][$key][$innerkey][$innerKey1 +1] !="")
							 //$placement = $_REQUEST['Placement'][$key][$innerkey][$innerKey1 +1]; 
							 //else
							 //$placement = $_REQUEST['Placement'][$key][$innerkey][$innerKey1];
							 $placement = $_REQUEST['Placement'][$key][$innerkey];
							  //echo $key." ".$innerkey." ".$l;
							
							 if(isset($seat_order_new) && !empty($seat_order_new)) {
								 $seat_order = 2;
							  }else{
								 $seat_order = 1; 
							  }
							  if($seat_order == 2){
							    $total_qantity = intval($total_qantity/2);
							  }
							  if($operator!="")
							 {
							  $data = array('event_seat_categories_id' => $row_seat_id[$innerkey],
							                'row_number' => $row_number,
											'seat_from' => $from,
											'operator_id' => $operator,
											'placement' => $placement,
											'seat_to' => $to,
											'total_qantity' => $total_qantity,
											'seat_order' => $seat_order);
							 }
							 else
							 {
								 $data = array('event_seat_categories_id' => $row_seat_id[$innerkey],
							                'row_number' => $row_number,
											'seat_from' => $from,
											'seat_to' => $to,
											'placement' => $placement,
											'total_qantity' => $total_qantity,
											'seat_order' => $seat_order);
							 }
							//print_r($data);			
							 $event_seat_categories_id = $row_seat_id[$innerKey1];
							 $event_seat_categories_id_new = Models\RowSeats::
							                                 where('id', '=', $event_seat_categories_id)
															 ->first()->event_seat_categories_id;
							 $audSTCRowsNew = new Models\RowSeats;
							 $audSTCRowsNew->event_seat_categories_id = $event_seat_categories_id_new;
							 $audSTCRowsNew->row_number = $valD;
							 $audSTCRowsNew->seat_from = $from[$innerKey1];
							 $audSTCRowsNew->seat_to = $to[$innerKey1];
							 $audSTCRowsNew->seat_from_val = $from[$innerKey1];
							 $audSTCRowsNew->seat_to_val = $to[$innerKey1];
							 $audSTCRowsNew->total_qantity = $total_qantity;
							 $audSTCRowsNew->placement = $placement;
							 $audSTCRowsNew->operator_id = $operator_id;
							 $audSTCRowsNew->seat_order = $seat_order;
							 $audSTCRowsNew->save();
							//echo mysqli_info();
							 //print_r($audSTCRowsNew);
							}				
						}
				    }
				  }
			 $m++;
			 //exit;
			  endforeach;
		}
		//exit;
		
	}
	
	// Save New Row seats table
	public function saveNewRowSeatsTable(){
	  if( isset($_REQUEST['from_value_old_new']) && !empty($_REQUEST['from_value_old_new']) ){
			      $m=0;
				  foreach($_REQUEST['seat_category_old'] as $key=>$seat_category):
				   $row_id_new = $_REQUEST['row_number_old_new'][$key];
				   $k=0;
					 foreach($_REQUEST['from_value_old_new'][$key] as $innerkey=>$val){
						  $row_number = $_REQUEST['row_number_old_new'][$key][$innerkey];
						  $seat_order = $_REQUEST['seat_order_old_new'][$key][$innerkey];
						  $row_seat_id =  $_REQUEST['row_seat_id_old_new'][$key];
						  $libres =  $_REQUEST['libres'][$key];
						  $from = $val;
						  $to   = $_REQUEST['to_value_old_new'][$key][$innerkey];
						  if( !empty($from) && !empty($to) ){
							  $data = array('event_seat_categories_id' => $row_seat_id[$innerKey],
							                'row_number' => $row_number,
											'seat_from' => $from,
											'seat_to' => $to,
											'libres' =>$libres,
											'total_qantity' => $total_qantity,
											'seat_order' => $seat_order);
							//ddump($data);	
							 $total_qantity = 0;			
							foreach($row_number as $innerKey1=>$valD){
							if(isset($seat_order) && !empty($seat_order)) {
								 $seat_order = 2;
							  }else{
								 $seat_order = 1; 
							  }
							 
							 $total_qantity = findQuantity($from[$innerKey1], $to[$innerKey1]);
							 if($seat_order == 2){
							   $total_qantity = intval($total_qantity/2);
							 }
							 $event_seat_categories_id = $row_seat_id[$innerKey1];
							 $audSTCRowsNew = new Models\RowSeats;
							 $audSTCRowsNew->event_seat_categories_id = $event_seat_categories_id;
							 $audSTCRowsNew->row_number = $valD;
							 $audSTCRowsNew->seat_from = $from[$innerKey1];
							 $audSTCRowsNew->seat_to = $to[$innerKey1];
							 $audSTCRowsNew->total_qantity = $total_qantity;
							 $audSTCRowsNew->seat_order = $seat_order;
							 //$audSTCRowsNew->save();	
							
							
							}				
						  }
				   $k++; }
			 $m++;
			exit;
			 endforeach;
		}	
	}
	
	// Save Row seats table
	public function saveRowSeatsTable($event_id, $auditorium_id){
	  if( isset($_REQUEST['seat_category']) && !empty($_REQUEST['seat_category']) ){
			 $m=0;
			  $counter_cat = sizeof($_REQUEST['seat_category']); 
			  //ddump($_REQUEST['libres']); // Loop through the seat category
			  foreach($_REQUEST['seat_category'] as $key=>$seat_category):
			     
			      $category_price = $_REQUEST['category_price'][$key]; 
				  //$total_seats = $_REQUEST['total_seats'][$key]; 
				  $first_char = $_REQUEST['seat_row_from'][$key]; 
				  $second_char = $_REQUEST['seat_row_to'][$key]; 
				  $from_range = join(',',range(''.$first_char.'', ''.$second_char.''));
				 
				  $libres =  $_REQUEST['libres'][$key];
				  if(isset($libres) && !empty($libres)) {
					 $libres = 1;
					 //$total_seats = $total_seats;
				  }else{
					 $libres = 0; 
					 //$total_seats = 0;
				  }
				  $total_seats = 0;
				  if(isset($category_price) && !empty($category_price) ){
					  $category_price = $category_price;
				  }else{
					 $category_price = 0;  
				  }
				  // Create array of the event seats
			      $event_seats = array('event_id' => $event_id,
				                       'auditorium_id' => $auditorium_id,
									   'seat_category' => $seat_category,
									   'seat_row_from' => $first_char,
									   'seat_row_to' => $second_char,
									   'category_price' => $category_price,
									   'libres' =>  $libres,
									   'from_range' => $from_range,
									   'total_seats' => $total_seats
									   ); 
				   /* ddump($event_seats);
					exit;*/				   				   
				   $audSTC = new Models\EventSeatCategories;
				   $audSTC->auditorium_id = $auditorium_id;
				   $audSTC->event_id = $event_id;
				   $audSTC->seat_category = $seat_category;
				   $audSTC->seat_row_from = $first_char;
				   $audSTC->seat_row_to = $second_char;
				   $audSTC->from_range  = join(',',range(''.$first_char.'', ''.$second_char.''));
				   $audSTC->category_price = $category_price;
				   $audSTC->libres = $libres;
				   $audSTC->total_seats = $total_seats;
				   $audSTC->save(); 
				   $row_id = $audSTC->id;
				   // Total Quantity
				   $total_qantity = 0;
				   $k=0;
					 foreach($_REQUEST['from_value'][$key] as $innerkey=>$val){
						  $row_number = $_REQUEST['row_number'][$key];
						  $seat_order_array = $_REQUEST['seat_order'][$key];
						 //ddump($seat_order_array);
						  $from = $val;
						  $to   = $_REQUEST['to_value'][$key][$innerkey];
						  if( !empty($from) && !empty($to) ){
							 
							  $data = array('event_seat_categories_id' => $row_id,
							                'row_number' => $row_number,
											'seat_from' => $from,
											'seat_to' => $to,
											'total_qantity' => $total_qantity,
											'seat_order' => $seat_order);
							//ddump($data);
							$l=0;
							$mm=0;			
							foreach($from as $innerKey1=>$val){
								
								$l=$l+1;
								
								$posted_seat_order = $seat_order_array[$innerkey][$innerKey1];

								if(isset($posted_seat_order) && !empty($posted_seat_order)) {
									 $seat_order = 2;
								  }else{
									 $seat_order = 1; 
									
								  }
								 //echo 'Seat order'.$seat_order.'<br>';
								 
								$total_qantity = findQuantity($from, $to);
								 if($seat_order == 2){
								   $total_qantity = intval($total_qantity/2);
								 }
							$placement_drop_down =  $_REQUEST['Placement'][$key][$innerkey][$innerKey1];
							if( isset($placement_drop_down) && !empty($placement_drop_down) ){
							     $placement = $_REQUEST['Placement'][$key][$innerkey][$innerKey1]; 
							}else{
							     $placement = $_REQUEST['Placement'][$key][$innerkey][$innerKey1];
							}
							 //print_r ($_REQUEST['operatorsSelection']); 
							 
							 $operator_drop_down = $_REQUEST['operatorsSelection'][$key][$innerkey][$innerKey1];
							  if( isset($operator_drop_down) && !empty($operator_drop_down) )
							 {
								$operator = $_REQUEST['operatorsSelection'][$key][$innerkey][$innerKey1];
								//echo 'IF ='. $operator.'<br>';
							 }
							 else
							 {
								$operator= "";
								//echo 'ELSE ='. $operator.'<br>';
							 }
							
							    
								 $audSTCRows = new Models\RowSeats;
								 $audSTCRows->event_seat_categories_id = $row_id;
								 $audSTCRows->row_number = $_REQUEST['row_number'][$key][$innerkey][$innerKey1];
								 $audSTCRows->placement = $placement;
								 $audSTCRows->operator_id = $operator;
								 $audSTCRows->seat_from = $from[$innerKey1];
								 $audSTCRows->seat_to = $to[$innerKey1];
								 $audSTCRows->seat_from_val = $from[$innerKey1];
								 $audSTCRows->seat_to_val = $to[$innerKey1];
								 $audSTCRows->total_qantity = findQuantity($from[$innerKey1], $to[$innerKey1]);
								 $audSTCRows->seat_order = $seat_order;
								 $audSTCRows->save();
								 $seat_row_table_id = $audSTCRows->id;
								 // Save all seats to the new table
								 $from_range = $from[$innerKey1];
								 $to_range   = $to[$innerKey1];
								 $row_number = $_REQUEST['row_number'][$key][$innerkey][$innerKey1];
								 if( isset($operator) && !empty($operator) ){
									  $operator_id = $operator;
								 }else{
									 $operator_id = 0;
								 }
								 $range = range($from_range, $to_range);
								 foreach($range as $seat_number){
									if($seat_order == 2){
										// It means seats should be saved in event order
									   if($seat_number % 2 == 0){
										 $this->saveRowSeatsIndividually($event_id, $row_id, $seat_row_table_id, $row_number, $seat_number, $placement,$operator_id,$category_price);  
									   }
									}else{
										// It means seats should be saved in sequence order
										$this->saveRowSeatsIndividually($event_id, $row_id, $seat_row_table_id, $row_number, $seat_number, $placement,$operator_id,$category_price);
									}
								    
		                         }
							$mm++;}				
						  }
				   $k++; }
			 $m++;
			 
			  endforeach; // End foreach loop
		}
		//exit;
			
	}
	
	
	// Get all categories of the event
	public function getEventSeatCategoryRows($request, $response, $args){
		$id = $args['id']; // Category id
		
        $validations = [
            v::intVal()->validate($id)
        ];
		
       // Check for validation
        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// Get category rows herer
		$category_rows = Models\RowSeats::where('event_seat_categories_id','=', $id)->get();
		$options  = '';
		foreach($category_rows as $row){
			$options .= '<option value="'.$row['id'].'">'.$row['row_number'].'</option>';
		}
		 echo '<option value="">Select Row</option>'.$options;
		
	}
	
	// Get all tickets solde of the event Category and Row
	public function getCategoryRowSaleReport($request, $response, $args){
		$event_ticket_category_id = $args['event_ticket_category_id']; // Category id
		$ticket_row_id = $args['ticket_row_id']; // ticket_row_id id
       
	   // Get This Event Complete Sale report
		$orderReport = Models\OrderItems::
					   where('type_product', '=', 'event')->
					   where('event_ticket_category_id', '=', $event_ticket_category_id)->
					   where('ticket_row_id', '=', $ticket_row_id)->get();
		
		if( $orderReport->isEmpty() ){
					$orderReportList .= '<tr>
										  <th scope="row" colspan="9"> 
										  <font color="red"><center>'.$this->lang['no_data_found_txt'].'</center></font> 
										  </th>
										</tr>';
				}else{
					$iCounter = 1;
					$reportData = array();
				  foreach($orderReport as $orderItem){
					  $order_id = $orderItem['order_id'];
					  $customer_id = $orderItem['Order']['customer_id'];
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
					  $ticket_row_id = $orderItem['ticket_row_id'];
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
					  
				       	$reportData['ticket_category'] = $ticket_category;
						$reportData['ticket_row'] = $ticket_row;
						$reportData['seat_sequence'] = $seat_sequence;
						$reportData['last_name'] = $last_name;
						$reportData['first_name'] = $first_name;
						$reportData['telephone'] = $telephone;
						$reportData['email'] = $email;
						$orderData['placement_id'] = $placement_id; 
						$reportData['source'] = 'Internet';
						$pdfData[] = $reportData;
				    $iCounter++; 
				  }
				  sort($pdfData);
				  foreach($pdfData as $arr){
				  $orderReportList .= '<tr>
				                         <td> '.$arr['last_name'].' </td>
										  <td>'.$arr['first_name'].'</td>
										  <td>'.$arr['telephone'].'</td>
										  <td>'.$arr['email'].'</td>
										  <td>'.$this->ticekt_type($arr['placement_id']).'</td>
										  <td>'.$arr['source'].'</td>
					                      <td> '.$arr['ticket_category'].' </td>
										  <td> '.$arr['ticket_row'].' </td>
										  <td> '.$arr['seat_sequence'].' </td>
										</tr>';
				  }
				  
				}
				echo $orderReportList;
		
	}
	
	
	// Download Sale report as PDF
public function downloadSaleReportPDF($request, $response, $args){
	        $event_ticket_category_id = $args['event_ticket_category_id']; // Category id
		    $ticket_row_id = $args['ticket_row_id']; // ticket_row_id id
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
			 // Get This Event Complete Sale report
		$orderReport = Models\OrderItems::
					   where('type_product', '=', 'event')->
					   where('event_ticket_category_id', '=', $event_ticket_category_id)->
					   where('ticket_row_id', '=', $ticket_row_id)->get();
		
		if( $orderReport->isEmpty() ){
					$reportTable .= '<tr>
										  <th scope="row" colspan="9"> 
										  <font color="red"><center>No data found.</center></font> 
										  </th>
										</tr>';
				}else{
					$iCounter = 1;
					$reportData = array();
				  foreach($orderReport as $orderItem){
					  $order_id = $orderItem['order_id'];
					  $customer_id = $orderItem['Order']['customer_id'];
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
					   $ticket_row_id = $orderItem['ticket_row_id'];
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
					  
					   $reportData['ticket_category'] = $ticket_category;
						$reportData['ticket_row'] = $ticket_row;
						$reportData['seat_sequence'] = $seat_sequence;
						$reportData['last_name'] = $last_name;
						$reportData['first_name'] = $first_name;
						$reportData['telephone'] = $telephone;
						$reportData['email'] = $email;
						$orderData['placement_id'] = $placement_id; 
						$reportData['source'] = 'Internet';
						$pdfData[] = $reportData;
				      	
				  $iCounter++; 
				  }
				  sort($pdfData);
				  foreach($pdfData as $ar){
					  $reportTable .= '<tr>
									  <td> '.$ar['last_name'].' </td>
									  <td>'.$ar['first_name'].'</td>
									  <td>'.$ar['telephone'].'</td>
									  <td>'.$ar['email'].'</td>
									  <td>'.$this->ticekt_type($ar['placement_id']).'</td>
									  <td>'.$ar['source'].'</td>
									  <td> '.$ar['ticket_category'].' </td>
									  <td> '.$ar['ticket_row'].' </td>
									  <td> '.$ar['seat_sequence'].' </td>
									</tr>';   
				  }
				}
			$htmlcontent.= $reportTable;	
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
			$filename = 'eventCategorySaleReport_'.time();
			$dompdf->stream($filename.".pdf");
			exit; // This is very important for downloading the PDF
						  
	}	
	
	
	// Ticket Type function
	public function ticekt_type($ticekt_type=''){
	   	if($ticekt_type == 1){
			return $this->lang['seat_map_standard_txt'];
		}else if($ticekt_type == 2){
			return $this->lang['seat_map_reserve_txt'];
		}else if($ticekt_type == 3){
			return  $this->lang['seat_map_inviation_txt'];
		}else if($ticekt_type == 4){
			return $this->lang['seat_map_vendues_txt'];
		}else{
		   return '';	
		}
	}
	
	
	// Delete Advertisement Picture
	public function removeEventAdvImage($request, $response, $args){
		$id = $args['id']; // Event Group id
		
	    $adv_image = Models\Event::where('id', '=', $id)->first()->adv_image;
	    if($adv_image){
		   // Unlink the picture

		   @unlink(EVENT_ADS_ROOT_PATH.'/'.$adv_image);
	    }
		
		// Update the event group table
		$data = array('adv_image' => '');
		$eventUpdate = Models\Event::where('id', '=', $id)->update($data);
		
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Check Row if not 
	public function checkRowSeat($request, $response, $args){
		$id = $args['id'];
		$row_number = $args['row_number'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// Get the event seat category id
		$event_seat_categories_id =  Models\RowSeats::where('id', '=', $id)->first()->event_seat_categories_id;
		// Check in the orders if this is reserved 
		$orderSeatCategoryId = Models\OrderItems::where('event_ticket_category_id', $event_seat_categories_id)->
		                                        first()->event_ticket_category_id; 		
												
		$orderTicketRowId = Models\OrderItems::where('ticket_row_id', $id)->
		                                        first()->ticket_row_id; 												 
		if($orderSeatCategoryId || $orderTicketRowId ){
			 return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => 'error')));
		}else{
		    return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
		}
	}
	
	// check Event Seat Row
	public function checkEventSeatRow($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// echo $created_date. ' == '.$event_id; exit;
		 $orderTicketRowId = Models\OrderItems::where('ticket_row_id', $id)->
		                                        first()->ticket_row_id; 												 
		if( $orderTicketRowId ){
			 return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => 'error')));
		}else{
		    return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
		}
	}
	
	// Save all seats individually
	public function saveRowSeatsIndividually($event_id, $cat_id, $row_id, $row_number, $seat_number, $placement,$operator,$category_price){
			 $eventCatRowSeat = new Models\EventCategoryRowSeat;
			 $eventCatRowSeat->event_id = $event_id;
			 $eventCatRowSeat->event_seat_categories_id = $cat_id;
			 $eventCatRowSeat->row_seats_id = $row_id;
			 $eventCatRowSeat->row_number = $row_number;
			 $eventCatRowSeat->seat_number = $seat_number;
			 $eventCatRowSeat->placement = $placement;
			 $eventCatRowSeat->operator_id = $operator;
			 $eventCatRowSeat->seat_price = $category_price;
			 $eventCatRowSeat->save();
	}
	
	// Display Seat Type Option
	public function seatType($type){
		$seatTypeVal = '';
		if($type == 1){
			$seatTypeVal = 'Standard';
		}else if($type == 2){
			$seatTypeVal = 'Réservées';
		}else if($type == 3){
			$seatTypeVal = 'Invitations';
		}else if($type == 4){
			$seatTypeVal = 'Vendues à autre opérateur';
		}
		return $seatTypeVal;
	}
	
	// Save New Seat Row
	public function saveNewRowSeat($request, $response, $args){
		$event_id = $args['event_id']; // Event id
        $cat_id   = $args['cat_id']; // Event Seat Category ID
		$row_id   = $args['row_id']; // Seat Row ID
		$table_id = $args['table_id']; // Table id
		// get data 
		$seatData = Models\EventCategoryRowSeat::where('event_id', '=', $event_id)->
		                                         where('event_seat_categories_id', '=', $cat_id)->
												 where('row_seats_id', '=', $row_id)->
												 orderBy('id', 'desc')->
												 limit(1)->
												 get();
		foreach($seatData as $key=>$data){
		  	 $row_number = $data['row_number'];
			 $placement = $data['placement'];
			 $operator_id = $data['operator_id'];
			 $seat_number = $data['seat_number'];
			 $seat_price = $data['seat_price'];
		}
		
		 // Get the maximum seat number of this
		 $seat_number = Models\EventCategoryRowSeat::where('event_id', '=', $event_id)->
		                                               where('event_seat_categories_id', '=', $cat_id)->
													   where('row_seats_id', '=', $row_id)->
													   whereRaw("row_number='".$row_number."'")->
													   get()->max('seat_number');
													   
		 $seat_number = checkAlphabetIncrement($seat_number); // Check the seat number and increment is
		// Save to the event_category_row_seats table
		 $eventCatRowSeat = new Models\EventCategoryRowSeat;
		 $eventCatRowSeat->event_id = $event_id;
		 $eventCatRowSeat->event_seat_categories_id = $cat_id;
		 $eventCatRowSeat->row_seats_id = $row_id;
		 $eventCatRowSeat->row_number = $row_number;
		 $eventCatRowSeat->placement = $placement;
		 $eventCatRowSeat->operator_id = $operator_id;
		 $eventCatRowSeat->seat_number = $seat_number;
		 $eventCatRowSeat->seat_price = $seat_price;
		 $eventCatRowSeat->save();
		 $laset_insert_id = $eventCatRowSeat->id;
		 /*
		 $seat_numberCircle  = '<div class="m-demo__preview m-demo__preview--badge">';	
		 $seat_numberCircle .= '<span class="m-badge m-badge--success">'.$seat_number.'</span>';	
		 $seat_numberCircle .= '</div>';
		
		 $textBoxInput = '<div class="form-group">
					  <div class="col-xs-2">
						<label for="ex1">Seat Number</label>
						<input class="form-control save_on_blur" id="seat_number_val_'.$laset_insert_id.'" type="text" name="seat_number" value="'.$seat_number.'">
					  </div>
					  </div>';
		
		$seatPriceInput = '<div class="form-group">
					  <div class="col-xs-2">
						<label for="ex1">Seat Price</label>
						<input class="form-control save_on_blur" id="seat_price_val_'.$laset_insert_id.'" type="text" name="seat_price" value="'.$seat_price.'">
					  </div>
					  </div>';			  
		 // Create the row which will append to the table later
		 $table_row  = '<tr id="tabl_id_row_'.$laset_insert_id.'" style="background-color:#fcd8b2;color:#000000">';
		 $table_row .= '<form id="seat_row_frm_'.$laset_insert_id.'" method="post" class="form-inline">';
	     $table_row .= '<td>'.$textBoxInput.'</td>';
		 $table_row .= '<td colspan="3">'.$this->seatTypeDropDown($laset_insert_id, $placement,$operator_id).'</td>';
		 $table_row .= '<td>'.$seatPriceInput.'</td>';
		 $table_row .= '<td>';
		 $table_row .= '<a href="javascript:void(0);" id="update_save_link_'.$laset_insert_id.'" onClick="updateNewSeat('.$laset_insert_id.')" style="color:green"><i class="fa fa-check-circle-o" style="font-size:30px"></i></a>';
		 $table_row .= '<a href="javascript:void(0);" onClick="removeSeat('.$laset_insert_id.')" class="error" style="margin-left:44px"><i class="fa fa-times-circle-o" style="font-size:30px"></i></a>';
		 $table_row .= '</td>';
		 $table_row .= '</form>';
		 $table_row .= '</tr>';
		 return $table_row;
		 */
		 
		 $table_row_created = $this->showPlainMode($laset_insert_id); 
		return $table_row_created;
	}
	
	// Save New Seat Row Fresh
	public function saveNewRowSeatFresh($request, $response, $args){
		$event_id = $args['event_id']; // Event id
        $cat_id   = $args['cat_id']; // Event Seat Category ID
		$row_id   = $args['row_id']; // Seat Row ID
		
		$seatData = Models\RowSeats::
							where('event_seat_categories_id', '=', $cat_id)->
							where('id', '=', $row_id)->
							limit(1)->
							get();
		foreach($seatData as $key=>$data){
		  	 $row_number = $data['row_number'];
			 $placement = $data['placement'];
			 $operator_id = $data['operator_id'];
			 $seat_number = $data['seat_number'];
			 $seat_price = $data['seat_price'];
		}
		
       
	    if($placement == '' || $placement == null){
		   $placement = 1;	
		}else{
		  $placement =  $placement;	
		}
		 
		 if($placement == 4){
			 $operator_id = $operator_id;
		 }else{
			$operator_id = 0; 
		 }
		 // Get event info
		 $seatDataEvent = Models\EventSeatCategories::
							where('id', '=', $cat_id)->
							where('event_id', '=', $event_id)->
							limit(1)->
							get();
	foreach($seatDataEvent as $key=>$data){
		  	 $event_id = $data['event_id'];
			 $category_price = $data['category_price'];
		}						
		// Save to the event_category_row_seats table
		 $eventCatRowSeat = new Models\EventCategoryRowSeat;
		 $eventCatRowSeat->event_id = $event_id;
		 $eventCatRowSeat->event_seat_categories_id = $cat_id;
		 $eventCatRowSeat->row_seats_id = $row_id;
		 $eventCatRowSeat->row_number = $row_number;
		 $eventCatRowSeat->placement = $placement;
		 $eventCatRowSeat->operator_id = $operator_id;
		 $eventCatRowSeat->seat_number = 1;
		 $eventCatRowSeat->seat_price = $category_price;
		 $eventCatRowSeat->save();
		 
		return true;
	}
	
	// Seat Type Drop Down
	public function seatTypeDropDown($id, $value,$operator_id){
		  $operators_list = '';
		  $css = 'none';
		  if($value ==  1){
			  $selected1 = 'selected="selected"';
		  }
		  if($value == 2){
			 $selected2 = 'selected="selected"';  
		  }
		  if($value == 3){
			 $selected3 = 'selected="selected"';  
		  }
		  if($value == 4){
			$selected4 = 'selected="selected"'; 
			$css = 'block';
		  }
		  // Get the productors list here
		  $operators_list = $this->getOperators($operator_id, $id);
		  $options = '<select class="Placement_new form-control" name="seat_type_id" id="seat_type_'.$id.'"  onChange="changePlacement(this, '.$id.')">';
		  $options .= '<option value="1" '.$selected1.'>Standard</option>';
		  $options .= '<option value="2" '.$selected2.'>Réservées</option>';
		  $options .= '<option value="3" '.$selected3.'>Invitations</option>"';
		  $options .= '<option value="4" '.$selected4.'>Vendues à autre opérateur</option>';
		  $options .= '</select>';
		  $options .= '<span id="operator_span_'.$id.'" style="display:'.$css.'">'.$operators_list.'</span>'; 
	  return $options;
	}
   
   // Get operators
   public function getOperators($operator_id, $id){
	  $lists= Models\Operators::get();
	  $operator_option = '<span class="text-left">Select Operator</span><br>';
	  $operator_option .= '<select class="form-control op_id_class" name="operator_id" id="op_val_'.$id.'">';
	  foreach($lists as $list)
	  {
		  if($operator_id > 0){
			  if($list['op_id'] == $operator_id){
				  $sel = 'selected="selected"';
			  }else{
				$sel = '';  
			  }
		  }else{
			$sel = '';  
		  }
		 $operator_option .= '<option value="'.$list['op_id'].'" '.$sel.'>'.$list['op_fname'].'</option>'; 
	  }
	  $operator_option .= '</select>';
	  return $operator_option;
	  
   }
   
   // New Seat Row Delete
   public function removeNewRowSeat($request, $response, $args){
		$id = $args['id']; // Seat Row Id
		// First of all check if this seat is already booked or not
		$status = Models\EventCategoryRowSeat::where('id', '=', $id)->first()->status;
		if($status == 'B'){
			return 'Booked';
		}else{
		   // Delete the seat row
		  $delete = Models\EventCategoryRowSeat::find($id)->delete();
		  return 'Yes';
		}
   }
   
   // Update the new seat Type
   public function changeNewRowSeatUpdate($request, $response, $args){ 	
		$id = $request->getParam('id'); // Seat Row Id
		$placement = $request->getParam('placement'); // Seat type i.e placement
		$operator_id = $request->getParam('operator_id'); // Operator id
		$seat_number = $request->getParam('seat_number'); // Seat number
		$seat_price = $request->getParam('seat_price'); // Seat Price
		$select_opt_id = $request->getParam('select_opt_id'); // Seat Placement Type
		if($placement < 4){
			 $operator_id = 0;
		}else{
			$operator_id = $operator_id;
		}
		if( !isset($seat_price) || empty($seat_price) ){
			$seat_price = 0;
		}else{
		   $seat_price = $seat_price;	
		}
		// Get all data of this seat
		$getSeatData = Models\EventCategoryRowSeat::where('id', '=', $id)->get();
		foreach($getSeatData as $row){
		  $event_id = $row['event_id'];	
		  $event_seat_categories_id = $row['event_seat_categories_id'];
		  $row_seats_id = $row['row_seats_id'];
		  $row_number_old = $row['row_number'];
		}
		
		// First check if this seat number already exist or not
		$seat_exist_id = Models\EventCategoryRowSeat::where('id', '!=', $id)
									->where('event_id', '=', $event_id)
									->where('event_seat_categories_id', '=', $event_seat_categories_id)
									->where('row_seats_id', '=', $row_seats_id)
									->whereRaw("seat_number='".$seat_number."' ")
									->first()->id;
	   
       if($seat_exist_id){    
		  $seatIfExist = 'E'; // Error as seat already exist
	   }else{
		  if($select_opt_id == 'Y'){
			 // It means update this seat type for all seats of this row
			 $data = array('placement' => $placement, 'operator_id' => $operator_id);
			 $updateAll = Models\EventCategoryRowSeat::where('event_seat_categories_id', '=', $event_seat_categories_id)->
			                                           where('row_seats_id', '=', $row_seats_id)->
													   where('event_id', '=', $event_id)->
													   whereRaw('status <> "B"')->
													   update($data); 
		  }
		  
		  // Update the seat status
		  $data = array('seat_price' => $seat_price,
					  'placement' => $placement,
					  'operator_id' => $operator_id,
					  'seat_number' => $seat_number);
		  $eventUpdate = Models\EventCategoryRowSeat::where('id', '=', $id)->update($data);  
		  
		  
		  $seatIfExist = 'S';   // Success as seat already not exist 
	   }
	   //return  $seatIfExist;
	    return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode($seatIfExist));
   }
   
   // Function to replace the row 
   public function changeSeatRowMode($request, $response, $args){
	   $laset_insert_id = $args['id']; // ID of the seat
	   $mode = $args['mode'];
	   // Check what it the action
	   if($mode == 'show_edit_mode'){
		   $table_row = $this->showEditMode($laset_insert_id);
	   }else if($mode == 'show_plain_mode'){
		   $table_row = $this->showPlainMode($laset_insert_id);
	   }
	   return $table_row ;
   }
   
   // Function to display the seat row in plain mode
   public function showPlainMode($laset_insert_id){
      $getseatData = Models\EventCategoryRowSeat::with(['Customer'])->
	                                          where('id','=', $laset_insert_id)->
											  get();	
				
			   foreach($getseatData as $rowSeat){
					$customer_id   = $rowSeat['customer_id'];   
					$customer_name = $rowSeat['Customer']['name'];
					$booked_datetime_val = $rowSeat['booked_datetime'];
					$refund_datetime_val = $rowSeat['refund_datetime'];
					$seat_number = $rowSeat['seat_number'];
					$placement = $rowSeat['placement'];
					$seatStatus  = $rowSeat['status'];
					$seatPriceVal  = $rowSeat['seat_price'];
				}
		     
                 // Get all data here
				 if($customer_id > 0 ){	
				   $customer_name = $customer_name;
				 }else{
				   $customer_name = '';
				 }
				 $booked_datetime = $booked_datetime_val;
				 if($seatStatus == 'B'){
					 $booked_datetime = date('d/m/Y g:i A',  strtotime($booked_datetime));
				 }else{
					 $booked_datetime = '';
				 }
				 $refund_datetime = $refund_datetime_val;
				 if($seatStatus  ==  'R'){
					 $refund_datetime = date('d/m/Y g:i A', strtotime($refund_datetime));
				 }else{
					 $refund_datetime= '';
				 }
				 // seat Type
				 $seatType    = $this->seatType($placement);	
				
				 if($seatStatus == 'B'){ // booked
				    $datetime = $booked_datetime; 
				    $ticket_class = 'danger';
					$statusIcon = 'fa fa-ban';
					$ticketStatus = '<a href="javascript:void(0)" class="btn btn-danger m-btn btn-sm 	m-btn m-btn--icon seat_btn" style="cursor:default">
															<span>
																<i class="fa fa-ban"></i>
																<span>
																	'.$this->lang['seat_map_booked_txt'].'
																</span>
															</span>
														</a>';
				 }else if($seatStatus == 'R'){ // Returned/Refunded
				     $datetime = $refund_datetime; 
				     $ticket_class = 'orange';
					 $statusIcon = 'fa fa-close';
					 $ticketStatus = '<a href="javascript:void(0)" class="btn btn-warning m-btn btn-sm 	m-btn m-btn--icon seat_btn" style="cursor:default">
															<span>
																<i class="fa fa-close"></i>
																<span>
																	'.$this->lang['seat_map_refunded_txt'].'
																</span>
															</span>
														</a>';
				 }else if($seatStatus == 'C'){ // Changed
				     $datetime = $refund_datetime; 
				     $ticket_class = 'accent';
					 $statusIcon = 'fa fa-close';
					 $ticketStatus = '<a href="javascript:void(0)" class="btn btn-accent m-btn btn-sm 	m-btn m-btn--icon seat_btn" style="cursor:default">
															<span>
																<i class="fa fa-close"></i>
																<span>
																	'.$this->lang['seat_map_changed_txt'].'
																</span>
															</span>
														</a>';
				 }else{
					 $datetime = '';
					 $ticket_class = 'success'; // Available/Empty
					 $statusIcon = 'fa fa-clock-o';
					 if($rowSeat['placement'] == 2){
						$ticket_class = 'reserve'; // Available/Empty
					    $btnStyle = 'btn-reserve';
						$style_color = 'cursor:default;height: 29px;border: 1px solid #ffdd99;background-color:  #ffdd99;color:  #fff;';
					}else{
						$ticket_class = 'success'; // Available/Empty
					    $btnStyle = 'btn-success';
						$style_color = '';
					}
					 $ticketStatus = '<a href="javascript:void(0)" class="btn '.$btnStyle.' btn-sm m-btn 	m-btn m-btn--icon " style="cursor:default; '.$style_color.'">
															<span>
																<i class="fa fa-clock-o"></i>
																<span>
																	'.$this->lang['seat_map_available_txt'].'
																</span>
															</span>
														</a>';
				 }
				 $edit_href = $rem_href = '';
				 if($seatStatus != 'B'){ // booked
				   // Edit Link
					$edit_href = '<a href="javascript:void(0);" id="edit_save_link_'.$laset_insert_id.'" onClick="editThisRow('.$laset_insert_id.')" style="color:blue;"><i class="fa fa-edit" style="font-size:25px"></i></a>';
					// Remove/Delete link
					$rem_href =  '<a href="javascript:void(0);" onClick="removeSeat('.$laset_insert_id.')" class="error"  style="margin-left:35px"><i class="fa fa-times-circle-o" style="font-size:30px; margin-top:4px"></i></a>';	
				 }
				 $seatCircle  = '<div class="m-demo__preview m-demo__preview--badge" style="cursor:pointer" onClick="view_seat_popup('.$laset_insert_id.')">';	
				 $seatCircle .= '<span class="m-badge m-badge--'.$ticket_class.'">'.$seat_number.'</span>';	
				 $seatCircle .= '</div>';				  		  
				 $table_row_plain_mode = '<tr id="tabl_id_row_'.$laset_insert_id.'">
							 <td>'.$seatCircle.'</td>
							 <td>'.$seatType.'</td>
							 <td>'.$customer_name.'</td>
							 <td>'.$datetime.'</td>
							 <td>'.$seatPriceVal.'</td>
							 <td style="display:none"><a href="#" class="btn btn-'.$ticket_class.' m-btn m-btn--icon btn-lg m-btn--icon-only">
								<i class="'.$statusIcon.'"></i>
							 </a></a></td>
						   <td>'.$ticketStatus.'<br>'.$edit_href.' '.$rem_href.'</td>	
						   </tr>';	
					return $table_row_plain_mode;	   
 
 }
   
   
   // Function to show plain mode of the ticket row
   public function showEditMode($laset_insert_id){
      $getseatData = Models\EventCategoryRowSeat::with(['Customer'])->
	                                          where('id','=', $laset_insert_id)->
											  get();	
		// Loop through seat data		
	   foreach($getseatData as $rowSeat){
		    $customer_id   = $rowSeat['customer_id'];   
			$customer_name = $rowSeat['Customer']['name'];
			$booked_datetime_val = $rowSeat['booked_datetime'];
			$refund_datetime_val = $rowSeat['refund_datetime'];
			$seat_number = $rowSeat['seat_number'];
			$placement = $rowSeat['placement'];
			$seatStatus  = $rowSeat['status'];
		    $seat_price  = $rowSeat['seat_price'];
			$operator_id = $rowSeat['operator_id'];
			$event_seat_cat_id = $rowSeat['event_seat_categories_id'];
			$row_seats_id = $rowSeat['row_seats_id'];
		}
		
		// Seat Number text box
		$textBoxInput = '<div class="form-group">
					  <div class="col-xs-2">
						<label for="ex1">'.$this->lang['seat_map_seat_number_txt'].'</label>
						<input class="form-control save_on_blur" id="seat_number_val_'.$laset_insert_id.'" type="text" name="seat_number" value="'.$seat_number.'">
					  </div>
					  </div>';
		// Seat price textobx			  
		$seatPriceInput = '<div class="form-group">
					  <div class="col-xs-2">
						<label for="ex1">'.$this->lang['seat_map_seat_price_txt'].'</label>
						<input class="form-control save_on_blur" id="seat_price_val_'.$laset_insert_id.'" type="text" name="seat_price" value="'.$seat_price.'" style="width:60px;">
					  </div>
					  </div>';			  
		 $seatTypeOption = $this->seatTypeChangeSelection($row_seats_id);			  
		 // Create the row which will append to the table later
		 $table_row_edit_mode  = '<tr id="tabl_id_row_'.$laset_insert_id.'" style="background-color:#c4e2bd">';
		 $table_row_edit_mode .= '<form id="seat_row_frm_'.$laset_insert_id.'" method="post" class="form-inline">';
	     $table_row_edit_mode .= '<td>'.$textBoxInput.'</td>';
		 $table_row_edit_mode .= '<td colspan="3">'.$seatTypeOption.$this->seatTypeDropDown($laset_insert_id, $placement,$operator_id).'</td>';
		 $table_row_edit_mode .= '<td>'.$seatPriceInput.'</td>';
		 $table_row_edit_mode .= '<td>';
		 $table_row_edit_mode .= '<a href="javascript:void(0);" id="update_save_link_'.$laset_insert_id.'" onClick="updateNewSeat('.$laset_insert_id.')" style="color:green"><i class="fa fa-check-circle-o" style="font-size:30px"></i></a>';
		 $table_row_edit_mode .= '<a href="javascript:void(0);" onClick="removeSeat('.$laset_insert_id.')" class="error" style="margin-left:44px"><i class="fa fa-times-circle-o" style="font-size:30px"></i></a>';
		 $table_row_edit_mode .= '</td>';
		 $table_row_edit_mode .= '</form>';
		 $table_row_edit_mode .= '</tr>';
	return $table_row_edit_mode;	   
}

// Display Seat in the Popup
public function getSeatHistory($request, $response, $args){
	 $id = $args['seat_id']; // Seat ID
	 // Check if this seat is booked or not
	 $customer_id = Models\EventCategoryRowSeat::where('id','=', $id)->first()->customer_id;
	 $resend_button = '';
	 if($customer_id > 0){
	    $resend_button = '<a href="javascript:void(0);"  onclick="sendConfirmationEmail('.$id.', '.$customer_id.')" class="btn btn-info pull-right" style="margin:0 auto;margin-right: 0px;"><i class="la la-mail"></i> Renvoyer l\'email </a> 
  <div id="emailMsgDiv"></div>';
	 }
	 // Get all the changed seat history
	 $changed_log_history = Models\SeatLogHistory::with(['Customer'])->where('seat_id','=', $id)->
	                       whereRaw('log_type="Changed"')->orderBy('id', 'DESC')->get();
	 
	 $seat_changed_data = '';
	 $seat_changed_data_total = 0;
	 if( $changed_log_history->isEmpty() ){
		 $seat_changed_data .= '<tr>';
		 $seat_changed_data .= '<td class="error" colspan="6" style="text-align:center">'.$this->lang['no_data_found_txt'].'</td>';
		 $seat_changed_data .= '</tr>';
	 }else{
		 $i=1;
		 $seatCircle_start  = '<div class="m-demo__preview m-demo__preview--badge" style="cursor:pointer" >';
		 $seatCircle_end  = '</div>';		
		 foreach($changed_log_history as $changed){
			 $changed_by_id = $changed['changed_by_id']; 
			 $adminName = Models\User::where('id', '=', $changed_by_id)->first()->name;
			 $customer_name = $changed['Customer']['name'];
			 $seat_numbers = $changed['seat_number'];
			 $changed_date = date('d/m/Y H:i ', strtotime($changed['changed_returned_date']));
			 $admin_name   = $adminName;
			 $changed_reason = $changed['changed_returned_reason'];
			 $seatCircle = $seatCircle_start. '<span class="m-badge m-badge--success">'.$seat_numbers.'</span>' .$seatCircle_end;		 
			 $seat_changed_data .= '<tr>';
			 $seat_changed_data .= '<td>'.$i.'</td>';
			 $seat_changed_data .= '<td>'.ucwords($customer_name).'</td>';
			 $seat_changed_data .= '<td>'.$seatCircle.'</td>';
			 $seat_changed_data .= '<td>'.$changed_date.'</td>';
			 $seat_changed_data .= '<td>'.ucwords($admin_name).'</td>';
			 $seat_changed_data .= '<td>'.$changed_reason.'</td>';
			 $seat_changed_data .= '</tr>';
		   $i++; // Increment the counter
		 }
		 $seat_changed_data_total = ($i-1);
	 }
	 
	 // Get all those entries which are refunded
	 $refund_log_history = Models\SeatLogHistory::with(['Customer'])->where('seat_id','=', $id)->
	                       whereRaw('log_type="Refunded"')->orderBy('id', 'DESC')->get();
	
	$seat_refund_data = '';
	$seat_refund_data_total = 0;
	if( $refund_log_history->isEmpty() ){
		 $seat_refund_data .= '<tr>';
		 $seat_refund_data .= '<td colspan="5" class="error" style="text-align:center">'.$this->lang['no_data_found_txt'].'</td>';
		 $seat_refund_data .= '</tr>';
	}else{
		$j=1;
		foreach($refund_log_history as $refund){
			 $changed_by_id = $refund['changed_by_id']; 
			 $adminName = Models\User::where('id', '=', $changed_by_id)->first()->name;
			 $customer_name = $refund['Customer']['name'];
			 $seat_numbers = $refund['seat_number'];
			 $refund_date = date('d/m/Y H:i', strtotime($refund['changed_returned_date']));
			 $admin_name   = $adminName;
			 $refund_reason = $refund['changed_returned_reason'];
			 $seatCircle = $seatCircle_start. '<span class="m-badge m-badge--success">'.$seat_numbers.'</span>' .$seatCircle_end;		 
			 $seat_refund_data .= '<tr>';
			 $seat_refund_data .= '<td>'.$j.'</td>';
			 $seat_refund_data .= '<td>'.ucwords($customer_name).'</td>';
			 $seat_refund_data .= '<td>'.$seatCircle.'</td>';
			 $seat_refund_data .= '<td>'.$refund_date.'</td>';
			 $seat_refund_data .= '<td>'.$refund_reason.'</td>';
			 $seat_refund_data .= '</tr>';
		   $j++; // Increment the counter
		}
		$seat_refund_data_total = ($j-1);
	}
	
	$admin_notes = Models\EventCategoryRowSeat::where('id','=', $id)->first()->admin_notes;
	$admin_notes_data = '<textarea class="form-control" id="m_autosize_comments" rows="3" name="admin_comments" onblur="sendData(this)" style="border-color: blue; margin-top: 0px; margin-bottom: 0px; height: 233px;">'.$admin_notes.'</textarea>';
	// Output the result as Json
	return json_encode( array('seat_changed_data' => $seat_changed_data, 
	                          'seat_refund_data' => $seat_refund_data,
							  'seat_changed_data_total' => $seat_changed_data_total,
							  'seat_refund_data_total' => $seat_refund_data_total,
							  'seat_email_button' => $resend_button,
							  'seat_id' => $id,
							  'admin_notes' => $admin_notes_data
							  )
					   );
	
}

// Seat Type for all or current seat
public function seatTypeChangeSelection($row_id){
    $type_selection = '<div class="m-form__group form-group">';
    $type_selection .= '<label for="" class="select_option_class"> '.$this->lang['seat_map_change_seat_type_txt'].' </label>';
    $type_selection .= '<div class="m-radio-inline">';
    $type_selection .= '<label class="m-radio select_option_class">';
    $type_selection .= '<input type="radio" name="select_opt"  id="select_opt_current" value="N" checked onChange="changeSeatType(this)">';
    $type_selection .= ' '.$this->lang['seat_map_current_seat_txt'].' <span></span>';
    $type_selection .= '</label>';
    $type_selection .= '<label class="m-radio select_option_class">';
    $type_selection .= '<input type="radio" name="select_opt"  id="select_opt_all" value="Y" onChange="changeSeatType(this)">';
    $type_selection .= ' '.$this->lang['seat_map_all_seats_txt'].' <span></span>';
	$type_selection .= '</label>';
	$type_selection .= '<input type="hidden" id="selected_seat_option" value="N">';
	$type_selection .= '<input type="hidden" id="event_seat_row_id" value="'.$row_id.'">';
	$type_selection .= '<input type="hidden" id="selected_placement_text" >';
    $type_selection .= '</div>';
    $type_selection .= '</div>';	
	return $type_selection;
}

// Archived Events function
	public function archived_events($request, $response) {
        $params = array( 'title' => 'All Archived Events',
		                 'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Event/archived_events.twig',$params);
    }

// Ajax Archived Events list
	public function ajaxArchivedEventsList($request, $response){
		$dateformate = strtotime(date('Y-m-d'));  
	    $today = date('Y-m-d', $dateformate);
		$From = $today." 00:00:00";
		$To   = $today." 23:59:59";
		$isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('title', 'date');
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
		    $total   = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereRaw('status=2')->whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereRaw('status=2')->count(); // get count 
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
		    $events_of_day_list = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereRaw('status=2')
			->whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			
			$events_of_day_list = Models\Event::with(['city', 'auditorium', 'eventgroup'])->whereRaw('status=2')
			->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		
		$data = array();
		foreach($events_of_day_list as $get){
			$event_group_title = ($get['eventgroup']['title'] == '') ? 'click to edit' : $get['eventgroup']['title'];
		  	$array_data = array();
			$title = ($get['title'] == '') ? 'click to edit' : $get['title'];
			$array_data['id']  = $get['id'];
            $array_data['title']  = '<a href="javascript:void(0);" title="View Event" onClick="view('.$get['id'].')">'.$title.'</a>';
			$array_data['date']  = hr_date($get['date']);
			$array_data['city_name']  = $get['city']['name'];
			$array_data['auditorium_name']  = $get['auditorium']['name'];
			$array_data['group_name']  =  '<a href="javascript:void(0);" title="View Event Group" onClick="edit('.$get['eventgroup']['id'].')">'.$event_group_title.'</a>';
			$array_data['status']  = $get['status'];
			
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
	
	
	// Edit event Digital map page
	public function eventDigitalMapEdit($request, $response, $args){
		$id = $args['id']; // Event id
		$event_table_pk_id = $id;
        $validations = [
            v::intVal()->validate($id)
        ];
		
        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		
		$event = Models\Event::find($id);
		$mapData = Models\EventAuditoriumMap::where('event_id', '=', $id)->get();
		
		$params = array( 'title' => $this->lang['seat_map_set_map_txt'],
		                    'event' => $event,
		                    'event_name'=> $event['title'],
							'data' => $mapData,
							'event_id' => $id,
							'eventgroup_id' => $event['eventgroup_id'],
							'seatmapKey' => $mapData[0]->auditorium_key,
						     'seatmapVal' => $mapData[0]->auditorium_map);
          return $this->render($response, ADMIN_VIEW.'/Event/edit_digital_map.twig',$params);
		
	}
	
	// Save auditorium Digital Map from admin
	public function saveAuditoriumDigitalMap($request, $response){
		
		
	   $event_id = $request->getParam('event_id');

	   $auditorium_key = $request->getParam('auditorium_key');

	   $auditorium_map  = $request->getParam('auditorium_map');


	   $decoded_map = json_decode($auditorium_map);

	   //var_dump($decoded_map->billets); exit;
	   $total_number_seats = 0;
	   $total_number_sections = count($decoded_map->sections);

	   //calculate number of sections 
	   foreach($decoded_map->sections as $section){
	   		$total_number_seats += ($section->_nbRange *  $section->_nbSeat);
	   }

	   /* save / update auditorium map */ 
	   //get the auditorium id

	  $auditorium_id = Models\Event::where('id', $event_id)->first()->auditorium_id;
  
	 //  Models\Auditorium::where('id', $auditorium_id)->update( array('auditorium_key' => $auditorium_key, 'auditorium_map' => $auditorium_map) );

	   //seats

	     //prices 
	   	
	   		$prices = array();
	   		foreach($decoded_map->billets as $billets){
	   			$prices[$billets->color] = $billets->tarif;
	   		}

	   		$type = array(); //Standard , Réservé , invitation , operator

	   		foreach($decoded_map->billets as $billets){
	   			$type[$billets->color] = $billets->type;
	   		}
		
	   
	   foreach($decoded_map->sections as $section){


		   		
		   		
		   		foreach($section->_seats as $seat){


		   			if($seat->_rangeText){ 
		   				//save or updating seats 

		   				$s  = Models\Seats::where('unique_id' , $seat->_id)->first()->id;
		   				
		   				if($s){
		   					//update
		   					if($seat->_rangeText){
		   						if($prices[$seat->tarifColor]){
		   							$price_seat = $prices[$seat->tarifColor];
		   						}else {
		   							$price_seat = 0;
		   						}

		   						Models\Seats::where('unique_id' , $seat->_id)->update(array(
			   						'name' => $seat->_text,
				   					'area' => $section->_nbTitle,
				   					'row' => $seat->_rangeText,
				   					'auditorium_id' => $auditorium_id,
				   					'price' => $price_seat,
			   						'status' => $type[$seat->tarifColor],
			   						'hidden' => $seat->_hidden,
                                    'event_id' => $event_id
		   						));
		   					}
		   					
		   				}else {
		   					//save	
		   					if($seat->_rangeText){ //1rst line doesn't have seat range

		   						if($prices[$seat->tarifColor]){
		   							$price_seat = $prices[$seat->tarifColor];
		   						}else {
		   							$price_seat = 0;
		   						}

			   					$newseat = new Models\Seats;
			   					$newseat->unique_id = $seat->_id;
			   					$newseat->name = $seat->_text;
			   					$newseat->area = $section->_nbTitle;
			   					$newseat->row = $seat->_rangeText;
			   					$newseat->auditorium_id = $auditorium_id;
			   					$newseat->price = $price_seat;
			   					$newseat->status = $type[$seat->tarifColor];
                                $newseat->hidden = $seat->_hidden;
                                $newseat->event_id = $event_id;

			   					$newseat->save();
			   				}

		   				}
		   			}
		   		}
	   	}
	   	

	   $digitalMapTablePKID = Models\EventAuditoriumMap::where('event_id', $event_id)->first()->id;

	   if($digitalMapTablePKID){

		    // Update the Digital Map

		   $data = array('auditorium_key' => $auditorium_key, 'auditorium_map' => $auditorium_map);

		   $event = Models\EventAuditoriumMap::where('id', '=', $digitalMapTablePKID)->update($data);	

		   //sync with other tables

	   }else{

		   

		   // Save the Digital Map

		   $aud = new Models\EventAuditoriumMap;

		   $aud->event_id = $event_id;

		   $aud->auditorium_key = $auditorium_key;

	       $aud->auditorium_map  = $auditorium_map;

		   $aud->save();





	   }



	   $asm = Models\AuditoriumSeatsMap::where('event_id', $event_id)->first()->id;
		   
	   if($asm){

		   $auditorium_seats_map = Models\AuditoriumSeatsMap::where('event_id', $event_id)->update(
		   	 array(
		   	 	'billets_json' => json_encode($decoded_map->billets) ,
		   	 	'labels_json' => json_encode($decoded_map->labels) , 
		   	 	'sections_json' => json_encode($decoded_map->sections),
		   	 	'total_number_seats' => $total_number_seats,
		   	    'total_number_sections' => $total_number_sections
		   ));


	   }else {

	   		$auditorium_seats_map = new Models\AuditoriumSeatsMap;

		   $auditorium_seats_map->event_id = $event_id;
		   $auditorium_seats_map->billets_json = json_encode($decoded_map->billets);
		   $auditorium_seats_map->labels_json = json_encode($decoded_map->labels);
		   $auditorium_seats_map->sections_json = json_encode($decoded_map->sections);
		   $auditorium_seats_map->total_number_seats = $total_number_seats;
		   $auditorium_seats_map->total_number_sections = $total_number_sections;


		   $auditorium_seats_map->save();

	   }
		   





	   



	   

	   return $response

            ->withHeader('Content-type','application/json')

            ->write(json_encode(array('status' => TRUE)));
	   
	}
	
	// Create new Row
	public function createNewRow($request, $response, $args){
		$event_id = $args['event_id'];
		$category_id = $args['cat_id'];
		$row_id = $args['row_id'];
		$table_id = $args['table_id'];
		$row = '<table class="seats_tbl_row" id="createNewRow">
		<tr><th>Row</th><th>Placement</th><th>From</th><th>To</th><th>Even Order?</th></tr>
		<tr><td><input type="text" name="row_number" id="row_number"  placeholder="Row Number" class="form-control"/></td>
		<td>'.$this->seatTypeDropDownPlacement($table_id).'</td>
			<td><input type="text" name="from_value_new" id="from_value_new"  placeholder="From" class="form-control"/></td>
			<td><input type="text" name="to_value_new" id="to_value_new"  placeholder="To" class="form-control"/></td>
			<td><input type="checkbox" name="seat_order_new" id="seat_order_new"  class="form-control" /><br>
			<input type="hidden" name="cat_id" id="cat_id"  value="'.$category_id.'">
			<input type="hidden" name="row_id" id="row_id" value="'.$row_id.'">
			<a href="javascript:void(0);"  onclick="SaveNewSeat()" style="color:green;margin-left: -5px;"><i class="fa fa-save" style="font-size:25px"></i></a>
		   <a href="javascript:void(0);" onClick="hideRowSeat(\''.$table_id.'\')" class="error" style="margin-left: 5px;"><i class="fa fa-times-circle-o" style="font-size:30px"></i></a>
			</td>
		</tr>
		</table>';
		return $row;
	}
	
	// Seat Type Drop Down Placement
	public function seatTypeDropDownPlacement($table_id){
		  $operators_list = '';
		  $css = 'none';
		  // Get the productors list here
		  $operators_list = $this->getOperatorsList();
		  $options = '<select class="Placement_new form-control" name="seat_type" id="seat_type"  onChange="changePlacementFresh(this,\''.$table_id.'\')">';
		  $options .= '<option value="1" '.$selected1.'>Standard</option>';
		  $options .= '<option value="2" '.$selected2.'>Réservées</option>';
		  $options .= '<option value="3" '.$selected3.'>Invitations</option>"';
		  $options .= '<option value="4" '.$selected4.'>Vendues à autre opérateur</option>';
		  $options .= '</select>';
		  $options .= '<span id="operator_span_'.$table_id.'" style="display:'.$css.'">'.$operators_list.'</span>'; 
	  return $options;
	}
	
	// Get operators
   public function getOperatorsList(){
	  $lists= Models\Operators::get();
	  $operator_option = '<span class="text-left">Select Operator</span><br>';
	  $operator_option .= '<select class="form-control" name="operator_id" id="operator_id" >';
	  foreach($lists as $list)
	  {
		 $operator_option .= '<option value="'.$list['op_id'].'" >'.$list['op_fname'].'</option>'; 
	  }
	  $operator_option .= '</select>';
	  return $operator_option;
	  
   }
   
   
   // Save new row seat
   public function saveNewRowSeatUpdate($request, $response, $args){ 
        //ddump($_REQUEST); exit;	
		$from = $request->getParam('from_value_new'); // Seat From
		$to = $request->getParam('to_value_new'); // Seat To
		$seat_order = $request->getParam('seat_order_new'); // seat order checkbox
		$seat_type = $request->getParam('seat_type'); // seat type
		$operator_id = $request->getParam('operator_id'); // operator id
		$row_number = $request->getParam('row_number'); // Row Number
		$cat_id     = $request->getParam('cat_id');
		$row_id = $request->getParam('row_id');
		
		if($seat_type < 4){
			 $operator_id = 0;
		}else{
			$operator_id = $operator_id;
		}
		// Get the Event ID & Price
		$getSeatEventData = Models\EventSeatCategories::where('id', '=', $cat_id)->get();
		foreach($getSeatEventData as $event){
		   $event_id  = $event['event_id'];
		   $seat_price = $event['category_price'];
		   $from_range = $event['from_range'];
		   $cat_table_id = $event['id'];	
		}
		
		// First check if this row already exist or not
		$row_exist_id = Models\RowSeats::where('event_seat_categories_id', '=', $cat_id)
										 /*->where('row_seats_id', '=', $row_id)*/
										 ->whereRaw("row_number='".$row_number."' ")
										 ->first()
										 ->id;
	   //$row_exist_id; exit;
       if($row_exist_id){    
		  $rowIfExist = 'E'; // Error as Row already exist
	   }else{
		  // Save to Row Seat Table
		  $rowSeat = new Models\RowSeats;
		  $rowSeat->event_seat_categories_id = $cat_id;
		  $rowSeat->row_number = $row_number;
		  $rowSeat->seat_from = $from;
		  $rowSeat->seat_to = $to;
		  $rowSeat->seat_from_val = $from;
		  $rowSeat->seat_to_val = $to;
		  $rowSeat->total_qantity = sizeof($from, $to);
		  $rowSeat->placement = $seat_type;
		  $rowSeat->operator_id = $operator_id;
		  $rowSeat->seat_order = $seat_order;
		  $rowSeat->save();
		  $rowseatID = $rowSeat->id;
		  // Loop through from and to
		   $range = range($from, $to);
			 foreach($range as $seat_number){
				if($seat_order == 2){
					// It means seats should be saved in event order
				   if($seat_number % 2 == 0){
					 $this->saveRowSeatsIndividually($event_id, $cat_id, $rowseatID, $row_number, $seat_number, $seat_type,$operator_id,$seat_price);  
				   }
				}else{
					// It means seats should be saved in sequence order
					$this->saveRowSeatsIndividually($event_id, $cat_id, $rowseatID, $row_number, $seat_number, $seat_type,$operator_id,$seat_price);
				}
				
			 }
		  // Update the event seat categories table
		  $dataUpdate = array('from_range' => $from_range.','.$row_number);
		  $eventUpdate = Models\EventSeatCategories::where('id', '=', $cat_table_id)->update($dataUpdate);
		  
		  $rowIfExist = 'S';   // Success as row already not exist 
	   }
	   
	    return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode($rowIfExist));
   }
   
   
   // Get Event Json Map by event_id
	public function eventJsonMap($request, $response, $args){
		$event_id = $args['event_id'];


        $validations = [
            v::intVal()->validate($event_id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }

		$eventJsonData = Models\EventAuditoriumMap::where('event_id',$event_id)->get();

		if(Models\Event::where('id', $event_id)->first()){
			echo json_encode($eventJsonData);
		}else {
			$return = array('error' => 'Not found');
			echo json_encode($return); exit;
		}
		
       
        	
	}
	
	
	// Create new multiple seats
	public function saveNewMultipleSeatsFresh($request, $response, $args){
		
		$event_id = $args['event_id'];
		$category_id = $args['cat_id'];
		$row_id = $args['row_id'];
		$table_id = $args['table_id'];
		$seat_number = $args['seat_number']-1;
		// Get row number
		$row_number = Models\EventCategoryRowSeat::where('event_id', '=', $event_id)->
		                                           where('event_seat_categories_id', '=', $category_id)->
												   where('row_seats_id', '=', $row_id)->
												   first()->row_number;
		// Get the latest seat number
		$latest_seat_number = '';
													   
		$latest_seat_number = checkAlphabetIncrement($seat_number);
		
		
		$table = '<table class="seats_tbl_row" id="createNewRow">
		<tr><th>From</th><th>To</th><th>Placement</th><th>Price</th></tr>
		<tr>
		<td><input type="text" name="from_value_new" id="from_value_new" value="'.$latest_seat_number.'"  placeholder="From" class="form-control"/></td>
		<td><input type="text" name="to_value_new" id="to_value_new"  placeholder="To" class="form-control"/></td>
		<td>'.$this->seatTypeDropDownPlacement($table_id).'</td>
		<td><input type="text" name="seat_price" id="seat_price"  placeholder="Price" class="form-control"/></td>
		</tr>
		<tr>
		 <td colspan="4">
		    <input type="hidden" name="cat_id" id="cat_id"  value="'.$category_id.'">
			<input type="hidden" name="event_id" id="event_id"  value="'.$event_id.'">
			<input type="hidden" name="row_number" id="row_number"  value="'.$row_number.'">
			<input type="hidden" name="row_id" id="row_id" value="'.$row_id.'">
			<a href="javascript:void(0);"  onclick="SaveNewMultipleSeats()" style="color:green;margin-left: 5px;"><i class="fa fa-save" style="font-size:25px"></i></a>
		    <a href="javascript:void(0);" onClick="hideRowSeatMultiple(\''.$table_id.'\')" class="error" style="margin-left: 15px;"><i class="fa fa-times-circle-o" style="font-size:30px"></i></a>
		   </td>
		  </tr>
		</table>';
		return $table;
	}
	
	// Save new row multiple seats
   public function saveRowMultipleSeatsUpdate($request, $response, $args){ 
        //ddump($_POST); exit;
        $from = $request->getParam('from_value_new'); // Seat From
		$to = $request->getParam('to_value_new'); // Seat To
		$seat_type = $request->getParam('seat_type'); // seat type
		$operator_id = $request->getParam('operator_id'); // operator id
		$seat_price = $request->getParam('seat_price'); // Seat Price
		$cat_id     = $request->getParam('cat_id');
		$row_id = $request->getParam('row_id');
		$row_number = $request->getParam('row_number');
		if($seat_type < 4){
			 $operator_id = 0;
		}else{
			$operator_id = $operator_id;
		}
		// Save to Row Seat Table
		$range = range($from, $to);
		foreach($range as $seat_number){
			 // Save to table 
			 $eventCatRowSeat = new Models\EventCategoryRowSeat;
			 $eventCatRowSeat->event_id = $event_id;
			 $eventCatRowSeat->event_seat_categories_id = $cat_id;
			 $eventCatRowSeat->row_seats_id = $row_id;
			 $eventCatRowSeat->row_number = $row_number;
			 $eventCatRowSeat->seat_number = $seat_number;
			 $eventCatRowSeat->placement = $seat_type;
			 $eventCatRowSeat->operator_id = $operator_id;
			 $eventCatRowSeat->seat_price = $seat_price;
			 $eventCatRowSeat->save();
		}
   }
   
   
   // Save Admin Notes for a seat
	public function saveSeatComments($request, $response, $args){
		$admin_comments = $request->getParam('admin_comments');
		$seat_id = $request->getParam('seat_id');
		$updateSeat = array('admin_notes' => $admin_comments);
		$updateSeatsTable = Models\EventCategoryRowSeat::where('id', '=', $seat_id)->
			                              update($updateSeat);
		 return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	}
	
	// Get Event Coupon
	public function getEventCoupon($request, $response, $args){
		// Get the Event ID
	    $event_id = $args['event_id'];	
		
		
		
		
		// Get Active Coupon of this event
		$couponActive = Models\Coupon::where('event_id','=',$event_id)->get();
		$CoupnHtmlTable = '<style>
			#tbl_cpn {
				font-family: arial, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#tbl_cpn td, th {
				border: 1px solid #dddddd;
				text-align: left;
				padding: 8px;
			}
			
			#tbl_cpn tr:nth-child(even) {
				background-color: #dddddd;
			}
			</style>
   <table  id="tbl_cpn" >
		                  <thead>
						      <th>'.$this->lang['assign_coupon_id_txt'].'</th>
							  <th>'.$this->lang['assign_coupon_name_txt'].'</th>
							  <th>'.$this->lang['assign_coupon_code_txt'].'</th>
							  <th>'.$this->lang['assign_coupon_type_txt'].'</th>
							  <th>'.$this->lang['assign_coupon_amount_txt'].'</th>
							  <th>'.$this->lang['assign_coupon_used_txt'].'</th>
							  <th>'.$this->lang['assign_coupon_option_txt'].'</th>
						</thead>';
		if(!$couponActive->isEmpty()){
			$storedCats = $couponActive[0]['category_ids'];
			$coupon_selected_id = $couponActive[0]['id'];
			$CoupnHtmlTable .= '<td>'.$couponActive[0]['id'].'</td>
			                   <td>'.$couponActive[0]['coupon_name'].'</td>
							   <td>'.$couponActive[0]['coupon_code'].'</td>
							   <td>'.$couponActive[0]['discount_type'].'</td>
							   <td>'.$couponActive[0]['discount_amount'].'</td>
							   <td>'.$couponActive[0]['coupon_used'].'</td>
							   <td><a style="padding-left:10px" href="javascript:void();" onClick="return removeEventCoupon('.$couponActive[0]['id'].')"><i class="fa fa-trash" style="color:red; font-size:20px"></i></a></td>';
		}else{
			$storedCats = $coupon_selected_id = false;
		  $CoupnHtmlTable .= '<td colspan="7" style="text-align:center">'.$this->lang['assign_coupon_nofound_txt'].'</td>';	
		}
		
		$CoupnHtmlTable .= '</table>';
		
		$event_id_hidden = '<input type="hidden" name="event_id" value="'.$event_id.'">';
	
	  // Get this event all categories
		$categories = Models\EventSeatCategories::where('event_id', $event_id)->get();
	    $categories_list = '';
		if($categories){
			foreach($categories as $cat){
				$catID = $cat['id'];
				$sel = '';
				if($storedCats){
				    $storeCateArray = explode(',', $storedCats);
					if(in_array($catID, $storeCateArray) ){
						$sel = 'selected="selected"';
					}
				}
		        $categories_list .= '<option value="'.$cat['id'].'" '.$sel.'>'.$cat['seat_category'].'</option>';
			}
		}else{
		  $categories_list = '';	
		}
		
		// First get all coupons
		$coupons = Models\Coupon::where('status', 1)->where('event_id', 0)->get();
		$coupons_list = '';
		$coupons_found_flag = false;
		if($coupons){
			$coupons_found_flag = true;
			$coupons_list .= '<select name="coupon_id" id="coupon_id" class="form-control">';
			$coupons_list .= '<option value="">'.$this->lang['assign_coupon_select_txt'].'</option>';
		     foreach($coupons as $coupon){
			      $coupon_name = $coupon['coupon_name']. ' ('. $coupon['coupon_code'].')';
				  $eventCouponId = $coupon['event_id'];
				  $sel = '';
				  if($eventCouponId == $event_id){
					 $sel = 'selected="selected"';  
				  }
			      $coupons_list .= '<option value="'.$coupon['id'].'" '.$sel.'>'.$coupon_name.'</option>';
		     }
		   $coupons_list .= '</select><input type="hidden" id="coupon_selected_id" name="coupon_selected_id" value="'.$coupon_selected_id.'">';
		  }else{
			$coupons_list .= '<select name="coupon_id" id="coupon_id" class="form-control">';
			$coupons_list .= '<option>'.$this->lang['assign_coupon_nofound_txt'].'</option>';
			$coupons_list .= '</select>';
		}
		
	  $responseData =  json_encode(array('title' => $this->lang['assign_coupon_txt'],  
	                                     'coupons_list' => $coupons_list, 
										 'categories_list' => $categories_list,
										 'activeCoupon' => $CoupnHtmlTable.$event_id_hidden
										 ));
	   return $response
            ->withHeader('Content-type','application/json')
            ->write($responseData);
	
	}
	
	// Save Event Coupon
	public function saveEventCoupon($request, $response){
		$event_id = $request->getParam('event_id');
		$coupon_id = $request->getParam('coupon_id');
		$coupon_selected_id = $request->getParam('coupon_selected_id');
		$category_ids = join(',',$request->getParam('category_ids'));
		if( empty($category_ids) ){
			$category_ids = 0;
		}
		if(empty($event_id)){
			$event_id = 0;
		}
		
		if(!empty($coupon_id) || !empty($coupon_selected_id) ){
			// First remove the coupon already linked to this event
			$updateCoupon = array('event_id' => 0,'category_ids' => 0);
		  $updateCouponsTable = Models\Coupon::where('event_id', '=', $event_id)->
			                                   update($updateCoupon);
		 if(empty($coupon_id)){
			$coupon_id = $coupon_selected_id; 
		 }else{
			 $coupon_id = $coupon_id;
		 }
		 // Now assign this coupon to this event								   
		  $updateCoupon = array('event_id' => $event_id,'category_ids' => $category_ids);
		  $updateCouponsTable = Models\Coupon::where('id', '=', $coupon_id)->
			                                   update($updateCoupon);
		}


		 return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	}
	
	// Remove Event Coupon
	public function removeEventCoupon($request, $response, $args){
		$coupon_id = $args['coupon_id'];
		if(!empty($coupon_id)){
		  $updateCoupon = array('event_id' => 0,'category_ids' => 0);
		  
		  $updateCouponsTable = Models\Coupon::where('id', '=', $coupon_id)->
			                                   update($updateCoupon);
		}
		 return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	}

	
    /**
     * Page digital map seat listing
     *
     *
     */

    public function digitalSeatListing($request , $response, $arg){
        $event_id = $arg['id'];

        //get seats
        $seats = Models\Seats::select('*')->where('event_id', $event_id)->orderby('area', 'ASC')->get();

        //get event name 
        $event = Models\Event::where('id',$event_id)->first();

        //auditorium name 
        $auditorium = Models\Auditorium::where('id',$event->auditorium_id)->first()->name;

        $params = array(
            'event_id' => $event_id,
            'eventname'=> $event->title,
            'auditoriumname' => $auditorium,
            'title' => $this->lang['edit_digital_seat_txt'],
            'seats' => $seats
        ); 

        return $this->render($response, ADMIN_VIEW.'/Event/digitalseatlisting.twig',$params);
    }

    /**
     * Update Seat table 
     * Then update back all jsons
     *
     *
     */
    public function updateDigitalSeat($request, $response, $arg){

         $seat_id = $arg['id'];
         $event_id = $request->getParam('event_id');
         $price = $request->getParam('price');
         $status = $request->getParam('status');


         //update json in eventauditoriummap 
         
         $auditorium_map  = Models\EventAuditoriumMap::where('event_id', $event_id)->first()->auditorium_map;
         

         $decoded = json_decode($auditorium_map, true);


         //find the seat to update it

         /* to update seat need to update tarifColor so lets check all pricea and status setup in auditorium map */
            // tarif as price, color and type as status

         
         $ticketfound = false;
         $newtarifcolor = "#".substr(str_shuffle('abcdef0123456789'), 0, 6);


         foreach($decoded['billets'] as $key => $billet){

            //remove the current seat from all billets first
             
                for($i=0; $i < count($billet['seats']); $i++){
                    if($billet['seats'][$i] == $seat_id){
                        unset($decoded['billets'][$key]['seats'][$i]);
                    }


                    $decoded['billets'][$key]['seats'] = array_values($decoded['billets'][$key]['seats']);
                }

             //find if any billets record may match the new price and status
                if($decoded['billets'][$key]['tarif'] == $price && $decoded['billets'][$key]['type'] == $status){
                    $decoded['billets'][$key]['seats'][]= intval($seat_id);

                    $decoded['billets'][$key]['seats'] = array_values($decoded['billets'][$key]['seats']);
                    $ticketfound = true;
                    $newtarifcolor = $decoded['billets'][$key]['color'];
                }
         }



         if(!$ticketfound){

            //if not any ticket match ,no choice we have to create a new ticket then
             $newbillet = array(
                 'libelle' => 'Billet',
                 'color' => $newtarifcolor,
                 'tarif' => intval($price),
                 'seats' => array(),
                 'type' => $status
             );

             $newbillet['seats'][] = intval($seat_id);

             //update seat json status 
             foreach($decoded['sections'] as $a => $section){


                 for($j=0; $j < count($section['_seats']); $j++){
                     if($section['_seats'][$j]['_id'] == $seat_id){

                         $decoded['sections'][$a]['_seats'][$j]['tarifColor'] = $newtarifcolor;
                     }
                 }
             }

             $decoded['billets'][] = $newbillet;

         }else {
             //update seat json status 
             foreach($decoded['sections'] as $a => $section){

                 for($j=0; $j < count($section['_seats']); $j++){
                     if($section['_seats'][$j]['_id'] == $seat_id){

                         $decoded['sections'][$a]['_seats'][$j]['tarifColor'] = $newtarifcolor;
                     }
                 }
             }
         }


  
         //now save the json in eventauditoriummap table

         $new_json_encoded = json_encode($decoded);

         Models\EventAuditoriumMap::where('event_id' ,$event_id)->update(array('auditorium_map' => $new_json_encoded));


         //updating  auditorium seat map table



           //var_dump($decoded_map->billets); exit;
           $total_number_seats = 0;

           $total_number_sections = count($decoded['sections']);

           //calculate number of sections 
           foreach($decoded['sections'] as $section){
                $total_number_seats += ($section['_nbRange'] *  $section['_nbSeat']);
           }




	   $asm = Models\AuditoriumSeatsMap::where('event_id', $event_id)->first()->id;
		   
	   if($asm){

		   $auditorium_seats_map = Models\AuditoriumSeatsMap::where('event_id', $event_id)->update(
		   	 array(
		   	 	'billets_json' => json_encode($decoded['billets']) ,
		   	 	'labels_json' => json_encode($decoded['labels']) , 
		   	 	'sections_json' => json_encode($decoded['sections']),
		   	 	'total_number_seats' => $total_number_seats,
		   	    'total_number_sections' => $total_number_sections
		   ));


	   }else {

	   		$auditorium_seats_map = new Models\AuditoriumSeatsMap;

		   $auditorium_seats_map->event_id = $event_id;
		   $auditorium_seats_map->billets_json = json_encode($decoded['billets']);
		   $auditorium_seats_map->labels_json = json_encode($decoded['labels']);
		   $auditorium_seats_map->sections_json = json_encode($decoded['sections']);
		   $auditorium_seats_map->total_number_seats = $total_number_seats;
		   $auditorium_seats_map->total_number_sections = $total_number_sections;


		   $auditorium_seats_map->save();
       }
	   

         
          
         //update seat table
         $data = array(
             'price' => $price,
             'status' => $status
         );

         Models\Seats::where('unique_id', $seat_id)->where('event_id',$event_id)->update($data);


         
         $params = array(
                'event_id' => $event_id,
                'seat_id' => $seat_id,
                'price' => $price,
                'status' => $status 
         );

		 return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode($params));
    }
	
}
