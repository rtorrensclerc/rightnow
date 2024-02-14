<rn:meta title="#rn:msg:SUPPORT_HISTORY_LBL#" template="2020.php" clickstream="incident_list" login_required="true" force_https="true" />

<div class="content-title wrapper">
    <h1>#rn:msg:CUSTOM_MSG_MY_REQUEST#</h1>
</div>

<h2>#rn:msg:CUSTOM_MSG_STATUS_DESC#</h2>
<div class="rn_Table">
	<table>
		<thead>
			<tr>
				<th>#rn:msg:STATES_LBL#</th>
				<th>#rn:msg:DESCRIPTION_LBL#</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>#rn:msg:CREATED_LBL#</td>
				<td>#rn:msg:CUSTOM_MSG_INITIAL_STATE_PRE_AGENT#</td>
			</tr>

			<tr>
				<td>#rn:msg:UPDATED_LBL#</td>
				<td>#rn:msg:CUSTOM_MSG_REQUEST_VIA_EMAIL#</td>
			</tr>

			<tr>
				<td>#rn:msg:CUSTOM_MSG_IN_EVALUATION#</td>
				<td>#rn:msg:CUSTOM_MSG_SUPERVISOR_ATT#</td>
			</tr>

			<tr>
				<td>#rn:msg:CUSTOM_MSG_IN_PROCESS#</td>
				<td>#rn:msg:CUSTOM_MSG_REQUEST_READY_TO_SENT#</td>
			</tr>

			<tr>
				<td>#rn:msg:CUSTOM_MSG_SOLVED_SUCCESFULLY#</td>
				<td>#rn:msg:CUSTOM_MSG_REQUEST_SOLVED_SENT#</td>
			</tr>

			<tr>
				<td>#rn:msg:CUSTOM_MSG_CANCELED#</td>
				<td>#rn:msg:CUSTOM_MSG_EVALUATION_NONSUCCESFULL#</td>
			</tr>
		</tbody>
	</table>
</div>


<div class="content-body">
    <rn:container report_id="101319">
    <div class="wrapper correction-words">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="reports/ResultInfo"/>
        <rn:widget path="reports/Grid" add_params_to_url="org" label_caption="<span class='rn_ScreenReaderOnly'>#rn:msg:SEARCH_YOUR_SUPPORT_HISTORY_CMD#</span>"/>
        <rn:widget path="reports/Paginator"/>
    </div>

    </rn:container>
</div>
