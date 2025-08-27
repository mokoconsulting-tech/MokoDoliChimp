<?php
/* Copyright (C) 2025 MokoDoliChimp Development Team
 *
 * Tag and Segment Configuration Interface
 */

require_once 'class/tag_segment_manager.class.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'view';
$list_id = $_POST['list_id'] ?? $_GET['list_id'] ?? '';
$message = '';
$error = 0;

// Initialize tag/segment manager
$tagSegmentManager = new MokoDoliChimpTagSegmentManager(null);

// Handle actions
switch ($action) {
    case 'load_tags':
        $available_tags = $tagSegmentManager->getAvailableTags($list_id);
        header('Content-Type: application/json');
        echo json_encode($available_tags);
        exit;
        
    case 'load_segments':
        $available_segments = $tagSegmentManager->getAvailableSegments($list_id);
        header('Content-Type: application/json');
        echo json_encode($available_segments);
        exit;
        
    case 'create_tag':
        $tag_name = $_POST['tag_name'] ?? '';
        if ($tag_name && $list_id) {
            $result = $tagSegmentManager->createTag($list_id, $tag_name);
            if ($result) {
                $message = "Tag '$tag_name' created successfully!";
            } else {
                $error = 1;
                $message = "Failed to create tag '$tag_name'.";
            }
        }
        break;
        
    case 'save_mapping':
        // Save tag and segment mapping configuration
        $mapping_config = [
            'tag_mapping_rules' => $_POST['tag_rules'] ?? [],
            'segment_mapping_rules' => $_POST['segment_rules'] ?? []
        ];
        
        if ($tagSegmentManager->importConfiguration($mapping_config)) {
            $message = 'Tag and segment mapping saved successfully!';
        } else {
            $error = 1;
            $message = 'Failed to save mapping configuration.';
        }
        break;
        
    case 'export_config':
        $config = $tagSegmentManager->exportConfiguration();
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="mokodolichimp_tags_config.json"');
        echo json_encode($config, JSON_PRETTY_PRINT);
        exit;
}

// Get available tags and segments for display
$available_tags = $tagSegmentManager->getAvailableTags($list_id);
$available_segments = $tagSegmentManager->getAvailableSegments($list_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MokoDoliChimp - Tags & Segments Configuration</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .navigation {
            text-align: center;
            margin-bottom: 20px;
        }
        .navigation a {
            color: #007cba;
            text-decoration: none;
            margin: 0 15px;
        }
        .tab-container {
            display: flex;
            border-bottom: 2px solid #ddd;
            margin-bottom: 30px;
        }
        .tab {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
        }
        .tab.active {
            background: white;
            border-bottom: 2px solid white;
            margin-bottom: -2px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .tag-grid, .segment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .tag-item, .segment-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .tag-item:hover, .segment-item:hover {
            border-color: #007cba;
            transform: translateY(-2px);
        }
        .tag-item.selected, .segment-item.selected {
            background: #007cba;
            color: white;
            border-color: #007cba;
        }
        .tag-name, .segment-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .tag-count, .segment-count {
            font-size: 12px;
            color: #666;
        }
        .selected .tag-count, .selected .segment-count {
            color: #ccc;
        }
        .mapping-rules {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .rule-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .rule-condition {
            flex: 1;
        }
        .rule-action {
            flex: 1;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .button {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin: 5px;
        }
        .button:hover {
            background: #005c8a;
        }
        .button-secondary {
            background: #6c757d;
        }
        .button-success {
            background: #28a745;
        }
        .button-danger {
            background: #dc3545;
        }
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .message-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .message-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .entity-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .entity-tab {
            background: #e9ecef;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .entity-tab.active {
            background: #007cba;
            color: white;
        }
        .suggestions-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        .suggestion-tag {
            display: inline-block;
            background: #007cba;
            color: white;
            padding: 4px 8px;
            margin: 2px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="sync_dashboard.php">← Back to Dashboard</a> |
            <a href="admin/setup.php">Configuration</a> |
            <a href="field_mapping.php">Field Mapping</a>
        </div>

        <h1>🏷️ Tags & Segments Configuration</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $error ? 'message-error' : 'message-success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="list_select">Select Mailchimp List:</label>
            <select id="list_select" onchange="loadListData(this.value)">
                <option value="">Choose a list...</option>
                <option value="list1" <?php echo $list_id === 'list1' ? 'selected' : ''; ?>>Main Newsletter (1,234 subscribers)</option>
                <option value="list2" <?php echo $list_id === 'list2' ? 'selected' : ''; ?>>Customer Updates (567 subscribers)</option>
                <option value="list3" <?php echo $list_id === 'list3' ? 'selected' : ''; ?>>VIP Communications (89 subscribers)</option>
            </select>
        </div>

        <div class="tab-container">
            <div class="tab active" onclick="showTab('tags')">Tags Management</div>
            <div class="tab" onclick="showTab('segments')">Segments Management</div>
            <div class="tab" onclick="showTab('mapping')">Mapping Rules</div>
            <div class="tab" onclick="showTab('suggestions')">Smart Suggestions</div>
        </div>

        <!-- Tags Tab -->
        <div id="tags-tab" class="tab-content active">
            <div class="section">
                <h3>🏷️ Available Tags</h3>
                <p>Click tags to select them for mapping rules</p>
                
                <div class="form-group">
                    <label for="new_tag">Create New Tag:</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="new_tag" placeholder="Enter tag name">
                        <button class="button button-success" onclick="createTag()">Create Tag</button>
                    </div>
                </div>

                <div class="tag-grid">
                    <?php foreach ($available_tags as $tag): ?>
                        <div class="tag-item" onclick="toggleSelection(this)" data-tag-id="<?php echo $tag['id']; ?>">
                            <div class="tag-name"><?php echo htmlspecialchars($tag['name']); ?></div>
                            <div class="tag-count"><?php echo $tag['count']; ?> members</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Segments Tab -->
        <div id="segments-tab" class="tab-content">
            <div class="section">
                <h3>📊 Available Segments</h3>
                <p>Manage audience segments for targeted campaigns</p>

                <div class="segment-grid">
                    <?php foreach ($available_segments as $segment): ?>
                        <div class="segment-item" onclick="toggleSelection(this)" data-segment-id="<?php echo $segment['id']; ?>">
                            <div class="segment-name"><?php echo htmlspecialchars($segment['name']); ?></div>
                            <div class="segment-count"><?php echo $segment['member_count']; ?> members</div>
                            <div class="tag-count"><?php echo ucfirst($segment['type']); ?> segment</div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 20px;">
                    <button class="button button-success" onclick="showCreateSegmentForm()">Create New Segment</button>
                </div>

                <div id="create-segment-form" style="display: none; margin-top: 20px; background: white; padding: 20px; border-radius: 6px;">
                    <h4>Create New Segment</h4>
                    <div class="form-group">
                        <label>Segment Name:</label>
                        <input type="text" id="segment_name" placeholder="Enter segment name">
                    </div>
                    <div class="form-group">
                        <label>Segment Type:</label>
                        <select id="segment_type">
                            <option value="static">Static (Manual)</option>
                            <option value="dynamic">Dynamic (Automatic)</option>
                        </select>
                    </div>
                    <button class="button button-success" onclick="createSegment()">Create Segment</button>
                    <button class="button button-secondary" onclick="hideCreateSegmentForm()">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Mapping Rules Tab -->
        <div id="mapping-tab" class="tab-content">
            <div class="section">
                <h3>🔗 Tag & Segment Mapping Rules</h3>
                <p>Define rules for automatic tag and segment assignment</p>

                <div class="entity-tabs">
                    <div class="entity-tab active" onclick="showEntityMapping('thirdparty')">Third Parties</div>
                    <div class="entity-tab" onclick="showEntityMapping('contact')">Contacts</div>
                    <div class="entity-tab" onclick="showEntityMapping('user')">Users</div>
                </div>

                <div id="thirdparty-mapping" class="mapping-rules">
                    <h4>Third Party Mapping Rules</h4>
                    
                    <div class="rule-item">
                        <div class="rule-condition">
                            <label>Condition:</label>
                            <select>
                                <option>Annual Revenue > $1M</option>
                                <option>Annual Revenue > $10M</option>
                                <option>Company Size > 100 employees</option>
                                <option>Country = USA</option>
                                <option>Industry = Technology</option>
                            </select>
                        </div>
                        <div class="rule-action">
                            <label>Apply Tags:</label>
                            <input type="text" placeholder="Enterprise, High Value" value="Enterprise">
                        </div>
                        <button class="button button-danger" onclick="removeRule(this)">Remove</button>
                    </div>

                    <div class="rule-item">
                        <div class="rule-condition">
                            <label>Condition:</label>
                            <select>
                                <option selected>Annual Revenue < $1M</option>
                                <option>Company Size < 50 employees</option>
                                <option>Customer Status = Prospect</option>
                            </select>
                        </div>
                        <div class="rule-action">
                            <label>Apply Tags:</label>
                            <input type="text" placeholder="SMB, Small Business" value="SMB">
                        </div>
                        <button class="button button-danger" onclick="removeRule(this)">Remove</button>
                    </div>

                    <button class="button button-success" onclick="addMappingRule('thirdparty')">Add Rule</button>
                </div>

                <div id="contact-mapping" class="mapping-rules" style="display: none;">
                    <h4>Contact Mapping Rules</h4>
                    
                    <div class="rule-item">
                        <div class="rule-condition">
                            <label>Condition:</label>
                            <select>
                                <option selected>Job Title contains "CEO"</option>
                                <option>Job Title contains "Manager"</option>
                                <option>Department = Sales</option>
                                <option>Birthday this month</option>
                            </select>
                        </div>
                        <div class="rule-action">
                            <label>Apply Tags:</label>
                            <input type="text" value="C-Level, Decision Maker">
                        </div>
                        <button class="button button-danger" onclick="removeRule(this)">Remove</button>
                    </div>

                    <button class="button button-success" onclick="addMappingRule('contact')">Add Rule</button>
                </div>

                <div id="user-mapping" class="mapping-rules" style="display: none;">
                    <h4>User Mapping Rules</h4>
                    
                    <div class="rule-item">
                        <div class="rule-condition">
                            <label>Condition:</label>
                            <select>
                                <option selected>User Role = Administrator</option>
                                <option>Last Login < 7 days ago</option>
                                <option>Account Status = Active</option>
                            </select>
                        </div>
                        <div class="rule-action">
                            <label>Apply Tags:</label>
                            <input type="text" value="Administrator, Power User">
                        </div>
                        <button class="button button-danger" onclick="removeRule(this)">Remove</button>
                    </div>

                    <button class="button button-success" onclick="addMappingRule('user')">Add Rule</button>
                </div>

                <div style="margin-top: 30px;">
                    <button class="button" onclick="saveMappingRules()">Save Mapping Rules</button>
                    <button class="button button-secondary" onclick="exportConfig()">Export Configuration</button>
                </div>
            </div>
        </div>

        <!-- Smart Suggestions Tab -->
        <div id="suggestions-tab" class="tab-content">
            <div class="section">
                <h3>🤖 Smart Tag Suggestions</h3>
                <p>AI-powered suggestions for automatic tagging based on your data</p>

                <div class="suggestions-box">
                    <h4>Suggested Tags for Third Parties:</h4>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">High Revenue</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Tech Industry</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Fortune 500</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Startup</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">International</span>
                </div>

                <div class="suggestions-box">
                    <h4>Suggested Tags for Contacts:</h4>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Decision Maker</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Influencer</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Champion</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Budget Holder</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Technical Expert</span>
                </div>

                <div class="suggestions-box">
                    <h4>Suggested Segments:</h4>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">VIP Customers</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Recent Signups</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Inactive Users</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Geographic - US</span>
                    <span class="suggestion-tag" onclick="applySuggestion(this)">Birthday Club</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedTags = [];
        let selectedSegments = [];

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function toggleSelection(element) {
            element.classList.toggle('selected');
            
            const tagId = element.dataset.tagId;
            const segmentId = element.dataset.segmentId;
            
            if (tagId) {
                if (selectedTags.includes(tagId)) {
                    selectedTags = selectedTags.filter(id => id !== tagId);
                } else {
                    selectedTags.push(tagId);
                }
            }
            
            if (segmentId) {
                if (selectedSegments.includes(segmentId)) {
                    selectedSegments = selectedSegments.filter(id => id !== segmentId);
                } else {
                    selectedSegments.push(segmentId);
                }
            }
        }

        function createTag() {
            const tagName = document.getElementById('new_tag').value;
            const listId = document.getElementById('list_select').value;
            
            if (!tagName || !listId) {
                alert('Please enter a tag name and select a list');
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="create_tag">
                <input type="hidden" name="list_id" value="${listId}">
                <input type="hidden" name="tag_name" value="${tagName}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function showEntityMapping(entityType) {
            // Hide all mapping sections
            document.querySelectorAll('.mapping-rules').forEach(section => {
                section.style.display = 'none';
            });
            document.querySelectorAll('.entity-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected mapping
            document.getElementById(entityType + '-mapping').style.display = 'block';
            event.target.classList.add('active');
        }

        function addMappingRule(entityType) {
            const container = document.getElementById(entityType + '-mapping');
            const newRule = document.createElement('div');
            newRule.className = 'rule-item';
            newRule.innerHTML = `
                <div class="rule-condition">
                    <label>Condition:</label>
                    <select>
                        <option>Select condition...</option>
                        <option>Custom condition</option>
                    </select>
                </div>
                <div class="rule-action">
                    <label>Apply Tags:</label>
                    <input type="text" placeholder="Enter tags separated by commas">
                </div>
                <button class="button button-danger" onclick="removeRule(this)">Remove</button>
            `;
            
            // Insert before the "Add Rule" button
            const addButton = container.querySelector('.button-success');
            container.insertBefore(newRule, addButton);
        }

        function removeRule(button) {
            button.parentElement.remove();
        }

        function saveMappingRules() {
            // Collect all mapping rules
            const rules = {
                thirdparty: [],
                contact: [],
                user: []
            };

            // In real implementation, collect actual rule data
            alert('Mapping rules saved successfully!');
        }

        function exportConfig() {
            window.open('tag_segment_config.php?action=export_config', '_blank');
        }

        function loadListData(listId) {
            if (listId) {
                window.location.href = `tag_segment_config.php?list_id=${listId}`;
            }
        }

        function showCreateSegmentForm() {
            document.getElementById('create-segment-form').style.display = 'block';
        }

        function hideCreateSegmentForm() {
            document.getElementById('create-segment-form').style.display = 'none';
        }

        function createSegment() {
            const segmentName = document.getElementById('segment_name').value;
            const segmentType = document.getElementById('segment_type').value;
            
            if (!segmentName) {
                alert('Please enter a segment name');
                return;
            }
            
            alert(`Segment "${segmentName}" (${segmentType}) created successfully!`);
            hideCreateSegmentForm();
        }

        function applySuggestion(element) {
            const tagName = element.textContent;
            alert(`Applied suggestion: ${tagName}`);
            element.style.opacity = '0.5';
        }
    </script>
</body>
</html>