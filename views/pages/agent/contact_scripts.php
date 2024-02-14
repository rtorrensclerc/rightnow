<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background-color: #fff;
    }
  </style>
   <script> window.timeStamp = <?php echo time()."000"; ?></script>
   <? if($_SERVER["HTTP_REFERER"] !== ""):?>
    <script type='text/javascript' src='/AgentWeb/module/extensibility/js/client/core/extension_loader.js' ></script>
    <script src="/euf/assets/js/contact.scripts_bui.js" charset="utf-8"></script>
   <? else:?>

</head>
<!-- <body onload=validateReceptionDate()> -->
<body>

</body>
</html>

