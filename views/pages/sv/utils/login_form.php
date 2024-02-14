<rn:meta title="Inicio de Sesión" template="2020.php" login_required="false" redirect_if_logged_in="account/overview" force_https="true" />

<div class="rn_StandardLogin">
  <h2>Conéctese con su cuenta</h2><br />
  <rn:widget path="login/LoginForm" redirect_url="/app/sv/home" initial_focus="true" label_login_button="Ingresar"/>
  <p><a href="/app/#rn:config:CP_ACCOUNT_ASSIST_URL##rn:session#">#rn:msg:FORGOT_YOUR_USERNAME_OR_PASSWORD_MSG#</a>
  </p>
  <p><a href="/app/utils/create_account">#rn:msg:CUSTOM_MSG_NEW_ACOUNT_REQUEST#</a></p>
</div>

<div class="login_content">
  <div class="banner">
  <div class="siema">
    <div>
    <img src="/euf/assets/themes/2020/images/banners/slide_5_1.png">    
    </div>  
    <div>
    <img src="/euf/assets/themes/2020/images/banners/slide_1_1.png">
    </div>
    <div>
    <img src="/euf/assets/themes/2020/images/banners/slide_2_1.png">
    </div>
    <div>
    <img src="/euf/assets/themes/2020/images/banners/slide_3_1.png">
    </div>
    <div>
    <img src="/euf/assets/themes/2020/images/banners/slide_4_1.png">
    </div>
    
  </div>
    <div>
<!--h2>Para mayor información contáctenos a <c style="color:red">midimacofi@dimacofi.cl</c></h2-->
</div>
  </div>
  
</div>
<style>
  .fab {
padding: 20px;
font-size: 30px;
width: 30px;
text-align: center;
text-decoration: none;
margin: 5px 2px;
border-radius: 50%;
margin-right: 5%;
}

.fab:hover {
opacity: 0.7;
}

.fa-whatsapp {
background: #FFFFFF;
color: white;
}
</style>
<div align="right"   >
<a class="fab fa-whatsapp" href='https://api.whatsapp.com/send?phone=+56975874934&text=Hola Dimacofi' target="_blank">
    <img src="/euf/assets/themes/2020/images/layout/logo-wasap.png" width="50" height="50">
</a>
</div>

<br/>
<br/>
<script>
  var mySiema = new Siema({
    duration: 500,
    loop: true,
  });

  setInterval(() => mySiema.next(), 5000)
</script>
