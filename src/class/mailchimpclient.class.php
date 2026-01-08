<?php
/* Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>
 *
 * This file is part of a Moko Consulting project.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * FILE INFORMATION
 * DEFGROUP: MokoDoliChimp.Classes
 * INGROUP: MokoDoliChimp
 * REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
 * PATH: /src/class/mailchimpclient.class.php
 * VERSION: 01.00.00
 * BRIEF: Mailchimp API client for syncing contacts and users to Mailchimp lists
 */

/**
 * Mailchimp API client
 */
class MailchimpClient
{
	/**
	 * @var string Mailchimp API key
	 */
	private $apiKey;

	/**
	 * @var string Mailchimp server prefix
	 */
	private $serverPrefix;

	/**
	 * @var string Mailchimp list/audience ID
	 */
	private $listId;

	/**
	 * @var string Default subscription status
	 */
	private $defaultStatus;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 *
	 * @param string $apiKey Mailchimp API key
	 * @param string $listId Mailchimp list/audience ID
	 * @param string $status Default subscription status
	 */
	public function __construct($apiKey = '', $listId = '', $status = 'subscribed')
	{
		global $conf;

		$this->apiKey = $apiKey ? $apiKey : getDolGlobalString('MOKODOLICHIMP_APIKEY');
		$this->listId = $listId ? $listId : getDolGlobalString('MOKODOLICHIMP_LISTID');
		$this->defaultStatus = $status ? $status : getDolGlobalString('MOKODOLICHIMP_STATUS', 'subscribed');
		
		if ($this->apiKey && strpos($this->apiKey, '-') !== false) {
			$parts = explode('-', $this->apiKey);
			$this->serverPrefix = end($parts);
		} else {
			$this->serverPrefix = getDolGlobalString('MOKODOLICHIMP_SERVER_PREFIX');
		}
	}

	/**
	 * Check if Mailchimp is properly configured
	 *
	 * @return bool True if configured, false otherwise
	 */
	public function isConfigured()
	{
		return !empty($this->apiKey) && !empty($this->listId) && !empty($this->serverPrefix);
	}

	/**
	 * Sync a contact or user to Mailchimp
	 *
	 * @param string $email Email address
	 * @param string $firstName First name
	 * @param string $lastName Last name
	 * @param array $additionalFields Additional merge fields
	 * @param string $status Subscription status (subscribed, pending, etc.)
	 * @return bool True on success, false on error
	 */
	public function syncSubscriber($email, $firstName = '', $lastName = '', $additionalFields = array(), $status = null)
	{
		if (!$this->isConfigured()) {
			$this->errors[] = 'Mailchimp is not properly configured';
			return false;
		}

		if (empty($email)) {
			$this->errors[] = 'Email address is required';
			return false;
		}

		$subscriberHash = md5(strtolower($email));
		$url = sprintf(
			'https://%s.api.mailchimp.com/3.0/lists/%s/members/%s',
			$this->serverPrefix,
			$this->listId,
			$subscriberHash
		);

		$mergeFields = array();
		if ($firstName) {
			$mergeFields['FNAME'] = $firstName;
		}
		if ($lastName) {
			$mergeFields['LNAME'] = $lastName;
		}
		$mergeFields = array_merge($mergeFields, $additionalFields);

		$data = array(
			'email_address' => $email,
			'status_if_new' => $status ? $status : $this->defaultStatus,
		);

		if (!empty($mergeFields)) {
			$data['merge_fields'] = $mergeFields;
		}

		$result = $this->makeRequest($url, 'PUT', $data);
		
		return $result !== false;
	}

	/**
	 * Unsubscribe a contact from Mailchimp
	 *
	 * @param string $email Email address
	 * @return bool True on success, false on error
	 */
	public function unsubscribeSubscriber($email)
	{
		if (!$this->isConfigured()) {
			$this->errors[] = 'Mailchimp is not properly configured';
			return false;
		}

		if (empty($email)) {
			$this->errors[] = 'Email address is required';
			return false;
		}

		$subscriberHash = md5(strtolower($email));
		$url = sprintf(
			'https://%s.api.mailchimp.com/3.0/lists/%s/members/%s',
			$this->serverPrefix,
			$this->listId,
			$subscriberHash
		);

		$data = array(
			'status' => 'unsubscribed'
		);

		$result = $this->makeRequest($url, 'PATCH', $data);
		
		return $result !== false;
	}

	/**
	 * Make an API request to Mailchimp
	 *
	 * @param string $url API endpoint URL
	 * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
	 * @param array $data Request data
	 * @return mixed Response data on success, false on error
	 */
	private function makeRequest($url, $method = 'GET', $data = null)
	{
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_USERPWD, 'user:'.$this->apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		
		if ($data !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		if ($curlError) {
			$this->errors[] = 'cURL error: '.$curlError;
			return false;
		}

		$responseData = json_decode($response, true);

		if ($httpCode >= 200 && $httpCode < 300) {
			return $responseData;
		} else {
			$errorMessage = 'HTTP '.$httpCode;
			if (isset($responseData['title'])) {
				$errorMessage .= ': '.$responseData['title'];
			}
			if (isset($responseData['detail'])) {
				$errorMessage .= ' - '.$responseData['detail'];
			}
			$this->errors[] = $errorMessage;
			return false;
		}
	}

	/**
	 * Get the last error message
	 *
	 * @return string Last error message
	 */
	public function getLastError()
	{
		return !empty($this->errors) ? end($this->errors) : '';
	}

	/**
	 * Get all error messages
	 *
	 * @return array All error messages
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}
