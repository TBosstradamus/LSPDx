document.addEventListener('DOMContentLoaded', () => {

    let isShotsFiredAlarmDismissed = false;

    const officerList = document.getElementById('officer-list');
    const vehicleGrid = document.getElementById('vehicle-grid');
    const headerRoleContainer = document.querySelector('.header-role-container');

    /**
     * Fetches the latest dispatch status from the API.
     */
    async function fetchDispatchStatus() {
        try {
            const response = await fetch('api/dispatch_status.php');
            if (!response.ok) {
                console.error('Failed to fetch dispatch status:', response.statusText);
                return;
            }
            const data = await response.json();
            updateDispatchView(data);
        } catch (error) {
            console.error('Error fetching or parsing dispatch data:', error);
        }
    }

    /**
     * Updates the entire dispatch view with new data from the API.
     * @param {object} data The data object from the API.
     */
    function updateDispatchView(data) {
        updateAvailableOfficers(data.available_officers);
        updateVehicleGrid(data.vehicles);
        updateHeaderRoles(data.header_roles);

    // Check for shots fired alarm
    const shotsFiredVehicle = data.vehicles ? data.vehicles.find(v => v.current_status === 7) : null;
    if (shotsFiredVehicle && !isShotsFiredAlarmDismissed) {
        showShotsFiredAlarm(shotsFiredVehicle);
    }
    }

    /**
     * Re-renders the list of available officers.
     */
    function updateAvailableOfficers(officers) {
        officerList.innerHTML = ''; // Clear the list
        if (!officers || officers.length === 0) {
            officerList.innerHTML = '<p style="color: #8b949e;">Keine Einheiten verfügbar.</p>';
            return;
        }
        officers.forEach(officer => {
            const officerCard = document.createElement('div');
            officerCard.className = 'officer-card';
            officerCard.draggable = true;
            officerCard.dataset.officerId = officer.id;
            officerCard.innerHTML = `
                <strong>${officer.lastName}, ${officer.firstName}</strong>
                <span>#${officer.badgeNumber} | ${officer.rank}</span>
            `;
            officerList.appendChild(officerCard);
        });
    }

    /**
     * Re-renders the grid of on-duty vehicles.
     */
    function updateVehicleGrid(vehicles) {
        vehicleGrid.innerHTML = ''; // Clear the grid
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
                if (officer) {
                    seatsHTML += `
                        <div class="seat occupied" data-seat-index="${i}">
                            <strong>${officer.lastName}, ${officer.firstName}</strong>
                            <span>#${officer.badgeNumber}</span>
                        </div>`;
                } else {
                    seatsHTML += `<div class="seat" data-seat-index="${i}">Sitz ${i + 1}</div>`;
                }
            }

            vehicleCard.innerHTML = `
                <div class="vehicle-header">
                    <span class="vehicle-name">${vehicle.name}</span>
                    <span class="vehicle-status status-${vehicle.current_status || 1}">Code ${vehicle.current_status || 1}</span>
                </div>
                <div class="vehicle-details">
                    <span class="vehicle-callsign">Callsign: ${vehicle.current_callsign || '--'}</span>
                    <span class="vehicle-funk">Funk: ${vehicle.current_funk || '--'}</span>
                </div>
                <div class="vehicle-seats">
                    ${seatsHTML}
                </div>
            `;
            vehicleGrid.appendChild(vehicleCard);
        });
    }

    /**
     * Re-renders the header roles with assigned officers.
     */
    function updateHeaderRoles(headerRoles) {
        if (!headerRoles) return;
        for (const roleName in headerRoles) {
            const roleElement = headerRoleContainer.querySelector(`[data-role-name="${roleName}"] .role-officer`);
            if (roleElement) {
                const officer = headerRoles[roleName];
                roleElement.textContent = officer ? `${officer.lastName}, ${officer.firstName}` : '--';
            }
        }
    }

    // --- DRAG & DROP LOGIC ---

    document.addEventListener('dragstart', (e) => {
        if (e.target.classList.contains('officer-card')) {
            e.dataTransfer.setData('text/plain', e.target.dataset.officerId);
            e.target.style.opacity = '0.5';
        }
    });

     document.addEventListener('dragend', (e) => {
        if (e.target.classList.contains('officer-card')) {
            e.target.style.opacity = '1';
        }
    });

    document.addEventListener('dragover', (e) => {
        const dropTarget = e.target.closest('.seat, .header-role');
        if (dropTarget) {
            e.preventDefault();
            dropTarget.classList.add('drag-over');
        }
    });

    document.addEventListener('dragleave', (e) => {
        const dropTarget = e.target.closest('.seat, .header-role');
        if (dropTarget) {
            dropTarget.classList.remove('drag-over');
        }
    });

    document.addEventListener('drop', (e) => {
        e.preventDefault();
        const officerId = e.dataTransfer.getData('text/plain');
        const dropTarget = e.target.closest('.seat, .header-role');

        if (!dropTarget || !officerId) return;

        dropTarget.classList.remove('drag-over');

        if (dropTarget.classList.contains('seat') && !dropTarget.classList.contains('occupied')) {
            const vehicleCard = dropTarget.closest('.vehicle-card');
            const vehicleId = vehicleCard.dataset.vehicleId;
            const seatIndex = dropTarget.dataset.seatIndex;
            assignOfficer(officerId, vehicleId, seatIndex);
        } else if (dropTarget.classList.contains('header-role')) {
            const roleName = dropTarget.dataset.roleName;
            assignOfficerToHeader(officerId, roleName);
        }
    });

    async function assignOfficer(officerId, vehicleId, seatIndex) {
        try {
            const response = await fetch('api/assign_officer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    officerId: parseInt(officerId),
                    vehicleId: parseInt(vehicleId),
                    seatIndex: parseInt(seatIndex)
                }),
            });
            const result = await response.json();
            if (result.success) {
                fetchDispatchStatus();
            } else {
                alert('Fehler: ' + (result.message || 'Unbekannter Fehler.'));
            }
        } catch (error) {
            console.error('Error during officer assignment:', error);
        }
    }

    async function assignOfficerToHeader(officerId, roleName) {
        try {
            const response = await fetch('api/assign_officer_to_header.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    officerId: parseInt(officerId),
                    roleName: roleName
                }),
            });
            const result = await response.json();
            if (result.success) {
                fetchDispatchStatus();
            } else {
                alert('Fehler: ' + (result.message || 'Unbekannter Fehler.'));
            }
        } catch (error) {
            console.error('Error assigning officer to header:', error);
        }
    }


    // --- CLICK EVENT HANDLING ---
    document.addEventListener('click', (e) => {
        const vehicleCard = e.target.closest('.vehicle-card');
        if (!vehicleCard) return;

        const vehicleId = vehicleCard.dataset.vehicleId;

        if (e.target.classList.contains('vehicle-status')) {
            const currentStatusClass = Array.from(e.target.classList).find(c => c.startsWith('status-'));
            const currentStatus = currentStatusClass ? parseInt(currentStatusClass.split('-')[1]) : 1;
            const statusCycle = [1, 2, 3, 5, 6, 7];
            const currentIndex = statusCycle.indexOf(currentStatus);
            const newStatus = currentIndex === -1 ? statusCycle[0] : statusCycle[(currentIndex + 1) % statusCycle.length];
            setVehicleStatus(vehicleId, newStatus);
        }

        if (e.target.classList.contains('vehicle-funk') || e.target.classList.contains('vehicle-callsign')) {
            const isFunk = e.target.classList.contains('vehicle-funk');
            const currentValue = e.target.textContent.split(': ')[1].trim();
            const newValue = prompt(`Neuen Wert für ${isFunk ? 'Funk' : 'Callsign'} eingeben:`, currentValue);

            if (newValue && newValue.trim() !== '' && newValue !== currentValue) {
                if (isFunk) {
                    setVehicleFunk(vehicleId, newValue.trim());
                } else {
                    setVehicleCallsign(vehicleId, newValue.trim());
                }
            }
        }
    });

    async function setVehicleStatus(vehicleId, status) {
        try {
            const response = await fetch('api/set_vehicle_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ vehicleId: parseInt(vehicleId), status: status }),
            });
            const result = await response.json();
            if (result.success) {
                fetchDispatchStatus();
            } else {
                alert('Fehler beim Aktualisieren des Status.');
            }
        } catch (error) {
            console.error('Error during status update:', error);
        }
    }

    async function setVehicleFunk(vehicleId, funk) {
        try {
            const response = await fetch('api/set_vehicle_funk.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ vehicleId: parseInt(vehicleId), funk: funk }),
            });
            const result = await response.json();
            if (result.success) {
                fetchDispatchStatus();
            } else {
                alert('Fehler beim Aktualisieren des Funkkanals.');
            }
        } catch (error) {
            console.error('Error updating funk:', error);
        }
    }

    async function setVehicleCallsign(vehicleId, callsign) {
        try {
            const response = await fetch('api/set_vehicle_callsign.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ vehicleId: parseInt(vehicleId), callsign: callsign }),
            });
            const result = await response.json();
            if (result.success) {
                fetchDispatchStatus();
            } else {
                alert('Fehler beim Aktualisieren des Callsigns.');
            }
        } catch (error) {
            console.error('Error updating callsign:', error);
        }
    }


    function showShotsFiredAlarm(vehicle) {
        // Prevent multiple alarms from being created
        if (document.querySelector('.shots-fired-overlay')) {
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'shots-fired-overlay';

        const occupants = vehicle.seats.filter(s => s).map(s => `${s.firstName} ${s.lastName}`).join(', ');

        overlay.innerHTML = `
            <h1>SHOTS FIRED</h1>
            <p>Einheit: ${vehicle.name} (${vehicle.current_callsign || 'N/A'})</p>
            <p>Insassen: ${occupants || 'Unbekannt'}</p>
            <button class="shots-fired-dismiss">Alarm bestätigen</button>
        `;

        document.body.appendChild(overlay);

        overlay.querySelector('.shots-fired-dismiss').addEventListener('click', () => {
            isShotsFiredAlarmDismissed = true;
            overlay.remove();
        });
    }


    // --- INITIAL FETCH & POLLING ---
    fetchDispatchStatus(); // Fetch data immediately on page load
    setInterval(fetchDispatchStatus, 5000); // Poll for new data every 5 seconds


    // --- CALLSIGN MODAL LOGIC ---
    const callsignModal = document.getElementById('callsign-modal');
    const openCallsignModalBtn = document.getElementById('open-callsign-modal');
    const closeCallsignModalBtn = document.getElementById('close-callsign-modal');
    const callsignModalBody = document.getElementById('callsign-modal-body');
    let callsignDataCache = null;

    async function openCallsignModal() {
        if (!callsignDataCache) {
            try {
                const response = await fetch('api/get_settings.php');
                const data = await response.json();
                if (data.error) {
                    callsignModalBody.innerHTML = `<p style="color: #f85149;">${data.error}</p>`;
                } else {
                    callsignDataCache = data;
                    renderCallsignData(callsignDataCache);
                }
            } catch (e) {
                callsignModalBody.innerHTML = '<p style="color: #f85149;">Fehler beim Laden der Daten.</p>';
            }
        }
        callsignModal.style.display = 'flex';
    }

    function closeCallsignModal() {
        callsignModal.style.display = 'none';
    }

    function renderCallsignData(data) {
        let html = '';
        for (const category in data) {
            html += `
                <div class="callsign-category">
                    <h3>${category.charAt(0).toUpperCase() + category.slice(1)}</h3>
                    <ul class="callsign-list">
            `;
            data[category].forEach(item => {
                html += `<li><span class="code">${item.code}</span><span class="meaning">${item.meaning}</span></li>`;
            });
            html += '</ul></div>';
        }
        callsignModalBody.innerHTML = html;
    }

    openCallsignModalBtn.addEventListener('click', openCallsignModal);
    closeCallsignModalBtn.addEventListener('click', closeCallsignModal);
    callsignModal.addEventListener('click', (e) => {
        if (e.target === callsignModal) {
            closeCallsignModal();
        }
    });
});