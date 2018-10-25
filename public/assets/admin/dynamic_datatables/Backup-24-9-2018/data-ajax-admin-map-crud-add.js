// First clear the local storage
//localStorage.clear();

localStorage.setItem('key', mapKey); // Set the Key
localStorage.setItem('value', mapVal); // Set the Value


jQuery(document).ready(function() {
  
});






function allStorage() {

    var values = [],
        keys = Object.keys(localStorage),
        i = keys.length;

    while ( i-- ) {
        values.push( localStorage.getItem(keys[i]) );
    }

    return values;
}

// Add
function addAuditoriumDigitalMap(){
	var local_storage_key = allStorage(); 
	$('#auditorium_key').val(Object.keys(localStorage));
	$('#auditorium_seats_map').val(local_storage_key);
	
	var div_id_or_class  = '#mesg_div';
	if($("#auditorium_key").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please set the map');
		hideErrorDiv(div_id_or_class);
		$("#auditorium_key").focus();
		return false;
	}
	if($("#auditorium_seats_map").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('The map really needs to be set for the auditorium');
		hideErrorDiv(div_id_or_class);
		$("#auditorium_seats_map").focus();
		return false;
	}
	
	
	var  eventgroup_id = $('#eventgroup_id').val();
	
	
	var url;
    url = "../../saveAuditoriumDigitalMap";
   var formData = new FormData($('#form_add_aud')[0]);
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
			 localStorage.clear(); // Clear the Storage
			 //reload.location();
			 // Redirect to the previous page
			 window.location.href='../../events/groups/edit/'+eventgroup_id;
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;

}













