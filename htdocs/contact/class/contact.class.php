<?php
/**
 * Contact Class for Demo
 */

class Contact {
    public $db;
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $phone;
    public $phone_mobile;
    public $birthday;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function fetch($id) {
        return 1;
    }
}