<rn:meta title="#rn:msg:FIND_ANS_HDG#" template="dimacofi.php" clickstream="answer_list"/>
<rn:widget path="custom/login/LoginAccountRequired" />

<rn:widget path="knowledgebase/RssIcon"/>
<rn:container report_id="101581">

<div class="rn_PageHeader">
    <div class="rn_PageHeaderInner">
      <h1>#rn:msg:CUSTOM_MSG_FRECUENT_QUESTION#</h1>
        <div class="rn_SearchControls">
            <form onsubmit="return false;">
                <div class="rn_SearchInput">
                    <rn:widget path="search/KeywordText" label_text="" initial_focus="true"/>
                </div>
                <rn:widget path="search/SearchButton" icon_path="images/layout/search_icon.png" />
            </form>
        </div>
    </div>
</div>

<div id="rn_PageContent" class="rn_AnswerList">
    <div class="rn_Padding">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="reports/ResultInfo"/>
        <rn:widget path="knowledgebase/TopicWords"/>
        <rn:widget path="reports/Multiline"/>
        <rn:widget path="reports/Paginator"/>
    </div>
</div>
</rn:container>
