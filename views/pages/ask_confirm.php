<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="dimacofi2017.php" clickstream="home"/>

<div class="rn_PageHeader rn_Wraper">
    <div class="rn_PageHeaderInner">
        <div class="rn_PageHeaderCopy">
            <h1>#rn:msg:CUSTOM_MSG_INPUT_REQUEST#</h1>
        </div>
    </div>
</div>

<div class="rn_PageContent rn_Home rn_Wraper">

    <div class="rn_PaneContentInner">
      <p>
            #rn:msg:SUBMITTING_QUEST_REFERENCE_FOLLOW_LBL#
            <b>
                <rn:condition url_parameter_check="i_id == null">
                    ##rn:url_param_value:refno#.
                <rn:condition_else/>
                    <a href="/app/#rn:config:CP_INCIDENT_RESPONSE_URL#/i_id/#rn:url_param_value:i_id##rn:session#">#<rn:field name="Incident.ReferenceNumber" /></a>.
                </rn:condition>
            </b>
        </p>
        <p>#rn:msg:SUPPORT_TEAM_SOON_MSG#</p>
        <p>#rn:msg:NEED_UPD_EXP_OR_M_GO_TO_HIST_O_UPD_IT_MSG#</p>
    </div>

</div>
