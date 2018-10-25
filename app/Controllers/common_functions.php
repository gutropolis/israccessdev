<?php 
inclued("mailjet.php");
use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;

use Dompdf\Dompdf;

// show error message

function showErrorMessage($msg){

	return '<div class="alert alert-danger"><strong>Error!</strong> '.$msg.'</div>'.hideMessage('.alert alert-danger');

}

// Show success message

function showSuccessMessage($msg){

	  return '<div class="alert alert-success"><strong>Success!</strong> '.$msg.'</div>';

}

// Check email if valid

function isValidEmail($email){

  return (filter_var($email, FILTER_VALIDATE_EMAIL)) ? true : false;

}

// fuction success response

function sResponse($message=''){

  return json_encode(array('status' => true, 'message' => showSuccessMessage($message)));	

}

// fuction error response

function eResponse($message=''){

  return json_encode(array('status' => false, 'message' => showErrorMessage($message)));	

}

//Check if login

function isAdminLogin(){

  return (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 'Okay') ?  true : false;

}

function isMemberLogin(){

  return (isset($_SESSION['isMember']) && $_SESSION['isMember'] == 'Okay') ?  true : false;

}

function isProductorLogin(){

  return (isset($_SESSION['isProductor']) && $_SESSION['isProductor'] == 'Okay') ?  true : false;

}

function isOperatorLogin(){

  return (isset($_SESSION['isOperator']) && $_SESSION['isOperator'] == 'Okay') ?  true : false;

}

// hide message 

function hideMessage($class){

  return '<script>$("'.$class.'").delay(5000).fadeOut("slow");</script>';	

}

// Dump array

function ddump($array){

  echo '<pre>';

  print_r($array);

  echo '<pre>';	

}

// Allowed list of extensions

function allowedExtensions(){

 return array("jpeg", "JPEG", "jpg", "JPG", "png", "PNG", "gif", "GIF", "bimp", "BIMP");

}



// Resize Image

if ( ! function_exists('smart_resize_image')) :

 

    /**

 * easy image resize function

 * @param  $file - file name to resize

 * @param  $string - The image data, as a string

 * @param  $width - new image width

 * @param  $height - new image height

 * @param  $proportional - keep image proportional, default is no

 * @param  $output - name of the new file (include path if needed)

 * @param  $delete_original - if true the original image will be deleted

 * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink

 * @param  $quality - enter 1-100 (100 is best quality) default is 100

 * @param  $grayscale - if true, image will be grayscale (default is false)

 * @return boolean|resource

 */

  function smart_resize_image($file,

                              $string             = null,

                              $width              = 0, 

                              $height             = 0, 

                              $proportional       = false, 

                              $output             = 'file', 

                              $delete_original    = false, 

                              $use_linux_commands = false,

                              $quality            = 100,

                              $grayscale          = false

  		 ) {

      

    if ( $height <= 0 && $width <= 0 ) return false;

    if ( $file === null && $string === null ) return false;



    # Setting defaults and meta

    $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);

    $image                        = '';

    $final_width                  = 0;

    $final_height                 = 0;

    list($width_old, $height_old) = $info;

	$cropHeight = $cropWidth = 0;



    # Calculating proportionality

    if ($proportional) {

      if      ($width  == 0)  $factor = $height/$height_old;

      elseif  ($height == 0)  $factor = $width/$width_old;

      else                    $factor = min( $width / $width_old, $height / $height_old );



      $final_width  = round( $width_old * $factor );

      $final_height = round( $height_old * $factor );

    }

    else {

      $final_width = ( $width <= 0 ) ? $width_old : $width;

      $final_height = ( $height <= 0 ) ? $height_old : $height;

	  $widthX = $width_old / $width;

	  $heightX = $height_old / $height;

	  

	  $x = min($widthX, $heightX);

	  $cropWidth = ($width_old - $width * $x) / 2;

	  $cropHeight = ($height_old - $height * $x) / 2;

    }



    # Loading image to memory according to type

    switch ( $info[2] ) {

      case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;

      case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;

      case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;

      default: return false;

    }

    

    # Making the image grayscale, if needed

    if ($grayscale) {

      imagefilter($image, IMG_FILTER_GRAYSCALE);

    }    

    

    # This is the resizing/resampling/transparency-preserving magic

    $image_resized = imagecreatetruecolor( $final_width, $final_height );

    if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {

      $transparency = imagecolortransparent($image);

      $palletsize = imagecolorstotal($image);



      if ($transparency >= 0 && $transparency < $palletsize) {

        $transparent_color  = imagecolorsforindex($image, $transparency);

        $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);

        imagefill($image_resized, 0, 0, $transparency);

        imagecolortransparent($image_resized, $transparency);

      }

      elseif ($info[2] == IMAGETYPE_PNG) {

        imagealphablending($image_resized, false);

        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);

        imagefill($image_resized, 0, 0, $color);

        imagesavealpha($image_resized, true);

      }

    }

    imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);

	

	

    # Taking care of original, if needed

    if ( $delete_original ) {

      if ( $use_linux_commands ) exec('rm '.$file);

      else @unlink($file);

    }



    # Preparing a method of providing result

    switch ( strtolower($output) ) {

      case 'browser':

        $mime = image_type_to_mime_type($info[2]);

        header("Content-type: $mime");

        $output = NULL;

      break;

      case 'file':

        $output = $file;

      break;

      case 'return':

        return $image_resized;

      break;

      default:

      break;

    }

    

    # Writing image according to type to the output destination and image quality

    switch ( $info[2] ) {

      case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;

      case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;

      case IMAGETYPE_PNG:

        $quality = 9 - (int)((0.9*$quality)/10.0);

        imagepng($image_resized, $output, $quality);

        break;

      default: 

	  return false;

    }



    return true;

  }

endif;





// Mysql Date Formate

if ( ! function_exists('mysql_date')) :

   function mysql_date($form_date){

	   if(!isset($form_date) && empty($form_date)){

		  return '';   

	   }else{

	      $form_date   = explode('/', $form_date); 

	      return $form_date[2].'-'.$form_date[1].'-'.$form_date[0];

	   }

   }

endif;





// Human Readable Date Formate

if ( ! function_exists('hr_date')) :

   function hr_date($mysql_date){

	   if(!isset($mysql_date) && empty($mysql_date)){

		  return '';   

	   }else{

	      return date('d/m/Y', strtotime($mysql_date));

	   }

   }

endif;



// Get state and zip code 

if( !function_exists('get_state_zip') ):

function get_state_zip($address){

	$address = urlencode($address);

$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=".GOOGLE_MAP_API_KEY;

$ch = curl_init();

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_URL,$url);

$result=curl_exec($ch);

curl_close($ch);

$data = json_decode($result,true);

print_r($data);

$components = $data["results"][0]["address_components"];



// filter the address_components field for type : $type

function filter($components, $type)

{

    return array_filter($components, function($component) use ($type) {

        return array_filter($component["types"], function($data) use ($type) {

            return $data == $type;

        });

    });

}

$zipcode = array_values(filter($components, "postal_code"))[0]["long_name"];

$citystate = array_values(filter($components, "administrative_area_level_1"))[0]["long_name"];

return array('zipCode' => $zipcode, 'stateName' => $citystate );

}

endif;



// Human Readable Date Formate

if ( ! function_exists('getEventGroupList')) :

   function getEventGroupList($event_list){

		 $dataSlider = array();

		 $auditorium='';

		 if(count($event_list) > 0 ){

				 foreach($event_list as $get) {

					 $array_data = array();

							$array_data['event_group_id']  = $get['id'];

							$array_data['event_group_status']=$get['status'];

							$array_data['event_group_slug']=$get['event_group_slug'];

							//$categoryid=$get['category_id'];



							$eventgrpcatid=App\Models\Category::where('id',$get['category_id'])->first();

					        $array_data['category_slug']=$eventgrpcatid['slug'];







					   /** Add date from and to for event group b event calendar **/



				            $fromToEventDate='';

						

						  $eventgroupid = $get['id'];

				            //->where('date', '>', $currentD)

				           $currentD= date('Y-m-d');

				           //->where('date', '>', $currentD)

						   /* Fetch All Events here  */

						   	 //Get All Event Ids from EventGroup Ids

								$event_id_Array=array();

								$event_id_by_eventgroup=App\Models\Event::select('id')->where('eventgroup_id',$eventgroupid)->where('status','1')->get(); 

								if( count($event_id_by_eventgroup) > 0){

										foreach($event_id_by_eventgroup as $eventid){

											$event_id_Array[]=$eventid['id'];

										}

								}

								$child_event_ids = App\Models\EventGroupChildren::select('events_id')->where('events_group_id',$eventgroupid)->get();;

									if(count($child_event_ids) > 0){

										foreach($child_event_ids as $eventid){

											$event_id_Array[]=$eventid['events_id'];

										}

									}

								 $event_id_Array = array_unique($event_id_Array);

								 $eventDArr =  App\Models\Event::wherein('id',$event_id_Array)->where('status',1)->orderBy('date', 'asc')->get();

								 

						    /* End Here  */

				            // $query = App\Models\Event::where('eventgroup_id',$get['id'])->orderBy('date', 'asc');

							// $eventDArr = $query->get();

				            $city_name='';

							$TotalEvents = count($eventDArr);

				            if(count($eventDArr) > 0 ){

				            	$totE = count($eventDArr);



				            	if($totE > 1){

				            		 $fromToEventDate='Du '.date('d/m/', strtotime($eventDArr[0]['date'])).'&nbsp;&nbsp;au '.date('d/m/Y', strtotime($eventDArr[$totE-1]['date'])) ; 

                                     

				            	}else{

									$fromToEventDate=' Le '.date('d/m/Y', strtotime($eventDArr[0]['date']));  

									 

									 

									$cityArr = $eventDArr[0]['city'];  

									 

									$city_name= $cityArr['name'];//echo $city_name;exit;

				            	}

				            	 



				            }

 

                            //$eventEventCityArr=$get['city'] ; 

							//$array_data['event_city']  = $eventEventCityArr['name'];

							

							$array_data['group_picture']  = $get['group_picture'];

							$array_data['event_group_picture']  = $get['group_picture'];

							$array_data['event_group_title']  = html_entity_decode($get['title']);

							$array_data['event_group_price_min']  = intval($get['price_min']);

							$array_data['event_group_description']  = $get['description'];

							$array_data['event_group_cardtitle']  = $get['thumbnail_title'];



							$array_data['event_artist_id']  = $get['artist_id'];



							$eventEventAuditoriumArr=$get['events'] ;

							$dataEvents=$get['events'] ;

							$city='';

							foreach( $eventEventAuditoriumArr as $ev){

							$auditorium=  $ev['auditorium']['name'].",";

							}

							$array_data['event_group_auditorium']  =  $auditorium;

							$array_data['event_group_total_events']  =  $TotalEvents;

							$array_data['event_group_cityname']  =  $city_name;

  

							$eventCategoryArr=$get['category'];

							$eventArtistArr=$get['artist'];

							$array_data['event_category_title']  =  $eventCategoryArr['name'];

							$array_data['event_category_id']  =  $eventCategoryArr['id'];

							$array_data['event_artist_name']  =  $eventArtistArr['name'];

							$array_data['event_artist_pic']  =  $eventArtistArr['user_picture'];

							$array_data['event_group_begin']  = hr_date($get['date_begin']);

							$array_data['event_group_end']  = hr_date($get['date_end']);

							$array_data['event_f_t'] = $fromToEventDate;

							$dataSlider[] = $array_data;

				 }

		 }

		 return $dataSlider;

   }

endif;

// checkRating

if ( ! function_exists('checkRating')) : 

   function checkRating($rating){

	   $ratediv='';

	   // far fa-star blank star

			if($rating	== '1'){

               $ratediv .= '<li><i class="fas fa-star"></i></li>

							<li><i class="far fa-star"></i></li>

							<li><i class="far fa-star"></i></li>

							<li><i class="far fa-star"></i></li>

							<li><i class="far fa-star"></i></li>'; 

			}else if($rating	== '2'){

				$ratediv .= '<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="far fa-star"></i></li>

							<li><i class="far fa-star"></i></li>

							<li><i class="far fa-star"></i></li>'; 

			}else if($rating	== '3'){

				$ratediv .= '<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="far fa-star"></i></li>

							<li><i class="far fa-star"></i></li>'; 

			}else if($rating	== '4'){

				$ratediv .= '<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="far fa-star"></i></li>'; 

			}else if($rating	== '5'){

				$ratediv .= '<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>'; 

			}else{

				$ratediv .= '<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>

							<li><i class="fas fa-star"></i></li>'; 

			}

			return $ratediv;

   }

endif;

// Human Readable Date Formate

if ( ! function_exists('getCommentList')) :

   function getCommentList($commentList){

		 $dataSlider = array();

		 

		 if(count($commentList) > 0 ){

				 foreach($commentList as $get) {

							$array_data = array();

							$array_data['title']  = $get['title'];

							$totalrating  = $get['ratings'];

							$ratingDiv = checkRating($totalrating);

							$array_data['comments']  = $get['comments'];

							$array_data['signature']  = $get['signature'];

							$array_data['star_rating']  = $ratingDiv;

							 

							$dataSlider[] = $array_data;

				 }

		 }

		 return $dataSlider;

   }

endif;

// Human Readable Date Formate

if ( ! function_exists('getVideoList')) :

   function getVideoList($vidList){

		 $dataSlider = array();

		 

		 if(count($vidList) > 0 ){

				 foreach($vidList as $get) {

							$array_data = array();

							$array_data['file_name']  = $get['file_name'];

							$file_type  = $get['file_type'];

							$videoDisplay = '';

						    if($flie_type=='vid'){

								

							}

							 

							$dataSlider[] = $array_data;

				 }

		 }

		 return $dataSlider;

   }

endif;



//Function for readmore

function shortDescription($desc, $total){

	

	 $desc = stripUnwantedTagsAndAttrs($desc);

	return  substr($desc, 0, $total) .((strlen($desc) > 200) ? '...' : ''); 

}



// Human Readable Date Formate

if ( ! function_exists('getEventList')) :

   function getEventList($event_list){

		 $dataSlider = array();

		 $auditorium='';

	 

		 if(count($event_list) > 0 ){

				 foreach($event_list as $get) {

						$array_data = array();

						$array_data['title']  = $get['title'];

						$array_data['eventgroup_id']  = $get['eventgroup_id'];

						$array_data['event_ticket_type']  = $get['event_ticket_type'];

						$array_data['id']  = $get['id'];

						$middleFormat = strtotime($get['date']);  

						$dateDisplayFormat= date('D j F', $middleFormat);

						$array_data['date']  =$dateDisplayFormat;							

						$array_data['date_m']  =date('m', $middleFormat);							

						$array_data['date_d']  =date('d', $middleFormat);						    

						$array_data['timeH']  =date('H', $middleFormat);							

						$array_data['timeI']  =date('i', $middleFormat);

						$array_data['date_D']  =date('D', $middleFormat);							

						$array_data['date_j']  =date('j', $middleFormat);							

						$array_data['date_F']  =date('F', $middleFormat);							

						$array_data['date_Y']  =date('Y', $middleFormat);							 

						$eventEventCityArr=$get['city'] ;



						$eventPictureArr=$get['picture'] ;

						$eventPicture= $eventPictureArr[0]['event_img'];

						$array_data['event_picture']  = $eventPicture;





						$eventEventCityArr=$get['city'] ; 

						$array_data['event_city']  = $eventEventCityArr['name'];



						$eventEventGroupArr=$get['eventgroup'] ; 

						$array_data['event_group_title']  = html_entity_decode($eventEventGroupArr['title']);

						$array_data['event_group_picture']  = $eventEventGroupArr['group_picture'];





						$eventEventAuditoriumArr=$get['auditorium'] ; 

						$array_data['event_auditorium']  = $eventEventAuditoriumArr['name'];							$array_data['event_auditorium_address']  = $eventEventAuditoriumArr['address'];



						$array_data['author']  = $get['author'];

						$array_data['description']  = $get['description'];



						$array_data['artist_name']  = $get['artist_name'];

						$array_data['author_name']  = $get['author_name'];

						$array_data['productor_name']  = $get['productor_name'];

						$array_data['director_name']  = $get['director_name'];



						$array_data['contributor_name']  = $get['contributor_name'];

						$array_data['contributor_description']  = $get['contributor_description'];

						$array_data['short_description']  = shortDescription($get['contributor_description'], '150');

						$array_data['booking_fee']  = $get['booking_fee'];

						$array_data['adv_image']  = $get['adv_image'];



						$dataSlider[] = $array_data;

				 }

		 }

		 

		 return $dataSlider;

   }

endif;



//function for stirope all html tags

function stripUnwantedTagsAndAttrs($text){

  

	   $text = preg_replace(

        array(

          // Remove invisible content

            '@<head[^>]*?>.*?</head>@siu',

            '@<style[^>]*?>.*?</style>@siu',

            '@<script[^>]*?.*?</script>@siu',

            '@<object[^>]*?.*?</object>@siu',

            '@<embed[^>]*?.*?</embed>@siu',

            '@<applet[^>]*?.*?</applet>@siu',

            '@<noframes[^>]*?.*?</noframes>@siu',

            '@<noscript[^>]*?.*?</noscript>@siu',

            '@<noembed[^>]*?.*?</noembed>@siu',

          // Add line breaks before and after blocks

            '@</?((address)|(blockquote)|(center)|(del))@iu',

            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',

            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',

            '@</?((table)|(th)|(td)|(caption))@iu',

            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',

            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',

            '@</?((frameset)|(frame)|(iframe))@iu',

        ),

        array(

            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',

            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",

            "\n\$0", "\n\$0",

        ),

        $text );

    return strip_tags( $text );

  

}



// Human Readable Date Formate

if ( ! function_exists('getCmsList')) :

   function getCmsList($cmsid){

	    $desc= '';

		// $query->where('id',$cmsid); 

         // $cmsr = $query->first();	

		 $cmsr = App\Models\Cms::where('id',$cmsid)->first();		 

		 $desc= $cmsr['description'];	 

		 return $desc;

   }

endif;



if ( ! function_exists('clearString')) :

   function clearString($string){

       $pattern = "=^<p>(.*)</p>$=i";

	   return preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', $string);

   }

endif;



// Mysql Time

if ( ! function_exists('mysqlTime')) :

   function mysqlTime($time){

       return date('H:i:s', strtotime($time));

   }

endif;



// Human Readable Time

if ( ! function_exists('hrTime')) :

   function hrTime($time){

	   return date('H:i', strtotime($time)); 

   }

endif;





function sendEmailOrder($from='',$to,$subject,$msgArr, $emailtemplate,$pdfcontent){

	 // print $pdfcontent;exit;

	 $htmlcontent='

		<!doctype html>

			<html lang="en">

			   <head></head>			  

			   <body>';

	$htmlcontent.=$pdfcontent;	   

	$htmlcontent.='</body></html>';

	 

	  $message='';

         

				

				  $guser = 'contact@culturaccess.com'; // username

				 $gpass = 'Coaccess2019'; //password

				 $mail = new PHPMailer(true);

				 if($from==''){

					  $mail->From = "contact@culturaccess.com";

				 }else{

					  $mail->From = $from;

				 }

				  $mail->From = "contact@culturaccess.com";

				/*SMTP Server Setting Here */

						$mail->SMTPDebug = 0; //Alternative to above constant

						$mail->Debugoutput = 'html';

						// SMTP configuration

						$mail->isSMTP();

						

						 

						$mail->SMTPAuth = true;

						$mail->Username = $guser;

						$mail->Password = $gpass;

						//$mail->SMTPSecure = 'ssl';

						$mail->Host = 'pro1.mail.ovh.net';

						$mail->Port = '587'; 

						$mail->IsHTML(true);

					   // $mail->SMTPSecure = "ssl";

						 

				       $mail->CharSet = 'UTF-8';

				

				/* End SMTP Server Setting Here */

				

				

				 $mail->FromName = "CulturAccess";



				$mail->addAddress($to, "");



				 

				 

				$message =  file_get_contents(ROOT_PATH.'/emailtemplate/'.$emailtemplate);  //Your Template



					foreach($msgArr as $key => $value){

						$message = str_replace('{'.$key.'}', $value, $message);

					}

					

					

					

					

					

			    //Attach .PDF Function Start Here //

					if((isset($_SESSION['memberId'])) || (isset($_SESSION['adminId'])))

					{

							 

							$html =$htmlcontent;

							$dompdf = new Dompdf();

							$dompdf->load_html($html);

							$dompdf->set_option('isHtml5ParserEnabled', true);

						// (Optional) Setup the paper size and orientation

							$dompdf->setPaper('A4');



						// Render the HTML as PDF

							$dompdf->render();



						// Output the generated PDF to Browser

							$output = $dompdf->output();

						//echo 'uploads/tempdata/'.$_SESSION['memberId'];

							$structure ='uploads/tempdata/'.$_SESSION['memberId'];

							 mkdir($structure, 0777, true)	;						

							

						 if($msgArr['ticket_type']==1)

						 {

							 file_put_contents('uploads/tempdata/'.$_SESSION['memberId'].'/free-placement.pdf', $output);

							 $path='uploads/tempdata/'.$_SESSION['memberId'].'/free-placement.pdf';

							 

							 $mail->AddAttachment($path, '', $encoding = 'base64', $type = 'application/pdf');

							// $mail->AddAttachment($path, 'uploads/tempdata/'.$_SESSION['memberId'].'/free-placement.pdf', $encoding = 'base64', $type = 'application/pdf'); 

						 }

						 if($msgArr['ticket_type']==2)

						 {

							 file_put_contents('uploads/tempdata/'.$_SESSION['memberId'].'/eticket.pdf', $output);

							 $path='uploads/tempdata/'.$_SESSION['memberId'].'/eticket.pdf';

							 $mail->AddAttachment($path, '', $encoding = 'base64', $type = 'application/pdf');

							 // $mail->AddAttachment($path, 'uploads/tempdata/'.$_SESSION['memberId'].'/eticket.pdf', $encoding = 'base64', $type = 'application/pdf'); 

						 }

						 if($msgArr['ticket_type']==3)

						 {

							 

							 file_put_contents('uploads/tempdata/'.$_SESSION['memberId'].'/countermark.pdf', $output);

							 $path='uploads/tempdata/'.$_SESSION['memberId'].'/countermark.pdf';

							 

							 $mail->AddAttachment($path, '', $encoding = 'base64', $type = 'application/pdf');

							 // $mail->AddAttachment($path, 'uploads/tempdata/'.$_SESSION['memberId'].'/countermark.pdf', $encoding = 'base64', $type = 'application/pdf');

						 }



						

						

						

						

					} 

                //End here //				

					

					 

				//print($message);exit;

				$mail->isHTML(true);                                  // Set email format to HTML

				$mail->Subject =$subject;

				$mail->Body = $message;

				 

                $mail->AddBCC("alain@culturaccess.com", "Alan");
				$mail->AddBCC("culturaccess55@gmail.com", "Cultur");

				if(!$mail->send()) 

				{

					echo "Mailer Error: " . $mail->ErrorInfo;exit;

				}else{

					 

					$files = glob('uploads/tempdata/'.$_SESSION['memberId'].'/*');// get all file names 

					foreach($files as $file){

						if(is_file($file))

						unlink($file); //delete file

						}

					//remove files is finish

						//start remove directory



						$src='uploads/tempdata';

						$dir = opendir($src);

						while(false !== ( $file = readdir($dir)) ) {

							if (( $file != '.' ) && ( $file != '..' )) {

								$full = $src . '/' . $file;

								if ( is_dir($full) ) {

									rmdir($full);

								}

								else {

									unlink($full);

								}

							}

						}

						



				}  			

	 

} 



		

	function sendEmail($from='',$to,$subject,$msgArr, $emailtemplate){

		 

					 	

				  //$to='gutropolis@gmail.com'; 	

				 $guser = 'contact@culturaccess.com'; // username

				 $gpass = 'Coaccess2019'; //password

				 $mail = new PHPMailer(true);

				 if($from==''){

					  $mail->From = "contact@culturaccess.com";

				 }else{

					  $mail->From = $from;

				 }

				  $mail->From = "contact@culturaccess.com";

				/*SMTP Server Setting Here */

						$mail->SMTPDebug = 0; //Alternative to above constant

						$mail->Debugoutput = 'html';

						// SMTP configuration

						$mail->isSMTP();

						

						 

						$mail->SMTPAuth = true;

						$mail->Username = $guser;

						$mail->Password = $gpass;

						//$mail->SMTPSecure = 'ssl';

						$mail->Host = 'pro1.mail.ovh.net';

						$mail->Port = '587'; 

						$mail->IsHTML(true);

					   // $mail->SMTPSecure = "ssl";

						 

				       $mail->CharSet = 'UTF-8';

				

				/* End SMTP Server Setting Here */

				

				/* For local testing here */

						 

				

				/* End here */

				

				 $mail->FromName = "CulturAccess";



				 $mail->addAddress($to, "");

					 

					 

					$message =  file_get_contents(ROOT_PATH.'/emailtemplate/'.$emailtemplate);  //Your Template



						foreach($msgArr as $key => $value){

							$message = str_replace('{'.$key.'}', $value, $message);

						}

						 

					//print($message);exit;

					$mail->isHTML(true);                                  // Set email format to HTML

					$mail->Subject =$subject;

					$mail->Body = $message;

				    $mail->AddBCC("alain@culturaccess.com", "Alan");

				    $mail->CharSet = 'UTF-8';

                    

					if(!$mail->send()) 

					{

						echo "Mailer Error: " . $mail->ErrorInfo;exit;

					}  

		  

	}


	function sendEmailNews($from='',$to,$subject,$msgArr, $emailtemplate){

		 	

				 
				  //$to='gutropolis@gmail.com'; 	

				 $guser = 'contact@culturaccess.com'; // username

				 $gpass = 'Coaccess2019'; //password

				 $mail = new PHPMailer(true);

				 if($from==''){

					  $mail->From = "contact@culturaccess.com";

				 }else{

					  $mail->From = $from;

				 }

				  $mail->From = "contact@culturaccess.com";

				/*SMTP Server Setting Here */

						$mail->SMTPDebug = 0; //Alternative to above constant

						$mail->Debugoutput = 'html';

						// SMTP configuration

						$mail->isSMTP();

						

						 

						$mail->SMTPAuth = true;

						$mail->Username = $guser;

						$mail->Password = $gpass;

						//$mail->SMTPSecure = 'ssl';

						$mail->Host = 'pro1.mail.ovh.net';

						$mail->Port = '587'; 

						$mail->IsHTML(true);

					   // $mail->SMTPSecure = "ssl";

						 

				       $mail->CharSet = 'UTF-8';

				

				/* End SMTP Server Setting Here */

				

				/* For local testing here */

						 

				

				/* End here */

				

				 $mail->FromName = "CulturAccess";



				 $mail->addAddress($to, "");

					 

					 

					$message =  file_get_contents(ROOT_PATH.'/emailtemplate/'.$emailtemplate);  //Your Template



						foreach($msgArr as $key => $value){

							$message = str_replace('{'.$key.'}', $value, $message);

						}

						 

					//print($message);exit;

					$mail->isHTML(true);                                  // Set email format to HTML

					$mail->Subject =$subject;

					$mail->Body = $message;

				    $mail->AddBCC("culturaccess55@gmail.com", "Alan");
					$mail->AddBCC("alain@city-service.com", "Alan");
					$mail->AddBCC("ajaythakur9329@outlook.com", "ajay");
				   
				    $mail->CharSet = 'UTF-8';

                    

					if(!$mail->send()) 

					{

						echo "Mailer Error: " . $mail->ErrorInfo;exit;

					}  

		  

	}

	//mail jet
	function createContact($Cemail)
		{
    $mj = new Mailjet();
    $params = array(
        "method" => "POST",
        "Email" => $Cemail
   			 );

    $result = $mj->contact($params);
    }

 if ( ! function_exists('get_random_string')) :

	 function get_random_string($string_length = 6){ 

		  $character_string     = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		  $random_string         = "";

		  for ($i = 0; $i < $string_length; $i++) {

			   $random_string .= $character_string[rand(0, strlen($character_string) - 1)];

		  }

		  return $random_string;

	 }

 endif;

 

 // Mysql DateTime Formate

if ( ! function_exists('mysql_datetime')) :

   function mysql_datetime($form_date){

	   if(!isset($form_date) && empty($form_date)){

		  return '';   

	   }else{

	       $form_date   = explode('/', $form_date); 

		   $year_time = explode(' ', $form_date[2]);

	      return $year_time[0].'-'.$form_date[1].'-'.$form_date[0].' '.$year_time[1];

	   }

   }

endif;





// Human Readable DateTime Formate

if ( ! function_exists('hr_datetime')) :

   function hr_datetime($mysql_date){

	   if(!isset($mysql_date) && empty($mysql_date)){

		  return '';   

	   }else{

	      return date('d/m/Y H:i', strtotime($mysql_date));

	   }

   }

endif;



// Seo friendly url

if ( ! function_exists('generateSeoURL')) :

function generateSeoURL($string, $wordLimit = 0){

    $separator = '-';

    

    if($wordLimit != 0){

        $wordArr = explode(' ', $string);

        $string = implode(' ', array_slice($wordArr, 0, $wordLimit));

    }



    $quoteSeparator = preg_quote($separator, '#');



    $trans = array(

        '&.+?;'                    => '',

        '[^\w\d _-]'            => '',

        '\s+'                    => $separator,

        '('.$quoteSeparator.')+'=> $separator

    );



    $string = strip_tags($string);

    foreach ($trans as $key => $val){

        $string = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $string);

    }



    $string = strtolower($string);



    return trim(trim($string, $separator));

}

endif;



// Seo friendly url

if ( ! function_exists('format_uri')) :

function format_uri( $string, $separator = '-' )

{

    $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';

    $special_cases = array( '&' => 'and', "'" => '');

    $string = mb_strtolower( trim( $string ), 'UTF-8' );

    $string = str_replace( array_keys($special_cases), array_values( $special_cases), $string );

    $string = preg_replace( $accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8' ) );

    $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);

    $string = preg_replace("/[$separator]+/u", "$separator", $string);

    return $string;

}

endif;



// sizeOfnumbers

if ( ! function_exists('sizeOfnumbers')) :

function sizeOfnumbers($number)

{

 if( empty($number) ){

	 return false;  

 }else{

	$arr = explode(';',$number); 

	$range = range($arr[0],$arr[1]);

	return sizeof($range);

 }

}

endif;



if ( ! function_exists('rangeFrom')) :

function rangeFrom($number)

{

 if( empty($number) ){

	 return false;  

 }else{

	$arr = explode(';',$number); 

	return $arr[0];

 }

}

endif;



if ( ! function_exists('rangeTo')) :

function rangeTo($number)

{

 if( empty($number) ){

	 return false;  

 }else{

	$arr = explode(';',$number); 

	return $arr[1];

 }

}

endif;



// This function will  update the front json array 

/*

Params:

row_id : this is the row id of the json array like 1, or A

booked_seats_quantity : number of seats a user has booked 

table_pk_id : Id of the table

*/

if ( ! function_exists('update_json_array')) :

function update_json_array($row_id,$booked_seats_quantity,$table_pk_id){

	$seatData = App\Models\EventSeatCategories::where('id', '=', $table_pk_id)->get();

	foreach($seatData as $row){

	   $frontJsonArray     = unserialize($row['seat_json_for_front']);

	   $total_qantity      = $row['total_qantity'];

	   $net_total_quantity = $row['net_total_quantity'];  	

	}

	

	$update_net_total_quantity = $total_qantity - $booked_seats_quantity; // This is the new net quantity

	

   if (array_key_exists($row_id,$frontJsonArray)){

		$slider_range_from_value = $frontJsonArray[$row_id]['slider_range_from_value']; // From value

		

		$slider_range_to_value = $frontJsonArray[$row_id]['slider_range_to_value']; // To value

		// Make the inner array

	    $update_inner_array = array('slider_range_from_value' => ($slider_range_from_value+$booked_seats_quantity), 

		'slider_range_to_value' => $slider_range_to_value);

		

		// Replace the inner array by its Key

		$replace_array = array($row_id => $update_inner_array);

		// Re arrange the array 

		$net_array = array_replace($frontJsonArray, $replace_array);

		$data = array('seat_json_for_front' => serialize($net_array),

		              'net_total_quantity' => $net_total_quantity+$booked_seats_quantity);

			  

		$event = App\Models\EventSeatCategories::where('id', '=', $table_pk_id)->update($data);	

	    return true;

   }else{

	   return false;

   }

}

endif;



// Human Readable Date Formate

if ( ! function_exists('getAdsList')) :

   function getAdsList($cmsid){

	    $desc= '';

		// $query->where('id',$cmsid); 

         // $cmsr = $query->first();	

		 $cmsr = App\Models\Advertisement::where('id',$cmsid)->first();		 

		 return $cmsr;

   }

endif;



// sizeOfnumbers

if ( ! function_exists('findQuantity')) :

function findQuantity($number1, $number2)

{

	$range = range($number1,$number2);

	return sizeof($range);

 

}

endif;



 if ( ! function_exists('get_random_int')) :

	 function get_random_int($string_length = 2){ 

		  $character_string     = '0123456789';

		  $random_string         = "";

		  for ($i = 0; $i < $string_length; $i++) {

			   $random_string .= $character_string[rand(0, strlen($character_string) - 1)];

		  }

		  return $random_string;

	 }

 endif;

 

	// This function will  update the front json array 

	/*

	Params:

	row_id : this is the row id of row like 1, or A

	booked_seats_quantity : number of seats a user has booked  

	*/

	

	if ( ! function_exists('update_row_Quantity')) :

	function update_row_Quantity($row_id,$booked_seats_quantity,$seatSeq,$customerid){

		 $seatSquenceArr = explode(',', $seatSeq);
		 $seat_ids_sequence='';
		 //echo $row_id;exit;
		 if(count($seatSquenceArr) > 0  ) {
					$endIndex = count($seatSquenceArr)-1;
					$startindex =0;
					  $seat_number='';
					for( $i = $startindex; $i<= $endIndex; $i++ ){ 
							$seat_number = $seatSquenceArr[$i];
							
							if(intval($row_id) > 0 ){ 
									$data = array(  'status' =>'B',
								 
									'booked_datetime' => date('Y-m-d H:i:s'),
									'customer_id'=>$customerid);
									$event = App\Models\EventCategoryRowSeat::where('row_seats_id', '=', $row_id)->where('seat_number', '=', $seat_number)->update($data); 
									$result = App\Models\EventCategoryRowSeat::where('row_seats_id', '=', $row_id)->where('seat_number', '=', $seat_number)->first();
								if(count($result)> 0){
									$seat_ids_sequence.= $result['id'].",";
								}
							} 
														
					} 
		 } 
		 			$seat_ids_sequence = rtrim($seat_ids_sequence,','); 	

			return $seat_ids_sequence;

		  

	}

endif;

	

	

// This function will  use for download the ticket as .pdf

	/*

	Params:

	msgArr : All variable for .pdf

	pdfcontent : Ticket content

	*/

	

	if ( ! function_exists('downloadOrderPDF')) :	

		function downloadOrderPDF( $msgArr,$pdfcontent){

		 

				 $return_file_name='';

				$dateFilename=date('Ymdhis');

				$htmlcontent='

				<!doctype html>

				<html lang="en">

				<head></head>			  

				<body>';

				$htmlcontent.=$pdfcontent;	   

				$htmlcontent.='</body></html>';

			   //echo $htmlcontent;exit;

			 

							

							

							

							

						//Attach .PDF Function Start Here //

							 

									 

									$html =$htmlcontent;

									$dompdf = new Dompdf();

									$dompdf->load_html($html);

									$dompdf->set_option('isHtml5ParserEnabled', true);

								// (Optional) Setup the paper size and orientation

									$dompdf->setPaper('A4');



								// Render the HTML as PDF

									$dompdf->render();



								// Output the generated PDF to Browser

									$output = $dompdf->output();

								//echo 'uploads/tempdata/'.$_SESSION['memberId'];

									$structure ='uploads/tempdata/'.$_SESSION['memberId'];

									 mkdir($structure, 0777, true)	;						

									

								 if($msgArr['ticket_type']==1)

								 {

									 $return_file_name= 'uploads/tempdata/'.$_SESSION['memberId'].'/free-placement_'.$dateFilename.'.pdf';

									 file_put_contents('uploads/tempdata/'.$_SESSION['memberId'].'/free-placement_'.$dateFilename.'.pdf', $output);

									 

								 }

								 if($msgArr['ticket_type']==2)

								 {

									  $return_file_name= 'uploads/tempdata/'.$_SESSION['memberId'].'/eticket_'.$dateFilename.'.pdf';

									 file_put_contents('uploads/tempdata/'.$_SESSION['memberId'].'/eticket_'.$dateFilename.'.pdf', $output);

								 }

								 if($msgArr['ticket_type']==3)

								 {

									 $return_file_name= 'uploads/tempdata/'.$_SESSION['memberId'].'/countermark_'.$dateFilename.'.pdf';

									 file_put_contents('uploads/tempdata/'.$_SESSION['memberId'].'/countermark_'.$dateFilename.'.pdf', $output);

											 // $mail->AddAttachment($path, 'uploads/tempdata/'.$_SESSION['memberId'].'/countermark.pdf', $encoding = 'base64', $type = 'application/pdf');

								 }



								

								return $return_file_name;

								

							 

						//End here //				

							 

		} 

	endif;

	

	

// Function to display month 

 if( ! function_exists('get_month_name') ):

  function get_month_name($month_name){

	  if($_SESSION['default'] == 'en_US'){

		   $monthArray = array(1 => 'January', 

		                       2 => 'February', 

							   3 => 'March', 

							   4 => 'April', 

							   5 => 'May', 

							   6 => 'June',

							   7 => 'July', 

							   8 => 'August', 

							   9 => 'September',

							   10 => 'October',

							   11 => 'November',

							   12 => 'December'); 

		

	  }else{

		  $monthArray = array(1 => 'Janvier',

		                      2 => 'FÃ©vrier', 

							  3 => 'Mars', 

							  4 => 'Avril',

							  5 => 'Mai',

							  6 =>'Juin',

		                      7 => 'Juillet', 

							  8 => 'AoÃ»t', 

							  9 => 'Septembre', 

							  10 => 'Octobre',

							  11 => 'Novembre',

							  12 => 'DÃ©cembre'); 

	  }

	  if( array_key_exists($month_name, $monthArray)){

		  return $monthArray[$month_name];

	  }else{

		 return '';

	  }

  }

 endif;

 

 

  // Function to display payment method

 if( !function_exists('get_payment_method') ):

    function get_payment_method($payment_method){

		if($_SESSION['default'] == 'en_US'){

			$methodArray = array('Tranzilla' => 'Tranzilla', 'cash' => 'Cash', 'cheque' => 'Cheque', 'ccard' => 'CCard');

		}else{

			$methodArray = array('Tranzilla' => 'Tranzila', 'cash' => 'EspÃ¨ces', 'cheque' => 'ChÃ¨que', 'ccard' => 'CCard');

		}

		if( array_key_exists($payment_method, $methodArray)){

		  return $methodArray[$payment_method];

	  }else{

		 return '';

	  }

	}

 endif; 

 



if ( ! function_exists('downloadOrderPDFTicket')) :	

		function downloadOrderPDFTicket( $msgArr,$pdfcontent){

		 

				 $return_file_name='';

				$dateFilename=date('Ymdhis');

				$htmlcontent='

				<!doctype html>

				<html lang="en">

				<head></head>			  

				<body>';

				$htmlcontent.=$pdfcontent;	   

				$htmlcontent.='</body></html>';

			  // echo $htmlcontent;exit;

			 			//Attach .PDF Function Start Here //

							$html =$htmlcontent;

							$dompdf = new Dompdf();

							$dompdf->load_html($html);

							$dompdf->set_option('isHtml5ParserEnabled', true);

						// (Optional) Setup the paper size and orientation

							$dompdf->setPaper('A4');



						// Render the HTML as PDF

							$dompdf->render();



						// Output the generated PDF to Browser

							$output = $dompdf->output();

							

						//echo 'uploads/tempdata/'.$_SESSION['memberId'];

							$structure ='uploads/tempdata/'.$msgArr['client_id'];

							 mkdir($structure, 0777, true)	;						

									

								 if($msgArr['ticket_type']==1)

								 {

									 $return_file_name= 'uploads/tempdata/'.$msgArr['client_id'].'/free-placement_'.$dateFilename.'.pdf';

									 file_put_contents('uploads/tempdata/'.$msgArr['client_id'].'/free-placement_'.$dateFilename.'.pdf', $output);

									 

								 }

								 if($msgArr['ticket_type']==2)

								 {

									  $return_file_name= 'uploads/tempdata/'.$msgArr['client_id'].'/eticket_'.$dateFilename.'.pdf';

									 file_put_contents('uploads/tempdata/'.$msgArr['client_id'].'/eticket_'.$dateFilename.'.pdf', $output);

								 }

								 if($msgArr['ticket_type']==3)

								 {

									 $return_file_name= 'uploads/tempdata/'.$msgArr['client_id'].'/countermark_'.$dateFilename.'.pdf';

									 file_put_contents('uploads/tempdata/'.$msgArr['client_id'].'/countermark_'.$dateFilename.'.pdf', $output);

											 // $mail->AddAttachment($path, 'uploads/tempdata/'.$_SESSION['memberId'].'/countermark.pdf', $encoding = 'base64', $type = 'application/pdf');

								 }



								

								return $return_file_name;

								

							 

						//End here //				

							 

		} 

	endif;

 

 // Generate SEO URL

  function Generate_SEO_Url ($s) {
      //$s = strip_html_tags($s);
	  //Convert accented characters, and remove parentheses and apostrophes
	  $from = explode (',', "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,(,),[,],'");
	  $to = explode (',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,,,,,,');
	  //Do the replacements, and convert all other non-alphanumeric characters to spaces
	  //$s = preg_replace ('~[^wd]+~', '-', str_replace ($from, $to, trim ($s)));
	  $s = str_replace ($from, $to, trim ($s));
	  
	  $s = str_replace('---', '-', $s);
	  $s = str_replace('--', '-', $s);
	  //Remove a - at the beginning or end and make lowercase
	  $s = strtolower (preg_replace ('/^-/', '', preg_replace ('/-$/', '', $s)));

	  return $s;

 } 
 
 function cleanString2($text) {
	
    $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
		'/quot/u'       =>  '-',
    );
	$text = strip_tags($text);
    $text = preg_replace(array_keys($utf8), array_values($utf8), $text);
	$text = preg_replace("/[^a-zA-Z]/", "-", $text);
  
    $text = preg_replace('/-+/', '-', $text);
	$from = explode (',', "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,(,),[,],'");
    $to = explode (',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,,,,,,');
    $text = str_replace ($from, $to, trim ($text));
	$text = trim($text, '"');
	$text = str_replace('"', '', $text);
	return $text;
}

 // Strip all html tags

 function strip_html_tags( $text )

{

// Remove invisible content

    $text = preg_replace(

        array(

            //ADD a (') before @<head ON NEXT LINE. Why? see below

            '@<head[^>]*?>.*?</head>@siu',

            '@<style[^>]*?>.*?</style>@siu',

            '@<script[^>]*?.*?</script>@siu',

            '@<object[^>]*?.*?</object>@siu',

            '@<embed[^>]*?.*?</embed>@siu',

            '@<applet[^>]*?.*?</applet>@siu',

            '@<noframes[^>]*?.*?</noframes>@siu',

            '@<noscript[^>]*?.*?</noscript>@siu',

            '@<noembed[^>]*?.*?</noembed>@siu',

          // Add line breaks before and after blocks

            '@</?((address)|(blockquote)|(center)|(del))@iu',

            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',

            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',

            '@</?((table)|(th)|(td)|(caption))@iu',

            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',

            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',

            '@</?((frameset)|(frame)|(iframe))@iu',

        ),

        array(

            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',

            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",

            "\n\$0", "\n\$0",

        ),

        $text );

    return strip_tags( $text );

}


function Generate_SEO_Url_FTN ($text) {
	  //Convert accented characters, and remove parentheses and apostrophes

	   $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',

        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
		'/quot/u'       =>  '-',
    );
	$text = strip_tags($text);
    $text = preg_replace(array_keys($utf8), array_values($utf8), $text);
	$text = preg_replace("/[^a-zA-Z]/", "-", $text);
  
    $text = preg_replace('/-+/', '-', $text);
	$from = explode (',', "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,(,),[,],'");
    $to = explode (',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,,,,,,');
    $text = str_replace ($from, $to, trim ($text));
	$text = trim($text, '"');
	$text = str_replace('"', '', $text);
	$text = str_replace('---', '-', $text);
	$text = str_replace('--', '-', $text);
	 //Remove a - at the beginning or end and make lowercase
    $text = strtolower (preg_replace ('/^-/', '', preg_replace ('/-$/', '', $text)));

	  return $text;

 } 
 
 function cleanStringData($text) {
	
    $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
		'/quot/u'       =>  '-',
    );
	$text = strip_tags($text);
    $text = preg_replace(array_keys($utf8), array_values($utf8), $text);
	$text = preg_replace("/[^a-zA-Z]/", "-", $text);
  
    
	$from = explode (',', "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,(,),[,],'");
    $to = explode (',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,,,,,,');
    $text = str_replace ($from, $to, trim ($text));
	$text = trim($text, '"');
	$text = str_replace('"', '-', $text);
	$text = preg_replace('/-+/', '-', $text);
	return $text;
}

 function cleanStringDataFtn($text) {
	
	$text = strip_tags($text);
	$text = preg_replace("/[^a-zA-Z]/", "-", $text);
  
    
	$from = explode (',', "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,(,),[,],'");
    $to = explode (',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,,,,,,');
    $text = str_replace ($from, $to, trim ($text));
	$text = trim($text, '"');
	$text = str_replace('"', '-', $text);
	$text = preg_replace('/-+/', '-', $text);
	return $text;
}

// Check if the character is integer or
function checkAlphabetIncrement($char){
	if( ctype_alpha($char) ){
		$next_ch = ++$char; 
		if (strlen($next_ch) > 1) { // if you go beyond z or Z reset to a or A
		    $next_ch = $next_ch;
		}else{
			$next_ch = $next_ch[0];
		}
	}else{
		$next_ch =  ++$char;
	}
	return $next_ch;
}
 

 

?>