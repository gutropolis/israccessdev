<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Usermeta;
use App\Models\Order;
use App\Models\EventTime;
use App\Models\Event;
use App\Models\Eventgroup;
use App\Models\EventSeatCategories;
use App\Models\EventCategoryRowSeat;
use App\Models\RowSeats;
use App\Models\Cheque_Detail;
use App\Models;
use App\Models\City;
use App\Models\OrderItems;
use App\Models\Coupon;
use App\Models\CouponHistory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;
use Endroid\QrCode\QrCode;
/*
* Admin City Controller
* CRUD for City
  Available Functions
  1. cities
  2. getStates
  3. ajaxCityList
  4. getCityById
  5. saveCity
  6. updateCity
  7. deleteCityById
  
  
 
*/
  $GLOBALS['bookingfee'] =0; 
class AdminSellingController extends Base 
{
	
	protected $container;
	protected $lang;
	
		public $userid=0;
		public $payment_type='cash';
		public $event_id=0;
		public $booking_fee=0;
		public $booking_items=0;
		public $total_reserved_fees=0;
		
		public $errorPayment=array();
		public $responsePayment=array();  
		//public $supplier = 'Ttxcultur'; // enter your supplier name
		//public $tranzilapw = 'CultR191131A'; //enter your tranzila pw 
		public $supplier = 'ttxcultur'; // enter your supplier name
		public $tranzilapw = 'CultR191131A'; //enter your tranzila pw 
		public $tranzila_api_host = 'https://secure5.tranzila.com';
		public $tranzila_api_path = '/cgi-bin/tranzila71pme.cgi';
		public $invoicecode   =array();
	
	    public $event_name_tranzilla = '';
		public $cat_tranzilla = '';
		public $place_tranzilla = '';
		
		 
	
	// Class Constructor
	public function __construct($container)
	{
		$this->container = $container;
		$this->lang =  $this->container->view['adminLang'];
	}

	
	public function getEventbyGroup($request, $response) {
		$dataEventList=array();
		$bodytxt='';
		$eventgroup_id=$request->getParam('event_group_id');
		
		$events=Event::where('eventgroup_id',$eventgroup_id)->where('status','1')->get();
           if(count($events) > 0){
           	$bodytxt.=' <option value="">'.$this->lang['selling_select_txt'].'</option>';
        	foreach ($events as $evgArr) {
					$a= array();
					//code change for add city with event name on 6 oct 2018
					$cityid=$evgArr['city_id'];
					$cityname=City::select('name')->where('id',$cityid)->first();
						$bodytxt.=' <option value="'.$evgArr['id'].'">('.$evgArr['id'].') - '.$evgArr['title'].' : '.date('d-m-Y', strtotime($evgArr['date'])).' - '.$cityname['name'].'</option></td>';
					
					//$dataEventList[]=$a;
        	}
        		 
        }  
		//print_r($dataEventList);
		//exit;

		$jsonData =$bodytxt;
					return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));
					exit();	
       // return $this->render($response, ADMIN_VIEW.'/SellingTicket/ticketselling.twig');
    }

	public function selling($request, $response) {
		
		
		$data=array();
    	$dataEvgroup=Eventgroup::select('title','id')->where('status','1')->where('date_end','>=',date('Y-m-d'))->orderby('id','asc')->get();

    	$dataEventGroupList=array();
          
        if(count($dataEvgroup) > 0){
        	foreach ($dataEvgroup as $evgArr) {
					$a= array();

					$a['title'] = strip_tags(htmlspecialchars_decode($evgArr['title']));
					$a['id'] = $evgArr['id'];
					$dataEventGroupList[]=$a;
        	} 
        }  
    	sort($dataEventGroupList);
    	//print_r($dataEventGroupList);


    	//exit;


		 $data['Event_group_name'] = $dataEventGroupList; 
		 $data['title'] = 'Ticket Selling';
		
        return $this->render($response, ADMIN_VIEW.'/SellingTicket/ticketselling.twig',$data);
    }
     


 
 
      public function getSellingdata($request, $response) {
		   
		  error_reporting(0);
      	    $isnewcustomer  = $request->getParam('isnewcustomer');
			 $full_total  = $request->getParam('full_total');
			 $password = $this->getToken('6');
			 $new_password = password_hash($password, PASSWORD_BCRYPT);
			 if($request->getParam('event_id')=='')
			 {
					 $jsonData = array(
						'status' => '0',
						'msg' => 'Please select any one event from the table '
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
			 }   
			if($isnewcustomer == '1'){
				
					$name=$request->getParam('firstname');
					$firstname=$request->getParam('lastname');
					//code add for using dummy mail on 6 oct
					$email='';
										
					if(empty($request->getParam('email')) && $request->getParam('noemail')!='')
					{
						$email=$this->getEmail('6');
					}
					else
					{
						$email=$request->getParam('email');

					}
					
					//code end here
					
					$phone=$request->getParam('phone');
					 
                    //Generate the User Registeration here //
					$UsermailExist = User::select('id')->where('email',$email)->first();
	
						if($UsermailExist)
						{
							 $jsonData = array('status' => '0','msg' => "Email existe déjà !!");
							 
								return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
								exit;
						}
						else
						{
							
							$create = User::create([

								 'name' => $name, 
								 'email' => $email, 
								 'password' => $new_password,
								 'status' => '1', 
								 'type'=>'Member',
								 'created_on' => date('Y-m-d h:i:s')  

							 ]);
							 if(intval($create->id) > 0 ) {
								  
														$usermeta = new Usermeta();
														$usermeta->user_id = $create->id; 
														$usermeta->first_name = $firstname; 
														$usermeta->ville = $request->getParam('city');  //add city in form on 6 oct 
														$usermeta->phone_no = $phone; 
														$usermeta->save();

														//Email send for password on 6 oct
														if(empty($request->getParam('noemail')))
														{
														$msgArr=array();
														$msgArr['name']=$name;
														$msgArr['email']=$email;
														$subject='Votre mot de passe CulturAccess';
														$msgArr['password']=$password;
														$to=$email;
														//sendEmail($email,$msgArr);
														sendEmail('',$to,$subject,$msgArr, 'password_notification.html');
														}														
							 } 
							$this->userid=$create->id;
						}
					 
				
			}else{
				
				$this->userid=$request->getParam('memberid');
				if(intval($this->userid) == 0){
								$jsonData = array('status' => '0','msg' => "Please select a user"); 
								return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
								exit;
				} 
				
			}
			
			 
			
			/*Add booking */
		  //print_r($_REQUEST);exit;
			//unset($_SESSION["isMember"]);
			//exit;
			if(isset($_SESSION["cart_products"])){
						 unset($_SESSION["cart_products"]);
			}			
			$extraData=array();
			$bookingfees='1';
			$giftcard='1';
			
			//$extraData['bookingfees'] = $bookingfees;
			$extraData['giftcard'] = $giftcard;
			
			//  Set Booking Fees and Other Booking Items here
				$this->booking_fee=$request->getParam('booking_fees');
				$this->booking_items=$request->getParam('total_row_qtx');
				$this->total_reserved_fees=$request->getParam('total_reserved_fees');
			
		    
			$ticket_type		= $request->getParam('ticket_type'); 
			$eventIds = $request->getParam('event');                 //Event Id
			
			$eventPriceArr = $request->getParam('ticket_price');     //Ticket Price
			$eventPriceSeqArr = $request->getParam('ticket_price_sequence');     //Ticket Price
			$eventTicketType = $request->getParam('ticket_type');    //Ticket Category like left,right
			$ticket_type_ids = $request->getParam('ticket_type_id');    //Ticket Category like left,right
			 
			 $seat_qtyt=$request->getParam('seat_qty');//for number of seats
			// $multiselect_row_number=$request->getParam('total_row_qtx'); //for multiselect box row number
			
           /*

			 print_r( $seat_qtyt);exit;
            list($seat_quentity,$ticket_price) = explode(',', $seat_qtyt);
					
			$eventQtyArr =$seat_quentity;

			print_r(explode(',',$seat_qtyt,0));

			print_r($seat_qtyt) ;exit;    //Quantity of seat have booked
			*/
			//$eventSeatQt = $request->getParam('seat_qty');          //Seat Quantity
			$eventTicketArr = $request->getParam('ticket_rows');//print_r($eventTicketArr);
			$evtime = $request->getParam('evtime');                 //Event Time
			$totalAvailableTicket  = $request->getParam('totalavailabletkthdn');       //Total Available Seats
			$seat_number  = $request->getParam('seat_number'); 
			$free_placement_ids  = $request->getParam('free_placement_id'); 
			
			
			
			$seat_from_Arr  = $request->getParam('seat_from'); 
			$seat_to_Arr  = $request->getParam('seat_to'); 
			$seat_sequence_Arr  = $request->getParam('seat_sequence'); 
			
//print_r(count($seat_sequence));exit;
			
            /*
			[event]
			[evtime]
			[ticket_price]
			[ticket_type]
			[ticket_rows]
			[totalavailabletkthdn]
			[seat_qty] */

			$countT= count($ticket_type);
			$ab='';
			if($countT >0){
				$j=0;
				for($i=0;$i<$countT; $i++){
					
					

					$qtxArr = $seat_qtyt[$i];  //for number of quantity of seats
					//$qtxMultiselectArr = $multiselect_row_number[$i];
					$qtx =0;
					if($qtxArr!=''){
						$a=explode(",",$qtxArr); 
						$qtx = $a[0]; 
					}else{
						  $seat_sequence = $seat_sequence_Arr[$i]; 
						  if($seat_sequence!=''){
							  
								  if (strpos($seat_sequence, ',') !== false) {
										$seat_sequence_arr =  explode(',' , $seat_sequence); 
										$qtx = count($seat_sequence_arr);  
								  }else{
									  $qtx =1;
									  
								  }
							  
						  }else{
							  $qtx =0;
						  }
						 
					     
						   
					}
					//print('hello this is qty');
					//print_r($qtx);exit;
					//$qtxArr= $eventQtyArr[$i]; 
					//$a=explode("-",$qtxArr); 
				    // $qtx = $a[0]; 
				    //$ab .=$qtx.'<br />';
					//print $qtx; 
					if(intval($qtx) > 0 ){  
					$j=$j+1;
								$dataShop = array();
								$event_id= $eventIds[$i]; 
								$this->event_id = $event_id; //Get Event ID
								$eventArr = Event::where('id',$event_id)->get();
								$event_list = getEventList($eventArr); 
								 
								foreach($event_list  as $ev){
									$this->event_name_tranzilla = $ev['title'];
									
									 
									$dataShop['event_name'] = $ev['title'];
									$dataShop['event_ticket_type'] = $ev['event_ticket_type'];
									//Add booking fees here //
									$dataShop['booking_fee'] = $ev['booking_fee'];
									$extraData['bookingfees'] =  $ev['booking_fee'];
									$bookingfees = $ev['booking_fee'];;
									
									$dataShop['eventgroup_id'] = $ev['eventgroup_id'];
									
									
									$dataShop['artist_name'] = $ev['artist_name'];
									$dataShop['artist_name'] = $ev['artist_name'];
									$dataShop['event_auditorium'] = $ev['event_auditorium'];
									$dataShop['event_auditorium_address'] = $ev['event_auditorium_address'];
									
									$this->place_tranzilla = $ev['event_auditorium_address'];
									
									$dataShop['event_city'] = $ev['event_city'];  	
									$dataShop['event_group_picture'] = EVENTGROUP_WEB_PATH.'/'.$ev['event_group_picture'];  
									$dataShop['event_date'] =  $ev['date_d'].'/'.$ev['date_m'].'/'.$ev['date_Y'];  
									
								}			
								$dataShop['event_id'] = $event_id;  
								$rowseat=$eventTicketArr[$i]; 
								    
									$a=explode("-",$rowseat); 
									$ticket_row = $a[1];
									$ticket_row_id = $a[0];
									$bookingSeatQtx=$qtx;  
									$booking_time = $evtime[$i];
									$total_available_seat = $totalAvailableTicket[$i];
											 
			                        $ticket_type_id = $ticket_type_ids[$i];
									$price= $eventPriceArr[$i];
									$price_sequence  = $eventPriceSeqArr[$i]; 
									$ticket_category= $eventTicketType[$i]; 
									
									$free_placement_id= $free_placement_ids[$i]; 
									
									
									$this->cat_tranzilla = $ticket_category;
									
									
 
									//Add New Field 
									$seat_number_display= $seat_number[$i]; 
									
									$seat_from = $seat_from_Arr[$i]; 
									$seat_to   = $seat_to_Arr[$i]; 
									$seat_sequence = $seat_sequence_Arr[$i]; 
									
								 
 

									$dataShop['qtx'] = $qtx;
									$dataShop['event_id'] = $event_id;
									$dataShop['price'] = $price;
									$dataShop['price_sequence'] = $price_sequence; 	

									$priceSeArr = $this->getTotalPriceSequence($price_sequence);  
							        $dataShop['total_amount'] =  $priceSeArr['totalAmt'] ;    // Total Amount
									$dataShop['seat_description'] =  $priceSeArr['seathtml'];     // Total Amount
									
									$totalAmt =  $priceSeArr['totalAmt'] ;  
									
									$dataShop['ticket_type'] = $ev['event_ticket_type'];;  //Type of ticket ->eticket, free placement 
						            $dataShop['ticket_category'] = $ticket_category; 		//eg cate1, cat2	
									//$dataShop['ticket_seat'] = $seat_number;    //
									
									$dataShop['seat_qty'] = $bookingSeatQtx; 		//Total Seat Quantity Booked 
									$uniqueNo=$event_id.$ticket_type_id."-".$ticket_category; 
									$uniqueNo = str_replace(' ', '_', $uniqueNo);
									$dataShop['ticketuid'] = $uniqueNo; 	
									

										 


									$dataShop['ticket_type_id'] =$ticket_type_id;
									 
							        $dataShop['total_amount'] = $totalAmt;     // Total Amount
									$dataShop['booking_time'] = $booking_time;   //Booking Time
									$dataShop['total_available_seat'] = $total_available_seat;

									$dataShop['seat_number'] = $seat_number_display;  //Display Seat Number
									$dataShop['ticket_row'] = $ticket_row;  //Ticket Row ID
									$dataShop['ticket_row_id'] = $ticket_row_id;  //Ticket Row ID
									
									$dataShop['seat_from'] = $seat_from;  //Ticket Row ID
									$dataShop['seat_to'] = $seat_to;  //Ticket Row ID
									$dataShop['seat_sequence'] = $seat_sequence;  //Ticket Row ID
									$dataShop['free_placement'] =$free_placement_id;
									
									 
								//print_r($dataShop);exit;
					 
								if(isset($_SESSION["cart_products"])){  
								   
									//if session var already exist
									if(isset($_SESSION["cart_products"][$dataShop['ticketuid'] ]  )) //check item exist in products array
									{
										unset($_SESSION["cart_products"][$dataShop['ticketuid']]); //unset old array item
									}          
								}
								//print_r($dataShop);exit;
								$_SESSION["cart_products"][$dataShop['ticketuid']] = $dataShop;   
									
					}
					 
				}
				$extraData['booking_fee_item'] =  $j;
				 
			}
			
 			  //echo $ab;exit;
		 
			/*End booking */
			
			 
            //print_r($_SESSION);exit;
			 //print_r($qtx);exit;
			$select_selling_type = $request->getParam('selling_type');
			
			$selling_type = $request->getParam('payment');
			
         
			if (  ($select_selling_type == 1)  || ($selling_type=='ccard' && $select_selling_type == 0)){
				
				
					$data = array();
					$cartvalue = $request->getParam('crdcard');
					$cartExpM = $request->getParam('cmonth');
					$cartExpY = $request->getParam('cyear');
					$cartExpY = substr($cartExpY, 2, 4);
					$cvv = $request->getParam('cvv');

					/* Start Payment Module Here  	*/
					
					
					$user = User::where('id', $this->userid)->first();
					$usermetainfo = Usermeta::where('user_id', $this->userid)->first();
			
					$data['nom'] = $user->name;
					$data['prenom'] = $usermetainfo->first_name;
					$data['email'] =  $user->email;
					$data['phone'] = $usermetainfo->phone_no;
					$data['ville'] = $usermetainfo->ville;
					$data['remarques'] = 'Event book by creditcard'; 			
				
					$data['event_name'] = $this->event_name_tranzilla; 
					$data['cat'] = $this->cat_tranzilla; 
					$data['place'] = $this->place_tranzilla; 

					/*End Payment Module Here */
					
					
					
					
					
					// $cette		= $request->getParam('cette');

					 
					$data['expiration_date'] = $cartExpM . '/' . $cartExpY;
					$data['total_amount'] = $this->total_reserved_fees;
					$data['currency'] = '1';
					$data['credit_card_number'] = $cartvalue;
					$data['expiration_Year'] = $cartExpY;
					$data['mycvv'] = $cvv;
					$data['id_number'] = '01';
					$ip_Addresss = $this->get_client_ip();
					$data['ipaddress'] = $ip_Addresss;
					$data['orderid'] = '2323232';
					$data['event_name_tranzilla'] = $this->event_name_tranzilla;
					
					
					
					
					$paymentID='0';

								// print_r($data);
								$token = $this->donfo_create_token($data ); 
								 
								if ($token !=''){
									//if there is a token - make a tranaction
									 $this->donfo_transaction($data ,$token);
									 
									 //$this->responsePayment['tranStatus']='1';
									 
									 if($this->responsePayment['tranStatus'] =='1' ){
											$paymentID= $this->responsePayment['transation_id'];
											//$paymentID='11';
											$order_id =  $this->confirmOrder($paymentID,$this->userid);
											if(intval($order_id) >0){
												 
											}
											if (intval($order_id) > 0)
											{      
										//add condition for dummy email on 6 oct
												if(empty($request->getParam('noemail')))
													{
														$this->sendTicketInformationEmail($order_id,$this->userid);
													} 
													unset($_SESSION["cart_products"]);
													$jsonData = array(
														'status' => '1',
														'msg' => 'Paiement réalisé avec succès, la commande a bien été confirmée !'
													);
													return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
													exit;
											} else {
													$jsonData = array(
														'status' => '0',
														'msg' => 'Order save Failed'
													);
													return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
													exit;
											}
										 
										   
                                         
										 
									 } else{
													$jsonData = array(
														'status' => '0',
														'msg' => 'La transaction a échoué'
													);
													return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
													exit;
										 
										
									 }
									 
									 
								}else{
													$jsonData = array(
														'status' => '0',
														'msg' => 'Payment get decline'
													);
													return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
													exit;
										 
										}
					 
						
			}			

			if ( $select_selling_type == 0 || $select_selling_type==2)
			{
				$successmsg='';
				$paymentID = '111';
				$order_id = $this->confirmOrder($paymentID,$this->userid);
				if (intval($order_id) > 0)
					{
						//change for dummy email on 6 oct
						if(empty($request->getParam('noemail')))
						{
							$this->sendTicketInformationEmail($order_id,$this->userid);
						}
						if($select_selling_type==2)
						{
							$successmsg='La commande de billets gratuits a bien été effectuée !';
						}
						else
						{
							$successmsg='Paiement réalisé avec succès, la commande a bien été confirmée !';
						}
					$jsonData = array(
						'status' => '1',
						'msg' =>$successmsg
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
					}

					{
					$jsonData = array(
						'status' => '0',
						'msg' => 'Order save Failed'
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
					}
			}
			if ( $select_selling_type == 2)
			{
				$jsonData = array(
						'status' => '0',
						'msg' => 'Keep patience we are working on it'
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
			}

			

	}

       
     public  function getEventTime($request, $response){ 
				/* date settings */ 
			 $bodytext='';
				 $eventid='0';
                  if( $request->getParam('event_id') != null)
                  	{
                  		$eventid = $request->getParam('event_id');

                  	 } 



				 
				  $event_timelist =  EventTime::where('event_id', $eventid)->get();
				   
				   $bodytext.='<option value="">'.$this->lang['selling_select_txt'].' </option>';
				   foreach($event_timelist as $row){ 
				   $bodytext.='<option value="'.$row['event_id'].','.date('H:i', strtotime($row['event_time'])).'">'.date('H:i', strtotime($row['event_time'])).'</option>';

				   }
				  
				 
				 
				// echo $bodytext;

				 return $bodytext;



	 }



		 function ajaxcallEventOrder($request, $response){ 
			 error_reporting(0);
			$jsonData=array();
			$booking_fees=0;
				/* date settings */ 
			    $reservedSeatArr =array('2','3','4'); //1=Standard,2=Réservées,3=Invitations,4=Vendues à autre opérateur
				$unreservedSeatArr =array('1','2','3','4','5');  //1=Standard,2=Réservées,3=Invitations,4=Vendues à autre opérateur
				 $eventid='0';
                  if( $request->getParam('evnt_id') != null){$eventid = $request->getParam('evnt_id'); } 
				  if($eventid=='0'){exit;}
				  $bodyText='';
				  $event_list = Event::where('id', $eventid)->where('status','1')->where('date','>=',date('Y-m-d'))->get();
				   
				  
				  $timeE =  $request->getParam('dataTime'); 
				  // $timein =   strtotime($timeE);
				  $timein =  $timeE;
				   
				  $array_data  = getEventList($event_list); 
				 
				  //print_r($array_data);exit;
				 
				 $eventgroup_id = '';
				 $advertisementImg = '';
				  foreach($array_data as $row){
					  
					  $advertisementImg = $row['adv_image']; //Get Even Advertisement Image
					  $eventgroup_id   = $row['eventgroup_id']; //get Event Group ID
					  $booking_fees=$row['booking_fee'];
					  $date_Y ='';$date_m ='';$date_d ='';	   
					  $date_Y = $row['date_Y'];
					  $date_m = $row['date_m'];
					  $date_d = $row['date_d'];
					  $selectedDate = $date_Y.'-'.$date_m.'-'.$date_d;
					  $geWeekDay = $this->getWeekday($selectedDate); 
					  //echo $geWeekDay;
					  $geWeekDay =$geWeekDay+1;
					  $monthName =  $this->ChangeCalanderLg($date_m);
					  $dayName =  $this->DaysLg($geWeekDay);
				  
				  
                  
				   $bodyText= '<div class="alert alert-primary text-center" role="alert"><h3>'.$row['title'].'  ' .$row['event_auditorium'].'<br />'.$dayName.' '.$row['date_j'].' '. $monthName.' '.$row['date_Y'].' à '.$timein.' </h3> </div>';
				   
				   
				   
				  $ticket_list =  EventSeatCategories::where('event_id', $eventid)->get();	


				   $bodyText .= ' 
				                        <table class="table">
											  <thead>
												<tr>
												  <th scope="col">catégorie</th>
												  <th scope="col">tarif</th>
												  <th scope="col">Rang</th>
												  
												  <th scope="col">quantité</th>
												  <th scope="col">Prix personnalisé</th>
												</tr>
											  </thead><tbody>';
				   
				   
				   
				   //<th scope="col">Custom</th>
				 
				   if(count( $ticket_list) >  0 ){
					 foreach($ticket_list as $ticket){
						 
									$event_ticket_type = $ticket['libres'];		
									
									$seat_cat_id =  intval($ticket['id']);// print $seat_cat_id;
									 
									//$row_ticket_list =  RowSeats::where('event_seat_categories_id', $seat_cat_id)->wherein('placement',$unreservedSeatArr)->get();
									$row_ticket_list =  RowSeats::where('event_seat_categories_id', $seat_cat_id)->get();
									// print_r($row_ticket_list);exit;
									/* Get All Rows */
										$query = RowSeats::query(); 
										$query = $query->select('row_number');
										$query = $query->where('event_seat_categories_id', $seat_cat_id);
										//$query = $query->wherein('placement',$unreservedSeatArr);
										$query = $query->groupBy('row_number');
										$query = $query->havingRaw('COUNT(*) > 1');
										$duplicate_rows = $query->get();
										$row_numbers=array(); 
										//print_r($duplicate_rows);
										 foreach ($duplicate_rows as $rowii){  $row_numbers[] = $rowii['row_number'];  }
										// print_r($row_numbers);exit;
									/* End Rows */
								if($event_ticket_type != '1'){					 
										 
											$bodyText .= ' <tr> <th scope="row"> '.$ticket['seat_category'].'</th>'; 
																
													//Add Price Here

													$bodyText .= 	'<td> 
																			<strong>'.$ticket['category_price'].'<span>&#8362;</span></strong>
																			<input type="hidden" name="free_placement_id[]" value="0"   > 	
																			<input type="hidden" name="event[]" value="'.$row['id'].'"   > 	
																			<input type="hidden" name="evtime[]" value="'.$timeE.'"   > 
																																			
																			<input type="hidden" name="ticket_price[]" id="ticket_price_'.$ticket['id'].'" value="'.$ticket['category_price'].'"  > 
																			<input type="hidden" name="pticket_price[]" id="pticket_price_'.$ticket['id'].'" value="'.$ticket['category_price'].'"  > 
																			<input type="hidden" name="ticket_type[]" value="'.$ticket['seat_category'].'"  > 
																			<input type="hidden" name="ticket_type_id[]" value="'.$ticket['id'].'"  > 
																			<input type="hidden" name="seat_number[]"  id="seat_number_hdn_'.$ticket['id'].'"  > 
																			<input type="hidden" name="totalavailabletkthdn[]" id="totalavailabletkthdn_'.$ticket['id'].'"   > 
																	 </td>';

													
													$bodyText.='<td>
																		 
																				<select name="ticket_rows[]" class="tickrow"  id="row_'.$ticket['id'].'">';
																				$i=0;
																			 
																						if(count($row_ticket_list) > 0){
																							$bodyText .= '<option></option>';
																							foreach($row_ticket_list as $rowlist){
                                                                                               
																							   $display_row_number=''; 
																							   if (in_array($rowlist['row_number'], $row_numbers)) {
																								    $display_row_number=$rowlist['row_number'].'(sièges '.$rowlist['seat_from'].' à '.$rowlist['seat_to'].')';
																							   }else{ $display_row_number= $rowlist['row_number'];}
																							   
																								//if (!in_array($rowlist['placement'], $reservedSeatArr)) {
																									$bodyText .= '<option value="'.$rowlist['id'].'-'.$rowlist['row_number'].'">'.$display_row_number.'</option>';
																								//}
                                                                                                																						   
																								

																							}
																							 
																						} 
																				 
																			 
																				
													$bodyText .= 			    '</select>
																					<input type="hidden" name="ticket_price_sequence[]"	 id="ticket_price_hdn_'.$ticket['id'].'"  />
																					<input type="hidden" name="pticket_price_sequence[]"	 id="pticket_price_hdn_'.$ticket['id'].'"  />	
																					<input type="hidden" name="ticket_quentity[]"	 id="ticket_quentity_hdn_'.$ticket['id'].'"  />
																					<input type="hidden" name="seat_from[]"  id="seat_from_hdn_'.$ticket['id'].'"  > 
																					<input type="hidden" name="seat_to[]"  id="seat_to_hdn_'.$ticket['id'].'"  >
																					<input type="hidden" name="seat_sequence[]"  id="seat_sequence_hdn_'.$ticket['id'].'"  >
																	  </td>';
			 
											  		$bodyText.='<td><select name="custom_seat_qty[]" id="custom_seat_qty_'.$ticket['id'].'" class="ctqtyto"  multiple="multiple" readonly="readonly">';

													$bodyText .= '<option value="0">0</option>
																		</select> </td> ';
																				 
							//$bodyText .= '<td><select name="seat_qty[]" id="seat_qty_'.$ticket['id'].'" class="qtyto"  >'; 
			//$bodyText .='<option value="">0</option></select></td>;
													
													$bodyText .='<td><input type="text" name="seatprice" class="seatprice" id="seatprice_'.$ticket['id'].'" ></td></tr>';
													$bodyText .= '<tr><td colspan="4" id="rowSeatMap_hdn_'.$ticket['id'].'" > </td></tr>    ';																				 
								}else{
											
											 
                                            $free_placment_row_number = 	$row_ticket_list[0]['row_number'];	
											$free_placment_row_id = 	$row_ticket_list[0]['id'];	
											$free_placment_row_seat_avlability = 	$row_ticket_list[0]['total_qantity'];	
											$free_placment_row_net_total_quantity = 	$row_ticket_list[0]['net_total_quantity'];	

 											$booking_seat_available_arr = $this->getAvailableSeat($free_placment_row_id);
											$totalSeatAvailable = $booking_seat_available_arr['booking_seat_available'] ;
											
											// $rowArr = $this->getSeatSeq($free_placment_row_id,$qtx)
											  
											 
											$bodyText .= '<tr> <th scope="row"> '.$ticket['seat_category'].'</th>'; 
																
													//Add Price Here

													$bodyText .= 	'<td>
																			<strong>'.$ticket['category_price'].'<span>&#8362;</span></strong>
																			<input type="hidden" name="free_placement_id[]" value="1"   > 	
																			<input type="hidden" name="event[]" value="'.$row['id'].'"   > 	
																			<input type="hidden" name="evtime[]" value="'.$timeE.'"   > 
																			<input type="hidden" name="ticket_price_sequence[]"	 id="ticket_price_hdn_'.$ticket['id'].'"  />
																			<input type="hidden" name="pticket_price_sequence[]"	 id="pticket_price_hdn_'.$ticket['id'].'"  />
																			<input type="hidden" name="ticket_quentity[]"	 id="ticket_quentity_hdn_'.$ticket['id'].'"  />
																			<input type="hidden" name="ticket_price[]" id="ticket_price_'.$ticket['id'].'" value="'.$ticket['category_price'].'"  > 
																			<input type="hidden" name="pticket_price[]" id="pticket_price_'.$ticket['id'].'" value="'.$ticket['category_price'].'"  > 
																			<input type="hidden" name="ticket_type[]" value="'.$ticket['seat_category'].'"  > 
																			<input type="hidden" name="ticket_type_id[]" value="'.$ticket['id'].'"  > 
																			<input type="hidden" name="seat_number[]"  id="seat_number_hdn_'.$ticket['id'].'"  > 
																			<input type="hidden" name="totalavailabletkthdn[]"  value="'.$ticket['id'].'"    > 
																	 </td>';

													
													$bodyText.='<td>
																		 
																				<strong>Libre</strong> 
																				 <input type="hidden" name="ticket_rows[]" value="'.$free_placment_row_id.'-'.$free_placment_row_number.'"  id="row_'.$ticket['id'].'" > 
																				  
																			 
																		';		
													$bodyText .= 			    ' 
																					<input type="hidden" name="seat_from[]" value=""  id="seat_from_hdn_'.$ticket['id'].'"  > 
																					<input type="hidden" name="seat_to[]"  value=""   id="seat_to_hdn_'.$ticket['id'].'"  >
																					<input type="hidden" name="seat_sequence[]"  value=""  id="seat_sequence_hdn_'.$ticket['id'].'"  >
																		 
																 </td>';
			 
											  			$bodyText .= '<td>
																		<select name="custom_seat_qty[]"  id="custom_seat_qty_'.$ticket['id'].'" class="ctqtyto"  multiple="multiple" readonly="readonly">'; 
																		
														$bodyText .= 			    '<option value="">0</option>';
														//changes for seat squence 
																					 if(intval($free_placment_row_id) > 0){
																									 
																									 
																									
																									/* Start New SeatManagement Code Implementation */
																									$seatNArr  =  EventCategoryRowSeat::where('row_seats_id', $free_placment_row_id)->where('status','!=', 'B')->where('placement', '1')->orderBy('id', 'ASC')->get();	
																									
																									if(count($seatNArr) > 0 ){
																											$seat_sequence_no='';
																											
																											foreach($seatNArr as $seatN){ 
																											   $bodyText.='<option value="'.$seatN->seat_number.",".$seatN->seat_price.'">'.$seatN->seat_number.'</option> ';
																											 
																											}  
																											 
																											 
																									}
																								 

																					 }
													$bodyText .= '
																		</select>
																	</td>';
																				 
				//$bodyText .= '<td><select name="seat_qty[]"  id="seat_qty_'.$ticket['id'].'" class="qtyto"  >'; 
																		
		//$bodyText .= '<option value="">0</option>';
							//	 for($i=1; $i <= $totalSeatAvailable; $i++){
	//$bodyText .= '<option value="'.$i.','.$ticket['category_price'].'">'.$i.'</option></select></td>';
																					// }
	$bodyText .= '<td><input type="text" name="seatprice" class="seatprice" id="seatprice_'.$ticket['id'].'"></td></tr>';
														$bodyText .= '<tr><td colspan="4" id="rowSeatMap_hdn_'.$ticket['id'].'" > </td></tr>    ';																		
								}								
								$bodyText .= ' <div id="booking_fee"></div><div id="total_items"> </div> <div id="tpromo"></div>';	 					
						}
						 
					}else{
                         $bodyText.= '<tr><td text-align="center" colspan="4"><div class="alert alert-error text-center" role="alert"><h3>Aucune information de ticket n\'existe </h3> </div></td></tr> ';
				   
						  
					}	
				  		//$bodyText.= '<tr id="" ><th scope="row"></th><td></td><td></td><td></td></tr>';	
			 	
                        // $bodyText.= '<tr><td text-align="center" colspan="4"><div class="alert alert-error text-center" role="alert"><h3>Aucune information de ticket n\'existe </h3> </div></td></tr> ';
				          $bodyText .= ' <tbody> </table>';
				   
				  }
				   
				 /*Fetch Event Advertisement */
							       
								 if($advertisementImg!=''){ 
									 $advertisementImg = EVENT_ADS_WEB_PATH.'/'.$advertisementImg;  
								 }else{
									 $advertisementImg = WEB_PATH.'/uploads/advertisements/default.jpg';
								 }
								 
					$jsonData = array('bodyText' => $bodyText, 'adv_image' => $advertisementImg, 'booking_fees'=>$booking_fees);
					return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));
					exit();	
				
			}
           //Fetch Booking Price Here
		   public function ajaxcallBookingPrice($request, $response){ 
			    $priceqtx =  $request->getParam('price'); 
				
			   
		   }
 
 
 
			function getAvailableSeat($rowseat){
				$jsonData=array();
				 $strList='';
				 
                 
				

				 if(intval($rowseat) > 0){
					 $audArr =  RowSeats::where('id', $rowseat)->first();	
					 
					 
					 
					//$total_qantity =$audArr['total_qantity'];
					//$net_total_quantity =$audArr['net_total_quantity'];
					//$total_qantity = intval($total_qantity) - intval($net_total_quantity);
					$total_qantity  =  EventCategoryRowSeat::where('row_seats_id', $rowseat)->where('status','!=', 'B')->where('placement', '1')->count();	 
					//echo "I am here".$total_qantity ;exit;
					
					$user_booking_seat ='30';
					if($user_booking_seat > $total_qantity){
						$user_booking_seat = $total_qantity;
					}

					 

					//'ticket_price' => $ticket_price, 'choose_rows' => $bodyText
					$jsonData = array(
						'available_ticket_quantity' => $total_qantity, 
						'booking_seat_available' => $user_booking_seat
					);

				 }else{
						 ;
						$jsonData = array(
							'available_ticket_quantity' => '0', 
							'booking_seat_available' => '0' 
						); 
				 }
				 return $jsonData;
				
			}




			public function ajaxcallPriceRaw($request, $response){ 
			   
				 $id=$request->getAttribute('id'); //fetch the category id
				 
				 $bodyText='';
				 $audArr =   EventSeatCategories::where('id', $id)->first() ;
				 $ticket_price =$audArr['category_price'];
 				 $seat_row_from =$audArr['seat_row_from'];
				 $seat_row_to =$audArr['seat_row_to']; 
				 $total_qantity =$audArr['total_qantity'];
				 $seat_row_to =$audArr['seat_row_to'];
				  $seat_row_to =$audArr['seat_row_to'];
				 $ticket_type=$audArr['seat_category'];
				 $bodyText .= '<option value=""></option>';
					if (is_numeric($seat_row_from)){
							if(intval($seat_row_from) > 0){
							 
								  for($i=$seat_row_from; $i<= $seat_row_to; $i++){
									  $bodyText .= '<option value="'.$id.'-'.$i.'">'.$i.'</option>';
								  }


							} 
					}
					else{
						 for($i = $seat_row_from ; $i <= $seat_row_to ; $i++){
							$bodyText .= '<option value="'.$id.'-'.$i.'">'.$i.'</option>';
						 }
							 
					}

                $jsonData = array('ticket_price' => $ticket_price, 'choose_rows' => $bodyText, 'ticket_type' => $ticket_type);
					return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));
					exit();				
               		
			}

		public	function ajaxcallRawSeat($request, $response){ 
			   $strList='';
			    $seatSquence='';
			   $seatSquence.='<option value="0"></option> ';
				 $rowseat=$request->getAttribute('rowno'); //fetch the category id
                 
				 

				 if(intval($rowseat) > 0){
					 
					 
					
					/* Start New SeatManagement Code Implementation */
					$seatNArr  =  EventCategoryRowSeat::where('row_seats_id', $rowseat)->where('status','!=', 'B')->where('placement', '1')->orderBy('id', 'ASC')->get();	
					if(count($seatNArr) > 0 ){
							$seat_sequence_no='';
							
							foreach($seatNArr as $seatN){ 
							   $seatSquence.='<option value="'.$seatN->seat_number.",".$seatN->seat_price.'">'.$seatN->seat_number.'</option> ';
							 
							}  
							 
							 
					}
					/* End here */		 
					 
					 
					$audArr =  RowSeats::where('id', $rowseat)->first();	
					 
					
					 
					//$total_qantity =$audArr['total_qantity'];
					//$net_total_quantity =$audArr['net_total_quantity'];
					$eentseatcatid=$audArr['event_seat_categories_id'];
                     $Eventseatprice = EventSeatCategories::select('category_price')->where('id', $eentseatcatid)->first() ;
                     $ticket_price= $Eventseatprice['category_price'];
					$total_qantity = EventCategoryRowSeat::where('row_seats_id', $rowseat)->where('status','!=', 'B')->where('placement', '1')->count();	
					//print_r($total_qantity);exit;
					$user_booking_seat ='30';
					if($user_booking_seat > $total_qantity){
						$user_booking_seat = $total_qantity;
					}

					if($total_qantity > 0 ){
						//$ticket_price=20;
						$strList.='<option value=""></option> ';
						for($i = 1; $i<= intval($user_booking_seat);$i++){
							$strList.='<option value="'.$i.','.$ticket_price.'">'.$i.'</option> ';
						}
					}else{
						$strList.='<option value="0">0</option> ';
					}

					//'ticket_price' => $ticket_price, 'choose_rows' => $bodyText
					$jsonData = array(
					    'multiple_select_seat' => $seatSquence,
						'available_ticket_quantity' => $total_qantity, 
						'choose_ticket_quantity' => $strList 
					);
					return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));
					exit();	  

				 }else{
						//'ticket_price' => $ticket_price, 'choose_rows' => $bodyText
						$strList.='<option value=""></option> ';
						$jsonData = array(
						   'multiple_select_seat' => $seatSquence,
							'available_ticket_quantity' => '0', 
							'choose_ticket_quantity' => $strList 
						);
						return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));
						exit();	 


				 }
			 
				 
               		
			}
			function getSeatSeq($row_id,$qtx){
				 
					$audArr =  RowSeats::where('id', $row_id)->first();	
					list($seat_quentity,$ticket_price) = explode(',', $qtx);
					$getqtx=$seat_quentity;
					 
					$seat_order =$audArr['seat_order'];
					
					$seat_from =$audArr['seat_from'];
					$seat_to='';
					if(intval($seat_order) == '2' ){
						$totalSeatSeq =  $getqtx*2;
						$seat_to=($seat_from-2)+$totalSeatSeq; 
						
					}else{
						$totalSeatSeq =  $getqtx*1;
						$seat_to=($seat_from-1)+$totalSeatSeq; 
					}
					 
					
					 
					$seat_sequence='';
					
					for( $i = $seat_from; $i<= $seat_to; $i++ ){
						
						if(intval($seat_order) == '2' ){
							if( $i%2==0){
								$seat_sequence.=$i.",";
							}
							 
						
						}else{
							 $seat_sequence.= $i.",";
						}
					}
					$seat_sequence = rtrim($seat_sequence,',');
					//'ticket_price' => $ticket_price, 'choose_rows' => $bodyText
					$jsonData = array(
						'seat_from' => $seat_from, 
						'seat_to' => $seat_to ,
						'seat_sequence' => $seat_sequence,
						
					);
					  
                    return 	$jsonData ;				  

				  
			}

		public 	function ajaxcallRawSeatSequence($request, $response){ 
		     error_reporting(0);
			   $strList='';
			  // $totalquanity=0;
			  $total_amt=0;
				 $row_id=$request->getAttribute('rid'); //fetch the category id
				 $getqtx=$request->getAttribute('qtx'); //fetch the category id
				 $seatno=$request->getAttribute('seatno'); //fetch the category id
				 
				 	list($seat_quentity,$ticket_price) = explode(',', $getqtx);
					$qtx=$seat_quentity;


				 // $eventPriceArr = $request->getParam('ticket_price');
				// $eventTicketType = $request->getParam('ticket_type');    //Ticket Category like left,right
			   //  $ticket_type_ids = $request->getParam('ticket_type_id'); 


				 $totalquanity[]=$qtx;


				 //$totalqty= $totalquanity+$totalquanity;

				 $seat_sequence='';
				 $seat_price_sequence='';
				$seat_from='';
				$seat_to='';
                
				if(intval($row_id) > 0){
					
				/* Start New SeatManagement Code Implementation */
					$seatNArr  =  EventCategoryRowSeat::where('row_seats_id', $row_id)->where('status','!=', 'B')->where('placement', '1')->orderBy('id', 'ASC')->get();	
					if(count($seatNArr) > 0 ){
							$seat_sequence_no='';
							$seat_sequence_price = '';
							foreach($seatNArr as $seatN){ 
								$seat_sequence_no.= $seatN->seat_number.",";
								if($request->getParam('seatprice') !='')
								{
									$ticketprice=$request->getParam('seatprice');
									$seat_sequence_price .=  $ticketprice.",";
								}
								else
								{
									$seat_sequence_price .=  $seatN->seat_price.",";
								}
								
							}  
							$seatSquence = rtrim($seat_sequence_no,','); 
							$seatPriceSquence = rtrim($seat_sequence_price,','); 
							$seatSquenceArr = explode(',', $seatSquence);
							
							$seatPriceSquenceArr = explode(',', $seatPriceSquence);
							
							if(count($seatSquenceArr) > 0 ){
								  $startindex= '0'; 
								  $endIndex =$qtx-1;
								    $seat_from=$seatSquenceArr[$startindex];
				                    $seat_to=$seatSquenceArr[$endIndex];
									for( $i = $startindex; $i<= $endIndex; $i++ ){ 
											$seat_sequence.= $seatSquenceArr[$i].",";
								    } 	
									$seat_sequence = rtrim($seat_sequence,','); 
							}
							$pricehtml='Seat Order: ';
							//Get Seat Price Sequence from here //
							if(count($seatPriceSquenceArr) > 0 ){
								  $startindex= '0'; 
								  $endIndex =$qtx-1;
								   
									for( $i = $startindex; $i<= $endIndex; $i++ ){ 
											$seat_price_sequence.= $seatPriceSquenceArr[$i].",";
											$total_amt += $seatPriceSquenceArr[$i];
											$pricehtml.='<span  data-toggle="tooltip" data-placement="top" title="prix: '.$seatPriceSquenceArr[$i].' &#8362;"  class="dot"   ></span>';
											
								    } 	
									$seat_price_sequence = rtrim($seat_price_sequence,','); 
							} 
					}
					 
				
				/* End Here */ 
					 
					 
					$jsonData = array(
						'seat_from' => $seat_from, 
						'seat_to' => $seat_to ,
						'seat_sequence' => $seat_sequence,
						'seat_price_sequence' => $seat_price_sequence,
						'price_html' => $pricehtml,
						'total_amt' => $total_amt
					);
					return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));
					exit();	  

				 }else{
						//'ticket_price' => $ticket_price, 'choose_rows' => $bodyText
						 
						$jsonData = array(
								'seat_from' => '', 
								'seat_to' => '' ,
								'seat_sequence' => '',
								'seat_price_sequence' => '',
								'price_html' => '',
								'total_amt' => ''
						);	 


				 }
			 
				 
               		
			}


			public function confirmOrder($paymentID,$userid){
				
					//print_r($_SESSION["cart_products"]);exit;
				
				
								$invoicecode=array();
								$order = new Order();
								//$userid=$_SESSION['adminId'];  
								//$userid='101'; 
								//print($this->userid);exit;
								$order->customer_id =$userid; 
								$order->payment_type = $this->payment_type; 
								$order->created_on = date('Y-m-d h:i:s'); 
								//echo  $this->total_reserved_fees;;exit;
								
								 
								$order->total_amount =  $this->total_reserved_fees;
								$order->payment_id =  $paymentID;
								$order->save(); 
								if($order->id > 0){
									$invoice = intval('18000');
									$invoice_id = $invoice+$order->id;
									$invoice_number='FA'.$invoice_id;
									$reservation_number='CA'.$invoice_id;
									$xxx = intval('74444');
									$xxx = intval($xxx)+$this->userid;;
									$customer_number='CANO'.$xxx.'XX' ;

									$this->invoicecode['invoice_number']=$invoice_number;
									$this->invoicecode['reservation_number']=$reservation_number;
									$this->invoicecode['customer_number']=$customer_number;
									
									Order::where('id', $order->id)->update(['invoice_number' => $invoice_number]);
								} 
								if(isset($_SESSION["cart_products"])){ 
								
									$itemCount= count($_SESSION["cart_products"]);
									//print_r($_SESSION["cart_products"]);exit;
									//echo $itemCount;
			  
									  if(count($_SESSION["cart_products"])>0){

											foreach($_SESSION["cart_products"] as $cartItem){	
												$orderitem = new OrderItems();
												$orderitem->order_id = $order->id; 
												
												
												$orderitem->product_id = $cartItem['event_id']; 
												$product_id=$cartItem['event_id']; 
												$orderitem->quantity =  $cartItem['qtx']; 
												$orderitem->price =  $cartItem['price']; 
												$orderitem->type_product = 'event'; 
												$orderitem->ticket_category = $cartItem['ticket_category']; 
										    	$orderitem->free_placement  = $cartItem['free_placement'];  
												
												$orderitem->ticket_row = $cartItem['ticket_row'];
												$orderitem->seat_qty = $cartItem['seat_qty'];
												$orderitem->booking_time = $cartItem['booking_time'];
												$orderitem->event_ticket_category_id = $cartItem['ticket_type_id'];
												$orderitem->ticket_type = $cartItem['ticket_type']; 
												if($orderitem->ticket_type == '1'){
												 
														$orderitem->available_seats = $cartItem['total_available_seat']; 
														$orderitem->ticket_row_id =   $cartItem['ticket_row_id'];
														$orderitem->seat_from =   $cartItem['seat_from'];
														$orderitem->seat_to =   $cartItem['seat_to'];
														$orderitem->seat_sequence =   $cartItem['seat_sequence'];
														
												}else{
														$orderitem->available_seats = $cartItem['total_available_seat']; 
														$orderitem->ticket_row_id =   $cartItem['ticket_row_id'];
														$orderitem->seat_from =   $cartItem['seat_from'];
														$orderitem->seat_to =   $cartItem['seat_to'];
														$orderitem->seat_sequence =   $cartItem['seat_sequence'];
												}
												$seatSeq= $orderitem->seat_sequence;
												
											 
												$orderitem->created_on = date('Y-m-d h:i:s'); 
												
												$orderitem->price_sequence =   $cartItem['price_sequence'];
												 
												
												$orderitem->save(); //echo 'event'.$orderitem->id;
													$table_pk_id = $cartItem['ticket_type_id'];
													$booked_seats_quantity = $cartItem['qtx'];
													$row_id =  $cartItem['ticket_row_id'];
													$customerid=$order->customer_id; 
												  
													//update_row_Quantity($row_id,$booked_seats_quantity,$seatSeq,$customerid); 
													$seat_sequence_ids = update_row_Quantity($row_id, $booked_seats_quantity, $seatSeq,$customerid); 
													$orderitem->seat_ids_sequence =$seat_sequence_ids;
												
													$orderitem->save(); //echo 'event'.$orderitem->id;
												 
												 	  	 
											}

									  }
									 
									 //Extra Booking Fees Items

												$orderitem = new OrderItems();
												$orderitem->order_id = $order->id; 
												$orderitem->product_id = '0'; 
												$total_seat_qtx= $this->booking_items;
												$orderitem->quantity =  $total_seat_qtx; 
												$orderitem->created_on = date('Y-m-d h:i:s');
												$orderitem->price =  $this->booking_fee;; 

												$orderitem->type_product = 'booking_fees'; 
												$orderitem->ticket_type = ''; 
												
												$orderitem->save();  //echo 'bookingfees'.$orderitem->id;

												if(isset( $_SESSION['cart_extra']['coupon_detail'])){ 	   
												//Add to coupon history // 
													//print_r($_SESSION['cart_extra']['coupon_detail']);
													//exit;
													$couponArr  = $_SESSION['cart_extra']['coupon_detail'];
												 if(count($couponArr)> 0 ){
												 $incriment=0;
														$couponH = new CouponHistory();
														$couponH->coupon_id=$couponArr['coupon_id'];
														$couponH->customer_id=$order->customer_id;
														$couponH->event_id=$product_id;
														$couponH->order_id = $order->id;
														$couponH->save(); 
														
														$couponused=Coupon::where('id',$couponArr['coupon_id'])->first();
														$coupoincriment=intval($couponused['coupon_used']);
														$incriment=$coupoincriment+1;
														//print_r($incriment);exit;
														
														$coupenupdate=Coupon::find($couponArr['coupon_id']);
														$coupenupdate->coupon_used=$incriment;
														$coupenupdate->save();
												
												 }
												 //End here //  
									  }



												unset( $_SESSION["cart_extra"]);
									 
									 //End Extra Booking 
									   
									   
											 
								}
								
								
								
								
								return intval($order->id);
			}	







          function getWeekday($date) {
				return date('w', strtotime($date));
			}

			function ChangeCalanderLg($month){

					$monthno =$month;
					$monthname='';

					switch ($monthno) {
					    case 1:
					        $monthname='janvier';
					        break;
					    case 2:
					        $monthname='février';
					        break;
							case 3:
					        $monthname='mars';
					        break;
							case 4:
					        $monthname='avril';
					        break;
							case 5:
					        $monthname='mai';
					        break;
							case 6:
					        $monthname='juin';
					        break;
							case 7:
					        $monthname='juillet';
					        break;
							case 8:
					        $monthname='août';
					        break;
							case 9:
					        $monthname='septembre';
					        break;
							case 10:
					        $monthname='octobre';
					        break;
							case 11:
					        $monthname='novembre';
					        break;
							case 12:
					        $monthname='décembre';
					        break;
					}
					return $monthname;
		    }

		    function DaysLg($day){

					$dayno =$day;
					$dayname='';

					switch ($dayno) { 
					    case 1:
					        $dayname='Dim'; //Sun
					        break;
					    case 2:
					        $dayname='Lun';
					        break;
						case 3:
					        $dayname='Mardi';
					        break;
					    case 4:
					        $dayname='Mer';
					        break;
						case 5:
					        $dayname='Jeu';
					        break;
						case 6:
					        $dayname='Ven';
					        break;
						case 7:
					        $dayname='Sam';
					        break;
							  
					}
					return $dayname;
		    }

		     function get_client_ip()
			 {
				  $ipaddress = '';
				  if (getenv('HTTP_CLIENT_IP'))
					  $ipaddress = getenv('HTTP_CLIENT_IP');
				  else if(getenv('HTTP_X_FORWARDED_FOR'))
					  $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
				  else if(getenv('HTTP_X_FORWARDED'))
					  $ipaddress = getenv('HTTP_X_FORWARDED');
				  else if(getenv('HTTP_FORWARDED_FOR'))
					  $ipaddress = getenv('HTTP_FORWARDED_FOR');
				  else if(getenv('HTTP_FORWARDED'))
					  $ipaddress = getenv('HTTP_FORWARDED');
				  else if(getenv('REMOTE_ADDR'))
					  $ipaddress = getenv('REMOTE_ADDR');
				  else
					  $ipaddress = 'UNKNOWN';

				  return $ipaddress;
			 }
			 function getToken($length){
					 $token = "";
					 $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
					 $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
					 $codeAlphabet.= "0123456789";
					 $max = strlen($codeAlphabet); // edited

					for ($i=0; $i < $length; $i++) {
						$token .= $codeAlphabet[random_int(0, $max-1)];
					}

					return $token;
		    }
			
			function getEmail($length){
					 $token = "";
					 $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
					 $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
					 $codeAlphabet.= "0123456789";
					 $max = strlen($codeAlphabet); // edited

					for ($i=0; $i < $length; $i++) {
						$token .= $codeAlphabet[random_int(0, $max-1)];
					}
					$email=$token.'_@dummymail.com';
					return $email;
		    }
			 
			 
			 
			
		 
 
			function FullDaysLg($day){

					$dayno =$day;
					$dayname='';

					switch ($dayno) { 
					    case 1:
					        $dayname='dimanche'; //Monday 
					        break;
					    case 2:
					        $dayname='Lundi';
					        break;
						case 3:
					        $dayname='Mardi';
					        break;
					    case 4:
					        $dayname='Mercredi';
					        break;
						case 5:
					        $dayname='Jeudi';
					        break;
						case 6:
					        $monthname='Vendredi';
					        break;
						case 7:
					        $dayname='samedi';
					        break;
							  
					}
					return $dayname;
		    }
				
			public function donfo_create_token($data){
			  
					$tranzilatoken = '';
					
					$expdate = str_replace("/", "", $data['expiration_date']);
					$expdate = preg_replace('/\s+/', '', $expdate);
					// Prepare transaction parameters>
					$query_parameters['supplier'] = $this->supplier;
				    $query_parameters['sum'] = $data['total_amount'];  
				 	$query_parameters['currency'] = $data['currency']; //Type of currency 1 NIS, 2 USD, 978 EUR, 826 GBP, 392 JPY
					$query_parameters['ccno'] = $data['credit_card_number']; // Test card number = '12312312'
					
				    $query_parameters['expyear'] = $data['expiration_Year'] ;// Card expiry date: mmyy ='0820'
				    $query_parameters['expdate'] = $expdate ;// Card expiry date: mmyy ='0820'
					$query_parameters['mycvv'] = $data['mycvv'] ;// Card expiry date: mmyy ='0820'
					 
					if($data['currency'] == 1){
				        $query_parameters['myid'] = $data['id_number']; // ID number = '12312312'
					}
					else{
				  //      $query_parameters['myid'] = '';
					}
					$query_parameters['myid'] = '';
					 
				 //   $query_parameters['cred_type'] = '1'; // This field specifies the type of transaction, 1 - normal transaction, 6 - credit, 8 - payments
					$query_parameters['TranzilaPW'] = $this->tranzilapw; // Token password if required
				  //  $query_parameters['tranmode'] = 'V'; // Mode for verify transaction
					$query_parameters['TranzilaTK'] = 1;
					// Prepare query string
					$query_string = '';
					foreach ($query_parameters as $name => $value) {
						$query_string .= $name . '=' . $value . '&';
					}
					$query_string = substr($query_string, 0, -1); // Remove trailing '&'
					// Initiate CURL
					$cr = curl_init();
					curl_setopt($cr, CURLOPT_URL,$this->tranzila_api_host.$this->tranzila_api_path);
					curl_setopt($cr, CURLOPT_POST, 1);
					curl_setopt($cr, CURLOPT_FAILONERROR, true);
					curl_setopt($cr, CURLOPT_POSTFIELDS, $query_string);
					curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 0);
					// Execute request
					$result = curl_exec($cr);
					$error = curl_error($cr);
					if (!empty($error)) {
						return ($error);
					}
					curl_close($cr);
					// Preparing associative array with response data
					$response_array = explode('&', $result);  
					if (count($response_array) >= 1) {
						foreach ($response_array as $value) {
							$tmp = explode('=', $value);
							if (count($tmp) > 1) {
								$response_assoc[$tmp[0]] = $tmp[1];
							}
						}
					}
				   // Analyze the result string
					if (!isset($response_assoc['TranzilaTK'])) {
						$this->errorPayment['token'] ='Token is not Valid. Please contact to Administration';
						//getting a token was unsuccessful
						
						//getting a token was unsuccessful 
					   /*
						foreach($response_assoc as $k=> $v){
							$res = 	filter_var($v, FILTER_SANITIZE_STRING);
									echo '<div class="error">'.$k.': <p>'.$res.'</p></div>';
							
						} */
						 
					}  else {
						$tranzilatoken = $response_assoc['TranzilaTK']; //TranzilaTK
					   //submit tranzila token  value into the tranzilatoken field
						
					}
					return $tranzilatoken;
			}
	
			/**
			* Send transaction data from Caldera form to Tranzila and recieve confirmation code.
			*/
			public function donfo_transaction($data ,$token){
				
			 
				
				$field_id = 'fld_7259702'; //confirmation field
				$tranzilaconfirmation = '';
				$expdate = str_replace("/", "", $data['expiration_date']);
				$expdate = preg_replace('/\s+/', '', $expdate);
				// Prepare transaction parameters>
				$query_parameters['supplier']   = $this->supplier;
				$query_parameters['sum']        = $data['total_amount'];       //Transaction sum
				$query_parameters['currency']   = $data['currency'];           //Type of currency 1 NIS, 2 USD, 978 EUR, 826 GBP, 392 JPY
				$query_parameters['ccno']       = $data['credit_card_number']; // Test card number = '12312312'
				$query_parameters['expdate']    = $expdate ;


				    $query_parameters['expyear'] = $data['expiration_Year'] ;// Card expiry date: mmyy ='0820'
				 
					$query_parameters['mycvv'] = $data['mycvv'] ;// Card expiry date: mmyy ='0820'
				// Card expiry date: mmyy ='0820'
				if($data['currency'] == 1){
					$query_parameters['myid'] = $data['id_number']; // ID number = '12312312'
				 }
				 else{
					 $query_parameters['myid'] = '';
				 }
				 $query_parameters['myid'] = '';
				$query_parameters['cred_type']  = '1';                         // This field specifies the type of transaction, 1 - normal transaction, 6 - credit, 8 - payments
				$query_parameters['TranzilaPW'] = $this->tranzilapw;                 // Token password if required
				$query_parameters['tranmode']   = 'A';     
				// Mode for verify transaction
				//$query_parameters['TranzilaTK'] = $token;
				 
				
				$query_parameters['nom']   = $data['nom'];  
				$query_parameters['prenom']   = $data['prenom'] ;  
				$query_parameters['email']   = $data['email']  ;
				$query_parameters['phone']   = $data['phone'];  
				$query_parameters['ville']   = $data['ville'];  
				$query_parameters['remarques']   = $data['remarques'];   
				$query_parameters['Event']   = $data['event_name_tranzilla'];	
				$query_parameters['cat']   = $data['cat_tranzilla'];	
				$query_parameters['place']   = $data['place_tranzilla'];					
				 
				
				/* if ($data['frequency']!= 1){ if there are payments
					//multiple payments
					$query_parameters['fpay'] = $data['total_amount'];
					$query_parameters['spay'] = $data['total_amount'];
					$query_parameters['npay'] = 12;
				} */
				// Prepare query string
				$query_string = '';
				foreach ($query_parameters as $name => $value) {
					$query_string .= $name . '=' . $value . '&';
				}
				$query_string = substr($query_string, 0, -1); // Remove trailing '&'
				// Initiate CURL
				$cr = curl_init();
				/*
				
				
				*/
				 //print $this->tranzila_api_host.$this->tranzila_api_path.'?'.$query_string;exit;
				curl_setopt($cr, CURLOPT_URL,$this->tranzila_api_host.$this->tranzila_api_path);
				curl_setopt($cr, CURLOPT_POST, 1);
				curl_setopt($cr, CURLOPT_FAILONERROR, true);
				curl_setopt($cr, CURLOPT_POSTFIELDS, $query_string);
				curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 0);
				// Execute request
				 $result = curl_exec($cr);
				// print_r($result);exit;
				 //Need to remove later//
				      /*
						$abc=array();
						$abc[] = 'Response=000';
						$abc[] = 'mycvv=0';
						$abc[] = 'TranzilaTK=a8658bd932e83f64661';
						$abc[] = 'myid=01';
						$abc[] = 'cred_type=1';
						$abc[] = 'ccno=4661';
						$abc[] = 'expyear=18';
						$abc[] = 'DclickTK=';
						$abc[] = 'supplier=acarragrotok';
						$abc[] = 'expdate=0718';
						$abc[] = 'tranmode=A';
						$abc[] = 'sum=1';
						$abc[] = 'ConfirmationCode=0000000';
						$abc[] = 'Responsesource=0';
						$abc[] = 'Responsecvv=3';

						$abc[] = 'Responseid=3';
						$abc[] = 'Tempref=01850001';
						$abc[] = 'DBFIsForeign=1';
						$abc[] = 'DBFcard=2';
						$abc[] = 'cardtype=2';
						$abc[] = 'DBFcardtype=2';
						$abc[] = 'cardissuer=2';
						$abc[] = 'DBFsolek=6';
						$abc[] = 'cardaquirer=6';
						$abc[] = 'tz_parent=acarragrotok';

						$result =$abc;*/
				// End remove here //   
				$error = curl_error($cr);
				if (!empty($error)) {
					die ($error);
				}
				curl_close($cr);
				// Preparing associative array with response data
				// print_r($result);
			    $response_array = explode('&', $result); //print_r($response_array);
				
				 
				//Need to remove later//
				
					//$response_array = $result;
				  
				// End here // 
				 
				 //print_r($response_array);exit;
				$response_assoc = array();
				if (count($response_array) > 1) {
					foreach ($response_array as $value) {
						$tmp = explode('=', $value);
						if (count($tmp) > 1) {
							$response_assoc[$tmp[0]] = $tmp[1];
						}
					}
				}
				 
				// Analyze the result string
				if (!isset($response_assoc['Response'])) {
				    $this->errorPayment['token'] ='There is authentication issue!!';
		            $this->responsePayment['tranStatus']= '0';
					 
					/**
					 * When there is no 'Response' parameter it either means
					 * that some pre-transaction error happened (like authentication
					 * problems), in which case the result string will be in HTML format,
					 * explaining the error, or the request was made for generate token only
					 * (in this case the response string will contain only 'TranzilaTK'
					 * parameter)
					 */
				} else if ($response_assoc['Response'] !== '000') {
					$this->errorPayment['token'] ='Transaction get Failure due to bad creditcard information';
		            $this->responsePayment['tranStatus']= '0';
				 
					// Any other than '000' code means transaction failure
					// (bad card, expiry, etc..)
				}
				else {
					    $tranzilaconfirmation = $response_assoc['Tempref']; //ConfirmationCode 
						$this->responsePayment['tranStatus']= '1';  
						$this->responsePayment['transation_id']= $tranzilaconfirmation;  
					
				}
				 //print_r($this->responsePayment);exit;
			}    
			public function sendTicketInformationEmail($order_id,$userid){
				//PRINT($userid);
				$msgArra= array(); 
				$msgArra['invoice_number'] = $this->invoicecode['invoice_number'];
				$msgArra['reservation_number'] = $this->invoicecode['reservation_number'];
				$msgArra['customer_number'] =$this->invoicecode['customer_number']; 
				if($this->invoicecode['invoice_number']=='' && $this->invoicecode['reservation_number']=='' &&  $this->invoicecode['reservation_number']=='' ){
					$invoice = intval('18000');
					$invoice_id = $invoice+$order_id;
					$invoice_number='FA'.$invoice_id;
					$reservation_number='CA'.$invoice_id;
					$xxx = intval('74444');
					$xxx = intval($xxx)+$userid;
					$customer_number='CANO'.$xxx.'XX' ; 
					$msgArra['invoice_number'] = $invoice_number;
					$msgArra['reservation_number'] = $reservation_number;
					$msgArra['customer_number'] = $customer_number; 
				}
				$orderData = $this->getOrderData($order_id);
				 
				if(count($orderData) > 0){
						 foreach($orderData as $array_data){
										$msgArra['auditorium_name'] = $array_data['event_auditorium']; 
										$msgArra['auditorium_address'] = $array_data['event_auditorium_address'];
										$msgArra['auditorium_city'] = $array_data['event_city']; 
										$msgArra['productor_name'] = $array_data['productor_name'];   
										$msgArra['artist_name'] =  $array_data['artist_name'];  
										$msgArra['event_name'] = $array_data['event_name']; 
										$msgArra['bookedfee'] = $array_data['evtbookingfee']; 
										$mydate=strtotime($array_data['event_date']);
										$convertdate=date('Y-m-d',$mydate);
                                           $week_id  =  date('w', strtotime($convertdate));$week_id=$week_id+1;
										   $month_id =  date('m', strtotime($convertdate));
										   $day_id =  date('d', strtotime($convertdate));
										   $year=date('Y', strtotime($convertdate));
										$msgArra['event_date'] =strtoupper( $this->DaysLg($week_id).' '. $day_id.' '.$this->ChangeCalanderLg($month_id).' '.$year.'  À ');
										// $msgArra['event_date'] = $array_data['event_date']; 
										list($hours, $mint)=explode(":", $array_data['booking_time']);

										$msgArra['event_hour'] =strtoupper($hours.'h'.$mint);
										
										//$gethours=date('h',$array_data['booking_time']);
											

												
										//echo $array_data['event_date'];
										//exit;
										

										$msgArra['total_price'] = $array_data['total_amount'];


										$msgArra['number_of_order'] = $msgArra['reservation_number'];
										$msgArra['total_seats'] = $array_data['total_seats'];


									      

										 $userInfo = User::where('id',$userid)->first();
										 $userMeta = Usermeta::where('user_id',$userid)->first();  
										 $msgArra['online_version'] =WEB_PATH.'/acceder-ver/'.$order_id;
										  
										 $msgArra['client_nom'] =strtoupper($userMeta['first_name']).'  '.strtoupper($userInfo['name']);
										 $email = $userInfo['email'];
										 $msgArra['client_address'] = $userMeta['address_1'];
										 $msgArra['client_city'] = $userMeta['ville'];
										 $msgArra['client_code'] = $userMeta['ville'];
										 $msgArra['client_country'] = $userMeta['country'];
										 $msgArra['client_id'] = $msgArra['customer_number']; 
										 $msgArra['site_url'] = WEB_PATH;  
										 $ticketSeatArr = $array_data['ticket_info'];
										 $ticketType = $array_data['event_ticket_type'];
										 
										$msgArra['ticket_type'] =  $ticketType;  
										
									     // $ticketType = '3';
										 $eventSt='';
										 $sidecatbar='';
										 $pdfContent='';
										 //print_r($ticketSeatArr);exit;
										if(count($ticketSeatArr )){
												foreach($ticketSeatArr as $ticket){
													
													$msgArra['seat_sequence'] = $ticket['seat_sequence'];  
													$msgArra['seat_from'] = $ticket['seat_from']; 
													$msgArra['seat_to']   = $ticket['seat_to']; 
													$msgArra['free_placement']   = $ticket['free_placement']; 


													$orderSaved = Order::where('id', $order_id)->get();
													$totalSaved  = $orderSaved[0]->total_amount;

													$discountcalculated = ($array_data['total_amount'] - $totalSaved) / $msgArra['total_seats'] ;

													$msgArra['total_price'] = $ticket['price'] + $msgArra['bookedfee'] - $discountcalculated;

													//get type of coupons 
                                                    $coupon_history = CouponHistory::where('order_id',$order_id)->get();

                                                    if(count($coupon_history) > 0 ){
                                                        $coupon_id = $coupon_history[0]->coupon_id;
                                                        $couponObject = Coupon::where('id' , $coupon_id)->get();

                                                        if(count($couponObject) > 0 ) {
                                                            $discount_type = $couponObject[0]->discount_type;

                                                            if($discount_type == 'Double'){

                                                                $msgArra['total_price'] = array();

                                                                $counter = explode(',',$ticket['seat_sequence']);

                                                                $limit = $msgArra['total_seats'] - (intval($msgArra['total_seats'] / 2 ));
                                                                $iteratorcounter = 0;
                                                                foreach($counter as $c){
                                                                    $iteratorcounter++;

                                                                    if($iteratorcounter > $limit ){
                                                                        $msgArra['total_price'][] = 0;
                                                                    }else {
                                                                        $msgArra['total_price'][] = $ticket['price'] + $msgArra['bookedfee'] ;
                                                                    }

                                                                }

                                                                
                                                            }
                                                        }
                                                        
                                                    }

													if($ticketType == '1'|| $ticketType == '2'){  //For Free placement
													    $ticket['qrcode'] = $this->generateQRCode($userid, $msgArra);
													   // $ticket['qrcode'] = '';
                                                    }
													
													$pdfContent.=$this->getPDFContent($ticketType,$ticket,$msgArra);

													if(is_array($msgArra['total_price'])){
                                                        $pdfContent = preg_replace('/{total_price}/', $msgArra['total_price'][0],$pdfContent, $limit);                                  
                                                        $pdfContent = str_replace('{total_price}', $msgArra['total_price'][$limit+1], $pdfContent);
                                                    }
													
													 
												}
										  }
										 
										 
										   $subject ='Confirmation de votre commande CulturAccess';
										   //echo $pdfContent;exit;
										    sendEmailOrder('',$email,$subject,$msgArra, 'confirmation-inscription.html',$pdfContent);
										   $msgArra['order_value']= $msgArra['total_price'];
										   //$to =$this->data['admin_email'];
										   $to='zaoui.emmanuel@gmail.com';
										   $subject=$msgArra['client_nom'].' ont fait la commande de billet';
										  
										   sendEmail('',$to,$subject,$msgArra, 'admin_order_notification.html');
										    
						 }
				}
				 

				//confirm ticket functionality
											 
						  
		} 			
		public function getOrderData($order_id){
			
			
			$orderItemHtm='';
			$ordersArr=array();
		    if($order_id > 0){		
						/* Get order Items Detail here */
						
							$extraData=array();
							$bookingfees='1';
							$giftcard='1';
							
							 $orderEvents = OrderItems::select('product_id')->where('order_id',$order_id)->distinct()->get();
							  
							 if(count($orderEvents) > 0 ){
								$dataShop=array();
								$dataShop['type'] ='event';
								$total_ticket_price =0;
								 foreach($orderEvents  as $item){
									 
								    $event_id = $item['product_id'];
									if(intval($event_id)> 0){  
									 //Event Detail have Added Here //
										$eventArr = Event::where('id',$event_id)->get();
										 
										$event_list = getEventList($eventArr); 
										
										foreach($event_list  as $ev){
											
											$middleFormat = strtotime($ev['date']);  
												 
										    $dataShop['time_range']  =date('H:i', $middleFormat);
											//$dataShop['type'] ='event';
											$dataShop['order_id'] ='IXXI-'.$order_id;
											$dataShop['event_name'] = $ev['title'];
											$dataShop['artist_name'] = $ev['artist_name'];
											$dataShop['event_auditorium'] = $ev['event_auditorium'];
											$dataShop['event_auditorium_address'] = $ev['event_auditorium_address'];
											$dataShop['event_ticket_type'] = $ev['event_ticket_type'];
											$dataShop['productor_name'] = $ev['productor_name'];
											$dataShop['artist_name'] = $ev['artist_name'];
											$dataShop['event_city'] = $ev['event_city'];  	
											$dataShop['event_group_picture'] = EVENTGROUP_WEB_PATH.'/'.$ev['event_group_picture'];  
											$dataShop['event_date'] =  $ev['date_Y'].'-'.$ev['date_m'].'-'.$ev['date_d'];  
											$dataShop['event_date_com'] =  date('Y-m-d', $middleFormat); 
											$dataShop['evtbookingfee'] = $ev['booking_fee'];
										}
										 
										/*=========Add Ticket row, Price, others ==========*/
										 
												$rowsItems = OrderItems::where('order_id',$order_id)->where('product_id', $event_id)->get();
												//print_r($rowsItems);exit;
												 $totalQuantity =0;
												 if(count($rowsItems) > 0 ){ 
												  $rowArr=array();
													foreach($rowsItems  as $item){
													
														$rowArr['ticket_row']=$item['ticket_row'];
														$qtx =$item['quantity'];;
														$price =$item['price'];;
														$rowArr['quantity']=$item['quantity'];
														$totalQuantity += intval($item['quantity']);
														$rowArr['price']=$item['price'];
														$rowArr['ticket_row']=$item['ticket_row'];
														$rowArr['ticket_category'] = $item['ticket_category']; 
														
														
														$rowArr['seat_sequence'] = $item['seat_sequence']; 
														$rowArr['seat_from'] = $item['seat_from'];
														$rowArr['seat_to'] = $item['seat_to'];
														$rowArr['free_placement'] = $item['free_placement'];
														
														
														
														
														$dataShop['booking_time'] = $item['booking_time'];
														
														$totalA = $qtx*$price;
													 
														$total_ticket_price =$total_ticket_price +$totalA;
														 
														$dataShop['ticket_info'][] =$rowArr;
														
													}
												 }
												 $dataShop['total_seats'] = $totalQuantity;
										
										/* ===========End here ===========================*/
										 	
									}	
										
								 } 	 
							 }
							 /*=========Fetch Other Item Price ==========*/
										$rowsItems = OrderItems::where('order_id',$order_id)->where('product_id','0')->get();
										if(count($rowsItems) > 0 ){ 
										  $rowArr=array();
											foreach($rowsItems  as $item){
												
											 
												$qtx =$item['quantity'];;
												$price =$item['price'];;
												 
												$totalA = $qtx*$price;
												
												$total_ticket_price =$total_ticket_price +$totalA;
												 
												 
												
											}
										 }
										 $dataShop['total_amount'] = $total_ticket_price;
							  /*================End here =======================*/
							 
							  
						  /* End here       */
						  
						$ordersArr[] = $dataShop;
				 }
				 
		  return $ordersArr;
			
		}
	public function getPDFContent($ticketType,$ticket,$msgArr){
						$sellingtype=$_GET['selling_type'];
							$pdfContent ='';
						$msgArr['web_root_path'] = "pdf";
						
							$commaSeperatedSeats = explode(',', $ticket['seat_sequence']);
							$free_placement = $ticket['free_placement'];
							
							if(count($commaSeperatedSeats)> 0 ){
								foreach($commaSeperatedSeats as $seat_display_number){	

													$sidecatbar  ='<tr>
																	<td colspan="2" style="font-size: 12px; color: #313132; padding: 11px 0 ;font-family: \'Roboto Condensed\', sans-serif;">
																		<p style="margin:0;">'.$ticket['ticket_category'].' </p>
																		<p style="margin:0;">Rang ';
																				if($free_placement == '1'){
																				    $sidecatbar  .=	 'Libre'; 
																					$sidecatbar  .=	' place '; 					
																					$sidecatbar  .=	 'Libre'; 
																				}else{
																					$sidecatbar  .=	 $ticket['ticket_row']; 
																					$sidecatbar  .=	' place '; 					
																					$sidecatbar  .=	 $seat_display_number;
																				}					
													
													$sidecatbar  .=	' </p>  
																	</td>
																</tr>';								
									if($ticketType == '1'|| $ticketType == '2'){  //For Free placement
									
												  $eventSt ='';
												  $seatPlace='';
													$eventSt =' <tr>
																	<td style="padding-left: 17px; font-family: \'Roboto Condensed\', sans-serif;  width: 30%" width="30%">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0" border="0">
																			<tbody><tr>
																				<td style="font-size: 10px; text-align: center; font-family: \'Roboto Condensed\', sans-serif; padding-bottom: 10px;">EMPLACEMENT</td>
																			</tr>
																			<tr>
																				<td style="padding: 0px 0 ; text-align: center; font-family: \'Roboto Condensed\', sans-serif;">
																				<table border="0" cellpadding="0" cellspacing="0" style="border: solid 2px #313132" width="100%"> <tr><td align="center" style="padding: 5px 0 10px;">';
																					$eventSt .= $ticket['ticket_category'];
																					
												$eventSt .= 					'</td></tr></table></td>
																			</tr>
																		</tbody></table>
																	</td>
																		<td style="padding-left: 20px; font-family:  \'Roboto Condensed\', sans-serif;" width="20%">
																			<table width="100%"  border="0" cellspacing="0" cellpadding="0">
																				<tbody><tr>
																					<td style="font-size: 10px; text-align: center; font-family:  \'Roboto Condensed\', sans-serif; padding-bottom: 10px;">RANG</td>
																				</tr>
																				<tr>
																					<td style="padding: 0px 0; text-align: center; font-family:  \'Roboto Condensed\', sans-serif;  width: 20%">
																						<table border="0" cellpadding="0" cellspacing="0" style="border: solid 2px #313132" width="100%"> <tr><td align="center" style="padding: 5px 0 10px;">';
																							 
																							if($free_placement == '1'){
																								$eventSt .= 'Libre';
																							}else{
																								$eventSt .= $ticket['ticket_row'];
																							}
																						
													$eventSt .= 					'</td></tr></table></td>
																				</tr>
																			</tbody></table>
																		</td>
																		<td style="padding-left: 25px; font-family:  \'Roboto Condensed\', sans-serif; width: 20%" width="20%">
																		
																			<table width="100%" cellspacing="0" cellpadding="0"  border="0" style="border-collapse: collapse">
																				<tbody><tr>
																					<td border="0" style="font-size: 10px; text-align: center; font-family:  \'Roboto Condensed\', sans-serif; border: none; padding-bottom: 10px;">PLACE</td>
																				</tr>
																				<tr>
																					<td>
																						
																							<table width="100%" cellspacing="0" cellpadding="0" border="0" style="border: solid 2px #313132">
																				<tr><td style="padding: 5px 0 10px; text-align: center; font-family:  \'Roboto Condensed\', sans-serif;"  >';
																				 
																				if($free_placement == '1'){
																								$eventSt .= 'Libre';
																							}else{
																								$eventSt .= $seat_display_number;
																							}
																				$eventSt .= '</td></tr></table>
																						
																					</td>
																				</tr>
																			</tbody></table>
																		</td>
																		<td width="20%">
																			<table width="100%" cellspacing="0" cellpadding="0"  border="0">
																			
																				<tr><td style="padding:10px;"><img src="'.$ticket['qrcode'].'" style="width:50px;" alt="barcode" /></td></tr>
																				
																			</table>
																		</td>
																	</tr>';
																	if($sellingtype==2)
																	{
																		$message =  file_get_contents(ROOT_PATH.'/pdf/freee-ticket.html');  //E-Ticket 
																	}
																	else
																	{
																		$message =  file_get_contents(ROOT_PATH.'/pdf/e-ticket.html');  //E-Ticket 
																	}
											
											
											 
									} else {
										$eventSt ='';
														  $eventSt.='<tr>
																			<td style="padding-left: 17px; font-family: \'Roboto Condensed\', sans-serif;" width="33%">
																				<table width="100%" cellspacing="0" cellpadding="0">
																					<tbody><tr>
																						<td style="font-size: 10px; text-align: center; font-family:\'Roboto Condensed\', sans-serif;padding-bottom:5px;">EMPLACEMENT</td>
																					</tr>
																					<tr>
																						<td style="padding: 5px 0; text-align: center; font-family:\'Roboto Condensed\', sans-serif; border: 2px solid #313132;text-transform:uppercase;font-size:14px;">
																							
																								'.$ticket['ticket_category'].'
																							
																						</td>
																					</tr>
																				</tbody></table>
																			</td>
																			<td style="padding-left: 30px; font-family: \'Roboto Condensed\', sans-serif;" width="33%">
																				<table width="100%" cellspacing="0" cellpadding="0">
																					<tbody><tr>
																						<td style="font-size: 10px; text-align: center; font-family: \'Roboto Condensed\', sans-serif;padding-bottom:5px;">RANG</td>
																					</tr>
																					<tr>
																						<td style="padding: 5px 0; text-align: center; font-family: \'Roboto Condensed\', sans-serif; border: 2px solid #313132;">
																							
																								'.$ticket['ticket_row'].'
																						
																						</td>
																					</tr>
																				</tbody></table>
																			</td>
																			<td style="padding-left: 35px; font-family: \'Roboto Condensed\', sans-serif;" width="34%">
																				<table width="100%" cellspacing="0" cellpadding="0">
																					<tbody><tr>
																						<td style="font-size: 10px; text-align: center; font-family: \'Roboto Condensed\', sans-serif;padding-bottom:5px;">PLACE</td>
																					</tr>
																					<tr>
																			<td style="padding: 5px 0;  text-align: center; font-family: \'Roboto Condensed\', sans-serif; border: 2px solid #313132;"> 
																								'.$seat_display_number.' 
																						</td>
																					</tr>
																				</tbody></table>
																			</td>
																		</tr>';
											 						 if($sellingtype==2)
																	{
																		$message =  file_get_contents(ROOT_PATH.'/pdf/freecountermark-ticket.html');  //E-Ticket 
																	}
																	else
																	{
																		$message =  file_get_contents(ROOT_PATH.'/pdf/countermark-ticket.html');  //E-Ticket 
																	}
										}
									
											$msgArr['seat_management_sidebar'] = $sidecatbar;
											$msgArr['seat_management'] = $eventSt;
																	
											//$msgArr['ticket_price'] = $ticket['price']+$msgArr['bookedfee'];

											$msgArr['ticket_price'] = $msgArr['total_price'] ;

											foreach($msgArr as $key => $value){

                                                if(!is_array($value)){
												    $message = str_replace('{'.$key.'}', $value, $message);
                                                }
											}
											
											$pdfContent.=$message;
								}
							}  
											
							//print_r($pdfContent);exit;						 
					return $pdfContent;							
										
			 }
	public function sendTicketInformationByAdmin($request, $response){ 
			  
		    $order_id=$request->getAttribute('order'); //fetch the category id
			$userid=$request->getAttribute('uid'); //fetch the category id
                 
		
			if (intval($order_id) > 0)
			{      $this->sendTicketInformationEmail($order_id,$userid);
					 
					$jsonData = array(
						'status' => '1',
						'msg' => 'Ticket information sent successfully!!'
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
			} else {
					$jsonData = array(
						'status' => '0',
						'msg' => 'Error in sending ticket information'
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
			}
	}

	public function ValidateCoupon($request,$response)
	{
		$promocode=$request->getParam('promocode');
		$userid=$request->getParam('userid');
		//echo $userid;exit;

		$currentTime=date('Y-m-d');
			$coupon_detail=Coupon::where('coupon_code',$promocode)
			->where('expiration_date','>=',$currentTime)
			->first();
				$customer_id =$userid;		
			$coupon_history=CouponHistory::where('coupon_id',$coupon_detail['id'])->where('customer_id',$customer_id)->get();
		
				if(intval($coupon_detail['id']) >0 )
				{
						
					
						if(count($coupon_history) >0)
						{
							$jsonData = array('status' => '0' , 'msg' =>'Coupon is already used');
							
						}
						else{
						
								$data=array();
								if($coupon_detail['discount_type']=='Fixed')
								{
										$discount_amount=$coupon_detail['discount_amount'];
								}
								elseif($coupon_detail['discount_type'] == 'PerTicket')
								{
										$discount_amount=$coupon_detail['discount_amount'];
								}
								elseif($coupon_detail['discount_type'] == 'Double')
								{
										$discount_amount = 0;
								}
								else
								{
										$discount_amount=$coupon_detail['discount_amount'];
								}
										$data=[
										'promocode'=>$coupon_detail['coupon_code'],
										'discount_type'=>$coupon_detail['discount_type'],
										'discount_amount'=>$discount_amount,
										];
								$jsonData = array(
													  'status' => '1',
													  'promocode'=> $coupon_detail['coupon_code'], 
													  'coupon_id'=> $coupon_detail['id'], 
													  'discount_type'=>$coupon_detail['discount_type'],
													  'discount_amount'=>$discount_amount  
												 );
												// $_SESSION['coupon_detail']= $jsonData;
												 $_SESSION['cart_extra']['coupon_detail']= $jsonData; // Coupon Detail

						}
				
				}
				else
				{
						$jsonData = array('status' => '0',  'msg' =>'Attention, le code promo saisi est invalide.');
				}
				
				return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData)); exit();	
	
	}	
	
	
	public function getTotalPriceSequence($priceSequence){
		
			$data=array();
				$seatSquenceArr = explode(',', $priceSequence);
				$totalAmt=0;
				$seathtml='Pricing<br />';
				//Get Seat Number from here //
				if(count($seatSquenceArr) > 0 ){
					  $startindex= '0'; 
					  $endIndex =count($seatSquenceArr)-1;
						
						for( $i = $startindex; $i<= $endIndex; $i++ ){ 
						 $j=$i+1;
						       $seathtml .= 'Seat '.$j.': '.$seatSquenceArr[$i].'  ₪<br />';
								$totalAmt+= $seatSquenceArr[$i];
						} 	
						 
				} 
				$data['totalAmt'] = $totalAmt;
				$data['seathtml'] = '	<span class="field-tip">
											 <svg class="svg-inline--fa fa-question-circle fa-w-16" aria-hidden="true" data-prefix="fas" data-icon="question-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"></path></svg>
											<span class="tip-content">'.$seathtml.'</span>
										</span> ';
				return $data;
		}

	public function generateQRCode($userid, $msgArra){


                $invoicenumber = $msgArra['invoice_number'];
				$client_nom = $msgArra['client_nom'];
				$price = $msgArra['total_price'];
				$event = $msgArra['event_name'];
				$order_id = $msgArra['order_id'];

				$text = $invoicenumber.','.$userid.','.$client_nom.','.$price.' IPN,'.$event.','.$order_id;


				//FA18378,1248,PARTIS JULIEN,55,ÃvÃ©nement de test,56


                $url = "http://qrcode.culturaccess.com/index.php";

                $c  = curl_init();
                curl_setopt_array($c, array(
				    CURLOPT_RETURNTRANSFER => 1,
				    CURLOPT_URL => $url,
				    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
					CURLOPT_POSTFIELDS => array(
					        'encode' => true,
					        'file' => $invoicenumber,
					        'content' => $text
					)
				));

                $return = curl_exec($c);

                curl_close($c);
                
				return $return;
	}
	
	public function checkemail($request,$response)
	{
		//$emailid=$request->getParam('email');
		$emailid=$request->getParam('email');
		$emailexist=User::select('email')->where('email',$emailid)->first();
		
		if(count($emailexist)>0)
		{
			$jsonData = array(
						'status' => '0',
						'msg' => "L'email existe déjà !"
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
		}
		else{
			$jsonData = array(
						'status' => '1',
						'msg' => "Email n'existe pas !!"
					);
					return $response->withHeader('Content-type', 'application/json')->write(json_encode($jsonData));
					exit;
		}
		//print_r($emailid);exit;
	}

}
