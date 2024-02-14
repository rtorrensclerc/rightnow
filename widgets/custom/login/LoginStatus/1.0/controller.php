<?php

namespace Custom\Widgets\login;

class LoginStatus extends \RightNow\Libraries\Widget\Base
{
    public function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    public function getData()
    {
        // $contactID = \RightNow\Utils\Framework::isLoggedIn() ? $this->CI->session->getProfileData('contactID') : null;
        //
        // if ($contactID) {
        //     $this->data['Count'] = $this->CI->model('custom/ShoppingCartModel')->getProductQuantity($this->CI->session->getProfile()->c_id->value);
        // } else {
        //   $this->data['Count'] = 0;
        // }

        return parent::getData();
    }
}
