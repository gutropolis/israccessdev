<?php

namespace App\Tools;

use App\Models\User;
use App\Models\Usermeta;

use App\Models;

use App\Models\Operators;



class Auth

{

	

	public function attempt($email, $password, $login_opt) {

		

		if($login_opt == 1){

		    $user = User::where('email', $email)->whereIn('type',array('Admin','Productor'))->first();

		    $usermetaname=Usermeta::where('user_id',$user->id)->first();

			//$user = User::where('email', $email)->whereRaw("type='Admin' OR type='Productor'")->first();

			if(!$user) {

				return false;

			}

			$check_password = $this->checkPasswordIsValid($password,$user->password);

			if($check_password===true) {

				$_SESSION['isAdmin'] = 'Okay';

				$_SESSION['adminId'] = $user->id;

				$_SESSION['adminRoleId'] = $user->role_id;

				$_SESSION['adminName'] = $user->name;

				$_SESSION['adminEmail'] = $user->email;

				$roleID = $user->role_id;

				$profile_picture = $user->user_picture;

				

				$user_type = $user->type;

				if($user_type == 'Admin'){

					if( $user->id == 1){

					   $Yes = 'Y';

					}else{

						$Yes = 'N';

					}

					$_SESSION['is_dashboard_view']       = $Yes;

					$_SESSION['is_advertisement_add']    = $Yes;

					$_SESSION['is_advertisement_edit']   = $Yes;

					$_SESSION['is_advertisement_del']    = $Yes;

					$_SESSION['is_city_add']             = $Yes;

					$_SESSION['is_city_edit']            = $Yes;

					$_SESSION['is_city_del']             = $Yes;

					$_SESSION['is_currency_add']         = $Yes;

					$_SESSION['is_currency_edit']        = $Yes;

					$_SESSION['is_currency_del']         = $Yes;

					$_SESSION['is_slider_add']           = $Yes;

					$_SESSION['is_slider_edit']          = $Yes;

					$_SESSION['is_slider_del']           = $Yes;

					$_SESSION['is_cat_page_slider_add']  = $Yes;

					$_SESSION['is_cat_page_slider_edit'] = $Yes;

					$_SESSION['is_cat_page_slider_del']  = $Yes;

					$_SESSION['is_category_add']         = $Yes;

					$_SESSION['is_category_edit']        = $Yes;

					$_SESSION['is_category_del']         = $Yes;

					$_SESSION['is_auditorium_add']       = $Yes;

					$_SESSION['is_auditorium_edit']      = $Yes;

					$_SESSION['is_auditorium_del']       = $Yes;

					$_SESSION['is_artist_add']           = $Yes;

					$_SESSION['is_artist_edit']          = $Yes;

					$_SESSION['is_artist_del']           = $Yes;

					$_SESSION['is_productor_add']        = $Yes;

					$_SESSION['is_productor_edit']       = $Yes;

					$_SESSION['is_productor_del']        = $Yes;

					$_SESSION['is_member_add']           = $Yes;

					$_SESSION['is_member_edit']          = $Yes;

					$_SESSION['is_member_del']           = $Yes;

					$_SESSION['is_operator_add']         = $Yes;

					$_SESSION['is_operator_edit']        = $Yes;

					$_SESSION['is_operator_del']         = $Yes;

					$_SESSION['is_section_add']          = $Yes;

					$_SESSION['is_section_edit']         = $Yes;

					$_SESSION['is_section_del']          = $Yes;

					$_SESSION['is_event_group_add']      = $Yes;

					$_SESSION['is_event_group_edit']     = $Yes;

					$_SESSION['is_event_group_del']      = $Yes;

					$_SESSION['is_event_add']            = $Yes;

					$_SESSION['is_event_edit']           = $Yes;

					$_SESSION['is_event_del']            = $Yes;

					$_SESSION['is_dont_miss_event_add']  = $Yes;

					$_SESSION['is_dont_miss_event_edit'] = $Yes;

					$_SESSION['is_dont_miss_event_del']  = $Yes;

					$_SESSION['is_event_of_day_add']     = $Yes;

					$_SESSION['is_event_of_day_edit']    = $Yes;

					$_SESSION['is_event_of_day_del']     = $Yes;

					$_SESSION['is_seat_add']             = $Yes;

					$_SESSION['is_seat_edit']            = $Yes;

					$_SESSION['is_seat_del']             = $Yes;

					$_SESSION['is_ticket_add']           = $Yes;

					$_SESSION['is_ticket_edit']          = $Yes;

					$_SESSION['is_ticket_del']           = $Yes;

					$_SESSION['is_ticket_view']           = $Yes;

					$_SESSION['is_user_add']             = $Yes;

					$_SESSION['is_user_edit']            = $Yes;

					$_SESSION['is_user_del']             = $Yes;

					$_SESSION['is_partner_add']          = $Yes;

					$_SESSION['is_partner_edit']         = $Yes;

					$_SESSION['is_partner_del']          = $Yes;

					$_SESSION['is_cms_view']             = $Yes;

					$_SESSION['is_cms_edit']             = $Yes;

					$_SESSION['is_payment_type_add']     = $Yes;

					$_SESSION['is_payment_type_edit']    = $Yes;

					$_SESSION['is_payment_type_del']     = $Yes;

					$_SESSION['is_community_add']        = $Yes;

					$_SESSION['is_community_edit']       = $Yes;

					$_SESSION['is_community_del']        = $Yes;

					$_SESSION['is_community_page_add']   = $Yes;

					$_SESSION['is_community_page_edit']  = $Yes;

					$_SESSION['is_community_page_del']   = $Yes;

					$_SESSION['is_order_view']           = $Yes;

					$_SESSION['is_ticket_selling_view']  = $Yes;

					$_SESSION['is_setting_edit']         = $Yes;

					$_SESSION['is_setting_view']         = $Yes;

					$_SESSION['is_coupon_add']   = $Yes;

					$_SESSION['is_coupon_edit']  = $Yes;

					$_SESSION['is_coupon_del']   = $Yes;

					$_SESSION['is_coupon_view']   = $Yes;

					//  BEGIN: Allowed Left Menu Items

					$_SESSION['is_dashboard_allowed']    = 'Y';

					$_SESSION['is_advertisement_allowed'] = $Yes;

					$_SESSION['is_city_allowed']          = $Yes;

					$_SESSION['is_currency_allowed']      = $Yes;

					$_SESSION['is_slider_allowed']        = $Yes;

					$_SESSION['is_cat_page_slider_allowed'] = $Yes;

					$_SESSION['is_category_allowed']       = $Yes;

					$_SESSION['is_auditorium_allowed']    = $Yes;

					$_SESSION['is_artist_allowed']        = $Yes;

					$_SESSION['is_productor_allowed']     = $Yes;

					$_SESSION['is_member_allowed']        = $Yes;

					$_SESSION['is_operator_allowed']      = $Yes;

					$_SESSION['is_section_allowed']       = $Yes;

					$_SESSION['is_event_group_allowed']   = $Yes;

					$_SESSION['is_event_allowed']         = $Yes;

					$_SESSION['is_dont_miss_event_allowed'] = $Yes;

					$_SESSION['is_event_of_day_allowed']  = $Yes;

					$_SESSION['is_seat_allowed']          = $Yes;

					$_SESSION['is_ticket_allowed']        = $Yes;

					$_SESSION['is_user_allowed']          = $Yes;

					$_SESSION['is_partner_allowed']       = $Yes;

					$_SESSION['is_cms_allowed']           = $Yes;

					$_SESSION['is_payment_type_allowed']  = $Yes;

					$_SESSION['is_community_allowed']     = $Yes;

					$_SESSION['is_community_page_allowed'] = $Yes;

					$_SESSION['is_order_allowed']         = $Yes;

					$_SESSION['is_ticket_selling_allowed'] = $Yes;

					$_SESSION['is_setting_allowed']        = $Yes;

					$_SESSION['is_coupon_allowed']        = $Yes;

					$_SESSION['is_reports_allowed']        = $Yes;

					$_SESSION['is_subscribers_allowed']        = $Yes;

					

					if( $user->id > 1){

					    $role_modules = Models\RoleAllowedModules::where('role_id', '=', $user->role_id)->

		                selectRaw('GROUP_CONCAT( CONCAT(role_id,"_",module_id,"_",function_id) ) as role_mdoule_function')->

					    orderBy('id', 'ASC')->get();

						$allowed_modules =  explode(',', $role_modules[0]->role_mdoule_function);

						//ddump($allowed_modules); exit;

						$Yes = 'Y';					    

						/* === START : Dashboard  === */

						 $dashboard_view_val = $roleID .'_1_4'; 

						 if( in_array($dashboard_view_val, $allowed_modules) ){

							 $_SESSION['is_dashboard_view'] = $Yes;

						 }

						/* === END   : Dashboard  === */

						

						

						/* === START : Advertisements  === */

						 $advertisement_add_val  = $roleID.'_2_1'; // Advertisement Add 

						 $advertisement_edit_val = $roleID.'_2_2'; // Advertisement Edit

						 $advertisement_del_val  = $roleID.'_2_3'; // Advertisement Delete

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

						 $city_add_val  = $roleID.'_3_1';  // City Add

						 $city_edit_val = $roleID.'_3_2'; // City Edit

						 $city_del_val  = $roleID.'_3_3'; // City Delete

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

						 $currency_add_val  = $roleID.'_4_1';  // Currency Add

						 $currency_edit_val = $roleID.'_4_2'; // currency Edit

						 $currency_del_val  = $roleID.'_4_3'; // Currency Delete

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

						 $slider_add_val  = $roleID.'_5_1';  // Sliders Add

						 $slider_edit_val = $roleID.'_5_2'; // Sliders Edit

						 $slider_del_val  = $roleID.'_5_3'; // Sliders Delete

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

						 $cat_page_slider_add_val  = $roleID.'_6_1';  // Category Page Slider Add

						 $cat_page_slider_edit_val = $roleID.'_6_2'; // Category Page Slider Edit

						 $cat_page_slider_del_val  = $roleID.'_6_3'; // Category Page Slider Delete

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

						 $auditorium_del_val    = $roleID.'_8_3';

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

						 $member_del_val    = $roleID.'_11_3';

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

						 $ticket_view    = $roleID.'_19_4';

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

						 

						 if( in_array($ticket_view, $allowed_modules) ){

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

				

					if($profile_picture == '' || $profile_picture === null || !file_exists(ADMIN_ROOT_PATH.'/thumbs/'.$profile_picture) ){

						$profile_pic_with_path = DEFAULT_PROFILE_IMG;

					 }else{

						$profile_pic_with_path = ADMIN_WEB_PATH.'/thumbs/'.$profile_picture;

					 }

				}else{

					$_SESSION['isProductor'] = 'Okay';

					 if($profile_picture == '' || $profile_picture === null || !file_exists(PRODUCTOR_ROOT_PATH.'/thumbs/'.$profile_picture) ){

					$profile_pic_with_path = DEFAULT_PROFILE_IMG;

					 }else{

						$profile_pic_with_path = PRODUCTOR_WEB_PATH.'/thumbs/'.$profile_picture;

					 }

				}

				$_SESSION['profile_pic'] = $profile_pic_with_path;

				return true;

			}else{

				return false;

			}

		}else{

			// Look for the posted email and password for Operator

			$operator = Operators::where('op_email', $email)->first();

			if(!$operator) {

				return false;

			}

			$check_password = $this->checkPasswordIsValid($password,$operator->password);

			if($check_password===true) {

				$_SESSION['isAdmin'] = 'Okay';

				$_SESSION['adminId'] = $operator->op_id;

				$_SESSION['adminName'] = $operator->op_fullname;

				$_SESSION['adminEmail'] = $operator->op_email;

				$_SESSION['isOperator'] = 'Okay';

				$profile_pic_with_path = DEFAULT_PROFILE_IMG;

				$_SESSION['profile_pic'] = $profile_pic_with_path;	

				return true;			

			}else{

				return false;

			}

		   

		}

	}

	

	// Function to log in as

	public function LogInAs($admin_user_id){

		$user = User::where('id', '=', $admin_user_id)->where('type', '=', 'Admin')->first();

		if(!$user) {

			return false;

		}else{

				$_SESSION['isAdmin'] = 'Okay';

				$_SESSION['adminId'] = $user->id;

				$_SESSION['adminRoleId'] = $user->role_id;

				$_SESSION['adminName'] = $user->name;

				$_SESSION['adminEmail'] = $user->email;

				$profile_picture = $user->user_picture;

				

				$user_type = $user->type;

				

					if($profile_picture == '' || $profile_picture === null || !file_exists(ADMIN_ROOT_PATH.'/thumbs/'.$profile_picture) ){

						$profile_pic_with_path = DEFAULT_PROFILE_IMG;

					 }else{

						$profile_pic_with_path = ADMIN_WEB_PATH.'/thumbs/'.$profile_picture;

					 }

				$_SESSION['profile_pic'] = $profile_pic_with_path;

				return true;

			

		}

	}

	

	public function checkPasswordIsValid($password,$passwordDb) {

		if (password_verify($password, $passwordDb)) {

			return true;

		} else {

			return false;

		}

	}

	

	// Check user password

	public function checkUserPassword($admin_id, $password){

		$user = User::where('id', $admin_id)->first();

		if(!$user) {

			return false;

		}

		$check_password = $this->checkPasswordIsValid($password,$user->password);

		if($check_password===true) {

			return true;

		}else{

		   return false;	

		}

	 }

	 

	 // Check operator password

	public function checkOperatorPassword($op_id, $password){

		$opUser = Operators::where('op_id', $op_id)->first();

		if(!$opUser) {

			return false;

		}

		$check_password = $this->checkPasswordIsValid($password,$opUser->password);

		if($check_password===true) {

			return true;

		}else{

		   return false;	

		}

	 }

	 

	 // Change password

	 public function changePassword($password){

		  return password_hash($password, PASSWORD_BCRYPT);

	 }

	 

	public	function userattempt($email, $password)

	{

				$user = User::where('email', $email)->first();

				$usermetaname=Usermeta::where('user_id',$user->id)->first();

				if (!$user)

				{

							return false;

				}



				$check_password = $this->checkPasswordIsValid($password, $user->password);

				if ($check_password === true)

				{

							$_SESSION['isMember'] = 'Okay';

							$_SESSION['memberId'] = $user->id;

							$_SESSION['memberName'] = $user->name;

							$_SESSION['memberFirstn'] = $usermetaname->first_name;

							$_SESSION['memberEmail'] = $user->email;

							return true;

				}

				else

				{

							return false;

				}

	}

	 

	

	 

	 

	 

	 

}