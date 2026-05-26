// assets/js/maps.js
// Creates a Leaflet map centered on Ormoc City.
// Used by both the employer and seeker dashboards.

var ORMOC_LAT  = 11.0044;
var ORMOC_LNG  = 124.6075;
var ORMOC_ZOOM = 13;

// Creates a map inside the HTML element with the given ID.
// Returns the Leaflet map object so other files can use it.
function createBaseOrmocMap(elementId) {
    var container = document.getElementById(elementId);

    if (!container) {
        console.error("Could not find a element with id: " + elementId);
        return null;
    }

    var map = L.map(elementId, {
        dragging: true,
        scrollWheelZoom: true
    }).setView([ORMOC_LAT, ORMOC_LNG], ORMOC_ZOOM);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Give Leaflet a moment to measure the container before rendering tiles
    setTimeout(function() {
        map.invalidateSize();
    }, 200);

    return map;
}