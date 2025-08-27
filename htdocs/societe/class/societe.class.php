<?php
/**
 * Third Party Class for Demo
 */

class Societe {
    public $db;
    public $id;
    public $nom;
    public $email;
    public $phone;
    public $address;
    public $zip;
    public $town;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function fetch($id) {
        return 1;
    }
}