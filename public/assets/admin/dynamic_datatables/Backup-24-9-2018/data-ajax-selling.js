	$(document).ready(function() {
		
		$('#couponshow').hide();

	    //validation
	    $('#form_add_selling').validate({
	        rules: {


	            firstname: {
	                required: true

	            },
	            lastname: {
	                required: true

	            },
	            selling_type: {
	                required: true

	            },

	            email: {
	                required: true,
	                email: true
	            },
	            phone: {
	                required: true


	            },
	            crdcard: {
	                required: true,
	                creditcard: true,
	                minlength: 15,
	                maxlength: 16,

	            },
				 crcard: {
	                required: true,
	                creditcard: true,
	                minlength: 15,
	                maxlength: 16,

	            },
	            cvv: {
	                required: true,
					number:true,
					minlength: 3,
					 maxlength:4,

	            },
				crvv: {
	                required: true,
					minlength: 3,
					 maxlength:4,
					number:true,
	            },

	            nameaccount: {
	                required: true

	            },
	            bankname: {
	                required: true

	            },
	            acoountnumber: {
	                required: true

	            },
	            generalSearch: {
	                required: true

	            },
	            eventtime: {
	                required: true

	            },
				cmonth: {
	                required: true

	            },
				cyear: {
	                required: true

	            },
				crmonth: {
	                required: true

	            },
				cryear: {
	                required: true

	            },
				
				event_group: {
	                required: true

	            },
				event_id: {
	                required: true

	            }
	        },
	        messages: {
	            firstname: {
	                required: F_NAME_TXT,
	            },
	            lastname: {
	                required: L_NAME_TXT,
	            },
	            selling_type: {
	                required: TYPE_TXT,
	            },

	            email: {
	                required: EMAIL_TXT,
	                email: VALID_EMAIL_TXT
	            },
	            phone: {
	                required: ENTER_PHONE_NUMBER_TXT,
	            },
	            crdcard: {
	                required: CCNUMBER_REQUIRED_TXT,
	                creditcard: INVALID_CARD_TXT,
	                minlength: INVALID_CARD_TXT,
	                maxlength: INVALID_CARD_TXT,
	            },
	            cvv: {
	                required: CVV_REQUIRED_TXT,
					minlength:CVV_INVALID_TXT,
					maxlength:CVV_INVALID_TXT,
					number: NUMBER_TXT,
	            },
				 crcard: {
	                required: CCNUMBER_REQUIRED_TXT,
	                creditcard: INVALID_CARD_TXT,
	                minlength: INVALID_CARD_TXT,
	                maxlength: INVALID_CARD_TXT,
	            },
	            crvv: {
	                required: CVV_REQUIRED_TXT,
					minlength: CVV_INVALID_TXT,
					maxlength: CVV_INVALID_TXT,
					number:NUMBER_TXT,
	            },
	            nameaccount: {
	                required: AC_NAME_REQ_TXT,
	            },
	            bankname: {
	                required: BANK_NAME_REQ_TXT,

	            },
	            acoountnumber: {
	                required: ACC_NUM_REQ_TXT,

	            },
	            generalSearch: {
	                required: EVENT_MSG_TXT,

	            },
	            eventtime: {
	                required: VENT_TIME_TXT,

	            },
				 cmonth: {
	                required: MONTH_MSG_TXT,

	            },
				cyear: {
	                required: YEAR_MSG_TXT,

	            },
				crmonth: {
	                required: MONTH_MSG_TXT,

	            },
				cryear: {
	                required: YEAR_MSG_TXT,

	            },
				event_group: {
	                required: EVENT_GROUP_MSG_TXT,

	            },
				event_id: {
	                required: EVENT_MSG_TXT,

	            },
	        }
	    });




	    //end validaion

	    $("#customerdetails").hide();
	    $("#cheque").hide();
	    $("#crdtcard").hide();
	    $('#eventtimeshow').hide();
	    $('#eventdetails').hide();



	    $("input[name$='payment']").click(function() {
	        /* alert('checkbox clicked');
	        if (this.value == 'cheque') {
	            $("#cheque").show();
	            $("#crdtcard").hide();
	        }
			*/
			if (this.value == 'ccard') {
	            $("#crdtcard").show();
	            //$("#cheque").hide();
	        }
 		else {
	            //$("#cheque").hide();
	            $("#crdtcard").hide();
	        }
	    });



	    if ($('#selling_type').val() === "") {
	        $("#byphone").hide();
	        $("#byphysical").hide();
	    }

	    $('#selling_type').on('change', function() {
	        if (this.value == 0)
	        //.....................^.......
	        {
	            $("#crdtcard").hide();
	            $("#byphysical").show();
	        }
	        if (this.value == 1) {
	            $("#crdtcard").show();
	            $("#byphysical").hide();
				 //$("#crdtcard").hide();
	        }
	        if (this.value == '') {
	            $("#crdtcard").hide();
	            $("#byphysical").hide();
	        }
	    });



	    //search users
	    $('#generalSearch').blur(function() {

	        if ($('#generalSearch').val().length > 0) {
	            $('.m_datatable_mem').show();
	            $("#addcustm").hide();
	            t = $(".m_datatable_mem").mDatatable({
	                    data: {
	                        type: "remote",
	                        source: {
	                            read: {
	                                url: "getAjaxMembersList",
	                                map: function(t) {
	                                    var e = t;
	                                    return void 0 !== t.data && (e = t.data), e
	                                }
	                            }
	                        },
	                        saveState: {
	                            cookie: false,
	                            webstorage: false
	                        },
	                        pageSize: 4,
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
	                                pageSizeSelect: [4, 20, 30, 50, 100]
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
	                            width: 20,
	                            selector: !1,
	                            textAlign: "center",
	                            template: function(t, e, a) {



	                                return '\t\t\t\t\t\t<input type="radio" name="memberid" value="[t.member_id]" onclick="memberfunction(' + [t.member_id] + ')" />\t\t\t\t\t\t\t</i>\t\t\t\t\t\t</a>\t\t\t\t\t'

	                            }


	                        }, {
	                            field: "member_id",
	                            title: 'ID',
	                            filterable: !1,
	                            width: 50,
	                            template: "{{member_id}}"
	                        }, {
	                            field: "name",
	                            title: Member_Name,
	                            filterable: !1,
	                            width: 150,
	                            template: "{{name}}"
	                        },
	                        {
	                            field: "first_name",
	                            title: member_first_name_txt,
	                            filterable: !1,
	                            width: 100,
	                            template: "{{first_name}}"
	                        },
	                        {
	                            field: "email",
	                            title: Member_Email,
	                            filterable: !1,
	                            width: 200,
	                            template: "{{email}}"
	                        },
	                        {
	                            field: "city",
	                            title: member_ville_txt,
	                            filterable: !1,
	                            width: 50,
	                            template: "{{city}}"
	                        },

	                        {
	                            field: "country",
	                            title: member_country_txt,
	                            filterable: !1,
	                            width: 100,
	                            template: "{{country}}"
	                        },

	                    ]
	                }), $("#refresh_table").on('click', function() {
	                    t.reload(); /*DatatableRemoteAjaxDemoCat.init();*/
	                }),
	                $("#m_form_status").on("change", function() {
	                    t.search($(this).val(), "Status")
	                }),
	                /*$("#m_form_type").on("change", function() {
	                           t.search($(this).val(), "Type")
	                       }),*/
	                $("#m_form_status, #m_form_type").selectpicker()
	        } else {
	            // $(".m_datatable_mem").val()="";
	            //alert("want remove");
	            $('.m_datatable_mem').hide();

	            $("#addcustm").show();
	        }


	    });



	   




	});

	function AddSellingTicket() {
	    if (!$("#form_add_selling").validate().form()) {

	    } else {
			 $('#finalsubmit').prop('disabled', true);
			 $("#selling_result").html('<img src="../assets/assets/img/ajax-loader.gif"  />');
			
	        //alert('Welcome to full registration');
	        var mydata = $('#form_add_selling').serialize();
	        // alert(mydata);
	        $.ajax({
	            type: "get",
	            url: 'sellingticketdata',
	            data: mydata,
	            success: function(data) {
	                //var obj = jQuery.parseJSON( data);
	                var obj = data;
	                console.log(obj.status);
	                console.log(obj.msg);
					$("#selling_result").html('');
	                if (obj.status === '1') {
	                    $("#selling_result").html('<div class="alert alert-info" role="alert"> ' + obj.msg + '.</div>');

						setTimeout(function(){
                       window.location.reload(1);
                        }, 5000);
	                } else {
	                    $("#selling_result").html('<div class="alert alert-danger" role="alert"> ' + obj.msg + '.</div>');
	                }
					$('#finalsubmit').prop('disabled', false);

	                return false;
	            }
	        });
	        return false;
	    }

	}
	$(document).on('change', ".ctqtyto", function() {
		var rowqtx=0;
		//var v = $(this).attr("value");
		//alert(v);
		var customseatid = this.id;
		//alert(customseatid);
		var res = customseatid.replace("custom_seat_qty_","");
		$('#seat_qty_'+res).val(rowqtx);
		var seat_from_hdn = "#seat_from_hdn_" + res;
	    var seat_to_hdn = "#seat_to_hdn_" + res;
	    var seat_sequence_hdn = "#seat_sequence_hdn_" + res;
		var ticket_price_hdn = "#ticket_price_hdn_"+res;
		var pticket_price_hdn = "#pticket_price_hdn_"+res;
	   // var customfieldid = res;
		//alert(customfieldid);
		
		 var first_value = $('option', this).filter(':selected:first').val();
		 var firstindexvalue=first_value.split(",");
		 var seat_from=firstindexvalue[0];
		 //alert('First index value : '+firstindexvalue[0]);
		 
		 
		var latest_value = $('option', this).filter(':selected:last').val();
		var lastindexvalue=latest_value.split(",");
		var seat_to=lastindexvalue[0];
		var count = $(".ctqtyto option:selected").length;
		var quentity=count;
		//alert(quentity);
		$('#seat_qty_'+res).val(quentity);
		//alert('quentity is : '+count);
        //alert ('last index value : '+lastindexvalue[0]);
		
		//$('#seat_qty_'+res).val(quentity);
		var myseatsq='';
		var mysqprice='';
		var quenty=0;
		//quenty = $("#custom_seat_qty_"+res+" option:selected").length;
		var i=0;
		  $("#custom_seat_qty_"+res+" option:selected ").each(function(){
			  
			  
			 var v = $(this).attr("value");
			// alert(v);
			if(v != 0)
			{
				i=i+1;
			 var arr=v.split(",");
			 var seatsquence=arr[0];
			 
			myseatsq+=seatsquence+',';
			
			 var  price=arr[1];
			 
			mysqprice+=price+',';
			}
		  });
		  quentity=i;
	      mysqprice=mysqprice.slice(0,-1); 
		  myseatsq=myseatsq.slice(0,-1); 
		  
         //alert('seat squence is '+myseatsq);
		 //alert('price squence is '+mysqprice);
		 
		 $(seat_from_hdn).val(seat_from);
	     $(seat_to_hdn).val(seat_to);
	     $(seat_sequence_hdn).val(myseatsq);
		 var customvalue=$('#seatprice_'+res).val()
		 if(customvalue=='')
		 {
		 $(ticket_price_hdn).val(mysqprice);
		 }
		 else
		 {
			 var customvalues='';
			 for(var i=0; i<quenty;i++)
			 {
				customvalues+=customvalue+','; 
			 }
			 
			$(ticket_price_hdn).val(customvalues);
		 }
		 $(pticket_price_hdn).val(mysqprice);
		// alert(quentity);
		 $("#total_row_qtx").val(quentity);
		$("#ticket_quentity_hdn_"+res).val(quentity);
		
		
		 GetValue();
		 $("#couponshow").show();
		
		  
        
    });

	function ShowForm() {

	    var newcustomer = $("#isnewcustomer").val();
	    if (newcustomer == '0') {
	        $("#customerdetails").show();
	        $("#isnewcustomer").val('1');
	    } else {
	        $("#customerdetails").hide();
	        $("#isnewcustomer").val('0');
	    }



	    //alert('Hello');
	}


	function memberfunction(mid) {
	    var userid = mid;

	    $("#addcustm").hide();
	    $("#usermid").val(userid);
	    //alert('Hello radio button clicked here'+mid);
	}

	function eventfunction(eid) {
	    var eventid = eid;

	    $("#eventeid").val(eventid);
	    $.ajax({
	        type: "get",
	        url: 'getEventTime',
	        data: {
	            eventid
	        }
	    }).done(function(msg) {
	        // $( "#divCommunity" ).hide();
	        $("#eventtimeshow").show();


	        $("#timeshow").html(msg);
	        $('#eventdetails').hide();
	    });
	    // $('#eventtimeshow').show();

	    //alert('Hello radio button clicked here'+eid);
	}

	$(document.body).on('change', '#eventtime', function() {
	    if (this.value == '') {
	        $('#eventdetails').hide();
	        $('#booking_fee').hide();
	        $('#total_items').hide();
	    }
	    if (this.value != '') {

	        var time = $("#eventtime").val();
	        //Eventid access

	        var eventid = time.split(',')[0];

	        var testtime = time.split(",");
	        //event time access
	        var eventtime = testtime[testtime.length - 1];
	        // alert('event id is '+eventid);
	        // alert('Time is '+eventtime);
	        var evnt_id = eventid;
	        var dataTime = eventtime;

	        $("#evnttime").val(dataTime);
	        $.ajax({
	            type: "get",
	            url: 'ajaxcallEventOrder',
	            data: {
	                evnt_id,
	                dataTime
	            }
	        }).done(function(msg) {
	            // $( "#divCommunity" ).hide();
	            $("#eventdetails").show();
	            console.log(msg);
	            $('html,body').animate({
	                    scrollTop: $("#eventdetails").offset().top - 20
	                },
	                'slow');
	            $("#myevent").html(msg.bodyText);
			    $("#booking_fees").val(msg.booking_fees);
	        });

	    }
	});
	
	
	

	//On change the Ticket Categories//
	$(document).on('change', ".tickrow", function() {

	    var eventRowId = this.id; //seat_qty_1
	    var res = eventRowId.replace("row_", "");
	    var evnt_id = res;
	    var ddlrowValue = this.value;
	    var arr = ddlrowValue.split('-');
	    var row_id = arr[0];
	    var seat_number = arr[1];
	    var choose_seat = "#seat_qty_" + evnt_id;
		var custom_choose_seat = "#custom_seat_qty_" + evnt_id;
	    //var toal_seat_available = "#totalavailabletkt";
	    var toal_seat_available_hdn = "#totalavailabletkthdn_" + evnt_id;
	    var seat_number_hdn = "#seat_number_hdn_" + evnt_id;
		 var seat_sequence_hdn = "#seat_sequence_hdn_" + evnt_id;
		 var ticket_quentity_hdn='#ticket_quentity_hdn_'+evnt_id;
		  var ticket_price_hdn='#ticket_price_hdn_'+evnt_id;
		 $(seat_sequence_hdn).val('');
	    if (row_id != '') {

	        //var seatIndexFrom = "#seat_qty_from_"+res;
	        //var seatIndexTo = "#seat_qty_to_"+res;
				$('#rownumber').val(row_id);

	        $.ajax({
	            type: "get",
	            url: 'ajaxcallRawSeat/' + row_id,
	            data: {}
	        }).done(function(data) {
	            //var obj = jQuery.parseJSON( data);
	            var obj = data;
	            console.log(obj.available_ticket_quantity);
	            console.log(obj.choose_ticket_quantity);
	            console.log(choose_seat);
				
	            $(choose_seat).html('');
	            $(choose_seat).html(obj.choose_ticket_quantity);
				$(custom_choose_seat).html('');
	            $(custom_choose_seat).html(obj.multiple_select_seat);

	            //$(toal_seat_available).html('');
	            //$(toal_seat_available).html(obj.available_ticket_quantity);
	            $(toal_seat_available_hdn).val('0');
	            $(toal_seat_available_hdn).val(obj.available_ticket_quantity);
	            console.log(seat_number_hdn);
	            console.log(seat_number);
	            $(seat_number_hdn).val(seat_number);
                 GetValue();

	        });
	    } else {
	        $(choose_seat).html('');
			$(custom_choose_seat).html('');
	        //$(toal_seat_available).html('');
	        $(toal_seat_available_hdn).val('0');
			$(ticket_quentity_hdn).val('');
			
			$(ticket_price_hdn).val('');
			GetValue();
	    }
	});
  
function GetValue(){
	 var sum=0;
	var quentities=0; 

	//var bookingfee=0;
	$('.ctqtyto').each(function() {
		
		//alert( this.id );
		var seatrwId = this.id;
		
		//bookingfee= $("#booking_fees").val();
		
		//alert(bookingfee);
		var res = seatrwId.replace("custom_seat_qty_", "");
		//alert(res);
		var customprice=$('#seatprice_'+res).val();
		//alert(customprice);
		//var qtxArr = this.value.split(",");
		var quantity = '';
		if(quantity=='')
		{
			quantity=$('#ticket_quentity_hdn_'+res).val();
		}
		//alert(qtxArr);
		if( quantity >0)
		{
		quentities+=parseInt(quantity);
		}
		
		var pricesquent=$('#ticket_price_hdn_'+res).val();
		//alert(pricesquent);
		var qtxArrp = pricesquent.split(",");
		//alert(qtxArrp);
		if(customprice > 0 && customprice!='')
		{
			//alert('quentity is : '+quantity);
			//alert(customprice);
			//alert(sum);
			sum+=quantity*customprice;
			
		}
	else{
		//alert('this is value from last case'+qtxArrp);
		if(pricesquent!='')
		{
		
		//alert('price sequence is '+qtxArrp);
		for (var i=0;i<qtxArrp.length;i++){
            sum += parseInt(qtxArrp[i]);

         }
		}
		 
		//alert('price sequence is : '+sum);
		
	  }
		
	});
	var booking_fees = $("#booking_fees").val();
	//alert(booking_fees);
	var totalbookingfees=quentities*booking_fees;
	var overalltotal=sum+totalbookingfees;
	
    //alert('All Sum is  is : '+sum);
		//alert('All quentities is  is : '+quentities);  
					$("#total_items").html('');
					$("#total_items").html('<tr> <th scope="row"></th><td>  </td><td>Total :</td><td>' + sum + '</td></tr>');
					$("#total_items").append('<tr> <th scope="row"></th><td>  </td><td>Total frais de réservation :&nbsp </td><td><span class="qtx2">' + quentities + '</span> x ' + booking_fees + '=' + totalbookingfees + '</td></tr>');
					$("#total_items").append('<tr> <th scope="row"></th><td>  </td><td>Total :</td><td>' + overalltotal + '</td></tr>');		
                   $("#total_row_qtx").val(quentities);
					$("#total_booking_fees").val(totalbookingfees);
					$("#total_reserved_fees").val(overalltotal);
					$("#ptotal_reserved_fees").val(overalltotal);
					
					$("#promocode").val('');
					$("#tpromo").html('');
					//$("#tpromo").hide();
					if(sum==0)
					{
						$("#total_items").html('');
						$("#tpromo").html('');
					}
					
}
	$(document).on('change', ".qtyto", function() {
	    $('#booking_fee').show();
	    $('#total_items').show();
		//alert('hello'+this.id);
	    var seatrowId = this.id; //seat_qty_1
		//alert();

	    var res = seatrowId.replace("seat_qty_", "");

	    var seat_from_hdn = "#seat_from_hdn_" + res;
	    var seat_to_hdn = "#seat_to_hdn_" + res;
	    var seat_sequence_hdn = "#seat_sequence_hdn_" + res;
		var ticket_price_hdn = "#ticket_price_hdn_"+res; 
		var pticket_price_hdn = "#pticket_price_hdn_"+res;
		
		var rowseatmap_hdn = "#rowSeatMap_hdn_"+res;
		//alert(rowseatmap_hdn);
	    //ticket_price

	    var row_id = '#row_' + res;
	    var ddlrowValue = $(row_id).val();
	    var arr = ddlrowValue.split('-');
	    var row_id = arr[0];
	    var seat_number = arr[1];
	  
		var qtxArr = this.value.split(",");
		var quantity = qtxArr[0]; //seat_qty_1
		var price = qtxArr[1];
		var total = 0;
		
		//alert(quantity);//alert(price);

	    var item = 0;
	    //var price = 0;
	    var total_price = 0;
		if(quantity > 0)
		{
			var total_itm = quantity;
		}
		else{
			quantity=0;
			var total_itm = quantity;
		}
	    
	    var countlist = 0;
	     


	    if (row_id != '') {

	        //var seatIndexFrom = "#seat_qty_from_"+res;
	        //var seatIndexTo = "#seat_qty_to_"+res;

	        var totalfee = 0;
	        $.ajax({
	            type: "get",
	            url: 'row-seat-sequence/' + row_id + '/' + quantity + '/' + seat_number,
	            data: {}
	        }).done(function(data) {


	            //var obj = jQuery.parseJSON( data);
	            var obj = data;
	            console.log(obj.seat_from);
	            console.log(obj.seat_to);
	            console.log(obj.seat_sequence);
	            console.log(obj.seat_sequence);
	            //console.log('total seat booked  :'+obj.total_quentity);
	            console.log(seat_from_hdn);
	            console.log(seat_to_hdn);
	            //console.log(total_quentity);

	            $(seat_from_hdn).val(obj.seat_from);
	            $(seat_to_hdn).val(obj.seat_to);
	            $(seat_sequence_hdn).val(obj.seat_sequence);
				 
				//$(ticket_price_hdn).val(obj.seat_price_sequence);
	            // $(total_quentity).val(obj.total_quentity);
				$(rowseatmap_hdn).html(obj.price_html);
				$(ticket_price_hdn).val(obj.seat_price_sequence);
				$(pticket_price_hdn).val(obj.seat_price_sequence);
				
				
				
				
				/*Add New code Value here */ 
	            console.log('price of ticket is' + obj.seat_quentity * obj.ticket_price);
	            totalfee = obj.total_amt;
               //alert('total fees'+totalfee);
	            
				
			 
				
					// console.log('ticket price is '+total);
					//console.log('total quentity is : '+total_itm);
					console.log('select list count is ' + countlist);


					var booking_fees = $("#booking_fees").val();
					var totalbookingfees = total_itm * booking_fees;
					var completeTotal = totalbookingfees + totalfee;
					//$("#total_items").html('');
					//$("#total_items").html('<tr> <th scope="row"></th><td>  </td><td>Total :</td><td>' + totalfee + '</td></tr>');
					//$("#total_items").append('<tr> <th scope="row"></th><td>  </td><td>Total Bookig Fees :</td><td>' + total_itm + '*' + booking_fees + '=' + totalbookingfees + '</td></tr>');
					//$("#total_items").append('<tr> <th scope="row"></th><td>  </td><td>Overall Total :</td><td>' + completeTotal + '</td></tr>');
				   
					$("#total_row_qtx").val(total_itm);
					$("#total_booking_fees").val(totalbookingfees);
					$("#total_reserved_fees").val(completeTotal);
					$("#ptotal_reserved_fees").val(completeTotal);
					$('#couponshow').show(); 
				
				/* End here */
				
				

	            // $( "#booking_fee" ).val(totalfee);

              GetValue();

	        });
	    } else {

	    }

    
	}
	
	);