var DatatableRemoteAjaxDemoCat = {
	
    init: function() {
       // var t;
        t = $(".m_datatable_users").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getAjaxUsersList",
                        map: function(t) {
                            var e = t;
                            return void 0 !== t.data && (e = t.data), e
                        }
                    }
                },
				saveState: {cookie: false, webstorage: false},
                pageSize: 10,
                serverPaging: true,
				serverFiltering: true,
				serverSorting: true
            },
            layout: {
                scroll: !1,
                footer: !1
            },
            sortable: !0,
            pagination: !0,
            toolbar: {
                items: {
                    pagination: {
                        pageSizeSelect: [10, 20, 30, 50, 100]
                    }
                }
            },
            search: {
                input: $("#generalSearch")
            },
           columns: [{
                field: "id",
                title: "#",
                sortable: !1,
                width: 20,
                selector: !1,
                textAlign: "center",
				 selector:{
					id : "check_box_class",
					class:"m-checkbox--solid m-checkbox--brand"}
            }, {
                field: "name",
                title: "Name",
                filterable: !1,
                width: 100,
                template: "{{name}}"
            },
			{
                field: "email",
                title: "Email Address",
                filterable: !1,
                width: 150,
                template: "{{email}}"
            },
			{
                field: "user_role",
                title: "User Role",
                filterable: !1,
                width: 200,
                template: "{{user_role}}"
            },
			{
                field: "reg_date",
                title: "Registered Date",
                filterable: !1,
                width: 130,
                template: "{{reg_date}}"
            },
			
			   {
                field: "Actions",
                width: 110,
                title: ACTIONS,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
					
					var edit_link = '<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+EDIT+' '+ member_txt+'" onclick="edit('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t';
					
					if(t.id == 1){
					   var delete_option = '';
					   var edit_link = '';
					}else{
						if(IS_USER_DEL == 'Y'){
					  var delete_option = '<a href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE+' '+member_txt +'">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>';
						}else{
						var delete_option = '';	
						}
					}
					
					
					
					if(IS_USER_EDIT == 'Y'){
					   var edit_lnk = edit_link;
					}
					
                    return edit_lnk +' '+ delete_option +'\t\t\t\t\t';
                }
            }]
        }), $("#refresh_table").on('click', function(){
          t.reload(); /*DatatableRemoteAjaxDemoCat.init();*/ }), 
		  $("#m_form_status").on("change", function() {
            t.search($(this).val(), "Status")
        }), /*$("#m_form_type").on("change", function() {
            t.search($(this).val(), "Type")
        }),*/ $("#m_form_status, #m_form_type").selectpicker()
    }
	
};



jQuery(document).ready(function() {
    if(IS_USER_ADD == 'Y' || IS_USER_EDIT == 'Y' || IS_USER_DEL == 'Y'){
	  DatatableRemoteAjaxDemoCat.init()
	}
});

function reloadTable()
{
    t.reload(); //reload datatable ajax 
}

function hideErrorMsg(){
  setTimeout(function(){
    $('#mesg_div').delay(1000).slideUp(300); 
 }, 1000);
}

// Add
function addUser(){
	var email_add = $("#admin_user_email").val();
	var div_id_or_class  = '#mesg_div';
	if($("#admin_user_name").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Enter Admin Name');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_name").focus();
		return false;
	}

	
	if($("#admin_user_email").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Enter Admin Email');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_email").focus();
		return false;
	}
	
	if( !validateEmail(email_add)) { 
		$(div_id_or_class).show().addClass('alert alert-danger').html('Enter valid email address');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_email").focus();
		return false;
	}
	
	if($("#admin_user_password").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Enter Admin Password');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_password").focus();
	    return false;
	}
	
	if($("#admin_user_password").val() !="" && $("#admin_user_password").val().length < 6)
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Password length must be at least 6 characters');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_password").focus();
	    return false;
	}
	
	if($("#role_id").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Select Role');
		hideErrorDiv(div_id_or_class);
		$("#role_id").focus();
		return false;
	}
	
	
	
	
	

	
	var url;
    url = "saveUser";
   var formData = new FormData($('#form_add_user')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
		
		if(data.status == 'error'){
			$('#mesg_div').show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'duplicate'){
			$('#mesg_div').show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$('#mesg_div').show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-add-user').modal('hide');
				$('#form_add_user')[0].reset();
			    //location.reload();	
				reloadTable();
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;

}

// Delete
function delete_data()
{
	var formData = new FormData($('#form_del_data')[0]);
	// ajax delete data from database
	$.ajax({
		url : "members/delete/"+$('#id').val(),
		type: "GET",
		dataType: "JSON",
		data: formData,
		processData: false,
		contentType: false,
		success: function(data)
		{
			$('#modal-delete').modal('hide');
			location.reload();
			//reload_table_c();
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert('Error deleting data');
		}
	});
}

// Delete Function
function delete_function(id){
   $('.del_title').html(DELETE+' '+member_txt);
   $('.del_text').html(common_delete_confirm_msg_txt +member_txt+'?');	
   $('#id').val(id);
   $('#modal-delete').modal('show');
}

function edit(id)
{   
    $('#form_add_user_e')[0].reset(); // reset form on modals
    //Ajax Load data from ajax
    $.ajax({
        url : "users/edit/" + id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
            $('#id_e').val(data['id']);
			$('#admin_user_name_e').val(data['name']);
			$('#admin_user_email_e').val(data['email']);
			$('#role_id_e').val(data['role_id']).prop('selected', true);
			$('#status_e').val(data['status']).prop('selected', true);
			$('#modal-edit-user').modal('show'); // show bootstrap modal when complete loaded
			
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}

// Update
function updateUser(){
	var email_add = $("#admin_user_email_e").val();
	var div_id_or_class  = '#mesg_div_e';
	if($("#admin_user_name_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Enter Admin Name');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_name_e").focus();
		return false;
	}

	
	if($("#admin_user_email_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Enter Admin Email');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_email_e").focus();
		return false;
	}
	
	if( !validateEmail(email_add)) { 
		$(div_id_or_class).show().addClass('alert alert-danger').html('Enter valid email address');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_email_e").focus();
		return false;
	}
	
	if($("#admin_user_password_e").val() !="" && $("#admin_user_password_e").val().length < 6)
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Password length must be at least 6 characters');
		hideErrorDiv(div_id_or_class);
		$("#admin_user_password_e").focus();
	    return false;
	}
	
	
	
	if($("#role_id_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Select Role');
		hideErrorDiv(div_id_or_class);
		$("#role_id_e").focus();
		return false;
	}
	
	
	var url;
    url = "users/update";
   var formData = new FormData($('#form_add_user_e')[0]);
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
				$('#modal-edit-user').modal('hide');
				$('#form_add_user_e')[0].reset();	
				reloadTable();
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;

}




function validateEmail($email) {
   var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
  return emailReg.test( $email );
}







