<!DOCTYPE html>
<html lang="#rn:language_code#">
<rn:meta javascript_module="standard" />

<head>
  <?php $this->load->helper('utils'); ?>
  <meta charset="utf-8" />
  <title>
    <rn:page_title />
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="apple-touch-icon" href="/euf/assets/images/apple-touch-icon.png">
  <link rel="icon" href="/euf/assets/images/favicon.png" type="image/png" />
  <link rel="icon" sizes="192x192" href="/euf/assets/images/icon_hires.png">
  <meta name="theme-color" content="#000000">
  <meta name="msapplication-navbutton-color" content="#000000">
  <meta name="apple-mobile-web-app-status-bar-style" content="#000000">
  <rn:theme path="/euf/assets/themes/2020" css="site.css" />
  <!-- <link href="//127.0.0.1:8080//site_dev.css" rel="stylesheet"> -->
  
  <script src="/euf/assets/js/integer.js"></script>
  <script src="/euf/assets/js/vendors/d3.min.js"></script>
  <script src="/euf/assets/js/vendors/download.js"></script>
  <script src="/euf/assets/js/vendors/xlsx.full.min.js"></script>
  <script src="/euf/assets/js/vendors/siema.min.js"></script>
  <link href="//fonts.googleapis.com/css?family=Lato:300,400,400i,700,700i" rel="stylesheet">
  <rn:head_content />
  <!-- <rn:widget path="utils/ClickjackPrevention" /> -->
</head>

<body class="yui-skin-sam yui3-skin-sam<?= (\RightNow\Utils\Framework::isLoggedIn())?" logged":" nologged" ?>" style="font-size: 1em;">

  <input type="checkbox" id="nav-trigger" class="nav-trigger" />
  <label for="nav-trigger" class="labelNavTrigger rn_Sprite"></label>

  <? // MENÚ MÓVIL ############################################ 
  ?>
  <? // FIN MENÚ MÓVIL ############################################ 
  ?>

  <div class="rn_Wrap">
   
    
    <div class="rn_Body row">
      <div class="rn_MainColumn" role="main">
        <a id="rn_MainContent"></a>
        <rn:page_content />
      </div>
    </div>

    
  </div>
</body>

</html>