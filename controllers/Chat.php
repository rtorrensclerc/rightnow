<?php

namespace Custom\Controllers;


header('Content-Type: application/json');

class Chat extends \RightNow\Controllers\Base
{
	// Carga el modelo de Chat
    function __construct()
    {
        parent::__construct();
        $this->load->model('Chat');
    }
	
	/**
	* Función que obtiene el horario de chat
	*/
    function getSchedule()
    {
        $schedule =  $this->Chat->getChatHours()->result;
        echo json_encode($schedule);
    }
}

