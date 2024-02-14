<?php
namespace Custom\Widgets\input;

class SelectField extends \RightNow\Libraries\Widget\Base
{
    public function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    public function getData()
    {
    $this->data['js']['initial_value'] = $this->data['attrs']['value'];
    
        $options = $this->data['attrs']['options'];
        $this->data['listMenu'] = [];

        if ($options) {
            $options = explode(',',$options);

            foreach ($options as $option_key => $option_value) {
                if(strpos('|', $option_value) != -1){
                    $this->data['listMenu'][] = (object)['id'=>explode('|', $option_value)[0],'name'=>explode('|', $option_value)[1]];
                } else {
                    $this->data['listMenu'][] = (object)['id'=>$option_key,'name'=>$option_value];
                }
            }

        } else {
            $this->data['listMenu'][] = (object)['id'=>0,'name'=>'Sin Datos'];
        }
    }
}
