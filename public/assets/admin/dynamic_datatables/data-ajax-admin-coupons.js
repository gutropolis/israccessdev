var DatatableRemoteAjaxDemoCat = {
	
    init: function() {
        //var t;
        t = $(".m_datatable_coupon").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getAjaxCouponsList",
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
                width: 40,
                selector: !1,
				textAlign:"center",
                selector:{
					id : "check_box_class",
					class:"m-checkbox--solid m-checkbox--brand"}
			}, 
			{
                field: "coupon_name",
                title: COUPON_NAME_TXT,
                filterable: !1,
                width: 120,
                template: "{{coupon_name}}"
            },
			{
                field: "coupon_code",
                title: COUPON_CODE_TXT,
                filterable: !1,
                width: 120,
                template: "{{coupon_code}}"
            },
			{
                field: "discount_type",
                title: DISCOUNT_TYPE_TXT,
                filterable: !1,
                width: 120,
                template: "{{discount_type}}"
            },
			{
                field: "discount_amount",
                title: COUPON_AMOUNT_TXT,
                filterable: !1,
                width: 80,
                template: "{{discount_amount}}"
            },
			{
                field: "coupon_used",
                title: COUPON_USED_TXT,
                filterable: !1,
                width: 100,
                template: "{{coupon_used}}"
            },
			{
                field: "expiration_date",
                title: COUPON_EXPIRATION_DATE_TXT,
                filterable: !1,
                width: 100,
                template: "{{expiration_date}}"
            },
			 {
                field: "status",
                title: STATUS,
                template: function(t) {
                    var e = {
                        0: {
                            title: INACTIVE,
                            class: "m-badge--danger"
                        },
                        1: {
                            title: ACTIVE,
                            class: " m-badge--success"
                        }
                    };
                    return '<span class="m-badge ' + e[t.status].class + ' m-badge--wide">' + e[t.status].title + "</span>"
                }
            },  {
                field: "Actions",
                width: 100,
                title: ACTIONS,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
					var edit_lnk = '';
					var del_lnk = '';
					var edit_link = '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+EDIT +' Coupon" onclick="edit('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>';
					var del_link = '\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE +' Coupon">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t';
					
					if(IS_COUPON_EDIT == 'Y'){
						var edit_lnk = edit_link;
					}
					if(IS_COUPON_DEL == 'Y'){
						var del_lnk = del_link;
					}
                    return edit_lnk+' '+del_lnk;
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



var DatatableRemoteAjaxDemoCoupon = {
	
    init: function() {
        //var t;
        t = $(".m_datatable_coupons_list").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "../../getAjaxCouponHistoryList",
						params: {
							// custom query params
							post_data: {
							   coupon_id: coupon_id
							}
						 },
                        map: function(t) {
                            var e = t;
                            return void 0 !== t.data && (e = t.data), e
                        }
                    }
                },
				saveState: {cookie: false, webstorage: false},
                pageSize: 5,
                serverPaging: true,
				serverFiltering: true,
				serverSorting: true
				
            },
            layout: {
                scroll: !1,
                footer: !1
            },
            sortable: !1,
            pagination: !0,
            toolbar: {
                items: {
                    pagination: {
                        pageSizeSelect: [5, 10, 20, 30, 50, 100]
                    }
                }
            },
            search: {
                input: $("#generalSearch")
            },
            columns: [/*{
                field: "id",
                title: "#",
                sortable: !1,
                width: 40,
                selector: !1,
				textAlign:"center",
                selector:{
					id : "check_box_class",
					class:"m-checkbox--solid m-checkbox--brand"}
			}, */
			{
                field: "order_id",
                title: 'Order ID',
                filterable: !0,
                width: 120,
                template: "{{order_id}}"
            },
			{
                field: "invoice_number",
                title: 'Invoice Number',
                filterable: !0,
                width: 120,
                template: "{{invoice_number}}"
            },
			{
                field: "customer_name",
                title: 'Customer Name',
                filterable: !0,
                width: 120,
                template: "{{customer_name}}"
            },
			
			{
                field: "date_used",
                title: 'Date Used',
                filterable: !0,
                width: 80,
                template: "{{date_used}}"
            },
			{
                field: "Actions",
                width: 100,
                title: ACTIONS,
                sortable: !0,
                overflow: "visible",
                template: function(t, e, a) {
					var edit_lnk = '';
					var del_lnk = '';
					var edit_link = '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View Order" onclick="view_order('+[t.order_id]+')">\t\t\t\t\t\t\t<i class="la la-eye"></i>\t\t\t\t\t\t</a>';
					var del_link = '\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE +' Coupon" style="display:none">\t\t\t\t\t\t\t<i class="la la-trash" ></i>\t\t\t\t\t\t</a>\t\t\t\t\t';
					//if(IS_COUPON_EDIT == 'Y'){
						var edit_lnk = edit_link;
					//}
					//if(IS_COUPON_DEL == 'Y'){
						var del_lnk = del_lnk;
					//}
                    return edit_lnk+' '+del_lnk;
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
	if(PAGE_OPT == 'coupons'){
		if(IS_COUPON_ADD == 'Y' || IS_COUPON_EDIT == 'Y' || IS_COUPON_DEL == 'Y' ){	
           DatatableRemoteAjaxDemoCat.init();
		}
	}else{
		if(IS_COUPON_ADD == 'Y' || IS_COUPON_EDIT == 'Y' || IS_COUPON_DEL == 'Y' ){	
		  DatatableRemoteAjaxDemoCoupon.init();
		}
	}
	//keypress keyup blur
	$(".discount_amount").on("keypress",function (event) {
            //this.value = this.value.replace(/[^0-9\.]/g,'');
     $(this).val($(this).val().replace(/[^0-9\.]/g,''));
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                //event.preventDefault();
				//display error message
			$(".errmsg").html(DIGITS_ONLY_TXT).show().fadeOut("slow");
				   return false;
            }
        });
	//called when key is pressed in textbox
	  $(".discount_amount_old").keypress(function (e) {
		 //if the letter is not digit then display error and don't type anything
		 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
			//display error message
			$(".errmsg").html("Digits Only").show().fadeOut("slow");
				   return false;
		}
	   });
});

function reloadTable()
{
    t.reload(); //reload datatable ajax 
}


// Add
function addCoupon(){
	var div_id_or_class  = '#mesg_div';
	
	if($("#coupon_name").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(ENTER_COUPON_TXT);
		hideErrorDiv(div_id_or_class);
		$("#coupon_name").focus();
		return false;
	}
	
	if($("#coupon_code").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(ENTER_COUPON_CODE_TXT);
		hideErrorDiv(div_id_or_class);
		$("#coupon_code").focus();
		return false;
	}
	
	if($("#discount_amount").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(COUPON_ENTER_AMOUNT_TXT);
		hideErrorDiv(div_id_or_class);
		$("#discount_amount").focus();
		return false;
	}
	
	var url;
    url = "saveCoupon";
   var formData = new FormData($('#form_add_coupon')[0]);
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
			
				$('#modal-add-coupon').modal('hide');
				$('#form_add_coupon')[0].reset();
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
    $('#form_add_coupon_e')[0].reset(); // reset form on modals
    //Ajax Load data from ajax
    $.ajax({
        url : "coupons/edit/" + id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
            $('#id_e').val(data['id']);
			$('#coupon_name_e').val(data['coupon_name']);
			$('#coupon_code_e').val(data['coupon_code']);
			$('#discount_type_e').val(data['discount_type']).prop('selected', true);
			$('#discount_amount_e').val(data['discount_amount']);
			$('#date_begin_edit').val(data['expiration_date']);
			$('#status_e').val(data['status']).prop('selected', true);
			$('#modal-edit-coupon').modal('show'); // show bootstrap modal when complete loaded
			
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}

// Update
function updateCoupon(){
	var div_id_or_class  = '#mesg_div_e';
	// check for coupon name here
	if($("#coupon_name_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(ENTER_COUPON_TXT);
		hideErrorDiv(div_id_or_class);
		$("#coupon_name_e").focus();
		return false;
	}
	
	// Check for coupon code
	if($("#coupon_code_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(ENTER_COUPON_CODE_TXT);
		hideErrorDiv(div_id_or_class);
		$("#coupon_code_e").focus();
		return false;
	}
	
	// Check for discounted Amount
	if($("#discount_amount_e").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(COUPON_ENTER_AMOUNT_TXT);
		hideErrorDiv(div_id_or_class);
		$("#discount_amount_e").focus();
		return false;
	}
	
	var url;
     url = "coupons/update";
   var formData = new FormData($('#form_add_coupon_e')[0]);
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
				$('#modal-edit-coupon').modal('hide');
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
		url : "coupons/delete/"+id,
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
   $('.del_title').html(DELETE+'  Coupon');
   $('.del_text').html(common_delete_confirm_msg_txt+' Coupon?');	
   $('#id').val(id);
   $('#modal-delete').modal('show');
}

// View function
function view(coupon_id){
   window.location.href= 'coupons/view/'+coupon_id;	
}

// View Order function
function view_order(order_id){
	window.location.href = '../../orders/view/'+order_id;
}








