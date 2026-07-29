<!doctype html>
<!--
===============================================================================
CONTROLLER DATA REQUIREMENTS
===============================================================================

Required Controller Method (Home.php or User.php):
- Load model: $this->load->model('usermodel');
- Get data from database and pass to view
- Required variables: latest_listings, latest_videos, latest_news, featured_listings, all_listing_categories

Database Tables Required:
- listings (business listings)
- videos (video content)  
- news (news/announcements)
- listing_categories (business categories)

===============================================================================
-->
<?php
/**
 * Category URL. The stored slug is used verbatim when there is one — that is
 * the string the directory matches on — and only a missing slug is generated,
 * via the shared pearland_slug() so every link on the site derives the same
 * URL from a given name. The four category lists on this page each used to
 * carry their own copy of this rule, and they disagreed with the header's and
 * the directory's on names containing "&": "Medical & Dental" became
 * medical-and-dental here and medical-dental there, so one of the two links
 * always landed on an empty page.
 *
 * Defined here, above every list that uses it.
 */
$home_cat_url = function ($category) {
	$slug = !empty($category->category_slug)
		? (string) $category->category_slug
		: pearland_slug($category->category_name);
	return base_url('all_listing/' . rawurlencode($slug));
};
?>
<html lang="en">

<head>
  <!--== META TAGS ==-->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

  <?php include APPPATH . 'views/partials/seo_head.php'; ?>

  <!--== FAV ICON(BROWSER TAB ICON) ==-->
  <link rel="shortcut icon" href="<?= base_url() ?>assets/images/fav.ico" type="image/x-icon">
  <!--== GOOGLE FONTS ==-->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Oswald:700|Source+Sans+Pro:300,400,600,700&display=swap" rel="stylesheet">
  <!--== WEB ICON FONTS ==-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--== Bootstrap CDN ==-->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <!-- Style Path -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/style.css">
  <!-- Brand palette + typography overrides — loaded last so it wins. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/pearland-brand.css?v=1">
  <!-- Homepage-only sections. Every selector is scoped to .p411-home and no
       other view loads this file, so it cannot affect the rest of the site. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/home-sections.css?v=1">
  <!-- The two house promo cards (partials/service_promo.php and
       partials/banner_design_promo.php). Shared with the directory and
       listing-details pages so the cards can't drift; every selector is a
       .p411-promo / .p411-bpromo descendant and nothing else emits those. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/promo-cards.css?v=1">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Calendar -->


  <style>
    /* ======= Root Variables ======= */
    :root {
      --primary-color: #180f5e;
      --accent-color: #7bb73f;
      --text-dark: #333;
      --text-light: #666;
      --bg-light: #f8f9fa;
      --border-color: #e0e0e0;
    }

    body {
      overflow-x: hidden !important;
    }

    .full_wrapper {
      margin: 0 auto;
      background-color: white !important;
    }

    .hom-top {
      transition: all 0.5s ease;
      background: none;
      box-shadow: none;
    }

    .top-ser {
      display: none;
    }

    .dmact .top-ser {
      display: block;
    }

    .caro-home {
      margin-top: 90px;
      width: 100%;
    }

    .carousel-item:before {
      background: none;
    }

    /* Basic Overrides CSS */

    .full_wrapper .hom-top {
      padding: 10px 0 !important;
    }

    .full_wrapper .hom-head {
      padding: 0px !important;
      margin-bottom: 0 !important;
    }

    .full_wrapper .hom-head:after {
      content: unset !important;
      display: unset !important;
      background: unset !important;
      position: unset !important;
      width: unset !important;
      height: unset !important;
    }

    .full_wrapper .ban-ql {
      margin-top: 0 !important;
    }

    /* Basic Overrides CSS */

    /* ======= Reset & Base ======= */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Poppins", sans-serif;
      line-height: 1.6;
      color: var(--text-dark);
      background: #fff;
    }

    .container {
      max-width: 1400px !important;
    }

    /* ======= Banner Section ======= */
    .new_banner {
      background: linear-gradient(177deg, var(--primary-color) 0%, #7bb73f88 50%, var(--accent-color) 100%);
      padding: 120px 0 80px 0;
      position: relative;
      overflow: hidden;
    }

    .new_banner::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      opacity: 0.3;
    }

    .new_banner .banner-content {
      position: relative;
      z-index: 2;
      text-align: center;
      color: white;
    }

    .new_banner .banner-title {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 20px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
      animation: fadeInUp 1s ease;
    }

    .new_banner .banner-subtitle {
      font-size: 1.3rem;
      margin-bottom: 40px;
      opacity: 0.9;
      animation: fadeInUp 1s ease 0.2s both;
    }

    .new_banner .search-box {
      max-width: 810px;
      margin: 0 auto;
      position: relative;
      animation: fadeInUp 1s ease 0.4s both;
    }

    .new_banner .search-input {
      padding: 16px 60px 16px 24px;
      border: none;
      border-radius: 50px;
      font-size: 1.15rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      width: 100%;
      height: 54px;
      background: #fff;
    }

    .new_banner .search-btn {
      position: absolute !important;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      background: var(--accent-color);
      border: none;
      padding: 10px 22px;
      border-radius: 50px;
      color: white;
      font-size: 1.25rem;
      transition: all 0.3s ease;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .new_banner .search-btn:hover {
      background: var(--primary-color) !important;
      transform: translateY(-50%) scale(1.05);
    }

    /* ======= Category Tabs ======= */
    .category-tabs {
      background: white;
      padding: 20px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .new_category .nav-pills .nav-link svg {
      pointer-events: none;
    }

    .new_category .nav-pills .nav-link {
      color: var(--text-dark);
      background: transparent;
      border-radius: 25px;
      padding: 10px 25px;
      margin: 0 5px;
      font-weight: 500;
      transition: all 0.3s ease;
      border: 2px solid transparent;
      cursor: pointer;
      display: inline-block;
    }

    .new_category .nav-pills .nav-link:hover {
      background: var(--accent-color);
      color: white;
      transform: translateY(-2px);
    }

    .new_category .nav-pills .nav-link.active {
      background: var(--accent-color);
      color: white;
    }



    /* ======= Main Content Layout ======= */
    .main-content {
      padding: 40px 0;
      background: var(--bg-light);
    }

    .main-content-flex {
      display: flex;
      gap: 30px;
    }

    .main-content-flex .main-area {
      width: 70%;
      min-width: 0;
    }

    .main-content-flex .sidebar {
      width: 30%;
      min-width: 260px;
      padding-left: 0;
    }

    @media (max-width: 991.98px) {
      .main-content-flex {
        flex-direction: column;
        gap: 0;
      }

      .main-content-flex .main-area,
      .main-content-flex .sidebar {
        width: 100%;
        min-width: 0;
        padding-left: 0;
      }

      .sidebar {
        margin-top: 40px;
      }
    }

    /* ======= BizBook Listing Styles ======= */
    #business-list {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
    }

    .lst-com-box {
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      border: 1px solid var(--border-color);
      /* display: flex;
			flex-direction: column;
			justify-content: space-between; */
    }

    .lst-com-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .list-com-img {
      position: relative;
      background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: -3px;
      margin-right: -3px;
      margin-left: -3px;
    }

    .list-com-img img {
      max-width: 100%;
      width: 100%;
      border-top-right-radius: 15px;
      border-top-left-radius: 15px;
      height: 150px;
      object-fit: cover;
      background: #fff;
      border: 3px solid #fff;

    }

    .list-com-cat {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(255, 255, 255, 0.9);
      color: var(--primary-color);
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .list-com-con {
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 180px;

    }

    .list-com-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 8px;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .list-com-desc {
      color: var(--text-light);
      font-size: 0.95rem;
      line-height: 1.5;
      margin-bottom: 15px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .list-enq-btn {
      display: inline-block;
      background: var(--accent-color);
      color: white;
      padding: 8px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      width: fit-content;
    }

    .list-enq-btn:hover {
      background: var(--primary-color);
      color: white;
      text-decoration: none;
      transform: translateY(-2px);
    }

    /* ======= Blog/News Styles ======= */

    .blog .section-title {
      margin-top: 10px !important;
    }

    .blog-box {
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      border: 1px solid var(--border-color);
      position: relative;
    }

    .blog-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .blog-img {

      height: 200px;
      background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .blog-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .blog-box .blog-cat {
      position: absolute;
      top: 10px !important;
      right: 10px !important;
      padding: 6px 12px;
      border-radius: 30px;
      background-color: var(--accent-color);
      color: white !important;
      font-size: 12px;
    }

    .blog-con {
      padding: 15px 15px 25px 15px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 207px;
    }

    .blog-con h4 {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 10px;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .blog-con p {
      color: var(--text-light);
      font-size: 0.95rem;
      line-height: 1.5;
      margin-bottom: 20px;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .blog-con .blog-read {
      color: white !important;
      text-decoration: none;
      font-weight: 500;
      background-color: var(--accent-color);
      padding: 10px 16px;
      border-radius: 31px;
      font-size: 14px;
      transition: all 0.3s ease;
      width: fit-content;
    }

    .blog-con .blog-read svg {
      stroke: white;
    }

    .blog-con .blog-read:hover {
      background-color: var(--primary-color);
      text-decoration: none;
    }

    /* ======= Video Styles ======= */
    .video-box {
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      margin-bottom: 25px;
      border: 1px solid var(--border-color);
    }

    .video-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .video-img {
      position: relative;
      height: 200px;
    }

    .video-img iframe {
      width: 100%;
      height: 100%;
      border: 0;
    }

    .video-con {
      padding: 20px;
      min-height: 158px;
    }

    .video-con h3 {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 10px;
      line-height: 1.3;
    }

    .video-con p {
      color: var(--text-light);
      font-size: 0.95rem;
      line-height: 1.5;
    }

    /* ======= Section Titles ======= */
    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary-color);
      margin-top: 2.5rem;
      margin-bottom: 1.5rem;
      position: relative;
      display: inline-block;
    }

    .section-title::after {
      content: "";
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--accent-color);
      border-radius: 2px;
    }

    /* ======= Swiper Slider ======= */
    .swiper {
      width: 100%;
      height: 400px;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .swiper-slide {
      position: relative;
      background-size: cover;
      background-position: center;
      background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
    }

    .slide-content {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
      color: white;
      padding: 40px 30px 30px;
    }

    .slide-title {
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .slide-meta {
      font-size: 0.9rem;
      opacity: 0.8;
      margin-bottom: 15px;
    }

    /* ======= News Cards ======= */
    .news-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(24, 15, 94, 0.07);
      transition: all 0.3s ease;
      margin-bottom: 0;
      height: 100%;
    }

    .news-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .card-image {
      height: 200px;
      background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
      position: relative;
      overflow: hidden;
      border-radius: 10px 10px 0 0;
    }

    .card-category {
      position: absolute;
      top: 15px;
      left: 15px;
      background: var(--accent-color);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }

    .card-content {
      padding: 0 0 0.5rem 0;
    }

    .card-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 15px;
      line-height: 1.4;
    }

    .card-excerpt {
      color: var(--text-light);
      margin-bottom: 16px;
      line-height: 1.6;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      min-height: 4.8em;
      max-height: 4.8em;
    }

    .card-bullets {
      margin: 0 0 8px 0;
      padding-left: 18px;
      color: var(--text-light);
      font-size: 0.95rem;
    }

    .card-tags .badge {
      margin-right: 5px;
      margin-bottom: 2px;
      background: #e0e0e0;
      color: #333;
      font-size: 0.8rem;
      border-radius: 12px;
      padding: 3px 10px;
    }

    .card-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: var(--text-light);
      font-size: 0.9rem;
    }

    .card-meta img {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      margin-right: 6px;
      object-fit: cover;
      vertical-align: middle;
    }

    .read-more {
      color: var(--accent-color);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .read-more:hover {
      color: var(--primary-color);
      text-decoration: none;
    }

    .col-md-4,
    .col-sm-6,
    .col-md-6 {
      padding: 10px !important;
    }

    /* ======= Sidebar ======= */
    .sidebar {
      padding-left: 30px;
    }

    .widget {
      background: white;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .widget-title {
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 20px;
      position: relative;
    }

    .widget-title::after {
      content: "";
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 40px;
      height: 3px;
      background: var(--accent-color);
      border-radius: 2px;
    }

    .trending-item {
      display: flex;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid var(--border-color);
      transition: all 0.3s ease;
    }

    .trending-item:last-child {
      border-bottom: none;
    }

    .trending-item:hover {
      background: var(--bg-light);
      margin: 0 -15px;
      padding-left: 15px;
      padding-right: 15px;
      border-radius: 10px;
    }

    .trending-number {
      background: var(--accent-color);
      color: white;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      margin-right: 15px;
      flex-shrink: 0;
    }

    .trending-content h6 {
      font-size: 0.95rem;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 5px;
      line-height: 1.4;
    }

    .trending-meta {
      font-size: 0.8rem;
      color: var(--text-light);
    }

    .newsletter-form {
      display: flex;
      margin-top: 15px;
    }

    .newsletter-input {
      flex: 1;
      padding: 12px 15px;
      border: 2px solid var(--border-color);
      border-radius: 25px 0 0 25px;
      border-right: none;
    }

    .newsletter-btn {
      background: var(--accent-color);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 0 25px 25px 0;
      transition: all 0.3s ease;
    }

    .newsletter-btn:hover {
      background: var(--primary-color);
    }

    /* Calendar */

    #sidebar-calendar {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(24, 15, 94, 0.10);
      padding: 0;
      overflow: hidden;
      width: 100%;
    }

    #sidebar-calendar .fc {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 0;
      box-shadow: 0 2px 8px rgba(24, 15, 94, 0.06);
      width: 100%;
    }

    .fc .fc-toolbar {
      margin-bottom: 6px;
      padding: 10px 10px 0 10px;
    }

    .fc .fc-toolbar-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--primary-color, #2a1a7f);
      letter-spacing: 0.5px;
    }

    .fc .fc-button {
      background: var(--accent-color, #7bb73f);
      border: none;
      color: #fff;
      border-radius: 8px;
      padding: 0.15em 0.7em;
      font-size: 0.95rem;
      box-shadow: 0 2px 6px rgba(24, 15, 94, 0.08);
      transition: background 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 32px;
      width: 32px;
    }

    .fc .fc-button svg {
      width: 18px;
      height: 18px;
      display: block;
    }

    .fc .fc-button:hover,
    .fc .fc-button:focus {
      background: #d11d1d;
      color: #fff;
    }

    .fc .fc-button:disabled {
      background: #eee;
      color: #aaa;
    }

    .fc .fc-daygrid-day.fc-day-today {
      background: #e9f0ff;
      border-radius: 8px;
      box-shadow: 0 1px 4px rgba(24, 15, 94, 0.06);
    }

    .fc .fc-col-header-cell-cushion {
      font-weight: 600;
      color: var(--primary-color, #2a1a7f);
      font-size: 0.93rem;
      padding: 2px 0;
    }

    .fc .fc-daygrid-day-number {
      font-size: 0.93rem;
      font-weight: 500;
      color: #333;
      padding: 2px 0 2px 2px;
    }

    .fc .fc-daygrid-day {
      height: 32px;
    }

    .fc .fc-scrollgrid {
      border-radius: 10px;
      overflow: hidden;
    }

    .fc .fc-event {
      border-radius: 8px;
      font-size: 0.89em;
      padding: 1px 6px;
      font-weight: 600;
      border: none;
      box-shadow: 0 1px 4px rgba(24, 15, 94, 0.07);
      letter-spacing: 0.2px;
    }

    .fc .fc-event-title {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .fc .fc-daygrid-event-dot {
      display: none;
    }

    .fc .fc-daygrid-event {
      margin-top: 2px;
    }

    .fc .fc-daygrid-day-frame {
      padding: 0 1px;
    }

    .fc .fc-button .fc-icon {
      font-size: 0 !important;
    }

    @media (max-width: 576px) {
      #sidebar-calendar {
        padding: 0;
      }

      #sidebar-calendar .fc {
        padding: 0;
      }

      .fc .fc-toolbar-title {
        font-size: 0.95rem;
      }

      .fc .fc-col-header-cell-cushion {
        font-size: 0.9rem;
      }

      .fc .fc-daygrid-day-number {
        font-size: 0.9rem;
      }
    }

    /* Calendar */

    /* Weather Widget Styles */
    .weather-widget {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 24px rgba(24, 15, 94, 0.08);
      padding: 22px 20px 18px 20px;
      margin-bottom: 30px;
    }

    .weather-current {
      display: flex;
      align-items: center;
      gap: 18px;
      background: #f8f9fa;
      border-radius: 10px;
      padding: 18px 18px 14px 18px;
      margin-bottom: 10px;
      min-height: 80px;
    }

    .weather-current-icon {
      width: 48px;
      height: 48px;
      flex-shrink: 0;
    }

    .weather-current-temp {
      font-size: 2.2rem;
      font-weight: 700;
      color: #2a1a7f;
      line-height: 1;
    }

    .weather-current-details {
      font-size: 1rem;
      color: #333;
      font-weight: 500;
    }

    .weather-current-meta {
      font-size: 0.92rem;
      color: #666;
      margin-top: 2px;
      line-height: 1.4;
    }

    #weather-forecast {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      grid-template-rows: repeat(2, auto);
      gap: 8px 8px;
      margin-top: 15px;
    }

    .weather-forecast-day {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 10px 6px 8px 6px;
      text-align: center;
      min-width: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: none;
    }

    .weather-forecast-label {
      font-size: 0.93rem;
      color: #222;
      font-weight: 600;
      margin-bottom: 2px;
    }

    .weather-forecast-icon {
      width: 28px;
      height: 28px;
      margin-bottom: 2px;
    }

    .weather-forecast-temp {
      font-size: 1.08rem;
      font-weight: 700;
      color: #7bb73f;
      margin-bottom: 0;
    }

    .weather-forecast-desc {
      font-size: 0.82rem;
      color: #666;
      line-height: 1.2;
      min-height: 24px;
      margin-top: 2px;
    }

    @media (max-width: 576px) {
      #weather-forecast {
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, auto);
        gap: 6px 4px;
      }

      .weather-forecast-day {
        padding: 8px 2px 6px 2px;
      }
    }

    /* Weather Widget Styles */


    .featured-section .btn {
      background-color: var(--accent-color) !important;
      border-color: var(--accent-color) !important;
      padding: 8px 15px !important;
      border-radius: 20px !important;
    }

    .featured-section .btn:hover {
      background-color: var(--primary-color) !important;
      border-color: var(--primary-color) !important;

    }

    /* =========================LIVE & VIDEO NEWS HIGHLIGHTS SECTION STYLES========================= */

    .video-section .video-box img {
      height: 200px !important;
      object-fit: cover !important;
      width: 100%;
      border-top-right-radius: 14px;
      border-top-left-radius: 14px;
    }


    .video-highlights-section {
      /* Section spacing and background */
      background: #f8f9fa;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(24, 15, 94, 0.08);
      padding: 30px 0 10px 0;
    }

    .video-highlights-section .section-title {
      margin-top: 0px !important;
    }

    .video-highlights-section .section-title svg {
      color: #7bb73f;
    }

    .video-card {
      transition: box-shadow 0.2s, transform 0.2s;
      border-radius: 14px;
      overflow: hidden;
      background: #fff;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .video-card:hover {
      box-shadow: 0 12px 32px rgba(24, 15, 94, 0.13);
      transform: translateY(-6px) scale(1.02);
    }

    .video-thumb {
      position: relative;
      background: #222;
    }

    .video-tag {
      position: absolute;
      top: 14px;
      left: 14px;
      padding: 4px 14px 4px 8px;
      border-radius: 16px;
      font-size: 0.88rem;
      font-weight: 600;
      color: #fff;
      background: #7bb73f;
      display: flex;
      align-items: center;
      box-shadow: 0 2px 8px rgba(24, 15, 94, 0.1);
      z-index: 2;
      letter-spacing: 0.5px;
    }

    .live-tag {
      background: #7bb73f;
    }

    .watch-tag {
      background: #2a1a7f;
    }

    .video-title {
      font-size: 22px;
      color: #180f5e;
      font-weight: 700;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .video-desc {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .video-meta .badge {
      font-size: 0.82rem;
      vertical-align: middle;
      margin-left: 2px;
      font-weight: 600;
      border-radius: 10px;
      padding: 3px 10px;
      color: #fff;
    }

    @media (max-width: 991.98px) {
      .video-highlights-section .row>div {
        margin-bottom: 24px;
      }
    }

    @media (max-width: 767.98px) {
      .video-highlights-section .row {
        flex-direction: column;
      }

      .video-highlights-section .col-md-4 {
        max-width: 100%;
        flex: 0 0 100%;
      }

      .video-thumb iframe {
        height: 180px !important;
      }

      #business-list {

        grid-template-columns: repeat(1, 1fr);

      }
    }

    @media (max-width: 576px) {
      .video-thumb iframe {
        height: 150px !important;
      }

      .video-title {
        font-size: 1rem;
      }

      .video-meta {
        font-size: 0.9rem;
      }

      #business-list {

        grid-template-columns: repeat(1, 1fr);

      }
    }

    .btn-learn-more {
      border-radius: 22px;
      padding: 8px 22px;
      font-weight: 600;
      font-size: 1rem;
      color: #2a1a7f !important;
      background: #fff !important;
      border: none;
      box-shadow: 0 2px 8px rgba(24, 15, 94, 0.08);
      transition: background 0.2s, color 0.2s;
    }

    .btn-learn-more:hover {
      background: #f1f1f1 !important;
      color: #7bb73f !important;
    }

    /* ======= Buttons ======= */
    .btn-view-more,
    .btn.btn-outline-primary {
      background: var(--primary-color);
      color: #fff !important;
      border: none;
      border-radius: 25px;
      padding: 12px 36px;
      font-weight: 600;
      font-size: 1.1rem;
      box-shadow: 0 4px 16px rgba(24, 15, 94, 0.08);
      transition: background 0.2s, color 0.2s, box-shadow 0.2s;
      letter-spacing: 0.5px;
    }

    .btn-view-more:hover,
    .btn.btn-outline-primary:hover {
      background: var(--accent-color);
      color: #fff !important;
      box-shadow: 0 8px 24px rgba(247, 31, 31, 0.12);
    }

    /* ======= Animations ======= */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .fade-in {
      animation: fadeInUp 0.6s ease;
    }

    /* ======= Swiper Custom ======= */
    .swiper-button-next,
    .swiper-button-prev {
      color: white !important;
      background: var(--accent-color) !important;
      width: 50px !important;
      height: 50px !important;
      border-radius: 50%;
      margin-top: -25px !important;
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
      font-size: 18px !important;
      color: white !important;
    }

    .swiper-pagination-bullet {
      background: white !important;
      opacity: 0.7;
    }

    .swiper-pagination-bullet-active {
      background: var(--accent-color) !important;
      opacity: 1;
    }

    /* ===========  Categoris List =============== */
    .category-list {
      display: flex;
      flex-direction: column;
    }

    .category-list li {
      border-bottom: 1px solid #66666626;
      width: 100%;
      padding: 8px 0;
    }

    .category-list li:last-child {
      border-bottom: none;
    }

    .category-list li a {
      text-decoration: none;
      color: black;
      transition: all .3s ease;
    }

    .category-list li a:hover {
      color: var(--accent-color);
    }



    /* ===========  Categoris List =============== */

    /* ======= List Group ======= */
    .list-group-item {
      border: none !important;
      padding: 0 !important;
      border-bottom: 1px solid var(--border-color) !important;
      transition: all 0.3s ease;
    }

    .list-group-item:hover {
      color: var(--accent-color);
    }

    .list-group-item:last-child {
      border-bottom: none !important;
    }

    .badge-primary {
      background: var(--accent-color) !important;
    }

    /* ======= Interactive Effects ======= */
    .pulse {
      animation: pulse 1s infinite;
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.05);
      }

      100% {
        transform: scale(1);
      }
    }

    .clicked {
      transform: scale(0.98);
      transition: transform 0.1s ease;
    }

    .animate {
      animation: fadeInUp 0.6s ease forwards;
    }

    /* ======= Ripple Effect ======= */
    .ripple {
      position: absolute;
      border-radius: 50%;
      transform: scale(0);
      animation: ripple 0.6s linear;
      pointer-events: none;
      background: rgba(255, 255, 255, 0.5);
    }

    @keyframes ripple {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }

    /* ======= Responsive ======= */
    @media (max-width: 992px) {
      .swiper {
        height: 300px;
      }

      .slide-title {
        font-size: 1.4rem;
      }
    }

    @media (max-width: 768px) {
      .main-content-flex {
        /* Was column-reverse, which fought the 991.98px rule above and put the
           sidebar ABOVE the businesses on phones — visitors met the weather
           widget before a single listing. .main-area is first in the DOM, so
           plain `column` is all that's needed. */
        flex-direction: column;
        gap: 0;
      }

      .main-content-flex .main-area,
      .main-content-flex .sidebar {
        width: 100%;
        min-width: 0;
        padding-left: 0;
      }

      .sidebar {
        margin-top: 40px;
        padding-left: 0;
      }

      .section-title {
        font-size: 2rem;
      }

      .new_banner .banner-title {
        font-size: 2.5rem;
      }
    }

    @media (max-width: 576px) {
      .new_banner .banner-title {
        font-size: 2rem;
      }

      .banner-subtitle {
        font-size: 1.1rem;
      }

      .search-input {
        padding: 12px 48px 12px 16px;
        font-size: 1rem;
        height: 44px;
      }

      .nav-pills .nav-link {
        padding: 8px 15px;
        margin: 2px;
        font-size: 0.9rem;
      }

      .card-title {
        font-size: 1.1rem;
      }

      .swiper {
        height: 250px;
      }

      .slide-content {
        padding: 20px 20px 20px;
      }

      .slide-title {
        font-size: 1.2rem;
      }

      .custom-ad-banner {
        padding: 18px 12px 18px 12px;
      }

      .ad-title {
        font-size: 1.1rem;
      }

      .ad-badge {
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        font-size: 0.95rem;
      }
    }

    /* ======= Scoped Styles for .new_style ======= */


    .new_style .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary-color);
      margin-top: 1.5rem;
      margin-bottom: 1.5rem !important;
      position: relative;
      display: inline-block;
    }

    .new_style .section-title::after {
      content: "";
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--accent-color);
      border-radius: 2px;
    }

    /* Swiper Slider */
    .new_style .swiper {
      width: 100%;
      height: 400px;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .new_style .swiper-slide {
      position: relative;
      background-size: cover;
      background-position: center;
      background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
    }

    .new_style .slide-content {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      top: 0;
      transform: translateY(22%);
      background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
      color: white;
      padding: 40px 30px 30px 100px;
    }

    .new_style .slide-title {
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .new_style .slide-meta {
      font-size: 0.9rem;
      opacity: 0.8;
      margin-bottom: 15px;
    }

    /* News Cards */
    .new_style .news-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(24, 15, 94, 0.07);
      transition: all 0.3s ease;
      margin-bottom: 0;
      height: 100%;
    }

    .new_style .news-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .new_style .card-image {
      height: 200px;
      background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
      position: relative;
      overflow: hidden;
      border-radius: 10px 10px 0 0;
    }

    .new_style .card-category {
      position: absolute;
      top: 15px;
      left: 15px;
      background: var(--accent-color);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }

    .new_style .card-content {
      padding: 0 0 0.5rem 0;
    }

    .new_style .card-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 15px;
      line-height: 1.4;
    }

    .new_style .card-excerpt {
      color: var(--text-light);
      margin-bottom: 16px;
      line-height: 1.6;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      min-height: 4.8em;
      max-height: 4.8em;
    }

    .new_style .card-bullets {
      margin: 0 0 8px 0;
      padding-left: 18px;
      color: var(--text-light);
      font-size: 0.95rem;
    }

    .new_style .card-tags .badge {
      margin-right: 5px;
      margin-bottom: 2px;
      background: #e0e0e0;
      color: #333;
      font-size: 0.8rem;
      border-radius: 12px;
      padding: 3px 10px;
    }

    .new_style .card-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: var(--text-light);
      font-size: 0.9rem;
    }

    .new_style .card-meta img {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      margin-right: 6px;
      object-fit: cover;
      vertical-align: middle;
    }

    .new_style .read-more {
      color: var(--accent-color);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .new_style .read-more:hover {
      color: var(--primary-color);
      text-decoration: none;
    }

    .new_style .col-md-4,
    .new_style .col-sm-6,
    .new_style .col-md-6 {
      padding: 10px !important;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .new_style .swiper {
        height: 300px;
      }

      .new_style .slide-title {
        font-size: 1.4rem;
      }
    }

    @media (max-width: 576px) {
      .new_style .section-title {
        font-size: 25px;
      }

      .new_style .slide-title {
        font-size: 1.2rem;
      }

      .new_style .swiper {
        height: 250px;
      }

      .new_style .slide-content {
        padding: 20px 20px 20px;
      }
    }

    /* ========================= Pricing Section Start ========================= */
    .ads-pricing-section {
      padding: 50px 0;
      background: linear-gradient(135deg, #ffffff 0%, var(--bg-light) 100%);
    }

    .ads-pricing-section .section-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .ads-pricing-section .section-header h2 {
      font-size: 35px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 12px;
    }

    .ads-pricing-section .section-header .highlight {
      color: var(--accent-color);
    }

    .ads-pricing-section .section-header .subtitle {
      font-size: 16px;
      color: var(--text-light);
      max-width: 550px;
      margin: 0 auto;
      line-height: 1.5;
    }

    .ads-pricing-section .pri {
      padding: 0px !important;
      background: none !important;
    }

    .ads-pricing-section .pri ul {
      display: flex;
      justify-content: center;
    }

    .ads-pricing-section .pri ul li {
      width: 30% !important;
    }

    .ads-pricing-section .pri .pri-box {
      background: #fff;
      border-radius: 11px;
      transition: all .4s;
      box-shadow: 0 5px 25px -7px #00000021;
      border: 1px solid #f0f4f7;
    }

    .ads-pricing-section .pri ul li:nth-child(3) .pri-box {
      background: #fff !important;
    }

    .ads-pricing-section .pri .pri-box .c2 {
      background: none !important;
      border-radius: 8px 8px 0 0;
      border-bottom: 1px solid #d9d9d9;
    }

    .ads-pricing-section .pri-box .c2 h4 {
      color: black !important;
      font-size: 18px !important;
      font-weight: 700 !important;
    }

    .ads-pricing-section .pri-box .c4 {
      text-align: left !important;
      padding-bottom: 40px !important;
      padding-left: 100px !important;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .ads-pricing-section .pri-box .c4 .feature-item {
      text-align: left !important;
    }

    .ads-pricing-section .pri-box .c4 .feature-item svg {
      color: var(--accent-color) !important;
      fill: var(--accent-color) !important;
      stroke: var(--accent-color) !important;
    }

    .ads-pricing-section .pri-box .c3 p {
      margin-bottom: 0 !important;
    }

    .ads-pricing-section .pri-box .c3 {
      padding: 40px 20px 20px 20px !important;
    }

    .ads-pricing-section .pri ul li:nth-child(3) .pri-box .c2 {
      background: var(--accent-color) !important;
      color: white !important;
    }

    .ads-pricing-section .pri ul li:nth-child(3) .pri-box .c2 h4 {
      color: white !important;
    }

    .ads-pricing-section .pri-box .c5 a {
      background: var(--accent-color) !important;
      color: white !important;
      border-radius: 50px !important;
      padding: 10px 25px !important;
      font-size: 14px !important;
    }

    .ads-pricing-section .pri-box .c5 a:hover {
      background: var(--primary-color) !important;
    }

    @media (max-width: 768px) {

      .ads-pricing-section .section-header h2 {
        font-size: 30px;
      }

      .ads-pricing-section .pri ul {
        flex-direction: column;
      }

      .ads-pricing-section .pri ul li {
        width: 100% !important;
      }

      .ads-pricing-section .pri-box .c4 {
        padding-left: 60px !important;
      }

    }

    /* ========================= Pricing Section Start ========================= */

    /* Website Development Section */
    .web-dev-section {
      padding: 50px 0;
    }

    .btn-get-started {
      background: var(--primary-color);
      color: white;
      padding: 15px 40px;
      font-size: 1.1rem;
      border-radius: 30px;
      font-weight: 600;
      border: none;
      margin: 0 10px;
      transition: all 0.3s;
    }

    .btn-get-started:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
    }

    .feature-box {
      background: white;
      border-radius: 15px;
      padding: 15px;
      text-align: center;
      height: 100%;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .feature-icon {
      width: 80px;
      height: 80px;
      background: var(--primary-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 30px;
      color: white;
    }

    .feature-icon svg path {
      stroke: white !important;
    }

    .feature-icon svg rect {
      stroke: white !important;
    }

    .feature-icon svg circle {
      stroke: white !important;
    }

    .feature-icon svg line {
      stroke: white !important;
    }

    .feature-title {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 15px;
    }

    .feature-description {
      color: #6b7280;
    }


    /* ................................................. 😊 New CSS for setuped new logo 😊 ................................................ */

    .fhns .hom-nav .ic-logo {
      width: 150px !important;
      margin-top: 0 !important;
    }

    .fhns .menu {
      margin-top: 40px !important;
    }

    .fhns .hom-nav .bl {
      padding-top: 34px !important;
    }

    .fhns .new_banner {
      padding: 160px 0 110px 0 !important;
    }

    @media only screen and (max-width: 767px) {
      .px-10 {
        padding-right: 10px;
        padding-left: 10px;
      }

      .mob-me-ic {
        top: 25px !important;
        right: 15px;
        cursor: pointer;
      }

      .mob-me-clo {
        cursor: pointer;
      }

      .hom-nav {
        padding: 0 20px;
      }

      .featured-section .swiper-button-next,
      .featured-section .swiper-button-prev {
        display: none;
      }

      .category-tabs {
        display: none !important;
      }

      .hom-top {
        background: unset !important;
      }
    }

    /* CTA */
    .CTA {
      padding: 60px 0 50px;
      position: relative;
      float: left;
      width: 100%;
      background: url(../assets/../assets/images/worldmap.png) #11009bdb;
      background-size: 700px;
      color: white;
      text-align: center;
    }

    .CTA h4 {
      font-size: 34px;
      padding-bottom: 10px;
      font-weight: 600;
      color: white;
    }

    .CTA p {
      margin-bottom: 0;
      font-size: 16px;
      font-weight: 400;
      line-height: 32px;
      padding-bottom: 30px;
      color: white;
    }

    .CTA a {
      color: #fff;
      padding: 15px 30px;
      border-radius: 2px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      width: fit-content;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      border: 1px solid #dbd6ff;
      margin: auto;
    }

    .CTA a:hover {
      background: #5538cb;
      box-shadow: 0 8px 15px -10px #0000003b;
      border: 1px solid #5538cb;
      text-decoration: none;
    }


    /* CTA */

    .big_ad_image {
      width: 100%;
      max-width: 100%;
      border-radius: 10px;
    }

    .sidebar_ad_image {
      width: 100%;
      max-width: 100%;
      border-radius: 10px;
      margin-bottom: 30px;
    }

    /* Sidebar Web Development Section */
    .sidebar-marketing-section {
      width: 100%;
      margin-bottom: 30px;
    }

    .sidebar-marketing-section .marketing-card {
      background: linear-gradient(145deg, #2563eb 0%, #1e40af 100%);
      border-radius: 24px;
      padding: 40px 32px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(37, 99, 235, 0.3);
    }

    .sidebar-marketing-section .marketing-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      animation: pulseEffect 8s ease-in-out infinite;
    }

    @keyframes pulseEffect {

      0%,
      100% {
        transform: scale(1);
        opacity: 0.5;
      }

      50% {
        transform: scale(1.1);
        opacity: 0.8;
      }
    }

    .sidebar-marketing-section .marketing-card .card-content {
      position: relative;
      z-index: 2;
    }

    .sidebar-marketing-section .marketing-card .card-content .icon-container {
      width: 64px;
      height: 64px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 24px;
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255, 255, 255, 0.25);
    }

    .sidebar-marketing-section .marketing-card .card-content .icon-container svg {
      width: 36px;
      height: 36px;
    }

    .sidebar-marketing-section .marketing-card .card-content .main-heading {
      color: white;
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 12px;
      line-height: 1.3;
    }

    .sidebar-marketing-section .marketing-card .card-content .description {
      color: rgba(255, 255, 255, 0.9);
      font-size: 15px;
      line-height: 1.6;
      margin-bottom: 28px;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper {
      margin-bottom: 28px;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature {
      display: flex;
      align-items: center;
      color: white;
      font-size: 14px;
      margin-bottom: 12px;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature:last-child {
      margin-bottom: 0;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature .check-icon {
      width: 20px;
      height: 20px;
      margin-right: 10px;
      flex-shrink: 0;
    }

    .sidebar-marketing-section .marketing-card .card-content .primary-button {
      background: white;
      color: #2563eb;
      border: none;
      padding: 16px 32px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 99px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: block;
      text-align: center;
      width: 100%;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .sidebar-marketing-section .marketing-card .card-content .primary-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
      background: #f8fafc;
    }

    .sidebar-marketing-section .marketing-card .card-content .primary-button:active {
      transform: translateY(0);
    }



    @media (max-width: 480px) {
      .sidebar-marketing-section .marketing-card {
        padding: 32px 24px;
      }

      .sidebar-marketing-section .marketing-card .card-content .main-heading {
        font-size: 22px;
      }

      .sidebar-marketing-section .marketing-card .card-content .description {
        font-size: 14px;
      }
    }

    /* Sidebar Web Development Section */

    /* How works */
    .how-wrks {
      position: relative;
      z-index: 1;
    }

    .how-wrks .home-tit {
      margin-bottom: 40px;
    }

    .how-wrks-inn .col-lg-3 div {
      text-align: center;
      border: 1px solid #f1f1f1;
      border-radius: 5px;
      padding: 30px;
      position: relative;
      transition: all .5s ease;
      background: #fff;
    }

    .how-wrks-inn .col-lg-3 div:hover {

      transform: translateY(-8px);
      box-shadow: 0 19px 46px -39px #25272a;
    }

    .how-wrks-inn .col-lg-3 div img {
      width: 72px;
      margin-top: 25px;
    }

    .how-wrks-inn .col-lg-3 div h4 {
      font-weight: 600;
      font-size: 18px;
      padding: 20px 0 2px;
    }

    .how-wrks-inn .col-lg-3 div p {
      margin: 0;
      font-size: 13px;
      color: #636363;
    }

    /* How works */

    /*============================= Phone Responsive Global ==========================*/
    @media (max-width: 768px) {
      .pop-menu::after {
        display: none;
      }

      .dir-home-nav-bot {
        padding-bottom: 75px !important;
      }

      .mobmenu-show .mv-cate {
        display: none !important;
      }

      .new_style .slide-content {
        transform: translateY(10%) !important;
      }

      . {
        display: none;
      }

      .btn-get-started {
        padding: 13px 18px;
        margin: 0 0 !important;
        font-size: 16px !important;
      }
    }

    /*============================= Phone Responsive Global ==========================*/
  </style>

</head>

<body class="p411-home">

  <div class="full_wrapper">
    <!-- START HEADER -->
    <section class="fhns">
      <div class="str ind2-home">
        <div class="hom-head">
          <div class="hom-top" style="position: absolute !important;">
            <div class="container">
              <div class="row">
                <div class="hom-nav ">
                  <!--MOBILE MENU-->
                  <a href="index" class="top-log">
                    <img src="https://i.ibb.co/93BYZ4DZ/pp.png" alt="" loading="lazy"
                      class="ic-logo">
                  </a>
                  <div class="menu">
                    <h4>Explore</h4>
                  </div>
                  <div class="pop-menu">
                    <div class="container">
                      <div class="row">
                        <i class="material-icons clopme">close</i>
                        <div class="pmenu-spri">
                          <ul>
                            <li><a href="all-category" class="act"><img
                                  src="<?= base_url() ?>assets/images/icon/shop.png" loading="lazy">All
                                Services
                              </a></li>

                            <li><a href="news"><img src="<?= base_url() ?>assets/images/icon/news.png"
                                  loading="lazy">News & Magazines </a></li>
                            <li><a href="event"><img src="<?= base_url() ?>assets/images/icon/calendar.png"
                                  loading="lazy">Events </a></li>
                            <li><a href="all-products"><img src="<?= base_url() ?>assets/images/icon/cart.png"
                                  loading="lazy">Products </a></li>

                            <li><a href="all-blog"><img src="<?= base_url() ?>assets/images/icon/blog1.png"
                                  loading="lazy">Blogs </a></li>

                          </ul>
                        </div>
                        <div class="pmenu-cat">
                          <h4>All Categories</h4>

                          <ul id="pg-resu">
                            <?php
                            // Professional way: Use data passed from controller
                            // DEBUG: Print the $header_categories array for troubleshooting



                            if (isset($header_categories) && !empty($header_categories)) {
                              // Display categories from controller (up to 50)
                              foreach ($header_categories as $category) {
                                // Generate category URL
                                // Shared slug rule — this used to map "&" to "and"
                                // while the directory dropped it, so those
                                // categories opened an empty page from here.
                                echo '<li><a target="_blank" href="' . htmlspecialchars($home_cat_url($category)) . '">' . htmlspecialchars($category->category_name) . '</a></li>';
                              }
                            } else {
                              // Fallback if no data from controller
                              echo '<li><a href="' . base_url('user/all_listing') . '">All Businesses - Please check controller data</a></li>';
                            }
                            ?>
                          </ul>

                          <!-- Explore All Categories Button - Bottom Right -->
                          <div style="text-align: right; margin-top: 20px; padding-top: 15px;">
                            <a href="all-category" class="waves-effect waves-light btn" style="background: #7bb73f; color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; display: inline-block; font-size: 13px;">
                              <i class="material-icons" style="vertical-align: middle; margin-right: 3px; font-size: 16px;">explore</i>
                              Explore All
                            </a>
                          </div>
                        </div>
                        <div class="dir-home-nav-bot">
                          <ul>
                            <li>A few reasons you’ll love Online Business Directory
                              <span>Call
                                us on: 281-482-2031</span>
                            </li>
                            <li><a href="post-your-ads"
                                class="waves-effect waves-light btn-large"><i
                                  class="material-icons">font_download</i> Advertise
                                with
                                us </a>
                            </li>
                            <li><a href="pricing-plan"
                                class="waves-effect waves-light btn-large"> <i
                                  class="material-icons">store</i> Add your business
                              </a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--END MOBILE MENU-->
                  <div class="top-ser">
                    <form name="filter_form" id="filter_form" class="filter_form" method="GET" action="search">
                      <ul>
                        <li class="sr-sea">
                          <input type="text" name="q" autocomplete="off" id="top-select-search"
                            placeholder="What are you looking for?">
                          <ul id="tser-res1" class="tser-res tser-res2">
                            <!-- Example search results, can be removed or replaced -->
                          </ul>
                        </li>
                        <li class="sbtn">
                          <button type="submit" class="btn btn-success"
                            id="top_filter_submit"><i
                              class="material-icons">search</i></button>
                        </li>
                      </ul>
                    </form>
                  </div>
                  <?php
                  // Check if user is logged in
                  $is_logged_in = false;
                  $user_name = 'User';

                  // Check for session data
                  if (isset($_SESSION['logged_user']) && !empty($_SESSION['logged_user'])) {
                    $is_logged_in = true;
                    $session_data = $_SESSION['logged_user'];
                    if (isset($session_data['name']) && !empty($session_data['name'])) {
                      $user_name = $session_data['name'];
                    }
                  }
                  ?>

                  <?php if (!$is_logged_in): ?>
                    <!-- Show login/register buttons for guests -->
                    <ul class="bl">
                      <li>
                        <a href="pricing-plan">Add business</a>
                      </li>
                      <li>
                        <a href="login">Sign in</a>
                      </li>
                      <li>
                        <a href="login?login=register">Create an account</a>
                      </li>
                    </ul>
                  <?php else: ?>
                    <!-- Show dashboard button for logged in users -->
                    <ul class="bl">
                      <li>
                        <a href="pricing-details">Add business</a>
                      </li>
                      <li>
                        <a href="dashboard" style="background: #21d78d; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none;">Dashboard</a>
                      </li>
                    </ul>
                  <?php endif; ?>
                  <!--MOBILE MENU-->
                  <div class="mob-menu">
                    <div class="mob-me-ic"><i class="material-icons">menu</i></div>
                    <div class="mob-me-all">
                      <div class="mob-me-clo"><i class="material-icons">close</i></div>
                      <div class="mv-bus">
                        <h4></h4>
                        <ul>
                          <li>
                            <a href="pricing-details">Add business</a>
                          </li>
                          <?php if (!$is_logged_in): ?>
                            <!-- Mobile menu for guests -->
                            <li>
                              <a href="login">Sign in</a>
                            </li>
                            <li>
                              <a href="login?login=register">Create an account</a>
                            </li>
                          <?php else: ?>
                            <!-- Mobile menu for logged in users -->
                            <li>
                              <a href="dashboard" style="background: #21d78d; color: white; padding: 8px 16px; border-radius: 5px;">Dashboard</a>
                            </li>
                            <li>
                              <span style="color: #666; font-size: 14px; padding: 8px 16px;">Welcome, <?= htmlspecialchars($user_name) ?>!</span>
                            </li>
                          <?php endif; ?>
                        </ul>
                      </div>
                      <div class="mv-cate">
                        <h4>All Categories</h4>
                        <ul>
                          <li>
                            <a href="all-listing">Spa and Facial</a>
                          </li>
                          <li>
                            <a href="all-listing">Wedding halls</a>
                          </li>
                          <li>
                            <a href="all-listing">Automobiles</a>
                          </li>
                          <li>
                            <a href="all-listing">Restaurants</a>
                          </li>
                          <li>
                            <a href="all-listing">Technology</a>
                          </li>
                          <li>
                            <a href="all-listing">Pet shop</a>
                          </li>
                          <li>
                            <a href="all-listing">Real Estate</a>
                          </li>
                          <li>
                            <a href="all-listing">Sports</a>
                          </li>
                          <li>
                            <a href="all-listing">Hospitals</a>
                          </li>
                          <li>
                            <a href="all-listing">Education</a>
                          </li>
                          <li>
                            <a href="all-listing">Transportation</a>
                          </li>
                          <li>
                            <a href="all-listing">Electricals</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <!--END MOBILE MENU-->
                </div>
              </div>
            </div>
          </div>
          <!-- Banner Section -->
          <section class="banner-section new_banner">
            <div class="container px-10">
              <div class="banner-content">
                <h1 class="banner-title">Discover Pearland's Best Businesses</h1>
                <p class="banner-subtitle">Your complete guide to local services, restaurants, shops, and professionals in Pearland, Texas</p>
                <form method="GET" action="search" class="search-box">
                  <div class="position-relative">
                    <input type="text" name="q" class="form-control search-input"
                      placeholder="Search for businesses, services, or categories..." required>
                    <button type="submit" class="search-btn">
                      <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"
                        style="vertical-align:middle;">
                        <path
                          d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242 1.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10z" />
                      </svg>
                    </button>
                  </div>
                </form>
                <p class="mt-1">Popular: Restaurants, Plumbers, Hair Salons, Dentists, Auto Repair, <a style="color: white;" href="https://www.spacecentersystems.com/website-development/" target="_blank">Website Design</a>, <a style="color: white;" href="https://www.spacecentersystems.com/website-marketing-seo-ppc-social-media/" target="_blank">Marketing Services</a></p>
              </div>
            </div>
          </section>
        </div>
      </div>
    </section>
    <!-- END HEADER -->

    <?php
    /* ==================================================================
       HOMEPAGE BODY
       ------------------------------------------------------------------
       Reorganised from the original. The header above and the footer
       below are untouched; so are the theme's card classes
       (.lst-com-box, .blog-box, .video-box, .widget, .section-title).
       Only genuinely new pieces carry `p411-` classes, styled in
       assets/css/home-sections.css, which no other view loads.

       Data comes from User::index(). Every block below hides itself or
       shows a message when its query returns nothing.
       ================================================================== */

    $p411_placeholder = base_url('assets/images/placeholder-listing.svg');

    // Trim a description without splitting a word or a multibyte character.
    $p411_excerpt = function ($text, $len = 105) {
      $text = trim(strip_tags(html_entity_decode((string) $text)));
      if ($text === '') return '';
      return function_exists('mb_strimwidth')
        ? mb_strimwidth($text, 0, $len, '…')
        : (strlen($text) > $len ? substr($text, 0, $len - 1) . '…' : $text);
    };

    // Icons, not emoji — inline SVG paths keyed by a word in the category name.
    $p411_icons = array(
      'home'         => '<path d="M3 21h18M5 21V9l7-5 7 5v12M10 21v-6h4v6"/>',
      'health'       => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
      'fitness'      => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
      'medical'      => '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="M12 8v8M8 12h8"/>',
      'dental'       => '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="M12 8v8M8 12h8"/>',
      'auto'         => '<path d="M5 17a2 2 0 1 0 4 0 2 2 0 1 0-4 0M15 17a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M3 17V10h12l4 4v3"/>',
      'real'         => '<path d="M3 21h8V3H3zM11 21h10v-9H11z"/><path d="M6 7h2M6 11h2M6 15h2M15 16h2"/>',
      'food'         => '<path d="M7 3v8a3 3 0 0 0 6 0V3M10 11v10"/><path d="M17 3c-1.5 2-2 4-2 6s.5 3 2 3v9"/>',
      'restaurant'   => '<path d="M7 3v8a3 3 0 0 0 6 0V3M10 11v10"/><path d="M17 3c-1.5 2-2 4-2 6s.5 3 2 3v9"/>',
      'beauty'       => '<path d="M8 3v6a4 4 0 0 0 8 0V3M12 13v8"/>',
      'professional' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
      'business'     => '<path d="M5 18h14M6 18V8h12v10"/><path d="M9 8V5h6v3"/>',
      'financial'    => '<path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
      'insurance'    => '<path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
      'legal'        => '<path d="M12 3v18M7 21h10M5 8h14M8 8l-3 6h6zM16 8l-3 6h6z"/>',
      'attorney'     => '<path d="M12 3v18M7 21h10M5 8h14M8 8l-3 6h6zM16 8l-3 6h6z"/>',
      'education'    => '<path d="m3 9 9-5 9 5-9 5z"/><path d="M7 11v5c0 1.5 2.2 3 5 3s5-1.5 5-3v-5"/>',
      'pet'          => '<circle cx="8" cy="7" r="2"/><circle cx="16" cy="7" r="2"/><circle cx="5" cy="13" r="2"/><circle cx="19" cy="13" r="2"/><path d="M12 12c-3 0-5 2.5-5 5a3 3 0 0 0 3 3h4a3 3 0 0 0 3-3c0-2.5-2-5-5-5z"/>',
      'pest'         => '<ellipse cx="12" cy="12" rx="4" ry="7"/><path d="M12 4v16M8 8l-4-2M16 8l4-2M8 14l-4 2M16 14l4 2"/>',
      'art'          => '<path d="M4 6h16v12H4z"/><path d="m9 10 6 2-6 2z"/>',
      'entertain'    => '<path d="M4 6h16v12H4z"/><path d="m9 10 6 2-6 2z"/>',
      'construct'    => '<path d="M3 21h18M5 21V11h14v10"/><path d="m4 11 8-7 8 7"/>',
      'clean'        => '<path d="M7 21h10l-1-9H8z"/><path d="M12 12V3M9 6h6"/>',
      'tech'         => '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
      '_default'     => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/>',
    );
    $p411_icon = function ($name) use ($p411_icons) {
      $n = strtolower((string) $name);
      foreach ($p411_icons as $k => $path) {
        if ($k !== '_default' && strpos($n, $k) !== false) return $path;
      }
      return $p411_icons['_default'];
    };
    ?>

    <!-- ============================================ TOP BANNER AD == -->
    <div class="container px-10" style="padding-top:22px;">
      <?php $slot = 'top-banner-site-wide';
      include APPPATH . 'views/partials/ad_slot.php'; ?>
    </div>

    <!-- =========================================== CATEGORY STRIP == -->
    <?php if (!empty($home_categories)): ?>
      <?php // Deliberately NOT .category-tabs — the theme hides that class on
            // mobile with !important (it was built for the old fake topic tabs).
            // This strip is a responsive grid and should stay visible. ?>
      <section class="p411-catstrip" aria-label="Browse by category">
        <div class="container px-10">
          <div class="p411-cats">
            <?php foreach (array_slice($home_categories, 0, 8) as $cat): ?>
              <a href="<?= e_url($home_cat_url($cat)) ?>">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $p411_icon($cat->category_name) ?></svg>
                <b><?= e($cat->category_name) ?></b>
                <?php if (isset($cat->listing_count)): ?>
                  <span><?= (int) $cat->listing_count ?> listing<?= ((int) $cat->listing_count === 1 ? '' : 's') ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <!-- ============================================== MAIN CONTENT == -->
    <section class="main-content new_style py-5">
      <div class="container px-10">
        <div class="main-content-flex">

          <!-- ======================================== MAIN COLUMN == -->
          <div class="main-area">

            <!-- Business Hotspot — a slim logo rail, deliberately not
                 another card row. Sold as plans.slug = business-hotspot. -->
            <?php if (!empty($home_hotspot)): ?>
              <section class="p411-sec">
                <div class="p411-hotspot">
                  <div class="p411-hotspot__head">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 2s5 4.5 5 9a5 5 0 0 1-10 0c0-4.5 5-9 5-9z"/><path d="M12 22a7 7 0 0 0 7-7"/></svg>
                    <h2>Business Hotspot</h2>
                    <a class="p411-more" href="<?= base_url('all_listing') ?>">All businesses &rarr;</a>
                  </div>
                  <div class="p411-hotspot__rail">
                    <?php foreach ($home_hotspot as $spot): ?>
                      <a class="p411-hotspot__item" href="<?= base_url('listing-details/' . rawurlencode($spot->listing_slug)) ?>">
                        <img src="<?= !empty($spot->listing_image) ? base_url($spot->listing_image) : e_attr($p411_placeholder) ?>"
                             alt="<?= e_attr($spot->listing_name) ?>" loading="lazy" decoding="async">
                        <b><?= e($spot->listing_name) ?></b>
                        <?php if (!empty($spot->category_name)): ?><span><?= e($spot->category_name) ?></span><?php endif; ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              </section>
            <?php endif; ?>

            <!-- Business of the Week — one wide spotlight. Being the only
                 one is what the customer is paying for. -->
            <?php if (!empty($home_week)): ?>
              <section class="p411-sec">
                <div class="p411-week">
                  <div class="p411-week__media">
                    <img src="<?= !empty($home_week->listing_image) ? base_url($home_week->listing_image) : e_attr($p411_placeholder) ?>"
                         alt="<?= e_attr($home_week->listing_name) ?>" loading="lazy" decoding="async">
                    <span class="p411-week__flag">Business of the Week</span>
                  </div>
                  <div class="p411-week__body">
                    <?php if (!empty($home_week->category_name)): ?>
                      <span class="p411-week__cat"><?= e($home_week->category_name) ?></span>
                    <?php endif; ?>
                    <h3><?= e($home_week->listing_name) ?></h3>
                    <?php if (!empty($home_week->listing_description)): ?>
                      <p><?= e($p411_excerpt($home_week->listing_description, 220)) ?></p>
                    <?php endif; ?>
                    <div class="p411-week__foot">
                      <a class="p411-btn" href="<?= base_url('listing-details/' . rawurlencode($home_week->listing_slug)) ?>">Visit this business</a>
                      <?php if (!empty($home_week->week_until) && strtotime($home_week->week_until)): ?>
                        <span class="p411-week__until">Featured until <?= e(date('M j', strtotime($home_week->week_until))) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </section>
            <?php endif; ?>

            <!-- Featured Businesses -->
            <section class="p411-sec">
              <div class="p411-sec__head">
                <h2>Featured Businesses</h2>
                <a class="p411-more" href="<?= base_url('all_listing') ?>">See all businesses &rarr;</a>
              </div>

              <?php if (!empty($featured_listings)): ?>
                <div class="p411-grid3">
                  <?php foreach (array_slice($featured_listings, 0, 3) as $item): ?>
                    <?php $url = base_url('listing-details/' . rawurlencode($item->listing_slug)); ?>
                    <div class="lst-com-box">
                      <div class="list-com-img">
                        <a href="<?= $url ?>">
                          <?php if (!empty($item->listing_image)): ?>
                            <?= responsive_img($item->listing_image, $item->listing_name) ?>
                          <?php else: ?>
                            <img src="<?= e_attr($p411_placeholder) ?>" alt="<?= e_attr($item->listing_name) ?>" loading="lazy" decoding="async">
                          <?php endif; ?>
                        </a>
                        <?php if (!empty($item->category_name)): ?>
                          <span class="list-com-cat"><?= e($item->category_name) ?></span>
                        <?php endif; ?>
                      </div>
                      <div class="list-com-con p-3">
                        <div>
                          <h3 class="list-com-title" style="font-size:1rem;"><a href="<?= $url ?>"><?= e($item->listing_name) ?></a></h3>
                          <?php if (!empty($item->listing_description)): ?>
                            <p class="list-com-desc" style="font-size:0.92rem;"><?= e($p411_excerpt($item->listing_description, 100)) ?></p>
                          <?php endif; ?>
                        </div>
                        <a href="<?= $url ?>" class="list-enq-btn">Read More &rarr;</a>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="p411-empty">
                  <h3>No featured businesses yet</h3>
                  <p>Featured placement puts a business at the top of the homepage. Nothing is currently marked as featured or spotlight.</p>
                  <a class="p411-btn" href="<?= base_url('pricing-plan') ?>">See featured packages</a>
                </div>
              <?php endif; ?>
            </section>

            <!-- Newest businesses -->
            <section class="p411-sec">
              <div class="p411-sec__head">
                <h2>Explore Pearland Businesses</h2>
                <a class="p411-more" href="<?= base_url('all_listing') ?>">View all listings &rarr;</a>
              </div>

              <?php if (!empty($latest_listings)): ?>
                <div id="business-list">
                  <?php foreach ($latest_listings as $row): ?>
                    <?php
                    $url = base_url('listing-details/' . rawurlencode($row->listing_slug));
                    $is_new = !empty($row->created_at) && strtotime($row->created_at) > strtotime('-7 days');
                    ?>
                    <div class="fade-in business-item lst-com-box">
                      <div class="list-com-img">
                        <a href="<?= $url ?>">
                          <?php if (!empty($row->listing_image)): ?>
                            <?= responsive_img($row->listing_image, $row->listing_name) ?>
                          <?php else: ?>
                            <img src="<?= e_attr($p411_placeholder) ?>" alt="<?= e_attr($row->listing_name) ?>" loading="lazy" decoding="async">
                          <?php endif; ?>
                        </a>
                        <?php if (!empty($row->category_name)): ?>
                          <span class="list-com-cat"><?= e($row->category_name) ?></span>
                        <?php endif; ?>
                        <?php if ($is_new): ?>
                          <span class="new-badge" style="position:absolute;top:10px;left:10px;background:#4E8A28;color:#fff;padding:3px 8px;border-radius:12px;font-size:0.7rem;font-weight:600;">NEW</span>
                        <?php endif; ?>
                      </div>
                      <div class="list-com-con p-3">
                        <div>
                          <h3 class="list-com-title" style="font-size:1rem;"><a href="<?= $url ?>"><?= e($row->listing_name) ?></a></h3>
                          <?php if (!empty($row->listing_description)): ?>
                            <p class="list-com-desc" style="font-size:0.92rem;"><?= e($p411_excerpt($row->listing_description, 100)) ?></p>
                          <?php endif; ?>
                        </div>
                        <a href="<?= $url ?>" class="list-enq-btn">Read More &rarr;</a>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="p411-empty">
                  <h3>No businesses listed yet</h3>
                  <p>Once businesses are added to the directory they&rsquo;ll appear here, newest first.</p>
                  <a class="p411-btn" href="<?= base_url('register') ?>">Add the first business</a>
                </div>
              <?php endif; ?>
            </section>

            <!-- ==================================== MIDDLE BANNER AD == -->
            <div class="p411-adrow">
              <?php $slot = 'middle-banner-category';
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <!-- What's On — deals and events share one band -->
            <?php if (!empty($home_deals) || (!empty($show_events) && !empty($home_events))): ?>
              <section class="p411-sec">
                <div class="p411-sec__head">
                  <h2>What&rsquo;s On in Pearland</h2>
                </div>
                <div class="p411-split">

                  <div>
                    <h3>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M20 12V7H4v5a2 2 0 0 1 0 4v1h16v-1a2 2 0 0 1 0-4z"/><path d="M12 7v10"/></svg>
                      Deals &amp; Coupons
                      <a class="p411-more" href="<?= base_url('coupons') ?>">All &rarr;</a>
                    </h3>
                    <?php if (!empty($home_deals)): ?>
                      <div class="p411-stack">
                        <?php foreach (array_slice($home_deals, 0, 3) as $deal): ?>
                          <a class="p411-deal" href="<?= base_url('coupons/' . (int) $deal->coupon_id) ?>">
                            <?php if (!empty($deal->coupon_code)): ?>
                              <span class="p411-deal__off"><?= e($deal->coupon_code) ?></span>
                            <?php endif; ?>
                            <span class="p411-deal__name"><?= e($deal->coupon_name) ?></span>
                            <span class="p411-deal__meta">
                              <?= e(!empty($deal->listing_name) ? $deal->listing_name : (!empty($deal->owner_name) ? $deal->owner_name : 'Pearland411')) ?><?php
                              if (!empty($deal->coupon_end_date) && strtotime($deal->coupon_end_date)):
                                ?> &middot; ends <?= e(date('M j', strtotime($deal->coupon_end_date))) ?><?php endif; ?>
                            </span>
                          </a>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <div class="p411-empty">
                        <h3>No live coupons right now</h3>
                        <p>Deals posted by Pearland businesses show here while they&rsquo;re running.</p>
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php if (!empty($show_events)): ?>
                    <div>
                      <h3>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/></svg>
                        Upcoming Events
                        <a class="p411-more" href="<?= base_url('event') ?>">All &rarr;</a>
                      </h3>
                      <?php if (!empty($home_events)): ?>
                        <div class="p411-stack">
                          <?php foreach (array_slice($home_events, 0, 3) as $event): ?>
                            <?php
                            $ets  = !empty($event->event_start_date) ? strtotime($event->event_start_date) : false;
                            $eurl = !empty($event->event_slug)
                              ? base_url('event-details/' . rawurlencode($event->event_slug))
                              : base_url('event');
                            ?>
                            <a class="p411-event" href="<?= $eurl ?>">
                              <?php if ($ets): ?>
                                <span class="p411-event__date">
                                  <i><?= e(date('M', $ets)) ?></i>
                                  <b><?= e(date('j', $ets)) ?></b>
                                </span>
                              <?php endif; ?>
                              <span class="p411-event__body">
                                <b><?= e($event->event_name) ?></b>
                                <?php if (!empty($event->event_address)): ?>
                                  <span><?= e($event->event_address) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($event->event_time)): ?>
                                  <span><?= e($event->event_time) ?></span>
                                <?php endif; ?>
                              </span>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <div class="p411-empty">
                          <h3>No upcoming events</h3>
                          <p>Events scheduled for today or later are listed here.</p>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                </div>
              </section>
            <?php endif; ?>

            <!-- Community — videos and news share one band -->
            <?php if ((!empty($show_videos) && !empty($latest_videos)) || (!empty($show_news) && !empty($latest_news))): ?>
              <section class="p411-sec">
                <div class="p411-sec__head">
                  <h2>Community Highlights</h2>
                </div>
                <div class="p411-split">

                  <?php if (!empty($show_videos)): ?>
                    <div>
                      <h3>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3z"/></svg>
                        Videos
                        <a class="p411-more" href="<?= base_url('videos') ?>">All &rarr;</a>
                      </h3>
                      <?php if (!empty($latest_videos)): ?>
                        <div class="p411-stack">
                          <?php foreach (array_slice($latest_videos, 0, 3) as $video): ?>
                            <a class="p411-media" href="<?= base_url('video-details/' . rawurlencode($video->video_slug)) ?>">
                              <span class="p411-media__thumb">
                                <img src="<?= !empty($video->video_image) ? base_url($video->video_image) : e_attr($p411_placeholder) ?>"
                                     alt="<?= e_attr($video->video_title) ?>" loading="lazy" decoding="async">
                                <span class="p411-media__play">
                                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                              </span>
                              <span class="p411-media__body">
                                <b><?= e($video->video_title) ?></b>
                                <?php if (!empty($video->video_description)): ?>
                                  <span><?= e($p411_excerpt($video->video_description, 78)) ?></span>
                                <?php endif; ?>
                              </span>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <div class="p411-empty"><h3>No videos yet</h3><p>Community videos appear here once they&rsquo;re added.</p></div>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($show_news)): ?>
                    <div>
                      <h3>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 5h13v14H4z"/><path d="M17 9h3v8a2 2 0 0 1-4 0"/><path d="M7 9h7M7 13h7"/></svg>
                        Local News
                        <a class="p411-more" href="<?= base_url('all-news') ?>">All &rarr;</a>
                      </h3>
                      <?php if (!empty($latest_news)): ?>
                        <div class="p411-stack">
                          <?php foreach (array_slice($latest_news, 0, 3) as $news): ?>
                            <a class="p411-media" href="<?= base_url('news-details/' . (int) $news->news_id) ?>">
                              <span class="p411-media__thumb">
                                <img src="<?= !empty($news->news_image) ? base_url($news->news_image) : e_attr($p411_placeholder) ?>"
                                     alt="<?= e_attr($news->news_title) ?>" loading="lazy" decoding="async">
                              </span>
                              <span class="p411-media__body">
                                <b><?= e($news->news_title) ?></b>
                                <?php if (!empty($news->news_description)): ?>
                                  <span><?= e($p411_excerpt($news->news_description, 78)) ?></span>
                                <?php endif; ?>
                              </span>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <div class="p411-empty"><h3>No news yet</h3><p>Local announcements appear here once they&rsquo;re posted.</p></div>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                </div>
              </section>
            <?php endif; ?>

          </div><!-- /.main-area -->

          <!-- ============================================= SIDEBAR == -->
          <div class="sidebar">

            <div class="p411-sidebar-ad">
              <?php $slot = 'right-side-banner-1';
              // Same pairing as the directory sidebar: the web-services card
              // directly below already carries that pitch, so an unsold zone
              // sells the space itself rather than repeating it.
              $slot_creative = 'house-ad';
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <?php // Static house promo — never sold, never rotated, so the
                  // position is always filled. Styles: promo-cards.css. ?>
            <?php include APPPATH . 'views/partials/service_promo.php'; ?>

            <!-- Weather (live, via the Weather::proxy endpoint) -->
            <div class="widget">
              <h3 class="widget-title">Pearland Weather</h3>
              <div id="weather-forecast" style="margin-bottom:10px;"></div>
              <div id="weather-current"></div>
            </div>

            <!-- Business categories -->
            <div class="widget categories">
              <h3 class="widget-title">Business Categories</h3>
              <?php if (!empty($home_categories)): ?>
                <ul class="category-list">
                  <?php foreach (array_slice($home_categories, 0, 10) as $cat): ?>
                    <li>
                      <a href="<?= e_url($home_cat_url($cat)) ?>"><?= e($cat->category_name) ?></a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <ul class="category-list">
                  <li><a href="<?= base_url('all-category') ?>">Browse all categories</a></li>
                </ul>
              <?php endif; ?>
            </div>

            <div class="p411-sidebar-ad">
              <?php $slot = 'right-side-banner-2';
              // Unsold, this position becomes the ad/banner design card
              // rather than a flat picture of an advertisement. A real
              // booking still wins the slot.
              $slot_fallback_partial = 'banner_design_promo.php';
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <!-- Latest deals — real coupons, replacing the invented list -->
            <?php if (!empty($home_deals)): ?>
              <div class="widget">
                <h3 class="widget-title">Latest Deals</h3>
                <div class="p411-stack">
                  <?php foreach (array_slice($home_deals, 0, 3) as $deal): ?>
                    <a class="p411-deal" href="<?= base_url('coupons/' . (int) $deal->coupon_id) ?>">
                      <?php if (!empty($deal->coupon_code)): ?>
                        <span class="p411-deal__off"><?= e($deal->coupon_code) ?></span>
                      <?php endif; ?>
                      <span class="p411-deal__name"><?= e($deal->coupon_name) ?></span>
                      <span class="p411-deal__meta"><?= e(!empty($deal->listing_name) ? $deal->listing_name : 'Pearland411') ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Newsletter -->
            <div class="widget">
              <h3 class="widget-title">Subscribe for Local Deals</h3>
              <p style="font-size:0.88rem;color:#5D6469;margin-bottom:10px;">Get the best local deals, new openings and events in Pearland &mdash; straight to your inbox.</p>
              <form id="newsletterForm" class="newsletter-form">
                <label class="sr-only" for="newsletterEmail">Email address</label>
                <input type="email" id="newsletterEmail" name="email" class="newsletter-input" placeholder="Your email address" required>
                <button type="submit" id="newsletterSubmit" class="newsletter-btn">Join</button>
              </form>
              <div id="newsletterMessage"></div>
            </div>

            <div class="p411-sidebar-ad">
              <?php $slot = 'right-side-banner-3';
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <!-- 295x600. Held back until sold: an unsold half-page house ad
                 is more harm than the zone is worth. -->
            <div class="p411-sidebar-ad">
              <?php $slot = 'right-panel-skyscraper';
              $slot_fallback = false;
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

          </div><!-- /.sidebar -->

        </div>
      </div>
    </section>

    <!-- ========================================= BOTTOM BANNER AD == -->
    <div class="container px-10">
      <?php $slot = 'bottom-banner-site-wide';
      include APPPATH . 'views/partials/ad_slot.php'; ?>
    </div>

    <!-- ============================================== OWNER BAND == -->
    <!-- "Grow Your Business Online" and "How It Works" merged: three
         separate sales blocks at the foot of the page was more than the
         message needed. -->
    <section class="p411-owner">
      <div class="container px-10">
        <div class="p411-owner__head">
          <h2>Grow Your Business in Pearland</h2>
          <p>List free in the directory, then add a website, local SEO and homepage placement when you&rsquo;re ready.</p>
        </div>

        <div class="p411-feat4">
          <div>
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M10 21v-6h4v6"/></svg></i>
            <h3>List your business</h3>
            <p>A free listing puts you in search and on your category page.</p>
          </div>
          <div>
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.6 5.6 6.4.8-4.6 4.3 1.1 6.3L12 17l-5.5 3 1.1-6.3L3 9.4l6.4-.8z"/></svg></i>
            <h3>Get seen on the homepage</h3>
            <p>Featured, Hotspot and Business of the Week placements, right here.</p>
          </div>
          <div>
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></i>
            <h3>Websites &amp; local SEO</h3>
            <p>Fast, mobile-first sites built for Pearland businesses.</p>
          </div>
          <div>
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
            <h3>Advertise on Pearland411</h3>
            <p>Banner positions across the site, from $20 a month.</p>
          </div>
        </div>

        <div class="p411-owner__cta">
          <a class="p411-btn" href="<?= base_url('register') ?>">Add my business &mdash; free</a>
          <a class="p411-btn p411-btn--ghost" href="<?= base_url('pricing-plan') ?>">See all packages</a>
        </div>
      </div>
    </section>
  </div>
  <!-- START FOOTER -->
  <section class="wed-hom-footer">
    <div class="container px-10">
      <div class="row foot-supp">
        <h2><span>Free support:</span> 281-482-2031 &nbsp;&nbsp;|&nbsp;&nbsp; <span>Email:</span>
          sales@pearland411.com</h2>
      </div>
      <div class="row wed-foot-link">
        <div class="col-md-4 foot-tc-mar-t-o">
          <h4>Top Category</h4>
          <ul>
            <?php
            // Display first 8 categories for Top Category column
            if (!empty($all_listing_categories)) {
              $top_categories = array_slice($all_listing_categories, 0, 8);
              foreach ($top_categories as $category) {
                echo '<li><a href="' . htmlspecialchars($home_cat_url($category)) . '">' . htmlspecialchars($category->category_name) . '</a></li>';
              }
            } else {
              // Fallback categories
              echo '<li><a href="all-listing">Technology</a></li>';
              echo '<li><a href="all-listing">Spa and Facial</a></li>';
              echo '<li><a href="all-listing">Real Estate</a></li>';
              echo '<li><a href="all-listing">Sports</a></li>';
              echo '<li><a href="all-listing">Education</a></li>';
              echo '<li><a href="all-listing">Electricals</a></li>';
              echo '<li><a href="all-listing">Automobiles</a></li>';
              echo '<li><a href="all-listing">Transportation</a></li>';
            }
            ?>
          </ul>
        </div>
        <div class="col-md-4">
          <h4>Trending Category</h4>
          <ul>
            <?php
            // Display next 6 categories for Trending Category column (avoiding duplicates)
            if (!empty($all_listing_categories) && count($all_listing_categories) > 8) {
              $trending_categories = array_slice($all_listing_categories, 8, 6);
              foreach ($trending_categories as $category) {
                echo '<li><a href="' . htmlspecialchars($home_cat_url($category)) . '">' . htmlspecialchars($category->category_name) . '</a></li>';
              }
            } else {
              // Fallback categories
              echo '<li><a href="all-listing">Hospitals</a></li>';
              echo '<li><a href="all-listing">Automobiles</a></li>';
              echo '<li><a href="all-listing">Real Estate</a></li>';
              echo '<li><a href="all-listing">Sports</a></li>';
              echo '<li><a href="all-listing">Education</a></li>';
              echo '<li><a href="all-listing">Electricals</a></li>';
            }
            ?>
          </ul>
        </div>
        <div class="col-md-4">
          <h4>HELP &amp; SUPPORT</h4>
          <ul>
            <li><a href="/about-us">About us</a>
            </li>
            <li><a href="/faq">FAQ</a>
            </li>
            <!-- <li><a href="feedback">Feedback</a>
						</li> -->
            <li><a href="contact-us">Contact us</a>
            </li>
            <li><a href="privacy-policy">Privacy Policy</a></li>
            <li><a href="terms-of-use">Terms of Use</a></li>
          </ul>
        </div>
      </div>

      <div class="row wed-foot-link-1">
        <div class="col-md-4">
          <h4>Get In Touch</h4>
          <p>Address: Pearland, Texas, USA</p>
          <p>Phone: <a href="tel:281-482-2031">281-482-2031</a></p>
          <p>Email: <a href="mailto:sales@pearland411.com">sales@pearland411.com</a></p>
        </div>

        <div class="col-md-4 fot-soc">
          <?php
          // Social icons render only when a URL is configured. Set
          // them via /admin/setting (preferred) — falls back to env
          // vars and finally to empty.
          $__home_socials = array(
              '1.png' => array('Facebook',    site_setting('social_facebook_url',  '')),
              '2.png' => array('Twitter / X', site_setting('social_twitter_url',   '')),
              '3.png' => array('Instagram',   site_setting('social_instagram_url', '')),
              '4.png' => array('LinkedIn',    site_setting('social_linkedin_url',  '')),
              '5.png' => array('YouTube',     site_setting('social_youtube_url',   '')),
          );
          $__home_socials_any = false;
          foreach ($__home_socials as $row) { if ($row[1] !== '') { $__home_socials_any = true; break; } }
          if ($__home_socials_any):
          ?>
          <h4>SOCIAL MEDIA</h4>
          <ul>
            <?php foreach ($__home_socials as $icon => $row): list($label, $url) = $row; if ($url === '') continue; ?>
              <li><a target="_blank" rel="noopener" href="<?= e_attr($url) ?>" aria-label="<?= e_attr($label) ?>"><img src="<?= base_url() ?>assets/images/social/<?= e_attr($icon) ?>" alt="<?= e_attr($label) ?>" loading="lazy"></a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; unset($__home_socials, $__home_socials_any); ?>
        </div>
      </div>

    </div>
  </section>
  <!-- END FOOTER -->

  <!-- START -->
  <section>
    <div class="cr">
      <div class="container">
        <div class="row">
          <p>Copyright © 2025-2035 Pearland411 Proudly
            powered by <a href="https://www.spacecentersystems.com/" target="_blank">Spacecentersystems</a></p>
        </div>
      </div>
    </div>
  </section>
  <!-- END -->

  <!-- START -->
  <div class="fqui-menu">
    <ul>
      <li><a href="/"><img src="<?= base_url() ?>assets/images/icon/home.png">Home</a></li>
      <li><span class="mob-sear"><img src="<?= base_url() ?>assets/images/icon/search1.png">Search</span></li>
      <li><a href="all-category" class="act"><img src="<?= base_url() ?>assets/images/icon/shop.png">Services</a></li>
<?php if (site_setting('service_events_enabled','1') !== '0'): ?><li><a href="event"><img src="<?= base_url() ?>assets/images/icon/calendar.png">Events</a></li><?php endif; ?>
      <?php if (site_setting('service_products_enabled','1') !== '0'): ?><li><a href="all-products"><img src="<?= base_url() ?>assets/images/icon/cart.png">Products</a></li><?php endif; ?>
      <?php if (site_setting('service_blog_enabled','1') !== '0'): ?><li><a href="all-blog"><img src="<?= base_url() ?>assets/images/icon/blog1.png">Blogs</a></li><?php endif; ?>
    </ul>
  </div>
  <!-- END -->

  <!-- Optional JavaScript -->
  <script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
  <script src="<?= base_url() ?>assets/js/popper.min.js"></script>
  <script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url() ?>assets/js/jquery-ui.js"></script>
  <script src="<?= base_url() ?>assets/js/select-opt.js"></script>
  <script src="<?= base_url() ?>assets/js/slick.js"></script>
  <script src="<?= base_url() ?>assets/js/custom.js"></script>
  <script src="<?= base_url() ?>assets/js/jquery.validate.min.js"></script>
  <script src="<?= base_url() ?>assets/js/custom_validation.js"></script>
  <!-- Scripts -->

  <!-- Weather Weekly Widget -->
  <script>
    /*
     * Weather Widget using Server-Side Proxy
     * Fixes CORS and geo-blocking issues for USA and international visitors
     * Shows current weather and 6-day forecast for Pearland, Texas
     */
    document.addEventListener('DOMContentLoaded', function() {
      const weatherCurrent = document.getElementById('weather-current');
      const weatherForecast = document.getElementById('weather-forecast');
      if (!weatherCurrent || !weatherForecast) return;

      // Weather code to icon/svg mapping
      function weatherIcon(code, size = 48) {
        if ([0, 1].includes(code)) {
          // Clear/Mainly clear
          return `<svg width="${size}" height="${size}" viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="14" fill="#ffe066" stroke="#f7b731" stroke-width="2"/></svg>`;
        }
        if ([2, 3].includes(code)) {
          // Partly cloudy/Overcast
          return `<svg width="${size}" height="${size}" viewBox="0 0 48 48" fill="none"><circle cx="18" cy="28" r="10" fill="#ffe066"/><ellipse cx="28" cy="28" rx="12" ry="8" fill="#dbeafe"/><ellipse cx="32" cy="32" rx="8" ry="6" fill="#b6d0f7"/></svg>`;
        }
        if ([45, 48, 51, 53, 55, 56, 57, 61, 63, 65, 80, 81, 82].includes(code)) {
          // Rainy
          return `<svg width="${size}" height="${size}" viewBox="0 0 48 48" fill="none"><ellipse cx="24" cy="28" rx="12" ry="8" fill="#b6d0f7"/><ellipse cx="28" cy="28" rx="8" ry="6" fill="#dbeafe"/><line x1="18" y1="38" x2="18" y2="44" stroke="#4fc3f7" stroke-width="2"/><line x1="24" y1="38" x2="24" y2="44" stroke="#4fc3f7" stroke-width="2"/><line x1="30" y1="38" x2="30" y2="44" stroke="#4fc3f7" stroke-width="2"/></svg>`;
        }
        if ([71, 73, 75, 77, 85, 86].includes(code)) {
          // Snow
          return `<svg width="${size}" height="${size}" viewBox="0 0 48 48" fill="none"><ellipse cx="24" cy="28" rx="12" ry="8" fill="#b6d0f7"/><ellipse cx="28" cy="28" rx="8" ry="6" fill="#dbeafe"/><text x="24" y="44" text-anchor="middle" font-size="18" fill="#90caf9">*</text></svg>`;
        }
        // Default: cloudy
        return `<svg width="${size}" height="${size}" viewBox="0 0 48 48" fill="none"><ellipse cx="24" cy="28" rx="12" ry="8" fill="#dbeafe"/><ellipse cx="28" cy="28" rx="8" ry="6" fill="#b6d0f7"/></svg>`;
      }

      // Weather descriptions
      const weatherDesc = {
        0: "Clear sky",
        1: "Mainly clear",
        2: "Partly cloudy",
        3: "Overcast",
        45: "Fog",
        48: "Depositing rime fog",
        51: "Light drizzle",
        53: "Drizzle",
        55: "Dense drizzle",
        56: "Freezing drizzle",
        57: "Freezing drizzle",
        61: "Slight rain",
        63: "Rain",
        65: "Heavy rain",
        71: "Slight snow",
        73: "Snow",
        75: "Heavy snow",
        77: "Snow grains",
        80: "Rain showers",
        81: "Rain showers",
        82: "Violent rain showers",
        85: "Snow showers",
        86: "Heavy snow showers"
      };

      // Server-side proxy (application/controllers/Weather.php) — avoids
      // browser CORS / regional blocks and lets us cache upstream.
      const proxyUrl = '<?= base_url('weather') ?>';

      // Show loading state
      weatherCurrent.innerHTML = '<div style="text-align:center;padding:20px;color:#666;"><i class="fa fa-spinner fa-spin" style="font-size:24px;"></i><div style="margin-top:10px;">Loading weather data...</div></div>';

      fetch(proxyUrl)
        .then(res => {
          if (!res.ok) {
            throw new Error(`HTTP ${res.status}: ${res.statusText}`);
          }
          return res.json();
        })
        .then(data => {
          // Check if API returned an error
          if (data.error) {
            throw new Error(data.message || 'Weather data unavailable');
          }
          return data;
        })
        .then(data => {
          // --- Current Weather ---
          if (!data.current || !data.daily) {
            throw new Error('Invalid weather data format');
          }
          const current = data.current;
          const daily = data.daily;

          const temp = Math.round(current.temperature_2m);
          const feelsLike = Math.round(current.apparent_temperature);
          const humidity = Math.round(current.relative_humidity_2m);
          const windSpeed = Math.round(current.wind_speed_10m);
          const windDir = current.wind_direction_10m;
          const weatherCode = current.weather_code;
          const desc = weatherDesc[weatherCode] || "Cloudy";

          // Get current time in Central timezone
          const now = new Date();
          const timeStr = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'America/Chicago'
          });

          // Wind direction
          const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
          const windDirection = directions[Math.round(windDir / 22.5) % 16];

          // Today's high/low
          const todayHigh = Math.round(daily.temperature_2m_max[0]);
          const todayLow = Math.round(daily.temperature_2m_min[0]);

          weatherCurrent.innerHTML = `
				<div class="weather-current-icon">${weatherIcon(weatherCode, 48)}</div>
				<div>
					<div style="font-size:0.93rem;color:#888;font-weight:500;margin-bottom:2px;">
						CURRENT WEATHER @ ${timeStr}
					</div>
					<div style="display:flex;align-items:baseline;gap:10px;">
						<span class="weather-current-temp">${temp}°</span>
						<span style="font-size:1.1rem;font-weight:600;color:#222;">F</span>
						<span class="weather-current-details">${desc}</span>
					</div>
					<div class="weather-current-meta">
						Feels like: ${feelsLike}° | Wind: ${windSpeed} mph ${windDirection} | Humidity: ${humidity}%
					</div>
					<div class="weather-current-meta">
						High: ${todayHigh}° | Low: ${todayLow}°
					</div>
				</div>
			`;

          // --- 6-Day Forecast ---
          let forecastHtml = '';
          for (let i = 0; i < 6; i++) {
            const day = new Date(daily.time[i]);
            const label = i === 0 ? "Today" : day.toLocaleDateString('en-US', {
              weekday: 'short',
              timeZone: 'America/Chicago'
            });
            const code = daily.weather_code[i];
            const max = Math.round(daily.temperature_2m_max[i]);
            const min = Math.round(daily.temperature_2m_min[i]);

            forecastHtml += `
					<div class="weather-forecast-day">
						<div class="weather-forecast-label">${label}</div>
						<div class="weather-forecast-icon">${weatherIcon(code, 28)}</div>
						<div class="weather-forecast-temp">${max}°/${min}°</div>
						<div class="weather-forecast-desc">${weatherDesc[code] || "Cloudy"}</div>
					</div>
				`;
          }
          weatherForecast.innerHTML = forecastHtml;
        })
        .catch(err => {
          console.error('Weather API Error:', err);
          console.error('Proxy URL:', proxyUrl);

          // More detailed error message
          let errorMsg = 'Weather service temporarily unavailable';
          if (err.message) {
            console.error('Error details:', err.message);
            errorMsg += ': ' + err.message;
          }

          weatherCurrent.innerHTML = `
						<div style="color:#dc3545;font-weight:600;padding:15px;background:#fff3cd;border-radius:8px;border-left:4px solid #dc3545;">
							<div style="margin-bottom:8px;">⚠️ ${errorMsg}</div>
							<div style="font-size:0.85rem;color:#856404;">
								Please check browser console for details or contact support.
							</div>
						</div>
					`;
          weatherForecast.innerHTML = '';
        });
    });
  </script>

  <!-- Search Functionality -->
  <script>
    $(document).ready(function() {
      // Search suggestions for top search
      let searchTimeout;
      $('#top-select-search').on('input', function() {
        const searchTerm = $(this).val();
        const resultsContainer = $('#tser-res1');

        clearTimeout(searchTimeout);

        if (searchTerm.length >= 2) {
          searchTimeout = setTimeout(function() {
            $.post('user/search_suggestions', {
              term: searchTerm
            }, function(data) {
              resultsContainer.empty();
              if (data && data.length > 0) {
                data.forEach(function(item) {
                  resultsContainer.append(`
										<li><a href="user/search?q=${encodeURIComponent(item.suggestion)}">${item.suggestion}</a></li>
									`);
                });
                resultsContainer.show();
              } else {
                resultsContainer.hide();
              }
            }, 'json').fail(function() {
              resultsContainer.hide();
            });
          }, 300);
        } else {
          resultsContainer.hide();
        }
      });

      // Hide suggestions when clicking outside
      $(document).on('click', function(e) {
        if (!$(e.target).closest('.sr-sea').length) {
          $('#tser-res1').hide();
        }
      });

      // Search suggestions for banner search
      $('.search-input').on('input', function() {
        const searchTerm = $(this).val();

        if (searchTerm.length >= 2) {
          // You can implement autocomplete here similar to above
          console.log('Searching for:', searchTerm);
        }
      });

      // Handle enter key for banner search
      $('.search-input').on('keypress', function(e) {
        if (e.which === 13) {
          $(this).closest('form').submit();
        }
      });

      // Handle banner search form submission
      $('.search-box').on('submit', function(e) {
        const searchInput = $(this).find('input[name="q"]');
        const searchTerm = searchInput.val().trim();

        if (searchTerm === '') {
          e.preventDefault();
          searchInput.focus();
          return false;
        }
      });

      // Handle top search form submission
      $('#filter_form').on('submit', function(e) {
        const searchInput = $(this).find('input[name="q"]');
        const searchTerm = searchInput.val().trim();

        if (searchTerm === '') {
          e.preventDefault();
          searchInput.focus();
          return false;
        }
      });

      // Category modal functionality
      $('a[data-target="#categoriesModal"]').on('click', function(e) {
        e.preventDefault();
        $('#categoriesModal').modal('show');
      });
    });
  </script>

  <!-- Categories Modal -->
  <div class="modal fade" id="categoriesModal" tabindex="-1" role="dialog" aria-labelledby="categoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="categoriesModalLabel">ALL CATEGORIES</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            
            <?php if (isset($allCategories) && !empty($allCategories)): ?>
              <?php
              // Limit to 50 categories for modal (25 per column)
              $modal_limited_categories = array_slice($allCategories, 0, 50);
              foreach ($modal_limited_categories as $category): ?>
                <div class="col-md-6 col-sm-6 mb-2">
                  <a href="<?php echo htmlspecialchars($home_cat_url($category)); ?>" class="d-block p-2" style="color: #666; text-decoration: none;">
                    <i class="fa fa-angle-right" style="margin-right: 8px; color: #7bb73f;"></i>
                    <?php echo htmlspecialchars($category->category_name); ?>
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Fallback categories -->
              <div class="col-md-6 col-sm-6 mb-2">
                <a href="<?php echo base_url('user/all_listing/business'); ?>" class="d-block p-2" style="color: #666; text-decoration: none;">
                  <i class="fa fa-angle-right" style="margin-right: 8px; color: #7bb73f;"></i>
                  Business Services
                </a>
              </div>
              <div class="col-md-6 col-sm-6 mb-2">
                <a href="<?php echo base_url('user/all_listing/food'); ?>" class="d-block p-2" style="color: #666; text-decoration: none;">
                  <i class="fa fa-angle-right" style="margin-right: 8px; color: #7bb73f;"></i>
                  Food & Dining
                </a>
              </div>
              <div class="col-md-6 col-sm-6 mb-2">
                <a href="<?php echo base_url('user/all_listing/health'); ?>" class="d-block p-2" style="color: #666; text-decoration: none;">
                  <i class="fa fa-angle-right" style="margin-right: 8px; color: #7bb73f;"></i>
                  Health & Medical
                </a>
              </div>
              <div class="col-md-6 col-sm-6 mb-2">
                <a href="<?php echo base_url('user/all_listing/automotive'); ?>" class="d-block p-2" style="color: #666; text-decoration: none;">
                  <i class="fa fa-angle-right" style="margin-right: 8px; color: #7bb73f;"></i>
                  Automotive
                </a>
              </div>
              <div class="col-md-6 col-sm-6 mb-2">
                <a href="<?php echo base_url('user/all_listing/shopping'); ?>" class="d-block p-2" style="color: #666; text-decoration: none;">
                  <i class="fa fa-angle-right" style="margin-right: 8px; color: #7bb73f;"></i>
                  Shopping
                </a>
              </div>
              <!-- Add more fallback categories as needed -->
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <a href="<?php echo base_url('all-category'); ?>" class="btn btn-primary" style="background: #7bb73f; border-color: #7bb73f;">
            <i class="fa fa-external-link-alt mr-2"></i>Explore All
          </a>
        </div>
      </div>
    </div>
  </div>


  <script>
    // Newsletter AJAX handler (keeps existing design; posts to user/subscribe_newsletter)
    (function() {
      var form = document.getElementById('newsletterForm');
      if (!form) return;
      var emailInput = document.getElementById('newsletterEmail');
      var submitBtn = document.getElementById('newsletterSubmit');
      var msgBox = document.getElementById('newsletterMessage');
      var origBtnHtml = submitBtn ? submitBtn.innerHTML : '';

      function showMessage(message, type) {
        if (!msgBox) return;
        msgBox.style.display = 'block';
        msgBox.textContent = message;
        if (type === 'success') {
          msgBox.style.background = '#d4edda';
          msgBox.style.color = '#155724';
          msgBox.style.border = '1px solid #c3e6cb';
        } else {
          msgBox.style.background = '#f8d7da';
          msgBox.style.color = '#721c24';
          msgBox.style.border = '1px solid #f5c6cb';
        }
      }

      form.addEventListener('submit', function(e) {
        e.preventDefault();
        var email = emailInput.value.trim();
        if (!email) {
          showMessage('Please enter your email address', 'error');
          return;
        }

        // disable + spinner (FontAwesome spinner used, FA already included)
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        msgBox.style.display = 'none';

        var fd = new FormData();
        fd.append('email', email);

        fetch('<?php echo base_url("user/subscribe_newsletter"); ?>', {
            method: 'POST',
            body: fd
          })
          .then(function(res) {
            return res.json();
          })
          .then(function(data) {
            if (data && data.success) {
              showMessage(data.message || 'Subscribed successfully', 'success');
              emailInput.value = '';
              setTimeout(function() {
                form.style.display = 'none';
                if (msgBox) msgBox.innerHTML = '<div style="text-align: center; padding: 20px;"><h3 style="color: #155724; margin-bottom: 10px;">✓ Successfully Subscribed!</h3><p style="color: #155724;">Check your email for confirmation.</p></div>';
              }, 3000);
            } else {
              showMessage((data && data.message) ? data.message : 'Subscription failed', 'error');
            }
          })
          .catch(function(err) {
            console.error('Newsletter error:', err);
            showMessage('An error occurred. Please try again.', 'error');
          })
          .finally(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnHtml;
          });
      });
    })();

    // Step 1: Store all URLs in one array
    const urls = [
      '/news',
      '/all_listing',
      '/all_listing/information-technology-services',
      '/all_listing/health-services',
      '/all_listing/sports-clubs',
      '/all_listing/entertainment-bureaus'
    ];

    // Step 2: Select all nav links
    const navLinks = document.querySelectorAll('.nav-link');

    // Step 3: Add click event to each link
    navLinks.forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault(); // prevent default broken behavior

        const index = link.getAttribute('data-index');
        const targetUrl = urls[index];

        if (targetUrl) {
          // Open in new tab (like target="_blank")
          window.location.href = targetUrl;
        }
      });
    });
  </script>
</body>

</html>