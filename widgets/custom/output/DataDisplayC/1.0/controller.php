<?php
namespace Custom\Widgets\output;

use \RightNow\Utils as RNCUTILS;

class DataDisplayC extends \RightNow\Widgets\DataDisplay {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData()
    {
        $site = '<rn:widget path="custom/output/IncidentThreadDisplay" sub_id="incident">';
        if(RNCUTILS\Connect::isFileAttachmentType($this->data['value']))
        {
            $site = '<rn:widget path="output/FileListDisplay" sub_id="file"/>';
        }
        elseif(RNCUTILS\Connect::getProductCategoryType($this->data['value']))
        {
            $site = '<rn:widget path="output/ProductCategoryDisplay" sub_id="prodCat"/>';
        }
        elseif(RNCUTILS\Connect::isIncidentThreadType($this->data['value']))
        {
            $site = '<rn:widget path="custom/output/IncidentThreadDisplay" sub_id="incident">';
        }
        else
        {
            $site = '<rn:widget path="output/FieldDisplay" sub_id="genericField"/>';
        }
        $this->data['figured_widget'] = $site;
        return parent::getData();

    }
}