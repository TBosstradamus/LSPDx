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
            console.log("Fetched data:", data);
            populateUI(data);
        } catch (error) {
            console.error("Could not fetch dispatch data:", error);
            officerList.innerHTML = '<p class="text-red-400 p-2">Fehler beim Laden der Daten.</p>';
        }
    }

    // --- UI POPULATION ---
    function populateUI(data) {
        // Clear existing elements
        officerList.innerHTML = '';
        vehicleGrid.innerHTML = '';
        // Clear officers from special roles, but not the roles themselves
        document.querySelectorAll('.role-officer').forEach(el => el.innerHTML = '--');
        document.querySelectorAll('.activity-officers').forEach(el => el.innerHTML = '');


        // Populate available officers
        data.officers.available.forEach(officer => {
            const officerEl = createOfficerElement(officer);
            officerList.appendChild(officerEl);
        });

        // Populate vehicles and assigned officers
        data.vehicles.forEach(vehicle => {
            const vehicleEl = createVehicleElement(vehicle);
            vehicle.assigned_officers.forEach((officer, index) => {
                if (officer) {
                    const officerEl = createOfficerElement(officer);
                    const seat = vehicleEl.querySelector(`.vehicle-seat[data-seat-index="${index}"]`);
                    if (seat) {
                        seat.appendChild(officerEl);
                    }
                }
            });
            vehicleGrid.appendChild(vehicleEl);
        });

        // Populate header roles
        data.assignments.header.forEach(assignment => {
            const roleEl = headerRoles.querySelector(`.header-role[data-role-name="${assignment.assignment_id}"] .role-officer`);
            if (roleEl) {
                const officer = data.officers.all.find(o => o.id === assignment.officer_id);
                 if(officer) {
                    roleEl.textContent = `${officer.firstName} ${officer.lastName}`;
                    roleEl.dataset.officerId = officer.id;
                 }
            }
        });

        // Populate activity zones
        data.assignments.activity.forEach(assignment => {
            const activityEl = activityZones.querySelector(`.activity-zone[data-activity-name="${assignment.assignment_id}"] .activity-officers`);
            if (activityEl) {
                const officer = data.officers.all.find(o => o.id === assignment.officer_id);
                if(officer) {
                    activityEl.appendChild(createOfficerElement(officer));
                }
            }
        });
    }

    // --- ELEMENT CREATION ---
    function createOfficerElement(officer) {
        const el = document.createElement('div');
        el.className = 'officer-draggable bg-gray-700 p-2 rounded shadow flex items-center cursor-move';
        el.draggable = true;
        el.dataset.officerId = officer.id;
        el.innerHTML = `<span class="font-bold text-white">${officer.lastName}, ${officer.firstName}</span>`;

        el.addEventListener('dragstart', handleDragStart);
        el.addEventListener('dragend', handleDragEnd);
        return el;
    }

    function createVehicleElement(vehicle) {
        const el = document.createElement('div');
        el.className = 'vehicle-card bg-brand-bg border border-brand-border rounded-lg p-3';
        el.dataset.vehicleId = vehicle.id;

        let seatsHTML = '';
        for (let i = 0; i < vehicle.capacity; i++) {
            seatsHTML += `<div class="vehicle-seat border border-dashed border-gray-600 rounded p-1 min-h-[40px]" data-seat-index="${i}"></div>`;
        }

        el.innerHTML = `
            <div class="flex justify-between items-center mb-2">
                <h4 class="font-bold text-white">${vehicle.name}</h4>
                <span class="text-xs font-mono px-2 py-1 bg-gray-700 rounded">${vehicle.licensePlate}</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                ${seatsHTML}
            </div>
        `;
        return el;
    }


    // --- DRAG & DROP HANDLERS ---
    function handleDragStart(e) {
        draggedOfficer = e.target;
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => {
            e.target.style.display = 'none';
        }, 0);
    }

    function handleDragEnd(e) {
        draggedOfficer.style.display = 'flex';
        draggedOfficer = null;
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDrop(e, targetElement) {
        e.preventDefault();
        if (!draggedOfficer) return;

        const officerId = draggedOfficer.dataset.officerId;

        // Case 1: Dropped on a vehicle seat
        if (targetElement.classList.contains('vehicle-seat')) {
            const vehicleCard = targetElement.closest('.vehicle-card');
            const vehicleId = vehicleCard.dataset.vehicleId;
            const seatIndex = targetElement.dataset.seatIndex;

            // Prevent dropping if seat is occupied
            if(targetElement.children.length === 0) {
                 targetElement.appendChild(draggedOfficer);
                 assignOfficerToVehicle(officerId, vehicleId, seatIndex);
            }
        }
        // Case 2: Dropped on the main officer list (unassignment)
        else if (targetElement.id === 'officer-list') {
             targetElement.appendChild(draggedOfficer);
             unassignOfficer(officerId);
        }
        // Case 3: Dropped on a header role
        else if(targetElement.classList.contains('header-role')) {
             const roleName = targetElement.dataset.roleName;
             assignOfficerToHeader(officerId, roleName);
             // We don't move the element, we just update text, so we refresh
             fetchData();
        }
        // Case 4: Dropped on an activity zone
        else if (targetElement.classList.contains('activity-officers')) {
            targetElement.appendChild(draggedOfficer);
            const activityName = targetElement.closest('.activity-zone').dataset.activityName;
            assignOfficerToActivity(officerId, activityName);
        }
    }

    // --- API CALLS ---
    async function assignOfficerToVehicle(officerId, vehicleId, seatIndex) {
        console.log(`Assigning officer ${officerId} to vehicle ${vehicleId}, seat ${seatIndex}`);
        await postRequest('index.php?page=assign_officer_to_vehicle', { officerId, vehicleId, seatIndex });
    }

    async function unassignOfficer(officerId) {
        console.log(`Unassigning officer ${officerId}`);
        await postRequest('index.php?page=unassign_officer', { officerId });
    }

    async function assignOfficerToHeader(officerId, roleName) {
        console.log(`Assigning officer ${officerId} to header role ${roleName}`);
        await postRequest('index.php?page=assign_officer_to_header', { officerId, roleName });
    }

    async function assignOfficerToActivity(officerId, activityName) {
        console.log(`Assigning officer ${officerId} to activity ${activityName}`);
        await postRequest('index.php?page=assign_officer_to_activity', { officerId, activityName });
    }

    async function postRequest(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                throw new Error(`Request failed: ${response.statusText}`);
            }
            // After any successful assignment, refresh the whole UI to ensure consistency
            fetchData();
        } catch (error) {
            console.error(`Error posting to ${url}:`, error);
            // If something fails, refresh to revert UI to last known good state
            fetchData();
        }
    }

    // --- EVENT LISTENERS ---
    officerList.addEventListener('dragover', handleDragOver);
    officerList.addEventListener('drop', (e) => handleDrop(e, officerList));

    vehicleGrid.addEventListener('dragover', handleDragOver);
    vehicleGrid.addEventListener('drop', (e) => {
        const seat = e.target.closest('.vehicle-seat');
        if (seat) {
            handleDrop(e, seat);
        }
    });

    headerRoles.addEventListener('dragover', handleDragOver);
    headerRoles.addEventListener('drop', (e) => {
        const role = e.target.closest('.header-role');
        if(role) {
             handleDrop(e, role);
        }
    });

    activityZones.addEventListener('dragover', handleDragOver);
    activityZones.addEventListener('drop', (e) => {
        const activityOfficers = e.target.closest('.activity-officers');
        if(activityOfficers) {
             handleDrop(e, activityOfficers);
        }
    });


    // --- MODAL ---
    const callsignModal = document.getElementById('callsign-modal');
    const openCallsignModalBtn = document.getElementById('open-callsign-modal');
    const closeCallsignModalBtn = document.getElementById('close-callsign-modal');
    const modalBody = document.getElementById('callsign-modal-body');

    openCallsignModalBtn.addEventListener('click', async () => {
        // Fetch 10-codes from settings and display them
        try {
            const response = await fetch('index.php?page=get_settings&key=10-codes');
            const data = await response.json();
            const codes = JSON.parse(data.value); // The value is a JSON string
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