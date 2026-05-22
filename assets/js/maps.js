// assets/js/maps.js

// 📍 Centralized Geographic Anchor Coordinates for Ormoc City Center
const ORMOC_DEFAULT_LAT = 11.0044;
const ORMOC_DEFAULT_LNG = 124.6075;
const DEFAULT_ZOOM = 13;

/**
 * Initializes a standard Leaflet map configuration bounded to Ormoc City.
 * @param {string} elementId - The raw HTML div string identifier container (e.g., 'map')
 * @returns {L.Map} - The instantiated Leaflet Map instance
 */
// assets/js/maps.js

function createBaseOrmocMap(elementId) {
    if (!document.getElementById(elementId)) {
        console.error(`Map target element #${elementId} could not be located in the view layout.`);
        return null;
    }

    // Explicitly inject interaction settings into the configuration object
    const mapInstance = L.map(elementId, {
        dragging: true,      // 🔥 Forces map dragging to stay active
        tap: true,           // 🔥 Ensures mobile/trackpad taps register smoothly
        scrollWheelZoom: true
    }).setView([ORMOC_DEFAULT_LAT, ORMOC_DEFAULT_LNG], DEFAULT_ZOOM);

    // Render free OpenStreetMap grid tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapInstance);

    // 🔄 UI Layout Fix: Force Leaflet to recalculate container dimensions
    setTimeout(() => {
        mapInstance.invalidateSize();
    }, 200);

    return mapInstance;
}

/**
 * Helper utility to build a standard map marker popup design layout string
 * @param {string} title - Job listing position title
 * @param {string} company - Company organization name
 * @param {string} desc - Short text job description content snippet
 * @returns {string} - Combined HTML markup layout template string
 */
function buildJobPopupTemplate(title, company, desc) {
  return `
    <div style="font-family: sans-serif; padding: 5px;">
      <h3 style="margin: 0 0 4px 0; color: #333;">${title}</h3>
      <h4 style="margin: 0 0 8px 0; color: #666; font-weight: normal;">${company}</h4>
      <p style="margin: 0 0 8px 0; font-size: 12px; color: #444; max-height: 60px; overflow: hidden; text-overflow: ellipsis;">${desc}</p>
      <button class="map-apply-btn" style="background: #007bff; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">View Opportunity</button>
    </div>
  `;
}