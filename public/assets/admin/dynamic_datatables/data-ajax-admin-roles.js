var DatatableRemoteAjaxDemoCat = {
	
    init: function() {
        //var t;
        t = $(".m_datatable_system_role").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getAjaxSystemRolesList",
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
                title: ROLE_ID_TXT,
                sortable: !1,
                width: 100,
			}, 
			{
                field: "role_name",
                title: ROLE_NAME_TXT,
                filterable: !1,
                width: 450,
                template: "{{role_name}}"
            },
			 {
                field: "status",
                title: STATUS_TXT,
				width: 100,
                template: function(t) {
                    var e = {
                        0: {
                            title: INACTIVE_STATUS,
                            class: "m-badge--danger"
                        },
                        1: {
                            title: ACTIVE_STATUS,
                            class: " m-badge--success"
                        }
                    };
                    return '<span class="m-badge ' + e[t.status].class + ' m-badge--wide">' + e[t.status].title + "</span>"
                }
            },  {
                field: "Actions",
                width: 110,
                title: ACTIONS,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
					
					var set_up_modules_to_role = '\t\t\t\t\t\t<a  href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Assign Modules" onclick="assign_module('+[t.id]+')" >\t\t\t\t\t\t\t<i class="la la-list" style="color:green"></i>\t\t\t\t\t\t</a>\t\t\t\t\t';
					
					if(t.role_exist == 'Y'){
						// It means this can not be deleted as there are users assigned to this role
						var delete_link = '\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function_warning()" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE_TXT +' '+ ROLE_NAME_TXT +'">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t';
					}else{
						// Delete is allowed as no users is assinged to this role
						var delete_link = '\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE_TXT +' '+ ROLE_NAME_TXT +'">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t';
					}
                    return '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+EDIT_TXT +' '+ ROLE_NAME_TXT +'" onclick="edit('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>\t\t\t\t\t'+delete_link+set_up_modules_to_role
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
	
    DatatableRemoteAjaxDemoCat.init()
});

function reloadTable()
{
    t.reload(); //reload datatable ajax 
}

// Delete functino warning
function delete_function_warning(){
  alert('You can not delete this role as users are assigned this role');	
}
// Function to assign modules
function assign_module(id){
  window.location.href = 'system_roles/assign_modules/'+id;	
}

// Add
function addRole(){
	var div_id_or_class  = '#mesg_div';
	if($("#role_name").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(ROLE_NAME_MSG_TXT);
		hideErrorDiv(div_id_or_class);
		$("#role_name").focus();
		return false;
	}
	
	var url;
    url = "saveRole";
   var formData = new FormData($('#form_add_sys_role')[0]);
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
			
				$('#modal-add-system-role').modal('hide');
				$('#form_add_sys_role')[0].reset();
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
    $('#form_add_system_role_e')[0].reset(); // reset form on modals
    //Ajax Load data from ajax
    $.ajax({
        url : "system_roles/edit/" + id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
            $('#id_e').val(data['id']);
			$('#role_name_e').val(data['title']);
			$('#status_e').val(data['status']).prop('selected', true);
			$('#modal-edit-system-role').modal('show'); // show bootstrap modal when complete loaded
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}

// Update
function updateRole(){
	var div_id_or_class  = '#mesg_div_e';
	if($("#role_name_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(ROLE_NAME_MSG_TXT);
		hideErrorDiv(div_id_or_class);
		$("#role_name_e").focus();
		return false;
	}
	// Define the route url here
	var url;
     url = "system_roles/update";
   var formData = new FormData($('#form_add_system_role_e')[0]);
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
			    // Check if the status is true then hide the model
				$('#modal-edit-system-role').modal('hide');	
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
		url : "cities/delete/"+id,
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
	alert('Delete is in progress');
	return false;
   $('.del_title').html(DELETE +' '+CITY_NAME);
   $('.del_text').html(common_delete_confirm_msg_txt+' '+ CITY_NAME +'?');	
   $('#id').val(id);
   $('#modal-delete').modal('show');
}

$(document).ready(function(){
	$(".state_name").keyup(function(){
		$.ajax({
		type: "POST",
		url: "getStates",
		data:'search_keyword='+$(this).val()+'&mode=add',
		beforeSend: function(){
			$(".state_name").css("background","#FFF url('') no-repeat 165px");
		},
		success: function(data){
			$(".suggesstion-box-add").show();
			$(".suggesstion-box-add").html(data);
			$(".state_name").css("background","#FFF");
		}
		});
	});
	
	$(".state_name_e").keyup(function(){
		$.ajax({
		type: "POST",
		url: "getStates",
		data:'search_keyword='+$(this).val()+'&mode=edit',
		beforeSend: function(){
			$(".state_name_e").css("background","#FFF url('') no-repeat 165px");
		},
		success: function(data){
			$(".suggesstion-box-edit").show();
			$(".suggesstion-box-edit").html(data);
			$(".state_name_e").css("background","#FFF");
		}
		});
	});
});

//To select country name
function selectCountry(val) {
	$(".state_name").val(val);
	$(".suggesstion-box-add").hide();
}

function selectCountryE(val) {
	$(".state_name_e").val(val);
	$(".suggesstion-box-edit").hide();
}


function roleModuleAdd(role_id, controller_id,module_id) {
	if($('#module_'+role_id+'_'+controller_id+'_'+module_id).is(':checked'))
	{
		var url = "../../system_roles/saveRoleModule";
	} else {
		var url = "../../system_roles/removeRoleModule";
	}	
	
	$.ajax({
		type: "POST",
		cache: false,       
		url: url, 
		datatype: "text",
		data: {role_id: role_id,
		       controller_id: controller_id,
			   function_id:module_id
			  },
		success: function(response) {
			console.log(response);
			//location.reload();
		}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;
} // saveMyModule







