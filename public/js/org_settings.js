document.addEventListener('DOMContentLoaded', () => {
    const matrixContainer = document.getElementById('sharing-matrix');
    const saveButton = document.getElementById('save-org-settings');

    let organizations = [];
    let sharingSettings = [];
    const dataTypes = [
        { key: 'dispatch_view', label: 'Dispatch einsehen' },
        { key: 'officer_list', label: 'Beamtenliste einsehen' }
    ];

    async function initializeMatrix() {
        try {
            const response = await fetch('api/get_org_settings.php');
            const data = await response.json();

            if (data.error) {
                matrixContainer.innerHTML = `<p style="color: #f85149;">Fehler: ${data.error}</p>`;
                return;
            }

            organizations = data.organizations;
            sharingSettings = data.sharing_settings;
            renderMatrix();
        } catch (e) {
            matrixContainer.innerHTML = '<p style="color: #f85149;">Fehler beim Laden der Einstellungsmatrix.</p>';
        }
    }

    function renderMatrix() {
        let tableHTML = '<table><thead><tr><th>Quelle \\ Ziel</th>';
        organizations.forEach(targetOrg => {
            tableHTML += `<th>${targetOrg.name}</th>`;
        });
        tableHTML += '</tr></thead><tbody>';

        organizations.forEach(sourceOrg => {
            tableHTML += `<tr><td class="source-org">${sourceOrg.name}</td>`;
            organizations.forEach(targetOrg => {
                if (sourceOrg.id === targetOrg.id) {
                    tableHTML += '<td>--</td>';
                } else {
                    tableHTML += '<td>';
                    dataTypes.forEach(type => {
                        const isChecked = isSharingEnabled(sourceOrg.id, targetOrg.id, type.key);
                        tableHTML += `
                            <div>
                                <input type="checkbox"
                                       id="share-${sourceOrg.id}-${targetOrg.id}-${type.key}"
                                       data-source-id="${sourceOrg.id}"
                                       data-target-id="${targetOrg.id}"
                                       data-type="${type.key}"
                                       ${isChecked ? 'checked' : ''}>
                                <label for="share-${sourceOrg.id}-${targetOrg.id}-${type.key}" class="data-type-label">
                                    ${type.label}
                                </label>
                            </div>`;
                    });
                    tableHTML += '</td>';
                }
            });
            tableHTML += '</tr>';
        });

        tableHTML += '</tbody></table>';
        matrixContainer.innerHTML = tableHTML;
    }

    function isSharingEnabled(sourceId, targetId, dataType) {
        const setting = sharingSettings.find(s =>
            s.source_org_id == sourceId &&
            s.target_org_id == targetId &&
            s.data_type === dataType
        );
        return setting ? setting.can_access : false;
    }

    async function saveSettings() {
        const checkboxes = document.querySelectorAll('#sharing-matrix input[type="checkbox"]');
        const settingsToSave = [];

        checkboxes.forEach(cb => {
            settingsToSave.push({
                source_org_id: parseInt(cb.dataset.sourceId),
                target_org_id: parseInt(cb.dataset.targetId),
                data_type: cb.dataset.type,
                can_access: cb.checked ? 1 : 0
            });
        });

        saveButton.disabled = true;
        saveButton.textContent = 'Speichern...';

        try {
            const response = await fetch('api/save_org_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ settings: settingsToSave })
            });
            const result = await response.json();

            if (result.success) {
                alert('Einstellungen erfolgreich gespeichert.');
                // Reload data to reflect changes
                initializeMatrix();
            } else {
                alert('Fehler beim Speichern: ' + (result.message || 'Unbekannter Fehler.'));
            }
        } catch (e) {
            alert('Ein schwerwiegender Fehler ist aufgetreten.');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Einstellungen speichern';
        }
    }

    saveButton.addEventListener('click', saveSettings);
    initializeMatrix();
});