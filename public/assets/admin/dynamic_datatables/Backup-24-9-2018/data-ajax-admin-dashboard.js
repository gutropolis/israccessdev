 var customer_id = $('#suggested_name_id').val();

var DatatableRemoteAjaxOrder = {
    init: function() {
        //var t;
        t = $(".m_datatable_orders_list").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getGeneralReport",
						params: {
							// custom query params
							post_data: {
							   from_date_val: formatDate(from_date_val),
							   to_date_val: formatDate(to_date_val)
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
                theme:"default",
				class:"",
				scroll:!1,
				/*height:380,
				footer:!0*/
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
                input: $("#generalSearch")
            },
            columns: [
			{
                field: "order_id",
                title: Order_ID,
                filterable: !0,
                width: 60,
                template: "{{order_id}}"
            },
			 {
                field: "user_last_name",
                title: Dash_last_name_txt,
                filterable: !0,
                width: 80,
                template: "{{user_last_name}}"
            },{
                field: "user_name",
                title: Dash_name_txt,
                filterable: !0,
                width: 80,
                template: "{{user_name}}"
            },{
                field: "email",
                title: Dash_email_txt,
                filterable: !0,
                width: 180,
                template: "{{email}}"
            }, {
                field: "event_name",
                title: Dash_report_event_name_txt,
                filterable: !1,
                width: 200,
                template: "{{event_name}}"
            },			
			{
                field: "event_date",
                title: Dash_report_event_date_txt,
                filterable: !1,
                width: 80,
                template: "{{event_date}}"
            },
			{
                field: "event_city",
                title: Dash_report_event_city_txt,
                filterable: !1,
                width: 80,
                template: "{{event_city}}"
            },
			{
                field: "total_amount",
                title: Dash_report_order_amount_txt,
                filterable: !1,
                width: 80,
                template: "{{total_amount}}"
            },
			{
                field: "seat_sequence",
                title: Dash_report_seat_number_txt,
                filterable: !1,
                width: 80,
                template: "{{seat_sequence}}"
            },
			{
                field: "ticket_category",
                title: Dash_report_category_name_txt,
                filterable: !1,
                width: 80,
                template: "{{ticket_category}}"
            },
			{
                field: "ticket_row",
                title: Dash_report_row_txt,
                filterable: !1,
                width: 80,
                template: "{{ticket_row}}"
            },
			{
                field: "seat_qty",
                title: Dash_report_seats_txt,
                filterable: !1,
                width: 80,
                template: "{{seat_qty}}"
            },
			{
                field: "order_date",
                title: Dash_report_order_date_txt,
                filterable: !1,
                width: 80,
                template: "{{order_date}}"
            },
			{
                field: "order_time",
                title: Dash_report_order_time_txt,
                filterable: !1,
                width: 80,
                template: "{{order_time}}"
            }
			]
        })
    }
	
};


var DatatableRemoteAjaxOrderCust = {
    init: function() {
        //var t;
        t = $(".m_datatable_orders_list_cust").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getGeneralReportCustomer",
						params: {
							// custom query params
							post_data: {
							   from_date_val: formatDate(from_date_val),
							   to_date_val: formatDate(to_date_val),
							   customer_id : customer_id
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
                theme:"default",
				class:"",
				scroll:!1,
				/*height:380,
				footer:!0*/
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
                input: $("#generalSearch")
            },
            columns: [
			{
                field: "order_id",
                title: Order_ID,
                filterable: !1,
                width: 60,
                template: "{{order_id}}"
            },
			 {
                field: "user_last_name",
                title: Dash_last_name_txt,
                filterable: !0,
                width: 80,
                template: "{{user_last_name}}"
            },{
                field: "user_name",
                title: Dash_name_txt,
                filterable: !0,
                width: 80,
                template: "{{user_name}}"
            },{
                field: "email",
                title: Dash_email_txt,
                filterable: !0,
                width: 180,
                template: "{{email}}"
            }, {
                field: "event_name",
                title: Dash_report_event_name_txt,
                filterable: !1,
                width: 200,
                template: "{{event_name}}"
            },			
			{
                field: "event_date",
                title: Dash_report_event_date_txt,
                filterable: !1,
                width: 80,
                template: "{{event_date}}"
            },
			{
                field: "event_city",
                title: Dash_report_event_city_txt,
                filterable: !1,
                width: 80,
                template: "{{event_city}}"
            },
			{
                field: "total_amount",
                title: Dash_report_order_amount_txt,
                filterable: !1,
                width: 80,
                template: "{{total_amount}}"
            },
			{
                field: "seat_sequence",
                title: Dash_report_seat_number_txt,
                filterable: !1,
                width: 80,
                template: "{{seat_sequence}}"
            },
			{
                field: "ticket_category",
                title: Dash_report_category_name_txt,
                filterable: !1,
                width: 80,
                template: "{{ticket_category}}"
            },
			{
                field: "ticket_row",
                title: Dash_report_row_txt,
                filterable: !1,
                width: 80,
                template: "{{ticket_row}}"
            },
			{
                field: "seat_qty",
                title: Dash_report_seats_txt,
                filterable: !1,
                width: 80,
                template: "{{seat_qty}}"
            },
			{
                field: "order_date",
                title: Dash_report_order_date_txt,
                filterable: !1,
                width: 80,
                template: "{{order_date}}"
            },
			{
                field: "order_time",
                title: Dash_report_order_time_txt,
                filterable: !1,
                width: 80,
                template: "{{order_time}}"
            }
			]
        })
    }
	
};




        
jQuery(document).ready(function() {
								
$("#m_select3_event_dashboard").select2({
    placeholder: "Select an Event Group"/*,
    allowClear: true*/
});
	// Check if there is any search put for the orders
	/*if(is_order_searched == 0){
		DatatableRemoteAjaxDemoCat.init();
	}else{*/
	if(is_order_searched == 1){
		if(customer_id <  1){
          DatatableRemoteAjaxOrder.init();
		}else{
	      DatatableRemoteAjaxOrderCust.init();	
		}
	}
	
	if(DEFAULT_LANG == 'en_US'){
		var defaultLang = 'en';
	}else{
		var defaultLang = 'fr';
	}
		
	$(function(){
		$('#from_date,#to_date').datepicker({
				language: defaultLang,
				format: 'dd/mm/yyyy',
                todayBtn: 'linked',
				autoclose: true
			});
		 // disabling dates
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        var checkin = $('#from_date').datepicker({
          onRender: function(date) {
            return date.valueOf() < now.valueOf() ? 'disabled' : '';
          }
        }).on('changeDate', function(ev) {
          if (ev.date.valueOf() > checkout.date.valueOf()) {
            var newDate = new Date(ev.date)
            newDate.setDate(newDate.getDate() + 1);
            checkout.setValue(newDate);
          }
          checkin.hide();
          $('#to_date')[0].focus();
        }).data('datepicker');
        var checkout = $('#to_date').datepicker({
          onRender: function(date) {
            return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
          }
        }).on('changeDate', function(ev) {
          checkout.hide();
        }).data('datepicker');
	});	
	
	$(function(){
		$('#report_from_date, #report_to_date').datepicker({
				language: defaultLang,
				format: 'dd/mm/yyyy',
                todayBtn: 'linked',
				autoclose: true
			});
		 // disabling dates
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        var checkin = $('#report_from_date').datepicker({
          onRender: function(date) {
            return date.valueOf() < now.valueOf() ? 'disabled' : '';
          }
        }).on('changeDate', function(ev) {
          if (ev.date.valueOf() > checkout.date.valueOf()) {
            var newDate = new Date(ev.date)
            newDate.setDate(newDate.getDate() + 1);
            checkout.setValue(newDate);
          }
          checkin.hide();
          $('#report_to_date')[0].focus();
        }).data('datepicker');
        var checkout = $('#report_to_date').datepicker({
          onRender: function(date) {
            return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
          }
        }).on('changeDate', function(ev) {
          checkout.hide();
        }).data('datepicker');
	});	

});





function reloadDashboard(){
  window.location.href='dashboard';	
}

// Functio to show sale report
function showSaleReport(sel)
{
	var event_id = sel.value;
	if(event_id != 'undefined' || event_id != ''){
	  $('#saleReportFrm').submit();	
	}
}

// function search sale report
function SearchSaleReport(){
   	var event_id = $('#event_id').val();
	
	if(event_id == 'undefined' || event_id == ''){
	   alert('Please select event first');
	   return false;
	}else{
	  $('#saleReportFrm').submit();		
	}
}

// Function SearchGeneralSaleReport
function SearchGeneralSaleReport(){
	var from_date = $('#from_date').val();
	var to_date = $('#to_date').val();
	var suggested_name_id = $('#suggested_name_id').val();
	if( from_date == '' || to_date == ''  ){
		alert('Select Start Date, End Date  ');
		return false;
	}else{
		$('#order_frm_changed').submit();
	}
}

// Function to download sale report
function downloadSaleReport(event_group_id, event_id){
	window.location.href='dashboard/downloadSaleReportPDF/'+event_group_id+'/'+event_id;
}

// Function to download sale report in CSV
function downloadSaleReportCSV(event_group_id, event_id){
	window.location.href='dashboard/downloadSaleReportCSV/'+event_group_id+'/'+event_id;
}

// Function to download sale report in Excel
function downloadSaleReportXls(event_group_id, event_id){
	window.location.href='dashboard/downloadSaleReportXLS/'+event_group_id+'/'+event_id;
}

//  Change event for Event Group
$(function(){
    $('#m_select1_event_dashboard').on('change', function() {
	$('#m_select2_event_dashboard').html('');
	$('#event_id').html('');
      var event_group_id = $("#m_select1_event_dashboard option:selected").val();
	  	 
	  if(event_group_id != ''){
	  var info = 'id=' + event_group_id;
		$.ajax({
		type: "GET",
		url: "dashboard/getGroupEventsList/"+event_group_id,
		data: '',
		success: function(data){
				$('#event_id').html(data);
				
		}
		});
	  }
    })
});


function view_event_sale_report(){
  	var event_group_id = $("#m_select1_event_dashboard option:selected").val();
	var event_id = $('#m_select2_event_dashboard option:selected').val();
	if(event_group_id == ''){
		alert('Please select event group first.');
		return false;
	}else if(event_id == ''){
		alert('Please select event.');
		return false;
	}else{
	   if(event_group_id != '' && event_id != ''){
			//var val = $('#container_chart_div').val();
			//use getJSON to get the dynamic data via AJAX call
			$.getJSON('dashboard/getEventSaleReport/'+event_id, function(chartData) {
				$('#container_chart_div').highcharts({
					chart: {
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false,
						type: 'pie'
					},
					title: {
						text: 'Browser market shares in January, 2018'
					},
					tooltip: {
						pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
					},
					plotOptions: {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							dataLabels: {
								enabled: true,
								format: '<b>{point.name}</b>: {point.percentage:.1f} %',
								style: {
									color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
								},
								connectorColor: 'silver'
							}
						}
					},
					series:  [{
						name: 'Share',
						data:   chartData 
		             }] 
				});	
			});
		 }
}

}

// Function to view order detail
function view_order(order_id){
	if(order_id != ''){
		$('#order_id_order_id').val(order_id);
	    var info = 'id=' + order_id;
		$.ajax({
		type: "GET",
		url: "dashboard/getOrder/"+order_id,
		data: '',
		success: function(data){		
				 $('.updatetextOrder').html(data);
				 $('#order_popup').modal('show');
		}
		});
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
			url: "./send-order-ticket/"+order_id+'/'+customer_id,
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

// Function to download Order Report
function downloadOrderReport(){
	var order_id = $('#order_id_order_id').val();
    window.location.href='orders/downloadOrderReportPDF/'+order_id;	
}

// Function to download Order Monthly Report
function downloadPDFold(){
	var from_date = $('#from_date_default').val();
	var to_date   = $('#to_date_default').val();
    window.location.href='dashboard/downloadPDF/'+from_date+'/'+to_date;	
}

// Function to download Order Monthly Report
function downloadPDF(){
	var from_date = $('#from_date').val();
	var to_date   = $('#to_date').val();
	var customer_id   = $('#suggested_name_id').val();
	if( from_date == '' ||  to_date == ''  ){
		alert('Select Start Date, End Date ');
		return false;
	}else{
       window.location.href='getGeneralReportPDF/'+formatDate(from_date)+'/'+formatDate(to_date)+'/'+customer_id;	
	}	
}

// Function to download order Montly CSV Report
function downloadCSV(){
    var from_date = $('#from_date').val();
	var to_date   = $('#to_date').val();
	var customer_id   = $('#suggested_name_id').val();
	if( from_date == '' ||  to_date == '' || suggested_name_id == '' ){
		alert('Select Start Date, End Date and Customer Name ');
		return false;
	}else{
       window.location.href='getGeneralReportCSV/'+formatDate(from_date)+'/'+formatDate(to_date)+'/'+customer_id;	
	}
}
// For suggestions
function clickableSearchResult(search_term)
{
	var partsOfStr = search_term.split(',');
	$("#suggested_names").val(partsOfStr[1]);
	$("#suggested_name_id").val(partsOfStr[0]);
	$("#hide_or_show_search_results_box").hide();
	//alert('You clicked = '+partsOfStr[0]+' - '+partsOfStr[1]);
}

$("#suggested_names").keyup(function() {

    if (!this.value) {
        $("#suggested_name_id").val(0);
    }

});

$(".clearable").each(function() {
  
  var $inp = $(this).find("input:text"),
      $cle = $(this).find(".clearable__clear");

  $inp.on("input", function(){
    $cle.toggle(!!this.value);
  });
  
  $cle.on("touchstart click", function(e) {
    e.preventDefault();
    $inp.val("").trigger("input");
	$("#suggested_name_id").val(0);
  });
  
});

function OnSearch(input) {
            $("#suggested_names").keyup(function() {
				if (!input.value) {
					$("#suggested_name_id").val(0);
				}
			});
        }

$(document).ready(function()
{
	//alert('Heree 12545');
	$("#suggested_names").on("keyup",function() 
	{
		//alert('Heree');
		var suggested_names = $("#suggested_names").val();
		var response_brought = $("#response_brought");
		var dataString = "suggested_names=" + suggested_names;
		
		if(suggested_names.length > 30)
		{
			$("#hide_or_show_search_results_box").show();
			$("#suggested_names").val('');
			$("#response_brought").html('<font color="red">Search term must not be greater than 30 characters.</font>');
		}
		else if(suggested_names.length < 1)
		{
			$("#hide_or_show_search_results_box").hide();
		}
		else if(suggested_names.length > 0 && suggested_names.length <= 30)
		{	
			$.ajax({  
				type: "POST",  
				url: "dashboard/auto_suggestion",  
				data: dataString,
				beforeSend: function() 
				{
					$("#hide_or_show_search_results_box").show();
					$("#response_brought").html('<img src="'+LOADER_IMG+'" align="absmiddle" alt="Searching '+suggested_names+'..."> Searching...');
				},  
				success: function(response)
				{
					$("#hide_or_show_search_results_box").show();
					$("#response_brought").html(response);
				}
			   
			}); 
		}
		else
		{
			$("#response_brought").html('<font color="red">Search term must not be less than 1 or greater than 30 characters.</font>');
		}
		return false;
	});
});

function formatDate(date) {
     /*var d = new Date(date),
         month = '' + (d.getMonth() + 1),
         day = '' + d.getDate(),
         year = d.getFullYear();

     if (month.length < 2) month = '0' + month;
     if (day.length < 2) day = '0' + day;

     return [year, month, day].join('-');*/
	 var splitString = date.split('/');
     return [splitString[2], splitString[1], splitString[0]].join('-');
 }
 
 
 //  Change event for Event Group
$(function(){
    $('#m_select3_event_dashboard').on('change', function() {
	$('#event_id_sel').html('');
      var event_group_id = $("#m_select3_event_dashboard option:selected").val();
	  	 
	  if(event_group_id != ''){
	  var info = 'id=' + event_group_id;
		$.ajax({
		type: "GET",
		url: "dashboard/getGroupEventsListing/"+event_group_id,
		data: '',
		success: function(data){
				$('#event_id_sel').html(data);
				
		}
		});
	  }
    })
});

/*===================================================================
           REPORTS
===================================================================*/
// Download General Report
function downloadReportGeneral(){
	var event_group_id = $('#m_select3_event_dashboard').val();
  	var from_date = $('#report_from_date').val();
	var to_date   = $('#report_to_date').val();
	var event_id   = $('#event_id_sel').val();
	if( from_date == '' ||  to_date == '' || event_group_id == '' ){
		alert('Select Event Group, Start and End Date ');
		return false;
	}else{
		//alert('Group='+event_group_id+'Event_id='+event_id+'From='+from_date+'To='+to_date);
		//return false;
       window.location.href='download_general_data_report/'+event_group_id+'/'+event_id+'/'+formatDate(from_date)+'/'+formatDate(to_date);	
	}
}

// Download  Report By event (accounting approach)
function downloadReportByEventAccount(){
	var event_group_id = $('#m_select3_event_dashboard').val();
  	var from_date = $('#report_from_date').val();
	var to_date   = $('#report_to_date').val();
	var event_id   = $('#event_id_sel').val();
	if( from_date == '' ||  to_date == '' || event_group_id == '' ){
		alert('Select Event Group, Start and End Date ');
		return false;
	}else{
       window.location.href='download_accounting_report/'+event_group_id+'/'+event_id+'/'+formatDate(from_date)+'/'+formatDate(to_date);	
	}
}

// Download  Report By event (sales approach)
function downloadReportByEventSale(){
	var event_group_id = $('#m_select3_event_dashboard').val();
  	var from_date = $('#report_from_date').val();
	var to_date   = $('#report_to_date').val();
	var event_id   = $('#event_id_sel').val();
	if( from_date == '' ||  to_date == '' || event_group_id == '' ){
		alert('Select Event Group, Start and End Date ');
		return false;
	}else{
       window.location.href='download_sales_report/'+event_group_id+'/'+event_id+'/'+formatDate(from_date)+'/'+formatDate(to_date);
	}
}

// Download  Report By Productor
function downloadReportByProductor(){
	var event_group_id = $('#m_select3_event_dashboard').val();
  	var from_date = $('#report_from_date').val();
	var to_date   = $('#report_to_date').val();
	var event_id   = $('#event_id_sel').val();
	if( from_date == '' ||  to_date == '' || event_group_id == '' ){
		alert('Select Event Group, Start and End Date ');
		return false;
	}else{
        window.location.href='download_by_productor_report/'+event_group_id+'/'+event_id+'/'+formatDate(from_date)+'/'+formatDate(to_date);
	}
}

// Download CulturAccess Report
function downloadReportCulturAccess(){
	var event_group_id = $('#m_select3_event_dashboard').val();
  	var from_date = $('#report_from_date').val();
	var to_date   = $('#report_to_date').val();
	var event_id   = $('#event_id_sel').val();
	if( from_date == '' ||  to_date == '' || event_group_id == '' ){
		alert('Select Event Group, Start and End Date ');
		return false;
	}else{
       window.location.href='download_culturaccess_report/'+event_group_id+'/'+event_id+'/'+formatDate(from_date)+'/'+formatDate(to_date);
	}
}











