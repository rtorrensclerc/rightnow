<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="dimacofi2017.php" clickstream="home"/>

<div class="rn_PageHeader rn_Wraper">
    <div class="rn_PageHeaderInner">
        <div class="rn_PageHeaderCopy">
            <h1>#rn:msg:CUSTOM_MSG_INPUT_REQUEST#</h1>
        </div>
    </div>
</div>
<rn:condition answers_viewed="3">
    <li><rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ASK_QUESTION_HDG#"
         link="/app/ask" pages="ask, ask_confirm"/></li>
<rn:condition_else/>
    <li><rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ASK_QUESTION_HDG#"
         searches_done="2" link="/app/ask" pages="ask, ask_confirm"/></li>
</rn:condition>
<div class="rn_PageContent rn_Home rn_Wraper">

    <div class="rn_PaneContentInner">
      <form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
        <div id="rn_ErrorLocation" style="display:none;"></div>

        <fieldset>
            <legend>#rn:msg:CUSTOM_MSG_CLIENT_DATA#</legend>
            <div class="form-content">
                <rn:condition logged_in="false">
                  <div class="form-element">
                      <rn:widget path="input/FormInput" name="Contact.Name.First" label_input="#rn:msg:FIRST_NAME_LBL#" required="true"/>
                  </div>

                  <div class="form-element">
                      <rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="#rn:msg:LAST_NAME_LBL#" required="true"/>
                  </div>

                </rn:condition>
                <div class="form-element">
                    <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" initial_focus="true" label_input="#rn:msg:CUSTOM_MSG_EMAIL_ADDR_LBL#" required="true" />
                </div>
                <div class="form-element">
                    <rn:widget path="input/FormInput" name="Contact.Phones.HOME.Number" label_input="#rn:msg:HOME_PHONE_LBL#" required="true" />
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>#rn:msg:CUSTOM_MSG_REQUEST_DATA#</legend>
            <div class="form-content">
                <div class="form-element">
                    <rn:widget path="input/ProductCategoryInput" name="Incident.Product" required_lvl="1" />
                </div>

                <div class="form-element form-element-wide">
                    <rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_COMMENTS_LBL#"/>
                </div>

                <div class="form-element-wide">
                    <rn:widget path="input/FileAttachmentUpload"/>
                </div>
            </div>
        </fieldset>

        <rn:widget path="input/FormSubmit" label_button="#rn:msg:SUBMIT_YOUR_QUESTION_CMD#" on_success_url="/app/ask_confirm" error_location="rn_ErrorLocation"/>
    </form>
    </div>

</div>
