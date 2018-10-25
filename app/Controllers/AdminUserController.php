<?php
namespace App\Controllers;

use App\Models;
use App\Tools\Auth;
use App\Middleware\RouteMiddleware; 
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

class AdminUserController extends Base 
{
	
	protected $container;
	protected $lang;
	
	// Class constructor
	public function __construct($container){
	    $this->container = $container;
		$this->lang      = 	$this->container->view['adminLang'];
	}
	
	
	// Main function to display list of users 
	public function users($request, $response){
		// Get all the Active System Roles
		$system_roles = Models\SystemRole::where('status', '=', 1)->orderBy('title', 'ASC')->get();
		$params = array( 'title' => 'All Users',
		                  'current_url' => $request->getUri()->getPath(),
						  'system_roles' => $system_roles);
        return $this->render($response, ADMIN_VIEW.'/User/users.twig',$params);
	}
	
	
	// Ajax users list
	public function getAjaxUsersList($request, $response){
		
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
			  if(isset($request->getParam('query')['genSearch'])) {
				 $prefix = '';
			 foreach($fields as $field){
			    if(isset($request->getParam('query')['genSearch'])) {
				     $conditions[] = "$field LIKE '%" . ($request->getParam('query')['genSearch']) . "%'";
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
		    $total   = Models\User::where('type', '=', 'Admin')
					                ->whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\User::where('type', '=', 'Admin')
					                ->count(); // get count 
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
		    $members_list = Models\User::where('type', '=', 'Admin')
										->whereRaw($whereData)
										->skip($offset*$perPageLimit)
										->take($perPageLimit)
										->orderBy($field, $sort)
										->get();
		}else{
			$members_list = Models\User::where('type', '=', 'Admin')
										->skip($offset*$perPageLimit)
										->take($perPageLimit)
										->orderBy($field, $sort)
										->get();
		}
		
		$data = array();
		foreach($members_list as $get){
			$user_type  = $get['type'];
			$role_id = $get['role_id'];
			if($role_id > 0){
				// Get the system role name only
				$user_role_name = Models\SystemRole::where('id', '=',  $role_id)->first()->title;
			}else{
			   $user_role_name = 'Super Admin';	
			}
			$array_data['id']  =  $get['id'];
			$array_data['name'] = $get['name'];
			$array_data['email'] = $get['email'];
			$array_data['reg_date'] = hr_date($get['registration_date']);
			$array_data['user_role'] = $user_role_name;
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
	
	// Get user by ID
	public function getAdminUserById($request, $response, $args){
		$id = $args['id']; // admin id
		$validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		// Get admin user data here
		$user = Models\User::find($id);
		if ($user) {
            echo json_encode($user);
        }
	}
	
	// Save Admin User 
	public function saveUser($request, $response){
		$isError = false;
		// Get all form posted data here
		$admin_name = $request->getParam('admin_name');
		$admin_email  = $request->getParam('admin_email');
		$admin_role = $request->getParam('role_id');
		$admin_password = $request->getParam('admin_password');
		$status  = 1;
		// Check if any admin name already exist
		$adminExist = Models\User::where('name', '=', $admin_name)->first();
		if(empty($admin_name)){
			$isError = true;
		   echo json_encode(array('status' => 'error',
		                          'message' => 'Enter Admin Name'));	
		}else if( empty($admin_email) ){
		    $isError = true;
			echo json_encode( array('status' => 'error',
			                        'message' => 'Enter Admin Email') );	
		}else if(!isValidEmail($admin_email)){
			$isError = true;
			echo json_encode( array('status' => 'error',
			                        'message' => 'Enter valid email address'));
		}else if(isValidEmail($admin_email) && $adminExist){
		   $isError = true;
		    echo json_encode(array("status" => 'duplicate', 
		                  'message' => 'Admin Email (<strong>'.$admin_email. '</strong>) already exist'));
		   exit();	   
	   }else if( empty($admin_role) ){
		  $isError = true;
		  echo json_encode( array('status' => 'error',
		                          'message' => 'Select Admin Role') );   
	   }else if( empty($admin_password) ){
		  $isError = true;
		  echo json_encode( array('status' => 'error',
		                          'message' => 'Enter Admin Password') );   
	   }else {
		   $isError = false; 
		   // Get the auth Tools here
		   $AuthObj = new  Auth();
		   $new_password = $AuthObj->changePassword($admin_password);
		   // Get user model object
		   $user = new Models\User;
		   $user->name = $admin_name;
		   $user->email = $admin_email;
		   $user->password = $new_password;
		   $user->status = $status;
		   $user->credit = 0.00;
		   $user->type = 'Admin';
		   $user->role_id = $admin_role;
		   $user->save();
		   return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));      
	   }	
	}
	 
	// Update User
	public function updateUser($request, $response){
	    $isError = false;
		$id = $request->getParam('id');
	    $admin_name = $request->getParam('admin_name');
		$admin_email  = $request->getParam('admin_email');
		$admin_role = $request->getParam('role_id');
		$admin_password = $request->getParam('admin_password');
		$status = $request->getParam('status');
		// Check if any admin name already exist
		$adminExist = Models\User::where('name', '=', $admin_name)->where('id', '!=', $id)->first();
		if(empty($admin_name)){
			$isError = true;
		   echo json_encode(array('status' => 'error',
		                          'message' => 'Enter Admin Name'));	
		}else if( empty($admin_email) ){
		    $isError = true;
			echo json_encode( array('status' => 'error',
			                        'message' => 'Enter Admin Email') );	
		}else if(!isValidEmail($admin_email)){
			$isError = true;
			echo json_encode( array('status' => 'error',
			                        'message' => 'Enter valid email address'));
		}else if(isValidEmail($admin_email) && $adminExist){
		   $isError = true;
		    echo json_encode(array("status" => 'duplicate', 
		                  'message' => 'Admin Email (<strong>'.$admin_email. '</strong>) already exist'));
		   exit();	   
	   }else if( empty($admin_role) ){
		  $isError = true;
		  echo json_encode( array('status' => 'error',
		                          'message' => 'Select Admin Role') );   
	   }else {
		   $isError = false; 
		   if( empty($admin_password) ){
		   // update to users table
		   $data = array('name' => $admin_name,
						 'email' => $admin_email,
						 'role_id' => $admin_role,
						 'status' => $status);
						 
		   }else{
			   // Get the auth Tools here
		       $AuthObj = new  Auth();
		       $new_password = $AuthObj->changePassword($admin_password);
			   $data = array('name' => $admin_name,
						 'email' => $admin_email,
						 'password' => $new_password,
						 'role_id' => $admin_role,
						 'status' => $status);
		   }
		   $user = Models\User::where('id', '=', $id)->update($data);
		    return $response
            ->withHeader('Content-type','application/json')
            ->write(json_encode(array('status' => TRUE)));
	   }
		
	}
	
	// Function Log In As
	public function log_in_as($request, $response){
		// Get all the Active System Roles
		$system_roles = Models\SystemRole::where('status', '=', 1)->orderBy('title', 'ASC')->get();
		$params = array( 'title' => 'Log In As',
		                  'current_url' => $request->getUri()->getPath(),
						  'system_roles' => $system_roles);
        return $this->render($response, ADMIN_VIEW.'/User/log_in_as.twig',$params);
	}
	
	// Get all Admin users based on Role Id
	public function getAdminUsersList($request, $response, $args){
		$role_id = $args['role_id'];
		$options = '';
		$adminUsers = Models\User::where('status', '=', 1)->where('role_id', '=', $role_id)->orderBy('name', 'ASC')->get();
		foreach($adminUsers as $user){
		   $options .= '<option value="'.$user->id.'">'.$user->name.'</option>';	
		}
		$sel = '<option value="">Select Admin User</option>';
		return $sel.$options;
	}
	
	// Get role allowed modules 
	public function getRoleModulesList($request, $response, $args){
		$role_id = $args['role_id'];
		// Get all modules having status active
		$modules_list = Models\SystemModule::where('status', '=',1)->orderBy('id', 'ASC')->get(); 
		
		$tr = '';
		if($modules_list->isEmpty() ){
			$tr .= '<tr><td colspan="5" style="text-align:center; color:red">No modules found.</td></tr>';
		}else{
			$i=1;
			$ftn_no = '<i class="fa fa-times-circle not-allowed"></i>';
			$ftn_yes = '<i class="fa fa-check-circle-o allowed"></i>';	
		  foreach($modules_list as $module){
			  $module_id = $module->id;
			 /* Get all system modules who are active */
		     $role_modules = Models\RoleAllowedModules::where('role_id', '=', $role_id)->where('module_id', '=', $module_id)->
		                selectRaw('GROUP_CONCAT( CONCAT(role_id,"_",module_id,"_",function_id) ) as role_mdoule_function')->
					    orderBy('id', 'ASC')->get(); 
			 if( $role_modules->isEmpty() ){
				 $add = ''; 
			 }else{
				$allowed_module = explode(',',$role_modules[0]->role_mdoule_function);
				$module_add = $role_id.'_'.$module_id.'_1';
				$module_edit = $role_id.'_'.$module_id.'_2';
				$module_del = $role_id.'_'.$module_id.'_3';
				$module_view = $role_id.'_'.$module_id.'_4';
				if( in_array($module_add,  $allowed_module) ){
				     $add = $ftn_yes;
				}else{
				     $add = $ftn_no;	
				}
				if( in_array($module_edit , $allowed_module)){
				     $edit = $ftn_yes;	
				}else{
				     $edit = $ftn_no;	
				}
				
				if( in_array( $module_del , $allowed_module ) ){
				     $del = $ftn_yes;	
				}else{
				    $del = $ftn_no;	
				}
				
				if( in_array( $module_view , $allowed_module ) ){
				     $view = $ftn_yes;	
				}else{
				    $view = $ftn_no;	
				}
			 }
			  $tr .= '<tr><td>'.$i.'</td><td>'.$module->module_name.'</td><td>'.$add.'</td><td>'.$edit.'</td><td>'.$del.'</td><td>'.$view.'</td></tr>';
	 	  $i++;
		  }
		}
		return $tr;
		
	}
	
	
	 
	 
	 
	
}
