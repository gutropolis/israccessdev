<?php 



/*-------------------------------------------------------------------



|                  Admin Pages Routes  By Amin                       |



---------------------------------------------------------------------*/



$app->get("/admin", "App\Controllers\AdminLoginController:checkAccess");



$app->get("/admin/", "App\Controllers\AdminLoginController:checkAccess");



$app->get("/admin/login", "App\Controllers\AdminLoginController:login");



$app->post("/admin/check_login", "App\Controllers\AdminLoginController:signIn");







$app->group('/admin', function () use ($app) { 



     // Dashboard + general admin routes



     $app->get("/dashboard", "App\Controllers\AdminDashboardController:dashboard");



	 



	 $app->get("/orderCSV", "App\Controllers\AdminDashboardController:orderCSV");



	 



	 



	 



	 /* ============= START  Data Migration ============= */



	 // This will update the correct event_category_id and the row_id in the orderitems table



	  $app->get("/step1", "App\Controllers\AdminMigrateDataController:step1");



	  // This will copy the order items seats to the new table with customer_id, booking datetime and status



	  $app->get("/step2", "App\Controllers\AdminMigrateDataController:step2");



	  // This will copy all the current empty seats from the event seat categories + row seats table



	  $app->get("/step3", "App\Controllers\AdminMigrateDataController:step3");



	  // This will update ids in the orderitems table for the already saved data



	  $app->get("/step4", "App\Controllers\AdminMigrateDataController:step4");



	  



	  



	  $app->get("/downloadOrdersDataCSV", "App\Controllers\AdminMigrateDataController:downloadOrdersDataCSV");



	 



	 /* ============= END  Data Migration ================ */



	 



	 $app->post("/dashboard/auto_suggestion", "App\Controllers\AdminDashboardController:auto_suggestion");



	 $app->get("/updateTitles", "App\Controllers\AdminDashboardController:updateTitles");



	 $app->get("/download-ticket/{order}/{u}", "App\Controllers\BookingController:downloadPDF");



	 $app->get("/dashboard/getOrder/{id}", "App\Controllers\AdminDashboardController:getOrder");



	 $app->post("/dashboard/dashboardOrdersList", "App\Controllers\AdminDashboardController:dashboardOrdersList");



	 $app->get("/dashboard/getGroupEventsList/{id}", "App\Controllers\AdminDashboardController:getGroupEventsList");



	 $app->get("/dashboard/getGroupEventsListing/{id}", "App\Controllers\AdminDashboardController:getGroupEventsListing");



	 $app->get("/dashboard/getEventSaleReport/{id}", "App\Controllers\AdminDashboardController:getEventSaleReport");



	 $app->get("/dashboard/getEventSaleReportTest/{id}", "App\Controllers\AdminDashboardController:getEventSaleReportD");



	 $app->get("/dashboard/downloadPDF/{from_date}/{to_date}", "App\Controllers\AdminDashboardController:downloadPDF");



	 $app->get("/dashboard/downloadCSV/{from_date}/{to_date}", "App\Controllers\AdminDashboardController:downloadCSV");



	 $app->get("/dashboard/downloadSaleReportPDF/{event_group_id}/{event_id}", "App\Controllers\AdminDashboardController:downloadSaleReportPDF");



	 $app->get("/dashboard/downloadSaleReportCSV/{event_group_id}/{event_id}", "App\Controllers\AdminDashboardController:downloadSaleReportCSV");



	 $app->get("/dashboard/downloadSaleReportXLS/{event_group_id}/{event_id}", "App\Controllers\AdminDashboardController:downloadSaleReportXLS");



	 $app->get("/profile/edit/{id}", "App\Controllers\AdminLoginController:getProfile");



	 $app->post("/display_selected_option_popup", "App\Controllers\AdminController:display_selected_option_popup");



	 $app->post("/edit_bulk_data", "App\Controllers\AdminController:edit_bulk_data");



	 $app->post("/profile/update", "App\Controllers\AdminLoginController:updateProfile");



	 $app->get("/settings", "App\Controllers\AdminController:settings");



	 $app->post("/save_settings", "App\Controllers\AdminController:save_settings");



	 $app->post("/updateSiteMode", "App\Controllers\AdminController:updateSiteMode");



	 $app->post("/profile/changePassword", "App\Controllers\AdminLoginController:changePassword");



	 $app->post("/adminLanguage/{langId}", "App\Controllers\AdminController:adminLanguage");



	 



	 // System Modules



	 $app->get("/system_modules", "App\Controllers\AdminSystemModulesController:system_modules");



	 $app->post("/getAjaxSystemModulesList", "App\Controllers\AdminSystemModulesController:ajaxSystemModuleList"); 



	 $app->get("/system_modules/edit/{id}", "App\Controllers\AdminSystemModulesController:getSystemModuleById");



	 $app->post("/system_modules/update", "App\Controllers\AdminSystemModulesController:updateSystemModule");



	 $app->post("/system_modules/updateSystemModuleStatus", "App\Controllers\AdminSystemModulesController:updateStatus");



	 



	 



	 // System Roles



	 $app->get("/system_roles", "App\Controllers\AdminSystemRolesController:system_roles");



	 $app->post("/getAjaxSystemRolesList", "App\Controllers\AdminSystemRolesController:getAjaxSystemRolesList");



	 $app->post("/saveRole", "App\Controllers\AdminSystemRolesController:saveRole"); 



	 $app->get("/system_roles/edit/{id}", "App\Controllers\AdminSystemRolesController:getSystemRoleById");



     $app->post("/system_roles/update", "App\Controllers\AdminSystemRolesController:updateRole");



	 $app->get("/system_roles/assign_modules/{id}", "App\Controllers\AdminSystemRolesController:assignModules");



	 $app->post("/system_roles/saveRoleModule", "App\Controllers\AdminSystemRolesController:saveRoleModule");



	 $app->post("/system_roles/removeRoleModule", "App\Controllers\AdminSystemRolesController:removeRoleModule");



	 



	 // All cities routes 



	 $app->get("/cities", "App\Controllers\AdminCityController:cities");



	 $app->post("/saveCity", "App\Controllers\AdminCityController:saveCity");



	 $app->get("/cities/edit/{id}", "App\Controllers\AdminCityController:getCityById");



	 $app->post("/cities/update", "App\Controllers\AdminCityController:updateCity");



	 $app->get("/cities/delete/{id}", "App\Controllers\AdminCityController:deleteCityById");



	 $app->post("/getAjaxCitiesList", "App\Controllers\AdminCityController:ajaxCityList"); 



	 $app->post("/getStates", "App\Controllers\AdminCityController:getStates");



	 



	 



	 // All category routes 



	 $app->get("/categories", "App\Controllers\AdminCategoryController:categories");



	 $app->post("/saveCategory", "App\Controllers\AdminCategoryController:saveCategory");



	 $app->get("/categories/edit/{id}", "App\Controllers\AdminCategoryController:getCategoryById");



	 $app->post("/categories/update", "App\Controllers\AdminCategoryController:updateCategory");



	 $app->get("/categories/delete/{id}", "App\Controllers\AdminCategoryController:deleteCategoryById");



	 $app->post("/getAjaxCategoriesList", "App\Controllers\AdminCategoryController:ajaxCategoriesList"); 



	 // For Re-Order



	 $app->get("/categories/getEventGroupsList/{id}", "App\Controllers\AdminCategoryController:getEventGroupsList");



	 $app->post("/categories/reOrderEventGroups", "App\Controllers\AdminCategoryController:reOrderEventGroups"); 



	 



	 



	 // All auditorium routes



	 $app->get("/auditoriums", "App\Controllers\AdminAuditoriumController:auditoriums");



	 $app->post("/getAjaxAuditoriumsList", "App\Controllers\AdminAuditoriumController:getAjaxAuditoriumsList"); 



	 $app->get("/auditoriums/add", "App\Controllers\AdminAuditoriumController:add");



	 $app->post("/saveAuditorium", "App\Controllers\AdminAuditoriumController:saveAuditorium");



	 $app->get("/auditoriums/getAuditoriumMap/{id}", "App\Controllers\AdminAuditoriumController:getAuditoriumMapById");


	 $app->get("/auditoriums/editDigitalMap/{id}", "App\Controllers\AdminAuditoriumController:editDigitalMap");



	 $app->get("/auditoriums/edit/{id}", "App\Controllers\AdminAuditoriumController:getAuditoriumById");



	 $app->post("/auditoriums/update", "App\Controllers\AdminAuditoriumController:updateAuditorium");



	 $app->get("/auditoriums/delete/{id}", "App\Controllers\AdminAuditoriumController:deleteAuditoriumById");



	 $app->get("/auditoriums/deleteAudSeats/{id}", "App\Controllers\AdminAuditoriumController:deleteAudSeatsById");



	 $app->get("/auditoriums/getAuditoriumSeatMap/{id}", "App\Controllers\AdminAuditoriumController:getAuditoriumSeatMapById");



	 $app->get("/auditoriums/getAuditoriumSeatMapEvent/{id}/{event_id}", "App\Controllers\AdminAuditoriumController:getAuditoriumSeatMapEventById");



	 $app->get("/auditorium/audiJsonMap/{auditorium_id}", "App\Controllers\AdminAuditoriumController:audiJsonMap");

	 
	 $app->post("/auditorium/saveAuditoriumDigitalMap", "App\Controllers\AdminAuditoriumController:saveAuditoriumDigitalMap");



	 $app->get("/auditorium/audiBookings", "App\Controllers\AdminAuditoriumController:audiBookings");



	 $app->get("/saveAudiMap", "App\Controllers\AdminAuditoriumController:saveAudiMap");











 



	 // All artists routes



	 $app->get("/artists", "App\Controllers\AdminArtistController:artists");



	 $app->post("/saveArtist", "App\Controllers\AdminArtistController:saveArtist");



	 $app->get("/artists/edit/{id}", "App\Controllers\AdminArtistController:getArtistById");



	 $app->post("/artists/update", "App\Controllers\AdminArtistController:updateArtist");



	 $app->get("/artists/delete/{id}", "App\Controllers\AdminArtistController:deleteArtistById");



	 $app->post("/getAjaxArtistsList", "App\Controllers\AdminArtistController:getAjaxArtistsList"); 



	 



	  // All members routes



	 $app->get("/members", "App\Controllers\AdminMemberController:members");



	 $app->post("/saveMember", "App\Controllers\AdminMemberController:saveMember");



	 $app->get("/members/edit/{id}", "App\Controllers\AdminMemberController:getMemberById");



	 $app->get("/members/view/{id}", "App\Controllers\AdminMemberController:viewMemberById");



	 $app->post("/members/update", "App\Controllers\AdminMemberController:updateMember");



	 $app->get("/members/delete/{id}", "App\Controllers\AdminMemberController:deleteMemberById");



	 $app->post("/getAjaxMembersList", "App\Controllers\AdminMemberController:getAjaxMembersList");



	 // Download



	 $app->get("/members/downloadMembersCSV", "App\Controllers\AdminMemberController:downloadMembersCSV"); 



	 



	 // All productors routes



	 $app->get("/productors", "App\Controllers\AdminProductorController:productors");



	 $app->post("/saveProductor", "App\Controllers\AdminProductorController:saveProductor");



	 $app->get("/productors/edit/{id}", "App\Controllers\AdminProductorController:getProductorById");



	 $app->post("/productors/update", "App\Controllers\AdminProductorController:updateProductor");



	 $app->get("/productors/delete/{id}", "App\Controllers\AdminProductorController:deleteProductorById");



	 $app->post("/getAjaxProductorsList", "App\Controllers\AdminProductorController:getAjaxProductorsList"); 



	 



	 // All users routes



	 $app->get("/users", "App\Controllers\AdminUserController:users");



	 $app->post("/getAjaxUsersList", "App\Controllers\AdminUserController:getAjaxUsersList");



	 $app->post("/saveUser", "App\Controllers\AdminUserController:saveUser");



	 $app->get("/users/edit/{id}", "App\Controllers\AdminUserController:getAdminUserById");



	 $app->post("/users/update", "App\Controllers\AdminUserController:updateUser");



	 



	 // Log In As



	 $app->get('/log_in_as', "App\Controllers\AdminUserController:log_in_as");



	 $app->get('/getAdminUsersList/{role_id}', "App\Controllers\AdminUserController:getAdminUsersList");



	 $app->get('/getRoleModulesList/{role_id}', "App\Controllers\AdminUserController:getRoleModulesList");



	 $app->post('/do_log_in_as', "App\Controllers\AdminLoginController:do_log_in_as");



	 



	 



	 



	 // All Events Group routes



	 $app->get("/events/groups", "App\Controllers\AdminEventGroupController:eventgroups");



	 $app->get("/events/groups_archived", "App\Controllers\AdminEventGroupController:groups_archived");



	 $app->post("/events/groups/getAjaxEventGroupsArchivedList", "App\Controllers\AdminEventGroupController:getAjaxEventGroupsArchivedList");



	 $app->post("/events/groups/saveEventGroup", "App\Controllers\AdminEventGroupController:saveEventGroup");



	 $app->get("/events/groups/get/{id}", "App\Controllers\AdminEventGroupController:getEventGroupById");



	 $app->get("/events/groups/edit/{id}", "App\Controllers\AdminEventGroupController:editEventGroupById");



	 $app->get("/events/groups/edit_archive/{id}", "App\Controllers\AdminEventGroupController:editEventGroupArchiveById");



	 $app->post("/events/groups/edit/update", "App\Controllers\AdminEventGroupController:updateEventGroup");



	 $app->post("/events/groups/edit/update_archive", "App\Controllers\AdminEventGroupController:updateEventGroupArchive");



	 $app->get("/events/groups/delete/{id}", "App\Controllers\AdminEventGroupController:deleteEventGroupById");



	 $app->post("/events/groups/getAjaxEventGroupsList", "App\Controllers\AdminEventGroupController:getAjaxEventGroupsList"); 



	 $app->post("/events/groups/upload/{id}", "App\Controllers\AdminEventGroupController:uploadImages");



	 $app->post("/events/groups/removeFile/{file_name}", "App\Controllers\AdminEventGroupController:removeFile");



	 $app->get("/events/groups/removeVideoLink/{id}", "App\Controllers\AdminEventGroupController:removeVideoLink");



	 $app->get("/events/groups/edit/deleteGroupComment/{id}", "App\Controllers\AdminEventGroupController:deleteGroupComment");



	 $app->get("/events/groups/duplicate/{id}", "App\Controllers\AdminEventGroupController:duplicateEventGroup");



	 $app->get("/events/groups/updateEventGroupStatus/{id}", "App\Controllers\AdminEventGroupController:updateGroupStatus");



	 $app->get("/events/groups/deleteEventGroupRole/{id}", "App\Controllers\AdminEventGroupController:deleteEventGroupRole");



	 $app->get("/events/groups/removeAdvImage/{id}", "App\Controllers\AdminEventGroupController:removeAdvImage");



	 $app->get("/events/groups/getParmalink/{category_id}", "App\Controllers\AdminEventGroupController:getParmalink");



	 



	 



	 // All operators routes



	 $app->get("/operators", "App\Controllers\AdminOperatorController:operators");



     $app->get("/operators/addoperators", "App\Controllers\AdminOperatorController:addoperators");



     $app->post("/operators/getOperatorsList", "App\Controllers\AdminOperatorController:getOperatorsList");



     $app->post("/operators/saveOperator", "App\Controllers\AdminOperatorController:saveOperator");



     $app->get("/operators/getOperator/{id}", "App\Controllers\AdminOperatorController:getOperatorById");



     $app->post("/operators/updateOperator", "App\Controllers\AdminOperatorController:updateOperator");



     $app->get("/operators/deleteOperator/{id}", "App\Controllers\AdminOperatorController:deleteOperator");



     $app->post("/operators/getAjaxOperatorList", "App\Controllers\AdminOperatorController:getajaxOperatorList");



	 $app->post("/operators/resetOperatorPass", "App\Controllers\AdminOperatorController:resetOperatorPass");



	 



	 



	 // All Events routes



	 $app->get("/events", "App\Controllers\AdminEventController:events");



	 $app->post("/events/saveEvent", "App\Controllers\AdminEventController:saveEvent");



	 $app->get("/events/get/{id}", "App\Controllers\AdminEventController:getEventById");



	 $app->get("/events/edit/{id}", "App\Controllers\AdminEventController:editEventById");



	 $app->post("/events/update", "App\Controllers\AdminEventController:updateEvent");



	 $app->get("/events/deleteEventPic/{id}", "App\Controllers\AdminEventController:deleteEventPictureById");



	 $app->get("/events/deleteEventTime/{id}", "App\Controllers\AdminEventController:deleteEventTimeById");



	 $app->get("/events/deleteEventTicket/{id}", "App\Controllers\AdminEventController:deleteEventTicketById");



	 $app->get("/events/delete/{id}", "App\Controllers\AdminEventController:deleteEventById");



	 $app->get("/events/updateEventStatus/{id}/{status}", "App\Controllers\AdminEventController:updateEventStatus");



	 $app->post("/events/getAjaxEventsList", "App\Controllers\AdminEventController:getAjaxEventsList"); 



	 $app->get("/events/deleteEventRole/{id}", "App\Controllers\AdminEventController:deleteEventRoleById");



	 $app->get("/events/eventMap/{id}", "App\Controllers\AdminEventController:eventMapPage");



	 $app->get("/events/eventMapAdd/{id}", "App\Controllers\AdminEventController:eventMapAdd");



	 $app->get("/events/eventMapEdit/{id}", "App\Controllers\AdminEventController:eventMapEdit");



	 



	 $app->get("/events/eventDigitalMapEdit/{id}", "App\Controllers\AdminEventController:eventDigitalMapEdit");


     $app->get("/events/digitalseatlisting/{id}" , "App\Controllers\AdminEventController:digitalSeatListing"); //new listing requested as alternative to modify seat datas


     $app->get("/events/updateseatalone/{id}" , "App\Controllers\AdminEventController:updateDigitalSeat"); //new listing requested as alternative to modify seat datas


	 $app->post("/saveAuditoriumDigitalMap", "App\Controllers\AdminEventController:saveAuditoriumDigitalMap");



	 $app->get("/events/eventJsonMap/{event_id}", "App\Controllers\AdminEventController:eventJsonMap");



      



	 $app->post("/events/saveEventSeatTicketMap", "App\Controllers\AdminEventController:saveEventSeatTicketMap");



	 $app->post("/events/updateEventSeatTicketMap", "App\Controllers\AdminEventController:updateEventSeatTicketMap");



	 $app->post("/events/getAjaxEventMapList", "App\Controllers\AdminEventController:getAjaxEventMapList");



	 $app->get("/events/deleteEventSeat/{id}", "App\Controllers\AdminEventController:deleteEventSeatById");



	 $app->get("/events/deleteEventSeatRow/{id}", "App\Controllers\AdminEventController:deleteEventSeatRowById");



	 $app->get("/events/deleteRowSeat/{id}/{row_number}", "App\Controllers\AdminEventController:deleteRowSeatById");



	 $app->get("/events/getEventSeatCategories/{id}", "App\Controllers\AdminEventController:getEventSeatCategories");



	 $app->get("/events/getEventSeatCategoryRows/{id}", "App\Controllers\AdminEventController:getEventSeatCategoryRows");



	 $app->get("/events/getCategoryRowSale/{event_ticket_category_id}/{ticket_row_id}", "App\Controllers\AdminEventController:getCategoryRowSaleReport");



	 $app->get("/events/downloadSaleReportPDF/{event_ticket_category_id}/{ticket_row_id}/{event_id}", "App\Controllers\AdminEventController:downloadSaleReportPDF");



	 $app->get("/events/checkRowSeat/{id}/{row_number}", "App\Controllers\AdminEventController:checkRowSeat");



	 $app->get("/events/checkEventSeatRow/{id}", "App\Controllers\AdminEventController:checkEventSeatRow");



	 $app->get("/events/removeEventAdvImage/{id}", "App\Controllers\AdminEventController:removeEventAdvImage");



	 $app->get("/events/saveNewRowSeat/{event_id}/{cat_id}/{row_id}/{table_id}", "App\Controllers\AdminEventController:saveNewRowSeat");



	 // For row having no seat



	 $app->get("/events/saveNewRowSeatFresh/{event_id}/{cat_id}/{row_id}", "App\Controllers\AdminEventController:saveNewRowSeatFresh");



	 $app->get("/events/removeNewRowSeat/{id}", "App\Controllers\AdminEventController:removeNewRowSeat");



	 $app->post("/events/changeNewRowSeatUpdate", "App\Controllers\AdminEventController:changeNewRowSeatUpdate");



	 $app->get("/events/changeSeatRowMode/{id}/{mode}", "App\Controllers\AdminEventController:changeSeatRowMode");



	 $app->get("/events/getSeatHistory/{seat_id}", "App\Controllers\AdminEventController:getSeatHistory");



	 // Add New Row



	 $app->get("/events/createNewRow/{event_id}/{cat_id}/{row_id}/{table_id}", "App\Controllers\AdminEventController:createNewRow");



	 $app->post("/events/saveNewRowSeatUpdate", "App\Controllers\AdminEventController:saveNewRowSeatUpdate");



	 // Add New Mutliple Seats



	 $app->get("/events/saveNewMultipleSeatsFresh/{event_id}/{cat_id}/{row_id}/{table_id}/{seat_number}", "App\Controllers\AdminEventController:saveNewMultipleSeatsFresh");



	 $app->post("/events/saveRowMultipleSeatsUpdate", "App\Controllers\AdminEventController:saveRowMultipleSeatsUpdate");



	 // Event Coupon

	 $app->get("/events/getEventCoupon/{event_id}", "App\Controllers\AdminEventController:getEventCoupon");

	 $app->post("/events/saveEventCoupon", "App\Controllers\AdminEventController:saveEventCoupon");

	 $app->get("/events/removeEventCoupon/{coupon_id}", "App\Controllers\AdminEventController:removeEventCoupon");



	 



	 // Dont Miss page



	  $app->get("/dont-miss", "App\Controllers\AdminEventController:eventsDontMiss");



	  $app->post("/getAjaxEventDontMissList", "App\Controllers\AdminEventController:ajaxDontMissEventsList"); 



	  $app->get("/event-day", "App\Controllers\AdminEventController:eventsOfDay");



	  $app->post("/getAjaxEventOfDayList", "App\Controllers\AdminEventController:ajaxEventsOfDayList");



	  



	  // Archived Events



	   $app->get("/archived_events", "App\Controllers\AdminEventController:archived_events");



	  $app->post("/getAjaxArchivedEventList", "App\Controllers\AdminEventController:ajaxArchivedEventsList");



	  



	     



	 // All slider routes 



	 $app->get("/sliders", "App\Controllers\AdminSliderController:sliders");



	 $app->post("/saveSlider", "App\Controllers\AdminSliderController:saveSlider");



	 $app->get("/sliders/edit/{id}", "App\Controllers\AdminSliderController:getSliderById");



	 $app->post("/sliders/update", "App\Controllers\AdminSliderController:updateSlider");



	 $app->get("/sliders/delete/{id}", "App\Controllers\AdminSliderController:deleteSliderById");



	 $app->post("/getAjaxSlidersList", "App\Controllers\AdminSliderController:ajaxSlidersList"); 



	 



	 // All advertisement routes 



	 $app->get("/advertisements", "App\Controllers\AdminAdvertisementController:advertisements");



	 $app->post("/saveAdd", "App\Controllers\AdminAdvertisementController:saveAd");



	 $app->get("/advertisements/edit/{id}", "App\Controllers\AdminAdvertisementController:getAdById");



	 $app->post("/advertisements/update", "App\Controllers\AdminAdvertisementController:updateAd");



	 $app->get("/advertisements/delete/{id}", "App\Controllers\AdminAdvertisementController:deleteAdById");



	 $app->post("/getAjaxAdsList", "App\Controllers\AdminAdvertisementController:ajaxAdsList"); 



	 



	 // All category page slider routes 



	 $app->get("/category_page_sliders", "App\Controllers\AdminCategorySliderController:category_page_sliders");



	 $app->post("/saveCategorySlider", "App\Controllers\AdminCategorySliderController:saveCategorySlider");



	 $app->get("/category_page_sliders/edit/{id}", "App\Controllers\AdminCategorySliderController:getCategorySliderById");



	 $app->post("/category_page_sliders/update", "App\Controllers\AdminCategorySliderController:updateCategorySlider");



	 $app->get("/category_page_sliders/delete/{id}", "App\Controllers\AdminCategorySliderController:deleteCategorySliderById");



	 $app->post("/getAjaxCategorySlidersList", "App\Controllers\AdminCategorySliderController:ajaxCategorySlidersList"); 



	 



	 // All partners routes 



	 $app->get("/partners", "App\Controllers\AdminPartnerController:partners");



	 $app->post("/savePartner", "App\Controllers\AdminPartnerController:savePartner");



	 $app->get("/partners/edit/{id}", "App\Controllers\AdminPartnerController:getPartnerById");



	 $app->post("/partners/update", "App\Controllers\AdminPartnerController:updatePartner");



	 $app->get("/partners/delete/{id}", "App\Controllers\AdminPartnerController:deletePartnerById");



	 $app->post("/getAjaxPartnersList", "App\Controllers\AdminPartnerController:ajaxPartnersList"); 



	 



	 



	 // All cms routes 



	 $app->get("/cms", "App\Controllers\AdminCmsController:cms");



	 $app->get("/cms/get/{id}", "App\Controllers\AdminCmsController:getCmsById");



	 $app->post("/cms/update", "App\Controllers\AdminCmsController:updateCms");



	 



	 



	 // All payment types routes 



	 $app->get("/paymentTypes", "App\Controllers\AdminPaymentTypeController:paymentTypes");



	 $app->post("/savePaymentType", "App\Controllers\AdminPaymentTypeController:savePaymentType");



	 $app->get("/paymentTypes/edit/{id}", "App\Controllers\AdminPaymentTypeController:getPaymentTypeById");



	 $app->post("/paymentTypes/update", "App\Controllers\AdminPaymentTypeController:updatePaymentType");



	 $app->get("/paymentTypes/delete/{id}", "App\Controllers\AdminPaymentTypeController:deletePaymentTypeById");



	 $app->post("/getAjaxPaymentTypesList", "App\Controllers\AdminPaymentTypeController:ajaxPaymentTypesList"); 



	 



	 



	 // All communities routes 



	 $app->get("/community", "App\Controllers\AdminCommunityController:communities");



	 $app->post("/saveCommunity", "App\Controllers\AdminCommunityController:saveCommunity");



	 $app->get("/community/edit/{id}", "App\Controllers\AdminCommunityController:getCommunityById");



	 $app->post("/community/update", "App\Controllers\AdminCommunityController:updateCommunity");



	 $app->get("/community/delete/{id}", "App\Controllers\AdminCommunityController:deleteCommunityById");



	 $app->post("/getAjaxCommunitiesList", "App\Controllers\AdminCommunityController:ajaxCommunitiesList"); 



	 



	 



	 // All community page routes 



	 $app->get("/community_page", "App\Controllers\AdminCommunityPageController:community_page");



	 $app->post("/saveCommunityPage", "App\Controllers\AdminCommunityPageController:saveCommunityPage");



	 $app->get("/community_page/add", "App\Controllers\AdminCommunityPageController:add");



	 $app->get("/community_page/edit/{id}", "App\Controllers\AdminCommunityPageController:getCommunityPageById");



	 $app->post("/community_page/update", "App\Controllers\AdminCommunityPageController:updateCommunityPage");



	 $app->get("/community_page/delete/{id}", "App\Controllers\AdminCommunityPageController:deleteCommunityPageById");



	 $app->post("/getAjaxCommunityPageList", "App\Controllers\AdminCommunityPageController:ajaxCommunityPageList"); 



	 



	 



	 // All currencies routes 



	 $app->get("/currencies", "App\Controllers\AdminCurrencyController:currencies");



	 $app->post("/saveCurrency", "App\Controllers\AdminCurrencyController:saveCurrency");



	 $app->get("/currencies/edit/{id}", "App\Controllers\AdminCurrencyController:getCurrenyById");



	 $app->post("/currencies/update", "App\Controllers\AdminCurrencyController:updateCurrency");



	 $app->get("/currencies/delete/{id}", "App\Controllers\AdminCurrencyController:deleteCurrencyById");



	 $app->post("/getAjaxCurrenciesList", "App\Controllers\AdminCurrencyController:ajaxCurrencyList"); 



	 



	 // All orders routes



	 $app->get("/orders", "App\Controllers\AdminOrderController:orders");



	 $app->get("/orders/view/{id}", "App\Controllers\AdminOrderController:getOrderById");



	 $app->get("/orders/delete/{id}", "App\Controllers\AdminOrderController:deleteOrderById");



	 $app->post("/getAjaxOrdersList", "App\Controllers\AdminOrderController:getAjaxOrdersList");



	 // For search



	 $app->post("/getAjaxOrdersListFilter", "App\Controllers\AdminOrderController:getAjaxOrdersListFilter");



	 $app->get("/orders/downloadOrderReportPDF/{id}", "App\Controllers\AdminOrderController:downloadOrderReportPDF");



	 $app->get("/orders/downloadOrderMonthlyReportPDF", "App\Controllers\AdminOrderController:downloadMonthlyReport");



	 // For order Filter



	 $app->get("/orders/getEventsList/{id}", "App\Controllers\AdminOrderController:getEventsList"); 



	 $app->get("/orders/getEventCategoriesList/{id}", "App\Controllers\AdminOrderController:getEventCategoriesList"); 



	 $app->get("/orders/getEventCategoryRowsList/{id}", "App\Controllers\AdminOrderController:getEventCategoryRowsList");



	 $app->get("/orders/downloadEventCSV/{id}", "App\Controllers\AdminOrderController:downloadEventCSV"); 



	 $app->get("/orders/getEventSeatCategoriesList/{order_id}", "App\Controllers\AdminOrderController:getEventSeatCategoriesList");



	 // Seat Change



	 $app->post("/orders/changeSeats", "App\Controllers\AdminOrderController:changeSeats");



	 // Refund order



	 $app->post("/orders/refundOrder", "App\Controllers\AdminOrderController:refundOrder");



	 // Get Seats of an order

	 $app->get("/orders/getOrderSeats/{order_id}", "App\Controllers\AdminOrderController:getOrderSeats"); 

	 $app->get("/orders/replaceOrderSeats/{order_id}/{current_seat_id}/{new_seat_id}", "App\Controllers\AdminOrderController:replaceOrderSeats"); 

	 



	  



	  // Reports Modules



	 $app->get("/reports", "App\Controllers\AdminReportsController:reports");



	 $app->get("/general_data_report", "App\Controllers\AdminReportsController:downloadGeneralDataReportXLS");



	 $app->get("/accounting_report", "App\Controllers\AdminReportsController:downloadAccountingReportXLS");



	 $app->get("/sales_report", "App\Controllers\AdminReportsController:downloadSalesReportXLS");



	 $app->get("/by_productor_report", "App\Controllers\AdminReportsController:downloadByProductorReportXLS");



	 $app->get("/culturaccess_report", "App\Controllers\AdminReportsController:downloadCulturaccessReportXLS");



	 $app->post("/getGeneralReport", "App\Controllers\AdminReportsController:getGeneralReport");



	 $app->post("/getGeneralReportCustomer", "App\Controllers\AdminReportsController:getGeneralReportCustomer");



	 



	 $app->get("/getGeneralReportCSV/{from_date}/{to_date}/{customer_id}", "App\Controllers\AdminReportsController:getGeneralReportCSV");



	 $app->get("/getGeneralReportPDF/{from_date}/{to_date}/{customer_id}", "App\Controllers\AdminReportsController:getGeneralReportPDF");



	 



	 // New Dashboard Reports



	 $app->get("/download_general_data_report/{event_group_id}/{event_id}/{from_date}/{to_date}", "App\Controllers\AdminReportsController:downloadGeneralDataReportXLS");



	 $app->get("/download_accounting_report/{event_group_id}/{event_id}/{from_date}/{to_date}", "App\Controllers\AdminReportsController:downloadAccountingReportXLS");



	 $app->get("/download_sales_report/{event_group_id}/{event_id}/{from_date}/{to_date}", "App\Controllers\AdminReportsController:downloadSalesReportXLS");



	 $app->get("/download_by_productor_report/{event_group_id}/{event_id}/{from_date}/{to_date}", "App\Controllers\AdminReportsController:downloadByProductorReportXLS");



	 $app->get("/download_culturaccess_report/{event_group_id}/{event_id}/{from_date}/{to_date}", "App\Controllers\AdminReportsController:downloadCulturaccessReportXLS");



	 



	 



	 



	 // All Subscribers routes



	 $app->get("/subscribers", "App\Controllers\AdminSubscriberController:subscribers");



	 $app->post("/getAjaxSubscribersList", "App\Controllers\AdminSubscriberController:getAjaxSubscribersList");



	 $app->get("/subscribers/delete/{id}", "App\Controllers\AdminSubscriberController:deleteSubscriberById");



	 // Download



	 $app->get("/subscribers/downloadSubscribersCSV", "App\Controllers\AdminSubscriberController:downloadSubscribersCSV");



	 



	 



	  



	 // All sections routes 



	 $app->get("/sections", "App\Controllers\AdminSectionController:sections");



	 $app->post("/saveSection", "App\Controllers\AdminSectionController:saveSection");



	 $app->get("/sections/edit/{id}", "App\Controllers\AdminSectionController:getSectionById");



	 $app->post("/sections/update", "App\Controllers\AdminSectionController:updateSection");



	 $app->get("/sections/delete/{id}", "App\Controllers\AdminSectionController:deleteSectionById");



	 $app->post("/getAjaxSectionsList", "App\Controllers\AdminSectionController:ajaxSectionsList");



	 // For Re-Order



	 $app->get("/sections/getEventGroupsList/{id}", "App\Controllers\AdminSectionController:getEventGroupsList");



	 $app->post("/sections/reOrderEventGroups", "App\Controllers\AdminSectionController:reOrderEventGroups"); 



	 



	 



	



	 



	 



	 



	 



      // All Selling ticket routes 	



	  $app->get("/sellingticket", "App\Controllers\AdminSellingController:selling");	  



	  $app->post("/sellingticket", "App\Controllers\AdminSellingController:selling");



	  $app->get("/sellingticketdata", "App\Controllers\AdminSellingController:getSellingdata");	



	  $app->post("/sellingticketdata", "App\Controllers\AdminSellingController:getSellingdata");	



	  $app->post("/getAjaxUserList", "App\Controllers\AdminMemberController:getAjaxUserList"); 



	  $app->get("/getEventTime", "App\Controllers\AdminSellingController:getEventTime");	



	  $app->post("/getEventTime", "App\Controllers\AdminSellingController:getEventTime");	 



	  $app->get("/ajaxcallEventOrder", "App\Controllers\AdminSellingController:ajaxcallEventOrder");	



	  $app->post("/ajaxcallEventOrder", "App\Controllers\AdminSellingController:ajaxcallEventOrder");	



	  $app->get("/ajaxcallRawSeat/{rowno}", "App\Controllers\AdminSellingController:ajaxcallRawSeat");  



	  $app->get("/ajaxcallPriceRaw/{id}", "App\Controllers\AdminSellingController:ajaxcallPriceRaw");







	  $app->get("/row-seat-sequence/{rid}/{qtx}/{seatno}", "App\Controllers\AdminSellingController:ajaxcallRawSeatSequence");



	  $app->get("/send-order-ticket/{order}/{uid}", "App\Controllers\AdminSellingController:sendTicketInformationByAdmin");







		//Testing perpose



	  $app->get("/testing", "App\Controllers\AdminSellingController:testing");	



	  $app->post("/testing", "App\Controllers\AdminSellingController:testing");



	  $app->get("/getEventbyGroup", "App\Controllers\AdminSellingController:getEventbyGroup");



	  



	  // All Ticket Routs



	   $app->get("/tickets", "App\Controllers\AdminTicketController:tickets");



	   $app->post("/getAjaxTicketsList", "App\Controllers\AdminTicketController:getAjaxTicketsList");



	   $app->get("/tickets/view/{id}", "App\Controllers\AdminTicketController:getTicketById");



	   



	   



	   



	   // All Coupons routes 



	 $app->get("/coupons", "App\Controllers\AdminCouponController:coupons");



	 $app->post("/saveCoupon", "App\Controllers\AdminCouponController:saveCoupon");



	 $app->get("/coupons/edit/{id}", "App\Controllers\AdminCouponController:getCouponById");



	 $app->post("/coupons/update", "App\Controllers\AdminCouponController:updateCoupon");



	 $app->get("/coupons/delete/{id}", "App\Controllers\AdminCouponController:deleteCouponById");



	 $app->post("/getAjaxCouponsList", "App\Controllers\AdminCouponController:ajaxCouponsList"); 



	 $app->get("/coupons/view/{id}", "App\Controllers\AdminCouponController:viewCouponById");



	 $app->post("/getAjaxCouponHistoryList", "App\Controllers\AdminCouponController:getAjaxCouponHistoryList"); 



	 



	 //validate coupon



       $app->post("/testCoupon", "App\Controllers\AdminSellingController:ValidateCoupon");



	   $app->get("/testCoupon", "App\Controllers\AdminSellingController:ValidateCoupon");

	   

	    //validate email

	

	    $app->post("/checkemail", "App\Controllers\AdminSellingController:checkemail");



	 //Auditorium Sync from event



	   $app->post("/syncfromevent", "App\Controllers\AdminAuditoriumController:SyncFromEvent");



	 //pointing qrcode tickets 

	  $app->get("/pointing/{id}", "App\Controllers\AdminPointingController:LoadList");

	  

	  $app->get("/exportpointing/{event_id}", "App\Controllers\AdminPointingController:downloadXLS" );

	 



})->add('App\Middleware\AdminAuthMiddleware');







/* RRODUCTOR SECTION */



$app->group('/productor', function () use ($app) { 



     // Dashboard + general productor routes



     $app->get("/dashboard", "App\Controllers\AdminProductorDashboardController:dashboard");



	 $app->get("/dashboard/getGroupEventsList/{id}", "App\Controllers\AdminProductorDashboardController:myEvents");



	 $app->get("/dashboard/eventSaleReport/{id}", "App\Controllers\AdminProductorDashboardController:eventSaleReport");



	 $app->get("/dashboard/downloadSaleReportPDF/{event_group_id}/{event_id}", "App\Controllers\AdminProductorDashboardController:downloadSaleReportPDF");



})->add('App\Middleware\ProductorAuthMiddleware');











/* 	OPERATOR SECTION */



$app->group('/operator', function () use ($app) { 



     // Dashboard + general productor routes



     $app->get("/dashboard", "App\Controllers\AdminOperatorDashboardController:dashboard");



	 $app->get("/dashboard/getOrder/{id}", "App\Controllers\AdminOperatorDashboardController:getOrder");



	 $app->post("/dashboard/dashboardOrdersList", "App\Controllers\AdminOperatorDashboardController:dashboardAjaxOrdersList");



	 $app->get("/dashboard/getGroupEventsList/{id}", "App\Controllers\AdminOperatorDashboardController:myEvents");



	 $app->get("/dashboard/eventSaleReport/{id}", "App\Controllers\AdminOperatorDashboardController:eventSaleReport");



	 $app->get("/dashboard/downloadSaleReportPDF/{event_group_id}/{event_id}", "App\Controllers\AdminOperatorDashboardController:downloadSaleReportPDF");



	 



	$app->get("/dashboard/downloadPDF/{from_date}/{to_date}", "App\Controllers\AdminOperatorDashboardController:downloadPDF");



	$app->get("/dashboard/downloadCSV/{from_date}/{to_date}", "App\Controllers\AdminOperatorDashboardController:downloadCSV");



	$app->get("/profile/{id}", "App\Controllers\AdminOperatorDashboardController:getProfile");



	$app->post("/profile/update", "App\Controllers\AdminOperatorDashboardController:updateProfile");



	$app->post("/profile/changePassword", "App\Controllers\AdminOperatorDashboardController:changePassword");



	 



})->add('App\Middleware\OperatorAuthMiddleware');







$app->get("/admin/logout", "App\Controllers\AdminLoginController:logout"); // log out











