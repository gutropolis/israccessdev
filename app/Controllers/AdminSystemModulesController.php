<?php
namespace App\Controllers;
use Slim\Http\Request;
use App\Models\User;
use App\Models;
use App\Tools\Auth;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

/*
*  Admin System Modules Controller
// Available Functions 


*/
class AdminSystemModulesController extends Base 
{
	protected $container;
	protected $lang;
	public function __construct($container)
	{
		$this->container = $container;
		$this->lang =  $this->container->view['adminLang'];
	}
    
	//System Modules page to display system modules
	 public function system_modules($request, $response) {
		 $modules_list = Models\SystemModule::orderBy('id', 'ASC')->get();
		 $params = array('title' => $this->lang['left_menu_system_module_txt'],
		                 'modules_list' => $modules_list,
						 'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/SystemModules/system_modules.twig', $params);	
    }
	
	
   // Get all system modules Ajax function
   public function ajaxSystemModuleList($request, $response){
	   //error_reporting(0);
	   $isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('name', 'zipcode', 'status');
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
		if($request->getParam('sort') != null){
			  $sort = $request->getParam('sort')['sort'];	
			  $field = $request->getParam('sort')['field'];	
		}else{
			 $sort = 'desc';	
			 $field = 'id';	
		}
		
		$page     = $request->getParam('pagination')['page'];
		if( !empty($request->getParam('pagination')['pages']) ){
		  $pages    = $request->getParam('pagination')['pages'];
		}
		$per_page = $request->getParam('pagination')['perpage'];
		
		if($isSearched){
		    $total   = Models\SystemModule::whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\SystemModule::get()->count(); // get count 
		}
		if($page == 1){
		   $offset = 0;	
		   $perpage = 0;
		}else{
		  $offset = ($page-1);	
		  $perpage = $per_page;	
		}
		if($per_page <= 1){
		  $pages = intval($total/50);
	    }else{
	      $pages = intval($total/$per_page);
		}
		if($per_page <= 1){
		   $perPageLimit = 50;	
		}else{
		   $perPageLimit = $per_page;	
		}
		if($isSearched){
			// Get data based on Search
		    $cities_list = Models\SystemModule::whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			// Get data without search
			$cities_list = Models\SystemModule::skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		$data = array();
		foreach($cities_list as $get){
		  	$array_data = array();
			$array_data['id']  = $get->id;
            $array_data['module_name']  = $get->module_name;
			$array_data['status']  = $get->status;
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
   
   // get system module
   public function getSystemModuleById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
			
		$system_module = Models\SystemModule::find($id);
		if ($system_module) {
			return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode($system_module));
        }
		
	}
	
	// Update Module Status
	public function updateStatus($request, $response){
		$module_id = $request->getParam('module_id');
		$status = $request->getParam('status');
		// Here deal with Status
		if($status == 0){
			// If Status  0 it means the current status is Inactive/Disable so make it Enable / Active
			$new_value = 1;	
		}else{
			// If the Status is 1 it means the current status is Active/Enable so make it Disable/Inactive
		   $new_value = 0;	
		}
		$data = array('status' => $new_value); // Define the Array
		$updated = Models\SystemModule::where('id', '=', $module_id)->update($data); // Update the data
		return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE))); 
	}
	
	// Update System module
	public function updateSystemModule($request, $response){
	   $id   = $request->getParam('id'); // Module ID
	   $module_name = $request->getParam('module_name'); // Moudule Name Posted From Form
	   $status = $request->getParam('status'); // status of the mdoule
	   $sysModExist = Models\SystemModule::where('module_name', '=', $module_name)->where('id', '!=', $id)->first();
	   if( $sysModExist){
		 echo json_encode(array("status" => 'duplicate', 
		                  'message' => $this->lang['sys_module_name_txt'].' (<strong>'.$module_name. '</strong>) '.$this->lang['common_already_exist_txt'].'.'));
		 exit();  
	   }else  if( empty($module_name) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['sys_module_name_msg_txt']));
		 exit();
	   }else{
		   // Save to system module table
		   $data = array('module_name' => $module_name,
						 'status' => $status);
			// Update mdoule			 
		   $module = Models\SystemModule::where('id', '=', $id)->update($data);					 
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	   }
	}
	
	
}
