<?php
namespace App\Controllers;

use App\Models;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Pointing;

use Zxing\QrReader;

class QrcodeController extends BaseController {

	public function verifyqrcodeform($request, $response){
		/* Simple form for tests */
        $datas = $request->getQueryParams('p');
   
        if (!is_null($datas)){
            $data = explode(',', $datas['p']); 

            $invoicenumber = $data[0] ;
            $user_id = intval( $data[1] ) ;
            $client_nom = $data[2] ;
            $price = $data[3] ;
            $event = $data[4] ;
            $order_id = intval($data[5]) ;




            $order_datas  = Order::where('id', $order_id)->first();
            if (isset($order_datas)){

                // check if invoice number is in order table
              
                    // if status is S
                if ($order_datas->status == 'S'  ){
                    // checking duplication in pointing table
                    $is_pointed  = Pointing::where('invoice_number', $invoicenumber)->first()->id;
                    // pointing data if  they are not already pointed
                    if ( ! isset( $is_pointed ) ){
                        // Get event id from order_items table
                        $event_id  = OrderItems::where('order_id' , $order_id)->first()->product_id;
                        // Saving datas in pointing table.
                        $pointingTable = new Pointing();
                        $pointingTable->invoice_number = $invoicenumber;
                        $pointingTable->client_nom = $client_nom;
                        $pointingTable->total_price = $price;
                        $pointingTable->event_name = $event;
                        $pointingTable->order_id = $order_id;
                        $pointingTable->event_id = $event_id;
                
                        $pointingTable->save();

                        $jsonData = array( 'success'=>true , 'success');
                    }
                    else {
                        $jsonData = array('success'=>false , 'error' => 'alreading pointed ticket');
                    }
                }
                // if status is R
                else if ($order_datas->status == 'R'  ){
                    $jsonData = array('success'=>false , 'error' => 'ticket status is R');;
                }
                // is status is undefined
                else {
                    $jsonData = array('success'=>false , 'error' => 'undefined status');;
                }
                
                
            }else {
                $jsonData = array('success'=>false , 'error' => 'No order matching');
            }
            
        } else {
                $jsonData = array('success'=>false , 'error'=> 'no ticket sent');
        }
        

        return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));
	}

    public function verifyqrcode($request, $response) {

        $datas = $request->getParam('qrcode_data');    
        
   
        if (!is_null($datas)){
            $data = explode(',', $datas); 
            var_dump($data);

            $invoicenumber = $data[0] ;
            $user_id = intval( $data[1] ) ;
            $client_nom = $data[2] ;
            $price = $data[3] ;
            $event = $data[4] ;
            $order_id = intval( $datas[5] );
    
            $order_datas  = Order::where('id', $order_id)->first();
            if (isset($order_datas)){

                // check if invoice number is in order table
              
                	// if status is S
                if ($order_datas->status == 'S'  ){
                    // checking duplication in pointing table
                	$is_pointed  = Pointing::where('invoice_number', $invoicenumber)->first()->id;
                    // pointing data if  they are not already pointed
                	if ( ! isset( $is_pointed ) ){
                        // Get event id from order_items table
                        $event_id  = OrderItems::where('order_id' , $order_id)->first()->product_id;
                        // Saving datas in pointing table.
                        $pointingTable = new Pointing();
                        $pointingTable->invoice_number = $invoicenumber;
                        $pointingTable->client_nom = $client_nom;
                        $pointingTable->total_price = $price;
                        $pointingTable->event_name = $event;
                        $pointingTable->order_id = $order_id;
                        $pointingTable->event_id = $event_id;
                
                        $pointingTable->save();
                	}
                    else {
                        $jsonData = array('status'=>false , 'error' => 'alreading pointed ticket');
                    }
                }
                // if status is R
                else if ($order_datas->status == 'R'  ){
                    $jsonData = array('status'=>false , 'error' => 'ticket status is R');;
                }
                // is status is undefined
                else {
                    $jsonData = array('status'=>false , 'error' => 'undefined status');;
                }
                
                
            }else {
                $jsonData = array('status'=>false , 'error' => 'No order matching');
            }
            
        } else {
                $jsonData = array('status'=>false , 'error'=> 'no ticket sent');
        }
        

        return $response->withHeader('Content-type','application/json')->write(json_encode($jsonData));

    }

    public  function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); 
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }
}
