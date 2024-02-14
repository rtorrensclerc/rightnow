<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

  <meta charset="utf-8">
    <title>Adjuntar Firma</title>
    <meta name="description" content="Signature Pad - HTML5 canvas based smooth signature drawing using variable width spline interpolation.">

    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">



    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-39365077-1']);
      _gaq.push(['_trackPageview']);
      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    </script>
  </head>

  <div id="openModal" class="modalDialog">
  	<div>
  		<div class="outter"   id="barra2"><div class="inner" id="barra" >Cargando</div></div>
  	</div>
  </div>


    <div id="signature-pad" class="m-signature-pad">
      <div class="m-signature-pad--body">
        <canvas>texto</canvas>
      </div>
      <div class="m-signature-pad--footer">
        <div class="description">Enviar Firma </div>
        <div class="left">
            <input type="button" name="btn_clear" value="Limpiar">
            <input name="hidden_data2" id='hidden_data2' type="hidden"/>
        </div>
        <div class="right">
          <form method="post" accept-charset="utf-8" name="form1">
            <input type="button" name="btn_send" value="Enviar">
            <input name="hidden_data" id='hidden_data' type="hidden"/>
        </form>
 

        </div>
      </div>
    </div>
