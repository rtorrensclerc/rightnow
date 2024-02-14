<?php
namespace Custom\Widgets\menu;

class iconButton extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
        
    }

    function getData() {
        $this->data['title']  = $this->data['attrs']['p_title'];
        $this->data['href']  = $this->data['attrs']['p_href'];
        $this->data['class']  = $this->data['attrs']['p_class'];
        $this->data['id']  = $this->data['attrs']['p_id'];
        $this->data['enabled']  = $this->data['attrs']['enabled'];
       
       
        return parent::getData();

    }
}