// First clear the local storage
localStorage.clear();

$(document).ready(function () {
	
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
function addAuditorium(){
	var local_storage_key = allStorage(); 
	$('#auditorium_key').val(Object.keys(localStorage));
	$('#auditorium_seats_map').val(local_storage_key);
	var div_id_or_class  = '#mesg_div';
	if($("#auditorium_name").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(auditorium_name_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#auditorium_name").focus();
		return false;
	}
	if($("#auditorium_picture").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(auditorium_pic_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#auditorium_picture").focus();
		return false;
	}
	
	
	
	if($("#auditorium_access").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(auditorium_access_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#auditorium_access").focus();
		return false;
	}
	
	if($("#waze_name").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(auditorium_waze_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#waze_name").focus();
		return false;
	}
	
	if($("#m_summernote_1").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(auditorium_detail_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#m_summernote_1").focus();
		return false;
	}
	
	if($("#pac-input").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html(auditorium_address_msg_txt);
		hideErrorDiv(div_id_or_class);
		$("#pac-input").focus();
		return false;
	}
	var url;
    url = "../saveAuditorium";
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
		}else if(data.status == 'error'){
			$(div_id_or_class).show().addClass('alert alert-danger').html(data.message);
			hideErrorDiv(div_id_or_class);
		}else if(data.status) {
			 localStorage.clear(); // Clear the Storage
			 window.location.href='../auditoriums';	
		}
	}, 
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest.toSource());
		}
	});
	return false;

}

