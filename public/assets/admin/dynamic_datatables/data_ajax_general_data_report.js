
var DatatableRemoteAjaxDemoCat = {
	
    init: function() {
        //var t;
        t = $(".m_datatable_general_data_report").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getAjaxOrdersList",
						params: {
							// custom query params
							post_data: {
							   event_id: event_id_val,
							   category_id: category_id_val,
							   row_id: row_id_val
							}
						},
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
            sortable: !1,
            pagination: !0,
            toolbar: {
                items: {
                    pagination: {
                        pageSizeSelect: [10, 20, 30, 50, 100]
                    }
                }
            },
            search: {
                input: $("#generalSearch_txt")
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
            },{
                field: "customer_name",
                title: Customer_name,
                filterable: !0,
                width: 200,
                template: "{{customer_name}}"
            }, {
                field: "total_amount",
                title: Total_Amount,
                filterable: !1,
                width: 90,
                template: "{{total_amount}}"
            },
			{
                field: "payment_type",
                title: Payment_Type,
                filterable: !1,
                width: 100,
                template: "{{payment_type}}"
            },
			{
                field: "seat_category",
                title: "Seat Category",
                filterable: !1,
                width: 100,
                template: "{{seat_category}}"
            },
			{
                field: "seat_row",
                title: "Seat Row",
                filterable: !1,
                width: 100,
                template: "{{seat_row}}"
            },{
                field: "seat_sequence",
                title: "Seat Sequence",
                filterable: !1,
                width: 100,
                template: "{{seat_sequence}}"
            },
			{
                field: "created_on",
                title: Date_Ordered,
                filterable: !1,
                width: 120,
                template: "{{created_on}}"
            },
			{
                field: "Actions",
                width: 80,
                title: Actions,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
                    return '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Download PDF" onclick="download_ticket('+[t.id]+','+[t.customer_id]+')">\t\t\t\t\t\t\t<i class="la la-download"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+ dashboard_view_order_txt +'" onclick="view('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-eye"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a style="display:none" href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete Productor">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t'
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


var DatatableRemoteAjaxDemoCatFilter = {
	
    init: function() {
        //var t;
        t = $(".m_datatable_orders_filter").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getAjaxOrdersListFilter",
						params: {
							// custom query params
							post_data: {
							   search_keyword: search_keyword
							}
						},
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
            sortable: !1,
            pagination: !0,
            toolbar: {
                items: {
                    pagination: {
                        pageSizeSelect: [10, 20, 30, 50, 100]
                    }
                }
            },
            search: {
                input: $("#generalSearch_txt")
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
            },
			{
                field: "customer_name",
                title: Customer_name,
                filterable: !0,
                width: 200,
                template: "{{customer_name}}"
            }, 
			{
                field: "total_amount",
                title: Total_Amount,
                filterable: !1,
                width: 90,
                template: "{{total_amount}}"
            },
			{
                field: "payment_type",
                title: Payment_Type,
                filterable: !1,
                width: 100,
                template: "{{payment_type}}"
            },
			{
                field: "seat_category",
                title: "Seat Category",
                filterable: !1,
                width: 100,
                template: "{{seat_category}}"
            },
			{
                field: "seat_row",
                title: "Seat Row",
                filterable: !1,
                width: 100,
                template: "{{seat_row}}"
            },
			{
                field: "seat_sequence",
                title: "Seat Sequence",
                filterable: !1,
                width: 100,
                template: "{{seat_sequence}}"
            },
			{
                field: "created_on",
                title: Date_Ordered,
                filterable: !1,
                width: 120,
                template: "{{created_on}}"
            },
			{
                field: "Actions",
                width: 80,
                title: Actions,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
                    return '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Download PDF" onclick="download_ticket('+[t.id]+','+[t.customer_id]+')">\t\t\t\t\t\t\t<i class="la la-download"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+ dashboard_view_order_txt +'" onclick="view('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-eye"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a style="display:none" href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete Productor">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t'
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
	if(ORDER_PAGE == 'O'){
		if(order_filtered == 'N'){
		   DatatableRemoteAjaxDemoCat.init();
		}else{
		  DatatableRemoteAjaxDemoCatFilter.init();	
		}
	}
});

// Function to reload the table
function reloadTable()
{
    t.reload(); //reload datatable ajax 
}

// Function to view order
function view(id)
{   
    window.location.href='./orders/view/'+id;
}

// Function to download Order Report
function downloadOrderReport(order_id){
  window.location.href='../downloadOrderReportPDF/'+order_id;	
}


// function download the E-Ticket
function download_ticket(order_id, customer_id){
  	window.location.href='../admin/download-ticket/'+order_id+'/'+customer_id;	
}

// Search orders
function SearchOrderReport(){
	// Get all the required params
	var event_group_id = $('#event_group_id').val();
	var event_id = $('#event_id').val();
	var category_id = $('#category_id').val();
	var row_id = $('#row_id').val();
	
	if(event_group_id == 'undefined' || event_group_id == ''){
	   alert('Please select event group first');
	   return false;
	}else if(event_id == 'undefined' || event_id == ''){
		 alert('Please select event');
	   return false;
	}else{
	    $('#orderReportFrm').submit();
	}
}

// reload the order page
function reloadOrderPage(){
	window.location.href='orders';	
}

// download order CSV
function downloadOrderCSV(){
	var event_id = $('#event_id').val();
	
	if(event_id == 'undefined' || event_id == ''){
	   alert('Please select event first');
	   return false;
	}else{
	 window.location.href='orders/downloadEventCSV/'+event_id;	
	}
}

// Send confirmation email
function sendConfirmationEmail(order_id, customer_id){
	if(order_id != '' && customer_id != ''){
		if(confirm("Are you sure, you want to send confirmation email to the customer?"))
	   {
		  var info = 'id=' + order_id;
			$.ajax({
			type: "GET",
			url: "../../send-order-ticket/"+order_id+'/'+customer_id,
			data: '',
			success: function(data){		
					if(data.status == 1){
						$('#emailMsgDiv').html('<div class="alert alert-success"><strong>Success!</strong> Email sent successfully</div>');
					}else{
						$('#emailMsgDiv').html('<div class="alert alert-danger"><strong>Error!</strong> Email sending failed</div>');
					}
			}
			});
	   }
	  }
}




/* ===========  All Drop Down works here =================== */
// Get all Events by Event Group Id
function getEvents(event_group_id){
	$('#event_id').html('');
	if(event_group_id != ''){
		$.ajax({
		type: "GET",
		url: "orders/getEventsList/"+event_group_id,
		data: '',
		success: function(data){		
		   $('#event_id').html(data);
		}
		});
	}
}

// Get event seat categories by event id
function getCategories(event_id){
	$('#category_id').html('');
	if(event_id != ''){
		$.ajax({
		type: "GET",
		url: "orders/getEventCategoriesList/"+event_id,
		data: '',
		success: function(data){		
		   $('#category_id').html(data);
		}
		});
	}
  
}

// Get rows of a category
function getRows(category_id){
	$('#row_id').html('');
    if(category_id != ''){
	  $.ajax({
		type: "GET",
		url: "orders/getEventCategoryRowsList/"+category_id,
		data: '',
		success: function(data){		
		   $('#row_id').html(data);
		}
	 });
  }
}


/* =========   Next Order  Function =============== */

function nextOrder(order_id){
  window.location.href = './'+order_id;	
}


/* ===============  Previous Order Function  ================== */
function previousOrder(order_id){
  window.location.href = './'+order_id;	
}

/*  ================ Change Seats  =========================== */
function changeSeats(order_id){
    //Ajax Load data from ajax
    $.ajax({
        url : "../../orders/getEventSeatCategoriesList/" + order_id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
			$('#loadContentSection').html(data.html);
			$('#seatsChangedSeatsModel').modal('show'); // show bootstrap modal when complete loaded			
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    }); 	
}


// Get seats of the category
function getCategorySeats(sel){
	var event_seat_category_id = sel.value; // Get the value of the selected select option
	if(event_seat_category_id != ''){
       $.ajax({
        url : "../../orders/getEventSeatCategoriesList/" + event_seat_category_id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
			$('#loadContentSection').html(data.html);
			$('#seatsChangedSeatsModel').modal('show'); // show bootstrap modal when complete loaded			
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
      }); 	
	}
}

function SearchOrder(){
	var searh_keyword = $('#search_keyword').val();
	if(searh_keyword == 'undefined' || searh_keyword == ''){
	   alert('Please customer name first');
	   return false;
	}else{
	    $('#search_keyword_frm').submit();
	}
}

// Change Seat for Customer
function changeSeatForCustomer(){
	
	var selected_ids = $('#selected_ids').val();
	if(selected_ids == 0){ 
	  alert(order_select_seat_txt);
	  return false;
	}else{
		var url;
		url = "../../orders/changeSeats";
	    var formData = new FormData($('#customer_change_seats_frm')[0]);
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
					//$('#modal-edit-auditorium').modal('hide');
					swal(
								  'Success!',
								  order_seat_changed_msg_txt,
								  'success'
								);
				// Now reload the page
				setTimeout(function() {
							location.reload();
						}, 1000);
				//window.location.href='../../events/groups/edit/'+event_group_id;
			}
		}, 
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				//console.log(XMLHttpRequest.toSource());
			}
		});
		return false;	
	}
	
}

// Refund Order
function refundOrder(){
	

  	var m_autosize_2 = $('#m_autosize_2').val();
	if(m_autosize_2 == 0){ 
	  swal(order_enter_refund_reason_txt);
	  return false;
	}else{
		
		swal({
		  title: are_you_sure_txt+ '?',
		  text: order_wont_revert_txt+"!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: order_yes_refund_it_txt+'!'
		}).then((result) => {
		  if (result.value) {
			  
			  var url;
				url = "../../orders/refundOrder";
				var formData = new FormData($('#customer_refund_frm')[0]);
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
							swal(
								  order_delete_txt+'!',
								  order_refunded_msg_txt,
								  SUCCESS_TXT
								);
								// Now reload the page
								setTimeout(function() {
											location.reload();
										}, 2000);
					}
				}, 
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						//console.log(XMLHttpRequest.toSource());
					}
				});
				return false;	
			
			
		  }
		});
		
	}
}





