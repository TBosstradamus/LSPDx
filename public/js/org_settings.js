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
                matrixContainer.innerHTML = `<p class="text-red-400">Fehler: ${data.error}</p>`;
                return;
            }

            organizations = data.organizations;
            sharingSettings = data.sharing_settings;
            renderMatrix();
        } catch (e) {
            matrixContainer.innerHTML = '<p class="text-red-400">Fehler beim Laden der Einstellungsmatrix.</p>';
        }
    }

    function renderMatrix() {
        let tableHTML = '<table class="min-w-full"><thead class="bg-gray-700"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Quelle \\ Ziel</th>';
        organizations.forEach(targetOrg => {
            tableHTML += `<th class="px-6 py-3 text-xs font-medium text-gray-300 uppercase tracking-wider">${targetOrg.name}</th>`;
        });
        tableHTML += '</tr></thead><tbody class="bg-gray-800 divide-y divide-gray-700">';

        organizations.forEach(sourceOrg => {
            tableHTML += `<tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">${sourceOrg.name}</td>`;
            organizations.forEach(targetOrg => {
                if (sourceOrg.id === targetOrg.id) {
                    tableHTML += '<td class="px-6 py-4">--</td>';
                } else {
                    tableHTML += '<td class="px-6 py-4">';
                    dataTypes.forEach(type => {
                        const isChecked = isSharingEnabled(sourceOrg.id, targetOrg.id, type.key);
                        tableHTML += `
                            <div class="flex items-center">
                                <input type="checkbox"
                                       id="share-${sourceOrg.id}-${targetOrg.id}-${type.key}"
                                       class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500"
                                       data-source-id="${sourceOrg.id}"
                                       data-target-id="${targetOrg.id}"
                                       data-type="${type.key}"
                                       ${isChecked ? 'checked' : ''}>
                                <label for="share-${sourceOrg.id}-${targetOrg.id}-${type.key}" class="ml-2 block text-sm text-gray-300">
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