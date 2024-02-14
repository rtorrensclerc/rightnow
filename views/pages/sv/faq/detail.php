
<rn:meta title="#rn:php:\RightNow\Libraries\SEO::getDynamicTitle('answer', \RightNow\Utils\Url::getParameter('a_id'))#" template="2020.php" answer_details="true" clickstream="faq_detail" login_required="false" />

<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
  <div class="rn_ContentDetail">
    <div class="rn_PageTitle rn_RecordDetail">
      <!--div class="rn_RecordInfo rn_AnswerInfo">
        #rn:msg:PUBLISHED_LBL# 
        <span itemprop="dateCreated">
          <rn:field name="Answer.CreatedTime" />
        </span>
        &nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        #rn:msg:UPDATED_LBL# <span itemprop="dateModified">
          <rn:field name="Answer.UpdatedTime" /></span>
      </div-->
    </div>

    <div class="rn_PageContent rn_RecordDetail">
      <div class="rn_RecordText rn_AnswerText" itemprop="articleBody">
        <rn:field name="Answer.Solution" highlight="true" />
      </div>
      <rn:widget path="knowledgebase/GuidedAssistant" />
      <div class="rn_FileAttach">
        <rn:widget path="output/DataDisplay" name="Answer.FileAttachments" label="#rn:msg:ATTACHMENTS_LBL#" />
      </div>

      <rn:widget path="feedback/AnswerFeedback" label_title="#rn:msg:IS_THIS_ANSWER_HELPFUL_LBL#" options_count="5" dialog_threshold="0" />
    </div>
  </div>
  <rn:widget path="knowledgebase/RelatedAnswers" />
</article>