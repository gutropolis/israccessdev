<?php
namespace App\Controllers;

use App\Models;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;


class AdminSubscriberController extends Base 
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
		set_time_limit(0);
		$this->container = $container;
		$this->servername = $this->container['settings']['database']['host'];
		$this->username = $this->container['settings']['database']['username'];
		$this->password = $this->container['settings']['database']['password'];
		$this->dbname = $this->container['settings']['database']['database'];
		$this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
		$this->lang =  $this->container->view['adminLang'];
	}
	
	
	
	// Main function to display list of subscribers
	public function subscribers($request, $response) {
		
        $params = array( 'title' => 'Subscribers',
		                 'current_url' => $request->getUri()->getPath());
        return $this->render($response, ADMIN_VIEW.'/Subscribers/subscribers.twig',$params);
    }
	
	// Ajax Subscribers list
	public function getAjaxSubscribersList($request, $response){
		$isSearched = $drpSearch = false; // set to false if user did not search something
		$conditions = array();
		$whereData =  $customSearch = $prefix = '';
		$whereData =  $customSearch = $prefix = '';
		if($request->getParam('query') != null){
			 $isSearched = true; // user has searched something
			 $fields = array('name');
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
		    $total   = Models\Subscriber::whereRaw($whereData)->count(); // get count 
		}else{
			$total   = Models\Subscriber::get()->count(); // get count 
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
		    $subscribers_list = Models\Subscriber::whereRaw($whereData)->skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}else{
			$subscribers_list = Models\Subscriber::skip($offset*$perPageLimit)->take($perPageLimit)->orderBy($field, $sort)->get();
		}
		
		$data = array();
		foreach($subscribers_list as $get){
		  	$array_data = array();
			$array_data['id']  = $get->id;
            $array_data['subscriber_email']  = $get->subscriber_email;
			$array_data['subscribed_date']  = hr_date($get->subscribed_date);
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
	
	
	/* 
	* Delete Subscriber
	*/
	public function deleteSubscriberById($request, $response, $args){
		 $id = $args['id'];
        $validations = [
            v::intVal()->validate($id)
        ];

        if ($this->validate($validations) === false) {
            return $response->withStatus(400);
        }
		
		$delete = Models\Subscriber::find($id)->delete();
		return $response->withJson(json_encode(array("status" => TRUE)));
		
	}
	// Download Subscribers list as CSV
	public function downloadSubscribersCSV($request, $response, $args){
		    $NthStyle = 'background-color: #f2f2f2;padding: 8px;';	
			$EthStyle = 'background-color: #fff;padding: 8px;';		
			$thStyle = 'background-color: #4CAF50; color:#fff; padding: 8px;';	
			$NthStyle = $EthStyle = $thStyle1 = '';	
			$reportTableTitle = 	'<h1><center> List of Subscribers '.date('d/m/Y').'</center></h1>';
			$reportTable = '';
			$reportTable .= '<table border="1" >
						  <thead>
						  <tr>
						  <th colspan="4" style="background-color:#fff2cb; color:#000">'.$reportTableTitle.'</th>
						  </tr>
							<tr>
							  <th style="'.$thStyle.'"><h3>#</h3></th>
							  <th style="'.$thStyle.'"><h3>Emaild Address</h3></th>
							  <th style="'.$thStyle.'"><h3>Subscribed Date</h3></th>
							  <th style="'.$thStyle.'"><h3>Status</h3></th>
							</tr>
						  </thead>
						  <tbody>';	
				$sqlReport = "SELECT 
							  U.id,
							  U.subscriber_email,
							  DATE_FORMAT(U.subscribed_date, '%d/%M/%Y') AS subscribed_date,
							  U.status
							FROM
							  `subscribers` AS U 
							ORDER BY U.`subscribed_date` DESC   ";	
			$resultQuery = mysqli_query($this->conn, $sqlReport);
			$i=0;
			$counter = 1;
			while($row = mysqli_fetch_assoc($resultQuery)){
				  if($i % 2 == 0){
					  $style = $NthStyle;	
					}else{
					  $style = $EthStyle;	
					}
					if($row['status'] == 1){
						$status = 'Active';
					}else{
					   $status = 'Inactive';	
					}
				$reportTable .= '<tr>
									  <td style="'.$style.'">'.$counter.'</td>
									  <td style="'.$style.'">'.$row['subscriber_email'].'</td>
									  <td style="'.$style.'">'.$row['subscribed_date'].'</td>
									  <td style="'.$style.'">'.$status.'</td>
								</tr>'; 	 	
			$counter++;
			}
			$htmlcontent = $reportTable;	   
			
			$htmlcontent .= '</table>';
			$html = utf8_decode($htmlcontent);
			header("Content-Type: application/xls"); 
			$file_name = 'SubscribersList_'.time();   
			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			header("Content-Type: application/vnd.ms-excel");
			 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			

			echo $html;
			exit; // This is very important for downloading the XLS 
											  
    
	}
    

	
	
	
	
}
