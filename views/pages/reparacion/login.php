<? if(profiling('1')) header("Location: /app/reparacion/home"); ?>
<rn:meta title="#rn:msg:ASK_QUESTION_HDG#" template="dimacofi.php" clickstream=""/>
<div class="rn_PageHeader rn_Account">
    <div class="rn_Container">
        <h1>#rn:msg:CUSTOM_MSG_LOGIN#</h1>
    </div>
</div>

<div id="rn_PageContent">
    <div class="rn_Padding">
      <? if(!profiling('1')): ?>
        <rn:widget path="custom/login/LoginAccount" url_redirect="/app/reparacion/home" url_redirect_disconnect="/app/reparacion/login" in_line="false" />
      <? endif; ?>
    </div>
</div>
