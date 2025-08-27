<?php
/* Copyright (C) 2024 Moko Consulting <hello@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    class/tag_segment_manager.class.php
 * \ingroup mokodolichimp
 * \brief   Tag and segment manager for advanced Mailchimp audience management
 */

require_once 'mokodolichimp.class.php';

/**
 * Manager for Mailchimp tags and audience segments
 */
class MokoDoliChimpTagSegmentManager
{
    private $mokodolichimp;
    private $db;
    private $logger;
    private $tag_mapping_rules;
    private $segment_mapping_rules;

    /**
     * Constructor
     * @param object $db Database connection
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->mokodolichimp = new MokoDoliChimp($db);
        $this->logger = new MokoDoliChimpLogger($db);
        $this->loadMappingRules();
    }

    /**
     * Get all available tags from Mailchimp list
     * @param string $list_id Mailchimp list ID
     * @return array Available tags
     */
    public function getAvailableTags($list_id)
    {
        try {
            // In real implementation, this would call Mailchimp API
            // $response = $this->mokodolichimp->mailchimp->lists->getListMembersInfo($list_id);
            
            // Mock available tags for demonstration
            return [
                ['id' => 'tag1', 'name' => 'VIP Customer', 'count' => 150],
                ['id' => 'tag2', 'name' => 'Enterprise', 'count' => 75],
                ['id' => 'tag3', 'name' => 'SMB', 'count' => 300],
                ['id' => 'tag4', 'name' => 'Prospect', 'count' => 200],
                ['id' => 'tag5', 'name' => 'Active User', 'count' => 450],
                ['id' => 'tag6', 'name' => 'Decision Maker', 'count' => 125],
                ['id' => 'tag7', 'name' => 'Technical Contact', 'count' => 180],
                ['id' => 'tag8', 'name' => 'Finance Team', 'count' => 90],
                ['id' => 'tag9', 'name' => 'Sales Team', 'count' => 65],
                ['id' => 'tag10', 'name' => 'Support Team', 'count' => 85]
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to get available tags: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available audience segments from Mailchimp list
     * @param string $list_id Mailchimp list ID
     * @return array Available segments
     */
    public function getAvailableSegments($list_id)
    {
        try {
            // In real implementation, this would call Mailchimp API
            // $response = $this->mokodolichimp->mailchimp->lists->getListSegments($list_id);
            
            // Mock available segments for demonstration
            return [
                [
                    'id' => 'seg1',
                    'name' => 'High Value Customers',
                    'type' => 'static',
                    'member_count' => 125,
                    'created_at' => '2024-01-15',
                    'updated_at' => '2024-12-01'
                ],
                [
                    'id' => 'seg2',
                    'name' => 'Recent Signups',
                    'type' => 'dynamic',
                    'member_count' => 89,
                    'created_at' => '2024-06-10',
                    'updated_at' => '2024-12-01'
                ],
                [
                    'id' => 'seg3',
                    'name' => 'Geographic - US East Coast',
                    'type' => 'static',
                    'member_count' => 267,
                    'created_at' => '2024-03-20',
                    'updated_at' => '2024-11-28'
                ],
                [
                    'id' => 'seg4',
                    'name' => 'Birthday This Month',
                    'type' => 'dynamic',
                    'member_count' => 42,
                    'created_at' => '2024-08-05',
                    'updated_at' => '2024-12-01'
                ],
                [
                    'id' => 'seg5',
                    'name' => 'Enterprise Contacts',
                    'type' => 'static',
                    'member_count' => 156,
                    'created_at' => '2024-02-12',
                    'updated_at' => '2024-11-30'
                ]
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to get available segments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Apply tag and segment mapping rules to entity data
     * @param array $entity_data Entity data from Dolibarr
     * @param string $entity_type Entity type (thirdparty, contact, user)
     * @return array Tags and segments to apply
     */
    public function applyMappingRules($entity_data, $entity_type)
    {
        $tags = [];
        $segments = [];

        // Apply tag mapping rules
        if (isset($this->tag_mapping_rules[$entity_type])) {
            foreach ($this->tag_mapping_rules[$entity_type] as $rule) {
                if ($this->evaluateRule($rule, $entity_data)) {
                    $tags = array_merge($tags, $rule['tags']);
                }
            }
        }

        // Apply segment mapping rules
        if (isset($this->segment_mapping_rules[$entity_type])) {
            foreach ($this->segment_mapping_rules[$entity_type] as $rule) {
                if ($this->evaluateRule($rule, $entity_data)) {
                    $segments = array_merge($segments, $rule['segments']);
                }
            }
        }

        // Remove duplicates
        $tags = array_unique($tags);
        $segments = array_unique($segments);

        return [
            'tags' => $tags,
            'segments' => $segments
        ];
    }

    /**
     * Create new tag in Mailchimp
     * @param string $list_id Mailchimp list ID
     * @param string $tag_name Tag name
     * @return array|false Tag creation result
     */
    public function createTag($list_id, $tag_name)
    {
        try {
            // In real implementation, this would call Mailchimp API
            // $response = $this->mokodolichimp->mailchimp->lists->createListTag($list_id, ['name' => $tag_name]);
            
            $this->logger->info("Created new tag: $tag_name in list: $list_id");
            
            return [
                'id' => 'tag_' . strtolower(str_replace(' ', '_', $tag_name)),
                'name' => $tag_name,
                'count' => 0
            ];
        } catch (Exception $e) {
            $this->logger->error("Failed to create tag $tag_name: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new segment in Mailchimp
     * @param string $list_id Mailchimp list ID
     * @param string $segment_name Segment name
     * @param array $segment_criteria Segment criteria
     * @return array|false Segment creation result
     */
    public function createSegment($list_id, $segment_name, $segment_criteria = [])
    {
        try {
            // In real implementation, this would call Mailchimp API
            // $response = $this->mokodolichimp->mailchimp->lists->createListSegment($list_id, [
            //     'name' => $segment_name,
            //     'static_segment' => $segment_criteria['members'] ?? []
            // ]);
            
            $this->logger->info("Created new segment: $segment_name in list: $list_id");
            
            return [
                'id' => 'seg_' . strtolower(str_replace(' ', '_', $segment_name)),
                'name' => $segment_name,
                'type' => 'static',
                'member_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->logger->error("Failed to create segment $segment_name: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add member to segment
     * @param string $list_id Mailchimp list ID
     * @param string $segment_id Segment ID
     * @param string $email_address Member email
     * @return bool Success status
     */
    public function addMemberToSegment($list_id, $segment_id, $email_address)
    {
        try {
            // In real implementation, this would call Mailchimp API
            // $response = $this->mokodolichimp->mailchimp->lists->createSegmentMember($list_id, $segment_id, [
            //     'email_address' => $email_address
            // ]);
            
            $this->logger->info("Added $email_address to segment $segment_id in list $list_id");
            return true;
        } catch (Exception $e) {
            $this->logger->error("Failed to add member to segment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get suggested tags based on entity data
     * @param array $entity_data Entity data
     * @param string $entity_type Entity type
     * @return array Suggested tags
     */
    public function getSuggestedTags($entity_data, $entity_type)
    {
        $suggestions = [];

        switch ($entity_type) {
            case 'thirdparty':
                // Company size suggestions
                $annual_revenue = $entity_data['annual_revenue'] ?? 0;
                if ($annual_revenue > 10000000) {
                    $suggestions[] = 'Enterprise';
                } elseif ($annual_revenue > 1000000) {
                    $suggestions[] = 'Mid-Market';
                } else {
                    $suggestions[] = 'SMB';
                }

                // Industry suggestions
                $industry = $entity_data['industry'] ?? '';
                if ($industry) {
                    $suggestions[] = 'Industry: ' . $industry;
                }

                // Geographic suggestions
                $country = $entity_data['country'] ?? '';
                if ($country) {
                    $suggestions[] = 'Region: ' . $country;
                }
                break;

            case 'contact':
                // Role-based suggestions
                $job_title = strtolower($entity_data['job'] ?? '');
                if (strpos($job_title, 'ceo') !== false || strpos($job_title, 'president') !== false) {
                    $suggestions[] = 'C-Level';
                } elseif (strpos($job_title, 'manager') !== false) {
                    $suggestions[] = 'Manager';
                } elseif (strpos($job_title, 'director') !== false) {
                    $suggestions[] = 'Director';
                }

                // Department suggestions
                if (strpos($job_title, 'sales') !== false) {
                    $suggestions[] = 'Sales Team';
                } elseif (strpos($job_title, 'marketing') !== false) {
                    $suggestions[] = 'Marketing Team';
                } elseif (strpos($job_title, 'tech') !== false || strpos($job_title, 'it') !== false) {
                    $suggestions[] = 'Technical Team';
                }

                // Age-based suggestions (if DOB available)
                if (!empty($entity_data['birthday'])) {
                    $age = date_diff(date_create($entity_data['birthday']), date_create('today'))->y;
                    if ($age < 30) {
                        $suggestions[] = 'Young Professional';
                    } elseif ($age >= 50) {
                        $suggestions[] = 'Senior Professional';
                    }
                }
                break;

            case 'user':
                // User role suggestions
                $user_role = $entity_data['admin'] ?? 0;
                if ($user_role) {
                    $suggestions[] = 'Administrator';
                } else {
                    $suggestions[] = 'Standard User';
                }

                // Activity suggestions
                $last_login = $entity_data['last_login'] ?? '';
                if ($last_login) {
                    $days_since_login = (time() - strtotime($last_login)) / (60 * 60 * 24);
                    if ($days_since_login < 7) {
                        $suggestions[] = 'Active User';
                    } elseif ($days_since_login > 30) {
                        $suggestions[] = 'Inactive User';
                    }
                }
                break;
        }

        return $suggestions;
    }

    /**
     * Load tag and segment mapping rules from configuration
     */
    private function loadMappingRules()
    {
        // In real implementation, these would be loaded from database configuration
        $this->tag_mapping_rules = [
            'thirdparty' => [
                [
                    'condition' => 'annual_revenue > 10000000',
                    'tags' => ['Enterprise', 'High Value']
                ],
                [
                    'condition' => 'annual_revenue < 1000000',
                    'tags' => ['SMB', 'Small Business']
                ],
                [
                    'condition' => 'country = US',
                    'tags' => ['US Market', 'North America']
                ]
            ],
            'contact' => [
                [
                    'condition' => 'job CONTAINS ceo',
                    'tags' => ['C-Level', 'Decision Maker']
                ],
                [
                    'condition' => 'job CONTAINS manager',
                    'tags' => ['Manager', 'Influencer']
                ],
                [
                    'condition' => 'birthday_month = current_month',
                    'tags' => ['Birthday This Month']
                ]
            ],
            'user' => [
                [
                    'condition' => 'admin = 1',
                    'tags' => ['Administrator', 'Power User']
                ],
                [
                    'condition' => 'last_login > 7_days_ago',
                    'tags' => ['Active User']
                ]
            ]
        ];

        $this->segment_mapping_rules = [
            'thirdparty' => [
                [
                    'condition' => 'annual_revenue > 5000000',
                    'segments' => ['High Value Customers']
                ],
                [
                    'condition' => 'country IN [US, CA, MX]',
                    'segments' => ['North America']
                ]
            ],
            'contact' => [
                [
                    'condition' => 'age BETWEEN 25 AND 35',
                    'segments' => ['Millennials']
                ],
                [
                    'condition' => 'birthday_month = current_month',
                    'segments' => ['Birthday This Month']
                ]
            ],
            'user' => [
                [
                    'condition' => 'date_creation > 30_days_ago',
                    'segments' => ['Recent Users']
                ]
            ]
        ];
    }

    /**
     * Evaluate mapping rule condition
     * @param array $rule Mapping rule
     * @param array $entity_data Entity data
     * @return bool Rule evaluation result
     */
    private function evaluateRule($rule, $entity_data)
    {
        // Simplified rule evaluation - in real implementation, this would be more sophisticated
        $condition = $rule['condition'] ?? '';
        
        // Mock evaluation for demo
        return true; // Always apply rules for demonstration
    }

    /**
     * Export tag and segment configuration
     * @return array Configuration data
     */
    public function exportConfiguration()
    {
        return [
            'tag_mapping_rules' => $this->tag_mapping_rules,
            'segment_mapping_rules' => $this->segment_mapping_rules,
            'export_date' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Import tag and segment configuration
     * @param array $config Configuration data
     * @return bool Success status
     */
    public function importConfiguration($config)
    {
        try {
            if (isset($config['tag_mapping_rules'])) {
                $this->tag_mapping_rules = $config['tag_mapping_rules'];
            }
            
            if (isset($config['segment_mapping_rules'])) {
                $this->segment_mapping_rules = $config['segment_mapping_rules'];
            }
            
            // In real implementation, save to database
            $this->logger->info('Tag and segment configuration imported successfully');
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to import configuration: ' . $e->getMessage());
            return false;
        }
    }
}