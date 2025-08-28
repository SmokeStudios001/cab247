<?php
include 'includes/auth.php';
// admin-panel/live-map.php
$page_title = "Live Map Tracking";
include 'includes/header.php';

// Get all active drivers
$drivers = $conn->query("SELECT d.*, ct.name as car_type, ct.icon 
                         FROM drivers d 
                         LEFT JOIN car_types ct ON d.car_type_id = ct.id 
                         WHERE d.status != 'offline' AND d.is_verified = 1");
?>

<div class="content">
    <h1>Live Map Tracking</h1>
    
    <div class="map-container">
        <div id="map"></div>
    </div>
    
    <div class="drivers-list">
        <h2>Active Drivers</h2>
        <div class="drivers-grid">
            <?php while ($driver = $drivers->fetch_assoc()): ?>
            <div class="driver-card" data-id="<?php echo $driver['id']; ?>" 
                 data-lat="<?php echo $driver['latitude'] ?? '40.7128'; ?>" 
                 data-lng="<?php echo $driver['longitude'] ?? '-74.0060'; ?>"
                 data-status="<?php echo $driver['status']; ?>">
                <div class="driver-info">
                    <h4><?php echo htmlspecialchars($driver['name']); ?></h4>
                    <p><?php echo htmlspecialchars($driver['car_model'] . ' (' . $driver['car_number'] . ')'); ?></p>
                    <p>Status: <span class="status-<?php echo strtolower($driver['status']); ?>">
                        <?php echo ucfirst($driver['status']); ?>
                    </span></p>
                    <?php if ($driver['latitude'] && $driver['longitude']): ?>
                        <p>Location: <?php echo number_format($driver['latitude'], 4); ?>, <?php echo number_format($driver['longitude'], 4); ?></p>
                    <?php else: ?>
                        <p>Location: Not available</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    // Leaflet map initialization
    const map = L.map('map').setView([40.7128, -74.0060], 13); // New York City
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const driverMarkers = {};
    const driverIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/1048/1048325.png',
        iconSize: [38, 38],
        iconAnchor: [19, 38],
        popupAnchor: [0, -38]
    });

    function updateDriverPositions() {
        fetch('get-drivers-location.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove old markers
                    for (const driverId in driverMarkers) {
                        if (driverMarkers.hasOwnProperty(driverId)) {
                            map.removeLayer(driverMarkers[driverId]);
                            delete driverMarkers[driverId];
                        }
                    }

                    // Add new markers
                    data.drivers.forEach(driver => {
                        if (driver.latitude && driver.longitude) {
                            const marker = L.marker([driver.latitude, driver.longitude], {
                                icon: driverIcon
                            }).addTo(map);

                            const popupContent = `
                                <strong>${driver.name}</strong><br>
                                ${driver.car_model} (${driver.car_number})<br>
                                Status: ${driver.status}<br>
                                Car Type: ${driver.car_type}
                            `;
                            marker.bindPopup(popupContent);
                            driverMarkers[driver.id] = marker;
                        }
                    });
                }
            })
            .catch(error => console.error('Error fetching driver locations:', error));
    }

    // Initial update
    updateDriverPositions();

    // Update every 5 seconds
    setInterval(updateDriverPositions, 5000);

    // Center map on driver when clicked
    document.querySelectorAll('.driver-card').forEach(card => {
        card.addEventListener('click', function() {
            const lat = parseFloat(this.getAttribute('data-lat'));
            const lng = parseFloat(this.getAttribute('data-lng'));
            const driverId = this.getAttribute('data-id');
            
            if (lat && lng) {
                map.setView([lat, lng], 15);
                
                // Open popup if marker exists
                if (driverMarkers[driverId]) {
                    driverMarkers[driverId].openPopup();
                }
            }
        });
    });

    // Add geolocation control
    map.locate({setView: false, maxZoom: 16});

    function onLocationFound(e) {
        const radius = e.accuracy / 2;
        
        L.marker(e.latlng).addTo(map)
            .bindPopup("You are within " + Math.round(radius) + " meters from this point").openPopup();
        
        L.circle(e.latlng, radius).addTo(map);
    }

    map.on('locationfound', onLocationFound);

    function onLocationError(e) {
        console.log("Location access denied or not available.");
    }

    map.on('locationerror', onLocationError);

    // Add zoom control
    L.control.zoom({
        position: 'topright'
    }).addTo(map);

    // Add scale control
    L.control.scale({
        position: 'bottomright'
    }).addTo(map);
</script>