/**
 * Mailchimp Sync Module JavaScript
 * 
 * @file        mailchimpsync.js
 * @ingroup     mailchimpsync
 * @brief       JavaScript functions for Mailchimp sync module
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.
 */

$(document).ready(function() {
    initializeMailchimpSync();
});

/**
 * Initialize Mailchimp sync functionality
 */
function initializeMailchimpSync() {
    // Auto-refresh dashboard every 30 seconds
    if (window.location.pathname.includes('dashboard.php')) {
        setInterval(refreshDashboardStats, 30000);
    }
    
    // Initialize field mapping interface
    if (window.location.pathname.includes('fieldmapping.php')) {
        initializeFieldMapping();
    }
    
    // Initialize sync history filters
    if (window.location.pathname.includes('synchistory.php')) {
        initializeSyncHistory();
    }
    
    // Initialize connection testing
    initializeConnectionTesting();
    
    // Initialize progress tracking
    initializeProgressTracking();
}

/**
 * Refresh dashboard statistics
 */
function refreshDashboardStats() {
    $.ajax({
        url: 'dashboard.php',
        type: 'GET',
        data: { ajax: 'getstats' },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                updateDashboardStats(data.stats);
            }
        },
        error: function() {
            console.log('Failed to refresh dashboard stats');
        }
    });
}

/**
 * Update dashboard statistics display
 * @param {Object} stats Statistics data
 */
function updateDashboardStats(stats) {
    // Update connection status
    if (stats.connection_status) {
        var statusElement = $('.mailchimpsync-connection-status');
        statusElement.removeClass('mailchimpsync-status-connected mailchimpsync-status-disconnected');
        
        if (stats.connection_status.connected) {
            statusElement.addClass('mailchimpsync-status-connected');
            statusElement.text('Connected');
        } else {
            statusElement.addClass('mailchimpsync-status-disconnected');
            statusElement.text('Disconnected');
        }
    }
    
    // Update sync counts
    if (stats.recent_syncs) {
        $('.mailchimpsync-total-syncs').text(stats.recent_syncs.total);
        $('.mailchimpsync-successful-syncs').text(stats.recent_syncs.successful);
        $('.mailchimpsync-failed-syncs').text(stats.recent_syncs.failed);
        
        // Update success rate
        if (stats.recent_syncs.total > 0) {
            var successRate = Math.round((stats.recent_syncs.successful / stats.recent_syncs.total) * 100);
            $('.mailchimpsync-success-rate').text(successRate + '%');
        }
    }
    
    // Update pending counts
    if (stats.pending_counts) {
        $('.mailchimpsync-pending-thirdparty').text(stats.pending_counts.thirdparty || 0);
        $('.mailchimpsync-pending-contact').text(stats.pending_counts.contact || 0);
        $('.mailchimpsync-pending-user').text(stats.pending_counts.user || 0);
    }
}

/**
 * Initialize field mapping interface
 */
function initializeFieldMapping() {
    // Add change handlers for sync direction
    $('.mailchimpsync-sync-direction').on('change', function() {
        var row = $(this).closest('.mailchimpsync-mapping-row');
        var direction = $(this).val();
        
        // Show/hide warning for bidirectional sync
        if (direction === 'bidirectional') {
            showMappingWarning(row, 'Bidirectional sync may overwrite data in both systems');
        } else {
            hideMappingWarning(row);
        }
    });
    
    // Add validation for email field
    $('.mailchimpsync-mailchimp-field').on('change', function() {
        var field = $(this).val();
        var row = $(this).closest('.mailchimpsync-mapping-row');
        
        if (field === 'EMAIL') {
            var dolField = row.find('.mailchimpsync-dolibarr-field').text().trim();
            if (dolField !== 'email') {
                showMappingWarning(row, 'EMAIL field should be mapped to email field only');
            } else {
                hideMappingWarning(row);
            }
        }
    });
    
    // Auto-save functionality
    var saveTimer;
    $('.mailchimpsync-mapping-form input, .mailchimpsync-mapping-form select').on('change', function() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(autoSaveMappings, 2000);
        showSaveIndicator('Changes will be auto-saved...');
    });
}

/**
 * Show mapping warning
 * @param {jQuery} row Row element
 * @param {string} message Warning message
 */
function showMappingWarning(row, message) {
    var existingWarning = row.find('.mailchimpsync-mapping-warning');
    if (existingWarning.length === 0) {
        var warning = $('<div class="mailchimpsync-mapping-warning mailchimpsync-alert mailchimpsync-alert-warning">' + message + '</div>');
        row.append(warning);
    } else {
        existingWarning.text(message);
    }
}

/**
 * Hide mapping warning
 * @param {jQuery} row Row element
 */
function hideMappingWarning(row) {
    row.find('.mailchimpsync-mapping-warning').remove();
}

/**
 * Auto-save field mappings
 */
function autoSaveMappings() {
    var formData = $('.mailchimpsync-mapping-form').serialize();
    
    $.ajax({
        url: 'fieldmapping.php',
        type: 'POST',
        data: formData + '&action=auto_save',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                showSaveIndicator('Changes saved automatically', 'success');
            } else {
                showSaveIndicator('Auto-save failed: ' + data.error, 'error');
            }
        },
        error: function() {
            showSaveIndicator('Auto-save failed: Network error', 'error');
        }
    });
}

/**
 * Show save indicator
 * @param {string} message Message to display
 * @param {string} type Message type (success, error, info)
 */
function showSaveIndicator(message, type = 'info') {
    var alertClass = 'mailchimpsync-alert-' + type;
    var indicator = $('.mailchimpsync-save-indicator');
    
    if (indicator.length === 0) {
        indicator = $('<div class="mailchimpsync-save-indicator mailchimpsync-alert"></div>');
        $('.mailchimpsync-mapping-form').prepend(indicator);
    }
    
    indicator.removeClass('mailchimpsync-alert-success mailchimpsync-alert-error mailchimpsync-alert-info mailchimpsync-alert-warning');
    indicator.addClass(alertClass);
    indicator.text(message).show();
    
    // Auto-hide success messages
    if (type === 'success') {
        setTimeout(function() {
            indicator.fadeOut();
        }, 3000);
    }
}

/**
 * Initialize sync history functionality
 */
function initializeSyncHistory() {
    // Auto-refresh history every 60 seconds
    setInterval(function() {
        if ($('.mailchimpsync-history-table').length > 0) {
            refreshSyncHistory();
        }
    }, 60000);
    
    // Initialize filter functionality
    $('.mailchimpsync-history-filter').on('change', function() {
        filterSyncHistory();
    });
    
    // Initialize export functionality
    $('.mailchimpsync-export-history').on('click', function(e) {
        e.preventDefault();
        exportSyncHistory();
    });
}

/**
 * Refresh sync history table
 */
function refreshSyncHistory() {
    var currentFilters = $('.mailchimpsync-history-filter').serialize();
    
    $.ajax({
        url: 'synchistory.php',
        type: 'GET',
        data: currentFilters + '&ajax=refresh',
        success: function(data) {
            $('.mailchimpsync-history-table tbody').html(data);
        },
        error: function() {
            console.log('Failed to refresh sync history');
        }
    });
}

/**
 * Filter sync history
 */
function filterSyncHistory() {
    var filterData = $('.mailchimpsync-history-filter').serialize();
    
    $.ajax({
        url: 'synchistory.php',
        type: 'GET',
        data: filterData + '&ajax=filter',
        success: function(data) {
            $('.mailchimpsync-history-table tbody').html(data);
        },
        error: function() {
            showAlert('Failed to filter sync history', 'error');
        }
    });
}

/**
 * Export sync history
 */
function exportSyncHistory() {
    var filterData = $('.mailchimpsync-history-filter').serialize();
    window.location.href = 'synchistory.php?' + filterData + '&action=export';
}

/**
 * Initialize connection testing
 */
function initializeConnectionTesting() {
    $('.mailchimpsync-test-connection').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        
        // Show loading state
        button.prop('disabled', true);
        button.html('<span class="mailchimpsync-loading"></span> Testing...');
        
        // Get API credentials
        var apiKey = $('input[name="api_key"]').val();
        var serverPrefix = $('input[name="server_prefix"]').val();
        
        $.ajax({
            url: 'setup.php',
            type: 'POST',
            data: {
                action: 'test_connection',
                api_key: apiKey,
                server_prefix: serverPrefix,
                token: $('input[name="token"]').val()
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    showAlert('Connection test successful!', 'success');
                } else {
                    showAlert('Connection test failed: ' + data.error, 'error');
                }
            },
            error: function() {
                showAlert('Connection test failed: Network error', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false);
                button.text(originalText);
            }
        });
    });
}

/**
 * Initialize progress tracking
 */
function initializeProgressTracking() {
    // Track sync progress for manual and full syncs
    $('.mailchimpsync-manual-sync, .mailchimpsync-full-sync').on('click', function(e) {
        var button = $(this);
        var action = button.hasClass('mailchimpsync-full-sync') ? 'full_sync' : 'manual_sync';
        
        // Confirm full sync
        if (action === 'full_sync') {
            if (!confirm('This will perform a complete synchronization. Continue?')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Show progress
        showSyncProgress(button, action);
    });
}

/**
 * Show sync progress
 * @param {jQuery} button Button element
 * @param {string} action Sync action
 */
function showSyncProgress(button, action) {
    var originalText = button.text();
    var progressContainer = $('<div class="mailchimpsync-progress"><div class="mailchimpsync-progress-bar" style="width: 0%">0%</div></div>');
    
    // Insert progress bar
    button.after(progressContainer);
    button.prop('disabled', true);
    button.text('Syncing...');
    
    // Start progress simulation
    var progress = 0;
    var progressInterval = setInterval(function() {
        progress += Math.random() * 10;
        if (progress > 90) progress = 90;
        
        progressContainer.find('.mailchimpsync-progress-bar').css('width', progress + '%').text(Math.round(progress) + '%');
    }, 500);
    
    // Monitor sync completion
    var checkInterval = setInterval(function() {
        $.ajax({
            url: 'dashboard.php',
            type: 'GET',
            data: { ajax: 'checkprogress', action: action },
            dataType: 'json',
            success: function(data) {
                if (data.completed) {
                    clearInterval(progressInterval);
                    clearInterval(checkInterval);
                    
                    // Complete progress
                    progressContainer.find('.mailchimpsync-progress-bar').css('width', '100%').text('100%');
                    
                    // Restore button and remove progress after delay
                    setTimeout(function() {
                        button.prop('disabled', false);
                        button.text(originalText);
                        progressContainer.remove();
                        
                        // Refresh page to show results
                        if (data.success) {
                            showAlert('Sync completed successfully!', 'success');
                            location.reload();
                        } else {
                            showAlert('Sync completed with errors: ' + data.error, 'error');
                        }
                    }, 1000);
                }
            },
            error: function() {
                // Continue monitoring on error
            }
        });
    }, 2000);
}

/**
 * Show alert message
 * @param {string} message Message to display
 * @param {string} type Alert type (success, error, warning, info)
 */
function showAlert(message, type = 'info') {
    var alertClass = 'mailchimpsync-alert-' + type;
    var alert = $('<div class="mailchimpsync-alert ' + alertClass + '">' + message + '</div>');
    
    // Insert at top of page
    var container = $('.mailchimpsync-dashboard, .fiche');
    if (container.length === 0) {
        container = $('body');
    }
    
    container.prepend(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        alert.fadeOut(function() {
            alert.remove();
        });
    }, 5000);
    
    // Allow manual close
    alert.on('click', function() {
        alert.fadeOut(function() {
            alert.remove();
        });
    });
}

/**
 * Utility function to format dates
 * @param {string} dateString Date string
 * @return {string} Formatted date
 */
function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleString();
}

/**
 * Utility function to escape HTML
 * @param {string} text Text to escape
 * @return {string} Escaped text
 */
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    $('[data-tooltip]').each(function() {
        var element = $(this);
        var tooltip = $('<div class="mailchimpsync-tooltip">' + element.data('tooltip') + '</div>');
        
        element.on('mouseenter', function() {
            $('body').append(tooltip);
            tooltip.show();
        });
        
        element.on('mouseleave', function() {
            tooltip.remove();
        });
        
        element.on('mousemove', function(e) {
            tooltip.css({
                left: e.pageX + 10,
                top: e.pageY + 10
            });
        });
    });
}

// Initialize tooltips when document is ready
$(document).ready(function() {
    initializeTooltips();
});
