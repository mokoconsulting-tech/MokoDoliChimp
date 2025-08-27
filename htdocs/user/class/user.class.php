<?php
/**
 * User Class for Demo
 */

class User {
    public $db;
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $office_phone;
    public $user_mobile;
    public $birth;
    public $job;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function fetch($id) {
        return 1;
    }
}