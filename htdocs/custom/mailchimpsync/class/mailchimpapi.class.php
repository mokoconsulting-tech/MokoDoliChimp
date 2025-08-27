<?php
/**
 * Mailchimp API Integration Class
 * 
 * @file        mailchimpapi.class.php
 * @ingroup     mailchimpsync
 * @brief       Mailchimp API communication class
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

/**
 * Class to handle Mailchimp API operations
 */
class MailchimpAPI
{
    /** @var DoliDB Database handler */
    public $db;
    
    /** @var string API key */
    private $api_key;
    
    /** @var string Server prefix */
    private $server_prefix;
    
    /** @var string API base URL */
    private $api_base_url;
    
    /** @var array Error container */
    public $errors = array();
    
    /**
     * Constructor
     * 
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $conf;
        
        $this->db = $db;
        $this->api_key = $conf->global->MAILCHIMPSYNC_API_KEY;
        $this->server_prefix = $conf->global->MAILCHIMPSYNC_SERVER_PREFIX;
        
        if ($this->server_prefix) {
            $this->api_base_url = "https://{$this->server_prefix}.api.mailchimp.com/3.0/";
        }
    }
    
    /**
     * Test API connection
     * 
     * @param string $api_key Optional API key to test
     * @param string $server_prefix Optional server prefix to test
     * @return array Result array with success status and data
     */
    public function testConnection($api_key = null, $server_prefix = null)
    {
        $test_api_key = $api_key ?: $this->api_key;
        $test_server_prefix = $server_prefix ?: $this->server_prefix;
        
        if (!$test_api_key || !$test_server_prefix) {
            return array('success' => false, 'error' => 'API key and server prefix are required');
        }
        
        $test_url = "https://{$test_server_prefix}.api.mailchimp.com/3.0/";
        
        $response = $this->makeRequest('GET', $test_url, null, $test_api_key);
        
        if ($response['success']) {
            return array(
                'success' => true,
                'account_name' => isset($response['data']['account_name']) ? $response['data']['account_name'] : '',
                'account_id' => isset($response['data']['account_id']) ? $response['data']['account_id'] : ''
            );
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Check current connection status
     * 
     * @return array Connection status
     */
    public function checkConnection()
    {
        if (!$this->api_key || !$this->server_prefix) {
            return array('connected' => false, 'error' => 'API credentials not configured');
        }
        
        return $this->testConnection();
    }
    
    /**
     * Get all lists
     * 
     * @return array Lists data
     */
    public function getLists()
    {
        $response = $this->makeRequest('GET', 'lists');
        
        if ($response['success']) {
            return array('success' => true, 'lists' => $response['data']['lists']);
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Get list members
     * 
     * @param string $list_id List ID
     * @param int $count Number of records to fetch
     * @param int $offset Offset for pagination
     * @return array Members data
     */
    public function getListMembers($list_id, $count = 100, $offset = 0)
    {
        $params = array(
            'count' => $count,
            'offset' => $offset,
            'fields' => 'members.id,members.email_address,members.status,members.merge_fields,members.tags'
        );
        
        $response = $this->makeRequest('GET', "lists/{$list_id}/members", $params);
        
        if ($response['success']) {
            return array('success' => true, 'members' => $response['data']['members']);
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Add or update member
     * 
     * @param string $list_id List ID
     * @param array $member_data Member data
     * @return array Result
     */
    public function addOrUpdateMember($list_id, $member_data)
    {
        $email_hash = md5(strtolower($member_data['email_address']));
        
        $response = $this->makeRequest('PUT', "lists/{$list_id}/members/{$email_hash}", $member_data);
        
        if ($response['success']) {
            return array('success' => true, 'member' => $response['data']);
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Delete member
     * 
     * @param string $list_id List ID
     * @param string $email Email address
     * @return array Result
     */
    public function deleteMember($list_id, $email)
    {
        $email_hash = md5(strtolower($email));
        
        $response = $this->makeRequest('DELETE', "lists/{$list_id}/members/{$email_hash}");
        
        if ($response['success']) {
            return array('success' => true);
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Get member by email
     * 
     * @param string $list_id List ID
     * @param string $email Email address
     * @return array Member data
     */
    public function getMember($list_id, $email)
    {
        $email_hash = md5(strtolower($email));
        
        $response = $this->makeRequest('GET', "lists/{$list_id}/members/{$email_hash}");
        
        if ($response['success']) {
            return array('success' => true, 'member' => $response['data']);
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Get available merge fields for a list
     * 
     * @param string $list_id List ID
     * @return array Available fields
     */
    public function getAvailableFields($list_id = null)
    {
        if (!$list_id) {
            global $conf;
            $list_id = $conf->global->MAILCHIMPSYNC_DEFAULT_LIST_ID;
        }
        
        if (!$list_id) {
            return array();
        }
        
        $response = $this->makeRequest('GET', "lists/{$list_id}/merge-fields");
        
        if ($response['success']) {
            $fields = array(
                'EMAIL' => array('name' => 'Email Address', 'type' => 'email', 'required' => true),
                'FNAME' => array('name' => 'First Name', 'type' => 'text', 'required' => false),
                'LNAME' => array('name' => 'Last Name', 'type' => 'text', 'required' => false)
            );
            
            foreach ($response['data']['merge_fields'] as $field) {
                $fields[$field['tag']] = array(
                    'name' => $field['name'],
                    'type' => $field['type'],
                    'required' => $field['required']
                );
            }
            
            return $fields;
        } else {
            // Return default fields if API call fails
            return array(
                'EMAIL' => array('name' => 'Email Address', 'type' => 'email', 'required' => true),
                'FNAME' => array('name' => 'First Name', 'type' => 'text', 'required' => false),
                'LNAME' => array('name' => 'Last Name', 'type' => 'text', 'required' => false)
            );
        }
    }
    
    /**
     * Add tags to member
     * 
     * @param string $list_id List ID
     * @param string $email Email address
     * @param array $tags Tags to add
     * @return array Result
     */
    public function addMemberTags($list_id, $email, $tags)
    {
        $email_hash = md5(strtolower($email));
        
        $tag_data = array(
            'tags' => array_map(function($tag) {
                return array('name' => $tag, 'status' => 'active');
            }, $tags)
        );
        
        $response = $this->makeRequest('POST', "lists/{$list_id}/members/{$email_hash}/tags", $tag_data);
        
        if ($response['success']) {
            return array('success' => true);
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Remove tags from member
     * 
     * @param string $list_id List ID
     * @param string $email Email address
     * @param array $tags Tags to remove
     * @return array Result
     */
    public function removeMemberTags($list_id, $email, $tags)
    {
        $email_hash = md5(strtolower($email));
        
        $tag_data = array(
            'tags' => array_map(function($tag) {
                return array('name' => $tag, 'status' => 'inactive');
            }, $tags)
        );
        
        $response = $this->makeRequest('POST', "lists/{$list_id}/members/{$email_hash}/tags", $tag_data);
        
        if ($response['success']) {
            return array('success' => true);
        } else {
            return array('success' => false, 'error' => $response['error']);
        }
    }
    
    /**
     * Make HTTP request to Mailchimp API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param string $api_key Override API key
     * @return array Response data
     */
    private function makeRequest($method, $endpoint, $data = null, $api_key = null)
    {
        $use_api_key = $api_key ?: $this->api_key;
        
        if (!$use_api_key) {
            return array('success' => false, 'error' => 'API key not configured');
        }
        
        // Build URL
        if (strpos($endpoint, 'http') === 0) {
            $url = $endpoint;
        } else {
            if (!$this->api_base_url) {
                return array('success' => false, 'error' => 'Server prefix not configured');
            }
            $url = $this->api_base_url . $endpoint;
        }
        
        // Add query parameters for GET requests
        if ($method === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
            $data = null;
        }
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $use_api_key);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        
        // Set method and data
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        // Execute request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($curl_error) {
            return array('success' => false, 'error' => 'cURL error: ' . $curl_error);
        }
        
        // Parse JSON response
        $decoded_response = json_decode($response, true);
        
        // Handle HTTP errors
        if ($http_code >= 400) {
            $error_message = 'HTTP ' . $http_code;
            if (isset($decoded_response['detail'])) {
                $error_message .= ': ' . $decoded_response['detail'];
            } elseif (isset($decoded_response['title'])) {
                $error_message .= ': ' . $decoded_response['title'];
            }
            return array('success' => false, 'error' => $error_message);
        }
        
        return array('success' => true, 'data' => $decoded_response);
    }
}
?>
