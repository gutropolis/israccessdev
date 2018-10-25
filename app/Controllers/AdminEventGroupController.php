<?php
namespace App\Controllers;

use App\Models;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;
/**
  Admin Event Group Controller
  CRUDs for Event Group controller
  Available Functions
  1. eventgroups
  2. getAjaxEventGroupsList
  3. saveEventGroup
  4. updateEventGroup
  5. getEventGroupById
  6. editEventGroupById
  7. deleteEventGroupById
  8. uploadImages 
  9. getUploads
  10. uploadGroupPicture
  11. uploadGroupThumbnailPicture
  12. removeFile
  13. removeVideoLink
  14. deleteGroupComment
  15. saveSectionPicComments
  16. eventGroupVideoLinks
  17. duplicateEventGroup
  18. renameFile
  19. updateGroupStatus
  20. saveEventGroupRoles
  21. deleteEventGroupRole
 
  
*/

class AdminEventGroupController extends Base 
{
	protected $container;
	protected $lang;
	public function __construct($container)
	{
		$this->container = $container;
		$this->lang =  $this->container->view['adminLang'];
	}
	
	 // Main function to display event group list
	public function eventgroups($request, $response) {
		// Get categories + Artists list
		$categories =  Models\Category::get();
		$artists =  Models\User::where('type', '=' ,'Artist')->get();
		$sections =  Models\Section::orderBy('section_title','ASC')->orderBy('display_order','ASC')->get();
		$productors =  Models\User::where('type','=','Productor')->orderBy('name','ASC')->get();
		$groups_data =  Models\Event::where('status', '=' ,1)->get();
		//$groups['title'] = strip_tags(clearString(htmlspecialchars_decode($groups[0]['title'])));
		$group_array = array();
		foreach($groups_data as $group){
			$group_array['id'] = $group['id']; 
			$group_array['title'] = strip_tags(htmlspecialchars_decode($group['title']));
			$groups[] = $group_array;
		}
		
        $params = array( 'title' => $this->lang['event_group_all_txt'],
		                 'categories' => $categories,
						 'artists' => $artists,
						 'sections' => $sections,
						 'current_url' => $request->getUri()->getPath(),
						 'productors' => $productors,
						 'groups' => $groups);
        return $this->render($response, ADMIN_VIEW.'/Eventgroup/events_group.twig',$params);
    }
	
	// Ajax Event Groups list
	public function getAjaxEventGroupsList($request, $response){
		
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
		    $total   = Models\Eventgroup::with(['category'])->whereRaw($whereData)->whereRaw('status<>2')->count(); // get count 
		}else{
			$total   = Models\Eventgroup::with(['category'])->whereRaw('status<>2')->count(); // get count 
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
		    $event_group_list = Models\Eventgroup::with(['category'])->whereRaw('status<>2')->whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			
			$event_group_list = Models\Eventgroup::with(['category'])->whereRaw('status<>2')->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		$data = array();
		foreach($event_group_list as $get){
		  	$array_data = array();
			$title = ($get['title'] == '') ? 'click to edit' : strip_tags(clearString(htmlspecialchars_decode($get['title'])));
			$array_data['id']  = $get['id'];
            $array_data['title']  = '<a href="javascript:void(0);" title="'.$this->lang['event_group_update_txt'].'" onclick="edit('.$get['id'].')">'.$title.'</a>';
			$array_data['category_name']  = $get['category']['name'];
			$array_data['date_begin']  = hr_date($get['date_begin']);
			$array_data['date_end']  = hr_date($get['date_end']);
			$array_data['display_order']  = $get['display_order'];
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
	
	// Save Event Group
	public function saveEventGroup($request, $response){
	   $isError = false;
	   $meta_title = $request->getParam('meta_title');
	   $meta_description = $request->getParam('meta_description'); 
	   $permalink = $request->getParam('permalink'); 
	   $title =  $request->getParam('title');
	   $price_min =  $request->getParam('price_min');
	   $category_id =  $request->getParam('category_id');
	   $date_begin =  $request->getParam('date_begin');
	   $date_end =  $request->getParam('date_end');
	   $description =  $request->getParam('description');
	   $parent_id = 0; //$request->getParam('parent_id');
	   $thumbnail_title = $request->getParam('thumbnail_title');
	   $event_group_slug = $request->getParam('event_group_slug');
	   $section = $request->getParam('section');
	   $status = $request->getParam('status');
	   $producer_id = $request->getParam('producer_id');
	   $display_order =  $request->getParam('display_order');
	   $eventGroupExist = Models\Eventgroup::where('title', '=', $title)->first();
	    if(!isset($_FILES) && $_FILES['group_picture']['name'] == ''){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event group picture.'));
		 exit();	   
	   }else if(empty($title)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group title.'));
		 exit();	   
	   }else if(empty($thumbnail_title)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter card title.'));
		 exit();	   
	   }else if($eventGroupExist){
		   $isError = true;
		 echo json_encode(array("status" => 'duplicate', 
		                  'message' => 'Event Group (<strong>'.$title. '</strong>) already exist.'));
		 exit();	   
	   }else if(empty($price_min)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group minimum price.'));
		 exit();	   
	   }else if(empty($category_id)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event group category.'));
		 exit();	   
	   }else if(empty($date_begin)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select begin date.'));
		 exit();	   
	   }else if(empty($date_end)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select end date.'));
		 exit();	   
	   }else if(empty($description)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group description.'));
		 exit();	   
	   }else{
		   
		   // Upload the picture here
		   $group_picture = $this->uploadGroupPicture();
		   $group_thumbnail  = $this->uploadGroupThumbnailPicture();
		  $isError = false;
	   }
	   if( !$isError ){
		   if( !empty($producer_id) ){
			    $producer_id = $producer_id;
		   }else{
			  $producer_id = $_SESSION['adminId'];   
		   }
		   $display_order = ($display_order == '') ? 0 : $display_order;
		   $slug = empty($event_group_slug) ?  str_replace(' ', '_', strip_tags($title)) :  str_replace(' ', '_', $event_group_slug);
		   $slug = str_replace('_', '-', $slug);
		   $slug = str_replace('/', '-', $slug);
		   $slug = Generate_SEO_Url($slug);
		  // Save to event_groups table
		   $event_group = new Models\Eventgroup;
		   $event_group->group_thumbnail = $group_thumbnail;
		   $event_group->group_picture = $group_picture;
		   $event_group->title = htmlspecialchars($title);
		   $event_group->date_begin = mysql_date($date_begin).' '.date('H:i:s');
		   $event_group->date_end = mysql_date($date_end).' '.date('H:i:s');
		   $event_group->description = htmlspecialchars($description);
		   $event_group->price_min = $price_min;
		   $event_group->category_id = 0; //$category_id;
		   $event_group->thumbnail_title = $thumbnail_title;
		   $event_group->section =  $section;
		   $event_group->status = $status;
		   $event_group->producer_id = $producer_id;
		   $event_group->event_group_slug = $slug;
		   $event_group->parent_id = $parent_id;
		   $event_group->adv_image = $this->uploadEventAds();
		   $event_group->display_order = $display_order;
		   $event_group->meta_title = $meta_title;
		   $event_group->meta_description = $meta_description;
		   $event_group->permalink = trim($permalink);
		   $event_group->save();
		   $event_group_id = $event_group->id;
		   $this->saveEventGroupChildren($event_group_id);
		   $this->saveMultipleCategories($event_group_id);
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE, 'id' => $event_group_id))); 
	   }
	   
	}
	
	// Update Event Group
	public function updateEventGroup($request, $response){
		/*ddump($_REQUEST);
		exit;*/
	   $isError = false;
	   $id =  $request->getParam('event_id');
	   $meta_title = $request->getParam('meta_title'); // get the meta title
	   $meta_description = $request->getParam('meta_description'); // get the meta description
	   $permalink = $request->getParam('permalink'); // The permalink
	   $title =  $request->getParam('title'); // Event Group title
	   $price_min =  $request->getParam('price_min'); // Minimum price
	   $category_id =  $request->getParam('category_id'); // Category ID
	   $date_begin =  $request->getParam('date_begin'); // Date started
	   $date_end =  $request->getParam('date_end'); // Date End
	   $description =  $request->getParam('description'); // Event Group Description
	   $group_picture_old = $request->getParam('group_picture_old'); // Group picture old
	   $group_thumbnail_old = $request->getParam('group_thumbnail_old');
	   $en_savoir_block1_name = $request->getParam('en_savoir_block1_name');
	   $en_savoir_desc1 = $request->getParam('en_savoir_desc1');
	   $en_savoir_block2_name = $request->getParam('en_savoir_block2_name');
	   $en_savoir_desc2 = $request->getParam('en_savoir_desc2');
	   $artist_name = $request->getParam('artist_name');
	   $author_name = $request->getParam('author_name');
	   $productor_name = $request->getParam('productor_name');
	   $director_name = $request->getParam('director_name');
	   $parent_id = 0; //$request->getParam('parent_id');
	   $photo_title = $request->getParam('photo_title');
	   $thumbnail_title = $request->getParam('thumbnail_title');
	   $section = $request->getParam('section');
	   $status = $request->getParam('status');
	   $producer_id = $request->getParam('producer_id');
	   $event_group_slug = $request->getParam('event_group_slug');
	   $display_order =  $request->getParam('display_order');
	   $eventGroupExist = Models\Eventgroup::where('title', '=', $title)->where('id', '!=', $id)->first();
	  
	    if(empty($title)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group title.'));
		 exit();	   
	   }else if(empty($price_min)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group minimum price.'));
		 exit();	   
	   }else if(empty($category_id)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event group category.'));
		 exit();	   
	   }else if(empty($date_begin)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select begin date.'));
		 exit();	   
	   }else if(empty($date_end)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select end date.'));
		 exit();	   
	   }else if(empty($description)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group description.'));
		 exit();	   
	   }else{
		   // Upload the picture here
		   $group_picture = $this->uploadGroupPicture();
		   $group_thumbnail  = $this->uploadGroupThumbnailPicture();
		  $isError = false;
	   }
	   if($_FILES['group_picture']['tmp_name'] <> ''){
			   // Delete old images
			   if($group_picture_old <> ''){
					 @unlink(EVENTGROUP_ROOT_PATH.'/'.$group_picture_old);
			   }
			   $group_picture = $group_picture;
		   }else{
			   $group_picture = $group_picture_old; 
		}
		if($_FILES['group_picture_thumb']['tmp_name'] <> ''){
			   // Delete old images
			   if($group_thumbnail_old <> ''){
					 @unlink(EVENTGROUP_ROOT_PATH.'/thumbs/'.$group_thumbnail_old);
			   }
			   $group_thumbnail = $group_thumbnail;
		   }else{
			   $group_thumbnail = $group_thumbnail_old; 
		}
	   if( !$isError ){
		   
		  $section = !isset($section) ? 0 : $section;
		  if( isset($producer_id) && !empty($producer_id) ){
			    $producer_id = $producer_id;
		   }else{
			  $producer_id = $_SESSION['adminId'];   
		   }
		   $display_order = ($display_order == '') ? 0 : $display_order;
		   $slug = empty($event_group_slug) ?  str_replace(' ', '_', strip_tags($title)) :  str_replace(' ', '_', $event_group_slug);
		   $slug = str_replace('_', '-', $slug);
		   $slug = str_replace('/', '-', $slug);
		   $slug = Generate_SEO_Url($slug);
		  // Update event_groups table
		    $data = array('group_thumbnail' => $group_thumbnail,
			             'group_picture' => $group_picture,
		                 'title' => htmlspecialchars($title),
						 'parent_id' => $parent_id,
						 'date_begin' => mysql_date($date_begin).' '.date('H:i:s'),
						 'date_end' => mysql_date($date_end).' '.date('H:i:s'),
						 'description' => htmlspecialchars($description),
						 'price_min' => $price_min,
						 'category_id' => 0,/* $category_id,*/
						 'en_savoir_block1_name' => $en_savoir_block1_name,
						 'en_savoir_desc1' => htmlspecialchars($en_savoir_desc1),
						 'en_savoir_block2_name' => $en_savoir_block2_name,
						 'en_savoir_desc2' => htmlspecialchars($en_savoir_desc2),
						 'artist_name' => $artist_name,
						 'author_name' => $author_name,
						 'productor_name' => $productor_name,
						 'director_name' => $director_name,
						 'status' => $status,
						 'thumbnail_title' => $thumbnail_title,
						 'photo_title' => $photo_title,
						 'event_group_slug' => $slug,
						 'section' => $section,
						 'producer_id' => $producer_id,
						 'display_order' => $display_order,
						 'meta_title' => $meta_title, // New column
						 'meta_description' => $meta_description, // New column
						 'permalink' => trim($permalink),
						 'adv_image' => $this->uploadEventAdsUpdate() );
					 
		   $eventGroup = Models\Eventgroup::where('id', '=', $id)->update($data);
		   
 		   if(isset($_REQUEST['file_name']) && !empty($_REQUEST['file_name'])){	
		      $this->eventGroupVideoLinks($id);
		   }
		    if( isset($_REQUEST['comment_tile']) && !empty($_REQUEST['comment_tile']) ){
		       $this->saveSectionPicComments($id);  // Save picture comments
			}

			$this->saveEventGroupRoles($id);  // Save this event group roles
		    $this->saveEventGroupChildrenUpdate($id);
			$this->saveMultipleCategories($id);
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 
	   }
	  
	   
	}
	
	// Save event Ads image
	public function uploadEventAds(){
		if(isset($_FILES) && !empty($_FILES) ) {
			$uploads_dir = EVENTGROUP_ADS_ROOT_PATH.'/';
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
			$uploads_dir = EVENTGROUP_ADS_ROOT_PATH.'/';
			$validextensions = allowedExtensions(); 
				//Get the temp file path
                $file_adv = $_FILES['event_adv_img']['tmp_name'];
				if($file_adv <> ''){
				$ext = explode('.', basename($_FILES['event_adv_img']['name']));   // Explode file name from dot(.)
				$file_extension = end($ext); // Store extensions in the variable.
				$event_img_name = md5(uniqid()) . "." . $ext[count($ext) - 1];
				$target_path = $uploads_dir . $event_img_name;
				if(in_array($file_extension, $validextensions)) {
					  list($width, $height, $type, $attr) = getimagesize($file_adv);
					  if (move_uploaded_file($_FILES['event_adv_img']['tmp_name'], $target_path)) {
						  // Delete old images
						   if($event_adv_img_old <> ''){
								 @unlink(EVENTGROUP_ADS_ROOT_PATH.'/'.$event_adv_img_old);
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
	
	// Get Event Group by id
	public function getEventGroupById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$eventgroup = Models\Eventgroup::with(['artist', 'category'])->find($id);
		
		if ($eventgroup) {
			$eventgroup['title'] = trim(strip_tags(trim(clearString(htmlspecialchars_decode($eventgroup['title'])))));
			$eventgroup['category_name'] = $eventgroup['category']['name'];
			$eventgroup['artist_name'] =  $eventgroup['artist']['name'];
			$eventgroup['date_begin'] = hr_date($eventgroup['date_begin']);
			$eventgroup['date_end'] = hr_date($eventgroup['date_end']);
			//$eventgroup['title'] = strip_tags($eventgroup['title']);
			$eventgroup['description'] = strip_tags($eventgroup['description']);
			if($eventgroup['group_picture'] <> ''){
			  $eventgroup['file_web_path'] = EVENTGROUP_WEB_PATH;
			  $eventgroup['group_picture'] = $eventgroup['group_picture'];
			}else{
			  $eventgroup['file_web_path'] = WEB_PATH.'/uploads/default';
			  $eventgroup['group_picture'] = 'default.png';
			}
            echo json_encode($eventgroup);
        }	
	}
	
	// Edit the whole Event Group + Event
	public function editEventGroupById($request, $response, $args){
		$id = $args['id'];
		
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		
		// Get categories + Artists list + categories + event groups + auditorirumns + events + cities
		$video_links =  Models\EventGrFiles::where('eventgroup_id', '=', $id)->where('file_type', '=', 'vid')->get();
		$categories =  Models\Category::orderBy('name','ASC')->get();
		$cities_list =  Models\City::orderBy('name','ASC')->where('status', '=' ,1)->get();
		$artists =  Models\User::orderBy('name','ASC')->where('type', '=' ,'Artist')->get();
		$auditoriums =  Models\Auditorium::orderBy('name','ASC')->get();
		$sections =  Models\Section::orderBy('section_title','ASC')->orderBy('display_order','ASC')->get();
		$events_list = Models\Event::with(['city', 'auditorium'])->where('eventgroup_id', '=', $id)->orderBy('id', 'DESC')->get();
		$event_detail = Models\Eventgroup::with(['artist', 'category', 'events'])->find($id);
		$event_detail['page_title'] = strip_tags(clearString(htmlspecialchars_decode($event_detail['title'])));
		$picture_comments =  Models\EventGrComment::where('for_section', '=' ,'1')->where('eventgroup_id', '=' ,$id)->orderBy('id','DESC')->get();
		$event_group_roles = Models\EventGroupRole::where('eventgroup_id', '=', $id)->get();
		// Decode some data
		$event_detail['title'] = htmlspecialchars_decode($event_detail['title']);
		$event_detail['en_savoir_desc1'] = htmlspecialchars_decode($event_detail['en_savoir_desc1']);
		$event_detail['en_savoir_desc2'] = htmlspecialchars_decode($event_detail['en_savoir_desc2']);
		$event_detail['description'] = htmlspecialchars_decode($event_detail['description']);
		$url = explode('/', $request->getUri()->getPath());
		$current_url = $url[0].'/'.$url[1].'/'.$url[2].'/'.$url[3];
		$productors =  Models\User::where('type','=','Productor')->orderBy('name','ASC')->get();
		$groups_data =  Models\Event::where('status', '=' ,1)->get();
		$group_array = array();
		foreach($groups_data as $group){
			$group_array['id'] = $group['id']; 
			$group_array['title'] = strip_tags(htmlspecialchars_decode($group['title']));
			$groups[] = $group_array;
		}
		// Get all children events of this event group
		$children_data =  Models\EventGroupChildren::where('events_group_id', '=' ,$id)->get();
		if( !$children_data->isEmpty()){
			$child_list = '';
			foreach($children_data as $child){
				$child_list .= $child['events_id'].','; 
			}
			if($child_list <> ''){
				$children_trim = rtrim($child_list, ',');
				$children = explode(',', $children_trim);
			}else{
				$children = array();
			}
		}else{
		   $children = array();	
		}
		
		// Get all categories  of this event group
		$children_data =  Models\EventGroupCategory::where('events_group_id', '=' ,$id)->get();
		if( !$children_data->isEmpty()){
			$child_list = '';
			foreach($children_data as $child){
				$child_list .= $child['category_id'].','; 
			}
			if($child_list <> ''){
				$children_trim = rtrim($child_list, ',');
				$childrenCats = explode(',', $children_trim);
			}else{
				$childrenCats = array();
			}
		}else{
		   $childrenCats = array();	
		}
				
		$params = array( 'title' => 'Edit Event Group',
		                 'event_detail' => $event_detail,
						 'categories' => $categories,
						 'artists' => $artists,
						 'auditoriums' => $auditoriums,
						 'events_list' => $events_list,
						 'cities_list' => $cities_list,
						 'video_links' => $video_links,
						 'picture_comments' => $picture_comments,
						 'sections' => $sections,
						 'event_group_roles' => $event_group_roles,
						 'current_url' => $current_url,
						 'productors' => $productors,
						 'groups' => $groups,
						 'children' => $children,
						 'childrenCats' => $childrenCats);
        return $this->render($response, ADMIN_VIEW.'/Eventgroup/edit.twig',$params);
		
	}

    // Edit the whole Event Group Archive + Event
	public function editEventGroupArchiveById($request, $response, $args){
		$id = $args['id'];
		
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		
		// Get categories + Artists list + categories + event groups + auditorirumns + events + cities
		$video_links =  Models\EventGrFiles::where('eventgroup_id', '=', $id)->where('file_type', '=', 'vid')->get();
		$categories =  Models\Category::orderBy('name','ASC')->get();
		$cities_list =  Models\City::orderBy('name','ASC')->where('status', '=' ,1)->get();
		$artists =  Models\User::orderBy('name','ASC')->where('type', '=' ,'Artist')->get();
		$auditoriums =  Models\Auditorium::orderBy('name','ASC')->get();
		$sections =  Models\Section::orderBy('section_title','ASC')->orderBy('display_order','ASC')->get();
		$events_list = Models\Event::with(['city', 'auditorium'])->where('eventgroup_id', '=', $id)->orderBy('id', 'DESC')->get();
		$event_detail = Models\Eventgroup::with(['artist', 'category', 'events'])->find($id);
		$event_detail['page_title'] = strip_tags(clearString(htmlspecialchars_decode($event_detail['title'])));
		$picture_comments =  Models\EventGrComment::where('for_section', '=' ,'1')->where('eventgroup_id', '=' ,$id)->orderBy('id','DESC')->get();
		$event_group_roles = Models\EventGroupRole::where('eventgroup_id', '=', $id)->get();
		// Decode some data
		$event_detail['title'] = htmlspecialchars_decode($event_detail['title']);
		$event_detail['en_savoir_desc1'] = htmlspecialchars_decode($event_detail['en_savoir_desc1']);
		$event_detail['en_savoir_desc2'] = htmlspecialchars_decode($event_detail['en_savoir_desc2']);
		$event_detail['description'] = htmlspecialchars_decode($event_detail['description']);
		$url = explode('/', $request->getUri()->getPath());
		$current_url = $url[0].'/'.$url[1].'/'.$url[2].'/'.$url[3];
		$productors =  Models\User::where('type','=','Productor')->orderBy('name','ASC')->get();
		$groups_data =  Models\Event::where('status', '=' ,1)->get();
		$group_array = array();
		foreach($groups_data as $group){
			$group_array['id'] = $group['id']; 
			$group_array['title'] = strip_tags(htmlspecialchars_decode($group['title']));
			$groups[] = $group_array;
		}
		// Get all children events of this event group
		$children_data =  Models\EventGroupChildren::where('events_group_id', '=' ,$id)->get();
		if( !$children_data->isEmpty()){
			$child_list = '';
			foreach($children_data as $child){
				$child_list .= $child['events_id'].','; 
			}
			if($child_list <> ''){
				$children_trim = rtrim($child_list, ',');
				$children = explode(',', $children_trim);
			}else{
				$children = array();
			}
		}else{
		   $children = array();	
		}
				
		$params = array( 'title' => 'Edit Event Group Archived',
		                 'event_detail' => $event_detail,
						 'categories' => $categories,
						 'artists' => $artists,
						 'auditoriums' => $auditoriums,
						 'events_list' => $events_list,
						 'cities_list' => $cities_list,
						 'video_links' => $video_links,
						 'picture_comments' => $picture_comments,
						 'sections' => $sections,
						 'event_group_roles' => $event_group_roles,
						 'current_url' => $current_url,
						 'productors' => $productors,
						 'groups' => $groups,
						 'children' => $children);
        return $this->render($response, ADMIN_VIEW.'/Eventgroup/edit_archived.twig',$params);
		
	}	

	// Delete Event Group
	public function deleteEventGroupById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		/*
		// Check if this event group has a picture uploaded.
		$pictureExist = Models\Eventgroup::where('id', '=', $id)->first()->group_picture;
	   if($pictureExist){
		   // Unlink the picture
		   @unlink(EVENTGROUP_ROOT_PATH.'/'.$pictureExist);
	   }

	   // Check if this event group has a picture uploaded.
		$group_thumbnail = Models\Eventgroup::where('id', '=', $id)->first()->group_thumbnail;
	   if($group_thumbnail){
		   // Unlink the picture
		   @unlink(EVENTGROUP_ROOT_PATH.'/thumbs/'.$group_thumbnail);
	   }
		$delete = Models\Eventgroup::find($id)->delete();*/
		$data = array('status' => 2);
		$eventGroupArchive = Models\Eventgroup::where('id', '=', $id)->update($data);	
		$eventdata = array('status' => 2);
		$eventArchive = Models\Event::where('eventgroup_id', '=', $id)->update($eventdata);	
		return $response->withJson(json_encode(array("status" => TRUE)));
	}
    
	// Upload Event Group Images
	public function uploadImages($request, $response, $args){
		$eventgroup_id =  $args['id'];
		
		if (!empty($_FILES)) {
			 $file = $_FILES['file']['tmp_name'];
			 $file_name = explode('.',$_FILES['file']['name']);
			 $file_extension = end($file_name);
			 $file_new_name = md5(uniqid());
			 $filename = $file_new_name.'.'.$file_extension;
			 $with_path = EVENTGROUP_EN_SAVOIR_ROOT_PATH.'/'.$filename;
		     if(move_uploaded_file($file, $with_path)){
			   // Save to event_group_files
			   $evgPic = new Models\EventGrFiles;
			   $evgPic->eventgroup_id = $eventgroup_id;
			   $evgPic->file_name = $filename;
			   $evgPic->file_type = "img";
			   $evgPic->save();
			   echo $filename;
		   } 
		 }else{
			// Get all uploaded images of this eventgroup_id
			$eventFiles = Models\EventGrFiles::where('eventgroup_id', '=', $eventgroup_id)->where('file_type', '=', 'img')->get();
			  
				$result  = array();
				if ( !empty($eventFiles) ) {
					// get gallery uploaded images
				  $uploaddir = EVENTGROUP_EN_SAVOIR_ROOT_PATH.'/';
				   foreach ( $eventFiles as $file ) {
							$extension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
							if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png' || $extension == 'gif' ) {
								 $obj['name'] = $file['file_name'];
								 $obj['row_id'] = $file['id'];
								 $obj['dir'] = EVENTGROUP_EN_SAVOIR_WEB_PATH.'/';
								 $obj['size'] = filesize($uploaddir.$file['file_name']);
								 $result[] = $obj;
							}
						
					}
					
				}
				//header('Content-type: text/json');              //3
				//header('Content-type: application/json');
				//echo json_encode($result);  
				 return $response
            /*->withHeader('Content-type','application/json')*/
            ->write(json_encode($result)); 
				
		 }
	}
	
	// Upload Event Group Images
	public function getUploads($request, $response, $args){
		$eventgroup_id =  $args['id'];
			// get gallery uploaded images
			  $uploaddir = EVENTGROUP_EN_SAVOIR_ROOT_PATH.'/';
			   $result  = array();
				$files = scandir($uploaddir);                 //1
				// Get all uploaded images of this eventgroup_id
				$eventFiles = Models\EventGrFiles::where('eventgroup_id', '=', $eventgroup_id)->get();
				if ( !empty($eventFiles) ) {
					foreach ( $eventFiles as $file ) {
						if ( '.'!=$file && '..'!=$file) {       //2
							$extension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
							if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png' || $extension == 'gif' ) {
								 $obj['row_id'] = $file['id'];
								 $obj['name'] = $file['file_name'];
								 $obj['dir'] = EVENTGROUP_EN_SAVOIR_WEB_PATH.'/';
								 $obj['size'] = filesize($uploaddir.$file['file_name']);
								 $result[] = $obj;
							}
						}
					}
				}
				 
				header('Content-type: text/json');              //3
				header('Content-type: application/json');
				echo json_encode($result);
		       
	}
	
	// Picture upload
	public function uploadGroupPicture(){
	   if(isset($_FILES) && $_FILES['group_picture']['name'] <> ''){
			   $allowedExtensions = allowedExtensions();
			   $file = $_FILES['group_picture']['tmp_name'];
			   $file_name = explode('.',$_FILES['group_picture']['name']);
			   $file_extension = end($file_name);
			   if(!in_array($file_extension, $allowedExtensions)){
				   echo json_encode(array("status" => 'file_error', 
					               'message' => 'File with Type [<strong>'.$file_extension. '</strong>] is not allowed.'));	
				   exit();
			   }else{
				    $file_new_name = md5(uniqid());
				    $filename = $file_new_name.'.'.$file_extension;
				   if(!empty($file)){
					    move_uploaded_file($file, EVENTGROUP_ROOT_PATH.'/'.$filename);
					  $group_picture = $filename;
					 }else{
					  $group_picture = '';	
				   }
			   }
		   }else{
			   $group_picture = '';
		   }
		  return $group_picture; 	
	}
	
	// Event Group thumbnail
	public function uploadGroupThumbnailPicture(){
		
	   if(isset($_FILES) && $_FILES['group_picture_thumb']['name'] <> ''){
			   $allowedExtensions = allowedExtensions();
			   $file_thumb = $_FILES['group_picture_thumb']['tmp_name'];
			   $file_name = explode('.',$_FILES['group_picture_thumb']['name']);
			   $file_extension = end($file_name);
			   if(!in_array($file_extension, $allowedExtensions)){
				   echo json_encode(array("status" => 'file_error', 
					               'message' => 'File with Type [<strong>'.$file_extension. '</strong>] is not allowed.'));	
				   exit();
			   }else{
				    $file_new_name = md5(uniqid());
				    $filename = $file_new_name.'.'.$file_extension;
				   if(!empty($file_thumb)){
					   list($width, $height) = getimagesize($_FILES["group_picture_thumb"]["tmp_name"]);
					   $resizedFile_thumb = EVENTGROUP_ROOT_PATH.'/thumbs/'.$filename;
					   if($height > 300 && $width >  400){
					   smart_resize_image($_FILES['group_picture_thumb']['tmp_name'], null,  THUMB_WEIGHT+300, THUMB_HEIGHT+200 , false , $resizedFile_thumb , false , false ,100 );
					   }else{
					      move_uploaded_file($file_thumb, EVENTGROUP_ROOT_PATH.'/thumbs/'.$filename);
					   }
					  $group_picture = $filename;
					 }else{
					  $group_picture = '';	
				   }
			   }
		   }else{
			   $group_picture = '';
		   }
		  return $group_picture; 	
	}
	
	
	
	// Delete Event Group File
	public function removeFile($request, $response, $args){
		$file_name = $args['file_name'];
	    @unlink(EVENTGROUP_EN_SAVOIR_ROOT_PATH.'/'.$file_name);
		$delete = Models\EventGrFiles::where('file_name', '=', $file_name)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Delete Advertisement Picture
	public function removeAdvImage($request, $response, $args){
		$id = $args['id']; // Event Group id
		
	    $adv_image = Models\Eventgroup::where('id', '=', $id)->first()->adv_image;
	    if($adv_image){
		   // Unlink the picture
		   @unlink(EVENTGROUP_ADS_ROOT_PATH.'/'.$adv_image);
	    }
		
		// Update the event group table
		$data = array('adv_image' => '');
		$eventGroupUpdate = Models\Eventgroup::where('id', '=', $id)->update($data);
		
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Delete Event Group Video link
	public function removeVideoLink($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// Check if this event group has a picture uploaded.
		$pictureExist = Models\EventGrFiles::where('id', '=', $id)->first()->video_img;
	    if($pictureExist){
		   // Unlink the picture
		   @unlink(EVENTGROUP_VID_ROOT_PATH.'/'.$pictureExist);
	    }
		$delete = Models\EventGrFiles::find($id)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Delete Event Group Comments
	public function deleteGroupComment($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$delete = Models\EventGrComment::find($id)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	// Save section picture Comments
	public function saveSectionPicComments($eventgroup_id){
		  if( isset($_REQUEST['comment_tile']) && !empty($_REQUEST['comment_tile']) ){
			  foreach($_REQUEST['comment_tile'] as $key=>$comment):
			     if( !empty($comment) ){
				 // Save to event_group_comments
				   $evgCom = new Models\EventGrComment;
				   $evgCom->eventgroup_id = $eventgroup_id;
				   $evgCom->title = $comment;
				   $evgCom->ratings = $_REQUEST['ratings'][$key];
				   $evgCom->comments = $_REQUEST['comments'][$key];
				   $evgCom->signature = $_REQUEST['signature'][$key];
				   $evgCom->for_section = '1';
				   $evgCom->save();
				  }
			  endforeach;
		  }
		   if( isset($_REQUEST['comment_title_old']) && !empty($_REQUEST['comment_title_old']) ){
			  foreach($_REQUEST['comment_title_old'] as $key=>$comment):
			     if( !empty($comment) ){
				  $id = $_REQUEST['comment_id'][$key];	
				 // Update to event_group_comments
				 $data = array('eventgroup_id' => $eventgroup_id,
				              'title' => $comment,
							  'ratings' => $_REQUEST['ratings_old'][$key],
							  'comments' => $_REQUEST['comments_old'][$key],
							  'signature' => $_REQUEST['signature_old'][$key],
							  'for_section' => '1'
							  );
					$evgVid = Models\EventGrComment::where('id', '=', $id)->update($data);		  
				  }
			  endforeach;
		  }
	}
	
	// Event group video links
	public function eventGroupVideoLinks($eventgroup_id){
		  if( isset($_REQUEST['file_name']) && !empty($_REQUEST['file_name']) ){
			  foreach($_REQUEST['file_name'] as $key=>$filename):
			    if( !empty($filename) ){
				 $video_img = '';
				  $value = explode("v=", $filename);
                  $videoId = $value[1];
				  if (! empty($videoId)) {
					  $linkPicture = "http://img.youtube.com/vi/".$videoId."/hqdefault.jpg";
					  $currentFile = str_replace('/', '_', $videoId."/hqdefault.jpg");
					  $file_name = explode('.', $currentFile);
					  $file_extension = end($file_name);
					  $file_new_name = md5(uniqid());
					  $video_img = $file_new_name.'.'.$file_extension;
					  $OldFile = $linkPicture;
					  $NewFile = EVENTGROUP_VID_ROOT_PATH.'/'.$video_img;
					  // Now copy and save it
					  @copy($OldFile, $NewFile);
				 }
				 // Save to event_group_files
				   $evgVid = new Models\EventGrFiles;
				   $evgVid->eventgroup_id = $eventgroup_id;
				   $evgVid->file_name = $filename;
				   $evgVid->file_type = 'vid';
				   $evgVid->video_img = $video_img;
				   $evgVid->save();
				  }
			   endforeach; 
		  }
		  
		  // Update if any is there
		  if( isset($_REQUEST['file_name_old']) && !empty($_REQUEST['file_name_old']) ){
			  foreach($_REQUEST['file_name_old'] as $key=>$filename):
			    if( !empty($filename) ){
				  $id = $_REQUEST['old_vid_id'][$key];	
				  $video_img = '';
				  $value = explode("v=", $filename);
                  $videoId = $value[1];
				  if (! empty($videoId)) {
					  $linkPicture = "http://img.youtube.com/vi/".$videoId."/hqdefault.jpg";
					  $currentFile = str_replace('/', '_', $videoId."/hqdefault.jpg");
					  $file_name = explode('.', $currentFile);
					  $file_extension = end($file_name);
					  $file_new_name = md5(uniqid());
					  $video_img = $file_new_name.'.'.$file_extension;
					  $OldFile = $linkPicture;
					  $NewFile = EVENTGROUP_VID_ROOT_PATH.'/'.$video_img;
					  // Now copy and save it
					  @copy($OldFile, $NewFile);
				 }
				 
				 // Update  event_group_files
				   $data = array('eventgroup_id' => $eventgroup_id,
		                        'file_name' => $filename,
						        'file_type' => 'vid',
								'video_img' => $video_img);
		          $evgVid = Models\EventGrFiles::where('id', '=', $id)->update($data);
				  }
			   endforeach; 
		  }
	}
	
	// Duplicate Event Group
	public function duplicateEventGroup($request, $response, $args){
		$eventgroup_id = $args['id'];
		
        $validations = [
            v::intVal()->validate($eventgroup_id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// First save this event group
		$event_group_data = Models\Eventgroup::where('id', '=', $eventgroup_id)->get()->first();
		
		$eventGroup = new Models\Eventgroup;
		// Event Group picture
		$new_group_picture = '';
		if($event_group_data['group_picture'] <> ''){
			 $current_file = $event_group_data['group_picture'];
             $new_group_picture = $this->renameFile($current_file);
			 // Copy picture of the event group
		     @copy(EVENTGROUP_ROOT_PATH.'/thumbs/'.$current_file, EVENTGROUP_ROOT_PATH.'/thumbs/'.$new_group_picture);
			 @copy(EVENTGROUP_ROOT_PATH.'/'.$current_file, EVENTGROUP_ROOT_PATH.'/'.$new_group_picture);
		}else{
			$new_group_picture = '';
		}
		// For advertisement picture
		$new_adv_image_picture = '';
		if($adv_image['adv_image'] <> ''){
			 $current_file = $event_group_data['adv_image'];
             $new_adv_image_picture = $this->renameFile($current_file);
			 // Copy picture of the event group advertisement 
			 @copy(EVENTGROUP_ADS_ROOT_PATH.'/'.$current_file, EVENTGROUP_ADS_ROOT_PATH.'/'.$new_adv_image_picture);
		}else{
			$new_adv_image_picture = '';
		}
		// Event Group Thumbnail
		$new_group_picture_thumb = '';
		if($event_group_data['group_thumbnail'] <> ''){
			 $current_file = $event_group_data['group_thumbnail'];
             $new_group_picture_thumb = $this->renameFile($current_file);
			 // Copy picture of the event group
		     @copy(EVENTGROUP_ROOT_PATH.'/thumbs/'.$current_file, EVENTGROUP_ROOT_PATH.'/thumbs/'.$new_group_picture_thumb);
		}else{
			$new_group_picture_thumb = '';
		}
		
		$eventGroup->group_picture = $new_group_picture;
		$eventGroup->group_thumbnail = $new_group_picture_thumb;
		$eventGroup->title = $event_group_data['title'];
		$eventGroup->date_begin =  $event_group_data['date_begin'];
		$eventGroup->date_end = $event_group_data['date_end'];
		$eventGroup->description = $event_group_data['description'];
		$eventGroup->price_min = $event_group_data['price_min'];
		$eventGroup->category_id = $event_group_data['category_id'];
		$eventGroup->section = $event_group_data['section'];
		$eventGroup->is_for_home = $event_group_data['is_for_home'];
		$eventGroup->en_savoir_block1_name = $event_group_data['en_savoir_block1_name'];
		$eventGroup->en_savoir_desc1 = $event_group_data['en_savoir_desc1'];
		$eventGroup->en_savoir_block2_name = $event_group_data['en_savoir_block2_name'];
		$eventGroup->en_savoir_desc2 = $event_group_data['en_savoir_desc2'];
		$eventGroup->artist_name = $event_group_data['artist_name'];
		$eventGroup->author_name = $event_group_data['author_name'];
		$eventGroup->productor_name = $event_group_data['productor_name'];
		$eventGroup->director_name = $event_group_data['director_name'];
		$eventGroup->status = $event_group_data['status'];
		$eventGroup->photo_title = $event_group_data['photo_title'];
		$eventGroup->thumbnail_title = $event_group_data['thumbnail_title'];
		$eventGroup->event_group_slug = $event_group_data['event_group_slug'];
		$eventGroup->producer_id = $event_group_data['producer_id'];
		$eventGroup->adv_image = $new_adv_image_picture;
		$eventGroup->display_order = $event_group_data['display_order'];
		$eventGroup->meta_title = $event_group_data['meta_title'];
		$eventGroup->meta_description = $event_group_data['meta_description'];
		$eventGroup->permalink = $event_group_data['permalink'];
		$eventGroup->save(); // Save to event group table
		$new_event_group_id = $eventGroup->id;
				
		
		// Now get all the event group files
		$event_group_files = Models\EventGrFiles::where('eventgroup_id','=', $eventgroup_id)->get();
		if( !empty($event_group_files) ){
		    foreach($event_group_files  as $row):	
			   $evgFiles = new Models\EventGrFiles;
			   $evgFiles->eventgroup_id = $new_event_group_id;
			   // Check for images
			   $new_file_vid = ''; 
			   $new_file_egp = '';
			   if($row['file_type'] == 'img'){
				   if($row['file_name'] <> ''){
				      $current_file_egp = $row['file_name'];
					  $new_file_egp = $this->renameFile($current_file_egp);
					  @copy(EVENTGROUP_ROOT_PATH.'/'.$current_file_egp, EVENTGROUP_ROOT_PATH.'/'.$new_file_egp);
				   }else{
					  $new_file_egp = '';   
				   }
			   }else{
				   if($row['video_img'] <> ''){
				      $current_file_vid = $row['video_img'];
					  $new_file_vid = $this->renameFile($current_file_vid);
					  @copy(EVENTGROUP_VID_ROOT_PATH.'/'.$current_file_vid, EVENTGROUP_VID_ROOT_PATH.'/'.$new_file_vid);
				   }else{
					  $new_file_vid = '';   
				   }
			   }		
			   $evgFiles->file_name = $row['file_name'];
			   $evgFiles->file_type = $row['file_type'];
			   $evgFiles->video_img = $new_file_vid;
			   $evgFiles->save();
			endforeach;
		}
		
		
		// Check for Event Group Comments
		$event_group_comments = Models\EventGrComment::where('eventgroup_id', '=',  $eventgroup_id)->get();
		if( !empty($event_group_comments) ){
		   foreach($event_group_comments as $row):
		         $evgComment = new Models\EventGrComment;
				 $evgComment->eventgroup_id = $new_event_group_id;
				 $evgComment->title = $row['title'];
				 $evgComment->ratings = $row['ratings'];
				 $evgComment->comments = $row['comments'];
				 $evgComment->signature = $row['signature'];
				 $evgComment->for_section = $row['for_section'];
				 $evgComment->save();
				 
		   endforeach;	
		}
		
		// Now get all roles of this event group
	   $event_gr_roles = Models\EventGroupRole::where('eventgroup_id', '=', $eventgroup_id)->get();
	   if( !empty($event_gr_roles) ){
		   foreach($event_gr_roles as $row):
				$eventRole = new Models\EventRole;
				$eventRole->eventgroup_id = $new_event_group_id;
				$eventRole->role_label    = $row['role_label'];
				$eventRole->role_name     =  $row['role_name'];
				$eventRole->save();
		   endforeach;   
	   }
		
		
		// First get all its events.
		$events_list = Models\Event::where('eventgroup_id', '=', $eventgroup_id)->get();
		if( !empty($events_list) ){
		    foreach($events_list as $row_event):
			   // get advertisement picture
			   $new_event_adv_image = '';
			   if($row_event['adv_image'] <> ''){
					 $current_file = $event_group_data['adv_image'];
					 $new_group_picture = $this->renameFile($current_file);
					 // Copy picture of the event group
					 @copy(EVENTGROUP_ROOT_PATH.'/thumbs/'.$current_file, EVENTGROUP_ROOT_PATH.'/thumbs/'.$new_group_picture);
					 @copy(EVENTGROUP_ROOT_PATH.'/'.$current_file, EVENTGROUP_ROOT_PATH.'/'.$new_group_picture);
				}else{
					$new_group_picture = '';
				}
			   $event_id = $row_event['id'];	
			   $event = new Models\Event;
			   $event->title = $row_event['title'];
			   $event->date = $row_event['date'];
			   $event->created_at = $row_event['created_at'];
			   $event->updated_at = $row_event['updated_at'];
			   $event->eventgroup_id = $new_event_group_id;
			   $event->city_id = $row_event['city_id'];
			   $event->auditorium_id = $row_event['auditorium_id'];
			   $event->status = $row_event['status'];
			   $event->section = $row_event['section'];
			   $event->description = $row_event['description'];
			   $event->artist_name = $row_event['artist_name'];
			   $event->author_name = $row_event['author_name'];
			   $event->productor_name = $row_event['productor_name'];
			   $event->director_name = $row_event['director_name'];
			   $event->contributor_name = $row_event['contributor_name'];
			   $event->contributor_description = $row_event['contributor_description'];
			   $event->seats_on_map             = $row_event['seats_on_map'];
			   $event->auditorium_key           = $row_event['auditorium_key'];
			   $event->auditorium_seats_map     = $row_event['auditorium_seats_map'];
			   $event->event_ticket_type        = $row_event['event_ticket_type'];
			   $event->booking_fee              = $row_event['booking_fee'];
			   $event->adv_image                = $new_event_adv_image;
			   $event->save();
			   $new_event_id = $event->id;
			   
			   // Now get all images of this event
			   $event_images = Models\Eventpicture::where('event_id', '=', $event_id)->get();
			   if( !empty($event_images) ){
				   foreach($event_images as $row):
				      $eventImg = new Models\Eventpicture;
					  $eventImg->event_id = $new_event_id;
					  if($row['event_img'] <> ''){
						  $current_image_evnt = $row['event_img'];
						  $new_image_evnt = $this->renameFile($current_image_evnt);
						  // Copy this image now
						  @copy(EVENT_ROOT_PATH.'/'.$current_image_evnt, EVENT_ROOT_PATH.'/'.$new_image_evnt);
					  }else{
						  $new_image_evnt = '';
					  }
					  $eventImg->event_img = $new_image_evnt;  
					  $eventImg->save();
					  
				   endforeach;   
			   }
			   
			   // Now get all roles of this event
			   $event_roles = Models\EventRole::where('event_id', '=', $event_id)->get();
			   if( !empty($event_roles) ){
				   foreach($event_roles as $row):
				        $eventRole = new Models\EventRole;
						$eventRole->event_id = $event_id;
						$eventRole->role_label = $row['role_label'];
						$eventRole->role_name =  $row['role_name'];
						$eventRole->save();
				   endforeach;   
			   }
			   
			   // check if this event has manual seats set up
			   if($row_event['seats_on_map'] == 'N'){
				   // Get all event tickets of this event
				   $event_tickets = Models\EventTicket::where('event_id', '=', $event_id)->get();
				   if( !empty($event_tickets) ){
					   foreach($event_tickets as $row):
							 $eventTicket = new Models\EventTicket;
							 $eventTicket->event_id           = $event_id;
							 $eventTicket->ticket_type        = $row['ticekt_type'];
							 $eventTicket->per_ticket_price   = $row['per_ticket_price'];
							 $eventTicket->total_quantity     = $row['total_quantity'];
							 $eventTicket->gate_id            = $row['gate_id'];
							 $eventTicket->row_id             = $row['row_id'];
							 $eventTicket->seat_id            = $row['seat_id'];
							 $eventTicket->type_of_seat_id    = $row['type_of_seat_id'];
							 $eventTicket->save();
					   endforeach;   
				   }
				   
				   
			   }
			   
			endforeach;
		}
	  return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 	
	}
	
  // Function to rename a file
  public function renameFile($fileName){
		$file_name = explode('.',$fileName);
		$file_extension = end($file_name);
		$file_rename = md5(uniqid());
		$new_file_name = $file_rename.'.'.$file_extension;  
        return $new_file_name;		
  }
  
  // Update Event Group status to Active or Inactive
	public function updateGroupStatus($request, $response, $args){
		  $id = $args['id'];
		  $eventGroupStatus = Models\Eventgroup::where('id', '=', $id)->first()->status;
		  $new_status = ($eventGroupStatus == 1) ? 0 : 1;
		  $data = array('status' => $new_status);
		  $event = Models\Eventgroup::where('id', '=', $id)->update($data);	
		  return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 	
	}
	
	// Save event group roles
	public function saveEventGroupRoles($eventgroup_id){
		  if( isset($_REQUEST['event_role_label']) && !empty($_REQUEST['event_role_label']) ){
			  foreach($_REQUEST['event_role_label'] as $key=>$role):
			     if( !empty($role) ){
				  // Save to event_group_roles
				    $eventRole = new Models\EventGroupRole;
					$eventRole->eventgroup_id = $eventgroup_id;
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
				    // Update to event_group_roles
					$data = array('eventgroup_id' => $eventgroup_id,
					             'role_label' => $role ,
								 'role_name' => $_REQUEST['event_role_name_old'][$key] 
								 );
					$eventRole = Models\EventGroupRole::where('id', '=', $id)->update($data);	
				  }
			  endforeach;
		  } 
	}
	
	
	// Delete Event Group Role
	public function deleteEventGroupRole($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$delete = Models\EventGroupRole::find($id)->delete();
		//return $response->withJson(json_encode(array("status" => TRUE)));
	}
	
	public function addoperators($request, $response)
	{
	
		return $this->render($response, ADMIN_VIEW.'/Eventgroup/addoperators.twig');
	}
	
	public function addoperatorsSave($request, $response)
	{
		
		$data = array(   		 'op_fname' => $_REQUEST['op_fname'],
								 'op_lname' => $_REQUEST['op_lname'],
								 'op_email' => $_REQUEST['op_email'],
								 'op_phone' => $_REQUEST['op_phone']
								 );
		if($operator = Models\Operators::insert($data))
		{
		
			return $response
            ->withHeader('Content-type','application/json')
             ->write(json_encode(array('status' => TRUE)));
        
		}
		
		
	}
	
	
	public function getOperatorsList($request, $response)
	{
		
		
		$lists= Models\Operators::get();
		?>
		     <span class="text-left">Select Operator</span>
			<select name="operatorsSelection<?= $_POST['data'] ?>" id="operatorsSelection<?=  $_POST['data']  ?>">
		
			<?php
		foreach($lists as $list)
		{
			?>
			<option value="<?=$list['op_id']?>"><?=$list['op_fname']?></option>
			<?php
		}
		?>
		
		</select>
			<?php	
		
	}
	// Save event group children
	public function saveEventGroupChildren($event_group_id){
		if(isset($_REQUEST['event_id']) && sizeof($_REQUEST['event_id']) > 0){
		foreach($_REQUEST['event_id'] as $key=>$event_id){
		   // Save to event_group_children
			$eventGroupChildren = new Models\EventGroupChildren;
			$eventGroupChildren->events_group_id = $event_group_id;
			$eventGroupChildren->events_id = $event_id;
			$eventGroupChildren->save(); 
		}
	  }
		
	}
	
	// Save & Update event group children
	public function saveEventGroupChildrenUpdate($event_group_id){
	
		if(isset($_REQUEST['event_ids']) && sizeof($_REQUEST['event_ids']) > 0){
		// Get all children events of this event group
		$children_data =  Models\EventGroupChildren::where('events_group_id', '=' ,$event_group_id)->get();
		if( !$children_data->isEmpty()){
			$child_list = '';
			foreach($children_data as $child){
				$child_list .= $child['events_id'].','; 
			}
			if($child_list <> ''){
				$children_trim = rtrim($child_list, ',');
				$children = explode(',', $children_trim);
			}else{
				$children = array();
			}
		}else{
		   $children = array();	
		}
			
		 if( !$children_data->isEmpty()){
			 $to_delete_enteries = array_diff($children,$_REQUEST['event_ids']);
             if(!empty($to_delete_enteries) ){
				 foreach($to_delete_enteries as $event_id){
					 $delete = Models\EventGroupChildren::where('events_id', '=', $event_id)
														->where('events_group_id', '=', $event_group_id)
														->delete();
				 }
			 }
		 }
		 
		
		foreach($_REQUEST['event_ids'] as $key=>$event_id){

			   // Check if event id not exist, then delete it
			   if( !in_array($event_id, $children) ){
				   // Save to event_group_children
				   $eventGroupChildren = new Models\EventGroupChildren;
				   $eventGroupChildren->events_group_id = $event_group_id;
				   $eventGroupChildren->events_id = $event_id;
				   $eventGroupChildren->save(); 									   
			   }	
		}
			   
	  }else{
		  // Delete data if nothing is there
		 $delete_data =  Models\EventGroupChildren::where('events_group_id', '=' ,$event_group_id)->delete(); 
	  }
		
	}
	
	// Get parmalink
	public function getParmalink($request, $response, $args){
		ddump($_REQUEST);
	}
	
	 // Main function to display event group list
	public function groups_archived($request, $response) {
		// Get categories + Artists list
		$categories =  Models\Category::get();
		$artists =  Models\User::where('type', '=' ,'Artist')->get();
		$sections =  Models\Section::orderBy('section_title','ASC')->orderBy('display_order','ASC')->get();
		$productors =  Models\User::where('type','=','Productor')->orderBy('name','ASC')->get();
		$groups_data =  Models\Event::where('status', '=' ,1)->get();
		//$groups['title'] = strip_tags(clearString(htmlspecialchars_decode($groups[0]['title'])));
		$group_array = array();
		foreach($groups_data as $group){
			$group_array['id'] = $group['id']; 
			$group_array['title'] = strip_tags(htmlspecialchars_decode($group['title']));
			$groups[] = $group_array;
		}
		
        $params = array( 'title' => $this->lang['event_group_all_txt'],
		                 'categories' => $categories,
						 'artists' => $artists,
						 'sections' => $sections,
						 'current_url' => $request->getUri()->getPath(),
						 'productors' => $productors,
						 'groups' => $groups);
        return $this->render($response, ADMIN_VIEW.'/Eventgroup/events_group_archived.twig',$params);
    }
	
	// Ajax Event Groups list
	public function getAjaxEventGroupsArchivedList($request, $response){
		
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
		    $total   = Models\Eventgroup::with(['category'])->whereRaw($whereData)->where('status', 2)->count(); // get count 
		}else{
			$total   = Models\Eventgroup::with(['category'])->where('status', 2)->count(); // get count 
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
		    $event_group_list = Models\Eventgroup::with(['category'])->whereRaw($whereData)->where('status', 2)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			
			$event_group_list = Models\Eventgroup::with(['category'])->where('status', 2)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		$data = array();
		foreach($event_group_list as $get){
		  	$array_data = array();
			$title = ($get['title'] == '') ? 'click to edit' : strip_tags(clearString(htmlspecialchars_decode($get['title'])));
			$array_data['id']  = $get['id'];
			if($_SESSION['is_event_group_edit'] == 'Y'){
            $array_data['title']  = '<a href="javascript:void(0);" title="'.$this->lang['event_group_update_txt'].'" onclick="edit_archive('.$get['id'].')">'.$title.'</a>';
			}else{
			$array_data['title']  = '<a href="javascript:void(0);" title="'.$this->lang['event_group_update_txt'].'" >'.$title.'</a>';
			}
			$array_data['category_name']  = $get['category']['name'];
			$array_data['date_begin']  = hr_date($get['date_begin']);
			$array_data['date_end']  = hr_date($get['date_end']);
			$array_data['display_order']  = $get['display_order'];
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
	
	
	// Update Event Group archive
	public function updateEventGroupArchive($request, $response){
	   $isError = false;
	   $id =  $request->getParam('event_id');
	   $meta_title = $request->getParam('meta_title'); // get the meta title
	   $meta_description = $request->getParam('meta_description'); // get the meta description
	   $permalink = $request->getParam('permalink');
	   $title =  $request->getParam('title');
	   $price_min =  $request->getParam('price_min');
	   $category_id =  $request->getParam('category_id');
	   $date_begin =  $request->getParam('date_begin');
	   $date_end =  $request->getParam('date_end');
	   $description =  $request->getParam('description');
	   $group_picture_old = $request->getParam('group_picture_old');
	   $group_thumbnail_old = $request->getParam('group_thumbnail_old');
	   $en_savoir_block1_name = $request->getParam('en_savoir_block1_name');
	   $en_savoir_desc1 = $request->getParam('en_savoir_desc1');
	   $en_savoir_block2_name = $request->getParam('en_savoir_block2_name');
	   $en_savoir_desc2 = $request->getParam('en_savoir_desc2');
	   $artist_name = $request->getParam('artist_name');
	   $author_name = $request->getParam('author_name');
	   $productor_name = $request->getParam('productor_name');
	   $director_name = $request->getParam('director_name');
	   $parent_id = 0; //$request->getParam('parent_id');
	   $photo_title = $request->getParam('photo_title');
	   $thumbnail_title = $request->getParam('thumbnail_title');
	   $section = $request->getParam('section');
	   $status = $request->getParam('status');
	   $producer_id = $request->getParam('producer_id');
	   $event_group_slug = $request->getParam('event_group_slug');
	   $display_order =  $request->getParam('display_order');
	   $eventGroupExist = Models\Eventgroup::where('title', '=', $title)->where('id', '!=', $id)->first();
	  
	    if(empty($title)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group title.'));
		 exit();	   
	   }else if(empty($price_min)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group minimum price.'));
		 exit();	   
	   }else if(empty($category_id)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select event group category.'));
		 exit();	   
	   }else if(empty($date_begin)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select begin date.'));
		 exit();	   
	   }else if(empty($date_end)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please select end date.'));
		 exit();	   
	   }else if(empty($description)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter event group description.'));
		 exit();	   
	   }else{
		   // Upload the picture here
		   $group_picture = $this->uploadGroupPicture();
		   $group_thumbnail  = $this->uploadGroupThumbnailPicture();
		  $isError = false;
	   }
	   if($_FILES['group_picture']['tmp_name'] <> ''){
			   // Delete old images
			   if($group_picture_old <> ''){
					 @unlink(EVENTGROUP_ROOT_PATH.'/'.$group_picture_old);
			   }
			   $group_picture = $group_picture;
		   }else{
			   $group_picture = $group_picture_old; 
		}
		if($_FILES['group_picture_thumb']['tmp_name'] <> ''){
			   // Delete old images
			   if($group_thumbnail_old <> ''){
					 @unlink(EVENTGROUP_ROOT_PATH.'/thumbs/'.$group_thumbnail_old);
			   }
			   $group_thumbnail = $group_thumbnail;
		   }else{
			   $group_thumbnail = $group_thumbnail_old; 
		}
	   if( !$isError ){
		   
		  $section = !isset($section) ? 0 : $section;
		  if( isset($producer_id) && !empty($producer_id) ){
			    $producer_id = $producer_id;
		   }else{
			  $producer_id = $_SESSION['adminId'];   

		   }
		   $display_order = ($display_order == '') ? 0 : $display_order;
		   $slug = empty($event_group_slug) ?  str_replace(' ', '_', strip_tags($title)) :  str_replace(' ', '_', $event_group_slug);
		   $slug = str_replace('_', '-', $slug);
		   $slug = str_replace('/', '-', $slug);
		   $slug = Generate_SEO_Url($slug);
		  // Update event_groups table
		    $data = array('group_thumbnail' => $group_thumbnail,
			             'group_picture' => $group_picture,
		                 'title' => htmlspecialchars($title),
						 'parent_id' => $parent_id,
						 'date_begin' => mysql_date($date_begin).' '.date('H:i:s'),
						 'date_end' => mysql_date($date_end).' '.date('H:i:s'),
						 'description' => htmlspecialchars($description),
						 'price_min' => $price_min,
						 'category_id' => $category_id,
						 'en_savoir_block1_name' => $en_savoir_block1_name,
						 'en_savoir_desc1' => htmlspecialchars($en_savoir_desc1),
						 'en_savoir_block2_name' => $en_savoir_block2_name,
						 'en_savoir_desc2' => htmlspecialchars($en_savoir_desc2),
						 'artist_name' => $artist_name,
						 'author_name' => $author_name,
						 'productor_name' => $productor_name,
						 'director_name' => $director_name,
						 'status' => $status,
						 'thumbnail_title' => $thumbnail_title,
						 'photo_title' => $photo_title,
						 'event_group_slug' => $slug,
						 'section' => $section,
						 'producer_id' => $producer_id,
						 'display_order' => $display_order,
						 'meta_title' => $meta_title, // New column
						 'meta_description' => $meta_description, // New column
						 'permalink' => trim($permalink),
						 'adv_image' => $this->uploadEventAdsUpdate() );
					 
		   $eventGroup = Models\Eventgroup::where('id', '=', $id)->update($data);
		   
 		   if(isset($_REQUEST['file_name']) && !empty($_REQUEST['file_name'])){	
		      $this->eventGroupVideoLinks($id);
		   }
		    if( isset($_REQUEST['comment_tile']) && !empty($_REQUEST['comment_tile']) ){
		       $this->saveSectionPicComments($id);  // Save picture comments
			}

			$this->saveEventGroupRoles($id);  // Save this event group roles
		    $this->saveEventGroupChildrenUpdate($id);
			if($status == 1){
			   	$this->unarchiveEvents($id); // Unarchive all events related to this event group
			}
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 
	   }
	  
	   
	}
	
	// Unarchive all events of an event group
	public function unarchiveEvents($event_group_id){
	     $eventdata = array('status' => 1);
		$eventArchive = Models\Event::where('eventgroup_id', '=', $event_group_id)->update($eventdata);		
	}
	
	// Save multiple categories
	public function saveMultipleCategories($event_group_id){
	   	// Check if categories are set
		if( isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']) ){
		    // First delete all categories of this event group
			$delete = Models\EventGroupCategory::where('events_group_id', $event_group_id)->delete();
			foreach($_REQUEST['category_id'] as $key=>$category_id){
			     if( !empty($category_id) ){
				  // Save to EventGroupCategory
				    $eventGroupCat = new Models\EventGroupCategory;
					$eventGroupCat->events_group_id = $event_group_id;
					$eventGroupCat->category_id = $category_id;
					$eventGroupCat->save();
				 }
			}
		}
		
		
	}
	
	
}
