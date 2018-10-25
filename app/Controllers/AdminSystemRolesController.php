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
*  Admin System Roles Controller
// Available Functions 
 1. system_roles
 2. getAjaxSystemRolesList
 3. saveRole
 4. updateRole
 5. getSystemRoleById
 
*/
class AdminSystemRolesController extends Base 
{
	protected $container;
	protected $lang;
	public function __construct($container)
	{
		$this->container = $container;
		$this->lang =  $this->container->view['adminLang'];
	}
	
	//System Roles page to display  all system roles
	 public function system_roles($request, $response) {
		 $params = array('title' => $this->lang['left_menu_system_roles_txt'],
						 'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/SystemRoles/system_roles.twig', $params);	
    }
	
    // Get all the roles via Ajax
	public function getAjaxSystemRolesList($request, $response){
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
		    $total   = Models\SystemRole::whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\SystemRole::get()->count(); // get count 
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
		    $roles_list = Models\SystemRole::whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			$roles_list = Models\SystemRole::skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		$data = array();
		foreach($roles_list as $get){
			$array_data = array();
			// Check if this role has assigned some users
			$role_id = Models\User::where('role_id', '=', $get->id)->count();
			if($role_id > 0 ){
				$array_data['role_exist']  = 'Y';
			}else{
				$array_data['role_exist']  = 'N';
			}
		  	
			$array_data['id']  = $get->id;
            $array_data['role_name']  = $get->title;
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
	
	// Save System Role
	public function saveRole($request, $response){
	   $isError = false;
	   $role_name = $request->getParam('role_name');
	   $status = $request->getParam('status');
	   $cityExist = Models\SystemRole::where('title', '=', $role_name)->first();
	   if( $cityExist){
		  
		 echo json_encode(array("status" => 'duplicate', 
		                  'message' => $this->lang['sys_role_name_txt'].' (<strong>'.$role_name. '</strong>) '.$this->lang['common_already_exist_txt'].'.'));
		 exit();	   
	   }else  if( empty($role_name) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['sys_role_name_msg_txt']));
		 exit();	   
	   } else{
		   // Save to roles table
		   $role = new Models\SystemRole;
		   $role->title = $role_name;
		   $role->status = $status;
		   $role->save();
		   
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));  
	   }
		
	}
	
	// Update System Role from Admin
	public function updateRole($request, $response){
		
	   $id   = $request->getParam('id');
	   $role_name = $request->getParam('role_name');
	   $status = $request->getParam('status');
	   $roleExist = Models\SystemRole::where('title', '=', $role_name)->where('id', '!=', $id)->first();
	   if( $roleExist){
		  
		 echo json_encode(array("status" => 'duplicate', 
		                  'message' => $this->lang['sys_role_name_txt'].' (<strong>'.$role_name. '</strong>) '.$this->lang['common_already_exist_txt'].'.'));
		 exit();	   
	   }else  if( empty($role_name) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['sys_role_name_msg_txt']));
		 exit();	   
	   }  else{
		   
		   // Save to Roles table
		   $data = array('title' => $role_name,
						 'status' => $status);
		  $role = Models\SystemRole::where('id', '=', $id)->update($data);					 
		  return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	   }
	}
	
	//  Function to get Get Role by id
	public function getSystemRoleById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// Get System Role by the requested ID
		$role = Models\SystemRole::find($id);
		// Return the response
		if ($role) {
			return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode($role));
        }
		
	}
	
	// Assign Modules Function
	public function assignModules($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$role = Models\SystemRole::find($id);
		$modules_list = Models\SystemModule::where('status', '=',1)->orderBy('id', 'ASC')->get(); 
		
		/* Get all system modules who are active */
		$role_modules = Models\RoleAllowedModules::where('role_id', '=', $id)->
		                selectRaw('GROUP_CONCAT( CONCAT(role_id,"_",module_id,"_",function_id) ) as role_mdoule_function')->
					    orderBy('id', 'ASC')->get();
									
		$params = array('title' => 'Assign Module',
		                 'role_data' => $role,
						 'modules_list' => $modules_list,
						 'role_modules' => $role_modules,
						 'current_url' => 'admin/system_roles');
        return $this->render($response, ADMIN_VIEW.'/SystemRoles/assign_modules.twig', $params);	
		
	}
	
	//  Save Role Modules
	public function saveRoleModule($request, $response){
	    $role_id   = $request->getParam('role_id');  // This is the role ID
	    $module_id = $request->getParam('controller_id'); // This is the Module ID
	    $function_id = $request->getParam('function_id'); // This is the Function ID like Add, Edit, Delete
		// Save to role_allowed_modules table
	    $saveModule = new Models\RoleAllowedModules;
	    $saveModule->role_id = $role_id;
	    $saveModule->module_id = $module_id;
		$saveModule->function_id = $function_id;
	    $saveModule->save();
		return 'Saved';
	}
	
	//  Remove Role Modules
	public function removeRoleModule($request, $response){
	    $role_id   = $request->getParam('role_id');  // This is the role ID
	    $module_id = $request->getParam('controller_id'); // This is the Module ID
	    $function_id = $request->getParam('function_id');	// This is the Function ID like Add, Edit, Delete
		// Now Delete from  role_allowed_modules 
		$delete = Models\RoleAllowedModules::where('role_id', $role_id)->
		                                     where('module_id', $module_id)->
											 where('function_id', $function_id)->delete();
		return 'Removed';
	}
	
	
	
}
