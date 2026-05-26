<?php
// Weather widget proxy now lives in application/controllers/Weather.php
// and is served at /weather (route registered in config/routes.php).

// Simple routing for search functionality
if (isset($_SERVER['REQUEST_URI'])) {
  $request_uri = $_SERVER['REQUEST_URI'];
  $request_path = parse_url($request_uri, PHP_URL_PATH);

  // Remove leading slash and get path segments
  $path = ltrim($request_path, '/');
  $segments = explode('/', $path);

  // Handle search route - both /search and direct search queries
  $is_search_route = ($segments[0] === 'search' && count($segments) >= 1);
  $has_search_query = (isset($_GET['q']) && !empty(trim($_GET['q'])));

  if ($is_search_route || ($has_search_query && ($segments[0] === 'search' || strpos($request_uri, 'search') !== false))) {

    // This is a search request, handle it with search page
    $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

    // Set up basic environment for search
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

    // Create a simple search handler
    class SimpleSearchHandler
    {
      private $data = array();

      public function __construct()
      {
        $this->data['search_query'] = '';
        $this->data['search_results'] = array('total_count' => 0);
        $this->data['categories'] = array();
        $this->data['latest_news'] = array();
      }

      public function handleSearch($query)
      {
        $this->data['search_query'] = htmlspecialchars($query);

        // Try to load model and get results if files exist
        if (!empty($query) && file_exists('Usermodel.php')) {
          include_once 'Usermodel.php';
          try {
            $model = new Usermodel();
            if (method_exists($model, 'globalSearch')) {
              $search_results = $model->globalSearch($query);
              $this->data['search_results'] = $search_results;
            }
            if (method_exists($model, 'getCategoriesWithCount')) {
              $this->data['categories'] = $model->getCategoriesWithCount();
            }
            if (method_exists($model, 'getLatestNews')) {
              $this->data['latest_news'] = $model->getLatestNews(5);
            }
          } catch (Exception $e) {
            // Handle error silently
            $this->data['error'] = 'Search temporarily unavailable';
            error_log('Search error: ' . $e->getMessage());
          }
        }

        $this->renderSearchPage();
      }

      private function renderSearchPage()
      {
        // Make data available to included files
        extract($this->data);

        // Load header if exists
        if (file_exists('header.php')) {
          include 'header.php';
        }

        // Load search page
        if (file_exists('search.php')) {
          include 'search.php';
        } else {
          $this->renderBasicSearch();
        }

        // Load footer if exists  
        if (file_exists('footer.php')) {
          include 'footer.php';
        }
      }

      private function renderBasicSearch()
      {
        echo "<div style='padding: 40px; text-align: center;'>";
        echo "<h1>Search Pearland411</h1>";
        if (!empty($this->data['search_query'])) {
          echo "<p>Search results for: <strong>" . $this->data['search_query'] . "</strong></p>";
          if (isset($this->data['search_results']['total_count'])) {
            echo "<p>Found " . $this->data['search_results']['total_count'] . " results</p>";
          }
        }
        echo "<form method='GET' action='search' style='max-width: 500px; margin: 20px auto;'>";
        echo "<input type='text' name='q' value='" . htmlspecialchars($this->data['search_query']) . "' placeholder='Search...' style='padding: 10px; width: 70%; border: 1px solid #ccc;'>";
        echo "<button type='submit' style='padding: 10px 20px; background: #7bb73f; color: white; border: none; cursor: pointer;'>Search</button>";
        echo "</form>";
        echo "</div>";
      }
    }

    $handler = new SimpleSearchHandler();
    $handler->handleSearch($search_query);
    exit; // Stop execution after handling search
  }
}
?>
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
<html lang="en">

<head>
  <title>Home - Pearland411</title>
  <!--== META TAGS ==-->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="theme-color" content="#76cef1" />
  <meta property="og:image" content="../assets/images/home/16077bizbook-white.png" />

  <!--== FAV ICON(BROWSER TAB ICON) ==-->
  <link rel="shortcut icon" href="../assets/images/fav.ico" type="image/x-icon">
  <!--== GOOGLE FONTS ==-->
  <link href="https://fonts.googleapis.com/css?family=Oswald:700|Source+Sans+Pro:300,400,600,700&display=swap"
    rel="stylesheet">
  <!--== WEB ICON FONTS ==-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--== Bootstrap CDN ==-->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <!-- Style Path -->
  <link rel="stylesheet" href="../assets/css/style.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.7/swiper-bundle.min.css">
  <!-- Calendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">


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
        flex-direction: column-reverse;
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

      .hide_on_phone {
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

<body>

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
                                  src="../assets/images/icon/shop.png" loading="lazy">All
                                Services
                              </a></li>

                            <li><a href="news"><img src="../assets/images/icon/news.png"
                                  loading="lazy">News & Magazines </a></li>
                            <li><a href="event"><img src="../assets/images/icon/calendar.png"
                                  loading="lazy">Events </a></li>
                            <li><a href="all-products"><img src="../assets/images/icon/cart.png"
                                  loading="lazy">Products </a></li>

                            <li><a href="all-blog"><img src="../assets/images/icon/blog1.png"
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
                                $category_url = !empty($category->category_slug)
                                  ? $category->category_slug
                                  : strtolower(str_replace([' ', '&'], ['-', 'and'], $category->category_name));

                                echo '<li><a target="_blank" href="' . base_url('user/all_listing/' . $category_url) . '">' . htmlspecialchars($category->category_name) . '</a></li>';
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



    <!-- Category Navigation -->
    <section class="category-tabs new_category">
      <div class="container px-10">
        <ul class="nav nav-pills justify-content-center">
          <li class="nav-item">
            <a class="nav-link" data-index="0">
              <!-- Globe SVG -->
              <svg width="18" height="18" fill="currentColor"
                style="vertical-align:middle;margin-right:6px;" viewBox="0 0 16 16">
                <path
                  d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0 0 14V1zm1 0v14a7 7 0 0 0 0-14z" />
              </svg>
              All News
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-index="1">
              <!-- Chart Line SVG -->
              <svg width="18" height="18" fill="currentColor"
                style="vertical-align:middle;margin-right:6px;" viewBox="0 0 16 16">
                <path
                  d="M0 0h1v15h15v1H0V0zm14.293 4.707l-4.147 4.146-3-3L1.5 11.5l.707.707 5-5 3 3 4.854-4.853-.707-.707z" />
              </svg>
              Business
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-index="2">
              <!-- Laptop SVG -->
              <svg width="18" height="18" fill="currentColor"
                style="vertical-align:middle;margin-right:6px;" viewBox="0 0 16 16">
                <path
                  d="M2.5 3A1.5 1.5 0 0 0 1 4.5v7A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 13.5 3h-11zm0 1h11A.5.5 0 0 1 14 4.5v7a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-7A.5.5 0 0 1 2.5 4z" />
              </svg>
              Technology
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-index="3">
              <!-- Heartbeat SVG -->
              <svg width="18" height="18" fill="currentColor"
                style="vertical-align:middle;margin-right:6px;" viewBox="0 0 16 16">
                <path
                  d="M8 2.748-.717 9.737c-.329.326-.329.855 0 1.181.329.326.86.326 1.189 0l6.528-6.326 6.528 6.326c.329.326.86.326 1.189 0 .329-.326.329-.855 0-1.181L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.721-3.042 23.333 4.867 8 15z" />
              </svg>
              Health
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-index="4">
              <!-- Futbol/Soccer SVG -->
              <svg width="18" height="18" fill="currentColor"
                style="vertical-align:middle;margin-right:6px;" viewBox="0 0 16 16">
                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1" fill="none" />
                <path
                  d="M8 1v2M8 13v2M1 8h2M13 8h2M3.5 3.5l1.5 1.5M11 11l1.5 1.5M3.5 12.5l1.5-1.5M11 5l1.5-1.5"
                  stroke="currentColor" stroke-width="1" />
                <circle cx="8" cy="8" r="2.5" fill="currentColor" />
              </svg>
              Sports
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-index="5">
              <!-- Film SVG -->
              <svg width="18" height="18" fill="currentColor"
                style="vertical-align:middle;margin-right:6px;" viewBox="0 0 16 16">
                <path
                  d="M0 1a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V1zm15 0H1v14h14V1zM2 2v2h2V2H2zm10 0v2h2V2h-2zM2 12v2h2v-2H2zm10 0v2h2v-2h-2z" />
              </svg>
              Entertainment
            </a>
          </li>
        </ul>
      </div>
    </section>


    <!-- ============
         START MAIN CONTENT
         ================ -->

    <section class="main-content new_style py-5">
      <div class="container px-10">

        <div class="main-content-flex">
          <div class="main-area">

            <!-- AD Image -->
            <img class="big_ad_image" src="/assets/images/AD-SAMPLE.png" alt="AD">
            <!-- AD Image -->
            <!-- =========================
                     SECTION 1: Featured Businesses (Replaces Featured Stories)
                ========================= -->
            <section class="featured-section">
              <h2 class="section-title">Featured Businesses</h2>
              <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                  <?php if (!empty($featured_listings)): ?>
                    <?php foreach ($featured_listings as $index => $listing): ?>
                      <?php
                      // Get category name
                      $categoryName = $this->usermodel->getCategoryName($listing->category_id, 'listing_categories', 'category_id');

                      // Dynamic background images
                      $backgroundImages = [
                        'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=800&q=80'
                      ];
                      $bgImage = $backgroundImages[$index % count($backgroundImages)];

                      // Always prioritize listing image, fallback to curated images
                      $slideBackground = !empty($listing->listing_image)
                        ? "linear-gradient(45deg, rgba(24,15,94,0.85), #7bb73f9d), url('" . base_url($listing->listing_image) . "') center center/cover no-repeat"
                        : "linear-gradient(45deg, rgba(24,15,94,0.85), #7bb73f9d), url('" . $bgImage . "') center center/cover no-repeat";
                      ?>
                      <div class="swiper-slide"
                        style="background: <?php echo $slideBackground; ?>;">
                        <div class="slide-content">
                          <h3 class="slide-title"><?php echo !empty($listing->listing_name) ? htmlspecialchars($listing->listing_name) . " – " . htmlspecialchars($categoryName) : "Featured Business in " . htmlspecialchars($categoryName); ?></h3>
                          <div class="slide-meta">
                            <?php echo date('M Y'); ?> | <?php echo htmlspecialchars($categoryName); ?> |
                            <?php echo isset($listing->spotlight_listing) && $listing->spotlight_listing == 1 ? 'Spotlight Business' : 'Featured Business'; ?>
                          </div>
                          <p><?php echo !empty($listing->listing_description) ? htmlspecialchars(substr($listing->listing_description, 0, 120)) . '...' : 'Discover this amazing local business in Pearland.'; ?></p>
                          <a href="<?php echo base_url('listing-details/' . $listing->listing_slug); ?>" class="btn btn-sm btn-primary mt-2">
                            <?php echo isset($listing->spotlight_listing) && $listing->spotlight_listing == 1 ? 'Spotlight Business' : 'Featured Listing'; ?>
                          </a>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <!-- Fallback: Show default slide if no featured listings -->
                    <div class="swiper-slide"
                      style="background: linear-gradient(45deg, rgba(24,15,94,0.85), #7bb73f9d), url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80') center center/cover no-repeat;">
                      <div class="slide-content">
                        <h3 class="slide-title">Featured Businesses Coming Soon</h3>
                        <div class="slide-meta">
                          <?php echo date('M Y'); ?> | Business Directory
                        </div>
                        <p>Discover amazing local businesses in Pearland. Featured listings coming soon!</p>
                        <a href="<?php echo base_url('all_listing'); ?>" class="btn btn-sm btn-primary mt-2">Explore All Businesses</a>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
              </div>
            </section>
            <!-- =========================
                     SECTION 2: Explore Pearland Businesses (Replaces Latest Directory)
                ========================= -->
            <section>
              <h2 class="section-title mb-4">Explore Pearland Businesses</h2>
              <div id="business-list">
                <?php if (!empty($latest_listings)): ?>
                  <?php foreach ($latest_listings as $row): ?>
                    <?php
                    $categoryName = $this->usermodel->getCategoryName($row->category_id);
                    ?>
                    <div class=" fade-in business-item lst-com-box" data-category="<?php echo htmlspecialchars($categoryName); ?>">

                      <div class="list-com-img">
                        <?php if (!empty($row->listing_image)): ?>
                          <?= responsive_img($row->listing_image, $row->listing_name) ?>
                        <?php else: ?>
                          <img src="<?= base_url('assets/images/pearland-logo.png') ?>" alt="<?= e_attr($row->listing_name) ?>" loading="lazy" decoding="async">
                        <?php endif; ?>
                        <span class="list-com-cat"><?php echo htmlspecialchars($categoryName); ?></span>
                        <?php
                        // Show "NEW" badge for listings created in the last 7 days
                        if (isset($row->created_at) && strtotime($row->created_at) > strtotime('-7 days')):
                        ?>
                          <span class="new-badge" style="position: absolute; top: 10px; left: 10px; background: #28a745; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600;">NEW</span>
                        <?php endif; ?>
                      </div>
                      <div class="list-com-con p-3">
                        <div>
                          <h3 class="list-com-title" style="font-size:1rem;"><?php echo htmlspecialchars($row->listing_name); ?></h3>
                          <p class="list-com-desc" style="font-size:0.92rem;">
                            <?php echo nl2br(htmlspecialchars(substr($row->listing_description, 0, 100) . '...')); ?>
                          </p>
                        </div>
                        <a href="<?php echo base_url('listing-details/' . $row->listing_slug); ?>" class="list-enq-btn">Read More →</a>
                      </div>

                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <!-- Sample Data for Testing -->
                  <div class="col-md-4 col-sm-6 fade-in business-item" data-category="Restaurant">
                    <div class="lst-com-box">
                      <div class="list-com-img">
                        <img src="../assets/images/listings/list1.jpg" alt="Bella's Bistro">
                      </div>
                      <div class="list-com-con">
                        <h4>Bella's Bistro</h4>
                        <span class="list-com-cat">Restaurant</span>
                        <p>Farm-to-table restaurant serving fresh, locally-sourced dishes...</p>
                        <a href="#" class="list-enq-btn">View Details</a>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-sm-6 fade-in business-item" data-category="Fitness">
                    <div class="news-card p-2">
                      <div class="card-image" style="height:120px; background: linear-gradient(45deg, #180f5e, #7bb73f); display: flex; align-items: center; justify-content: center;">
                        <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=100&h=100&fit=crop&crop=center" alt="Pearland Fit Gym" style="width:60px;height:60px;background:#fff;border-radius:50%; object-fit: cover;">
                        <span class="card-category">Fitness</span>
                      </div>
                      <div class="card-content p-2">
                        <h3 class="card-title" style="font-size:1rem;">Pearland Fit Gym</h3>
                        <p class="card-excerpt" style="font-size:0.92rem;">
                          Modern equipment, expert trainers, and a welcoming atmosphere for all ages and fitness levels in Pearland...
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-sm-6 fade-in business-item" data-category="Pizza">
                    <div class="news-card p-2">
                      <div class="card-image" style="height:120px; background: linear-gradient(45deg, #180f5e, #7bb73f); display: flex; align-items: center; justify-content: center;">
                        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=100&h=100&fit=crop&crop=center" alt="Tony's Pizzeria" style="width:60px;height:60px;background:#fff;border-radius:50%; object-fit: cover;">
                        <span class="card-category">Pizza</span>
                      </div>
                      <div class="card-content p-2">
                        <h3 class="card-title" style="font-size:1rem;">Tony's Pizzeria</h3>
                        <p class="card-excerpt" style="font-size:0.92rem;">
                          Hand-tossed pizzas, classic recipes, and a warm family vibe. Serving authentic Italian cuisine since 1995...
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-sm-6 fade-in business-item" data-category="Healthcare">
                    <div class="news-card p-2">
                      <div class="card-image" style="height:120px; background: linear-gradient(45deg, #180f5e, #7bb73f); display: flex; align-items: center; justify-content: center;">
                        <img src="https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=100&h=100&fit=crop&crop=center" alt="Care+ Medical Clinic" style="width:60px;height:60px;background:#fff;border-radius:50%; object-fit: cover;">
                        <span class="card-category">Healthcare</span>
                      </div>
                      <div class="card-content p-2">
                        <h3 class="card-title" style="font-size:1rem;">Care+ Medical Clinic</h3>
                        <p class="card-excerpt" style="font-size:0.92rem;">
                          Comprehensive healthcare services with experienced doctors and state-of-the-art medical equipment...
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-sm-6 fade-in business-item" data-category="Automotive">
                    <div class="news-card p-2">
                      <div class="card-image" style="height:120px; background: linear-gradient(45deg, #180f5e, #7bb73f); display: flex; align-items: center; justify-content: center;">
                        <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=100&h=100&fit=crop&crop=center" alt="AutoCare Plus" style="width:60px;height:60px;background:#fff;border-radius:50%; object-fit: cover;">
                        <span class="card-category">Automotive</span>
                      </div>
                      <div class="card-content p-2">
                        <h3 class="card-title" style="font-size:1rem;">AutoCare Plus</h3>
                        <p class="card-excerpt" style="font-size:0.92rem;">
                          Professional auto repair and maintenance services. ASE certified technicians with 20+ years experience...
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-sm-6 fade-in business-item" data-category="Beauty">
                    <div class="news-card p-2">
                      <div class="card-image" style="height:120px; background: linear-gradient(45deg, #180f5e, #7bb73f); display: flex; align-items: center; justify-content: center;">
                        <img src="https://images.unsplash.com/photo-1560066984-138dadb4c035?w=100&h=100&fit=crop&crop=center" alt="Luxe Hair Salon" style="width:60px;height:60px;background:#fff;border-radius:50%; object-fit: cover;">
                        <span class="card-category">Beauty</span>
                      </div>
                      <div class="card-content p-2">
                        <h3 class="card-title" style="font-size:1rem;">Luxe Hair Salon</h3>
                        <p class="card-excerpt" style="font-size:0.92rem;">
                          Professional hair styling, coloring, and beauty treatments. Expert stylists using premium products...
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
              <div class="text-center mt-3">
                <a href="/all-listing" class="btn btn-view-more mt-3 m1-5">View More</a>
              </div>
            </section>
            <!-- =========================
                     SECTION 4: Video Business Highlights (Replaces Live & Video News Highlights)
                ========================= -->
            <section class="video-section">
              <div class="container">
                <div class="row">
                  <div class="col-lg-12">
                    <h2 class="section-title">Community Highlights</h2>
                    <div class="row">
                      <?php if (!empty($latest_videos)): ?>
                        <?php foreach (array_slice($latest_videos, 0, 4) as $video): ?>
                          <div class="col-md-4 col-sm-12">
                            <div class="video-box">
                              <div class="video-img" style="position:relative;">
                                <?php
                                $fallbackImg = 'https://www.techsmith.com/wp-content/uploads/2023/08/What-are-High-Resolution-Images.png';
                                $imgSrc = !empty($video->video_image) ? htmlspecialchars($video->video_image) : $fallbackImg;
                                ?>
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($video->video_title); ?>">
                                <a href="/video_details/<?php echo htmlspecialchars($video->video_slug); ?>" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(24,15,94,0.7);border-radius:50%;padding:18px;display:inline-block;">
                                  <svg width="32" height="32" viewBox="0 0 32 32" fill="#fff">
                                    <circle cx="16" cy="16" r="16" fill="rgba(0,0,0,0.2)" />
                                    <polygon points="13,10 23,16 13,22" fill="#fff" />
                                  </svg>
                                </a>
                              </div>
                              <div class="video-con">
                                <a href="/video_details/<?php echo htmlspecialchars($video->video_slug); ?>" class="video-title"><?php echo htmlspecialchars($video->video_title); ?></a>
                                <p class="video-desc"><?php echo strip_tags(html_entity_decode($video->video_description)) . '...'; ?></p>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                      <div class="col-md-12">
                        <div class="text-center mb-3">
                          <a href="/videos" class="btn btn-view-more mt-3 m1-5">View More Videos</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!--====================
				   NEWS & BLOG
				=====================-->
            <section class="blog">
              <div class="container">
                <div class="row">
                  <div class="col-lg-12">
                    <h2 class="section-title">Local Announcements & Updates</h2>
                    <div class="row">
                      <?php if (!empty($latest_news)): ?>
                        <?php foreach ($latest_news as $i => $news): ?>
                          <?php $colClass = 'col-md-4 col-sm-6'; ?>
                          <div class="<?php echo $colClass; ?>">
                            <div class="blog-box">
                              <div class="blog-img">
                                <?php if (!empty($news->news_image)): ?>
                                  <?= responsive_img($news->news_image, $news->news_title) ?>
                                <?php else: ?>
                                  <img src="<?= base_url('assets/images/pearland-logo.png') ?>" alt="<?= e_attr($news->news_title) ?>" loading="lazy" decoding="async">
                                <?php endif; ?>
                              </div>
                              <div class="blog-con">
                                <span class="blog-cat">News</span>
                                <div>
                                  <h4><?php echo htmlspecialchars($news->news_title); ?></h4>
                                  <p><?php echo strip_tags(html_entity_decode($news->news_description)) . '...'; ?></p>
                                </div>
                                <a href="<?php echo base_url('news_details/' . $news->news_id); ?>" class="blog-read">
                                  Read More
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                  </svg>
                                </a>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                    <div class="text-center mt-3">
                      <a href="/all-news" class="btn btn-view-more mb-3 m1-5">View More News</a>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Website Development Section -->
            <section id="web-services" class="web-dev-section">
              <div class="container">
                <h2 class="section-title">Grow Your Business Online</h2>
                <p class="section-subtitle">
                  Professional website development services for Pearland businesses
                </p>

                <div class="row mb-5">
                  <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box">
                      <div class="feature-icon">
                        <!-- Laptop Code SVG -->
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <path d="M3 4H21V15H3V4Z" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                          <path d="M2 20H22" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                          <path d="M8 20H16" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        </svg>
                      </div>
                      <h4 class="feature-title">Custom Websites</h4>
                      <p class="feature-description">
                        Beautiful, responsive websites tailored to your business needs
                      </p>
                    </div>
                  </div>

                  <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box">
                      <div class="feature-icon">
                        <!-- Mobile Device SVG -->
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <rect x="6" y="2" width="12" height="20" rx="2" stroke="#10B981" stroke-width="2" />
                          <circle cx="12" cy="18" r="1" fill="#10B981" />
                        </svg>
                      </div>
                      <h4 class="feature-title">Mobile-First Design</h4>
                      <p class="feature-description">
                        Websites that look perfect on any device or screen size
                      </p>
                    </div>
                  </div>

                  <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box">
                      <div class="feature-icon">
                        <!-- Search/SEO SVG -->
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <circle cx="11" cy="11" r="7" stroke="#F59E0B" stroke-width="2" />
                          <line x1="16.65" y1="16.65" x2="21" y2="21" stroke="#F59E0B" stroke-width="2"
                            stroke-linecap="round" />
                        </svg>
                      </div>
                      <h4 class="feature-title">SEO Optimization</h4>
                      <p class="feature-description">
                        Get found on Google and attract more local customers
                      </p>
                    </div>
                  </div>

                  <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box">
                      <div class="feature-icon">
                        <!-- Support Headset SVG -->
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <path d="M12 2C6.48 2 2 6.48 2 12V16H6V12H4C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12H18V16H22V12C22 6.48 17.52 2 12 2Z"
                            stroke="#6366F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                          <rect x="9" y="16" width="6" height="6" rx="1" stroke="#6366F1" stroke-width="2"
                            stroke-linejoin="round" />
                        </svg>
                      </div>
                      <h4 class="feature-title">24/7 Support</h4>
                      <p class="feature-description">
                        Ongoing maintenance and support whenever you need it
                      </p>
                    </div>
                  </div>
                </div>

                <div class="row align-items-center">

                  <div class="col-lg-12" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 30px; color: white;">
                    <div class="text-center">
                      <div>
                        <!-- Chart Line SVG -->
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg" style="margin-bottom:20px;">
                          <path d="M3 17L9 11L13 15L21 7" stroke="white" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                          <path d="M21 10V7H18" stroke="white" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        </svg>
                        <h2 class="mb-3">Special Offer for Listed Businesses</h2>
                        <p style="font-size: 1.1rem;">
                          List your business for FREE and get 20% off website development
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12 mt-4">
                    <h3 class="mb-4 fw-bold" style="font-weight: 700;">Why Your Business Needs a Professional Website</h3>
                  </div>
                  <div class="col-lg-12">
                    <ul class="list-unstyled">
                      <li class="mb-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          style="vertical-align:middle; margin-right:6px;">
                          <path d="M5 13L9 17L19 7" stroke="#10B981" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        </svg>
                        <strong>Increase Visibility:</strong> Be found by customers searching online
                      </li>
                      <li class="mb-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          style="vertical-align:middle; margin-right:6px;">
                          <path d="M5 13L9 17L19 7" stroke="#10B981" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        </svg>
                        <strong>Build Credibility:</strong> Professional website builds trust with customers
                      </li>
                      <li class="mb-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          style="vertical-align:middle; margin-right:6px;">
                          <path d="M5 13L9 17L19 7" stroke="#10B981" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        </svg>
                        <strong>24/7 Availability:</strong> Your business works even when you're closed
                      </li>
                      <li class="mb-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          style="vertical-align:middle; margin-right:6px;">
                          <path d="M5 13L9 17L19 7" stroke="#10B981" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        </svg>
                        <strong>Cost-Effective Marketing:</strong> Reach more customers for less than traditional ads
                      </li>
                      <li class="mb-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          style="vertical-align:middle; margin-right:6px;">
                          <path d="M5 13L9 17L19 7" stroke="#10B981" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        </svg>
                        <strong>Stay Competitive:</strong> Keep up with competitors who are already online
                      </li>
                    </ul>
                  </div>



                  <div class="col-md-12 mt-4">
                    <a href="/contact-us" class="btn btn-primary btn-get-started btn-lg">
                      Get Your Website Started Today
                    </a>
                  </div>
                </div>
              </div>
            </section>




          </div>
          <!-- =========================
					SIDEBAR
					========================= -->
          <div class="sidebar">
            <!-- Sidebar: Weather Widget -->
            <div class="widget hide_on_phone">
              <h3 class="widget-title">
                Pearland Weather
              </h3>
              <div id="weather-forecast" style="margin-bottom:10px;"></div>
              <div id="weather-current"></div>
            </div>


            <!-- Categories Widget -->
            <div class="widget categories">
              <h3 class="widget-title">Business Categories</h3>
              <ul class="category-list">
                <?php if (!empty($random_listing_categories)): ?>
                  <?php
                  // Function to create safe URL slug (defined again for sidebar)
                  if (!function_exists('createSafeSlug')) {
                    function createSafeSlug($text)
                    {
                      // Convert to lowercase
                      $slug = strtolower($text);
                      // Replace spaces with hyphens
                      $slug = str_replace(' ', '-', $slug);
                      // Remove special characters (keep only letters, numbers, and hyphens)
                      $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
                      // Remove multiple consecutive hyphens
                      $slug = preg_replace('/-+/', '-', $slug);
                      // Remove leading and trailing hyphens
                      $slug = trim($slug, '-');
                      return $slug;
                    }
                  }

                  // Randomize the category order
                  shuffle($random_listing_categories);

                  // Limit to 10 random categories
                  $random_categories = array_slice($random_listing_categories, 0, 10);

                  foreach ($random_categories as $cat):
                    $slug = createSafeSlug(!empty($cat->category_slug) ? $cat->category_slug : $cat->category_name);
                  ?>
                    <li>
                      <a href="<?php echo base_url('all_listing/' . $slug); ?>">
                        <?php echo htmlspecialchars($cat->category_name); ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <!-- Sample Categories -->
                  <li><a href="#">Restaurants</a></li>
                  <li><a href="#">Fitness & Health</a></li>
                  <li><a href="#">Beauty & Spa</a></li>
                  <li><a href="#">Automotive</a></li>
                  <li><a href="#">Healthcare</a></li>
                  <li><a href="#">Shopping</a></li>
                <?php endif; ?>
              </ul>


            </div>

            <!-- AD Image -->
            <img class="sidebar_ad_image hide_on_phone" src="/assets/images/ad-400-150.png" alt="AD">
            <!-- <img class="sidebar_ad_image" src="/assets/images/ad-400-300.png" alt="AD"> -->
            <!-- Web Development Card -->
            <section class="sidebar-marketing-section">
              <div class="marketing-card">
                <div class="card-content">
                  <div class="icon-container">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <rect x="2" y="3" width="20" height="14" rx="2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M8 21h8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M12 17v4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M7 7h10M7 11h6" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                  </div>

                  <h2 class="main-heading">Do You Need a Website?</h2>
                  <p class="description">
                    Transform your business with a professional, modern website that drives results.
                  </p>

                  <div class="features-wrapper">
                    <div class="feature">
                      <svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
                        <path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span>Custom design tailored to your brand</span>
                    </div>
                    <div class="feature">
                      <svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
                        <path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span>Mobile-responsive & fast loading</span>
                    </div>
                    <div class="feature">
                      <svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
                        <path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span>SEO optimized for better visibility</span>
                    </div>
                  </div>

                  <a href="/contact-us" class="primary-button">Get Your Website</a>
                </div>
              </div>
            </section>
            <!-- Web Development Card -->
            <!-- AD Image -->

            <!-- Hot Deals Widget -->
            <div class="widget hide_on_phone">
              <h3 class="widget-title">Hot Deals & Trending Offers</h3>
              <div class="trending-item">
                <div class="trending-number">1</div>
                <div class="trending-content">
                  <h6>20% Off First Visit – Luxe Hair Salon</h6>
                  <div class="trending-meta">Diamond Plan Deal of the Day</div>
                </div>
              </div>
              <div class="trending-item">
                <div class="trending-number">2</div>
                <div class="trending-content">
                  <h6>Buy 1 Get 1 Pizza – Tony's Pizzeria</h6>
                  <div class="trending-meta">Coupon promotion</div>
                </div>
              </div>
              <div class="trending-item">
                <div class="trending-number">3</div>
                <div class="trending-content">
                  <h6>Free Consultation – Pearland Tax Experts</h6>
                  <div class="trending-meta">Service offer</div>
                </div>
              </div>
              <div class="trending-item">
                <div class="trending-number">4</div>
                <div class="trending-content">
                  <h6>Hot Spot: Tidy Garage Pearland</h6>
                  <div class="trending-meta">Premium listing</div>
                </div>
              </div>
            </div>

            <!-- Newsletter Widget -->
            <div class="widget hide_on_phone">
              <h3 class="widget-title">Subscribe for Local Deals</h3>
              <p>Get the best local deals, new openings, and events in Pearland — direct to your inbox.</p>
              <form id="newsletterForm" class="newsletter-form" method="post" action="<?= base_url('user/subscribe_newsletter') ?>">
                <label for="newsletterEmail" style="position:absolute;left:-9999px;">Email</label>
                <input type="email" id="newsletterEmail" name="email" class="newsletter-input" placeholder="Your email address" required>
                <button type="submit" id="newsletterSubmit" class="newsletter-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="white">
                    <path d="M2 21l21-9L2 3v7l15 2-15 2z" />
                  </svg>
                </button>
              </form>
              <div id="newsletterMessage" style="margin-top:12px;display:none;padding:10px;border-radius:6px;font-size:14px;"></div>
            </div>

            <!-- Sidebar: Local Business Events Calendar -->
            <div class="widget hide_on_phone">
              <h3 class="widget-title">
                Local Business Events Calendar
              </h3>
              <div id="sidebar-calendar"></div>
              <ul class="list-group list-group-flush mt-3">
                <li class="list-group-item">
                  <strong>June 24:</strong> Free Health Check-up Camp by Care+ Clinic
                </li>
                <li class="list-group-item">
                  <strong>June 30:</strong> Live Music at Pearl’s Diner
                </li>
                <li class="list-group-item">
                  <strong>July 5:</strong> Small Biz Networking Breakfast
                </li>
              </ul>
            </div>
            <!-- AD Image -->
            <img class="sidebar_ad_image hide_on_phone" src="/assets/images/sidebar-ad.png" alt="AD">


          </div>



        </div>

      </div>
  </div>
  </div>
  </section>

  </div>


  </div>

  <!-- ================ Pricing Section ================z -->
  <div class="ads-pricing-section">
    <div class="container">
      <div class="section-header">
        <h2>Choose Your <span class="highlight">Advertisement</span> Plan</h2>
        <p class="subtitle">Get your business discovered with flexible pricing designed to fit your needs and drive results.</p>
      </div>
    </div>
    <section class="pri">
      <div class="container">
        <div class="row">
          <div>
            <ul>
              <li>
                <div class="pri-box">
                  <div class="c2">
                    <h4>Basic PLAN</h4>
                    <p>Business with logo display</p>
                  </div>
                  <div class="c3">
                    <h2>
                      $29
                    </h2>
                    <p>Per month</p>
                  </div>
                  <div class="c4">
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Business logo display</span>
                    </div>
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Basic listing</span>
                    </div>
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Company name visible</span>
                    </div>
                  </div>
                  <div class="c5">
                    <a href="checkout?plan_id=1" class="cta1">Get Start</a>
                  </div>
                </div>
              </li>
              <li>
                <div class="pri-box">
                  <div class="c2">
                    <h4>Standard PLAN</h4>
                    <p>Location & address included</p>
                  </div>
                  <div class="c3">
                    <h2>
                      $59
                    </h2>
                    <p>Per month</p>
                  </div>
                  <div class="c4">
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Everything in Basic</span>
                    </div>
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Full address/location</span>
                    </div>
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Map integration</span>
                    </div>
                  </div>
                  <div class="c5">
                    <a href="checkout?plan_id=2" class="cta1">Get Start</a>
                  </div>
                </div>
              </li>
              <li>
                <div class="pri-box">
                  <div class="c2">
                    <h4>Premium PLAN</h4>
                    <p>Full package with video</p>
                  </div>
                  <div class="c3">
                    <h2>
                      $99
                    </h2>
                    <p>Per month</p>
                  </div>
                  <div class="c4">
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Everything in Standard</span>
                    </div>
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Video showcase</span>
                    </div>
                    <div class="feature-item">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                      </svg>
                      <span>Priority listing</span>
                    </div>
                  </div>
                  <div class="c5">
                    <a href="checkout?plan_id=3" class="cta1">Get Start</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </div>
  <!-- ================ Pricing Section ================z -->


  <!-- ================ How Works Section ================z -->
  <section>
    <div class="str count">
      <div class="container">
        <div class="row">
          <div class="how-wrks">
            <div class="home-tit">
              <h2>
                <span>How It Works</span>
              </h2>
              <p>Explore some of the best tips from around the world from our
                <br>partners and friends.lacinia viverra lectus.
              </p>
            </div>
            <div class="how-wrks-inn row">
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div>

                  <img src="/assets/images/icon/how1.png" class="b-lazy b-loaded" alt="">
                  <h4>Create an account</h4>
                  <p>Quickly register to unlock access to powerful business tools and features.</p>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div>

                  <img src="/assets/images/icon/how2.png" class="b-lazy b-loaded" alt="">
                  <h4>Add your business</h4>
                  <p>List your company details to boost visibility and attract potential customers.</p>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div>

                  <img src="/assets/images/icon/how3.png" class="b-lazy b-loaded" alt="">
                  <h4>Get more leads</h4>
                  <p>Connect with interested clients and grow your business through targeted exposure.</p>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div>

                  <img src="/assets/images/icon/how4.png" class="b-lazy b-loaded" alt="">
                  <h4>Achieve goles</h4>
                  <p>Easily manage, track, and store your achieved milestones for future reference.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ================ How Works Section ================z -->

  <!-- ============
         END MAIN CONTENT
         ================ -->


  <section>

    <div class="CTA">
      <div class="container">
        <div class="row">
          <div class="col-md-12 bb-text">
            <h4>List your business for FREE</h4>
            <p>There are many variations of passages of Lorem Ipsum available, but the majority have <br>
              suffered alteration in some form, by injected humour.</p>
            <a href="/pricing-plan">Add my business <i
                class="material-icons">arrow_forward</i></a>
          </div>
        </div>
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
            // Function to create safe URL slug
            function createSafeSlug($text)
            {
              // Convert to lowercase
              $slug = strtolower($text);
              // Replace spaces with hyphens
              $slug = str_replace(' ', '-', $slug);
              // Remove special characters (keep only letters, numbers, and hyphens)
              $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
              // Remove multiple consecutive hyphens
              $slug = preg_replace('/-+/', '-', $slug);
              // Remove leading and trailing hyphens
              $slug = trim($slug, '-');
              return $slug;
            }

            // Display first 8 categories for Top Category column
            if (!empty($all_listing_categories)) {
              $top_categories = array_slice($all_listing_categories, 0, 8);
              foreach ($top_categories as $category) {
                $category_slug = (!empty($category->category_slug)) ? createSafeSlug($category->category_slug) : createSafeSlug($category->category_name);
                echo '<li><a href="user/all_listing/' . htmlspecialchars($category_slug) . '">' . htmlspecialchars($category->category_name) . '</a></li>';
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
                $category_slug = (!empty($category->category_slug)) ? createSafeSlug($category->category_slug) : createSafeSlug($category->category_name);
                echo '<li><a href="user/all_listing/' . htmlspecialchars($category_slug) . '">' . htmlspecialchars($category->category_name) . '</a></li>';
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
          <h4>SOCIAL MEDIA</h4>
          <ul>
            <li><a target="_blank" href=""><img src="../assets/images/social/1.png" alt="" loading="lazy"></a>
            </li>
            <li><a target="_blank"
                href="https://twitter.com/Google?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor"><img
                  src="../assets/images/social/2.png" alt="" loading="lazy"></a></li>
            <li><a target="_blank" href="https://www.facebook.com/pearland411-1956793534579530/"><img
                  src="../assets/images/social/3.png" alt="" loading="lazy"></a></li>
            <li><a target="_blank" href=""><img src="../assets/images/social/4.png" alt="" loading="lazy"></a>
            </li>
            <li><a target="_blank" href=""><img src="../assets/images/social/5.png" alt="" loading="lazy"></a>
            </li>
          </ul>
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
      <li><a href="/"><img src="../assets/images/icon/home.png">Home</a></li>
      <li><span class="mob-sear"><img src="../assets/images/icon/search1.png">Search</span></li>
      <li><a href="all-category" class="act"><img src="../assets/images/icon/shop.png">Services</a></li>
      <li><a href="event"><img src="../assets/images/icon/calendar.png">Events</a></li>
      <li><a href="all-products"><img src="../assets/images/icon/cart.png">Products</a></li>
      <li><a href="all-blog"><img src="../assets/images/icon/blog1.png">Blogs</a></li>
    </ul>
  </div>
  <!-- END -->

  <!-- Optional JavaScript -->
  <script src="../assets/js/jquery.min.js"></script>
  <script src="../assets/js/popper.min.js"></script>
  <script src="../assets/js/bootstrap.min.js"></script>
  <script src="../assets/js/jquery-ui.js"></script>
  <script src="../assets/js/select-opt.js"></script>
  <script src="../assets/js/slick.js"></script>
  <script src="../assets/js/custom.js"></script>
  <script src="../assets/js/new.js"></script>
  <script src="../assets/js/jquery.validate.min.js"></script>
  <script src="../assets/js/custom_validation.js"></script>
  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
    integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
    integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"
    integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
    crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.7/swiper-bundle.min.js"></script>

  <script>
    // Initialize Swiper
    var swiper = new Swiper(".mySwiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });




    // Category tab functionality (replace the old one)
    $('.nav-pills .nav-link').on('click', function(e) {
      e.preventDefault();
      $('.nav-pills .nav-link').removeClass('active');
      $(this).addClass('active');
      const category = $(this).text().trim();
      if (category === "All News") {
        $('.news-item').show();
      } else {
        $('.news-item').hide();
        $(`.news-item[data-category="${category}"]`).show();
      }
    });


    // Parallax effect for banner
    $(window).scroll(function() {
      const scrollTop = $(this).scrollTop();
      $('.banner-section').css('transform', 'translateY(' + scrollTop * 0.1 + 'px)');
    });

    // Dynamic time update
    function updateTimes() {
      const now = new Date();
      $('.card-meta span:first-child, .trending-meta, .slide-meta').each(function() {
        const text = $(this).text();
        if (text.includes('hours ago')) {
          // Simulate dynamic time updates
        }
      });
    }

    // Initialize animations
    setTimeout(() => {
      $('.fade-in').addClass('animate');
    }, 500);

    // Add interactive hover effects to cards
    $('.news-card').hover(
      function() {
        $(this).find('.card-title').css('color', 'var(--accent-color)');
      },
      function() {
        $(this).find('.card-title').css('color', 'var(--primary-color)');
      }
    );

    // Mobile menu enhancement
    $('.navbar-toggler').on('click', function() {
      $(this).toggleClass('active');
    });

    // Add click effect to all interactive elements
    $('button, .btn, .nav-link, .list-group-item').on('click', function(e) {
      const ripple = $('<span class="ripple"></span>');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      ripple.css({
        position: 'absolute',
        borderRadius: '50%',
        transform: 'scale(0)',
        animation: 'ripple 0.6s linear',
        left: x + 'px',
        top: y + 'px',
        width: size + 'px',
        height: size + 'px',
        background: 'rgba(255, 255, 255, 0.5)',
        pointerEvents: 'none'
      });

      $(this).css('position', 'relative').css('overflow', 'hidden').append(ripple);

      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
    // Add ripple animation CSS
    const style = document.createElement('style');
    style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                .pulse {
                    animation: pulse 1s infinite;
                }
                
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                    100% { transform: scale(1); }
                }
                
                .clicked {
                    transform: scale(0.98);
                    transition: transform 0.1s ease;
                }
                
                .animate {
                    animation: fadeInUp 0.6s ease forwards;
                }
                
                .swiper-button-next,
                .swiper-button-prev {
                    color: white !important;
                    background: rgba(24, 15, 94, 0.8);
                    width: 50px !important;
                    height: 50px !important;
                    border-radius: 50%;
                    margin-top: -25px !important;
                }
                
                .swiper-button-next:after,
                .swiper-button-prev:after {
                    font-size: 18px !important;
                }
                
                .swiper-pagination-bullet {
                    background: white !important;
                    opacity: 0.7;
                }
                
                .swiper-pagination-bullet-active {
                    background: var(--accent-color) !important;
                    opacity: 1;
                }
                
                .list-group-item {
                    border: none !important;
                    padding: 15px 0 !important;
                    border-bottom: 1px solid var(--border-color) !important;
                    transition: all 0.3s ease;
                }
                
                .list-group-item:hover {
                    background: var(--bg-light) !important;
                    padding-left: 15px !important;
                    border-radius: 10px !important;
                    margin: 0 -15px !important;
                }
                
                .list-group-item:last-child {
                    border-bottom: none !important;
                }
                
                .badge-primary {
                    background: var(--accent-color) !important;
                }
                
                /* Mobile Responsiveness Enhancements */
                @media (max-width: 992px) {
                    .swiper {
                        height: 300px;
                    }
                    
                    .slide-title {
                        font-size: 1.4rem;
                    }
                }
                
                @media (max-width: 576px) {
                    .banner-title {
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
                }
            `;
    document.head.appendChild(style);
  </script>

  <!-- Full Calendar -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('sidebar-calendar');
      if (calendarEl) {
        // Custom SVGs for prev/next (zoom) buttons
        function svgBtn(icon) {
          if (icon === 'prev') {
            return `<svg viewBox="0 0 16 16" fill="currentColor"><path d="M11 2L5 8l6 6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>`;
          }
          if (icon === 'next') {
            return `<svg viewBox="0 0 16 16" fill="currentColor"><path d="M5 2l6 6-6 6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>`;
          }
          if (icon === 'minus') {
            return `<svg viewBox="0 0 16 16" fill="currentColor"><rect x="3" y="7.25" width="10" height="1.5" rx="0.75"/></svg>`;
          }
          return '';
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          height: 320,
          headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: ''
          },
          events: [{
              title: 'Live News Event',
              start: new Date().toISOString().slice(0, 10),
              color: '#2a1a7f'
            },
            {
              title: 'Sports Final',
              start: new Date(new Date().setDate(new Date().getDate() + 3)).toISOString().slice(0, 10),
              color: '#ffc107'
            }
          ],
          buttonText: {
            prev: '',
            next: ''
          },
          customButtons: {
            prev: {
              text: '',
              icon: 'prev',
              click: function() {
                calendar.prev();
              }
            },
            next: {
              text: '',
              icon: 'next',
              click: function() {
                calendar.next();
              }
            }
          },
          // Render SVG icons for navigation
          viewDidMount: function() {
            setTimeout(function() {
              let prevBtn = calendarEl.querySelector('.fc-prev-button');
              let nextBtn = calendarEl.querySelector('.fc-next-button');
              if (prevBtn) prevBtn.innerHTML = svgBtn('prev');
              if (nextBtn) nextBtn.innerHTML = svgBtn('next');
            }, 10);
          }
        });
        calendar.render();
      }
    });
  </script>

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
            <?php
            // Function to create safe URL slug (defined again for modal)
            if (!function_exists('createSafeSlug')) {
              function createSafeSlug($text)
              {
                // Convert to lowercase
                $slug = strtolower($text);
                // Replace spaces with hyphens
                $slug = str_replace(' ', '-', $slug);
                // Remove special characters (keep only letters, numbers, and hyphens)
                $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
                // Remove multiple consecutive hyphens
                $slug = preg_replace('/-+/', '-', $slug);
                // Remove leading and trailing hyphens
                $slug = trim($slug, '-');
                return $slug;
              }
            }
            ?>
            <?php if (isset($allCategories) && !empty($allCategories)): ?>
              <?php
              // Limit to 50 categories for modal (25 per column)
              $modal_limited_categories = array_slice($allCategories, 0, 50);
              foreach ($modal_limited_categories as $category): ?>
                <div class="col-md-6 col-sm-6 mb-2">
                  <a href="<?php echo base_url('user/all_listing/' . createSafeSlug((!empty($category->category_slug) ? $category->category_slug : $category->category_name))); ?>" class="d-block p-2" style="color: #666; text-decoration: none;">
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