<?php
namespace App\Controllers;

use App\Tools\Auth;
use App\Models;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;
/**
  Admin Operator Controller
  CRUDs for Operator controller
  Available Functions
  
*/

class AdminOperatorController extends Base 
{
	protected $container;
	protected $lang;
	public function __construct($container)
	{
		$this->container = $container;
		$this->lang =  $this->container->view['adminLang'];
	}
	
	 // Main function to display event group list
	 public function operators($request, $response)
	{
		$data= array(
		     'title' => $this->lang['operators_txt'],
			'current_url' => $request->getUri()->getPath(),
			);
		return $this->render($response, ADMIN_VIEW.'/Operator/operators.twig',$data);
	}
	
	public function getAjaxOperatorList($request, $response){
		
		$operator_list = Models\Operators::where('op_id', '!=', NULL)->get();
		
		$data = array();
		foreach($operator_list as $get){
		  	$array_data = array();
			$array_data['op_id']  = $get->op_id;
			$array_data['op_fullname'] = $get->op_fullname;
            $array_data['op_fname']  = $get->op_fname;
			$array_data['op_lanme']  = $get->op_lanme;
			$array_data['op_email']  = $get->op_email;
			$array_data['op_phone']  = $get->op_phone;
			$data[] = $array_data;
		}
		
		
		$output = array(
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
		
        
	}
	
	
	
	public function saveOperator($request, $response, $args)
	{
		
	  
	   $id   = $request->getParam('id');
	   $op_fullname = $request->getParam('op_fullname');
	   $first_name = $request->getParam('op_fname');
	   $last_name = $request->getParam('op_lname');
	   $email = $request->getParam('op_email');
	   $phone = $request->getParam('op_phone');
	   $password = $request->getParam('password');
	   $password_confirm = $request->getParam('password_confirm');
	    $operatorExists = Models\Operators::where('op_email', '=', $email)->first();
	   if( $operatorExists){
		  
		 echo json_encode(array("status" => 'duplicate', 
		                  'message' => $this->lang['operator_txt'].' (<strong>'.$email. '</strong>) '.$this->lang['common_already_exist_txt'].'.'));
		 exit();	   
	   }else  if( empty($op_fullname) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_full_name_msg_txt']));
		 exit();	   
	  
	   }else if( empty($first_name) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_fname_msg_txt']));
		 exit();	   
	  
	   }else  if( empty($last_name) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_lname_msg_txt']));
		 exit();	   
	  
	   }else  if( empty($email) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_email_msg_txt']));
		 exit();	   
	  
	   }else  if(!isValidEmail($email)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                    'message' => $this->lang['op_valid_email_msg_txt']));
		   exit();	   
	   }else  if( empty($phone) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_mobile_msg_txt']));
		 exit();	   
	  
	   }else  if( empty($password) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => 'Please enter password'));
		 exit();	   
	  
	   }else  if( empty($password_confirm)  ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => 'Please confirm your password'));
		 exit();	   
	  
	   } else{
		   
		  if( $password <> $password_confirm){
			  echo json_encode(array("status" => 'error', 
		                  'message' => 'Both passwords must be same'));
		      exit();
		  }else{
			  
			 $hashed_password = password_hash($password, PASSWORD_BCRYPT);
			 $data = array('op_fullname' => $op_fullname,
							 'op_fname' => $first_name,
							 'op_lname' =>$last_name,
							 'op_email' => $email,
							 'op_phone' => $phone,
							 'password' => $hashed_password);
			//print_r($data);
			if($operator = Models\Operators::insert($data))
			{
				// Send email to the operator
				$msgArr = array('last_name' => $last_name,
				                'email' => $email,
								'login_password' => $password,
								'site_url' => WEB_PATH);
				$to = $email;
				$subject = 'Your Operator Account created successfully';				
				sendEmail($from='',$to,$subject,$msgArr, 'register_operator.html');
				
				return $response
				->withHeader('Content-type','application/json')
				 ->write(json_encode(array('status' => TRUE)));
			
			}
		 }	
	 }
	}

	
	public function updateOperator($request, $response, $args)
	{
		
	  
	   $id   = $request->getParam('id');
	   $op_fullname = $request->getParam('op_fullname');
	   $first_name = $request->getParam('op_fname');
	   $last_name = $request->getParam('op_lname');
	   $email = $request->getParam('op_email');
	   $phone = $request->getParam('op_phone');
	    $operatorExists = Models\Operators::where('op_email', '=', $email)->where('op_id', '!=', $id)->first();
	   if( $operatorExists){
		  
		 echo json_encode(array("status" => 'duplicate', 
		                  'message' => $this->lang['operator_txt'].' (<strong>'.$email. '</strong>) '.$this->lang['common_already_exist_txt'].'.'));
		 exit();	   
	   }else  if( empty($op_fullname) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_full_name_msg_txt']));
		 exit();	   
	  
	   }else  if( empty($first_name) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_fname_msg_txt']));
		 exit();	   
	  
	   }else  if( empty($last_name) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_lname_msg_txt']));
		 exit();	   
	  
	   }else  if( empty($email) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_email_msg_txt']));
		 exit();	   
	  
	   }else  if(!isValidEmail($email)){
		   $isError = true;
		 echo json_encode(array("status" => 'error', 
		                    'message' => $this->lang['op_valid_email_msg_txt']));
		   exit();	   
	   }else  if( empty($phone) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => $this->lang['op_mobile_msg_txt']));
		 exit();	   
	  
	   } else{
	   
	   
	   
	    $data = array('op_fullname' => $op_fullname,
		                 'op_fname' => $first_name,
			             'op_lname' =>$last_name,
		                 'op_email' => $email,
						 'op_phone' => $phone);
		//print_r($data);
		$operator = Models\Operators::where('op_id', '=', $id)->update($data);	
		if($operator)
		{
		
			return $response
            ->withHeader('Content-type','application/json')
             ->write(json_encode(array('status' => TRUE)));
        
		}
		
		
		
	}
	}
	
	public function getOperatorById($request, $response, $args){
		$id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		$operator = Models\Operators::find($id);
		echo json_encode($operator);
		
	}
	
	public function deleteOperator($request, $response, $args)
	{
		
		 $id = $args['id'];
		 
		$delete = Models\Operators::find($id)->delete();
		
		if ($delete) {
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
			<select name="operatorsSelection<?= $_POST['data'] ?>" id="operatorsSelection<?=  $_POST['data']  ?>" class="form-control">
		
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
	
	
	// Reset operator password
	public function resetOperatorPass($request, $response, $args)
	{
	   $id   = $request->getParam('res_id');
	   $password = $request->getParam('password');
	   $password_confirm = $request->getParam('password_confirm');
	   if( empty($password) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => 'Please enter password'));
		 exit();	   
	  
	   }else  if( empty($password_confirm) ){
		  
		 echo json_encode(array("status" => 'error', 
		                  'message' => 'Please confirm your password'));
		 exit();	   
	  
	   } else{
	      if( $password <> $password_confirm ){
			   echo json_encode(array("status" => 'error', 
		                  'message' => 'Both passwords must be same'));
		 exit();
		  }else{
			  
			$hashed_password = password_hash($password, PASSWORD_BCRYPT);
			$data = array('password' => $hashed_password); // the hashed password
			
			
			$operator = Models\Operators::where('op_id', '=', $id)->update($data);	
			if($operator)
			{
				$operator = Models\Operators::where('op_id', '=', $id)->get();
				foreach($operator as $row){
				  $last_name = $row->op_lname;
				  $email = $row->op_email;	
				}
			    // Send Password Change Email here
				$msgArr = array('last_name' => $last_name,
				                'email' => $email,
								'login_password' => $password,
								'site_url' => WEB_PATH);
				$to = $email;
				$subject = 'Your password reset successfully';				
				sendEmail($from='',$to,$subject,$msgArr, 'reset-pass.html');
				return $response
				->withHeader('Content-type','application/json')
				 ->write(json_encode(array('status' => TRUE)));
			
			}
		
		}
		
	}
	}
	
	
	
}
