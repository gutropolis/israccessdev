var DatatableRemoteAjaxDemoCat = {
	
    init: function() {
        //var t;
        t = $(".m_datatable_community_page").mDatatable({
            data: {
                type: "remote",
                source: {
                    read: {
                        url: "getAjaxCommunityPageList",
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
                field: "title",
                title: "Community Title",
                filterable: !1,
                width: 500,
                template: "{{title}}"
            },
			{
                field: "display_order",
                title: "Display Order",
                filterable: !1,
                width: 100,
                template: "{{display_order}}"
            },
			 {
                field: "status",
                title: "Status",
				width: 100,
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
                width: 100,
                title: "Actions",
                sortable: !1,
                overflow: "visible",
                template: function(t, e, a) {
					var edit_lnk = '';
					var del_lnk = '';
					var edit_link = '\t\t\t\t\t\t<a href="javascript:void(0);" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Edit Community Page" onclick="edit('+[t.id]+')">\t\t\t\t\t\t\t<i class="la la-edit"></i>\t\t\t\t\t\t</a>';
					var del_link = '\t\t\t\t\t\t<a href="javascript:void(0);" onclick="delete_function('+[t.id]+')" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete Community Page">\t\t\t\t\t\t\t<i class="la la-trash"></i>\t\t\t\t\t\t</a>\t\t\t\t\t';
					if(IS_COMMUNITY_PAGE_EDIT == 'Y'){
						var edit_lnk = edit_link;
					}
					if(IS_COMMUNITY_PAGE_DEL == 'Y'){
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



jQuery(document).ready(function() {
	if(IS_COMMUNITY_PAGE_ADD == 'Y' || IS_COMMUNITY_PAGE_EDIT == 'Y' || IS_COMMUNITY_PAGE_DEL == 'Y'){							
      DatatableRemoteAjaxDemoCat.init()
	}
});

function reloadTable()
{
    t.reload(); //reload datatable ajax 
}


// Add
function addCommunityPage(){
	var div_id_or_class  = '#mesg_div';
	
	if($("#summernote_title").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter community page title.');
		hideErrorDiv(div_id_or_class);
		//$("#title").focus();
		return false;
	}
	
	if($("#m_summernote_1").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter short description.');
		hideErrorDiv(div_id_or_class);
		//$("#numbers").focus();
		return false;
	}
	
	if($("#m_summernote_2").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter full description.');
		hideErrorDiv(div_id_or_class);
		//$("#numbers").focus();
		return false;
	}
	
	
	var url;
    url = "../saveCommunityPage";
   var formData = new FormData($('#form_add_community_add')[0]);
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
			window.location.href='../community_page';
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
	window.location.href='community_page/edit/' + id   
}

// Update
function updateCommunityPage(){
	var div_id_or_class  = '#mesg_div_e';
	if($("#summernote_title").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter community page title.');
		hideErrorDiv(div_id_or_class);
		//$("#title").focus();
		return false;
	}
	
	if($("#m_summernote_1").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter short description.');
		hideErrorDiv(div_id_or_class);
		//$("#numbers").focus();
		return false;
	}
	
	if($("#m_summernote_2").val()=="")
	{
		$(div_id_or_class).show().addClass('alert alert-danger').html('Please enter full description.');
		hideErrorDiv(div_id_or_class);
		//$("#numbers").focus();
		return false;
	}
	
	
	var url;
     url = "../../community_page/update";
   var formData = new FormData($('#form_add_community_edit')[0]);
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
				window.location.href='../../community_page';
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
		url : "community_page/delete/"+id,
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
   $('.del_title').html('Delete Community Page');
   $('.del_text').html('Are you sure you want to delete this Community Page?');	
   $('#id').val(id);
   $('#modal-delete').modal('show');
}









