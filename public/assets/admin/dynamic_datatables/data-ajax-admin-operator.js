var DatatableRemoteAjaxDemoCat = {
  init: function() {
        //var t;
        t = $(".m_datatable_operator").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "operators/getAjaxOperatorList",
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
                field: "op_id",
                title: "#",
                sortable: !1,
                width: 40,
                selector: !1,
				textAlign:"center",
                selector:{
					id : "check_box_class",
					class:"m-checkbox--solid m-checkbox--brand"}
			}, 
			{
                field: "op_fullname",
                title: op_full_name,
                filterable: !1,
                width: 150,
                template: "{{op_fullname}}"
            },{
                field: "op_fname",
                title: NAME_TXT,
                filterable: !1,
                width: 150,
                template: "{{op_fname}}"
            },
			{
                field: "op_email",
                title: EMAIL_TXT ,
                filterable: !1,
                width: 200,
                template: "{{op_email}}"
            },
			 {
                field: "Actions",
                width: 110,
                title: ACTIONS,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
					var edit_lnk = '';
					var del_lnk = '';
					var edit_link = '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+EDIT_TXT +' '+ OP_TXT +'" onclick="edit('+[t.op_id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>';
					var del_link = '\t\t\t\t\t\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function('+[t.op_id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE_TXT +' '+ OP_TXT +'">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t';
					if(IS_OPERATOR_EDIT == 'Y'){
						var edit_lnk = edit_link;
					}
					if(IS_OPERATOR_DEL == 'Y'){
						var del_lnk = del_link;
					}
                    return edit_lnk+ '\t\t\t\t\t\t<a href="javascript:void(0);" onclick="reset_pass('+[t.op_id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Reset Password">\t\t\t\t\t\t\t<i class="la la-key"></i>\t\t\t\t\t\t</a>'+del_lnk;
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
	if(IS_OPERATOR_ADD == 'Y' || IS_OPERATOR_EDIT == 'Y' || IS_OPERATOR_DEL == 'Y'){								
        DatatableRemoteAjaxDemoCat.init()
	}
});

function reloadTable()
{
    t.reload(); //reload datatable ajax 
}


// Add

function addOperator(){
	var div_id_or_class  = '#mesg_div';
	var email_add = $("#op_email").val();
	if($("#op_fullname").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_full_name_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_fullname").focus();
		return false;
	}
	if($("#op_fname").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_fname_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_fname").focus();
		return false;
	}
	if($("#op_lname").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_lname_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_lname").focus();
		return false;
	}
	
	if($("#op_email").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_email_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_email").focus();
		return false;
	}
	
	if( !validateEmail(email_add)) { 
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_valid_email_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_email").focus();
		return false;
	}
	
	if($("#op_phone").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_mobile_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_phone").focus();
		return false;
	}

	
	var url;
    url = "operators/saveOperator";
   var formData = new FormData($('#form_add_operator')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
	if(data.status == 'duplicate'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-add-operator').modal('hide');
				$('#form_add_operator')[0].reset();
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

// Edit function 

function edit(id)
{   
    $('#form_add_opereator_e')[0].reset(); // reset form on modals
    //Ajax Load data from ajax
    $.ajax({
        url : "operators/getOperator/" + id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
            $('#id_e').val(data['op_id']);
			$('#op_fullname_e').val(data['op_fullname']);
			$('#op_fname_e').val(data['op_fname']);
			$('#op_lname_e').val(data['op_lname']);
		     $('#op_email_e').val(data['op_email']);
            $('#op_phone_e').val(data['op_phone']);
			$('#modal-edit-operator').modal('show'); // show bootstrap modal when complete loaded
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}



// Update

function updateOperator(){
	var div_id_or_class  = '#mesg_div_e';
	var email_add = $("#op_email_e").val();
	if($("#op_fullname_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_full_name_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_fullname_e").focus();
		return false;
	}
	if($("#op_fname_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_fname_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_fname_e").focus();
		return false;
	}
	if($("#op_lname_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_lname_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_lname_e").focus();
		return false;
	}
	
	if($("#op_email_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_email_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_email_e").focus();
		return false;
	}
	
	if( !validateEmail(email_add)) { 
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_valid_email_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_email_e").focus();
		return false;
	}
	
	if($("#op_phone_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(op_mobile_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#op_phone_e").focus();
		return false;
	}
	var url;
     url = "operators/updateOperator";
   var formData = new FormData($('#form_add_opereator_e')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
		if(data.status == 'duplicate'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-edit-operator').modal('hide');
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
	var id = $('#id').val();
	var formData = new FormData($('#form_del_data')[0]);
	// ajax delete data from database
	$.ajax({
		url : "operators/deleteOperator/"+id,
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
   $('.del_title').html(DELETE +' '+OP_TXT);
   $('.del_text').html(common_delete_confirm_msg_txt+' '+ OP_TXT +'?');	
   $('#id').val(id);
   $('#modal-delete').modal('show');
}

// Function to validate email address
function validateEmail($email) {
   var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
  return emailReg.test( $email );
}

// Function to reset password for the operator
function reset_pass(id){
   //$('.res_title').html(DELETE +' '+OP_TXT);
   $('.res_text').html('Reset operator password');	
   $('#res_id').val(id);
   $('#modal-pass').modal('show');
}

// Reset password for operator
function resetOperatorPassword(){
	var div_id_or_class  = '#mesg_div_pass';
	var url;
    url = "operators/resetOperatorPass";
   var formData = new FormData($('#form_add_pass')[0]);
   $.ajax({
	 url: url, 
	 type: "POST",
	data: formData,
	contentType: false,
	processData: false,
	dataType: "JSON",
	success: function(data) {
	if(data.status == 'duplicate'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'file_error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status == 'error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
				$('#modal-pass').modal('hide');
				$('#form_add_pass')[0].reset();
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










