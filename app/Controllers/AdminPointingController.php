<?php
namespace App\Controllers;

use App\Models\Pointing;

class AdminPointingController extends Base 
{
	protected $container;
	protected $lang;
	protected $servername;
	protected $username;
	protected $password;
	protected $dbname;
	protected $conn;

	// Class constructor
	public function __construct($container)
	{
	    ini_set('default_charset', 'utf-8');
		set_time_limit(0);
		$this->container = $container;
		$this->servername = $this->container['settings']['database']['host'];
		$this->username = $this->container['settings']['database']['username'];
		$this->password = $this->container['settings']['database']['password'];
		$this->dbname = $this->container['settings']['database']['database'];
		$this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
		$this->lang =  $this->container->view['adminLang'];
	}


	public function LoadList($request, $response){
		
		
		// get by event id
		$event_id = $request->getAttribute('id');
		$params = array(
			'event_id' => $event_id,
			'liste' => Pointing::where('event_id', $event_id)->get() 
			);  // get all orders from database
		
		//load event id from order id and order_id (product_id)
		//Pointing::where('event_id' , $event_id)->get();

		return $this->render($response, ADMIN_VIEW.'/Event/pointing.twig',$params);
	}


	public function downloadXLSold($request, $response){

			$event_id = $request->getAttribute('event_id');
			$liste = Pointing::where('event_id', $event_id)->get();


			$htmlcontent='<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
    		<html>
        	<head><meta http-equiv=\"Content-type\" content=\"text/html;charset=utf-8\" /></head>
        	<body>';


			$table .= '<table border="1">';
			$table .= '<thead >
				<tr>
					<th style="background-color:#5cb7d4;">
						ID</th>
					<th style="background-color:#5cb7d4;">'.htmlentities('N° Facture').'</th>
					<th style="background-color:#5cb7d4;">'.htmlentities('Client').'</th>
					<th style="background-color:#5cb7d4;">'.htmlentities('Montant').'</th>
					<th style="background-color:#5cb7d4;">'.htmlentities('Événement').' </th>
					<th style="background-color:#5cb7d4;">'.htmlentities('N° commande').'</th>
					<th style="background-color:#5cb7d4;">'.htmlentities('N° événement').'</th>
				</tr>
			</thead>';


			$table .= '<tbody>';

			if (count($liste)>0){
				foreach ( $liste as $row){

					$table .= '<tr">
						<td> <span>'.$row->id .'</span></td>
						<td> <span>'.$row->invoice_number .'</span></td>
						<td> <span>'.$row->client_nom .'</span></td>
						<td> <span>'.$row->total_price .'</span></td>
						<td> <span>'.$row->event_name .'</span></td>
						<td> <span>'.$row->order_id .'</span></td>
						<td> <span>'.$row->event_id .'</span></td>
						</tr>';
				}
			}


			$table.=  '</tbody>';

			$htmlcontent .= $table;
			$htmlcontent .= '</table></body></html>';

			$html = $htmlcontent;



			header( "Content-type: application/vnd.ms-excel; charset=UTF-8" ); 


			$file_name = 'Pointing'.$event_id.'_'.time();   

			header("Content-Disposition: attachment; filename=".$file_name.".xls"); 
			header("Pragma: no-cache"); 
			header("Expires: 0");
			echo $html;
			exit;							  

    }
    public function downloadXLS($request, $response){

    		$event_id = $request->getAttribute('event_id');


			$htmlcontent='';
		    error_reporting(0);
			ob_end_clean();
			header( 'Content-Type: text/csv' );
			$file_name = 'Pointing'.$event_id.'_'.time();
			header( 'Content-Disposition: attachment;filename='.$file_name.'.csv');
			$fp = fopen('php://output', 'w');
			$reportTable = '';
			//$reportTable = 	utf8_decode($this->lang['report_by_event_accounting_approach_txt']).' Date:'.date('d/m/Y');	
			//$titles1 = array(strip_tags($reportTable));
			//fputcsv($fp, $titles1);
			//$reportTableTitle = 	'List of Members '.date('d/m/Y');
			//$titles12 = array(strip_tags($reportTableTitle));
			//fputcsv($fp, $titles12);


			$sqlPointings = "SELECT * FROM `pointing` WHERE `event_id` = $event_id ";
			//id
			//invoice_number
			//client_nom 
			//total_price 
			//event_name 
			//order_id 
			//event_id 
					
			$resultQuery = mysqli_query($this->conn, $sqlPointings);
			
			while($row = mysqli_fetch_assoc($resultQuery)){

				$reportTablett =  array(utf8_decode('N° Facture'),
			                			utf8_decode('Client'),
										utf8_decode('Montant'),
										utf8_decode('Événement'),
										utf8_decode('N° commande'),
										utf8_decode('N° événement')
									)
									;

				fputcsv($fp, $reportTablett);
				
				$data = array(
							  $row['invoice_number'],
							  $row['client_nom'],
							  $row['total_price'] ,
							  $row['event_name'],
							  $row['order_id'],
							  $row['event_id']
						);

				// Put the data array to the make its CSV Columns
				$data = array_map("utf8_decode", $data);		
				fputcsv($fp, $data);

			}


				// closing
				fclose($fp);
				$contLength = ob_get_length();
				header( 'Content-Length: '.$contLength);

			
    }


}