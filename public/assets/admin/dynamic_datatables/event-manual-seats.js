// Function to make id
function makeid() {
  var text = "";
  //var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  var possible = "0123456789";
  for (var i = 0; i < 2; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}

function makeid2() {
  var text = "";
  //var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"; 
  var possible = "0123456789084";
  for (var i = 0; i < 2; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}

var counterGlobal;

function getUserValue(counter) {
	
	var previous = null;
	var previous_counter = null;
	counterGlobal = counter;
	
    var user_value_from = $("#seat_row_from_"+counter).val();
	var user_value_to = $("#seat_row_to_"+counter).val();
	
	 $("#seat_row_to_"+counter).focus(function () {
                 previous = $(this).val();

     });
	  
	$("#seat_row_from_"+counter).val(($("#seat_row_from_"+counter).val()).toUpperCase());
	$("#seat_row_to_"+counter).val(($("#seat_row_to_"+counter).val()).toUpperCase());
	// Hence we have now range from and to use a loop
	var i;
	var rows_cols = '';
	var is_number = 'N';	
    var alphabetic_array = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o',
	                       'p','q','r','s','t','u','v','w','x','y','z'];
	
	if( $.inArray(user_value_to, alphabetic_array) != -1 ){
		start_value  = user_value_from.charCodeAt(0);
		end_value    = user_value_to.charCodeAt(0);
		is_number = 'Y';
		
	}else{
	    start_value  = user_value_from;
		end_value    = user_value_to;
		is_number = 'N';
		
	}
    var k = Date.now();
	var kk = Date.now();
	$("#seat_row_to_"+counter).blur(function () {
         if (previous != $(this).val()){
			$("div.row_new_"+counter).html('');	
			var j;
			
			if(user_value_to  !=''){
				var m=0;
			for(i = start_value; i <= parseInt(end_value); i++){
				
				if(is_number == 'Y'){
					 j = String.fromCharCode(i).toUpperCase();
				}else{
					j = i; 
				}
				rows_cols += '<div class="row" style="margin-bottom:20px !important">';
				rows_cols += '<div class="col-md-2">&nbsp;</div>';
				rows_cols += '<div class="col-md-2" style="margin-top:14px;">'+ROW_TXT+' &nbsp&nbsp '+ j +'</div>';
				rows_cols += '<div class="col-md-7" style="margin-top:5px">';
				
				// For each row define the range textboses
				rows_cols += '<table class="table table-bordered table-hover" id="tableAddRow_'+k+'">';
				rows_cols += '<thead>';
				rows_cols += '<tr >';
                rows_cols += '<th class="text-left">'+Placement_TXT+' </th>';
				rows_cols += '<th class="text-left">'+From_TXT+' </th>';
				rows_cols += '<th class="text-left">'+To_TXT+'</th>';
				rows_cols += '<th class="text-left">'+Even_Order_TXT+'?</th>';
				rows_cols += '<th style="width:10px;">';
                
				rows_cols += '<span class="la la-plus default_c" id="addBtn_'+k+'" onclick="add_row('+counter+','+m+','+k+','+kk+',\''+j+'\')"></span>';
				
				rows_cols += '</th>';
				rows_cols += '</tr>';
				rows_cols += '</thead>';
				rows_cols += '<tbody>';
				rows_cols += '<tr id="tr_0">';
                // By Somdeb
   	            rows_cols += '<td>  <select id="Placement['+counter+']['+m+'][]" data-id="['+counter+']['+m+'][0]" class="Placement form-control" name="Placement['+counter+']['+m+'][0]"><option value="1">'+Standard_TXT+'</option> <option value="2">'+Reserve_TXT+'</option><option value="3">'+Inviation_TXT+'</option> <option value="4">'+Vendues_TXT+'</option>  </select> <span id="operator['+counter+']['+m+'][]"></span>  </td>';
  
				rows_cols += '<td><input type="text" name="from_value['+counter+']['+m+'][]"  placeholder="'+From_TXT+'" class="form-control"/></td>';
				rows_cols += '<td><input type="text" name="to_value['+counter+']['+m+'][]"  placeholder="'+To_TXT+'" class="form-control"/></td>';
				rows_cols += '<td>';
		        rows_cols += '<input type="checkbox" name="seat_order['+counter+']['+m+'][0]"   class="form-control" value="2"/>';
				rows_cols += '<input type="hidden" name="seat_order_counter" id="seat_order_counter_'+j+'" value="0" class="seat_order_counter"/>';
				rows_cols += '<input type="hidden" name="row_number['+counter+']['+m+'][]" value="'+ j +'"  />';
		        rows_cols += '</td>';
				rows_cols += '<td ><span class="la la-minus default_c trRemove" id="addBtnRemove_'+k+'"></span></td>';
				rows_cols += '</tr>';
				rows_cols += '</tbody>';
				rows_cols += '</table>';
				
				rows_cols += '</div>'; // Div with class col-md-7 End
				
				rows_cols += '</div>';
				k=k+200;
				kk=kk+makeid();
			m++;	
			}
			rows_cols += '<div class="row" >';
			rows_cols += '<div class="col-md-2">&nbsp</div>';
			rows_cols += '<div class="col-md-2" style="margin-top:14px;">'+Category_Price_TXT+'</div>';
			rows_cols += '<div class="col-md-3"><input type="text" class="form-control" name="category_price[]"   placeholder="'+Category_Price_TXT+'" /></div>';
			
			/*rows_cols += '<div id="total_seats_div_'+counter+'" class="row col-md-4" style="display:none"><div class="col-md-5" style="margin-top:14px;">Total Seats</div>';
			rows_cols += '<div class="col-md-7"><input type="text"  class="form-control " name="total_seats[]" id="total_seats_'+counter+'"  placeholder="Enter total Seats"  /></div></div>';*/
			
			rows_cols += '</div>';
			$(".row_new_"+counter).append(rows_cols).slideDown("slow");
			is_number = 'N'; // Set the is number to N back
			}
	   }
	 });			 
	
}

// This is to check the onchange method
$(document).on('change','.attribute_check_1', function() {
    var data_id = $(this).data('id');
	if(this.checked){
		$('#total_seats_div_'+data_id).show();
	}else{
		$('#total_seats_'+data_id).val('');
		$('#total_seats_div_'+data_id).hide();
	}
    //alert($(this).data('id') + ' ' + (this.checked ? 'checked' : 'unchecked'));
	
});

// Check the on change method
$(document).on('change','.attribute_check_old_1', function() {
    var data_id = $(this).data('id');
	if(this.checked){
		$('.total_seats_div_'+data_id).show();
	}else{
		//$('#total_seats_'+data_id).val('');
		$('.total_seats_div_'+data_id).hide();
	}
    //alert($(this).data('id') + ' ' + (this.checked ? 'checked' : 'unchecked'));
});

// This is to change the placment
  $(document).on('change','.Placement', function() {
		var operator= $(this).next();
		var value = $(this).find(":selected").text();
		var selectedID = $(this).find(":selected").val();
		var counter = $(".seat_order_counter").val();
		if (selectedID == 4) {                   
			$.ajax({
			type: "POST",
			url: "../../operators/getOperatorsList",
			data: {data :  $(this).data('id')},
			success: function(data){
			operator.html(data);
			}
			});
		}
		else
		{
		 // Empty the operato html
		 operator.html("");
		}
  });


    // function to add rows when user click on + Icon
    function add_row(counter,l,k,kk,j)
    {
		// Set the order counter
		var seat_order_counter_var = $('#seat_order_counter_'+j).val();
		
		if(seat_order_counter_var > 0){
			var i = seat_order_counter_var;
		}else{
		 var i = 1;	
		}
		
		var cols = "";
		
		cols += '<tr id="tabl_row_'+i+'_'+kk+'">';
   	    cols += '<td>  <select id="Placement['+counter+']['+l+'][]" data-id="['+counter+']['+l+']['+i+']" class="Placement form-control" name="Placement['+counter+']['+l+']['+i+']"><option value="1">'+ Standard_TXT +'</option> <option value="2">'+Reserve_TXT+'</option><option value="3">'+Inviation_TXT+'</option> <option value="4">'+Vendues_TXT+'</option>  </select> <span id="operator['+counter+']['+l+'][]"></span>  </td>';
    	cols += '<td>';
		cols += '<input type="text" name="from_value['+counter+']['+l+'][]"  placeholder="'+From_TXT+'" class="form-control"/>';
		
		cols += '</td>';
		
		cols += '<td>';
		
		cols += '<input type="text" name="to_value['+counter+']['+l+'][]"  placeholder="'+To_TXT+'" class="form-control"/>';
		
		cols += '<input type="hidden" name="row_number['+counter+']['+l+'][]" value="'+ j +'"  />';
		
		cols += '</td>';
		cols += '<td>';
		cols += '<input type="checkbox" name="seat_order['+counter+']['+l+']['+i+']"   class="form-control" value="2"/>';
		
		cols += '</td>';
		cols += '<td>';
		cols += '<span class="la la-minus default_c trRemove" id="addBtn_' + i + '" ></span>';
		cols += '</td>';
		cols += '<tr>';
		//var tempTr = $(cols);
        var tempTr = $(cols).on('click', function () {
           //$(this).closest('tr').remove(); 
           $(document.body).on('click', '.trRemove', function (e) {
                $(this).closest('tr').remove();  
				
				var seat_order_counter_var = $('#seat_order_counter_'+j).val();
				i = seat_order_counter_var;
				i--;
				$('#seat_order_counter_'+j).val(i);
				
            });
        });
        $("#tableAddRow_"+k).append(tempTr)
        i++;
		$('#seat_order_counter_'+j).val(i);
    }
	
	// Remove the current created tr when user clicks on trRemove class
	  $(document.body).on('click', '.trRemove', function (e) {
        // Remove the table row
	   if( $(this).closest('tr').next('tr').length == "" )
       {
            $(this).closest('tr').remove();
       }
    });
	
	// Function to add or update the row
	 var i = 1;
    function add_update_row(counter,l,k,kk,j,table_id)
    {
		var current_id = $('.row_number_old_id_new_class_'+ counter +'_'+ l).val();
		var cols = "";
		cols += '<tr id="tabl_row_'+i+'_'+kk+'">';
		cols += '<td>';
		cols += '<input type="text" name="from_value_old_new['+counter+']['+l+'][]"  placeholder="From" class="form-control"/>';
		cols += '</td>';
		cols += '<td>';
		cols += '<input type="text" name="to_value_old_new['+counter+']['+l+'][]"  placeholder="To" class="form-control"/>';
		cols += '<input type="hidden" name="row_number_old_new['+counter+']['+l+'][]" value="'+ j +'"  />';
		cols += '</td>';
		cols += '<td>';
		cols += '<input type="checkbox" name="seat_order_old_new['+counter+']['+l+'][]"   class="form-control"/>';
		cols += '<input type="hidden" name="row_seat_id_old_new['+counter+']['+l+'][]" value="'+ current_id +'"  />';
		cols += '</td>';
		cols += '<td>';
		cols += '<span class="la la-minus default_c trRemove" id="addBtn_' + i + '" ></span>';
		cols += '</td>';
		cols += '<tr>';
		//var tempTr = $(cols);
        var tempTr = $(cols).on('click', function () {
           //$(this).closest('tr').remove(); 
           $(document.body).on('click', '.trRemove', function (e) {
                $(this).closest('tr').remove();  
            });
        });
		
		// Append to the table add row id
        $("#tableAddRow_"+table_id).append(tempTr)
		
        i++;
    }
	
	// Function to delete row
	 var i = 1;
    function del_row(counter,l,k,kk,j,table_id)
    {
		var current_id = $('.row_seat_id_old_'+ counter +'_'+ l).val();
		var info = 'id=' + current_id;
		if(confirm("Are you sure, you want to remove this row?"))
		{
			$.ajax({
					type: "GET",
					url: "../../events/deleteRowSeat/"+current_id+'/'+k,
					data: info,
					success: function(data){
						$('#tableAddRow_'+table_id).hide('slow').slideUp('slow');
						$('div#div_row_rm_'+table_id).hide('slow').slideUp('slow');
					  }
					});
		}
    }
	
// Del Row
function del_row11(i,k){
 alert('Here = '+ k);
 $("#tabl_row_"+k+"").remove();  
         
}
	

 // Dom
$(document).ready(function () {
	
	// For Adding Event Seat Rows
    var counter_comnt = 0;
    $("#addrowSeats").on("click", function () {
        
		var cols = "<div class='main'>";
		cols +='<div class="row" style="padding-top:10px">';
		cols +='<div class="col-md-2">'+Category_Name_TXT+'</div>';
		cols +='<div class="col-md-2"><input type="text" class="form-control" name="seat_category[]" id="seat_category_'+ counter_comnt +'"  placeholder="'+Enter_Cat_PlaceHolder_TXT+'" /></div>';
		cols +='<div class="col-md-2"><input type="text" style="width: 128px;" maxlength="3" class="form-control" name="seat_row_from[]" id="seat_row_from_'+ counter_comnt +'"  placeholder="'+Row_From_PlaceHolder_TXT+'" /></div>';
		cols +='<div class="col-md-2"><input type="text" style="width: 128px;" maxlength="3" class="form-control range_to" name="seat_row_to[]"  id="seat_row_to_'+ counter_comnt +'"   placeholder="'+Row_To_PlaceHolder_TXT+'" onchange="getUserValue('+counter_comnt+')"/></div>';
        cols += '<div class ="col-md-2"><span class="libres">'+LIBRES_TXT+' </span><input class="attribute_check" type="checkbox" name="libres['+ counter_comnt +']" value="1" id="Libres'+ counter_comnt +'" data-id="['+counter_comnt+']" ></div>'; 
  cols += '<div class="col-md-2"><div  class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill ibtnDelCmntLink"> <span> <i class="la la-trash-o"></i> <span> '+Delete_Seat_TXT+' </span> </span> </div></div>';
		cols +='</div>';
		cols +='<div class="row_new_'+ counter_comnt +'"></div>';
		cols += '</div>';	
        $("#auditorium_seat_div").append(cols).slideDown("slow");
        counter_comnt++;
    });
    
    $("#auditorium_seat_div").on("click", ".ibtnDelCmntLink", function (event) {       
		$(this).closest('div.main').remove();         
        counter_comnt -= 1
    });
	
});

// Save Event Map
function saveEventMap(){
	var div_id_or_class  = '#mesg_div';	
	 var event_id = $('#id').val();
	var url;
     url = "../../events/saveEventSeatTicketMap";
   var formData = new FormData($('#form_add_aud_e')[0]);
   console.log(JSON.stringify(formData));
   
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
			    location.reload();	
				window.location.href='../../events/eventMap/'+event_id;
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		} 
	});
	return false;
}
// Update Event Map
function updateEventMap(){
	 var div_id_or_class  = '#mesg_div';
	var event_group_id = $('#event_group_id').val();
	
	var url;
     url = "../../events/updateEventSeatTicketMap";
   var formData = new FormData($('#form_add_aud_e')[0]);
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
			    //location.reload();	
			window.location.href='../../events/groups/edit/'+event_group_id;
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			//console.log(XMLHttpRequest.toSource());
		}
	});
	return false;
}

// Do not remove auditorium seat ticket
function dontremoveAudSeatTicket(){
	//alert('You can not delete this Category. Some seats are reserved by customers.');
	swal("Warning!",DONT_REMOVE_CAT_TXT,"error");
	return false;
}

// Remove removeAudSeatTicket
function removeAudSeatTicket(id){
	var info = 'id=' + id;
	if(confirm(REMOVE_CAT_CONFIRMATION_TXT+ "?"))
	{
	$.ajax({
	type: "GET",
	url: "../../events/deleteEventSeat/"+id,
	data: info,
	success: function(data){
		$('#aud_seats_div_data_'+id).hide('slow').slideUp('slow');
		$('#aud_seats_div_data_row_'+id).hide('slow').slideUp('slow'); 
	 }
	});
  }
}

// Remove seat row
function removeAudSeatRow(id){	
  var info = 'id=' + id;
	if(confirm("Are you sure, you remove this seat row?"))
	{
	$.ajax({
	type: "GET",
	url: "../../events/deleteEventSeatRow/"+id,
	data: info,
	success: function(data){
		$('#tr_num_row_'+id).hide('slow').slideUp('slow');
	}
	});
	}
}

/** ===================================================   */

function getCateId(sel){
  var categoryID = sel.value;
  if(categoryID == '' || categoryID == 'undefined'){
	  $('#row_id').html(''); 
  }
  if(categoryID != ''){
	  $.ajax({
		type: "GET",
		url: "../../events/getEventSeatCategoryRows/"+categoryID,
		data: '',
		success: function(data){
			$('#row_id').html(data);
		}
	});
  }
}


// view sale report
function ViewSaleReport(){
  var categoryID = $('#category_id').val();
  var rowID = $('#row_id').val();	
  if(categoryID != '' && rowID != ''){
	  $('#view_report_div').html('');
  }
  if(rowID == ''){
	  alert('Please select row');  
  }
  if(categoryID != '' && rowID != ''){
	   $.ajax({
		type: "GET",
		url: "../../events/getCategoryRowSale/"+categoryID+'/'+rowID,
		data: '',
		success: function(data){
			$('#view_report_div').html(data).slideDown('slow');
		}
	});
  }
}

// Clear sale report
function clearSaleReport(){
	$('#category_id').val('');
    $('#row_id,#view_report_div').html('').slideDown('slow');
}

// Function to download sale report
function downloadSaleReport(){
  var categoryID = $('#category_id').val();
  var rowID = $('#row_id').val();
  var eventID = $('#event_id').val();	
  if(categoryID != '' && rowID != '' && eventID != ''){
	 window.location.href='../../events/downloadSaleReportPDF/'+categoryID+'/'+rowID+'/'+eventID;
  }else{
	alert(SEAT_MAP_REPORT_ERROR_TXT);  
  }
	
}


// Function to add more seats
function addSeatRowFresh(event_id, cat_id, row_id)
{
	$.ajax({
			type: "GET",
			url: "../../events/saveNewRowSeatFresh/"+event_id+'/'+cat_id+'/'+row_id,
			data: '',
			success: function(data){
				toastr.options = {
					  "closeButton": false,
					  "debug": false,
					  "newestOnTop": false,
					  "progressBar": true,
					  "positionClass": "toast-top-right",
					  "preventDuplicates": false,
					  "onclick": null,
					  "showDuration": "300",
					  "hideDuration": "1000",
					  "timeOut": "4000",
					  "extendedTimeOut": "1000",
					  "showEasing": "swing",
					  "hideEasing": "linear",
					  "showMethod": "fadeIn",
					  "hideMethod": "fadeOut"
				  };
				
				toastr.success(SEAT_CREATED_SUCCESSFULLY_TXT, "Good Job!");
				location.reload();
				//$('#'+table_id).append(data).children("td").effect("highlight", { color: "#4ca456" }, 1000);/*.slideDown(5000);*/
				
			}
	});	
}



// Function to add more seats
function addSeatRow(table_id,event_id, cat_id, row_id)
{
	$.ajax({
			type: "GET",
			url: "../../events/saveNewRowSeat/"+event_id+'/'+cat_id+'/'+row_id+'/'+table_id,
			data: '',
			success: function(data){
				toastr.options = {
					  "closeButton": false,
					  "debug": false,
					  "newestOnTop": false,
					  "progressBar": true,
					  "positionClass": "toast-top-right",
					  "preventDuplicates": false,
					  "onclick": null,
					  "showDuration": "300",
					  "hideDuration": "1000",
					  "timeOut": "4000",
					  "extendedTimeOut": "1000",
					  "showEasing": "swing",
					  "hideEasing": "linear",
					  "showMethod": "fadeIn",
					  "hideMethod": "fadeOut"
				  };
				
				toastr.success(SEAT_CREATED_SUCCESSFULLY_TXT, "Good Job!");
				$('#'+table_id).append(data).children("td").effect("highlight", { color: "#4ca456" }, 1000);/*.slideDown(5000);*/
				
			}
	});	
}




// Function to delete seat from the new table
function removeSeat(id){
	var selected_row = $("#tabl_id_row_"+id);
	selected_row.css("background-color","#fb6c6c");
	//var parentv = $('tr#tabl_id_row_"'+id);
	if(confirm(SEAT_REMOVE_TXT+"?"))
	{
	$.ajax({
			type: "GET",
			url: "../../events/removeNewRowSeat/"+id,
			data: '',
			beforeSend: function() {
				selected_row.css("background-color","#fb6c6c");
			},
			success: function(data){
				if(data == 'Booked'){
					toastr.options = {
				  "closeButton": false,
				  "debug": false,
				  "newestOnTop": false,
				  "progressBar": true,
				  "positionClass": "toast-top-right",
				  "preventDuplicates": false,
				  "onclick": null,
				  "showDuration": "300",
				  "hideDuration": "1000",
				  "timeOut": "4000",
				  "extendedTimeOut": "1000",
				  "showEasing": "swing",
				  "hideEasing": "linear",
				  "showMethod": "fadeIn",
				  "hideMethod": "fadeOut"
				};
				
			    toastr.success('This seat is already booked', SUCCES_TXT+"!");
				location.reload();
				}else{
				selected_row.css("background-color","#fcd8b2");
				selected_row.fadeOut('slow', function() {selected_row.remove();});
			  toastr.options = {
				  "closeButton": false,
				  "debug": false,
				  "newestOnTop": false,
				  "progressBar": true,
				  "positionClass": "toast-top-right",
				  "preventDuplicates": false,
				  "onclick": null,
				  "showDuration": "300",
				  "hideDuration": "1000",
				  "timeOut": "4000",
				  "extendedTimeOut": "1000",
				  "showEasing": "swing",
				  "hideEasing": "linear",
				  "showMethod": "fadeIn",
				  "hideMethod": "fadeOut"
				};
				
			toastr.error(SEAT_DELETED_TXT, SUCCES_TXT+"!");
			}
				//$('#'+table_id).append(data).slideDown('slow');
			}
	});
  }else{
	selected_row.css("background-color","#fff");  
  }
}



// Update the new created Seat
function updateNewSeat(id){
	var record_id = id;
	var seat_type_id = $('#seat_type_'+id).val();
	var seat_type_text = $('#selected_placement_text').val();
	var op_val_id = $('#op_val_'+id).val();
	var seat_number = $('#seat_number_val_'+id).val();
	var seat_price = $('#seat_price_val_'+id).val();
	var select_opt_id = $('#selected_seat_option').val();
	var selected_placement_text = $('#selected_placement_text').val();
	var event_seat_row_id = $('#event_seat_row_id').val();
	// Prepare form data to send to the method to update it.
	var form_posted_data = 'id='+record_id+'&placement='+seat_type_id+'&operator_id='+op_val_id+'&seat_number='+seat_number+'&seat_price='+seat_price+'&select_opt_id='+select_opt_id;
	$.ajax({
			type: "POST",
			url: "../../events/changeNewRowSeatUpdate",
			data: form_posted_data,
			success: function(data){
				if(data == 'E'){
					swal(DUPLICATION_TXT+"!",SEAT_ALREADY_EXIST_TXT+"!","error");
				}else{
				// Send another request to bring the plain mode
				 $.ajax({
						type: "GET",
						url: "../../events/changeSeatRowMode/"+id+'/show_plain_mode',
						data: '',
						success: function(data){
							//$('#tabl_id_row_'+id).replaceWith(data);
							if(select_opt_id == 'Y'){
							  $('.seat_type_class_'+event_seat_row_id).text('');
							  $('.seat_type_class_'+event_seat_row_id).text(seat_type_text);
							}
							$('#tabl_id_row_'+id).replaceWith(data).slideUp(5000);
						}
					});
					
					toastr.options = {
					  "closeButton": false,
					  "debug": false,
					  "newestOnTop": false,
					  "progressBar": true,
					  "positionClass": "toast-top-right",
					  "preventDuplicates": false,
					  "onclick": null,
					  "showDuration": "300",
					  "hideDuration": "1000",
					  "timeOut": "4000",
					  "extendedTimeOut": "1000",
					  "showEasing": "swing",
					  "hideEasing": "linear",
					  "showMethod": "fadeIn",
					  "hideMethod": "fadeOut"
				  };		
				toastr.success(UPDATED_TXT, SUCCES_TXT+"!");
			  }
			}
	});
}

// Edit this row
function editThisRow (id){
   	$.ajax({
			type: "GET",
			url: "../../events/changeSeatRowMode/"+id+'/show_edit_mode',
			data: '',
			success: function(data){
				// Replace the row and display the edit mode
				$('#tabl_id_row_'+id).replaceWith(data);
			}
	});
}

// Function changePlacement
function changePlacement(sel, id){
  selected_id = sel.value;
  record_id   = id;
 // $('#operator_span_'+record_id).css('display', 'none'); 
  if(selected_id == 4){
	  $('#operator_span_'+record_id).css('display', 'block');
  }else{
	 $('#operator_span_'+record_id).css('display', 'none'); 
  }
  $('#selected_placement_text').val($('#seat_type_'+id+' option:selected').text());
 
}

// Function to view Popup function 
// Function to view Popup function 
function view_seat_popup(seat_id)
{   
    //Ajax Load data from ajax
    $.ajax({
        url : "../../events/getSeatHistory/" + seat_id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
			$('#seat_changed_data').html(data['seat_changed_data']);
			$('#seat_changed_data_total').html(data['seat_changed_data_total']);
			$('#seat_refund_data').html(data['seat_refund_data']);
			$('#seat_refund_data_total').html(data['seat_refund_data_total']);
			$('#seat_email_button').html(data['seat_email_button']);
			$('#admin_nots_span').html(data['admin_notes']);
			$('#seat_id').val(data['seat_id']);
			$('#seatLogHistoryModel').modal('show'); // show bootstrap modal when complete loaded
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}


// Functio to get the seat type changed
function changeSeatType(sel) {
	 if (sel.value == 'Y') {
		$('#selected_seat_option').val('Y');
		swal("Warning!","This will change placement for all seats of this row. Are you sure you want to change for all seats of this row?","error");
	}else{
		$('#selected_seat_option').val('N');
	}
}


// Save the new seat
function SaveNewSeat(){
	var row_number = $('#row_number').val();
	var from_value_new = $('#from_value_new').val();
	var to_value_new = $('#to_value_new').val();
	//var seat_order_new = $('#seat_order_new').val();
	var seat_type = $('#seat_type').val();
	var operator_id = $('#operator_id').val();
	var cat_id = $('#cat_id').val();
	var row_id = $('#row_id').val();
	var isChecked = $('#seat_order_new').is(':checked');
	if(isChecked){
	   var seat_order_new = '2';	
	}else{
	   var seat_order_new = '1';		
	}
	
	toastr.options = {
				  "closeButton": true,
				  "debug": false,
				  "newestOnTop": false,
				  "progressBar": true,
				  "positionClass": "toast-top-center",
				  "preventDuplicates": true,
				  "onclick": null,
				  "showDuration": "300",
				  "hideDuration": "1000",
				  "timeOut": "5000",
				  "extendedTimeOut": "1000",
				  "showEasing": "swing",
				  "hideEasing": "linear",
				  "showMethod": "fadeIn",
				  "hideMethod": "fadeOut"
				};


	
	if(row_number == ''){
		toastr.error("Please enter Row Number", "Error!");
	}else if(from_value_new == ''){
		toastr.error("Please enter From Value", "Error!");
	}else if(to_value_new == ''){
		toastr.error("Please enter To Value", "Error!");
	}else{
	// Prepare form data to send to the method to update it.
	var form_posted_data = 'from_value_new='+from_value_new+'&to_value_new='+to_value_new+'&seat_order_new='+seat_order_new+'&seat_type='+seat_type+'&operator_id='+operator_id+'&row_number='+row_number+'&cat_id='+cat_id+'&row_id='+row_id;
	$.ajax({
			type: "POST",
			url: "../../events/saveNewRowSeatUpdate",
			data: form_posted_data,
			success: function(data){
				//return false;
				if(data == 'E'){
					//alert('Error occured');
					swal("Duplication Occurred!","Row already exist!","error");
				}else{
					toastr.options = {
					  "closeButton": false,
					  "debug": false,
					  "newestOnTop": false,
					  "progressBar": true,
					  "positionClass": "toast-top-right",
					  "preventDuplicates": false,
					  "onclick": null,
					  "showDuration": "300",
					  "hideDuration": "1000",
					  "timeOut": "4000",
					  "extendedTimeOut": "1000",
					  "showEasing": "swing",
					  "hideEasing": "linear",
					  "showMethod": "fadeIn",
					  "hideMethod": "fadeOut"
				  };	
					// Show message	
				toastr.success('Row created successfully!', "Success!");
				location.reload();
			  }
			}
	});
	
  }
}


// Function hide row
function hideRowSeat(table_id){
	$('#btn_'+table_id).show();
	$('#btnMultiple_'+table_id).show();
	$('#newFreshRow_'+table_id).html('');/*.slideDown(5000);*/
}

// Function changePlacement
function changePlacementFresh(sel, id){
  selected_id = sel.value;
  record_id   = id;
 // $('#operator_span_'+record_id).css('display', 'none'); 
  if(selected_id == 4){
	  $('#operator_span_'+record_id).css('display', 'block');
  }else{
	 $('#operator_span_'+record_id).css('display', 'none'); 
  }
  //$('#selected_placement_text').val($('#seat_type_'+id+' option:selected').text());
 
}

// Function to add new row
function addNewSeatRow(table_id,event_id, cat_id, row_id)
{
	
	$.ajax({
			type: "GET",
			url: "../../events/createNewRow/"+event_id+'/'+cat_id+'/'+row_id+'/'+table_id,
			data: '',
			success: function(data){
				$('#btn_'+table_id).hide();
				$('#btnMultiple_'+table_id).hide();
				$('#newFreshRow_'+table_id).html(data);/*.children("td").effect("highlight", { color: "#4ca456" }, 1000);/*.slideDown(5000);*/
				
			}
	});	
}

// Function to add new  multiple seats
function addMultipleSeatsRowFresh(table_id,event_id, cat_id, row_id, seat_number)
{
	
	$.ajax({
			type: "GET",
			url: "../../events/saveNewMultipleSeatsFresh/"+event_id+'/'+cat_id+'/'+row_id+'/'+table_id+'/'+seat_number,
			data: '',
			success: function(data){
				$('#btn_'+table_id).hide();
				$('#btnMultiple_'+table_id).hide();
				$('#newFreshRow_'+table_id).html(data);/*.children("td").effect("highlight", { color: "#4ca456" }, 1000);/*.slideDown(5000);*/
				
			}
	});	
}

// Function hide Multiple seats row
function hideRowSeatMultiple(table_id){
	$('#btn_'+table_id).show();
	$('#btnMultiple_'+table_id).show();
	$('#newFreshRow_'+table_id).html('');/*.slideDown(5000);*/
}

// Save the new Multiple seat
function SaveNewMultipleSeats(){
	var seat_price = $('#seat_price').val();
	var from_value_new = $('#from_value_new').val();
	var to_value_new = $('#to_value_new').val();
	//var seat_order_new = $('#seat_order_new').val();
	var seat_type = $('#seat_type').val();
	var operator_id = $('#operator_id').val();
	var cat_id = $('#cat_id').val();
	var row_id = $('#row_id').val();
	var event_id = $('#event_id').val();
	var row_number = $('#row_number').val();
	
	toastr.options = {
				  "closeButton": true,
				  "debug": false,
				  "newestOnTop": false,
				  "progressBar": true,
				  "positionClass": "toast-top-center",
				  "preventDuplicates": true,
				  "onclick": null,
				  "showDuration": "300",
				  "hideDuration": "1000",
				  "timeOut": "5000",
				  "extendedTimeOut": "1000",
				  "showEasing": "swing",
				  "hideEasing": "linear",
				  "showMethod": "fadeIn",
				  "hideMethod": "fadeOut"
				};


	
	if(from_value_new == ''){
		toastr.error("Please enter From Value", "Error!");
	}else if(to_value_new == ''){
		toastr.error("Please enter To Value", "Error!");
	}else if(seat_price == ''){
		toastr.error("Please enter seat price", "Error!");
	}else{
	// Prepare form data to send to the method to update it.
	var form_posted_data = 'from_value_new='+from_value_new+'&to_value_new='+to_value_new+'&seat_type='+seat_type+'&operator_id='+operator_id+'&seat_price='+seat_price+'&cat_id='+cat_id+'&row_id='+row_id+'&event_id='+event_id+'&row_number='+row_number;
	$.ajax({
			type: "POST",
			url: "../../events/saveRowMultipleSeatsUpdate",
			data: form_posted_data,
			success: function(data){
				//return false;
				if(data == 'E'){
					//alert('Error occured');
					swal("Duplication Occurred!","Row already exist!","error");
				}else{
					toastr.options = {
					  "closeButton": false,
					  "debug": false,
					  "newestOnTop": false,
					  "progressBar": true,
					  "positionClass": "toast-top-right",
					  "preventDuplicates": false,
					  "onclick": null,
					  "showDuration": "300",
					  "hideDuration": "1000",
					  "timeOut": "4000",
					  "extendedTimeOut": "1000",
					  "showEasing": "swing",
					  "hideEasing": "linear",
					  "showMethod": "fadeIn",
					  "hideMethod": "fadeOut"
				  };	
					// Show message	
				toastr.success('Seats created successfully!', "Success!");
				//return false;
				location.reload();
			  }
			}
	});
	
  }
}




function sendData(data){
	
  var admin_comments = data.value;
  var seat_id = $('#seat_id').val();
  var formData = 'admin_comments='+admin_comments+'&seat_id='+seat_id;	
  //if(admin_comments.length>0){
    $.ajax({
        url : "../../events/saveSeatComments",
        type: "POST",
		data: formData,
        dataType: "JSON",
        success: function(data)
        {
			
			/*swal({
            title: "Etes vous sûr",
            text: "You will not be able to recover this imaginary file!",
            type: "warning",
            showCancelButton: false,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        });*/
			/*swal({
			title              :  "Etes vous sûr ?",
              text               :  "Le formulaire sera définitivement perdu !",
              type               : "warning",
              showCancelButton   : true,
              confirmButtonColor : "#DD6B55",
              confirmButtonText  : "Oui, supprimer",
              cancelButtonText   :  "Annuler",
              closeOnCancel      : true
			 });*/
			swal({
				 
        title: "Succ&egrave;s!",
        text: 'Commentaire sauvegarde avec succes',
        type: "success"
		//html: true
        //confirmButtonText: "Ok!",
        //closeOnConfirm: false,
        //html: true
    });
			//swal(
				 /*title: "Succ&egrave;s!",
        text: 'Commentaire sauvegard&eacute; avec succ&egrave;s',
        type: "success",
        confirmButtonText: "Ok!",
        closeOnConfirm: false,
        html: true*/
								  /*'Succ&egrave;s!',
								  'Commentaire sauvegard&eacute; avec succ&egrave;s',
								  'success',
								  html: true*/
								  /*swal(
								   title:  'Succ&egrave;s!',
								  text : 'Commentaire sauvegardé avec succès',
								  type : 'success',
								  html: 'true'
								);*/	
								//);			
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
      });
	//}  
}






