<?php

namespace App\Controllers;

use App\Models;

use Psr\Http\Message\RequestInterface;

use Psr\Http\Message\ResponseInterface;

use Respect\Validation\Validator as v;



/**

 Admin Coupon Controller

 

*/

class AdminCouponController extends Base 

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

	public function coupons($request, $response) {

		

        $params = array( 'title' => $this->lang['coupons_all_txt'],

		                 'current_url' => $request->getUri()->getPath());

        return $this->render($response, ADMIN_VIEW.'/Coupon/coupons.twig',$params);

    }

	

	// Ajax Coupons list

	public function ajaxCouponsList($request, $response){

		$isSearched = $drpSearch = false; // set to false if user did not search something

		$conditions = array();

		$whereData =  $customSearch = $prefix = '';

		if($request->getParam('query') != null){

			 $isSearched = true; // user has searched something

			 $fields = array('name', 'symbol', 'status');

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

		    $total   = Models\Coupon::whereRaw($whereData)->count(); // get count 

		}else{

			

			$total   = Models\Coupon::get()->count(); // get count 

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

		    $categories_list = Models\Coupon::whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();

		}else{

			$categories_list = Models\Coupon::skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();

		}

		

		$data = array();

		foreach($categories_list as $get){

			$expiration_date = $get->expiration_date;

			if($expiration_date == '0000-00-00'){

				$expiration_date = '';

			}else{

			   $expiration_date = hr_date($expiration_date);	

			}

		  	$array_data = array();

			$array_data['id']  = $get->id;

			$discount_type_db = $get->discount_type;

			if($this->lang['defaultLang'] == 'en_US'){

				$discount_type = $discount_type_db;

			}else{

			   	if($discount_type_db == 'Fixed'){

				   $discount_type = $this->lang['coupon_fixed_txt'];	

				}elseif($discount_type_db == 'PerTicket'){

					$discount_type = $this->lang['coupon_per_ticket_txt'];

				}elseif($discount_type_db == 'Double'){

					$discount_type = $this->lang['coupon_double_txt'];

				}else{

					$discount_type = $this->lang['coupon_percentage_txt'];	

				}

			}

            $array_data['coupon_name']  = '<a href="javascript:void()" onClick="view('.$get->id.')" title="View History">'.$get->coupon_name.'</a>';

            $array_data['coupon_code']  = $get->coupon_code;

			$array_data['discount_type']  = $discount_type;

			$array_data['discount_amount']  = $get->discount_amount;

			$array_data['coupon_used']  = $get->coupon_used;

			$array_data['expiration_date']  = $expiration_date;

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

	

	// Get Coupon by id

	public function getCouponById($request, $response, $args){

		$id = $args['id'];

        $validations = [

            v::intVal()->validate($id)

        ];



        if ($this->validate($validations) === false) {

            return $response->withStatus(400);

        }

		$coupon = Models\Coupon::find($id);

		if ($coupon) {

			if($coupon['expiration_date'] == '0000-00-00'){

			    $coupon['expiration_date'] = '';

			}else{

				$coupon['expiration_date'] = hr_date($coupon['expiration_date']);

			}

			return $response

            ->withHeader('Content-type','application/json')

            ->write(json_encode($coupon));

        }

		

	}

	

	// Save Coupon from admin

	public function saveCoupon($request, $response){

		//ddump($_REQUEST);

	   $isError = false;

	   $coupon_name = $request->getParam('coupon_name');

	   $coupon_code = $request->getParam('coupon_code');

	   $discount_type = $request->getParam('discount_type');

	   $discount_amount = $request->getParam('discount_amount');

	   $expiration_date = $request->getParam('expiration_date');

	   $status = $request->getParam('status');

	   $couponExist = Models\Coupon::where('coupon_code', '=', $coupon_code)->first();

	   if( $couponExist){

		  

		 echo json_encode(array("status" => 'duplicate', 

		                  'message' => 'Coupon (<strong>'.$coupon_code. '</strong>) '.$this->lang['common_already_exist_txt']));

		 exit();	   

	   }else  if( empty($coupon_name) ){

		  

		 echo json_encode(array("status" => 'error', 

		                  'message' => $this->lang['coupon_enter_coupon_txt']));

		 exit();	   

	   }else  if( empty($coupon_code) ){

		  

		 echo json_encode(array("status" => 'error', 

		                  'message' => $this->lang['coupon_enter_code_txt']));

		 exit();	   

	   } else  if( empty($discount_amount) ){

		  

		 echo json_encode(array("status" => 'error', 

		                  'message' => $this->lang['coupon_enter_amount_txt']));

		 exit();	   

	   } else{

		   // Save to coupons table

		   if( !isset($expiration_date) || empty($expiration_date) ){

		     $expiration_date  = '0000-00-00';

		   }else{

		     $expiration_date = mysql_date($expiration_date);

		   }

		   

		   $coupon = new Models\Coupon;

		   $coupon->coupon_name = $coupon_name;

		   $coupon->coupon_code = $coupon_code;

		   $coupon->discount_type = $discount_type;

		   $coupon->discount_amount = $discount_amount;

		   $coupon->expiration_date = $expiration_date;

           $coupon->status = $status;

		   $coupon->save();

		   

		   return $response

            ->withHeader('Content-type','application/json')

            ->write(json_encode(array('status' => TRUE)));  

	   }

	   

	   

	   

	   

	}

	

	// Update Coupon

	public function updateCoupon($request, $response){

		

	   $id   = $request->getParam('id');

	   $coupon_name = $request->getParam('coupon_name');

	   $coupon_code = $request->getParam('coupon_code');

	   $discount_type = $request->getParam('discount_type');

	   $discount_amount = $request->getParam('discount_amount');

	   $expiration_date = $request->getParam('expiration_date');

	   $status = $request->getParam('status');

	   $couponExist = Models\Coupon::where('coupon_code', '=', $coupon_code)->where('id', '<>', $id)->first();

	   if( $couponExist){

		  

		 echo json_encode(array("status" => 'duplicate', 

		                  'message' => 'Coupon (<strong>'.$coupon_code. '</strong>) '.$this->lang['common_already_exist_txt']));

		 exit();	   

	   }else  if( empty($coupon_name) ){

		  

		 echo json_encode(array("status" => 'error', 

		                  'message' => 'Please enter coupon name'));

		 exit();	   

	   }else  if( empty($coupon_code) ){

		  

		 echo json_encode(array("status" => 'error', 

		                  'message' => 'Please enter coupon code'));

		 exit();	   

	   } else  if( empty($discount_amount) ){

		  

		 echo json_encode(array("status" => 'error', 

		                  'message' => 'Please enter discount amount'));

		 exit();	   

	   } else{

		   // Save to coupons table

		   if( !isset($expiration_date) || empty($expiration_date) ){

		     $expiration_date  = '0000-00-00';

		   }else{

		     $expiration_date = mysql_date($expiration_date);

		   }

		   

		   // Save to currencies table

		   $data = array('coupon_name' => $coupon_name,

		                 'coupon_code' => $coupon_code,

						 'discount_type' => $discount_type,

						 'discount_amount' => $discount_amount,

						 'expiration_date' => $expiration_date,

						 'status' => $status);

		  $city = Models\Coupon::where('id', '=', $id)->update($data);					 

		  return $response

            ->withHeader('Content-type','application/json')

            ->write(json_encode(array('status' => TRUE)));

	   }

	}

	

	// Delete Coupon

	public function deleteCouponById($request, $response, $args){

		 $id = $args['id'];

        $validations = [

            v::intVal()->validate($id)

        ];



        if ($this->validate($validations) === false) {

            return $response->withStatus(400);

        }

		$delete = Models\Coupon::find($id)->delete();

		return $response->withJson(json_encode(array("status" => TRUE)));

		

	}

	

	

	// View Coupon by id

	public function viewCouponById($request, $response, $args){

		$id = $args['id'];

        $validations = [

            v::intVal()->validate($id)

        ];



        if ($this->validate($validations) === false) {

            return $response->withStatus(400);

        }

		$coupon = Models\Coupon::find($id);

		if ($coupon) {

			if($coupon['expiration_date'] == '0000-00-00'){

			    $coupon['expiration_date'] = 'No';

			}else{

				$coupon['expiration_date'] = hr_date($coupon['expiration_date']);

			}

        }

		

		$params = array( 'title' => 'View Coupon History',

		                'coupon' => $coupon,

						'coupon_id' => $id);

						

        return $this->render($response, ADMIN_VIEW.'/Coupon/view.twig',$params);

		

	}

	

	

	// Ajax Coupon History list

	public function getAjaxCouponHistoryList($request, $response){

		$coupon_id = $request->getParam('post_data')['coupon_id'];

		$isSearched = $drpSearch = false; // set to false if user did not search something

		$conditions = array();

		$whereData =  $customSearch = $prefix = '';

		if($request->getParam('query') != null){

			 $isSearched = true; // user has searched something

			 $fields = array('name', 'symbol', 'status');

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

		

		$sqlCount = "SELECT COUNT(CH.id) AS cnt FROM `coupon_history` AS CH 

					INNER JOIN `orders` AS O  ON CH.`order_id`=O.`id` INNER JOIN `users` AS U 

					ON CH.`customer_id`=U.`id` INNER JOIN `events` AS E  ON CH.`event_id`=E.`id`

					WHERE CH.`coupon_id`=".$coupon_id." ";

		$resultQuery = mysqli_query($this->conn, $sqlCount);

		$row = mysqli_fetch_assoc($resultQuery);	

	    $total   = $row['cnt']; // get count 

		

		if($page == 1){

		   $offset = 0;	

		   $perpage = 0;

		}else{

		  $offset = ($page-1);	

		  $perpage = $per_page;	

		}

		if($per_page <= 1){

		  $pages = intval($total/5);

	    }else{

	      $pages = intval($total/$per_page);

		}

		if($per_page <= 1){

		   $perPageLimit = 5;	

		}else{

		   $perPageLimit = $per_page;	

		}

		

			$categories_list = Models\Coupon::skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();

		 $sqlQuery = "SELECT 

					  E.title AS event_title,

					  U.name AS customer_name,

					  O.invoice_number,

					  CH.* 

					FROM

					  `coupon_history` AS CH 

					  INNER JOIN `orders` AS O 

						ON CH.`order_id` = O.`id` 

					  INNER JOIN `users` AS U 

						ON CH.`customer_id` = U.`id` 

					  INNER JOIN `events` AS E 

						ON CH.`event_id` = E.`id` 

					WHERE CH.`coupon_id` = ".$coupon_id." LIMIT ".($offset*$perPageLimit)." ,  ".$perPageLimit." ";

		$resultQuery = mysqli_query($this->conn, $sqlQuery);			

		$data = array();

		while($rowResult  = mysqli_fetch_object($resultQuery)){

			$array_data = array();

			$date_used = $rowResult->date_used;

			if($date_used == '0000-00-00'){

				$date_used = '';

			}else{

			   $date_used = hr_date($date_used);	

			}

			//$event_title = trim(strip_tags(trim(htmlspecialchars_decode($get->event_title))));

            $array_data['order_id']  = $rowResult->order_id;

			$array_data['invoice_number']  = $rowResult->invoice_number;

			$array_data['customer_name']  = $rowResult->customer_name;

			$array_data['date_used']  = $date_used;

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

	

	

	

	

	

	

	

	

	

}

