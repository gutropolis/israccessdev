var DatatableRemoteAjaxDemoCat = {
	
    init: function() {
       // var t;
        t = $(".m_datatable_evg").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "groups/getAjaxEventGroupsList",
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
                input: $("#generalSearch")
            },
            columns: [{
                field: "id",
                title: "#",
                sortable: !1,
                width: 30,
                selector: !1,
                textAlign: "center",
				 selector:{
					id : "check_box_class",
					class:"m-checkbox--solid m-checkbox--brand"}
            }, {
                field: "title",
                title: Title,
                filterable: !1,
                width: 150,
                template: "{{title}}"
            },
			{
                field: "category_name",
                title: Category_Name,
                filterable: !0,
                width: 150,
                template: "{{category_name}}"
            },
			{
                field: "date_begin",
                title: Date_Begin,
                filterable: !1,
                width: 110,
                template: "{{date_begin}}"
            },
			{
                field: "date_end",
                title: Date_End,
                filterable: !0,
                width: 100,
                template: "{{date_end}}"
            },
			{
                field: "display_order",
                title: order_TXT,
                filterable: !0,
                width: 50,
                template: "{{display_order}}"
            },
			{
                field: "status",
                title: STATUS,
                template: function(t) {
                    var e = {
                        0: {
                            title: INACTIVE,
                            class: "m-badge--danger",
							statusTitle : "Active"
                        },
                        1: {
                            title: ACTIVE,
                            class: " m-badge--success",
							statusTitle : "Inactive"
                        },
						2: {
                            title: Archived_TXT,
                            class: " m-badge--warning",
							statusTitle : "Archived"
                        }
                    };
                    return '<span class="m-badge ' + e[t.status].class + ' m-badge--wide">' + e[t.status].title + "</span>"
                }
            },
			{
                field: "Actions",
                width: 180,
                title: ACTIONS,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
					
                    return '\t\t\t\t\t\t<a  href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+event_group_txt+'" onclick="mark_status('+[t.id]+')" >\t\t\t\t\t\t\t<i class="la la-recycle"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a  href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+common_duplicate_txt+' '+event_group_txt+'" onclick="duplicate_function('+[t.id]+')" >\t\t\t\t\t\t\t<i class="la la-copy"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+common_view_txt+''+event_group_txt+'" onclick="view('+[t.id]+')" >\t\t\t\t\t\t\t<i class="la la-eye"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a href="javascript:void(0);"  class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+EDIT+' '+event_group_txt+'" onclick="edit('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE+' '+event_group_txt+'">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t'
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


var DatatableRemoteAjaxDemoCatArchived = {
	
    init: function() {
       // var t;
        t = $(".m_datatable_evg_archive").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "groups/getAjaxEventGroupsArchivedList",
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
                input: $("#generalSearch")
            },
            columns: [{
                field: "id",
                title: "#",
                sortable: !1,
                width: 30,
                selector: !1,
                textAlign: "center",
				 selector:{
					id : "check_box_class",
					class:"m-checkbox--solid m-checkbox--brand"}
            }, {
                field: "title",
                title: Title,
                filterable: !1,
                width: 150,
                template: "{{title}}"
            },
			{
                field: "category_name",
                title: Category_Name,
                filterable: !0,
                width: 150,
                template: "{{category_name}}"
            },
			{
                field: "date_begin",
                title: Date_Begin,
                filterable: !1,
                width: 110,
                template: "{{date_begin}}"
            },
			{
                field: "date_end",
                title: Date_End,
                filterable: !0,
                width: 100,
                template: "{{date_end}}"
            },
			{
                field: "display_order",
                title: order_TXT,
                filterable: !0,
                width: 50,
                template: "{{display_order}}"
            },
			{
                field: "status",
                title: STATUS,
                template: function(t) {
                    var e = {
                        0: {
                            title: INACTIVE,
                            class: "m-badge--danger",
							statusTitle : "Active"
                        },
                        1: {
                            title: ACTIVE,
                            class: " m-badge--success",
							statusTitle : "Inactive"
                        },
                        2: {
                            title: Archived_TXT,
                            class: " m-badge--warning",
							statusTitle : "Archived"
                        }
                    };
                    return '<span class="m-badge ' + e[t.status].class + ' m-badge--wide">' + e[t.status].title + "</span>"
                }
            },
			{
                field: "Actions",
                width: 180,
                title: ACTIONS,
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
					
                    return '\t\t\t\t\t\t<a  href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+change_status_label_txt+'" onclick="mark_status('+[t.id]+')" style="display:none" >\t\t\t\t\t\t\t<i class="la la-recycle"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a  href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+common_duplicate_txt+' '+event_group_txt+'" onclick="duplicate_function('+[t.id]+')" style="display:none" >\t\t\t\t\t\t\t<i class="la la-copy"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+common_view_txt+''+event_group_txt+'" onclick="view('+[t.id]+')" style="display:none">\t\t\t\t\t\t\t<i class="la la-eye"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a href="javascript:void(0);"  class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="'+EDIT+' '+event_group_txt+'" onclick="edit_archive('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>\t\t\t\t\t\t<a style="display:none" href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="'+DELETE+' '+event_group_txt+'">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t'
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
   if(group_page == 'Y'){
    DatatableRemoteAjaxDemoCat.init();
   }else{
	DatatableRemoteAjaxDemoCatArchived.init();
   }
});


function reloadTable()
{
    t.reload(); //reload datatable ajax 
}


// Add
function addEventGroup(){
	var div_id_or_class  = '#mesg_div';
	
	textbox_data = CKEDITOR.instances.title.getData();
    if (textbox_data==='')
	{
		$("#title").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_title_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	if($("#thumbnail_title").val()=="")
	{
		//$("#en_savoir_block1_name").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_card_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($("#group_picture").val()=="")
	{
		$("#group_picture").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_pic_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($("#price_min").val()=="")
	{
		$("#price_min").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_price_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($(".artist_id").val()=="")
	{
		$(".artist_id").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_artist_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($(".category_id").val()=="")
	{
		$(".category_id").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_category_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($(".date_begin").val()=="")
	{
		$(".date_begin").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_date_begin_ms_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
	
	if($(".date_end").val()=="")
	{
		$(".date_end").focus();
		$(div_id_or_class).show().addClass('alert alert-danger').html(event_group_pic_msg_txt);
		hideErrorDiv(div_id_or_class);
		return false;
	}
    
	for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
    }
	
	var url;
    url = "groups/saveEventGroup";
   var formData = new FormData($('#form_add_event_group')[0]);
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
				$('#modal-add-event-group').modal('hide');
				$('#form_add_event_group')[0].reset();
				// Redirect to the edit page
				window.location.href='groups/edit/'+data.id;
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
	swal({
		  title: 'Attention',
		  text: archive_group_alert_txt+"?",
		  type: 'error',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: archive_group_yes_txt+'!'
		}).then((result) => {
		  if (result.value) {
			var formData = new FormData($('#form_del_data')[0]);
			// ajax delete data from database
			$.ajax({
				url : "groups/delete/"+$('#id').val(),
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
		  }else{
			$('#modal-delete').modal('hide');
		  }
		  });
}

// Delete Function
function delete_function(id){
   $('.del_title').html(DELETE+' '+event_group_txt);
   $('.del_text').html( common_delete_confirm_msg_txt +event_group_txt+'?');	
   $('#id').val(id);
   $('#modal-delete').modal('show');
}

function view(id)
{   

    //Ajax Load data from ajax
    $.ajax({
        url : "groups/get/" + id,
        type: "GET",
        dataType: "JSON",
        success: function(data)
        {
            $('#title_e').text(data['title']);
			$('#price_min_e').text(data['price_min']);
			$('#artist_id_e').text(data['artist_name']);
			$('#category_id_e').text(data['category_name']);
			$('#date_begin_e').text(data['date_begin']);
			$('#date_end_e').text(data['date_end']);
			$('#description_e').text(data['description']);
			var back_file = data['group_picture'];
			if(back_file != '')
            {
                $('#photo-preview-view div').html('<img src="'+data['file_web_path']+'/'+data['group_picture']+'" class="img-rounded img-thumbnail" >'); // show photo       
            }
            else
            {
                //$('#label-photo').text('Upload Photo'); // label photo upload
                $('group_picture div').text(common_no_photo_txt);
            }
			
			$('#modal-view-event-group').modal('show'); // show bootstrap modal when complete loaded
			
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}

function edit(id){
    window.location.href='groups/edit/'+id;	
}

function edit_archive(id){
    window.location.href='groups/edit_archive/'+id;	
}

// Duplicate Function
function duplicate_function(id){
   $('.dup_title').html(event_group_dupli_txt);
   $('.dup_text').html(common_duplicate_msg_txt+'?');	
   $('#id_duplicate').val(id);
   $('#modal-duplicate').modal('show');
}

function duplicateEventGroup(id){
	var id = $('#id_duplicate').val(); // get id from the popup
  // ajax delete data from database
	$.ajax({
		url : "groups/duplicate/"+id,
		type: "GET",
		dataType: "JSON",
		data: {'id' : id},
		processData: false,
		contentType: false,
		success: function(data)
		{
			$('#modal-duplicate').modal('hide');
			reloadTable();
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert('Error deleting data');
		}
	});
}

function mark_status(id){
	if(id != ''){
		var info = 'id='+id;
		$.ajax({
		type: "GET",
		url: "groups/updateEventGroupStatus/"+id,
		//data: info,
		success: function(data){
			location.reload();
		}
		});
	}
}

// Check if category is changed
$(function(){
    $('#m_select2_1_validate').on('change', function() {	
	 $('#permalink').val('');
var category_id = $("#m_select2_1_validate option:selected").val();
	  var title = $('#title').val();
	  var regX = /(<([^>]+)>)/ig;
	  var brX  = /[&]nbsp[;]/gi;
	  var brXX = /[<]br[^>]*[>]/gi;
	  if(category_id != '' && title != ''){
		  var category_text = $("#m_select2_1_validate option:selected").text();
	      title = title.replace(regX, "").trim();
		  title = title.replace(brX, "").trim();
		  title = title.replace(brXX, "").trim();
		  title = title.replace(/<\/p>/gi, "\n");
		  var parmalink_title = '/'+category_text+'/'+title+'/';
		  parmalink_title = parmalink_title.split(' ').join('-').trim();
		  parmalink_title = parmalink_title;
		  $('#permalink').val(parmalink_title);
	  }
    })
});


function edit_selected_rows_fields_group(selected_field)
{   
    var modal_title = false;
	var table_name = $('#table_name').val();
	
	if(selected_field == "delete_selected"){
		modal_title = DELETE_SELECTED_ROWS_TXT;
	}
	if(selected_field == "update"){
		modal_title = UPDATE_SELECTED_ROWS_TXT;
	}
	if(selected_field == "change_status"){
		modal_title = ALL_DELETE_SELECTED_ROWS_TXT;
	}
	   $('.editmodaltitle').html(modal_title);
	   var checkboxValues = [];
		$('.m-checkbox>input, .m-radio>input').each(function() 
		{    
			if($(this).is(':checked'))
			{
			  checkboxValues.push($(this).val());
			}
		});
		if(checkboxValues.length > 0){
		var urlquerystring ='?'
		for (var i = 0; i < checkboxValues.length; i++) {
			urlquerystring += 'lead_id_'+checkboxValues[i]+'='+checkboxValues[i]+'&';
		}
		
		if(selected_field != ''){
	    $('#with_selected_options_popup').modal('show');
		var postData={ selected_field:selected_field, table_name : table_name}
			$.ajax({
			type: 'POST',
			data: postData,
			url: base_url_constant+'display_selected_option_popup'+urlquerystring, 
			success: function(result){
				$('.updatetext').html(result);
				$(".SubmitBtn").attr("onclick","return update_options_bulk_data_group()");
			}});

	 }
	 
	}
}

function update_options_bulk_data_group(){
	 swal({
		  title: 'Attention',
		  text: "All events in this group wil be archive, do you want to proceed ?",
		  type: 'error',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, archive it!'
		}).then((result) => {
		  if (result.value) {
       var table_id = $('#datable_table_id').val();
	    $.ajax({
		type: 'POST',
		dataType: "JSON",
		data: $('#form_edit_bulk_data').serialize(),
		url: base_url_constant+'edit_bulk_data', 
		success: function(data){
		  if(data.status){
			 $("input:checkbox").prop('checked', false);
			 $(".first").prop("checked", false);
			 $('#selected_fields_edit').val('');
		     $('#with_selected_options_popup').modal('hide');
			 if(table_id == 'm_datatable_group_events'){
				 location.reload();
			 }else{
			    reloadTable();
			 }
		  }
		}});
		
		}else{
			$('#selected_fields_edit').val('');
			$('#with_selected_options_popup').modal('hide');
		  }
	});	
		
}











