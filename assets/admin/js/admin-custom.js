$(document).ready(function () {

    "use strict";

    $('.ad-menu ul li a.mact').siblings().slideDown();

    $('.ad-menu ul li a').on('click', function () {

		if($(this).hasClass("mact")){

			$(".ad-menu ul li div").slideUp();

            $('.ad-menu ul li a').removeClass('mact');

		}

		else{

			$(".ad-menu ul li div").slideUp();

            $('.ad-menu ul li a').removeClass('mact');

            $(this).addClass('mact');

            $(this).siblings().slideDown();

		}

	});

    $('.mopen').on('click', function () {

        $(this).fadeOut();

        $('.mclose').fadeIn();

        $('.ad-menu-lhs').addClass('mshow');

        $('.ad-dash').addClass('leftpadd');

    });

    $('.mclose').on('click', function () {

        $(this).fadeOut();

        $('.mopen').fadeIn();

        $('.ad-menu-lhs').removeClass('mshow');

        $('.ad-dash').removeClass('leftpadd');

    });

    //CREATE DUPLICATE LISTING

    $('.cre-dup-btn').on('click', function () {

        $('.cre-dup-form').slideDown();

    });

    //SERVICES LIST ADD - APPEND

    $(".lis-ser-add-btn").click(function () {

        $(".add-list-ser ul li:last-child").after('<li><div class="row"> <div class="col-md-6"> <div class="form-group"> <label>Service name 1:</label> <input type="text" name="service_id[]" class="form-control" placeholder="Ex: Plumbile"> </div> </div> <div class="col-md-6"> <div class="form-group"> <label>Choose profile image</label> <input type="file" name="service_image[]" class="form-control"> </div> </div> </div></li>');

    });

    //SERVICES OFFER LIST REMOVE - APPEND

    $(".lis-ser-rem-btn").click(function () {

        $(".add-list-ser ul li:last-child").remove();

    });

    //SPECIAL OFFER LIST ADD - APPEND

    $(".lis-add-off").click(function () {

        $(".add-list-off ul li:last-child").after('<li><div class="row"> <div class="col-md-6"> <div class="form-group"> <input type="text" name="service_1_name[]" class="form-control" placeholder="Offer name *"> </div> </div> <div class="col-md-6"> <div class="form-group"> <input type="text" name="service_1_price[]" class="form-control" onkeypress="return isNumber(event)" placeholder="Price"> </div> </div> </div><div class="row"> <div class="col-md-12"> <div class="form-group"> <textarea class="form-control" name="service_1_detail[]" placeholder="Details about this offer"></textarea> </div> </div> </div><div class="row"> <div class="col-md-12"> <div class="form-group"> <label>Choose offer image</label> <input type="file" name="service_1_image[]" class="form-control"> </div> </div> </div><div class="row"> <div class="col-md-12"> <div class="form-group"> <input type="text" name="service_1_view_more[]" class="form-control"  placeholder="View More Link"></div></div></div></li>');

    });

    //SPECIAL OFFER LIST ADD - APPEND

    // $(".lis-add-off").click(function(){

    //     $(".add-list-off ul li:last-child").after('<li><div class="row"> <div class="col-md-6"> <div class="form-group"> <input type="text" class="form-control" placeholder="Offer name *"> </div> </div> <div class="col-md-6"> <div class="form-group"> <input type="text" class="form-control" placeholder="Price"> </div> </div> </div><div class="row"> <div class="col-md-12"> <div class="form-group"> <textarea class="form-control" placeholder="Details about this offer"></textarea> </div> </div> </div><div class="row"> <div class="col-md-12"> <div class="form-group"> <label>Choose offer image</label> <input type="file" class="form-control"> </div> </div> </div></li>');

    // });

    //SPECIAL OFFER LIST REMOVE - APPEND

    $(".lis-add-rem").click(function () {

        $(".add-list-off ul li:last-child").remove();

    });

    //SPECIAL OFFER LIST ADD - APPEND

    $(".lis-add-oad").click(function () {

        $(".add-lis-oth ul li:last-child").after('<li> <div class="row"> <div class="col-md-5"> <div class="form-group"> <input type="text" name="listing_info_question[]" class="form-control" placeholder="Type your information"> </div> </div><div class="col-md-2"> <div class="form-group"> <i class="material-icons">arrow_forward</i> </div> </div> <div class="col-md-5"> <div class="form-group"> <input type="text" name="listing_info_answer[]" class="form-control" placeholder="yes"> </div> </div> </div> </li>');

    });

    //SPECIAL OFFER LIST REMOVE - APPEND

    $(".lis-add-ore").click(function () {

        $(".add-lis-oth ul li:last-child").remove();

    });

    //LISTING CATEGORY ADD - APPEND
    var list_cate_id=1; 
    $(".cate-add-btn").click(function () {
        list_cate_id++;
        $(".add-ncate ul ").append('<li><input type="hidden" name="category_arr[]" value="cate'+list_cate_id+'"> <div class="row"> <div class="col-md-12"> <div class="form-group"> <input type="text" id="category_name" name="category_name[]" class="form-control" placeholder="Category name *" required> </div> </div><div class="col-md-12"><div class="form-group"><label>Choose category image</label><input type="file" name="category_image[]" id="category_image" class="form-control" required></div></div></div></li>');

    });

    //LISTING CATEGORY REMOVE - APPEND

    $(".cate-rem-btn").click(function () {

        $(".add-ncate ul li:last-child").remove();

    });

    //COUNTRY ADD - APPEND

    $(".count-add-btn").click(function () {

        $(".add-ncate ul li:last-child").after('<li> <div class="row"> <div class="col-md-12"> <div class="form-group"> <input type="text" class="form-control" name="country_name[]" placeholder="Country name *" required> </div> </div> </div> </li>');

    });

    //COUNTRY REMOVE - APPEND

    $(".count-rem-btn").click(function () {

        $(".add-ncate ul li:last-child").remove();

    });

    //CITY ADD - APPEND

    $(".city-add-btn").click(function () {

        $(".add-ncate ul li:last-child").after('<li> <div class="row"> <div class="col-md-12"> <div class="form-group"> <input type="text" class="form-control" name="city_name[]" placeholder="City name *" required> </div> </div> </div> </li>');

    });

    //CITY REMOVE - APPEND

    $(".city-rem-btn").click(function () {

        $(".add-ncate ul li:last-child").remove();

    });

    //SUB CATEGORY ADD - APPEND

    $(".scat-add-btn").click(function () {

        $(".add-ncate ul li:last-child").after('<li> <div class="row"> <div class="col-md-12"> <div class="form-group"> <input type="text" class="form-control" placeholder="Sub category name *" name="sub_category_name[]" required> </div> </div> </div> </li>');

    });

    //SUB CATEGORY REMOVE - APPEND

    $(".scat-rem-btn").click(function () {

        $(".add-ncate ul li:last-child").remove();

    });



    //VIDEO LIST ADD - APPEND

    $(".lis-add-oadvideo").on('click', function() {

        $(".add-list-map ul li:last-child").after('<li> <div class="row"> <div class="col-md-12"> <div class="form-group"> <textarea id="listing_video" name="listing_video[]" class="form-control" placeholder="Paste Your Youtube Url here"></textarea> </div> </div> </div> </li>');

    });

    //VIDEO LIST REMOVE - APPEND

    $(".lis-add-orevideo").on('click', function() {

        $(".add-list-map ul li:last-child").remove();

    });



    //BOOTSTRAP TOOL TIP

    $('[data-toggle="tooltip"]').tooltip();



    //PRODUCT SPECIFICATION LIST ADD - APPEND

    $(".prod-add-oad").on('click', function() {

        $(".add-prod-oth ul li:last-child").after('<li> <div class="row"> <div class="col-md-5"> <div class="form-group"> <input type="text" name="product_info_question[]" class="form-control" placeholder="Type your information"> </div> </div><div class="col-md-2"> <div class="form-group"> <i class="material-icons">arrow_forward</i> </div> </div> <div class="col-md-5"> <div class="form-group"> <input type="text" name="product_info_answer[]" class="form-control" placeholder="yes"> </div> </div> </div> </li>');

    });

    //PRODUCT SPECIFICATION LIST REMOVE - APPEND

    $(".prod-add-ore").on('click', function() {

        $(".add-prod-oth ul li:last-child").remove();

    });



    //PRODUCT HIGHLIGHTS LIST ADD - APPEND

    $(".prod-add-high-oad").on('click', function() {

        $(".add-prod-high-oth ul li:last-child").after('<li> <div class="row"> <div class="col-md-12"> <div class="form-group"> <input type="text" name="product_highlights[]" class="form-control" placeholder="Type your highlights"> </div> </div> </div> </li>');

    });

    //PRODUCT HIGHLIGHTS LIST REMOVE - APPEND

    $(".prod-add-high-ore").on('click', function() {

        $(".add-prod-high-oth ul li:last-child").remove();

    });

    

    $('.btn-disa').prop('disabled', true);

    $(".btn-disa").on('click', function() {

        alert("This button was disable for the security reasons.")

        $('.btn-disa').prop('disabled', true);

        return false;

    });



    //ENQUIRY AND REVIEW LIKE

    $(".enq-sav i").click(function () {

        $(this).toggleClass('sav-act');

    });



    //INTERNAL PAGE SEARCH

    $("#pg-sear").on("keyup", function () {

        var value = $(this).val().toLowerCase();

        $("#pg-resu tr").filter(function () {

            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)

        });

    });

    //ADS TOTAL DAYS CALCULATION

    $("#stdate, #endate, #adposi").change(function () {

        var firstDate = $("#stdate").val();

        var secondDate = $("#endate").val();

        var millisecondsPerDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds

        var startDay = new Date(firstDate);

        var endDay = new Date(secondDate);

        var diffDays = Math.abs((startDay.getTime() - endDay.getTime()) / (millisecondsPerDay));

        $(".ad-tdays").text(diffDays);

        $("#ad_total_days").val(diffDays);

        var adpocost = $('#adposi').find('option:selected', this).attr('mytag');

        $(".ad-pocost").text(adpocost);

        $("#ad_cost_per_day").val(adpocost);

        var totcost = diffDays * adpocost;

        $(".ad-tcost").text(totcost);

        $("#ad_total_cost").val(totcost);

    });
    
    //ADS WIDTH & HEIGHT
    $("#adposi").change(function () {
        var width = $('#adposi').find('option:selected', this).attr('adWidth');
        var height= $('#adposi').find('option:selected', this).attr('adHeight');

        $("#ad_width").val(width);
        $("#ad_height").val(height);
    });    

});



//GET URL SOURCE

function urlParam(name) {

    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);

    if (results == null) {

        return null;

    } else {

        return results[1] || 0;

    }

}

//URL PAREM VALUE

//$("#source").val(urlParam("source"));

if (urlParam("login") == "register") {

    $('.log-1, .log-3').slideUp();

    $('.log-2').slideDown();

}



$(function () {

    var availlistings = [

        "Rn53 themes",

        "Real estates",

        "Room rent",

        "Directory and listing",

        "Health care in new york",

        "Child specality",

        "Plumping services",

        "Care and bike services",

        "Care showrooms",

        "Bike showroom",

        "Best t-shirts for men",

        "Tution center",

        "IT training institute",

        "Ui developer training center",

        "Job openings",

        "Lifestyle shops",

        "Medicale and health care",

        "Child care hospital",

        "Univercity and colleges",

        "Trust and help center",

        "Drinking water service",

        "Travel and transport",

        "Events"

    ];

    $("#search").autocomplete({

        source: availlistings

    });

});



$(function () {

    var availlcity = [

        "Alexandria",

        "Aurora",

        "Austin",

        "Chennai",

        "Delhi",

        "Mumbai",

        "Bangalure",

        "Chandler",

        "Dallas",

        "Dayton",

        "Elizabeth",

        "Eugene",

        "Gilbert",

        "Houston",

        "Jackson",

        "Lincoln",

        "Madison",

        "Memphis",

        "Orlando",

        "Phoenix",

        "Savannah",

        "Warren"

    ];

    $("#city").autocomplete({

        source: availlcity

    });

});



$(function () {

    $("#event_start_date").datepicker({

        changeMonth: true,

        changeYear: true,

        minDate: 0

    });

    $("#stdate").datepicker({

        changeMonth: true,

        changeYear: true,

        minDate: 0

    });

    $("#endate").datepicker({

        changeMonth: true,

        changeYear: true,

        minDate: 0

    });

});



//DOWNLOAD INVOICE

$('#downloadPDF').click(function () {

    domtoimage.toPng(document.getElementById('content2'))

        .then(function (blob) {

            var pdf = new jsPDF('l', 'pt', [$('#content2').width(), $('#content2').height()]);



            pdf.addImage(blob, 'PNG', 0, 0, $('#content2').width(), $('#content2').height());

            pdf.save("invoice.pdf");



            that.options.api.optionsChanged();

        });

});



//Number Only Input box



function isNumber(evt) {

    evt = (evt) ? evt : window.event;

    var charCode = (evt.which) ? evt.which : evt.keyCode;

    if (charCode > 31 && (charCode < 48 || charCode > 57)) {

        return false;

    }

    return true;

}





//Auto complete For City List Starts



jQuery(document).ready(function ($) {



    $(function () {

        $.ajax({

            url: '../city_search.html',

            success: function (response) {

                var cityArray = JSON.parse(response);



                // var dataCountry = {};

                // for (var i = 0; i < cityArray.length; i++) {

                //     dataCountry[cityArray[i]] = undefined; //cityArray[i].flag or null

                // }



                $('#select-city.autocomplete').autocomplete({  //Home Page City Search Box

                    source: cityArray,

                    minLength: 3, // The minimum number of characters to be entered

                    limit: 5 // The max amount of results that can be shown at once. Default: Infinity.

                });

            }

        });

    });

});



$(function() {

    $('.chosen-select').chosen();

});



var url = window.location.pathname;

var nav_nave = url.substring(url.lastIndexOf('/')+1);

$(".ad-menu ul li a").each(function(){

    if($(this).attr("href") == nav_nave){

        $(this).addClass('s-act');

    }

})



//SEARCH TERMS



	var htmlElement = document.getElementById("res");

	// $('.lhs ul li a').each(function(){

	// 	if($(this).attr('href') != ""){

	// 		var listElement = document.createElement("li");

	// 		var aElement = document.createElement("a");

	// 		aElement.innerHTML = $(this).text();

	// 		aElement.setAttribute("href",$(this).attr('href'));

	// 		listElement.appendChild(aElement);	

	// 		htmlElement.appendChild(listElement);		

	// 	}

	// });





	



	//SEARCH EVENTS

	$(".search-field").focus(function(){

		$(".tser-res").addClass("act");

	});

$(".search-field").click(function(){

		$(".tser-res").addClass("act");

	});

$(".ad-dash").click(function(){

		$(".tser-res").removeClass("act");

	});

$(".head-s2").mouseleave(function(){

		$(".tser-res").removeClass("act");

	});

 

    /* highlight matches text */

    var highlight = function (string) {

        $("#tser-res li a.match").each(function () {

            var matchStart = $(this).text().toLowerCase().indexOf("" + string.toLowerCase() + "");

            var matchEnd = matchStart + string.length - 1;

            var beforeMatch = $(this).text().slice(0, matchStart);

            var matchText = $(this).text().slice(matchStart, matchEnd + 1);

            var afterMatch = $(this).text().slice(matchEnd + 1);

            $(this).html(beforeMatch + "<em>" + matchText + "</em>" + afterMatch);

        });

    };

 

 

    /* filter products */

    $("#top_search").on("keyup click input", function () {

        if (this.value.length > 0) {

            $("#tser-res li a").removeClass("match").hide().filter(function () {

                return $(this).text().toLowerCase().indexOf($("#top_search").val().toLowerCase()) != -1;

            }).addClass("match").show();

            highlight(this.value);

            $("#tser-res").addClass("act");

        }

        else {

            //$("#tser-res, #tser-res li a").removeClass("match").hide();

        }

    });

    

setTimeout(function () {

     $('.log-suc').fadeOut();

 }, 4000);    


function deleteConfirm(url)
{
    if(confirm('Do you want to Delete this record ?'))
    {
        window.location.href=url;
    }
}

/////////////////////
// Image's Function//
/////////////////////

function restoreConfirm(url)
{
    if(confirm('Do you want to restore this record ?'))
    {
        window.location.href=url;
    }
}


function updateStatus(selected_value,id)
{
    var id              = id;            // EX: listing_id value
    var tableIdName     = $('#tableIdName').val();  // EX: listing_id
    var selected_value  = selected_value;           // EX: listing_status Value either 0 or 1.  
    var updateFieldName = $('#updateField').val();  // EX: Listing_status
    var tableName       = $('#tableName').val();    // EX: Listings


    $.ajax({
        url: "update_status",
        method:"POST",
        data:{id:id, fieldName: updateFieldName, 
                value: selected_value, tableIdName: tableIdName,
                tableName:tableName},
        dataType:"json",
        success:function(response)
        {
            if(response.message == 'success'){
                location.reload();
            }
        }
    });
}

function isSaved(selected_value,id)
{
    var id              = id;            // EX: listing_id value
    var tableIdName     = $('#tableIdName').val();  // EX: listing_id
    var selected_value  = selected_value;           // EX: listing_status Value either 0 or 1.  
    var updateFieldName = $('#updateField').val();  // EX: Listing_status
    var tableName       = $('#tableName').val();    // EX: Listings


    $.ajax({
        url: "update_status",
        method:"POST",
        data:{id:id, fieldName: updateFieldName, 
                value: selected_value, tableIdName: tableIdName,
                tableName:tableName},
        dataType:"json",
        success:function(response)
        {
            if(response.message == 'success'){
                location.reload();
            }
        }
    });
}


function updateAdStatus(value,id)
{
    var id        = id;            
    var adStatus  = value; 


    $.ajax({
        url: "approveAdStatus",
        method:"POST",
        data:{id:id, status: adStatus},
        dataType:"json",
        success:function(response)
        {
            if(response.message == 'success'){
                location.reload();
            }
        }
    });
}