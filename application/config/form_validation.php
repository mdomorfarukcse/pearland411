<?php



$config = [

  'event_rules' => [

    [

      'field' => 'event_name',

      'label' => 'Event Name',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'event_address',

      'label' => 'Event Address',

      'rules' => 'trim|required|min_length[8]'

    ],

    [

      'field' => 'event_start_date',

      'label' => 'Event Date',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'event_time',

      'label' => 'Event Time',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'event_description',

      'label' => 'Event Description',

      'rules' => 'trim|required|min_length[8]'

    ],

    [

      'field' => 'event_map',

      'label' => 'Event Map',

      'rules' => ''

    ],

    [

      'field' => 'event_contact_name',

      'label' => 'Contact Person Name',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'event_mobile',

      'label' => 'Event Date',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'event_email',

      'label' => 'Event Date',

      'rules' => 'trim|required|valid_email'

    ],

    [

      'field' => 'event_website',

      'label' => 'Event Date',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'isenquiry',

      'label' => 'Event Date',

      'rules' => ''

    ],

  ],

  'listing_step_1' => [

    [

      'field' => 'listing_name',

      'label' => 'Listing Name',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'listing_mobile',

      'label' => 'Listing Mobile',

      'rules' => 'trim|min_length[11]'

    ],

    [

      'field' => 'listing_email',

      'label' => 'Listing Email',

      'rules' => 'trim|valid_email|min_length[3]'

    ],

    [

      'field' => 'listing_whatsapp',

      'label' => 'Listing Whatsapp Number',

      'rules' => 'trim|min_length[11]'

    ],

    [

      'field' => 'listing_website',

      'label' => 'Listing Website',

      'rules' => ''

    ],

    [

      'field' => 'listing_address',

      'label' => 'Listing Address',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'listing_lat',

      'label' => 'Listing Latitude',

      'rules' => 'trim'

    ],

    [

      'field' => 'listing_lng',

      'label' => 'Listing Longitude',

      'rules' => 'trim'

    ],

    [

      'field' => 'country_id',

      'label' => 'Listing Country',

      'rules' => 'trim'

    ],

    [

      'field' => 'city_id',

      'label' => 'Listing City',

      'rules' => 'trim'

    ],

    [

      'field' => 'category_id',

      'label' => 'Listing Category',

      'rules' => 'trim'

    ],

    [

      'field' => 'sub_category_id',

      'label' => 'Listing Sub Category',

      'rules' => 'trim'

    ],

    [

      'field' => 'listing_description',

      'label' => 'Listing Description',

      'rules' => 'trim|min_length[10]'

    ],

    [

      'field' => 'service_locations',

      'label' => 'Listing Service Location',

      'rules' => 'trim'

    ],

  ],

  'listing_step_2' => [

    [

      'field' => 'service_name',

      'label' => 'Service Name',

      'rules' => 'trim|min_length[3]'

    ],



  ],

  'listing_step_3' => [

    [

      'field' => 'service_1_name',

      'label' => 'Offer Name',

      'rules' => 'trim|min_length[3]'

    ],

    [

      'field' => 'service_1_price',

      'label' => 'Price',

      'rules' => 'trim'

    ],

    [

      'field' => 'service_1_detail',

      'label' => 'Offer Details',

      'rules' => 'trim|min_length[3]'

    ],

    [

      'field' => 'service_1_view_more',

      'label' => 'Offer More Link',

      'rules' => 'trim|min_length[3]'

    ],



  ],

  'listing_step_4' => [

    [

      'field' => 'listing_video',

      'label' => 'Link',

      'rules' => 'trim'

    ],

    [

      'field' => 'google_map',

      'label' => 'Map',

      'rules' => 'trim'

    ],

    [

      'field' => '360_view',

      'label' => 'Offer Details',

      'rules' => 'trim'

    ],



  ],

  'listing_step_5' => [

    [

      'field' => 'listing_info_question',

      'label' => 'Link',

      'rules' => 'trim'

    ],

    [

      'field' => 'listing_info_answer',

      'label' => 'Map',

      'rules' => 'trim'

    ],



  ],

  'products' => [

    [

      'field' => 'product_name',

      'label' => 'Product Name',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'category_id',

      'label' => 'Product Category',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'product_price',

      'label' => 'Product Price',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'product_payment_link',

      'label' => 'Product Payment Link',

      'rules' => 'trim|required|prep_url'

    ],

    [

      'field' => 'product_description',

      'label' => 'Product Description',

      'rules' => 'trim|required'

    ],

  ],

  'blogs' => [

    [

      'field' => 'blog_name',

      'label' => 'Blog Name',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'blog_description',

      'label' => 'Blog Description',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'isenquiry',

      'label' => 'Isenquiry',

      'rules' => ''

    ],

  ],

  'admin_products' => [

    [

      'field' => 'user_id',

      'label' => 'User',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'product_name',

      'label' => 'Product Name',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'category_id',

      'label' => 'Product Category',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'product_price',

      'label' => 'Product Price',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'product_payment_link',

      'label' => 'Product Payment Link',

      'rules' => 'trim|required|prep_url'

    ],

    [

      'field' => 'product_description',

      'label' => 'Product Description',

      'rules' => 'trim|required'

    ],

  ],

  'admin_event_rules' => [

    [

      'field' => 'user_id',

      'label' => 'User',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'event_name',

      'label' => 'Event Name',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'event_address',

      'label' => 'Event Address',

      'rules' => 'trim|required|min_length[8]'

    ],

    [

      'field' => 'event_start_date',

      'label' => 'Event Date',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'event_time',

      'label' => 'Event Time',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'event_description',

      'label' => 'Event Description',

      'rules' => 'trim|required|min_length[8]'

    ],

    [

      'field' => 'event_map',

      'label' => 'Event Map',

      'rules' => ''

    ],

    [

      'field' => 'event_contact_name',

      'label' => 'Contact Person Name',

      'rules' => 'trim|required|min_length[3]'

    ],

    [

      'field' => 'event_mobile',

      'label' => 'Event Date',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'event_email',

      'label' => 'Event Date',

      'rules' => 'trim|required|valid_email'

    ],

    [

      'field' => 'event_website',

      'label' => 'Event Date',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'isenquiry',

      'label' => 'Event Date',

      'rules' => ''

    ],

  ],

  'enquiry' => [

    [

      'field' => 'listing_id',

      'label' => 'Listing ID',

      'rules' => ''

    ],

    [

      'field' => 'event_id',

      'label' => 'Event ID',

      'rules' => ''

    ],

    [

      'field' => 'blog_id',

      'label' => 'Blog ID',

      'rules' => ''

    ],

    [

      'field' => 'product_id',

      'label' => 'Product ID',

      'rules' => ''

    ],

    [

      'field' => 'receiving_user_id',

      'label' => 'Listing User ID',

      'rules' => ''

    ],

    [

      'field' => 'enquiry_sender_id',

      'label' => 'Enquiry Sender ID',

      'rules' => 'required'

    ],

    [

      'field' => 'enquiry_source',

      'label' => 'Enquiry Source',

      'rules' => 'trim'

    ],

    [

      'field' => 'enquiry_type',

      'label' => 'Enquiry Type',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'enquiry_name',

      'label' => 'Enquiry Name',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'enquiry_email',

      'label' => 'Enquiry Email',

      'rules' => 'trim|required|valid_email'

    ],

    [

      'field' => 'enquiry_mobile',

      'label' => 'Enquiry Mobile',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'enquiry_message',

      'label' => 'Enquiry Message',

      'rules' => 'trim|required'

    ],

  ],

  'admin_listing_scratch' => [

    [

      'field' => 'user_id',

      'label' => 'User ID',

      'rules' => 'trim|required'

    ],

    [

      'field' => 'listing_name',

      'label' => 'Listing Name',

      'rules' => 'trim|required'

    ]

  ],

  'user_listing_scratch' => [
    [
      'field' => 'listing_name',
      'label' => 'Listing Name',
      'rules' => 'trim|required'
    ]
  ],

  'admin_duplicate_listing' =>[        

    [

      'field' => 'listing_name',

      'label' => 'Listing Name',

      'rules' => 'trim|required'

    ],

  ],
  'news' =>[        

    [

      'field' => 'news_title',

      'label' => 'News Title',

      'rules' => 'trim|required'

    ],
    [

      'field' => 'news_description',

      'label' => 'News Description',

      'rules' => 'trim|required'

    ],

  ],
  'video' =>[        

    [

      'field' => 'video_title',
      'label' => 'Video Title',
      'rules' => 'trim|required'

    ],
    [

      'field' => 'video_code',
      'label' => 'Video Code',
      'rules' => 'trim|required'

    ],

  ],
  'classifieds' =>[        

    [

      'field' => 'classifieds_title',
      'label' => 'Classifieds Title',
      'rules' => 'trim|required'

    ],
    [

      'field' => 'classifieds_price',
      'label' => 'Classifieds Code',
      'rules' => 'trim|required'

    ],

  ],'rental' =>[        

    [

      'field' => 'rental_title',
      'label' => 'Rental Title',
      'rules' => 'trim|required'

    ],
    [

      'field' => 'rental_price',
      'label' => 'Rental Price',
      'rules' => 'trim|required'

    ],

  ],
  'house' =>[        

    [

      'field' => 'house_title',
      'label' => 'House Title',
      'rules' => 'trim|required'

    ],
    [

      'field' => 'house_price',
      'label' => 'House Price',
      'rules' => 'trim|required'

    ],

  ],
  'coupons' =>[        

    [

      'field' => 'coupon_name',

      'label' => 'Coupon Title',

      'rules' => 'trim|required'

    ],
    [

      'field' => 'coupon_code',

      'label' => 'Coupon code',

      'rules' => 'trim|required'

    ],

  ],
  


];