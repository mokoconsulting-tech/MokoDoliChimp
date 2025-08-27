<?php
/**
 * HTML Form Other Class for Demo
 * 
 * @file        html.formother.class.php
 * @brief       HTML form helper class for standalone demo
 */

class FormOther {
    public $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function selectarray($htmlname, $array, $id = '', $show_empty = 0, $key_in_label = 0, $value_as_key = 0, $moreparam = '', $translate = 0, $maxlen = 0, $disabled = 0, $sort = '', $morecss = 'minwidth75', $addjscombo = 1, $moreparamonempty = '', $disablebademail = 0, $nohtmlescape = 0) {
        $html = '<select name="' . $htmlname . '" class="' . $morecss . '">';
        if ($show_empty) $html .= '<option value="">--</option>';
        foreach ($array as $key => $value) {
            $selected = ($key == $id) ? ' selected' : '';
            $html .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}