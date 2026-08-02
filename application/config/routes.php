<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'User';
$route['admin'] = 'Admin/index';
$route['sitemap\.xml'] = 'Sitemap/index';
$route['weather'] = 'Weather/proxy';
$route['contact-us'] = 'User/contact_us';
$route['contact-submit'] = 'User/contact_submit';
$route['pricing-plan'] = 'User/pricing_plan';
$route['pricing-details'] = 'User/pricing_plan';
$route['all-news'] = 'User/news';
$route['post-your-ads'] = 'User/post_your_ads';

// Payments (Helcim). Explicit so the method name is unambiguous — the
// webhook is session-less and must match the __construct public whitelist,
// and the AJAX endpoints resolve consistently regardless of _remap.
$route['helcim-webhook']      = 'User/helcim_webhook';
$route['helcim-init']         = 'User/helcim_init';
$route['process-checkout']    = 'User/process_checkout';
$route['plan-change-init']    = 'User/plan_change_init';
$route['cancel-subscription'] = 'User/cancel_subscription';

// Advertising cart (session-backed; guests allowed)
$route['cart-add']    = 'User/cart_add';
$route['cart-remove'] = 'User/cart_remove';
$route['cart-get']    = 'User/cart_get';
$route['cart-clear']  = 'User/cart_clear';
$route['cart-pay-init'] = 'User/cart_pay_init';
$route['buy-now']       = 'User/buy_now';
$route['cart-set-term'] = 'User/cart_set_term';

$route['(:any)'] = 'User/$1';
$route['404_override'] = 'errors/page_missing';
$route['translate_uri_dashes'] = TRUE;

$route['listing-details/(:any)'] = 'user/listing_details/$1';
// CI3's (:any) is [^/]+, so the paginated form needs its own rule and it must
// come first — without it /all_listing/<category>/<offset> matches nothing and
// falls through to a 404, which is why pagination links used to be written as
// the long /user/all_listing/... form.
$route['all_listing/(:any)/(:num)'] = 'user/all_listing/$1/$2';
$route['all_listing/(:any)'] = 'user/all_listing/$1';
$route['all-listing/(:any)/(:num)'] = 'user/all_listing/$1/$2';
$route['all-listing/(:any)'] = 'user/all_listing/$1';

// Auth flows
$route['forgot-password']           = 'user/forgot_password';
$route['reset-password/(:any)']     = 'user/reset_password/$1';
$route['verify-email/(:any)']       = 'user/verify_email/$1';

// IT / web services catalog (public side)
$route['services']                    = 'user/services';
$route['services/(:any)']             = 'user/service_details/$1';
$route['request-service']             = 'user/request_service';
$route['request-service/(:any)']      = 'user/request_service/$1';

// Listing claim
$route['claim-listing/(:any)']        = 'user/claim_listing/$1';

// Public coupons / deals
$route['coupons']                     = 'user/coupons';
$route['coupons/(:num)']              = 'user/coupon_details/$1';

// IT / web services catalog (admin side)
$route['admin/services']                       = 'admin/services_list';
$route['admin/services/add']                   = 'admin/add_service';
$route['admin/services/edit/(:num)']           = 'admin/edit_service/$1';
$route['admin/services/delete/(:num)']         = 'admin/delete_service/$1';
$route['admin/service-requests']               = 'admin/service_requests_list';
$route['admin/service-requests/(:num)']        = 'admin/service_request_view/$1';
$route['admin/service-requests/delete/(:num)'] = 'admin/service_request_delete/$1';

// Admin self-service
$route['admin/change-password']             = 'admin/change_password';
$route['admin/setting']                     = 'admin/admin_setting';
$route['admin-setting']                     = 'admin/admin_setting';

// Listing claims
$route['admin/claim-approve/(:num)']        = 'admin/claim_approve/$1';
$route['admin/claim-reject/(:num)']         = 'admin/claim_reject/$1';
$route['admin/claimed-listings']            = 'admin/claimed_listings';

// Sub-admin / role management (super_admin only)
$route['admin/sub-admins']                  = 'admin/sub_admin_all';
$route['admin/sub-admins/new']              = 'admin/sub_admin_create';
$route['admin/sub-admins/edit/(:num)']      = 'admin/sub_admin_edit/$1';
$route['admin/sub-admins/delete/(:num)']    = 'admin/sub_admin_delete/$1';

// Newsletter campaigns (admin side)
$route['admin/campaigns']                       = 'admin/campaigns_list';
$route['admin/campaigns/new']                   = 'admin/campaign_compose';
$route['admin/campaigns/edit/(:num)']           = 'admin/campaign_compose/$1';
$route['admin/campaigns/view/(:num)']           = 'admin/campaign_view/$1';
$route['admin/campaigns/queue/(:num)']          = 'admin/campaign_queue/$1';
$route['admin/campaigns/send-batch/(:num)']     = 'admin/campaign_send_batch/$1';
$route['admin/campaigns/delete/(:num)']         = 'admin/campaign_delete/$1';

// News. The hyphenated form is the canonical one — it is what the share
// buttons, the <link rel=canonical> and the JSON-LD carry — but it had no
// route, so every "Related posts" link on a story 404'd: CI3's catch-all
// `(:any)` is [^/]+ and matches one segment, and `news-details` is not a
// controller. The underscore form stays for links already in the wild.
$route['news-details/(:any)'] = 'user/news_details/$1';
$route['news_details/(:any)'] = 'user/news_details/$1';
// Videos — same pair, same reason.
$route['video-details/(:any)'] = 'user/video_details/$1';
$route['video_details/(:any)'] = 'user/video_details/$1';
// Event
$route['event-details/(:any)'] = 'user/event_details/$1';
$route['event_details/(:any)'] = 'user/event_details/$1';
// Classifieds
$route['classifieds-details/(:any)'] = '/user/classifieds-details/$1';
// Products - Not working
$route['products-details/(:any)'] = 'user/product_details/$1';
// blogs
$route['blogs-details/(:any)'] = 'user/blog_details/$1';
// rental 
$route['rental-details/(:any)'] = 'user/rental-details/$1';
// House 
$route['house-details/(:any)'] = 'user/house-details/$1';
