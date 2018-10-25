<?php

	require_once 'dompdf/autoload.inc.php';
	
	$html = $dompdf = '';
	
	use Dompdf\Dompdf;
	
	// $html = '<h2>hello world !</h2>';
	
	$html = '
		<!doctype html>
			<html lang="en">
			   <head></head>			  
			   <body>
				  <!-- head table -->
				  <table width="100%" style="page-break-after: always; font-family: Roboto Condensed, sans-serif;   margin: 0px auto;" cellspacing="0" cellpadding="0" >
					<tr>
						<td>
						   <table style="" width="100%" cellspacing="0" cellpadding="0">';
			
			$html .= '   <tr>				  
							<td  style="">
									<table width="100%" cellspacing="0" cellpadding="0" style="background:#a91968;
									   background-repeat:repeat-y;padding:5px;text-align:center;color:#fff;">
									   <tr>
										  <td align="center" width="100%" style="font-family: Roboto Condensed, sans-serif; border-bottom: 10px solid #a91968;" ></td>
									   </tr>
									</table>
								 </td>
							  </tr>
							  <tr >
								 <td style=" text-align: center;   padding: 10px 0px; border-bottom: 5px solid #a91968;" width="100%">
									<img src="logo_left.png"   style="width:100px;" />
								 </td>
							  </tr>
							  <tr>
								 <td style="    text-align: center;    padding: 10px 0px;">
									<table width="100%" cellspacing="0" cellpadding="0">
									   <tr>
										  <td width="100%" align="center" style="background: #313132;color: #fff; text-transform: uppercase;    padding: 10px;    font-weight: 600;    letter-spacing: 2px;font-family: Roboto Condensed, sans-serif;">
											 CONTREMARQUE à éCHANGER à L’ACCUEIL DE L’éVèNEMENT 
										  </td>
									   </tr>
									</table>
								 </td>
							  </tr>
							  </table>';
							  
					$html .= '<table width="100%" cellspacing="0" cellpadding="0">
							<tr>
								 <td style="padding:0px 15px 20px; background:url(bg-center.png) #f0eaec;">
									<table   style=" background:url(bg-center.png) #f0eaec;" width="100%" cellspacing="0" cellpadding="0" font-family: Roboto Condensed, sans-serif;   margin: 0px auto;" align="center">
							  <tr>
								 <td width="40%" style="padding:15px;font-family: Roboto Condensed, sans-serif;" align="left">
									<table width="100%" cellspacing="0" cellpadding="0" style="border-right: 2px dashed gray; width: 100%">
									   <tr>
										  <td colspan="2" style="color: #313132; font-size: 15px;  font-family: Roboto Condensed, sans-serif;">
											 {auditorium_name}
										  </td>
									   </tr>
									   <tr>
										  <td colspan="2"   style="font-size: 12px; color: #313132; padding: 5px 0 ; font-family: Roboto Condensed, sans-serif;">
												<p style="margin:0;">Rehov Yerushalaim</p> 
									<p style="margin:0;">{auditorium_city}</p> 
									<p style="margin:0;">{event_date}</p> 
									<p style="margin:0;">{event_hour}</p> 
										  </td>
									   </tr>
									   <tr><td width="25%">{seat_management_sidebar}</td></tr>
									   <tr>
										  <td colspan="2"  style="font-size: 12px; color: #313132; padding: 5px 0 ;font-family: Roboto Condensed, sans-serif;">
											<p style="margin:0;">{total_price} NIS  </p>
									<p style="margin:0;">Frais de gestion  </p>
									<p style="margin:0;">Inclus</p>
										  </td>
									   </tr>
									   <tr>
										  <td colspan="2"  style="font-size: 12px; color: #313132; padding: 5px 0 ;font-family: Roboto Condensed, sans-serif;">
											<p style="margin:0;">Réglé via culturaccess.com</p>
									<p style="margin:0;">Billet niéchangeable ni remboursable (sauf assurance)</p>
									
										  </td>
									   </tr>
									   <tr>
										  <td colspan="2"  style="font-size: 12px; color: #313132; padding: 5px 0 ; font-family: Roboto Condensed, sans-serif;">
											 <p style="margin:0;">Réglé via culturaccess.com</p>
											 <p style="margin:0;">Billet niéchangeable ni remboursable (sauf assurance)</p>
										  </td>
									   </tr>
									   <tr>
										  <td  width="50%" style="font-size: 12px; color: #313132; font-size: 12px; color: #313132;  font-family: Roboto Condensed, sans-serif; border: 2px solid #313132; padding: 10px; ">
											{number_of_order}	
										  </td>
										  <td width="50%">
										  </td>
									   </tr>
									</table>
								 </td>';
								 
					   $html .= '<td width="60%" style="padding:15px;font-family: Roboto Condensed, sans-serif;"  valign="top">
									<table cellspacing="0" cellpadding="0" align="center" style="text-align: center;">
									   <tr>
										  <td align="center" style="font-size: 25px; color: #313132; font-family: Roboto Condensed, sans-serif;">
											{auditorium_name}
										  </td>
									   </tr>
									   <tr>
										  <td style="font-size: 11px; color: #313132; padding-top: 3px; font-family: Roboto Condensed, sans-serif;">
											{productor_name}
										  </td>
									   </tr>
									   <tr>
										  <td style="font-size: 11px; color: #313132; padding-bottom: 5px; font-family: Roboto Condensed, sans-serif;">présentent</td>
									   </tr>
									   <tr>
										  <td style="font-size: 30px; color: #313132; font-family: Roboto Condensed, sans-serif;">
											 {artist_name}
										  </td>
									   </tr>
									   <tr>
										  <td style="font-size: 20px; color: #313132; padding-top:5px; font-family: Roboto Condensed, sans-serif;">
											{event_name}
										  </td>
									   </tr>
									   <tr>
										  <td style="padding: 20px 0; border: 1px solid #313132; font-size: 24px; margin: 0; font-family: Roboto Condensed, sans-serif;">
												{event_date} {event_hour}
										  </td>
									   </tr>
									</table>
									<table  width="100%" align="center">
									    <tr><td>{seat_management}</td></tr>
									</table>
								 </td>
							  </tr>
						   </table>
						</td>
					 <tr>';
			$html .= '
						<td style=" padding:0px;">
						   <table width="100%"  cellspacing="0" cellpadding="0" style="  background-color: #fcfcfc ;  font-family: Roboto Condensed, sans-serif;">
							  <tr>
								 <td width="40%" style="padding:15px;font-family: Roboto Condensed, sans-serif;" align="left">
									<table cellspacing="0" cellpadding="0" style="  width: 100%">
									   <tr>
										  <td style="color: #9f0057; font-size: 12px; width: 15%; font-family: Roboto Condensed, sans-serif;">
											 NOM :
										  </td>
										  <td style="font-family: Roboto Condensed, sans-serif; color: #575656; font-size: 12px;">
											{client_nom}
										  </td>
									   </tr>
									   <tr>
										  <td style="font-family: Roboto Condensed, sans-serif; color: #9f0057; font-size: 12px; width: 10%;font-family: Roboto Condensed, sans-serif;">
											 ADRESSE :
										  </td>
										  <td style=" font-family: Roboto Condensed, sans-serif; color: #575656; font-size: 12px;">
											 {client_address}
										  </td>
									   </tr>
									   <tr>
										  <td style="font-family: Roboto Condensed, sans-serif; color: #9f0057; font-size: 12px; width: 10%;">
											 VILLE :
										  </td>
										  <td style="font-family: Roboto Condensed, sans-serif; color: #575656; font-size: 12px;">
											{client_city}
										  </td>
									   </tr>
									   <tr>
										  <td style="font-family: Roboto Condensed, sans-serif; color: #9f0057; font-size: 12px; width: 10%;">
											 ID CLIENT
										  </td>
										  <td style="font-family: Roboto Condensed, sans-serif; color: #575656; font-size: 12px;">
											 {client_id}
										  </td>
									   </tr>
									</table>
								 </td>
							  </tr>
						   </table>
						</td>
					 </tr>';
					 
			$html .= '<table style=""  width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<td style="font-family: Roboto Condensed, sans-serif;    text-align: center;    padding: 5px 0px 5px;">
						   <table width="100%" cellspacing="0" cellpadding="0">
							  <tr>
								 <td width="100%" align="center" style="background: #313132;color: #fff; text-transform: uppercase;    padding: 10px; padding: 10px 0; font-size: 12px;font-weight: 600; letter-spacing: 2px;font-family: Roboto Condensed, sans-serif;">
									CONDITIONS D’UTILISATION 
								 </td>
							  </tr>
						   </table>
						</td>
					 </tr>
					 <tr>
						<td style="color: #9f0057; font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;">
						   Cette contremarque doit être échangée par un billet définitif avant le début de l’événement. Merci d’arriver au plus tard 30 min. avant le début de la manifestation.
						</td>
					 </tr>
					 <tr>
						<td style="color: #575656;font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;" >
						   Toute utilisation frauduleuse est de la responsabilité du titulaire du billet. CulturAccess et la production de l’événement ne sont pas en  mesure d’intervenir dans ce cas là.
						</td>
					 </tr>
					 <tr>
						<td style="color: #575656;font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;">
						   En cas de question ou de requête merci d’adresser un mail qui sera traité de façon prioritaire à : <span style="color: #9f0057"> suivibillets@culturaccess.com </span>
						</td>
					 </tr>
					 <tr>
						<td style="color: #575656;font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;">
						   Voici quelques conditions à respecter pour une bonne utilisation de votre billet :
						   • Votre billet doit être imprimé sur un papier A4 blanc 
						</td>
					 </tr>
					 <tr>
						<td style="color: #575656;font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;">
						   • Si l’impression est de mauvaise qualité ou si le document est endommagé, l’accès ne sera pas possible. 
						</td>
					 </tr>
					 <tr>
						<td style="color: #575656;font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;">
						   • Le billet n’est ni échangeable, ni remboursable sauf si une assurance a été souscrite. Auquel cas le billet sera remboursable dans le mois qui suit l’événement.
						</td>
					 </tr>
					 <tr>
						<td style="color: #575656;font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;">
						   • Il est recommandé d’être présent sur le lieu de l’événement 30 min. avant l’horaire, d’échanger sa contremarque, et d’occuper son siège 15 min. à l’avance. L’accès aux places numérotées n’est pas garanti après l’heure de début de l’événement. Dans le cas ou votre retard ne vous permet pas d’accéder à la salle, cela ne donne droit à aucun remboursement.
						</td>
					 </tr>
					 <tr>
						<td style="color: #575656;font-size: 12px; padding-bottom: 5px;font-family: Roboto Condensed, sans-serif;">
						   • CulturAccess est un prestataire de vente de billets. Les événements se déroulent sous l’entière responsabilité des producteurs et organisateurs 
						</td>
					 </tr>
					 </table>';
					 
		$html .= '</tr>
				  </table>
				  <tr>
					 <td style="font-family: Roboto Condensed, sans-serif;padding-top:5px;">
						<table width="100%" style="font-family: Roboto Condensed, sans-serif;   margin: 0px auto;" cellspacing="0" cellpadding="0">
						   <tr>
							  <td style="background:#2d2d2d;  width: 100%;padding:10px;">
								 <table cellspacing="0" cellpadding="0" width="100%">
									<tr>
									   <td width="100%" align="center"><img src="footlogo.png" style="width:100px"></td>
									   
									</tr>
								 </table>
							  </td>
						   </tr>
						</table>
					 </td>
				  </tr>
				  </td>
				  </tr>
				  </table>
				  
			
			   </body>
			</html>';
	echo $html;
	//$dompdf = new Dompdf();	
	//$dompdf->loadHtml($html);									
	//$dompdf->setPaper('A4', 'portrait');
	//$dompdf->render();
	//$dompdf->stream();


