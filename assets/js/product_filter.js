/**
 * Created by Vignesh on 10/12/2019.
 */

<!--    Listing Filter Script Starts-->


// $("input[type='checkbox'], input[type='radio']").on( "click", productFilter() );
// $("select").on( "change", productFilter() );


$(".city_check, .sub_cat_check ,.discount_check, .price_check").on( "click", productFilter );
$("select").on( "change", productFilter );


function productFilter() {

    $(".all-product-total").css("opacity",0);


    var mainarray = [];

    var size = [];
    $('input[name="scheck"]:checked').each(function(){
        size.push($(this).val());
        $('.spansizecls').css('visibility','visible');
    });
    if(size=='') $('.spansizecls').css('visibility','hidden');
    var size_checklist = "&scheck="+size;

    //To get Category values from All listing page starts

    var cat_check = [];
    $('#cat_check :selected').each(function(){
        // $('input[name="cat_check"]:checked').each(function(){
        cat_check.push($(this).val());
    });

    var cat_checklist = "&cat_check="+cat_check;

    //To get Category values from All listing page ends


    //To get Sub category values from All listing page starts

    var sub_cat_check = [];
    $('input[name="sub_cat_check"]:checked').each(function(){
        sub_cat_check.push($(this).val());

    });

    var sub_cat_checklist = "&sub_cat_check="+sub_cat_check;

    //To get Sub category values from All listing page ends

    //To get Feature values from All listing page starts

    var price_check = [];
    $('input[name="price_check"]:checked').each(function(){
        price_check.push($(this).val());

    });

    var price_checklist = "&price_check="+price_check;

    //To get Feature values from All listing page ends

    var city_check = [];
    $('input[name="city_check"]:checked').each(function(){
        city_check.push($(this).val());

    });
    var city_checklist = "&city_check="+city_check;


    //To get Rating values from All listing page starts

    var discount_check = [];
    $('input[name="discount_check"]:checked').each(function(){
        discount_check.push($(this).val());
    });
    var discount_checklist = "&discount_check="+discount_check;

    //To get Rating values from All listing page ends


    var main_string = size_checklist+cat_checklist+sub_cat_checklist+discount_checklist+city_checklist+price_checklist;
    main_string = main_string.substring(1, main_string.length);


    if(webpage_full_link != null){
        var link = webpage_full_link +'filter_product.html';
    }else{
        var link = 'filter_product.html';
    }

    $.ajax({
        type: "POST",
        url: link,
        data: main_string ,
        cache: false,
        success: function(html){
            //alert(html);
            $(".all-product-total").html(html);
            $(".all-product-total").css("opacity",1);
            // $("#loaderID").css("opacity",0);



        }
    });


}

<!--    Listing Page Filter Script Ends-->


