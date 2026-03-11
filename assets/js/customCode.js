// Datatable codes
$(document).ready(function () {
    // alert(document.location.pathname);
    var url = document.location.pathname;
    var res = url.split('/');
    // alert(res[2]);

    if(document.location.pathname == '/user_events')
    {
        // fetch();
    }
    
    if( res[2] == 'add_user_event')
    {
        CKEDITOR.replace('event_description');
    }
    if( res[2] == 'edit_user_event')
    {
        CKEDITOR.replace('event_description');
    }
    if( res[2] == 'add-listing-step-1')
    {
        CKEDITOR.replace('listing_description');
    }    
    
    // code for user add listing page
    if( res[2] == 'add_new_listing')
    {
        CKEDITOR.replace('listing_description');
    }
    if (res[2] == 'add_product') {
        CKEDITOR.replace('product_description');
    }    
    if( res[2] == 'add_new_listing')
    {
        CKEDITOR.replace('listing_description');
    }
    if( res[2] == 'add-blog')
    {
        CKEDITOR.replace('blog_description');
    } 
    if( res[2] == 'edit-blog')
    {
        CKEDITOR.replace('blog_description');
    } 
    if( res[2] == 'edit-listing-step-1')
    {
        CKEDITOR.replace('listing_description');
    } 
    if( res[2] == 'add-product')
    {
        CKEDITOR.replace('product_description');
    } 
    
    
    
});


function fetch(){
    $.ajax({
       url: "user/user_events_list" ,
       type: "post",
       dataType: "json",
       success: function(data){
           console.log(data);
       }
    });
}

function deleteConfirm(url)
{
    if(confirm('Do you want to Delete this record ?'))
    {
        window.location.href=url;
    }
}