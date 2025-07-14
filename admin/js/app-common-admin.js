jQuery(document).ready(function($) {
    document.getElementById("hubspotlink").click();
});

document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("hubspot-form-groups");
    const addBtn = document.getElementById("add-hubspot-form-group");

    let index = 0;

    const createFormGroup = (name = "", live = "", dev = "", enabled = true, phone_validate = false) => {
        const group = document.createElement("div");
        group.className = "hubspot-form-group";
        group.innerHTML = `
            <input type="text" name="app_hubspot_forms[${index}][name]" placeholder="Form Name" value="${name}" class="regular-text" required />
            <input type="text" name="app_hubspot_forms[${index}][live]" placeholder="Live Form ID" value="${live}" class="regular-text" required />
            <input type="text" name="app_hubspot_forms[${index}][dev]" placeholder="Dev Form ID" value="${dev}" class="regular-text" required />

            <div class="field-enable-wrapper">
                <label class="toggle-switch">
                    <input type="checkbox" name="app_hubspot_forms[${index}][enabled]" ${enabled ? 'checked' : ''} value="1" />
                    <span class="slider"></span>
                </label>
                <span class="toggle-label">Enabled</span>
            </div>

            <div class="field-enable-wrapper">
                <label class="toggle-switch">
                    <input type="checkbox" name="app_hubspot_forms[${index}][phone_validate]" ${phone_validate ? 'checked' : ''} value="1" />
                    <span class="slider"></span>
                </label>
                <span class="toggle-label">Phone Validation</span>
            </div>

            <button type="button" class="remove-hubspot-form-group button delete-button" title="Remove">
                <span class="dashicons dashicons-trash"></span>
            </button>`;
        container.appendChild(group);
        index++;
    };

    addBtn.addEventListener("click", () => {
        createFormGroup("", "", "", true, false);
    });

    container.addEventListener("click", function (e) {
        const removeBtn = e.target.closest(".remove-hubspot-form-group");
        if (removeBtn) {
            removeBtn.parentElement.remove();
        }
    });

    if (typeof savedHubspotForms !== "undefined" && Array.isArray(savedHubspotForms)) {
        savedHubspotForms.forEach(item => {
            createFormGroup(
                item.name || "",
                item.live || "",
                item.dev || "",
                item.enabled === "1" || item.enabled === true,
                item.phone_validate === "1" || item.phone_validate === true
            );
        });
    } else {
        createFormGroup("", "", "", true, false);
    }
});

jQuery(document).ready(function ($) {
    function updateProgress(percent, message) {
        $('#app-comments-progress').show();
        $('#app-progress-bar').css('width', percent + '%').text(percent + '%');
        $('#app-progress-status').text(message);
    }

    $('#app-backup-comments').on('click', function (e) {
        e.preventDefault(); // prevent form from submitting
        alert('backup started');
        updateProgress(0, 'Starting backup...');
        $.post(ajaxurl, { action: 'app_backup_comments_ajax' }, function (res) {
            if (res.success) {
                updateProgress(100, 'Backup complete: ' + res.data.filename);
            } else {
                updateProgress(0, 'Backup failed: ' + res.data);
            }
        });
    });

    $('#app-restore-comments').on('click', function (e) {
        e.preventDefault(); 
        updateProgress(0, 'Restoring comments...');

        $.post(ajaxurl, { action: 'app_restore_comments_ajax' }, function (res) {
            if (res.success) {
                updateProgress(100, 'Restore complete from: ' + res.data.filename);

                // Show admin notice message
                const notice = $('<div class="notice notice-success is-dismissible"><p>Comment restore complete.</p></div>');
                $('.wrap h1').after(notice);
            } else {
                updateProgress(0, 'Restore failed: ' + res.data);

                const notice = $('<div class="notice notice-error is-dismissible"><p>' + res.data + '</p></div>');
                $('.wrap h1').after(notice);
            }
        });
    });

    $('#app-delete-comments').on('click', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete ALL comments? This cannot be undone.')) return;

        $.post(ajaxurl, { action: 'app_delete_all_comments_ajax' }, function (res) {
            if (res.success) {
                alert('All comments have been deleted.');
            } else {
                alert('Failed to delete comments: ' + res.data);
            }
        });
    });

});