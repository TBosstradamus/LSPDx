document.addEventListener('DOMContentLoaded', () => {
    const officerList = document.getElementById('officer-list');
    const vehicleGrid = document.getElementById('vehicle-grid');
    const headerRoles = document.getElementById('header-role-container');
    const activityZones = document.getElementById('activity-zones');

    let draggedOfficer = null;

    // --- DATA FETCHING ---
    async function fetchData() {
        try {
            const response = await fetch('index.php?page=dispatch_status');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            populateUI(data);
        } catch (error) {
            console.error("Could not fetch dispatch data:", error);
            officerList.innerHTML = '<p class="text-red-400 p-2">Fehler beim Laden der Daten.</p>';
        }
    }

    // --- UI POPULATION ---
    function populateUI(data) {
        officerList.innerHTML = '';
        vehicleGrid.innerHTML = '';
        document.querySelectorAll('.role-officer').forEach(el => el.innerHTML = '');
        document.querySelectorAll('.activity-officers').forEach(el => el.innerHTML = '');

        data.officers.available.forEach(officer => {
            officerList.appendChild(createOfficerElement(officer));
        });

        data.vehicles.forEach(vehicle => {
            const vehicleEl = createVehicleElement(vehicle);
            vehicle.assigned_officers.forEach((officer, index) => {
                if (officer) {
                    const seat = vehicleEl.querySelector(`.vehicle-seat[data-seat-index="${index}"]`);
                    if (seat) seat.appendChild(createOfficerElement(officer));
                }
            });
            vehicleGrid.appendChild(vehicleEl);
        });

        data.assignments.header.forEach(assignment => {
            const roleContainer = headerRoles.querySelector(`.header-role-wrapper[data-role-name="${assignment.assignment_id}"] .role-officer`);
            const officer = data.officers.all.find(o => o.id === assignment.officer_id);
            if (roleContainer && officer) {
                roleContainer.appendChild(createOfficerElement(officer));
            }
        });

        data.assignments.activity.forEach(assignment => {
            const activityEl = activityZones.querySelector(`.activity-zone[data-activity-name="${assignment.assignment_id}"] .activity-officers`);
            const officer = data.officers.all.find(o => o.id === assignment.officer_id);
            if (activityEl && officer) {
                activityEl.appendChild(createOfficerElement(officer));
            }
        });
    }

    // --- ELEMENT CREATION ---
    function createOfficerElement(officer) {
        const el = document.createElement('div');
        el.className = 'officer-draggable bg-gray-700 p-2 rounded shadow flex items-center cursor-move text-sm';
        el.draggable = true;
        el.dataset.officerId = officer.id;
        el.innerHTML = `<span class="font-bold text-white">${officer.lastName}, ${officer.firstName}</span>`;
        el.addEventListener('dragstart', handleDragStart);
        el.addEventListener('dragend', handleDragEnd);
        return el;
    }

    function createVehicleElement(vehicle) {
        const el = document.createElement('div');
        el.className = 'vehicle-card bg-brand-bg border border-brand-border rounded-lg p-3 flex flex-col gap-2';
        el.dataset.vehicleId = vehicle.id;

        let seatsHTML = '';
        for (let i = 0; i < vehicle.capacity; i++) {
            seatsHTML += `<div class="vehicle-seat border border-dashed border-gray-600 rounded p-1 min-h-[48px]" data-seat-index="${i}"></div>`;
        }

        // Note: In a real scenario, these options might come from the API
        const funkOptions = ['LSPD 1', 'LSPD 2', 'STATE', 'CITY'].map(f => `<option value="${f}" ${vehicle.current_funk === f ? 'selected' : ''}>${f}</option>`).join('');
        const callsignOptions = ['1-ADAM', '2-ADAM', '3-ADAM', '1-KING', '2-KING'].map(c => `<option value="${c}" ${vehicle.current_callsign === c ? 'selected' : ''}>${c}</option>`).join('');

        el.innerHTML = `
            <div class="flex justify-between items-center">
                <h4 class="font-bold text-white">${vehicle.name}</h4>
                <span class="text-xs font-mono px-2 py-1 bg-gray-700 rounded">${vehicle.licensePlate}</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                ${seatsHTML}
            </div>
            <div class="grid grid-cols-2 gap-2 mt-1">
                <select class="vehicle-funk-select bg-brand-sidebar border-brand-border rounded-md text-xs p-1">
                    <option value="">Funk...</option>
                    ${funkOptions}
                </select>
                <select class="vehicle-callsign-select bg-brand-sidebar border-brand-border rounded-md text-xs p-1">
                    <option value="">Callsign...</option>
                    ${callsignOptions}
                </select>
            </div>
        `;

        el.querySelector('.vehicle-funk-select').addEventListener('change', (e) => {
            setVehicleFunk(vehicle.id, e.target.value);
        });
        el.querySelector('.vehicle-callsign-select').addEventListener('change', (e) => {
            setVehicleCallsign(vehicle.id, e.target.value);
        });

        return el;
    }

    // --- DRAG & DROP HANDLERS ---
    function handleDragStart(e) {
        draggedOfficer = e.target;
        setTimeout(() => e.target.style.display = 'none', 0);
    }

    function handleDragEnd(e) {
        draggedOfficer.style.display = 'flex';
        draggedOfficer = null;
    }

    function handleDragOver(e) {
        e.preventDefault();
    }

    function handleDrop(e, targetElement) {
        e.preventDefault();
        if (!draggedOfficer) return;

        const officerId = draggedOfficer.dataset.officerId;

        if (targetElement.children.length > 0 && !targetElement.id === 'officer-list') return;

        if (targetElement.classList.contains('vehicle-seat')) {
            const vehicleId = targetElement.closest('.vehicle-card').dataset.vehicleId;
            const seatIndex = targetElement.dataset.seatIndex;
            targetElement.appendChild(draggedOfficer);
            assignOfficerToVehicle(officerId, vehicleId, seatIndex);
        } else if (targetElement.id === 'officer-list') {
            targetElement.appendChild(draggedOfficer);
            unassignOfficer(officerId);
        } else if (targetElement.classList.contains('role-officer')) {
            const roleName = targetElement.closest('.header-role-wrapper').dataset.roleName;
            targetElement.appendChild(draggedOfficer);
            assignOfficerToHeader(officerId, roleName);
        } else if (targetElement.classList.contains('activity-officers')) {
            const activityName = targetElement.closest('.activity-zone').dataset.activityName;
            targetElement.appendChild(draggedOfficer);
            assignOfficerToActivity(officerId, activityName);
        }
    }

    // --- API CALLS ---
    async function assignOfficerToVehicle(officerId, vehicleId, seatIndex) {
        await postRequest('index.php?page=assign_officer_to_vehicle', { officerId, vehicleId, seatIndex });
    }
    async function unassignOfficer(officerId) {
        await postRequest('index.php?page=unassign_officer', { officerId });
    }
    async function assignOfficerToHeader(officerId, roleName) {
        await postRequest('index.php?page=assign_officer_to_header', { officerId, roleName });
    }
    async function assignOfficerToActivity(officerId, activityName) {
        await postRequest('index.php?page=assign_officer_to_activity', { officerId, activityName });
    }
    async function setVehicleFunk(vehicleId, funk) {
        await postRequest('index.php?page=set_vehicle_funk', { vehicleId, funk });
    }
    async function setVehicleCallsign(vehicleId, callsign) {
        await postRequest('index.php?page=set_vehicle_callsign', { vehicleId, callsign });
    }

    async function postRequest(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error(`Request failed: ${response.statusText}`);
        } catch (error) {
            console.error(`Error posting to ${url}:`, error);
            fetchData();
        }
    }

    // --- EVENT LISTENERS ---
    officerList.addEventListener('dragover', handleDragOver);
    officerList.addEventListener('drop', (e) => handleDrop(e, officerList));

    document.body.addEventListener('dragover', handleDragOver);
    document.body.addEventListener('drop', (e) => {
        const seat = e.target.closest('.vehicle-seat');
        const role = e.target.closest('.role-officer');
        const activity = e.target.closest('.activity-officers');
        if (seat) handleDrop(e, seat);
        else if (role) handleDrop(e, role);
        else if (activity) handleDrop(e, activity);
    });

    // --- MODAL ---
    const callsignModal = document.getElementById('callsign-modal');
    const openCallsignModalBtn = document.getElementById('open-callsign-modal');
    const closeCallsignModalBtn = document.getElementById('close-callsign-modal');
    const modalBody = document.getElementById('callsign-modal-body');

    openCallsignModalBtn.addEventListener('click', async () => {
        try {
            const response = await fetch('index.php?page=get_settings&key=10-codes');
            const data = await response.json();
            const codes = JSON.parse(data.value);
            let html = '<dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-4">';
            for (const code in codes) {
                html += `<div class="relative"><dt><span class="font-bold text-white">${code}</span></dt><dd class="pl-2 text-brand-text-secondary">${codes[code]}</dd></div>`;
            }
            html += '</dl>';
            modalBody.innerHTML = html;
            callsignModal.style.display = 'flex';
        } catch(e) {
            modalBody.innerHTML = '<p class="text-red-400">Fehler beim Laden der 10-Codes.</p>';
            callsignModal.style.display = 'flex';
        }
    });

    closeCallsignModalBtn.addEventListener('click', () => {
        callsignModal.style.display = 'none';
    });

    // Initial data load
    fetchData();
});