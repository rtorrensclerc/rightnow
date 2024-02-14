<rn:widget path="custom/redirect/RedirectBlocked" />
<rn:meta title="Bienvenido al Centro de Ayuda" template="2020.php" clickstream="freqQuestions" login_required="false" />

<?
  $CI = get_instance();
  $obj_info_contact= $CI->session->getSessionData('info_contact');

$kw     = \RightNow\Utils\Url::getParameter('kw');
$search = \RightNow\Utils\Url::getParameter('search');
?>

<? if (isset($kw) || isset($search)) : ?>
  <rn:container source_id="KFSearch" per_page="10" history_source_id="KFSearch">
    <div class="rn_Hero">
      <div class="rn_HeroInner">
        <div class="rn_SearchControls">
          <h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>
          <form onsubmit="return false;">
            <div class="rn_SearchInput">
              <rn:widget path="searchsource/SourceSearchField" initial_focus="true" filter_label="Keyword" filter_type="query" />
            </div>
            <rn:widget path="searchsource/SourceSearchButton" search_results_url="/app/sv/questions" endpoint="/ci/ajaxRequest/search" history_source_id="KFSearch" report_page_url="/app/sv/questions" />
          </form>
        </div>
      </div>
    </div>
  </rn:container>

  <div class="rn_Container">
    <div class="rn_PageContent rn_KBAnswerList">
      <div class="rn_HeaderContainer">
        <h2>#rn:msg:PUBLISHED_ANSWERS_LBL#</h2>
      </div>

      <rn:condition flashdata_value_for="info">
        <div class="rn_MessageBox rn_InfoMessage">
          #rn:flashdata:info#
        </div>
      </rn:condition>

      <rn:container source_id="KFSearch" per_page="10" report_id="101571" history_source_id="KFSearch">
        <div>
          <rn:widget path="searchsource/SourceResultDetails" />
          <rn:widget path="searchsource/SourceResultListing" more_link_url="" />
          <rn:widget path="searchsource/SourcePagination" />
        </div>
      </rn:container>
    </div>
  </div>
<? else : ?>
  <rn:container report_id="101571">
    <div class="rn_PageHeaderInner">
      <div class="rn_PageHeaderCopy">
      <h1 style="justify-content: center;text-align: center;">Preguntas Frecuentes</h1>
        <div class="rn_SearchControls">
          <h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>
          <form onsubmit="return false;">
            <div class="rn_SearchInput">
              <rn:widget path="search/KeywordText" label_text="#rn:msg:FIND_THE_ANSWER_TO_YOUR_QUESTION_CMD#" initial_focus="true" />
            </div>
            <rn:widget path="search/SearchButton" force_page_flip="true" />
          </form>
        </div>
      </div>
    </div>

 
    <div class="rn_Container">
      <div class="rn_PageContent">
        <h3>Preguntas Frecuentes</h3>
        <div class="rn_PageContentInner rn_PageDivided">
          <div class="rn_MultilineWrapper">
            <h3>Populares</h3>
            <rn:widget path="reports/Multiline" report_id="101569" per_page="5" />
          </div>
        </div>
        <div class="rn_PageContentInner rn_PageDivided">
          <div class="rn_MultilineWrapper">
            <h3>Nuevas y Actualizadas Recientemente</h3>
            <rn:widget path="reports/Multiline" report_id="101570" per_page="5" />
          </div>
        </div>
      </div>
    </div>

  </rn:container>
<? endif; ?>