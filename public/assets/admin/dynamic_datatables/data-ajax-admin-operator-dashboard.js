


var DatatableRemoteAjaxOrder = {
    init: function() {
        //var t;
        t = $(".m_datatable_orders_list").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "dashboard/dashboardOrdersList",
						params: {
							// custom query params
							post_data: {
							   from_date_val: from_date_val,
							   to_date_val: to_date_val
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
                field: "customer_name",
                title: "Nom",
                filterable: !0,
                width: 160,
                template: "{{customer_name}}"
            },{
                field: "customer_first_name",
                title: 'Prenom',
                filterable: !0,
                width: 160,
                template: "{{customer_first_name}}"
            },{
                field: "customer_telephone",
                title: 'Telephone',
                filterable: !0,
                width: 160,
                template: "{{customer_telephone}}"
            },{
                field: "seat_category",
                title: 'Category',
                filterable: !0,
                width: 160,
                template: "{{seat_category}}"
            },{
                field: "seat_row",
                title: 'Range',
                filterable: !0,
                width: 160,
                template: "{{seat_row}}"
            }, {
                field: "seat_sequence",
                title: 'Seat',
                filterable: !0,
                width: 160,
                template: "{{seat_sequence}}"
            }]
        })
    }
	
};





jQuery(document).ready(function() {
	// Check if there is any search put for the orders
	
     DatatableRemoteAjaxOrder.init();
	
	//https://stackoverflow.com/questions/34079667/configuring-language-in-bootstrap-date-range-picker
	// https://longbill.github.io/jquery-date-range-picker/
		$('#defaultrange_1').daterangepicker({
			opens: ('left'),
			format: 'MM/DD/YYYY',
			separator: ' to ',
			startDate: moment().subtract('days', 29),
			endDate: moment(),
			/*language: 'ru',*/
			ranges: {
				
				'Today' : [moment(), moment()],
				'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
				'Last 7 Days': [moment().subtract('days', 6), moment()],
				'Last 30 Days': [moment().subtract('days', 29), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
			},
			minDate: '01/01/2012',
			maxDate: '12/31/2018',
		},
		function (start, end) {
			$('#defaultrange_1 input').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			$('#from_date').val(start.format('YYYY-MM-DD')); // Assign the selected from date value to from_date
			$('#to_date').val(end.format('YYYY-MM-DD')); // Assign the selected to date value to to_date
			$( "#order_frm_changed" ).submit(); // Submit the form
		}
	);
});



function dateRangeSelector(){
	if(0!=$("#m_dashboard_daterangepicker_1").length){
		var n=$("#m_dashboard_daterangepicker_1"),
		e=moment(),
		t=moment();
		n.daterangepicker({
			startDate:e,
			endDate:t,
			opens:"left",
			ranges:{
				Today:[moment(),moment()],
				Yesterday:[moment().subtract(1,"days"),moment().subtract(1,"days")],
				"Last 7 Days":[moment().subtract(6,"days"),moment()],
				"Last 30 Days":[moment().subtract(29,"days"),moment()],
				"This Month":[moment().startOf("month"),moment().endOf("month")],
				"Last Month":[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")]
				}
				},
				a),
				a(e,t,"")
		}
		function a(e,t,a){
			alert('Hello');
			/*
			var r="",o="";
			t-e<100?(r="Today:",
			o=e.format("MMM D")):"Yesterday"==a?(r="Yesterday:",o=e.format("MMM D")):o=e.format("MMM D")+" - "+t.format("MMM D"),n.find(".m-subheader__daterange-date").html(o),
			n.find(".m-subheader__daterange-title").html(r)
			*/
			}
}

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
// Function to download sale report
function downloadSaleReport(event_group_id, event_id){
	window.location.href='dashboard/downloadSaleReportPDF/'+event_group_id+'/'+event_id;
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
    window.location.href='../admin/orders/downloadOrderReportPDF/'+order_id;	
}

// Function to download Order Monthly Report
function downloadPDF(){
	var from_date = $('#from_date_default').val();
	var to_date   = $('#to_date_default').val();
    window.location.href='dashboard/downloadPDF/'+from_date+'/'+to_date;	
}

// Function to download order Montly CSV Report
function downloadCSV(){
    var from_date = $('#from_date_default').val();
	var to_date   = $('#to_date_default').val();
    window.location.href='dashboard/downloadCSV/'+from_date+'/'+to_date;	
}









