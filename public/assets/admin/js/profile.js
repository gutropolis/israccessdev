var host_name = location.hostname;
var host_name_variable = location.protocol+'//'+location.hostname;
if(host_name == 'localhost'){
 var base_url_constant = 'http://localhost/event_site_v1/public/admin/';
}else{
 var base_url_constant = host_name_variable+'/admin/';	
}

var t;
//$("#m_form_status, #m_form_type").selectpicker()

function newTitle() {
    var selectpicker = $(".selectpicker");
    selectpicker.selectpicker();
    selectpicker.selectpicker({title: 'Change Language'}).selectpicker('render');
    html = 'Change Language';
    selectpicker.html(html);
    selectpicker.selectpicker("refresh");
}

// Admin to Edit
function edit_admin(admin_id){
	
	$('#form_add_admin_e')[0].reset(); // reset form on modals
    //Ajax Load data from ajax
    $.ajax({
        url : base_url_constant+"profile/edit/" +admin_id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
            $('#id_admin').val(data['id']);
			$('#admin_name_e').val(data['name']);
			$('#admin_username_e').val(data['username']);
			$('#admin_email_e').val(data['email']);
			$('#admin_user_picture_old').val(data['user_picture']);
			var profile_pic_with_path_show = data['profile_pic_with_path_show'];
			if(profile_pic_with_path_show == 'Yes')
            {
                $('#label-photo-admin-profile-edit').text(CHANGE_PIC_TXT); // label photo upload
                $('#photo-preview-edit-profile div').html('<img src="'+data['profile_pic_with_path']+'" class="img-rounded img-thumbnail" height=100 width=100>'); // show photo
                
            }else
            {
				 $('#label-photo-admin-profile-edit').text(UPLOAD_PIC_LABEL_TXT);
                 $('#photo-preview-edit-profile div').text(NO_PHOTO_TEXT);
            }
			$('#modal-user-profile').modal('show'); // show the model
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}

// Load Password change popup
function load_password_popup(admin_id){
   	$('#form_password_admin_e')[0].reset(); // reset form on modals
	$('#id_admin_pass').val(admin_id);
	$('#modal-admin-password').modal('show'); // show the model
}

// Update logged in admin data
function updateAdmin(){
	var div_id_or_class = '#mesg_div_admin_profile';
	var email_add = $("#artist_email_e").val();
	if($("#admin_name_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(NAME_MSG_TXT);
		hideErrorDiv(div_id_or_class);
		$("#admin_name_e").focus();
		return false;
	}
    
	if($("#admin_username_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(USERNAME_MSG_TXT);
		hideErrorDiv(div_id_or_class);
		$("#admin_username_e").focus();
		return false;
	}
	
	if($("#admin_email_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(EMAIL_MSG_TXT);
		hideErrorDiv(div_id_or_class);
		$("#admin_email_e").focus();
		return false;
	}
	
	/*if(email_add !="" && !validateEmailAddress(email_add))
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter valid email address.');
		hideErrorDiv(div_id_or_class);
		$("#admin_email_e").focus();
		return false;
	}*/
	var url;
    url = base_url_constant+"profile/update";
   var formData = new FormData($('#form_add_admin_e')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
		
		if(data.status == 'error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'duplicate'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-user-profile').modal('hide');
			    location.reload();	
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;
}


// Change admin password
function updateAdminPassword(){
	var div_id_or_class = '#mesg_div_admin_pass';
	if($("#current_pass").val()=="")
	{
		$("#current_pass").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_cp_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
    
	if($("#new_pass").val()=="")
	{
		$("#new_pass").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_np_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($("#new_pass_confirm").val()=="")
	{
		$("#new_pass_confirm").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_cnp_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($("#new_pass_confirm").val() !="")
	{
		if($("#new_pass_confirm").val() != $("#new_pass").val() ){
			$("#new_pass_confirm").focus();
			$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_same_msg_txt);
			hideErrorDiv(div_id_or_class);
			return false;
		}
	}
	
	
	var url;
    url = base_url_constant+"profile/changePassword";
   var formData = new FormData($('#form_password_admin_e')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
		
		if(data.status == 'error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'duplicate'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-admin-password').modal('hide');
			    location.reload();	
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;
}

/** ===================   Operator Section =====================  **/


// Admin to Edit
function edit_operator(admin_id){
	
	$('#form_add_op_e')[0].reset(); // reset form on modals
    //Ajax Load data from ajax
    $.ajax({
        url : base_url_constant+"../operator/profile/" +admin_id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
            $('#op_id').val(data['op_id']);
			$('#op_fullname').val(data['op_fullname']);
			$('#op_fname').val(data['op_fname']);
			$('#op_lname').val(data['op_lname']);
			$('#op_email').val(data['op_email']);
			$('#op_phone').val(data['op_phone']);
			
			$('#modal-operator-profile').modal('show'); // show the model
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}
// Update logged in admin data
function updateOperator(){
	var div_id_or_class = '#mesg_div_operator_profile';
	var email_add = $("#artist_email_e").val();
	if($("#op_fullname").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter your full name');
		hideErrorDiv(div_id_or_class);
		$("#op_fullname").focus();
		return false;
	}
    
	if($("#op_fname").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter your first name');
		hideErrorDiv(div_id_or_class);
		$("#op_fname").focus();
		return false;
	}
	
	if($("#op_lname").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter your last name');
		hideErrorDiv(div_id_or_class);
		$("#op_lname").focus();
		return false;
	}
	
	if($("#op_email").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter your email');
		hideErrorDiv(div_id_or_class);
		$("#op_email").focus();
		return false;
	}
	
	if($("#op_phone").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter your phone number');
		hideErrorDiv(div_id_or_class);
		$("#op_phone").focus();
		return false;
	}
	
	var url;
    url = base_url_constant+"../operator/profile/update";
   var formData = new FormData($('#form_add_op_e')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
		
		if(data.status == 'error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'duplicate'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-operator-profile').modal('hide');
			    location.reload();	
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;
}

// Change admin password
function updateOpPassword(){
	var div_id_or_class = '#mesg_div_op_pass';
	if($("#current_pass_op").val()=="")
	{
		$("#current_pass_op").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_cp_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
    
	if($("#new_pass_op").val()=="")
	{
		$("#new_pass_op").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_np_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($("#new_pass_confirm_op").val()=="")
	{
		$("#new_pass_confirm_op").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_cnp_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($("#new_pass_confirm_op").val() !="")
	{
		if($("#new_pass_confirm_op").val() != $("#new_pass_op").val() ){
			$("#new_pass_confirm_op").focus();
			$(div_id_or_class).show().addClass('alert alert-danger').html(change_pass_same_msg_txt);
			hideErrorDiv(div_id_or_class);
			return false;
		}
	}
	
	
	var url;
    url = base_url_constant+"../operator/profile/changePassword";
   var formData = new FormData($('#form_password_op_e')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
		
		if(data.status == 'error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'duplicate'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-op-password').modal('hide');
			    location.reload();	
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;
}

// Load Password change popup
function load_op_password_popup(admin_id){
   	$('#form_password_op_e')[0].reset(); // reset form on modals
	$('#id_op_pass').val(admin_id);
	$('#modal-op-password').modal('show'); // show the model
}

// Valid email address
function validateEmailAddress(emailAddress) {
    /*var x = emailAddress;
    var atpos = x.indexOf("@");
    var dotpos = x.lastIndexOf(".");
    if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
        return false;
    }else{
	    return true;	
	}*/
}
//Hide Error Dive
function hideErrorDiv(div_id_or_class){
  setTimeout(function(){
    $(div_id_or_class).delay(2000).slideUp(200); 
 }, 2000);
}
/*
function containsWord(haystack, needle) {
    return haystack.indexOf(needle) !== -1;
}
jQuery.isSubstring = function(haystack, needle) {
    return haystack.indexOf(needle) !== -1;
};
*/
// For Bulk things.
function edit_selected_rows_fields(selected_field)
{   
    var modal_title = false;
	var table_name = $('#table_name').val();
	
	if(selected_field == "delete_selected"){
		modal_title = DELETE_SELECTED_ROWS_TXT;
	}
	if(selected_field == "update"){
		modal_title = UPDATE_SELECTED_ROWS_TXT;
	}
	if(selected_field == "change_status"){
		modal_title = ALL_DELETE_SELECTED_ROWS_TXT;
	}
	   $('.editmodaltitle').html(modal_title);
	   var checkboxValues = [];
		$('.m-checkbox>input, .m-radio>input').each(function() 
		{    
			if($(this).is(':checked'))
			{
			  checkboxValues.push($(this).val());
			}
		});
		if(checkboxValues.length > 0){
		var urlquerystring ='?'
		for (var i = 0; i < checkboxValues.length; i++) {
			urlquerystring += 'lead_id_'+checkboxValues[i]+'='+checkboxValues[i]+'&';
		}
		
		if(selected_field != ''){
	    $('#with_selected_options_popup').modal('show');
		var postData={ selected_field:selected_field, table_name : table_name}
			$.ajax({
			type: 'POST',
			data: postData,
			url: base_url_constant+'display_selected_option_popup'+urlquerystring, 
			success: function(result){
				$('.updatetext').html(result);
			}});

	 }
	 
	}
}

function update_options_bulk_data(){ 
       var table_id = $('#datable_table_id').val();
	    $.ajax({
		type: 'POST',
		dataType: "JSON",
		data: $('#form_edit_bulk_data').serialize(),
		url: base_url_constant+'edit_bulk_data', 
		success: function(data){
		  if(data.status){
			 $("input:checkbox").prop('checked', false);
			 $(".first").prop("checked", false);
			 $('#selected_fields_edit').val('');
		     $('#with_selected_options_popup').modal('hide');
			 if(table_id == 'm_datatable_group_events'){
				 location.reload();
			 }else{
			    reloadTable();
			 }
		  }
		}});
}

function reloadTable()
{
    t.reload(); //reload datatable ajax 
}

function changeLang(sel)
{
	var langId = sel.value;
	 $.ajax({
		type: 'POST',
		dataType: "JSON",
		data: '',
		url: base_url_constant+'adminLanguage/'+langId, 
		success: function(data){
		  if(data.status){
			 	 location.reload();
		  }
		}});
}
















