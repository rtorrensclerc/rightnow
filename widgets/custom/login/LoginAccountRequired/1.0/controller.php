<?php
namespace Custom\Widgets\login;

class LoginAccountRequired extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {

        if (!empty($this->data['attrs']['url_redirect']))
        {
          $account_values = $this->CI->session->getSessionData('Account_loggedValues');
          //parche Cookie
          $account_values = unserialize($_COOKIE['Account_loggedValues']);

          if (empty($account_values) and !is_array($account_values))
            header("Location: {$this->data['attrs']['url_redirect']}");
        }
        else
          \RightNow\Utils\Url::redirectToErrorPage(4);
        return parent::getData();
    }
}
