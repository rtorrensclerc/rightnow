<rn:meta title="#rn:msg:ERROR_LBL#" template="dimacofi.php" login_required="false" />

<?list($errorTitle, $errorMessage) = \RightNow\Utils\Framework::getErrorMessageFromCode(\RightNow\Utils\Url::getParameter('error_id'));?>
<div class="rn_PageHeader">
    <div class="rn_Container">
        <h1><?=$errorTitle;?></h1>
    </div>
</div>

<div id="rn_PageContent rn_Home">
    <div class="rn_Padding">
      <p><?=$errorMessage;?></p>
    </div>
</div>
