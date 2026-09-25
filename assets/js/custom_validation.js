// Register Form validation starts
$(document).ready(function () {
    $("#register_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            name: {required: true},
            email_id: {required: true,email: true},
            mobile_number: {required: true},
            password: {required: true, minlength: 6}
        },
        messages: {

            name: {required: "Name is Required"},
            email_id: {required: "Email ID is Required"},
            mobile_number: {required: "Phone Number is Required"},
            password: {required: "Password is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Register Form validation ends

// Login Form validation starts

$(document).ready(function () {
    $("#login_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            email_id: {required: true},
            password: {required: true}
        },
        messages: {
            email_id: {required: "Email ID is Required"},
            password: {required: "Password is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Login Form validation ends

// Forget Password Form validation starts

$(document).ready(function () {
    $("#forget_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            email_id: {required: true}
        },
        messages: {
            email_id: {required: "Email ID is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Forget Password Form validation ends

// Listing Form -1 validation starts
$(document).ready(function () {
    $("#listing_form_1").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            listing_name: {required: true},
            listing_address: {required: true},
            // listing_lat: {required: true},
            // listing_lng: {required: true},
            event_image: {required: true},
            event_address: {required: true},
            event_start_date: {required: true},
            event_time: {required: true},
            event_description: {required: true},
            event_email: {required: true,email: true},
            event_mobile: {required: true}
        },
        messages: {

            listing_name: {required: "Listing Name is Required"},
            event_contact_name: {required: "Contact Person Name is Required"},
            event_image: {required: "Event Image is Required"},
            listing_address: {required: "Listing Address is Required"},
            // listing_lat: {required: "Listing Latitude is Required"},
            // listing_lng: {required: "Listing Longitude is Required"},
            event_start_date: {required: "Event Date is Required"},
            event_time: {required: "Event Time is Required"},
            event_description: {required: "Event Description is Required"},
            event_email: {required: "Event Email ID is Required"},
            event_mobile: {required: "Phone Number is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Listing Form -1 validation ends

// Event Form validation starts
$(document).ready(function () {
    $("#event_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            event_name: {required: true},
            event_contact_name: {required: true},
            event_image: {required: true},
            event_address: {required: true},
            event_start_date: {required: true},
            event_time: {required: true},
            event_description: {required: true},
            event_email: {required: true,email: true},
            event_mobile: {required: true}
        },
        messages: {

            event_name: {required: "Event Name is Required"},
            event_contact_name: {required: "Contact Person Name is Required"},
            event_image: {required: "Event Image is Required"},
            event_address: {required: "Event Address is Required"},
            event_start_date: {required: "Event Date is Required"},
            event_time: {required: "Event Time is Required"},
            event_description: {required: "Event Description is Required"},
            event_email: {required: "Event Email ID is Required"},
            event_mobile: {required: "Phone Number is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Event Form validation ends

// Event Edit Form validation starts
$(document).ready(function () {
    $("#event_edit_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            event_name: {required: true},
            event_contact_name: {required: true},
            event_address: {required: true},
            event_start_date: {required: true},
            event_time: {required: true},
            event_description: {required: true},
            event_email: {required: true,email: true},
            event_mobile: {required: true}
        },
        messages: {

            event_name: {required: "Event Name is Required"},
            event_contact_name: {required: "Contact Person Name is Required"},
            event_address: {required: "Event Address is Required"},
            event_start_date: {required: "Event Date is Required"},
            event_time: {required: "Event Time is Required"},
            event_description: {required: "Event Description is Required"},
            event_email: {required: "Event Email ID is Required"},
            event_mobile: {required: "Phone Number is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Event Edit Form validation ends

// Blog Form validation starts
$(document).ready(function () {
    $("#blog_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            blog_name: {required: true},
            blog_image: {required: true},
            blog_description: {required: true}
        },
        messages: {

            blog_name: {required: "Blog Name is Required"},
            blog_image: {required: "Blog Image is Required"},
            blog_description: {required: "Blog Description is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Blog Form validation ends

// Blog Edit Form validation starts
$(document).ready(function () {
    $("#blog_edit_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            blog_name: {required: true},
            blog_description: {required: true}
        },
        messages: {

            blog_name: {required: "Blog Name is Required"},
            blog_description: {required: "Blog Description is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Blog Edit Form validation ends


/*
 * The eight jQuery-validate blocks that used to live here are gone.
 *
 * Every one of them hijacked a real form's submit button to POST to
 * `enquiry_submit.html`, `review_submit.html` or `claim_submit.html` --
 * flat-file endpoints from the bought theme that have never existed in
 * this CodeIgniter app -- and each built that URL from `webpage_full_link`,
 * a variable no page defines. So the handler threw a ReferenceError before
 * it got as far as the 404.
 *
 * What that broke, on every page that renders one of these forms:
 *
 *   #detail_enquiry_submit  the "Get a quote" form on /listing-details,
 *                           /classifieds-details, /house-details and
 *                           /rental-details
 *   #review_submit          the "Write a review" form on /listing-details
 *   #home_slide_enquiry_submit  the Help & Support slide-out in footer.php,
 *                           which is to say every page on the site
 *
 * All of those post to live CodeIgniter endpoints (`user/add-enquiry/...`,
 * `user/submit_review`, `/contact-submit`) and the endpoints validate their
 * own input. Client-side validation now comes from the markup, via
 * assets/js/form-validate.js -- one implementation that cannot drift from
 * what the server accepts.
 *
 * Someone had already commented out the `#popup_claim_submit` binding for
 * the same reason; the rest were left live.
 */

// User Ad Request Form validation starts
$(document).ready(function () {
    $("#create_ads_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            all_ads_price_id: {required: true},
            ad_start_date: {required: true},
            ad_end_date: {required: true},
            ad_enquiry_photo: {required: true},
            ad_link: {required: true}
        },
        messages: {

            all_ads_price_id: {required: "Ad Position is Required"},
            ad_start_date: {required: "Start Date is Required"},
            ad_end_date: {required: "End Date is Required"},
            ad_enquiry_photo: {required: "Ad is Required"},
            ad_link: {required: "Ad Link is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// User Ad Request Form validation ends






// product Form validation starts
$(document).ready(function () {
    $("#product_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            product_name: {required: true},
            product_price: {required: true},
            gallery_image: {required: true},
            product_description: {required: true},
            category_id: {required: true}
        },
        messages: {

            product_name: {required: "Product name is Required"},
            product_price: {required: "Product price is Required"},
            gallery_image: {required: "Product image is Required"},
            product_description: {required: "Product description is Required"},
            category_id: {required: "Product category is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// product Form validation ends

// Feedback Form validation starts
$(document).ready(function () {
    $("#feedback_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            feedback_name: {required: true},
            feedback_email: {required: true,email: true},
            feedback_mobile: {required: true},
            feedback_message: {required: true}
        },
        messages: {

            feedback_name: {required: "Name is Required"},
            feedback_email: {required: "Email ID is Required"},
            feedback_mobile: {required: "Phone Number is Required"},
            feedback_message: {required: "Feedback is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Feedback Form validation ends

// Add Coupon Form validation starts
$(document).ready(function () {
    $("#coupon_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            coupon_name: {required: true},
            coupon_code: {required: true},
            coupon_photo: {required: true},
            coupon_start_date: {required: true},
            coupon_end_date: {required: true}
        },
        messages: {

            coupon_name: {required: "Coupon Name is Required"},
            coupon_code: {required: "Offer Code is Required"},
            coupon_photo: {required: "Coupon Photo is Required"},
            coupon_start_date: {required: "Start date is Required"},
            coupon_end_date: {required: "End date is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Add Coupon Form validation ends

// Edit Coupon Form validation starts
$(document).ready(function () {
    $("#edit_coupon_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            coupon_name: {required: true},
            coupon_code: {required: true},
            coupon_start_date: {required: true},
            coupon_end_date: {required: true}
        },
        messages: {

            coupon_name: {required: "Coupon Name is Required"},
            coupon_code: {required: "Offer Code is Required"},
            coupon_start_date: {required: "Start date is Required"},
            coupon_end_date: {required: "End date is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Edit Coupon Form validation ends


//





// Promotion Listing Form validation starts
$(document).ready(function () {
    $("#create_promote_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            type_id: {required: true},
            ad_start_date: {required: true},
            ad_end_date: {required: true}
        },
        messages: {

            type_id: {required: "Listing Name is Required"},
            ad_start_date: {required: "Start Date is Required"},
            ad_end_date: {required: "End Date is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

//Promotion Listing Form validation ends


// Buy Points Form validation starts
$(document).ready(function () {
    $("#buy_points_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            new_points: {required: true}
        },
        messages: {

            new_points: {required: "Points to buy is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Buy Points validation ends




// Installation Form validation starts
$(document).ready(function () {
    $("#install_form").validate({
        onfocusout: function (element) {
            $(element).valid();
        },
        rules: {
            website_name: {required: true},
            website_url: {required: true},
            admin_user_name: {required: true},
            admin_user_email_id: {required: true,email: true},
            admin_currency: {required: true},
            admin_user_password: {required: true},
            admin_confirm_password: {required: true,
                equalTo: "#admin_user_password"}

        },
        messages: {

            website_name: {required: "Website Name is Required"},
            website_url: {required: "Website URL is Required"},
            admin_user_name: {required: "Admin User Name is Required"},
            admin_user_email_id: {required: "Admin Email ID is Required"},
            admin_currency: {required: "Curency Symbol is Required"},
            admin_user_password: {required: "Admin Password is Required"},
            admin_confirm_password: {required: "Confirm Password is Required"}
        },

        submitHandler: function (form) { // for demo
            form.submit();
        }
    });
});

// Installation Form validation ends

