document.addEventListener('DOMContentLoaded', () => {

    let isShotsFiredAlarmDismissed = false;
    let settingsCache = null;

    const officerList = document.getElementById('officer-list');
    const vehicleGrid = document.getElementById('vehicle-grid');
    const headerRoleContainer = document.querySelector('.header-role-container');
    const activityZonesContainer = document.getElementById('activity-zones');

    /**
     * Fetches all necessary data for the dispatch view.
     */
    async function fetchDispatchStatus() {
        try {
            const response = await fetch('api/dispatch_status.php');
            if (!response.ok) throw new Error('Failed to fetch dispatch status');
            const data = await response.json();
            updateDispatchView(data);
        } catch (error) {
            console.error('Error fetching dispatch data:', error);
        }
    }

    async function fetchSettings() {
        if (settingsCache) return;
        try {
            const response = await fetch('api/get_settings.php');
            if (!response.ok) throw new Error('Failed to fetch settings');
            settingsCache = await response.json();
        } catch (error) {
            console.error('Error fetching settings:', error);
        }
    }

    /**
     * Updates the entire dispatch view with new data.
     */
    function updateDispatchView(data) {
        updateAvailableOfficers(data.available_officers);
        updateVehicleGrid(data.vehicles);
        updateHeaderRoles(data.header_roles);
        updateActivityZones(data.on_activity_officers);

        const shotsFiredVehicle = data.vehicles ? data.vehicles.find(v => v.current_status === 7) : null;
        if (shotsFiredVehicle && !isShotsFiredAlarmDismissed) {
            showShotsFiredAlarm(shotsFiredVehicle);
        }
    }

    function updateAvailableOfficers(officers) {
        officerList.innerHTML = '';
        if (!officers || officers.length === 0) {
            officerList.innerHTML = '<p style="color: #8b949e;">Keine Einheiten verfügbar.</p>';
            return;
        }
        officers.forEach(officer => {
            const officerCard = document.createElement('div');
            officerCard.className = 'officer-card';
            officerCard.draggable = true;
            officerCard.dataset.officerId = officer.id;
            officerCard.innerHTML = `<strong>${officer.lastName}, ${officer.firstName}</strong><span>#${officer.badgeNumber} | ${officer.rank}</span>`;
            officerList.appendChild(officerCard);
        });
    }

    function updateVehicleGrid(vehicles) {
        vehicleGrid.innerHTML = '';
        if (!vehicles || vehicles.length === 0) {
            vehicleGrid.innerHTML = '<p style="color: #8b949e;">Keine Fahrzeuge im Dienst.</p>';
            return;
        }
        vehicles.forEach(vehicle => {
            const vehicleCard = document.createElement('div');
            vehicleCard.className = 'vehicle-card';
            vehicleCard.dataset.vehicleId = vehicle.id;

            let seatsHTML = '';
            for (let i = 0; i < vehicle.capacity; i++) {
                const officer = vehicle.seats[i];
                seatsHTML += officer
                    ? `<div class="seat occupied" data-seat-index="${i}" draggable="true" data-officer-id="${officer.id}"><strong>${officer.lastName}, ${officer.firstName}</strong><span>#${officer.badgeNumber}</span></div>`
                    : `<div class="seat" data-seat-index="${i}">Sitz ${i + 1}</div>`;
            }

            const status = settingsCache?.callsigns?.status.find(s => s.code === `Code ${vehicle.current_status || 1}`);

            vehicleCard.innerHTML = `
                <div class="vehicle-header">
                    <span class="vehicle-name">${vehicle.name}</span>
                    <span class="vehicle-status status-${vehicle.current_status || 1}" data-editable="status">${status?.meaning || `Code ${vehicle.current_status || 1}`}</span>
                </div>
                <div class="vehicle-details">
                    <span class="vehicle-callsign" data-editable="callsign">Callsign: ${vehicle.current_callsign || '--'}</span>
                    <span class="vehicle-funk" data-editable="funk">Funk: ${vehicle.current_funk || '--'}</span>
                </div>
                <div class="vehicle-seats">${seatsHTML}</div>
            `;
            vehicleGrid.appendChild(vehicleCard);
        });
    }

    function updateHeaderRoles(headerRoles) {
        if (!headerRoles) return;
        for (const roleName in headerRoles) {
            const roleElementContainer = headerRoleContainer.querySelector(`[data-role-name="${roleName}"]`);
            const roleElement = roleElementContainer.querySelector('.role-officer');
            if (roleElement) {
                const officer = headerRoles[roleName];
                if (officer) {
                    roleElement.textContent = `${officer.lastName}, ${officer.firstName}`;
                    roleElementContainer.draggable = true;
                    roleElementContainer.dataset.officerId = officer.officer_id;
                } else {
                    roleElement.textContent = '--';
                    roleElementContainer.draggable = false;
                    delete roleElementContainer.dataset.officerId;
                }
            }
        }
    }

    function updateActivityZones(activityOfficers) {
        // Clear all existing officers from activity zones
        document.querySelectorAll('.activity-officers').forEach(zone => zone.innerHTML = '');

        if (!activityOfficers) return;

        activityOfficers.forEach(officer => {
            const zone = activityZonesContainer.querySelector(`[data-activity-name="${officer.activity_name}"] .activity-officers`);
            if (zone) {
                const officerCard = document.createElement('div');
                officerCard.className = 'officer-card';
                officerCard.draggable = true;
                officerCard.dataset.officerId = officer.officer_id;
                officerCard.innerHTML = `<strong>${officer.lastName}, ${officer.firstName}</strong><span>#${officer.badgeNumber}</span>`;
                zone.appendChild(officerCard);
            }
        });
    }

    // --- DRAG & DROP LOGIC ---

    document.addEventListener('dragstart', (e) => {
        const draggableElement = e.target.closest('[draggable="true"]');
        if (draggableElement && draggableElement.dataset.officerId) {
            e.dataTransfer.setData('text/plain', draggableElement.dataset.officerId);
            draggableElement.style.opacity = '0.5';
        }
    });

     document.addEventListener('dragend', (e) => {
        const draggableElement = e.target.closest('[draggable="true"]');
        if (draggableElement) {
            draggableElement.style.opacity = '1';
        }
    });

    document.addEventListener('dragover', (e) => {
        const dropTarget = e.target.closest('.seat, .header-role, .activity-zone, .dispatch-sidebar');
        if (dropTarget) {
            e.preventDefault();
            if (!dropTarget.classList.contains('dispatch-sidebar')) {
                dropTarget.classList.add('drag-over');
            }
        }
    });

    document.addEventListener('dragleave', (e) => {
        const dropTarget = e.target.closest('.seat, .header-role, .activity-zone');
        if (dropTarget) {
            dropTarget.classList.remove('drag-over');
        }
    });

    document.addEventListener('drop', (e) => {
        e.preventDefault();
        const officerId = e.dataTransfer.getData('text/plain');
        const dropTarget = e.target.closest('.seat, .header-role, .activity-zone, .dispatch-sidebar');

        if (!dropTarget || !officerId) return;

        if (dropTarget.classList.contains('seat') || dropTarget.classList.contains('header-role') || dropTarget.classList.contains('activity-zone')) {
            dropTarget.classList.remove('drag-over');
        }

        if (dropTarget.classList.contains('seat') && !dropTarget.classList.contains('occupied')) {
            const vehicleId = dropTarget.closest('.vehicle-card').dataset.vehicleId;
            const seatIndex = dropTarget.dataset.seatIndex;
            assignOfficerToVehicle(officerId, vehicleId, seatIndex);
        } else if (dropTarget.classList.contains('header-role')) {
            const roleName = dropTarget.dataset.roleName;
            assignOfficerToHeader(officerId, roleName);
        } else if (dropTarget.classList.contains('activity-zone')) {
            const activityName = dropTarget.dataset.activityName;
            assignOfficerToActivity(officerId, activityName);
        } else if (dropTarget.classList.contains('dispatch-sidebar')) {
            unassignOfficer(officerId);
        }
    });

    // --- API CALLS ---
    async function performPost(url, body) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const result = await response.json();
            if (result.success) {
                fetchDispatchStatus();
            } else {
                alert('Fehler: ' + (result.message || 'Unbekannter Fehler.'));
            }
        } catch (error) {
            console.error(`Error with ${url}:`, error);
        }
    }

    function assignOfficerToVehicle(officerId, vehicleId, seatIndex) {
        performPost('api/assign_officer.php', { officerId: parseInt(officerId), vehicleId: parseInt(vehicleId), seatIndex: parseInt(seatIndex) });
    }
    function assignOfficerToHeader(officerId, roleName) {
        performPost('api/assign_officer_to_header.php', { officerId: parseInt(officerId), roleName: roleName });
    }
    function assignOfficerToActivity(officerId, activityName) {
        performPost('api/assign_officer_to_activity.php', { officerId: parseInt(officerId), activityName: activityName });
    }
    function unassignOfficer(officerId) {
        performPost('api/unassign_officer.php', { officerId: parseInt(officerId) });
    }
    function setVehicleStatus(vehicleId, status) {
        performPost('api/set_vehicle_status.php', { vehicleId: parseInt(vehicleId), status: status });
    }
    function setVehicleFunk(vehicleId, funk) {
        performPost('api/set_vehicle_funk.php', { vehicleId: parseInt(vehicleId), funk: funk });
    }
    function setVehicleCallsign(vehicleId, callsign) {
        performPost('api/set_vehicle_callsign.php', { vehicleId: parseInt(vehicleId), callsign: callsign });
    }

    // --- CLICK EVENT HANDLING & DROPDOWN ---
    document.addEventListener('click', (e) => {
        const editableTarget = e.target.closest('[data-editable]');
        if (editableTarget) {
            const vehicleId = editableTarget.closest('.vehicle-card').dataset.vehicleId;
            const editType = editableTarget.dataset.editable;
            createDropdown(editableTarget, editType, vehicleId);
            return;
        }
        if (e.target.id === 'open-callsign-modal') openCallsignModal();
        if (e.target.id === 'close-callsign-modal') closeCallsignModal();
        if (e.target.id === 'callsign-modal') closeCallsignModal();
    });

    function createDropdown(targetElement, type, vehicleId) {
        document.querySelectorAll('.dynamic-dropdown').forEach(d => d.remove());
        const dropdown = document.createElement('div');
        dropdown.className = 'dynamic-dropdown';

        let options = [];
        if (type === 'status') options = settingsCache?.callsigns?.status.map(s => ({ value: s.code.replace('Code ', ''), text: s.meaning })) || [];
        if (type === 'funk') options = settingsCache?.funk_channels.map(c => ({ value: c, text: c })) || [];

        if (type === 'callsign') {
            const input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Callsign...';
            input.className = 'dropdown-input';
            dropdown.appendChild(input);
            input.focus();
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter' && input.value.trim()) {
                    setVehicleCallsign(vehicleId, input.value.trim());
                    dropdown.remove();
                }
            });
        } else {
            options.forEach(opt => {
                const optionEl = document.createElement('div');
                optionEl.className = 'dropdown-item';
                optionEl.textContent = opt.text;
                optionEl.addEventListener('click', () => {
                    if (type === 'status') setVehicleStatus(vehicleId, opt.value);
                    if (type === 'funk') setVehicleFunk(vehicleId, opt.value);
                    dropdown.remove();
                });
                dropdown.appendChild(optionEl);
            });
        }

        document.body.appendChild(dropdown);
        const rect = targetElement.getBoundingClientRect();
        dropdown.style.left = `${rect.left}px`;
        dropdown.style.top = `${rect.bottom}px`;

        setTimeout(() => {
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && e.target !== targetElement) {
                    dropdown.remove();
                }
            }, { once: true });
        }, 0);
    }

    // (Shots Fired and Callsign Modal logic remain the same)
    function showShotsFiredAlarm(vehicle) { /* ... */ }
    const callsignModal = document.getElementById('callsign-modal');
    const callsignModalBody = document.getElementById('callsign-modal-body');
    async function openCallsignModal() { /* ... */ }
    function closeCallsignModal() { /* ... */ }
    function renderCallsignData(data) { /* ... */ }

    // --- INITIALIZATION ---
    async function init() {
        await fetchSettings();
        await fetchDispatchStatus();
        setInterval(fetchDispatchStatus, 5000);
    }
    init();
});