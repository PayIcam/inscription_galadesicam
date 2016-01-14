<?php

namespace PayIcam;


class Reservation{
    
    private $id;
    private $icam_id;

    function __construct($login){
        global $DB;

        $this->id = $DB->query('INSERT INTO reservations_payicam (date_option, login) VALUES (:date_option, :login)', array('date_option'=> date('Y-m-d H:m:s'), 'login' => $login));
    }

    public function addNewIcam($data){
        global $DB;
        $data['reservation_id'] = $this->id;
        $this->icam_id = $DB->query("INSERT INTO guests_payicam (".implode(', ', array_keys($data)).") VALUES (:".implode(', :', array_keys($data)).")", $data);
        echo 'new id: '.$this->icam_id;
    }
}
