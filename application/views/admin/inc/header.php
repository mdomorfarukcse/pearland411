



<!doctype html>



  <html lang="en">







  <head>



    <title>Pearland Admin Panel</title>



    <!--== META TAGS ==-->



    <meta charset="utf-8">



    <meta http-equiv="X-UA-Compatible" content="IE=edge">



    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">



    <meta name="theme-color" content="#76cef1" />



    <!--== FAV ICON(BROWSER TAB ICON) ==-->



    <link rel="shortcut icon" href="<?= base_url() ?>assets/images/fav.ico" type="image/x-icon">



    <!--== GOOGLE FONTS ==-->



    <link href="https://fonts.googleapis.com/css?family=Oswald:700|Source+Sans+Pro:300,400,600,700&display=swap" rel="stylesheet">







    <!--== WEB ICON FONTS ==-->



    <link rel="preload" as="font" href="css/icon.woff2" type="font/woff2" crossorigin="anonymous">



    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">



    <!--== CSS FILES ==-->



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">







    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">







    <link rel="stylesheet" href="<?= base_url() ?>assets/admin/css/admin-style.css">



    <link rel="stylesheet" href="<?= base_url() ?>assets/css/fonts.css">



    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">











    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->



    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->



    <!--[if lt IE 9]>



    <script src="../js/html5shiv.js"></script>



    <script src="../js/respond.min.js"></script>



  <![endif]-->



  </head>







  <body>







    <?php if (isset($_SESSION['logged_user']) && !empty($_SESSION['logged_user'])) { ?>



      <!-- START -->



      <section>



        <div class="ad-head">



          <div class="head-s1">



            <div class="menu">



              <i class="material-icons mopen">menu</i>



              <i class="material-icons mclose">close</i>



            </div>



            <div class="logo">



              <img src="<?= base_url() ?>assets/admin/images/logo-b.png">



            </div>



          </div>



          <div class="head-s2">



            <div class="head-sear">



              <input type="text" id="top_search" placeholder="Search the listing and users..." class="search-field">



              <ul id="tser-res" class="tser-res">



                <li><a href="activate.html">Activate template</a></li>



                <li><a href="updates.html">Bizbook template updates and release</a></li>



                <li><a href="addons.html">Premium Addons</a></li>





              </ul>



            </div>



          </div>



          <div class="head-s3">



            <div class="head-pro">



              <img src="<?= base_url() ?>assets/images/user/3.jpg" alt="">



              <b>Profile by</b><br>



              <h4>Bizbook Directory Template</h4>



              <a href="admin-setting.html" class="fclick"></a>



            </div>



          </div>



        </div>



      </section>



      <!-- END -->



      <?php } ?>