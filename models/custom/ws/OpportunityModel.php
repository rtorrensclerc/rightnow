<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_3 as RNCPHP;

class OpportunityModel extends \RightNow\Models\Base
{
    public $error = '';

    function __construct()
    {
        parent::__construct();
        // \RightNow\Libraries\AbuseDetection::check();
    }
    public function attachPDF($op_id,$pdfBuffer,$name)
    {
        try
        {
          $new_opportunity = RNCPHP\Opportunity::fetch($op_id);
          $new_opportunity->FileAttachments = new RNCPHP\FileAttachmentCommonArray();
          $fattach = new RNCPHP\FileAttachmentCommon();
          $fattach->ContentType = "text/pdf";
          $fp = $fattach->makeFile();
          fwrite( $fp, $pdfBuffer);
          fclose( $fp );
          $fattach->FileName = $name.".pdf";
          $new_opportunity->FileAttachments[] = $fattach;

          $new_opportunity->save();
          return true;

        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "attachPDF : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function getOpportunity($op_id)
    {
        try
        {
          $opportunity = RNCPHP\Opportunity::fetch($op_id);
          return $opportunity;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "getOpportunity : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function getItems($op_id)
    {
        try
        {
          $items = RNCPHP\OP\OrderItems::find("Opportunity.ID = {$op_id} and Enabled = 1");
          //$items = RNCPHP\OP\OrderItems::fetch(10739);
          return $items;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Error ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function getError()
    {
      return $this->error;
    }


}
