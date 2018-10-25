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
		if( !isAdminLogin() ) {
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
	   
	   // Check if there is no error
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
		       // Do nothing
			   $Yes = 'Y';
					$No = 'N';
					$roleID = $_SESSION['adminRoleId'];
					$_SESSION['is_dashboard_view']       = $No;
					$_SESSION['is_advertisement_add']    = $No;
					$_SESSION['is_advertisement_edit']   = $No;
					$_SESSION['is_advertisement_del']    = $No;
					$_SESSION['is_city_add']             = $No;
					$_SESSION['is_city_edit']            = $No;
					$_SESSION['is_city_del']             = $No;
					$_SESSION['is_currency_add']         = $No;
					$_SESSION['is_currency_edit']        = $No;
					$_SESSION['is_currency_del']         = $No;
					$_SESSION['is_slider_add']           = $No;
					$_SESSION['is_slider_edit']          = $No;
					$_SESSION['is_slider_del']           = $No;
					$_SESSION['is_cat_page_slider_add']  = $No;
					$_SESSION['is_cat_page_slider_edit'] = $No;
					$_SESSION['is_cat_page_slider_del']  = $No;
					$_SESSION['is_category_add']         = $No;
					$_SESSION['is_category_edit']        = $No;
					$_SESSION['is_category_del']         = $No;
					$_SESSION['is_auditorium_add']       = $No;
					$_SESSION['is_auditorium_edit']      = $No;
					$_SESSION['is_auditorium_del']       = $No;
					$_SESSION['is_artist_add']           = $No;
					$_SESSION['is_artist_edit']          = $No;
					$_SESSION['is_artist_del']           = $No;
					$_SESSION['is_productor_add']        = $No;
					$_SESSION['is_productor_edit']       = $No;
					$_SESSION['is_productor_del']        = $No;
					$_SESSION['is_member_add']           = $No;
					$_SESSION['is_member_edit']          = $No;
					$_SESSION['is_member_del']           = $No;
					$_SESSION['is_operator_add']         = $No;
					$_SESSION['is_operator_edit']        = $No;
					$_SESSION['is_operator_del']         = $No;
					$_SESSION['is_section_add']          = $No;
					$_SESSION['is_section_edit']         = $No;
					$_SESSION['is_section_del']          = $No;
					$_SESSION['is_event_group_add']      = $No;
					$_SESSION['is_event_group_edit']     = $No;
					$_SESSION['is_event_group_del']      = $No;
					$_SESSION['is_event_add']            = $No;
					$_SESSION['is_event_edit']           = $No;
					$_SESSION['is_event_del']            = $No;
					$_SESSION['is_dont_miss_event_add']  = $No;
					$_SESSION['is_dont_miss_event_edit'] = $No;
					$_SESSION['is_dont_miss_event_del']  = $No;
					$_SESSION['is_event_of_day_add']     = $No;
					$_SESSION['is_event_of_day_edit']    = $No;
					$_SESSION['is_event_of_day_del']     = $No;
					$_SESSION['is_seat_add']             = $No;
					$_SESSION['is_seat_edit']            = $No;
					$_SESSION['is_seat_del']             = $No;
					$_SESSION['is_ticket_add']           = $No;
					$_SESSION['is_ticket_edit']          = $No;
					$_SESSION['is_ticket_del']           = $No;
					$_SESSION['is_ticket_view']           = $No;
					$_SESSION['is_user_add']             = $No;
					$_SESSION['is_user_edit']            = $No;
					$_SESSION['is_user_del']             = $No;
					$_SESSION['is_partner_add']          = $No;
					$_SESSION['is_partner_edit']         = $No;
					$_SESSION['is_partner_del']          = $No;
					$_SESSION['is_cms_view']             = $No;
					$_SESSION['is_cms_edit']             = $No;
					$_SESSION['is_payment_type_add']     = $No;
					$_SESSION['is_payment_type_edit']    = $No;
					$_SESSION['is_payment_type_del']     = $No;
					$_SESSION['is_community_add']        = $No;
					$_SESSION['is_community_edit']       = $No;
					$_SESSION['is_community_del']        = $No;
					$_SESSION['is_community_page_add']   = $No;
					$_SESSION['is_community_page_edit']  = $No;
					$_SESSION['is_community_page_del']   = $No;
					$_SESSION['is_order_view']           = $No;
					$_SESSION['is_ticket_selling_view']  = $No;
					$_SESSION['is_setting_edit']         = $No;
					$_SESSION['is_setting_view']         = $No;
					//  BEGIN: Allowed Left Menu Items
					$_SESSION['is_dashboard_allowed']    = $Yes;
					$_SESSION['is_advertisement_allowed'] = $No;
					$_SESSION['is_city_allowed']          = $No;
					$_SESSION['is_currency_allowed']      = $No;
					$_SESSION['is_slider_allowed']        = $No;
					$_SESSION['is_cat_page_slider_allowed'] = $No;
					$_SESSION['is_category_allowed']       = $No;
					$_SESSION['is_auditorium_allowed']    = $No;
					$_SESSION['is_artist_allowed']        = $No;
					$_SESSION['is_productor_allowed']     = $No;
					$_SESSION['is_member_allowed']        = $No;
					$_SESSION['is_operator_allowed']      = $No;
					$_SESSION['is_section_allowed']       = $No;
					$_SESSION['is_event_group_allowed']   = $No;
					$_SESSION['is_event_allowed']         = $No;
					$_SESSION['is_dont_miss_event_allowed'] = $No;
					$_SESSION['is_event_of_day_allowed']  = $No;
					$_SESSION['is_seat_allowed']          = $No;
					$_SESSION['is_ticket_allowed']        = $No;
					$_SESSION['is_user_allowed']          = $No;
					$_SESSION['is_partner_allowed']       = $No;
					$_SESSION['is_cms_allowed']           = $No;
					$_SESSION['is_payment_type_allowed']  = $No;
					$_SESSION['is_community_allowed']     = $No;
					$_SESSION['is_community_page_allowed'] = $No;
					$_SESSION['is_order_allowed']         = $No;
					$_SESSION['is_ticket_selling_allowed'] = $No;
					$_SESSION['is_setting_allowed']        = $No;
					$_SESSION['is_coupon_allowed']        = $No;
					$_SESSION['is_reports_allowed']        = $No;
					$_SESSION['is_subscribers_allowed']        = $No;
					
		}else{
			$AuthObj = new  Auth();
				$auth = $AuthObj->LogInAs($admin_user_id);
			if($auth == true ) {
				if( empty($role_modules[0]->role_mdoule_function) ){
					// Do nothing
					$Yes = 'Y';
					$No = 'N';
					$roleID = $_SESSION['adminRoleId'];
					$_SESSION['is_dashboard_view']       = $No;
					$_SESSION['is_advertisement_add']    = $No;
					$_SESSION['is_advertisement_edit']   = $No;
					$_SESSION['is_advertisement_del']    = $No;
					$_SESSION['is_city_add']             = $No;
					$_SESSION['is_city_edit']            = $No;
					$_SESSION['is_city_del']             = $No;
					$_SESSION['is_currency_add']         = $No;
					$_SESSION['is_currency_edit']        = $No;
					$_SESSION['is_currency_del']         = $No;
					$_SESSION['is_slider_add']           = $No;
					$_SESSION['is_slider_edit']          = $No;
					$_SESSION['is_slider_del']           = $No;
					$_SESSION['is_cat_page_slider_add']  = $No;
					$_SESSION['is_cat_page_slider_edit'] = $No;
					$_SESSION['is_cat_page_slider_del']  = $No;
					$_SESSION['is_category_add']         = $No;
					$_SESSION['is_category_edit']        = $No;
					$_SESSION['is_category_del']         = $No;
					$_SESSION['is_auditorium_add']       = $No;
					$_SESSION['is_auditorium_edit']      = $No;
					$_SESSION['is_auditorium_del']       = $No;
					$_SESSION['is_artist_add']           = $No;
					$_SESSION['is_artist_edit']          = $No;
					$_SESSION['is_artist_del']           = $No;
					$_SESSION['is_productor_add']        = $No;
					$_SESSION['is_productor_edit']       = $No;
					$_SESSION['is_productor_del']        = $No;
					$_SESSION['is_member_add']           = $No;
					$_SESSION['is_member_edit']          = $No;
					$_SESSION['is_member_del']           = $No;
					$_SESSION['is_operator_add']         = $No;
					$_SESSION['is_operator_edit']        = $No;
					$_SESSION['is_operator_del']         = $No;
					$_SESSION['is_section_add']          = $No;
					$_SESSION['is_section_edit']         = $No;
					$_SESSION['is_section_del']          = $No;
					$_SESSION['is_event_group_add']      = $No;
					$_SESSION['is_event_group_edit']     = $No;
					$_SESSION['is_event_group_del']      = $No;
					$_SESSION['is_event_add']            = $No;
					$_SESSION['is_event_edit']           = $No;
					$_SESSION['is_event_del']            = $No;
					$_SESSION['is_dont_miss_event_add']  = $No;
					$_SESSION['is_dont_miss_event_edit'] = $No;
					$_SESSION['is_dont_miss_event_del']  = $No;
					$_SESSION['is_event_of_day_add']     = $No;
					$_SESSION['is_event_of_day_edit']    = $No;
					$_SESSION['is_event_of_day_del']     = $No;
					$_SESSION['is_seat_add']             = $No;
					$_SESSION['is_seat_edit']            = $No;
					$_SESSION['is_seat_del']             = $No;
					$_SESSION['is_ticket_add']           = $No;
					$_SESSION['is_ticket_edit']          = $No;
					$_SESSION['is_ticket_del']           = $No;
					$_SESSION['is_ticket_view']           = $No;
					$_SESSION['is_user_add']             = $No;
					$_SESSION['is_user_edit']            = $No;
					$_SESSION['is_user_del']             = $No;
					$_SESSION['is_partner_add']          = $No;
					$_SESSION['is_partner_edit']         = $No;
					$_SESSION['is_partner_del']          = $No;
					$_SESSION['is_cms_view']             = $No;
					$_SESSION['is_cms_edit']             = $No;
					$_SESSION['is_payment_type_add']     = $No;
					$_SESSION['is_payment_type_edit']    = $No;
					$_SESSION['is_payment_type_del']     = $No;
					$_SESSION['is_community_add']        = $No;
					$_SESSION['is_community_edit']       = $No;
					$_SESSION['is_community_del']        = $No;
					$_SESSION['is_community_page_add']   = $No;
					$_SESSION['is_community_page_edit']  = $No;
					$_SESSION['is_community_page_del']   = $No;
					$_SESSION['is_order_view']           = $No;
					$_SESSION['is_ticket_selling_view']  = $No;
					$_SESSION['is_setting_edit']         = $No;
					$_SESSION['is_setting_view']         = $No;
					//  BEGIN: Allowed Left Menu Items
					$_SESSION['is_dashboard_allowed']    = $Yes;
					$_SESSION['is_advertisement_allowed'] = $No;
					$_SESSION['is_city_allowed']          = $No;
					$_SESSION['is_currency_allowed']      = $No;
					$_SESSION['is_slider_allowed']        = $No;
					$_SESSION['is_cat_page_slider_allowed'] = $No;
					$_SESSION['is_category_allowed']       = $No;
					$_SESSION['is_auditorium_allowed']    = $No;
					$_SESSION['is_artist_allowed']        = $No;
					$_SESSION['is_productor_allowed']     = $No;
					$_SESSION['is_member_allowed']        = $No;
					$_SESSION['is_operator_allowed']      = $No;
					$_SESSION['is_section_allowed']       = $No;
					$_SESSION['is_event_group_allowed']   = $No;
					$_SESSION['is_event_allowed']         = $No;
					$_SESSION['is_dont_miss_event_allowed'] = $No;
					$_SESSION['is_event_of_day_allowed']  = $No;
					$_SESSION['is_seat_allowed']          = $No;
					$_SESSION['is_ticket_allowed']        = $No;
					$_SESSION['is_user_allowed']          = $No;
					$_SESSION['is_partner_allowed']       = $No;
					$_SESSION['is_cms_allowed']           = $No;
					$_SESSION['is_payment_type_allowed']  = $No;
					$_SESSION['is_community_allowed']     = $No;
					$_SESSION['is_community_page_allowed'] = $No;
					$_SESSION['is_order_allowed']         = $No;
					$_SESSION['is_ticket_selling_allowed'] = $No;
					$_SESSION['is_setting_allowed']        = $No;
					$_SESSION['is_coupon_allowed']        = $No;
					$_SESSION['is_reports_allowed']        = $No;
					$_SESSION['is_subscribers_allowed']        = $No;
				}else{
				    $allowed_modules =  explode(',', $role_modules[0]->role_mdoule_function);
			        /* =========== Set the modules to Session  ======================= */
					$Yes = 'Y';
					$No = 'N';
					$roleID = $_SESSION['adminRoleId'];
					$_SESSION['is_dashboard_view']       = $No;
					$_SESSION['is_advertisement_add']    = $No;
					$_SESSION['is_advertisement_edit']   = $No;
					$_SESSION['is_advertisement_del']    = $No;
					$_SESSION['is_city_add']             = $No;
					$_SESSION['is_city_edit']            = $No;
					$_SESSION['is_city_del']             = $No;
					$_SESSION['is_currency_add']         = $No;
					$_SESSION['is_currency_edit']        = $No;
					$_SESSION['is_currency_del']         = $No;
					$_SESSION['is_slider_add']           = $No;
					$_SESSION['is_slider_edit']          = $No;
					$_SESSION['is_slider_del']           = $No;
					$_SESSION['is_cat_page_slider_add']  = $No;
					$_SESSION['is_cat_page_slider_edit'] = $No;
					$_SESSION['is_cat_page_slider_del']  = $No;
					$_SESSION['is_category_add']         = $No;
					$_SESSION['is_category_edit']        = $No;
					$_SESSION['is_category_del']         = $No;
					$_SESSION['is_auditorium_add']       = $No;
					$_SESSION['is_auditorium_edit']      = $No;
					$_SESSION['is_auditorium_del']       = $No;
					$_SESSION['is_artist_add']           = $No;
					$_SESSION['is_artist_edit']          = $No;
					$_SESSION['is_artist_del']           = $No;
					$_SESSION['is_productor_add']        = $No;
					$_SESSION['is_productor_edit']       = $No;
					$_SESSION['is_productor_del']        = $No;
					$_SESSION['is_member_add']           = $No;
					$_SESSION['is_member_edit']          = $No;
					$_SESSION['is_member_del']           = $No;
					$_SESSION['is_operator_add']         = $No;
					$_SESSION['is_operator_edit']        = $No;
					$_SESSION['is_operator_del']         = $No;
					$_SESSION['is_section_add']          = $No;
					$_SESSION['is_section_edit']         = $No;
					$_SESSION['is_section_del']          = $No;
					$_SESSION['is_event_group_add']      = $No;
					$_SESSION['is_event_group_edit']     = $No;
					$_SESSION['is_event_group_del']      = $No;
					$_SESSION['is_event_add']            = $No;
					$_SESSION['is_event_edit']           = $No;
					$_SESSION['is_event_del']            = $No;
					$_SESSION['is_dont_miss_event_add']  = $No;
					$_SESSION['is_dont_miss_event_edit'] = $No;
					$_SESSION['is_dont_miss_event_del']  = $No;
					$_SESSION['is_event_of_day_add']     = $No;
					$_SESSION['is_event_of_day_edit']    = $No;
					$_SESSION['is_event_of_day_del']     = $No;
					$_SESSION['is_seat_add']             = $No;
					$_SESSION['is_seat_edit']            = $No;
					$_SESSION['is_seat_del']             = $No;
					$_SESSION['is_ticket_add']           = $No;
					$_SESSION['is_ticket_edit']          = $No;
					$_SESSION['is_ticket_del']           = $No;
					$_SESSION['is_ticket_view']           = $No;
					$_SESSION['is_user_add']             = $No;
					$_SESSION['is_user_edit']            = $No;
					$_SESSION['is_user_del']             = $No;
					$_SESSION['is_partner_add']          = $No;
					$_SESSION['is_partner_edit']         = $No;
					$_SESSION['is_partner_del']          = $No;
					$_SESSION['is_cms_view']             = $No;
					$_SESSION['is_cms_edit']             = $No;
					$_SESSION['is_payment_type_add']     = $No;
					$_SESSION['is_payment_type_edit']    = $No;
					$_SESSION['is_payment_type_del']     = $No;
					$_SESSION['is_community_add']        = $No;
					$_SESSION['is_community_edit']       = $No;
					$_SESSION['is_community_del']        = $No;
					$_SESSION['is_community_page_add']   = $No;
					$_SESSION['is_community_page_edit']  = $No;
					$_SESSION['is_community_page_del']   = $No;
					$_SESSION['is_order_view']           = $No;
					$_SESSION['is_ticket_selling_view']  = $No;
					$_SESSION['is_setting_edit']         = $No;
					$_SESSION['is_setting_view']         = $No;
					//  BEGIN: Allowed Left Menu Items
					$_SESSION['is_dashboard_allowed']    = $No;
					$_SESSION['is_advertisement_allowed'] = $No;
					$_SESSION['is_city_allowed']          = $No;
					$_SESSION['is_currency_allowed']      = $No;
					$_SESSION['is_slider_allowed']        = $No;
					$_SESSION['is_cat_page_slider_allowed'] = $No;
					$_SESSION['is_category_allowed']       = $No;
					$_SESSION['is_auditorium_allowed']    = $No;
					$_SESSION['is_artist_allowed']        = $No;
					$_SESSION['is_productor_allowed']     = $No;
					$_SESSION['is_member_allowed']        = $No;
					$_SESSION['is_operator_allowed']      = $No;
					$_SESSION['is_section_allowed']       = $No;
					$_SESSION['is_event_group_allowed']   = $No;
					$_SESSION['is_event_allowed']         = $No;
					$_SESSION['is_dont_miss_event_allowed'] = $No;
					$_SESSION['is_event_of_day_allowed']  = $No;
					$_SESSION['is_seat_allowed']          = $No;
					$_SESSION['is_ticket_allowed']        = $No;
					$_SESSION['is_user_allowed']          = $No;
					$_SESSION['is_partner_allowed']       = $No;
					$_SESSION['is_cms_allowed']           = $No;
					$_SESSION['is_payment_type_allowed']  = $No;
					$_SESSION['is_community_allowed']     = $No;
					$_SESSION['is_community_page_allowed'] = $No;
					$_SESSION['is_order_allowed']         = $No;
					$_SESSION['is_ticket_selling_allowed'] = $No;
					$_SESSION['is_setting_allowed']        = $No;
					$_SESSION['is_coupon_allowed']        = $No;
					$_SESSION['is_reports_allowed']        = $No;
					$_SESSION['is_subscribers_allowed']        = $No;
					
					/* === START : Dashboard  === */
					 $dashboard_view_val = $roleID .'_1_4'; 
					 if( in_array($dashboard_view_val, $allowed_modules) ){
						 $_SESSION['is_dashboard_view'] = $Yes;
					 }
					/* === END   : Dashboard  === */
					
					
					/* === START : Advertisements  === */
					 $advertisement_add_val  = $roleID.'_2_1'; 
					 $advertisement_edit_val = $roleID.'_2_2';
					 $advertisement_del_val  = $roleID.'_2_3';
					 if( in_array($advertisement_add_val, $allowed_modules) ){
						 $_SESSION['is_advertisement_add'] = $Yes;
						 $_SESSION['is_advertisement_allowed'] = $Yes;
					 }
					 
					 if( in_array($advertisement_edit_val, $allowed_modules) ){
						 $_SESSION['is_advertisement_edit'] = $Yes;
						 $_SESSION['is_advertisement_allowed'] = $Yes;
					 }
					 
					 if( in_array($advertisement_del_val, $allowed_modules) ){
						 $_SESSION['is_advertisement_del'] = $Yes;
						 $_SESSION['is_advertisement_allowed'] = $Yes;
					 }
					/* === END   : Advertisements  === */
					
					
					/* === START : Cities  === */
					 $city_add_val  = $roleID.'_3_1'; 
					 $city_edit_val = $roleID.'_3_2';
					 $city_del_val  = $roleID.'_3_3';
					 if( in_array($city_add_val, $allowed_modules) ){
						 $_SESSION['is_city_add'] = $Yes;
						 $_SESSION['is_city_allowed'] = $Yes;
					 }
					 
					 if( in_array($city_edit_val, $allowed_modules) ){
						 $_SESSION['is_city_edit'] = $Yes;
						 $_SESSION['is_city_allowed'] = $Yes;
					 }
					 
					 if( in_array($city_del_val, $allowed_modules) ){
						 $_SESSION['is_city_del'] = $Yes;
						 $_SESSION['is_city_allowed'] = $Yes;
					 }
					/* === END   : Cities  === */
					
					/* === START : Currencies  === */
					 $currency_add_val  = $roleID.'_4_1'; 
					 $currency_edit_val = $roleID.'_4_2';
					 $currency_del_val  = $roleID.'_4_3';
					 if( in_array($currency_add_val, $allowed_modules) ){
						 $_SESSION['is_currency_add'] = $Yes;
						 $_SESSION['is_currency_allowed'] = $Yes;
					 }
					 
					 if( in_array($currency_edit_val, $allowed_modules) ){
						 $_SESSION['is_currency_edit'] = $Yes;
						 $_SESSION['is_currency_allowed'] = $Yes;
					 }
					 
					 if( in_array($currency_del_val, $allowed_modules) ){
						 $_SESSION['is_currency_del'] = $Yes;
						 $_SESSION['is_currency_allowed'] = $Yes;
					 }
					/* === END   : Currencies  === */
					
					/* === START : Sliders  === */
					 $slider_add_val  = $roleID.'_5_1'; 
					 $slider_edit_val = $roleID.'_5_2';
					 $slider_del_val  = $roleID.'_5_3';
					 if( in_array($slider_add_val, $allowed_modules) ){
						 $_SESSION['is_slider_add'] = $Yes;
						 $_SESSION['is_slider_allowed'] = $Yes;
					 }
					 
					 if( in_array($slider_edit_val, $allowed_modules) ){
						 $_SESSION['is_slider_edit'] = $Yes;
						 $_SESSION['is_slider_allowed'] = $Yes;
					 }
					 
					 if( in_array($slider_del_val, $allowed_modules) ){
						 $_SESSION['is_slider_del'] = $Yes;
						 $_SESSION['is_slider_allowed'] = $Yes;
					 }
					/* === END   : Sliders  === */
					
					/* === START : Category Page Slider  === */
					 $cat_page_slider_add_val  = $roleID.'_6_1'; 
					 $cat_page_slider_edit_val = $roleID.'_6_2';
					 $cat_page_slider_del_val  = $roleID.'_6_3';
					 if( in_array($cat_page_slider_add_val, $allowed_modules) ){
						 $_SESSION['is_cat_page_slider_add'] = $Yes;
						 $_SESSION['is_cat_page_slider_allowed'] = $Yes;
					 }
					 
					 if( in_array($cat_page_slider_edit_val, $allowed_modules) ){
						 $_SESSION['is_cat_page_slider_edit'] = $Yes;
						 $_SESSION['is_cat_page_slider_allowed'] = $Yes;
					 }
					 
					 if( in_array($cat_page_slider_del_val, $allowed_modules) ){
						 $_SESSION['is_cat_page_slider_del'] = $Yes;
						 $_SESSION['is_cat_page_slider_allowed'] = $Yes;
					 }
					/* === END   : Category Page Slider  === */
					
					
					/* === START : Categories  === */
					 $category_add_val  = $roleID.'_7_1'; 
					 $category_edit_val = $roleID.'_7_2';
					 $category_del_val  = $roleID.'_7_3';
					 if( in_array($category_add_val, $allowed_modules) ){
						 $_SESSION['is_category_add'] = $Yes;
						 $_SESSION['is_category_allowed'] = $Yes;
					 }
					 
					 if( in_array($category_edit_val, $allowed_modules) ){
						 $_SESSION['is_category_edit'] = $Yes;
						 $_SESSION['is_category_allowed'] = $Yes;
					 }
					 
					 if( in_array($category_del_val, $allowed_modules) ){
						 $_SESSION['is_category_del'] = $Yes;
						 $_SESSION['is_category_allowed'] = $Yes;
					 }
					/* === END   : Categories  === */
					
					/* === START : Auditoriums  === */
					 $auditorium_add_val  = $roleID.'_8_1'; 
					 $auditorium_edit_val = $roleID.'_8_2';
					 $category_del_val    = $roleID.'_8_3';
					 if( in_array($auditorium_add_val, $allowed_modules) ){
						 $_SESSION['is_auditorium_add'] = $Yes;
						 $_SESSION['is_auditorium_allowed'] = $Yes;
					 }
					 
					 if( in_array($auditorium_edit_val, $allowed_modules) ){
						 $_SESSION['is_auditorium_edit'] = $Yes;
						 $_SESSION['is_auditorium_allowed'] = $Yes;
					 }
					 
					 if( in_array($auditorium_del_val, $allowed_modules) ){
						 $_SESSION['is_auditorium_del'] = $Yes;
						 $_SESSION['is_auditorium_allowed'] = $Yes;
					 }
					/* === END   : Auditoriums  === */
					
					/* === START : Artists  === */
					 $artist_add_val    = $roleID.'_9_1'; 
					 $artist_edit_val   = $roleID.'_9_2';
					 $artist_del_val    = $roleID.'_9_3';
					 if( in_array($artist_add_val, $allowed_modules) ){
						 $_SESSION['is_artist_add'] = $Yes;
						 $_SESSION['is_artist_allowed'] = $Yes;
					 }
					 
					 if( in_array($artist_edit_val, $allowed_modules) ){
						 $_SESSION['is_artist_edit'] = $Yes;
						 $_SESSION['is_artist_allowed'] = $Yes;
					 }
					 
					 if( in_array($artist_del_val, $allowed_modules) ){
						 $_SESSION['is_artist_del'] = $Yes;
						 $_SESSION['is_artist_allowed'] = $Yes;
					 }
					/* === END   : Artists  === */
					
					/* === START : Productors  === */
					 $productor_add_val    = $roleID.'_10_1'; 
					 $productor_edit_val   = $roleID.'_10_2';
					 $productor_del_val    = $roleID.'_10_3';
					 if( in_array($productor_add_val, $allowed_modules) ){
						 $_SESSION['is_productor_add'] = $Yes;
						 $_SESSION['is_productor_allowed'] = $Yes;
					 }
					 
					 if( in_array($productor_edit_val, $allowed_modules) ){
						 $_SESSION['is_productor_edit'] = $Yes;
						 $_SESSION['is_productor_allowed'] = $Yes;
					 }
					 
					 if( in_array($productor_del_val, $allowed_modules) ){
						 $_SESSION['is_productor_del'] = $Yes;
						 $_SESSION['is_productor_allowed'] = $Yes;
					 }
					/* === END   : Productors  === */
					
					/* === START : Members  === */
					 $member_add_val    = $roleID.'_11_1'; 
					 $member_edit_val   = $roleID.'_11_2';
					 $productor_del_val    = $roleID.'_11_3';
					 if( in_array($member_add_val, $allowed_modules) ){
						 $_SESSION['is_member_add'] = $Yes;
						 $_SESSION['is_member_allowed'] = $Yes;
					 }
					 
					 if( in_array($member_edit_val, $allowed_modules) ){
						 $_SESSION['is_member_edit'] = $Yes;
						 $_SESSION['is_member_allowed'] = $Yes;
					 }
					 
					 if( in_array($member_del_val, $allowed_modules) ){
						 $_SESSION['is_member_del'] = $Yes;
						 $_SESSION['is_member_allowed'] = $Yes;
					 }
					/* === END   : Members  === */
					
					/* === START : Operators  === */
					 $operator_add_val    = $roleID.'_12_1'; 
					 $operator_edit_val   = $roleID.'_12_2';
					 $operator_del_val    = $roleID.'_12_3';
					 if( in_array($operator_add_val, $allowed_modules) ){
						 $_SESSION['is_operator_add'] = $Yes;
						 $_SESSION['is_operator_allowed'] = $Yes;
					 }
					 
					 if( in_array($operator_edit_val, $allowed_modules) ){
						 $_SESSION['is_operator_edit'] = $Yes;
						 $_SESSION['is_operator_allowed'] = $Yes;
					 }
					 
					 if( in_array($operator_del_val, $allowed_modules) ){
						 $_SESSION['is_operator_del'] = $Yes;
						 $_SESSION['is_operator_allowed'] = $Yes;
					 }
					/* === END   : Operators  === */
					
					/* === START : Sections  === */
					 $section_add_val    = $roleID.'_13_1'; 
					 $section_edit_val   = $roleID.'_13_2';
					 $section_del_val    = $roleID.'_13_3';
					 if( in_array($section_add_val, $allowed_modules) ){
						 $_SESSION['is_section_add'] = $Yes;
						 $_SESSION['is_section_allowed'] = $Yes;
					 }
					 
					 if( in_array($section_edit_val, $allowed_modules) ){
						 $_SESSION['is_section_edit'] = $Yes;
						 $_SESSION['is_section_allowed'] = $Yes;
					 }
					 
					 if( in_array($section_del_val, $allowed_modules) ){
						 $_SESSION['is_section_del'] = $Yes;
						 $_SESSION['is_section_allowed'] = $Yes;
					 }
					/* === END   : Sections  === */
					
					/* === START : Event Groups  === */
					 $event_group_add_val    = $roleID.'_14_1'; 
					 $event_group_edit_val   = $roleID.'_14_2';
					 $event_group_del_val    = $roleID.'_14_3';
					 if( in_array($event_group_add_val, $allowed_modules) ){
						 $_SESSION['is_event_group_add'] = $Yes;
						 $_SESSION['is_event_group_allowed'] = $Yes;
					 }
					 
					 if( in_array($event_group_edit_val, $allowed_modules) ){
						 $_SESSION['is_event_group_edit'] = $Yes;
						 $_SESSION['is_event_group_allowed'] = $Yes;
					 }
					 
					 if( in_array($event_group_del_val, $allowed_modules) ){
						 $_SESSION['is_event_group_del'] = $Yes;
						 $_SESSION['is_event_group_allowed'] = $Yes;
					 }
					/* === END   : Event Groups  === */
					
					
					/* === START : Events  === */
					 $event_add_val    = $roleID.'_15_1'; 
					 $event_edit_val   = $roleID.'_15_2';
					 $event_del_val    = $roleID.'_15_3';
					 if( in_array($event_add_val, $allowed_modules) ){
						 $_SESSION['is_event_add'] = $Yes;
						 $_SESSION['is_event_allowed'] = $Yes;
					 }
					 
					 if( in_array($event_edit_val, $allowed_modules) ){
						 $_SESSION['is_event_edit'] = $Yes;
						 $_SESSION['is_event_allowed'] = $Yes;
					 }
					 
					 if( in_array($event_del_val, $allowed_modules) ){
						 $_SESSION['is_event_del'] = $Yes;
						 $_SESSION['is_event_allowed'] = $Yes;
					 }
					/* === END   : Events  === */
					
					/* === START : Dont Miss Events  === */
					 $dont_miss_event_add_val    = $roleID.'_16_1'; 
					 $dont_miss_event_edit_val   = $roleID.'_16_2';
					 $dont_miss_event_del_val    = $roleID.'_16_3';
					 if( in_array($dont_miss_event_add_val, $allowed_modules) ){
						 $_SESSION['is_dont_miss_event_add'] = $Yes;
						 $_SESSION['is_dont_miss_event_allowed'] = $Yes;
					 }
					 
					 if( in_array($dont_miss_event_edit_val, $allowed_modules) ){
						 $_SESSION['is_dont_miss_event_edit'] = $Yes;
						 $_SESSION['is_dont_miss_event_allowed'] = $Yes;
					 }
					 
					 if( in_array($dont_miss_event_del_val, $allowed_modules) ){
						 $_SESSION['is_dont_miss_event_del'] = $Yes;
						 $_SESSION['is_dont_miss_event_allowed'] = $Yes;
					 }
					/* === END   : Dont Miss Events  === */
					
					/* === START : Events of Day  === */
					 $event_of_day_add_val    = $roleID.'_17_1'; 
					 $event_of_day_edit_val   = $roleID.'_17_2';
					 $event_of_day_del_val    = $roleID.'_17_3';
					 if( in_array($event_of_day_add_val, $allowed_modules) ){
						 $_SESSION['is_event_of_day_add'] = $Yes;
						 $_SESSION['is_event_of_day_allowed'] = $Yes;
					 }
					 
					 if( in_array($event_of_day_edit_val, $allowed_modules) ){
						 $_SESSION['is_event_of_day_edit'] = $Yes;
						 $_SESSION['is_event_of_day_allowed'] = $Yes;
					 }
					 
					 if( in_array($event_of_day_del_val, $allowed_modules) ){
						 $_SESSION['is_event_of_day_del'] = $Yes;
						 $_SESSION['is_event_of_day_allowed'] = $Yes;
					 }
					/* === END   : Events of Day  === */
					
					/* === START : Seats  === */
					 $seat_add_val    = $roleID.'_18_1'; 
					 $seat_edit_val   = $roleID.'_18_2';
					 $seat_del_val    = $roleID.'_18_3';
					 if( in_array($seat_add_val, $allowed_modules) ){
						 $_SESSION['is_seat_add'] = $Yes;
						 $_SESSION['is_seat_allowed'] = $Yes;
					 }
					 
					 if( in_array($seat_edit_val, $allowed_modules) ){
						 $_SESSION['is_seat_edit'] = $Yes;
						 $_SESSION['is_seat_allowed'] = $Yes;
					 }
					 
					 if( in_array($seat_del_val, $allowed_modules) ){
						 $_SESSION['is_seat_del'] = $Yes;
						 $_SESSION['is_seat_allowed'] = $Yes;
					 }
					/* === END   : Seats  === */
					
					/* === START : Tickets  === */
					 $ticket_add_val    = $roleID.'_19_1'; 
					 $ticket_edit_val   = $roleID.'_19_2';
					 $ticket_del_val    = $roleID.'_19_3';
					 $ticket_view_val    = $roleID.'_19_4';
					 if( in_array($ticket_add_val, $allowed_modules) ){
						 $_SESSION['is_ticket_add'] = $Yes;
						 $_SESSION['is_ticket_allowed'] = $Yes;
					 }
					 
					 if( in_array($ticket_edit_val, $allowed_modules) ){
						 $_SESSION['is_ticket_edit'] = $Yes;
						 $_SESSION['is_ticket_allowed'] = $Yes;
					 }
					 
					 if( in_array($ticket_del_val, $allowed_modules) ){
						 $_SESSION['is_ticket_del'] = $Yes;
						 $_SESSION['is_ticket_allowed'] = $Yes;
					 }
					 
					 if( in_array($ticket_view_val, $allowed_modules) ){
						 $_SESSION['is_ticket_view'] = $Yes;
						 $_SESSION['is_ticket_allowed'] = $Yes;
					 }
					/* === END   : Tickets  === */
					
					/* === START : Users  === */
					 $user_add_val    = $roleID.'_20_1'; 
					 $user_edit_val   = $roleID.'_20_2';
					 $user_del_val    = $roleID.'_20_3';
					 if( in_array($user_add_val, $allowed_modules) ){
						 $_SESSION['is_user_add'] = $Yes;
						 $_SESSION['is_user_allowed'] = $Yes;
					 }
					 
					 if( in_array($user_edit_val, $allowed_modules) ){
						 $_SESSION['is_user_edit'] = $Yes;
						 $_SESSION['is_user_allowed'] = $Yes;
					 }
					 
					 if( in_array($user_del_val, $allowed_modules) ){
						 $_SESSION['is_user_del'] = $Yes;
						 $_SESSION['is_user_allowed'] = $Yes;
					 }
					/* === END   : Users  === */
					
					/* === START : Partners  === */
					 $partner_add_val    = $roleID.'_21_1'; 
					 $partner_edit_val   = $roleID.'_21_2';
					 $partner_del_val    = $roleID.'_21_3';
					 if( in_array($partner_add_val, $allowed_modules) ){
						 $_SESSION['is_partner_add'] = $Yes;
						 $_SESSION['is_partner_allowed'] = $Yes;
					 }
					 
					 if( in_array($partner_edit_val, $allowed_modules) ){
						 $_SESSION['is_partner_edit'] = $Yes;
						 $_SESSION['is_partner_allowed'] = $Yes;
					 }
					 
					 if( in_array($partner_del_val, $allowed_modules) ){
						 $_SESSION['is_partner_del'] = $Yes;
						 $_SESSION['is_partner_allowed'] = $Yes;
					 }
					/* === END   : Partners  === */
					
					/* === START : CMS  === */
					 $cms_edit_val    = $roleID.'_22_2';
					 $cms_view_val    = $roleID.'_22_4';
					 if( in_array($cms_edit_val, $allowed_modules) ){
						 $_SESSION['is_cms_edit'] = $Yes;
						 $_SESSION['is_cms_allowed'] = $Yes;
					 }
					 
					 if( in_array($cms_view_val, $allowed_modules) ){
						 $_SESSION['is_cms_view'] = $Yes;
						 $_SESSION['is_cms_allowed'] = $Yes;
					 }
					 
					/* === END   : CMS  === */
					
					/* === START : Payment Types  === */
					 $payment_type_add_val    = $roleID.'_23_1'; 
					 $payment_type_edit_val   = $roleID.'_23_2';
					 $payment_type_del_val    = $roleID.'_23_3';
					 if( in_array($payment_type_add_val, $allowed_modules) ){
						 $_SESSION['is_payment_type_add'] = $Yes;
						 $_SESSION['is_payment_type_allowed'] = $Yes;
					 }
					 
					 if( in_array($payment_type_edit_val, $allowed_modules) ){
						 $_SESSION['is_payment_type_edit'] = $Yes;
						 $_SESSION['is_payment_type_allowed'] = $Yes;
					 }
					 
					 if( in_array($payment_type_del_val, $allowed_modules) ){
						 $_SESSION['is_payment_type_del'] = $Yes;
						 $_SESSION['is_payment_type_allowed'] = $Yes;
					 }
					/* === END   : Payment Types  === */
					
					/* === START : Community  === */
					 $community_add_val    = $roleID.'_24_1'; 
					 $community_edit_val   = $roleID.'_24_2';
					 $community_del_val    = $roleID.'_24_3';
					 if( in_array($community_add_val, $allowed_modules) ){
						 $_SESSION['is_community_add'] = $Yes;
						 $_SESSION['is_community_allowed'] = $Yes;
					 }
					 
					 if( in_array($community_edit_val, $allowed_modules) ){
						 $_SESSION['is_community_edit'] = $Yes;
						 $_SESSION['is_community_allowed'] = $Yes;
					 }
					 
					 if( in_array($community_del_val, $allowed_modules) ){
						 $_SESSION['is_community_del'] = $Yes;
						 $_SESSION['is_community_allowed'] = $Yes;
					 }
					/* === END   : Community  === */
					
					/* === START : Community Page  === */
					 $community_page_add_val    = $roleID.'_25_1'; 
					 $community_page_edit_val   = $roleID.'_25_2';
					 $community_page_del_val    = $roleID.'_25_3';
					 if( in_array($community_page_add_val, $allowed_modules) ){
						 $_SESSION['is_community_page_add'] = $Yes;
						 $_SESSION['is_community_page_allowed'] = $Yes;
					 }
					 
					 if( in_array($community_page_edit_val, $allowed_modules) ){
						 $_SESSION['is_community_page_edit'] = $Yes;
						 $_SESSION['is_community_page_allowed'] = $Yes;
					 }
					 
					 if( in_array($community_page_del_val, $allowed_modules) ){
						 $_SESSION['is_community_page_del'] = $Yes;
						 $_SESSION['is_community_page_allowed'] = $Yes;
					 }
					/* === END   : Community Page  === */
					
					/* === START : Orders  === */
					 $order_view_val    = $roleID.'_26_4'; 
					 if( in_array($order_view_val, $allowed_modules) ){
						 $_SESSION['is_order_view'] = $Yes;
						 $_SESSION['is_order_allowed'] = $Yes;
					 }
					 
					/* === END   : Orders  === */
					
					/* === START : Ticket Selling  === */
					 $ticket_selling_view_val    = $roleID.'_27_4'; 
					 if( in_array($ticket_selling_view_val, $allowed_modules) ){
						 $_SESSION['is_ticket_selling_view'] = $Yes;
						 $_SESSION['is_ticket_selling_allowed'] = $Yes;
					 }
					 
					/* === END   : Ticket Selling  === */
					
					/* === START : REPORTS  === */
					 $reports_view_val    = $roleID.'_29_4';
					 
					 if( in_array($reports_view_val, $allowed_modules) ){
						 $_SESSION['is_reports_view'] = $Yes;
						 $_SESSION['is_reports_allowed'] = $Yes;
					 }
					 
					/* === END   : REPORTS  === */
					
					/* === START : Coupons  === */
					 $coupon_add_val    = $roleID.'_30_1'; 
					 $coupon_edit_val   = $roleID.'_30_2';
					 $coupon_del_val    = $roleID.'_30_3';
					 $coupon_view_val    = $roleID.'_30_4';
					 if( in_array($coupon_add_val, $allowed_modules) ){
						 $_SESSION['is_coupon_add'] = $Yes;
						 $_SESSION['is_coupon_allowed'] = $Yes;
					 }
					 
					 if( in_array($coupon_edit_val, $allowed_modules) ){
						 $_SESSION['is_coupon_edit'] = $Yes;
						 $_SESSION['is_coupon_allowed'] = $Yes;
					 }
					 
					 if( in_array($coupon_del_val, $allowed_modules) ){
						 $_SESSION['is_coupon_del'] = $Yes;
						 $_SESSION['is_coupon_allowed'] = $Yes;
					 }
					 if( in_array($coupon_view_val, $allowed_modules) ){
						 $_SESSION['is_coupon_view'] = $Yes;
						 $_SESSION['is_coupon_allowed'] = $Yes;
					 }
					/* === END   : Coupons  === */
					
					/* === START : SUBSCRIBERS  === */
					 $subscribers_view_val    = $roleID.'_31_4';
					 
					 if( in_array($subscribers_view_val, $allowed_modules) ){
						 $_SESSION['is_subscribers_view'] = $Yes;
						 $_SESSION['is_subscribers_allowed'] = $Yes;
					 }
					 
					/* === END   : SUBSCRIBERS  === */
					
					/* === START : Settings  === */
					 $setting_edit_val    = $roleID.'_28_2'; 
					 $setting_view_val    = $roleID.'_28_4'; 
					 if( in_array($setting_edit_val, $allowed_modules) ){
						 $_SESSION['is_setting_edit'] = $Yes;
						 $_SESSION['is_setting_allowed'] = $Yes;
					 }
					 
					 if( in_array($setting_view_val, $allowed_modules) ){
						 $_SESSION['is_setting_view'] = $Yes;
						 $_SESSION['is_setting_allowed'] = $Yes;
					 }
					 
					/* === END   : Settings  === */
					
					
					
					
				}		
			}
		}
		
		$redirect_page = 'dashboard';
		return $response->withRedirect(base_url.'/admin/'.$redirect_page);
		
	}
	
	
	
	
	
}
