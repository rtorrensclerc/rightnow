<rn:meta title="#rn:php:\RightNow\Libraries\SEO::getDynamicTitle('incident', \RightNow\Utils\Url::getParameter('i_id'))#" template="2020.php" login_required="true" clickstream="incident_view" force_https="true" />


<div class="content-title wrapper">
    <h1><rn:field name="Incident.Subject" highlight="true"/></h1>
</div>

<div class="content-body">
  <div class="rn_RecordDetail rn_IncidentDetail wrapper">
      <!--rn:condition incident_reopen_deadline_hours="168"-->
          <fieldset>
            <legend>#rn:msg:UPDATE_THIS_QUESTION_CMD#</legend>
            <div id="rn_ErrorLocation" aria-atomic="true" aria-live="assertive" class="rn_Hidden"></div>
            <form id="rn_UpdateQuestion" onsubmit="return false;">

              <div class="form-element form-element-wide">
               
              
                  <rn:widget path="input/FormInput" id="z" name="Incident.CustomFields.c.severity" label_input="Severidad"
                      required="true" />
              
             
                <rn:widget path="input/FormInput" name="Incident.Threads" label_input="#rn:msg:ADD_ADDTL_INFORMATION_QUESTION_CMD#" initial_focus="true" required="true"/>
              </div>
              <div class="form-element-wide">
                <rn:widget path="input/FileAttachmentUpload" label_input="#rn:msg:ATTACH_FILE_CMD#"/>
              </div>
              
              
              <div class="form-buttons">
                <div class="rn_FormSubmit">
                <rn:widget path="input/FormSubmit" label_on_success_banner="#rn:msg:YOUR_QUESTION_HAS_BEEN_UPDATED_MSG#" on_success_url="#rn:php:(\RightNow\Utils\Url::getParameter('org'))?'/app/sv/request/history/org/'.\RightNow\Utils\Url::getParameter('org'):'/app/sv/request/history'#" error_location="rn_ErrorLocation"/>
                </div>
              </div>

            </form>
          </fieldset>
      <!--rn:condition_else/>
          <legend>#rn:msg:INC_REOPNED_UPD_FURTHER_ASST_PLS_MSG#</legend>
      </rn:condition-->

      <div class="rn_QuestionThread">
        <fieldset>
          <legend>#rn:msg:COMMUNICATION_HISTORY_LBL#</legend>
          <div class="form-element-wide">
              <rn:widget path="custom/output/DataDisplayC" name="Incident.Threads" label=""/>
          </div>
        </fieldset>
      </div>


      <div class="rn_AdditionalInfo">
        <fieldset>
            <legend>#rn:msg:ADDITIONAL_DETAILS_LBL#</legend>
            <div class="form-element">
              <rn:widget path="output/DataDisplay" name="Incident.PrimaryContact.Emails.PRIMARY.Address" label="Correo Electrónico" />
            </div>
            <div class="form-element">
              <rn:widget path="output/DataDisplay" name="Incident.ReferenceNumber" label="#rn:msg:REFERENCE_NUMBER_LBL#"/>
            </div>
            <div class="form-element">
            <rn:widget path="custom/output/DataDisplayInteger" name="Incident.StatusWithType.Status" />
            </div>
            <div class="form-element">
              <rn:widget path="output/DataDisplay" name="Incident.CreatedTime" label="#rn:msg:CREATED_LBL#" />
            </div>
            <div class="form-element">
              <rn:widget path="output/DataDisplay" name="Incident.UpdatedTime" label="#rn:msg:UPDATED_LBL#"/>
            </div>
            <div class="form-element">
              <rn:widget path="output/DataDisplay" name="Incident.Product"  label="#rn:msg:PRODUCT_LBL#"/>
            </div>
            <div class="form-element">
              <rn:widget path="output/DataDisplay" name="Incident.Category" label="#rn:msg:CATEGORY_LBL#"/>
            </div>
            <div class="form-element-wide">
              <rn:widget path="output/DataDisplay" name="Incident.FileAttachments" label="#rn:msg:FILE_ATTACHMENTS_LBL#"/>
            </div>
        </fieldset>
      </div>
      <div class="form-buttons">
        <div class="rn_DetailTools">
            <rn:widget path="utils/PrintPageLink" />
        </div>
      </div>
  </div>
</div>