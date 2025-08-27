<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    core/triggers/interface_99_modMokoDoliChimp_MokoDoliChimpTrigger.class.php
 * \ingroup mokodolichimp
 * \brief   Trigger file for MokoDoliChimp module
 *
 * This trigger automatically syncs entities to Mailchimp when they are modified in Dolibarr
 */

// Mock Dolibarr trigger base class for standalone testing
if (!class_exists('DolibarrTriggers')) {
    class DolibarrTriggers {
        protected $db;
        protected $name;
        protected $family;
        protected $description;
        protected $version;
        protected $picto;
    }
}

/**
 * Class for MokoDoliChimp triggers
 */
class InterfaceMokoDoliChimpTrigger extends DolibarrTriggers
{
    /**
     * @var DoliDB Database handler
     */
    protected $db;

    /**
     * Constructor
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "MokoDoliChimp triggers to automatically sync entities to Mailchimp";
        $this->version = '1.0.0';
        $this->picto = 'mokodolichimp@mokodolichimp';
    }

    /**
     * Trigger function
     *
     * @param string $action Event action code
     * @param object $object Object
     * @param User $user Object user
     * @param Translate $langs Object langs
     * @param Conf $conf Object conf
     * @return int <0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function runTrigger($action, $object, $user, $langs, $conf)
    {
        if (empty($conf->mokodolichimp->enabled)) {
            return 0; // Module not enabled
        }

        // Check if real-time sync is enabled
        if (!$this->isRealTimeSyncEnabled($conf)) {
            return 0; // Real-time sync disabled
        }

        // Define supported triggers and their corresponding entity types
        $supported_triggers = [
            // Third party triggers
            'COMPANY_CREATE' => ['entity_type' => 'thirdparty', 'action' => 'create'],
            'COMPANY_MODIFY' => ['entity_type' => 'thirdparty', 'action' => 'update'],
            'COMPANY_DELETE' => ['entity_type' => 'thirdparty', 'action' => 'delete'],
            
            // Contact triggers
            'CONTACT_CREATE' => ['entity_type' => 'contact', 'action' => 'create'],
            'CONTACT_MODIFY' => ['entity_type' => 'contact', 'action' => 'update'],
            'CONTACT_DELETE' => ['entity_type' => 'contact', 'action' => 'delete'],
            
            // User triggers
            'USER_CREATE' => ['entity_type' => 'user', 'action' => 'create'],
            'USER_MODIFY' => ['entity_type' => 'user', 'action' => 'update'],
            'USER_DELETE' => ['entity_type' => 'user', 'action' => 'delete'],
            'USER_ENABLEDISABLE' => ['entity_type' => 'user', 'action' => 'update'],
        ];

        if (!isset($supported_triggers[$action])) {
            return 0; // Action not supported
        }

        $trigger_info = $supported_triggers[$action];
        
        try {
            // Load sync manager
            require_once __DIR__ . '/../../class/sync_manager.class.php';
            $syncManager = new MokoDoliChimpSyncManager($this->db);

            // Perform real-time sync
            $result = $syncManager->realTimeSync(
                $trigger_info['entity_type'],
                $object->id,
                $trigger_info['action']
            );

            // Log the trigger execution
            $this->logTriggerExecution($action, $object, $result);

            return 1; // Success

        } catch (Exception $e) {
            // Log error
            // Log error (in real Dolibarr: dol_syslog)
            error_log("MokoDoliChimp Trigger Error: " . $e->getMessage());
            
            // Don't fail the main operation if sync fails
            return 0;
        }
    }

    /**
     * Check if real-time sync is enabled
     * @param Conf $conf Configuration object
     * @return bool
     */
    private function isRealTimeSyncEnabled($conf)
    {
        // In real implementation, check configuration
        // return !empty($conf->global->MOKODOLICHIMP_REALTIME_SYNC);
        
        // For demo, check if auto sync is enabled
        return true; // Mock return
    }

    /**
     * Log trigger execution for debugging and monitoring
     * @param string $action Trigger action
     * @param object $object Object that triggered the event
     * @param array $result Sync result
     */
    private function logTriggerExecution($action, $object, $result)
    {
        $message = sprintf(
            "MokoDoliChimp Trigger: %s on %s (ID: %d) - Status: %s",
            $action,
            get_class($object),
            $object->id,
            $result['status'] ?? 'unknown'
        );

        // Log message (in real Dolibarr: dol_syslog)
        error_log($message);

        // In real implementation, could also log to custom table for detailed monitoring
        // $this->logToCustomTable($action, $object, $result);
    }

    /**
     * Get entity data for sync based on object type
     * @param object $object Dolibarr object
     * @return array|null Entity data for sync
     */
    private function getEntityDataForSync($object)
    {
        $entity_data = null;

        switch (get_class($object)) {
            case 'Societe':
                $entity_data = [
                    'id' => $object->id,
                    'name' => $object->name,
                    'email' => $object->email,
                    'phone' => $object->phone,
                    'address' => $object->address,
                    'zip' => $object->zip,
                    'town' => $object->town,
                    'country' => $object->country,
                    'date_creation' => $object->date_creation,
                    'date_modification' => $object->date_modification
                ];
                break;

            case 'Contact':
                $entity_data = [
                    'id' => $object->id,
                    'firstname' => $object->firstname,
                    'lastname' => $object->lastname,
                    'email' => $object->email,
                    'phone' => $object->phone,
                    'phone_mobile' => $object->phone_mobile,
                    'birthday' => $object->birthday,
                    'address' => $object->address,
                    'zip' => $object->zip,
                    'town' => $object->town,
                    'country' => $object->country,
                    'fk_soc' => $object->fk_soc,
                    'date_creation' => $object->date_creation,
                    'date_modification' => $object->date_modification
                ];
                break;

            case 'User':
                $entity_data = [
                    'id' => $object->id,
                    'login' => $object->login,
                    'firstname' => $object->firstname,
                    'lastname' => $object->lastname,
                    'email' => $object->email,
                    'office_phone' => $object->office_phone,
                    'user_mobile' => $object->user_mobile,
                    'birth' => $object->birth,
                    'job' => $object->job,
                    'address' => $object->address,
                    'zip' => $object->zip,
                    'town' => $object->town,
                    'country' => $object->country,
                    'date_creation' => $object->date_creation,
                    'date_modification' => $object->date_modification
                ];
                break;
        }

        return $entity_data;
    }
}