<?php
namespace App\Controllers;

use App\Models\User;
use App\Models;
use App\Tools\Auth;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;

/**
  Admin Login Controller
  Available Functions
  1. login
  2. logout
  3. checkAccess
  4. signIn
  5. getProfile
  6. updateProfile
  7. changePassword
  8.
  9.
  10.
  
*/
class AdminLoginController extends Base 
{
	protected $container;
	public function __construct($container)
	{
		$this->container = $container;
	}
	// Login 
	 public function login($request, $response) {
		 if( isProductorLogin() ) {
			  return $this->response->withStatus(200)->withHeader('Location', base_url.'/productor/dashboard'); 
				  exit;	
		 }else if( isOperatorLogin()) {
			  return $this->response->withStatus(200)->withHeader('Location', base_url.'/operator/dashboard'); 
				    exit;
		 }else if( isAdminLogin()) {
			 return $this->response->withStatus(200)->withHeader('Location', base_url.'/admin/dashboard'); 
				    exit;
		 }else{
		    $params = array('base_url' => base_url, 'title' => 'Culture Access');
            return $this->render($response, ADMIN_VIEW.'/Login/login.twig', $params);		
		 }
    }
	// Logout
	public function logout($request, $response) {
		 @session_start();
		 session_unset($_SESSION['isAdmin']);
		 session_destroy();
		 return $response->withRedirect(base_url.'/admin/login');		
    }
	
	// check access 
	public function checkAccess($request, $response){
		if(!isAdminLogin()) {
			return $response->withRedirect(base_url.'/admin/login');	
		}else{
			return $response->withRedirect(base_url.'/admin/dashboard');	
		}
	}
	
	// Sign in
	public function signIn($request, $response) {
		$email = $request->getParam('email');
		$password = $request->getParam('password');
		$login_opt = $request->getParam('login_opt');
		if( empty($email) ){
		   	echo eResponse('Please enter email address');
			exit();
		}else if( !empty($email) && !isValidEmail($email) ){
			echo eResponse('Please enter valid email address');
			exit();
		}else if( empty($password) ){
			echo eResponse('Please enter password');
			exit();
		}else{
			$AuthObj = new  Auth();
				$auth = $AuthObj->attempt(
					$email,
					$password,
					$login_opt
				);
			if($auth == true ) {
				if(isProductorLogin()){
					$redirect_page = '../productor/dashboard';
				}else if(isOperatorLogin()){
					$redirect_page = '../operator/dashboard';
				}else{
					$redirect_page = 'dashboard';
				}
				echo json_encode(array('status' => true, 'redirect_page' => $redirect_page));	
			    exit();
			}else{
			  echo eResponse('Authentication failed.');
			  exit();
			}
		}
		
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
		$loggedInUser = Models\User::find($id);
		
		if($loggedInUser['user_picture'] == '' || $loggedInUser['user_picture'] === null){
		     $loggedInUser['profile_pic_with_path'] = DEFAULT_PROFILE_IMG;
		}else{
			
			if( isProductorLogin() ){
			    $loggedInUser['profile_pic_with_path'] = PRODUCTOR_WEB_PATH.'/thumbs/'.$loggedInUser['user_picture'];
			}else{
				$loggedInUser['profile_pic_with_path'] = ADMIN_WEB_PATH.'/thumbs/'.$loggedInUser['user_picture'];
			}
		}
		$loggedInUser['profile_pic_with_path_show'] = 'Yes';
		if ($loggedInUser) {
            echo json_encode($loggedInUser);
        }
  }
  
    // Update logged in user
	public function updateProfile($request, $response){
	   $isError = false;
	   $id   = $request->getParam('id');
	   $adminName   = $request->getParam('admin_name');
	   $adminUsername   = $request->getParam('admin_username');
	   $adminEmail = $request->getParam('admin_email');
	   $admin_user_picture_old = $request->getParam('admin_user_picture_old');
	   $emailExist = Models\User::where('email', '=', $adminEmail)->where('id', '!=', $id)->first();
	   if(empty($adminName)){
		   $isError = true;
		   echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter your name.'));
		   exit();
	   }elseif(empty($adminUsername)){
		   $isError = true;
		   echo json_encode(array("status" => 'error', 
		                        'message' => 'Please enter your user name.'));
		   exit();
	   }else  if(!isValidEmail($adminEmail)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                    'message' => 'Please enter valid email.'));
		 exit();
	   }else if(isValidEmail($adminEmail) && $emailExist){
		   $isError = true;
		    echo json_encode(array("status" => 'duplicate', 
		                  'message' => 'Email (<strong>'.$adminEmail. '</strong>) already exist.'));
		   exit();	   
	   }else{
		   if(isset($_FILES) && $_FILES['admin_user_picture']['name'] <> ''){
			   $allowedExtensions = allowedExtensions();
			   $file = $_FILES['admin_user_picture']['tmp_name'];
			   $file_name = explode('.',$_FILES['admin_user_picture']['name']);
			   $file_extension = end($file_name);
			   if(!in_array($file_extension, $allowedExtensions)){
				   echo json_encode(array("status" => 'file_error', 
					               'message' => 'File with Type [<strong>'.$file_extension. '</strong>] is not allowed.'));	
				   exit();
			   }else{
				   
				   $file = $_FILES['admin_user_picture']['tmp_name'];
				    
				   if(!empty($_FILES['admin_user_picture']['tmp_name'])){
					   $file_new_name = md5(uniqid());
				        $filename = $file_new_name.'.'.$file_extension;
						if( isProductorLogin() ){
					        $resizedFile = PRODUCTOR_ROOT_PATH.'/thumbs/'.$filename;
						}else{
							$resizedFile = ADMIN_ROOT_PATH.'/thumbs/'.$filename;
						}
					   smart_resize_image($_FILES['admin_user_picture']['tmp_name'], null,  THUMB_WEIGHT, THUMB_HEIGHT , false , $resizedFile , false , false ,100 );
					   if( isProductorLogin() ){
					       move_uploaded_file($_FILES['admin_user_picture']['tmp_name'], PRODUCTOR_ROOT_PATH.'/'.$filename);
					   }else{
						  move_uploaded_file($_FILES['admin_user_picture']['tmp_name'], ADMIN_ROOT_PATH.'/'.$filename); 
					   }
					  $admin_profile_picture = $filename;
					 }else{
					  $admin_profile_picture = '';	
				   }
			   }   
		   }else{
			   $admin_profile_picture = '';
		   }   
		   if($_FILES['admin_user_picture']['tmp_name'] <> ''){
			   // Delete old images
			   if($admin_user_picture_old <> ''){
				   if( isProductorLogin() ){
					   @unlink(PRODUCTOR_ROOT_PATH.'/thumbs/'.$admin_user_picture_old);
					   @unlink(PRODUCTOR_ROOT_PATH.'/'.$admin_user_picture_old);
				   }else{
					   @unlink(ADMIN_ROOT_PATH.'/thumbs/'.$admin_user_picture_old);
					   @unlink(ADMIN_ROOT_PATH.'/'.$admin_user_picture_old);
				   }
			   }
			   $admin_profile_picture = $admin_profile_picture;
		   }else{
			   $admin_profile_picture = $admin_user_picture_old; 
		   }
		$isError = false;   
	   }
	   
	   
	   if(!$isError ){
		   // update to users table
		   $data = array('name' => $adminName,
		                 'username' => $adminUsername,
						 'email' => $adminEmail,
						 'user_picture' => $admin_profile_picture);		 
		   $userUpdate = Models\User::where('id', '=', $id)->update($data);
		   // Now get this user latest updated data and put in session
		   $user = Models\User::where('id', '=', $id)->first();
		   // Overwrite session values here
			$_SESSION['adminId'] = $user->id;
			$_SESSION['adminName'] = $user->name;
			$_SESSION['adminEmail'] = $user->email;
			$profile_picture = $user->user_picture;
			if($profile_picture == '' || $profile_picture === null){
		        $profile_pic_with_path = DEFAULT_PROFILE_IMG;
			 }else{
				 if( isProductorLogin() ){
				    $profile_pic_with_path = PRODUCTOR_WEB_PATH.'/thumbs/'.$profile_picture;
				 }else{
					 $profile_pic_with_path = ADMIN_WEB_PATH.'/thumbs/'.$profile_picture;
				 }
			 }
			$_SESSION['profile_pic'] = $profile_pic_with_path;	
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
		   $auth = $AuthObj->checkUserPassword($id,$current_pass);
		   if($auth == true ) {
			   // update to users table
			   $new_password = $AuthObj->changePassword($new_pass_confirm);
			   $data = array('password' => $new_password);	 
			   $user = Models\User::where('id', '=', $id)->update($data);
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
	
	// Function to log in the user by the Admin
	public function do_log_in_as($request, $response){
		// Get all the params posted from the Form
		$role_id = $request->getParam('role_id');
		$admin_user_id = $request->getParam('admin_user_id');
		// Get all the allowed modules related to this role Id
		 $role_modules = Models\RoleAllowedModules::where('role_id', '=', $role_id)->
		                selectRaw('GROUP_CONCAT( CONCAT(role_id,"_",module_id,"_",function_id) ) as role_mdoule_function')->
					    orderBy('id', 'ASC')->get();
		if( $role_modules->isEmpty() ) {
		       return 'No module is defined';	
		}else{
			$AuthObj = new  Auth();
				$auth = $AuthObj->LogInAs($admin_user_id);
			if($auth == true ) {
				$allowed_modules =  explode(',', $role_modules[0]->role_mdoule_function);
			    $_SESSION['allowed_modules'] = $allowed_modules; 
				$redirect_page = 'dashboard';
				return $response->withRedirect(base_url.'/admin/'.$redirect_page);	
			}
		      
		}
		
		
	}
	
	
}
