jQuery(document).ready(function() {
	
});

function reloadDashboard(){
  window.location.href='log_in_as';	
}

// Functio to show sale report
function showSaleReport(sel)
{
	var event_id = sel.value;
	if(event_id != 'undefined' || event_id != ''){
	  $('#saleReportFrm').submit();	
	}
}

// function of Log in As
function LogInAs(){
   	var role_id = $('#role_id').val();
	var admin_user_id = $('#admin_user_id').val();
	if(role_id == 'undefined' || role_id == ''){
		alert('Please select role first');
		   return false;
	}else if(admin_user_id == 'undefined' || admin_user_id == ''){
		alert('Please select admin user');
		   return false;
	}else{
	  $('#logInAsFrm').submit();		
	}
}

//  Change event for Event Group
$(function(){
    $('#role_id').on('change', function() {
	$('#admin_user_id,#role_modules_div').html('');
	  var role_id = $("#role_id option:selected").val();
	  
	  if(role_id != ''){
	  var info = 'role_id=' + role_id;
		$.ajax({
		type: "GET",
		url: "./getAdminUsersList/"+role_id,
		data: '',
		success: function(data){
			$('#admin_user_id').html(data); 
			// Send another ajax request
			$.ajax({
			type: "GET",
			url: "./getRoleModulesList/"+role_id,
			data: '',
			success: function(data){
				$('#role_modules_div').html(data);	
			}
			});
		}
		});
	  }
    })
});


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

// Function to download modules
function downloadModulesReport(){
  alert('In progress');	
}













