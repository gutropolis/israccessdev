
var DatatableRemoteAjaxDemoCat = {
	
    init: function() {
        //var t;
        t = $(".m_datatable_payment_type").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getAjaxPaymentTypesList",
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
			//bStateSave: !1,
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
                width: 40,
                selector: !1,
                textAlign: "center",
				selector:{
					id : "check_box_class",
					class:"m-checkbox--solid m-checkbox--brand"}
            }, {
                field: "name",
                title: "Payment Type Name",
                filterable: !1,
                width: 150,
                template: "{{name}}"
            },
			{
                field: "payment_logo",
                title: "Payment Logo",
                filterable: !1,
                width: 200,
                template: "<img src='{{payment_logo}}' height=100 width=100 class='img-responsive img-circle' >"
            },
			 {
                field: "status",
                title: "Status",
                template: function(t) {
                    var e = {
                        0: {
                            title: "Inactive",
                            class: "m-badge--danger"
                        },
                        1: {
                            title: "Active",
                            class: " m-badge--success"
                        }
                    };
                    return '<span class="m-badge ' + e[t.status].class + ' m-badge--wide">' + e[t.status].title + "</span>"
                }
            },  {
                field: "Actions",
                width: 110,
                title: "Actions",
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
                    return '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Edit Payment Type" onclick="edit('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete Payment Type">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t'
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

// Add
function addPaymentType(){
	var div_id_or_class  = '#mesg_div';
	if($("#name").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter payment type name.');
		hideErrorDiv(div_id_or_class);
		$("#name").focus();
		return false;
	}
	
	if($("#payment_logo").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please select payment type logo.');
		hideErrorDiv(div_id_or_class);
		$("#payment_logo").focus();
		return false;
	}

	
	var url;
    url = "savePaymentType";
   var formData = new FormData($('#form_add_payment_type')[0]);
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
				$('#modal-add-payment-type').modal('hide');
				$('#form_add_payment_type')[0].reset();
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
    $('#form_add_payment_type_e')[0].reset(); // reset form on modals
    //Ajax Load data from ajax
    $.ajax({
        url : "paymentTypes/edit/" + id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
			
            $('#id_e').val(data['id']);
			$('#name_e').val(data['name']);
			$('#payment_logo_old').val(data['payment_logo']);
			$('#status_e').val(data['status']).prop('selected', true);
			var payment_logo = data['payment_logo'];
			if(payment_logo != '')
            {
                $('#label-photo-edit').text('Change Payment Logo'); // label photo upload
                $('#photo-preview-edit div').html('<img src="'+data['file_web_path']+'/'+payment_logo+'" class="img-rounded img-thumbnail" height=100 width=100>'); // show photo
                
            }
            else
            {
                $('#photo-preview div').text('(No payment logo)');
            }
			
			$('#modal-edit-payment-type').modal('show'); // show bootstrap modal when complete loaded
			
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}

// Update
function updatePaymentType(){
	var div_id_or_class  = '#mesg_div_e';
	if($("#name_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter payment type name.');
		hideErrorDiv(div_id_or_class);
		$("#name_e").focus();
		return false;
	}
	
	
	
	var url;
     url = "paymentTypes/update";
   var formData = new FormData($('#form_add_payment_type_e')[0]);
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
				$('#modal-edit-payment-type').modal('hide');
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
		url : "paymentTypes/delete/"+id,
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
   $('.del_title').html('Delete Payment Type');
   $('.del_text').html('Are you sure you want to delete this Payment Type?');	
   $('#id').val(id);
   $('#modal-delete').modal('show');
}





