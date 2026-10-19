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


// A global `function fetch()` stub used to live here — a theme leftover that
// posted to user/user_events_list, called only from the commented-out line
// above. Declared at the top level of a classic script it REPLACED
// window.fetch on every page that loads this file, so every native fetch()
// returned undefined: the /checkout payment button, the review moderation
// switch and the listing page's quote form all threw "cannot read 'then'".

function deleteConfirm(url)
{
    if(confirm('Do you want to Delete this record ?'))
    {
        window.location.href=url;
    }
}