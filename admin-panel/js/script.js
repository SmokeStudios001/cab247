// admin-panel/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const toggleSidebar = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleSidebar && sidebar) {
        toggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('sidebar-collapsed');
        });
    }
    
    // Handle modal functionality
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const span = modal.querySelector('.close');
        
        if (span) {
            span.onclick = function() {
                modal.style.display = 'none';
            };
        }
        
        window.onclick = function(event) {
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        };
    });
    
    // Handle edit buttons for all edit modals
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const phone = this.getAttribute('data-phone');
            const license_number = this.getAttribute('data-license_number');
            const car_model = this.getAttribute('data-car_model');
            const car_number = this.getAttribute('data-car_number');
            const car_type_id = this.getAttribute('data-car_type_id');
            const base_fare = this.getAttribute('data-base_fare');
            const per_km = this.getAttribute('data-per_km');
            const per_minute = this.getAttribute('data-per_minute');
            const waiting_fee = this.getAttribute('data-waiting_fee');
            const status = this.getAttribute('data-status');
            
            // Set values based on which form we're editing
            if (document.getElementById('edit_id')) {
                document.getElementById('edit_id').value = id;
            }
            
            if (document.getElementById('edit_name')) {
                document.getElementById('edit_name').value = name;
            }
            
            if (document.getElementById('edit_email')) {
                document.getElementById('edit_email').value = email;
            }
            
            if (document.getElementById('edit_phone')) {
                document.getElementById('edit_phone').value = phone;
            }
            
            if (document.getElementById('edit_license_number')) {
                document.getElementById('edit_license_number').value = license_number;
            }
            
            if (document.getElementById('edit_car_model')) {
                document.getElementById('edit_car_model').value = car_model;
            }
            
            if (document.getElementById('edit_car_number')) {
                document.getElementById('edit_car_number').value = car_number;
            }
            
            if (document.getElementById('edit_car_type_id')) {
                document.getElementById('edit_car_type_id').value = car_type_id;
            }
            
            if (document.getElementById('edit_base_fare')) {
                document.getElementById('edit_base_fare').value = base_fare;
            }
            
            if (document.getElementById('edit_per_km')) {
                document.getElementById('edit_per_km').value = per_km;
            }
            
            if (document.getElementById('edit_per_minute')) {
                document.getElementById('edit_per_minute').value = per_minute;
            }
            
            if (document.getElementById('edit_waiting_fee')) {
                document.getElementById('edit_waiting_fee').value = waiting_fee;
            }
            
            if (document.getElementById('edit_status')) {
                document.getElementById('edit_status').checked = status === '1';
            }
            
            document.getElementById('editModal').style.display = 'block';
        });
    });
    
    // Status toggle handling
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const table = this.getAttribute('data-table');
            const status = this.checked ? 1 : 0;
            
            // Show confirmation for status change
            if (confirm('Are you sure you want to change the status?')) {
                // Use fetch API to update status securely
                fetch('update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id, table: table, status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Error updating status: ' + data.message);
                        this.checked = !this.checked; // Reset to original state
                    } else {
                        location.reload(); // Reload page to reflect changes
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating status.');
                    this.checked = !this.checked; // Reset to original state
                });
            } else {
                // Reset to original state if cancelled
                this.checked = !this.checked;
            }
        });
    });
    
    // Status selection handling
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const status = this.value;
            const originalValue = this.getAttribute('data-original-value');
            
            if (confirm('Are you sure you want to change the status to ' + status + '?')) {
                // Use fetch API to update status securely
                fetch('update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id, table: 'rides', status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Error updating status: ' + data.message);
                        this.value = originalValue; // Reset to original value
                    } else {
                        location.reload(); // Reload page to reflect changes
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating status.');
                    this.value = originalValue; // Reset to original value
                });
            } else {
                // Reset to original value if cancelled
                this.value = originalValue;
            }
        });
    });
    
    // Handle ride details modal
    document.querySelectorAll('.btn-view-details').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const rideId = this.getAttribute('data-id');
            
            fetch(`get-ride-details.php?id=${rideId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ride = data.data;
                        const detailsHtml = `
                            <h4>Ride Details #${ride.id}</h4>
                            <div class="detail-row">
                                <strong>Passenger:</strong> ${ride.passenger_name} (${ride.passenger_phone})
                            </div>
                            <div class="detail-row">
                                <strong>Driver:</strong> ${ride.driver_name ? `${ride.driver_name} (${ride.driver_phone})` : 'Not assigned'}
                            </div>
                            <div class="detail-row">
                                <strong>Car Type:</strong> ${ride.car_type_name}
                            </div>
                            <div class="detail-row">
                                <strong>Pickup:</strong> ${ride.pickup_address}
                            </div>
                            <div class="detail-row">
                                <strong>Destination:</strong> ${ride.destination_address}
                            </div>
                            <div class="detail-row">
                                <strong>Fare:</strong> ${ride.currency}${ride.fare}
                            </div>
                            <div class="detail-row">
                                <strong>Distance:</strong> ${ride.distance} km
                            </div>
                            <div class="detail-row">
                                <strong>Duration:</strong> ${ride.duration} mins
                            </div>
                            <div class="detail-row">
                                <strong>Status:</strong> <span class="status-badge status-${ride.status}">${ride.status}</span>
                            </div>
                            <div class="detail-row">
                                <strong>Payment Status:</strong> <span class="status-badge status-${ride.payment_status}">${ride.payment_status}</span>
                            </div>
                        `;
                        
                        document.getElementById('rideDetails').innerHTML = detailsHtml;
                        document.getElementById('detailsModal').style.display = 'block';
                    } else {
                        alert('Error loading ride details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading ride details');
                });
        });
    });
    
    // Verification toggle handling
    document.querySelectorAll('.verification-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const driverId = this.getAttribute('data-id');
            const isVerified = this.checked;
            const status = isVerified ? 'verify' : 'unverify';
            
            if (confirm('Are you sure you want to ' + status + ' this driver?')) {
                window.location.href = `drivers.php?toggle_verification=${driverId}`;
            } else {
                // Reset to original state if cancelled
                this.checked = !this.checked;
            }
        });
    });
    
    // Map functionality (if map exists on page)
    if (typeof L !== 'undefined' && document.getElementById('map')) {
        // Initialize the map with default view (will be updated based on user's location)
        window.map = L.map('map').setView([0, 0], 2);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(window.map);
        
        // Custom taxi icons
        window.freeTaxiIcon = L.divIcon({
            className: 'taxi-marker',
            html: '<div style="background-color: #28a745; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        window.pobTaxiIcon = L.divIcon({
            className: 'taxi-marker',
            html: '<div style="background-color: #dc3545; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        window.enrouteTaxiIcon = L.divIcon({
            className: 'taxi-marker',
            html: '<div style="background-color: #ffc107; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        window.aboutFreeTaxiIcon = L.divIcon({
            className: 'taxi-marker',
            html: '<div style="background-color: #17a2b8; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        // User location marker
        window.userLocationMarker = null;
        window.userLocationCircle = null;
        
        // Sample taxi data (will be positioned relative to user's location)
        window.taxis = [];
        
        // Sample routes
        window.routes = [];
        
        // Add taxis to the map
        window.taxiMarkers = {};
        
        // Add routes to the map
        window.routeLayers = {};
        
        // Helper function to get status text
        window.getStatusText = function(status) {
            switch(status) {
                case 'free': return 'Available';
                case 'pob': return 'Passenger on Board';
                case 'enroute': return 'Enroute to Pickup';
                case 'about-free': return 'About to be Free';
                default: return status;
            }
        };
        
        // Function to update taxi positions relative to user's location
        window.updateTaxisPosition = function(userLat, userLng) {
            // Clear existing taxis and routes
            Object.values(window.taxiMarkers).forEach(marker => window.map.removeLayer(marker));
            Object.values(window.routeLayers).forEach(layer => window.map.removeLayer(layer));
            
            window.taxiMarkers = {};
            window.routeLayers = {};
            
            // Generate taxis around user's location
            window.taxis = [
                { 
                    id: 1, 
                    lat: userLat + (Math.random() * 0.02 - 0.01), 
                    lng: userLng + (Math.random() * 0.02 - 0.01), 
                    status: 'free', 
                    name: 'Taxi #101', 
                    carModel: 'Toyota Camry', 
                    price: 2.5, 
                    distance: (Math.random() * 2).toFixed(1) 
                },
                { 
                    id: 2, 
                    lat: userLat + (Math.random() * 0.02 - 0.01), 
                    lng: userLng + (Math.random() * 0.02 - 0.01), 
                    status: 'pob', 
                    name: 'Taxi #102', 
                    carModel: 'Honda Accord', 
                    price: 3.2, 
                    distance: (Math.random() * 2).toFixed(1) 
                },
                { 
                    id: 3, 
                    lat: userLat + (Math.random() * 0.02 - 0.01), 
                    lng: userLng + (Math.random() * 0.02 - 0.01), 
                    status: 'enroute', 
                    name: 'Taxi #103', 
                    carModel: 'Toyota Prius', 
                    price: 2.8, 
                    distance: (Math.random() * 2).toFixed(1) 
                },
                { 
                    id: 4, 
                    lat: userLat + (Math.random() * 0.02 - 0.01), 
                    lng: userLng + (Math.random() * 0.02 - 0.01), 
                    status: 'about-free', 
                    name: 'Taxi #104', 
                    carModel: 'Nissan Altima', 
                    price: 3.0, 
                    distance: (Math.random() * 2).toFixed(1) 
                },
                { 
                    id: 5, 
                    lat: userLat + (Math.random() * 0.02 - 0.01), 
                    lng: userLng + (Math.random() * 0.02 - 0.01), 
                    status: 'free', 
                    name: 'Taxi #105', 
                    carModel: 'Hyundai Elantra', 
                    price: 2.3, 
                    distance: (Math.random() * 2).toFixed(1) 
                }
            ];
            
            // Generate routes relative to user's location
            window.routes = [
                { 
                    taxiId: 3, 
                    type: 'pickup', 
                    coordinates: [
                        [userLat + 0.005, userLng - 0.005],
                        [userLat + 0.007, userLng - 0.003],
                        [userLat + 0.009, userLng - 0.001],
                        [userLat + 0.011, userLng + 0.001]
                    ]
                },
                { 
                    taxiId: 2, 
                    type: 'dropoff', 
                    coordinates: [
                        [userLat - 0.005, userLng + 0.005],
                        [userLat - 0.007, userLng + 0.007],
                        [userLat - 0.009, userLng + 0.009],
                        [userLat - 0.011, userLng + 0.011]
                    ]
                }
            ];
            
            // Add taxis to the map
            window.taxis.forEach(taxi => {
                let icon;
                
                switch(taxi.status) {
                    case 'free':
                        icon = window.freeTaxiIcon;
                        break;
                    case 'pob':
                        icon = window.pobTaxiIcon;
                        break;
                    case 'enroute':
                        icon = window.enrouteTaxiIcon;
                        break;
                    case 'about-free':
                        icon = window.aboutFreeTaxiIcon;
                        break;
                    default:
                        icon = window.freeTaxiIcon;
                }
                
                const marker = L.marker([taxi.lat, taxi.lng], { icon: icon }).addTo(window.map);
                
                // Create popup content
                const popupContent = `
                    <div class="map-popup">
                        <div class="popup-header">${taxi.name}</div>
                        <div class="popup-detail">${taxi.carModel}</div>
                        <div class="popup-detail">Status: ${window.getStatusText(taxi.status)}</div>
                        <div class="popup-detail popup-price">Price: $${taxi.price.toFixed(2)}/km</div>
                        <div class="popup-detail popup-distance">Distance: ${taxi.distance} km away</div>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                window.taxiMarkers[taxi.id] = marker;
            });
            
            // Add routes to the map
            window.routes.forEach(route => {
                const color = route.type === 'pickup' ? '#ff9800' : '#333';
                const polyline = L.polyline(route.coordinates, { color: color, weight: 5 }).addTo(window.map);
                window.routeLayers[route.taxiId] = polyline;
            });
        };
        
        // Function to center map on user's location
        window.centerMapOnUser = function() {
            if (navigator.geolocation) {
                const locationStatus = document.getElementById('location-status');
                if (locationStatus) {
                    locationStatus.textContent = 'Locating...';
                }
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;
                        
                        // Remove existing user location marker and circle if they exist
                        if (window.userLocationMarker) {
                            window.map.removeLayer(window.userLocationMarker);
                        }
                        if (window.userLocationCircle) {
                            window.map.removeLayer(window.userLocationCircle);
                        }
                        
                        // Add user location marker
                        window.userLocationMarker = L.marker([userLat, userLng]).addTo(window.map)
                            .bindPopup("You are here").openPopup();
                        
                        // Add accuracy circle
                        window.userLocationCircle = L.circle([userLat, userLng], {
                            radius: accuracy,
                            color: '#007bff',
                            fillColor: '#007bff',
                            fillOpacity: 0.2
                        }).addTo(window.map);
                        
                        // Center map on user's location
                        window.map.setView([userLat, userLng], 15);
                        
                        // Update taxi positions relative to user's location
                        if (typeof window.updateTaxisPosition === 'function') {
                            window.updateTaxisPosition(userLat, userLng);
                        }
                        
                        if (locationStatus) {
                            locationStatus.textContent = 
                                `Location found (accuracy: ${Math.round(accuracy)} meters)`;
                        }
                    },
                    function(error) {
                        let errorMsg;
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = "Location access denied. Using default location.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = "Location information unavailable. Using default location.";
                                break;
                            case error.TIMEOUT:
                                errorMsg = "Location request timed out. Using default location.";
                                break;
                            default:
                                errorMsg = "Unknown error occurred. Using default location.";
                                break;
                        }
                        
                        const locationStatus = document.getElementById('location-status');
                        if (locationStatus) {
                            locationStatus.textContent = errorMsg;
                        }
                        
                        // Use a default location if geolocation fails
                        const defaultLat = 40.7128;
                        const defaultLng = -74.0060;
                        window.map.setView([defaultLat, defaultLng], 13);
                        if (typeof window.updateTaxisPosition === 'function') {
                            window.updateTaxisPosition(defaultLat, defaultLng);
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                const locationStatus = document.getElementById('location-status');
                if (locationStatus) {
                    locationStatus.textContent = 
                        "Geolocation is not supported by this browser. Using default location.";
                }
                
                // Use a default location if geolocation is not supported
                const defaultLat = 40.7128;
                const defaultLng = -74.0060;
                window.map.setView([defaultLat, defaultLng], 13);
                if (typeof window.updateTaxisPosition === 'function') {
                    window.updateTaxisPosition(defaultLat, defaultLng);
                }
            }
        };
        
        // Center map button
        const centerMapBtn = document.getElementById('center-map');
        if (centerMapBtn) {
            centerMapBtn.addEventListener('click', window.centerMapOnUser);
        }
        
        // Refresh map button
        const refreshMapBtn = document.getElementById('refresh-map');
        if (refreshMapBtn) {
            refreshMapBtn.addEventListener('click', function() {
                // Get current center of the map
                const center = window.map.getCenter();
                
                // Simulate updating taxi positions
                if (window.taxis && window.taxiMarkers) {
                    window.taxis.forEach(taxi => {
                        // Add small random movement
                        const latChange = (Math.random() - 0.5) * 0.002;
                        const lngChange = (Math.random() - 0.5) * 0.002;
                        
                        taxi.lat += latChange;
                        taxi.lng += lngChange;
                        
                        // Update marker position
                        if (window.taxiMarkers[taxi.id]) {
                            window.taxiMarkers[taxi.id].setLatLng([taxi.lat, taxi.lng]);
                        }
                        
                        // Update distance (randomize slightly)
                        taxi.distance = (parseFloat(taxi.distance) + (Math.random() - 0.5) * 0.2).toFixed(1);
                    });
                }
                
                const locationStatus = document.getElementById('location-status');
                if (locationStatus) {
                    locationStatus.textContent = 'Map data refreshed!';
                }
            });
        }
        
        // Add zoom control
        L.control.zoom({
            position: 'topright'
        }).addTo(window.map);
        
        // Add scale control
        L.control.scale({
            position: 'bottomright'
        }).addTo(window.map);
        
        // Center map on user's location when page loads
        setTimeout(function() {
            if (typeof window.centerMapOnUser === 'function') {
                window.centerMapOnUser();
            }
        }, 1000);
        
        // Trigger a resize event to ensure map renders correctly
        setTimeout(function() {
            window.map.invalidateSize();
        }, 100);
    }
});