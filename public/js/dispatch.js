document.addEventListener('DOMContentLoaded', () => {

    const officerList = document.getElementById('officer-list');
    const vehicleGrid = document.getElementById('vehicle-grid');

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
        // updateHeaderRoles(data.header_roles); // To be implemented
    }

    /**
     * Re-renders the list of available officers.
     * @param {Array} officers An array of officer objects.
     */
    function updateAvailableOfficers(officers) {
        officerList.innerHTML = ''; // Clear the list
        if (officers.length === 0) {
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
     * @param {Array} vehicles An array of vehicle objects.
     */
    function updateVehicleGrid(vehicles) {
        vehicleGrid.innerHTML = ''; // Clear the grid
        if (vehicles.length === 0) {
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
                    <span class="vehicle-status status-${vehicle.status}">Code ${vehicle.status}</span>
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

    // --- DRAG & DROP LOGIC ---
    // This will be expanded upon later.

    // When dragging starts on an officer card
    document.addEventListener('dragstart', (e) => {
        if (e.target.classList.contains('officer-card')) {
            e.dataTransfer.setData('text/plain', e.target.dataset.officerId);
            e.target.style.opacity = '0.5';
        }
    });

    // When dragging ends
     document.addEventListener('dragend', (e) => {
        if (e.target.classList.contains('officer-card')) {
            e.target.style.opacity = '1';
        }
    });

    // When dragging over a potential drop zone (a seat)
    document.addEventListener('dragover', (e) => {
        if (e.target.classList.contains('seat')) {
            e.preventDefault(); // Allow the drop
            e.target.classList.add('drag-over');
        }
    });

    // When leaving a potential drop zone
    document.addEventListener('dragleave', (e) => {
        if (e.target.classList.contains('seat')) {
            e.target.classList.remove('drag-over');
        }
    });

    // When dropping an officer on a seat
    document.addEventListener('drop', (e) => {
        e.preventDefault();
        if (e.target.classList.contains('seat') && !e.target.classList.contains('occupied')) {
            e.target.classList.remove('drag-over');
            const officerId = e.dataTransfer.getData('text/plain');
            const seat = e.target;
            const vehicleCard = seat.closest('.vehicle-card');
            const vehicleId = vehicleCard.dataset.vehicleId;
            const seatIndex = seat.dataset.seatIndex;

            assignOfficer(officerId, vehicleId, seatIndex);
        }
    });

    /**
     * Sends the assignment of an officer to a vehicle to the backend.
     * @param {string} officerId
     * @param {string} vehicleId
     * @param {string} seatIndex
     */
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
                // The assignment was successful, immediately refresh the view.
                fetchDispatchStatus();
            } else {
                console.error('Failed to assign officer:', result.message);
                alert('Fehler beim Zuweisen des Beamten: ' + (result.message || 'Unbekannter Fehler.'));
            }
        } catch (error) {
            console.error('Error during officer assignment:', error);
            alert('Ein Netzwerk- oder Serverfehler ist aufgetreten.');
        }
    }


    // --- CLICK EVENT HANDLING ---
    vehicleGrid.addEventListener('click', (e) => {
        if (e.target.classList.contains('vehicle-status')) {
            const vehicleCard = e.target.closest('.vehicle-card');
            const vehicleId = vehicleCard.dataset.vehicleId;
            const currentStatusClass = Array.from(e.target.classList).find(c => c.startsWith('status-'));
            const currentStatus = currentStatusClass ? parseInt(currentStatusClass.split('-')[1]) : 1;

            // Cycle through statuses: 1, 2, 3, 5, 6, 7
            const statusCycle = [1, 2, 3, 5, 6, 7];
            const currentIndex = statusCycle.indexOf(currentStatus);
            const newStatus = currentIndex === -1 ? statusCycle[0] : statusCycle[(currentIndex + 1) % statusCycle.length];

            setVehicleStatus(vehicleId, newStatus);
        }

        // Handle clicks on Funk or Callsign
        if (e.target.classList.contains('vehicle-funk') || e.target.classList.contains('vehicle-callsign')) {
            const vehicleCard = e.target.closest('.vehicle-card');
            const vehicleId = vehicleCard.dataset.vehicleId;
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

    /**
     * Sends the new status of a vehicle to the backend.
     * @param {string} vehicleId
     * @param {number} status
     */
    async function setVehicleStatus(vehicleId, status) {
        try {
            const response = await fetch('api/set_vehicle_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    vehicleId: parseInt(vehicleId),
                    status: status
                }),
            });
            const result = await response.json();
            if (result.success) {
                fetchDispatchStatus();
            } else {
                console.error('Failed to update status:', result.message);
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


    // --- INITIAL FETCH & POLLING ---
    fetchDispatchStatus(); // Fetch data immediately on page load
    setInterval(fetchDispatchStatus, 5000); // Poll for new data every 5 seconds
});