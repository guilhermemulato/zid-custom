(function () {
  let map;
  let markers = new Map();
  let lastTs = 0;
  let polling = null;

  function showOffline(mapEl) {
    mapEl.classList.add('map-offline');
    mapEl.innerHTML = '<div class=\"map-overlay\">Mapa offline (sem tiles)</div>';
  }

  function clearOffline(mapEl) {
    mapEl.classList.remove('map-offline');
    const overlay = mapEl.querySelector('.map-overlay');
    if (overlay) overlay.remove();
  }

  function buildMap(mapEl) {
    map = L.map(mapEl, {
      zoomControl: true,
      attributionControl: false,
    }).setView([0, 0], 2);

    const tilesUrl = (window.ZID_UI && window.ZID_UI.config && window.ZID_UI.config.tilesUrl) || '';
    if (tilesUrl) {
      const layer = L.tileLayer(tilesUrl, { maxZoom: 8, minZoom: 2 });
      layer.on('tileerror', () => showOffline(mapEl));
      layer.on('load', () => clearOffline(mapEl));
      layer.addTo(map);
    } else {
      showOffline(mapEl);
    }
  }

  function upsertMarkers(events) {
    const seen = new Set();
    events.forEach((evt) => {
      seen.add(evt.id);
      if (markers.has(evt.id)) {
        const marker = markers.get(evt.id);
        marker.setLatLng([evt.lat, evt.lng]);
        marker.setPopupContent(`${evt.label} (${evt.count})`);
      } else {
        const marker = L.marker([evt.lat, evt.lng]);
        marker.bindPopup(`${evt.label} (${evt.count})`);
        marker.addTo(map);
        markers.set(evt.id, marker);
      }
    });

    markers.forEach((marker, id) => {
      if (!seen.has(id)) {
        map.removeLayer(marker);
        markers.delete(id);
      }
    });
  }

  async function pollEvents() {
    try {
      const res = await fetch(`/api/map_events.php?since=${lastTs}`, { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('http');
      const json = await res.json();
      if (json && json.data) {
        lastTs = json.data.ts || lastTs;
        if (json.data.events) {
          upsertMarkers(json.data.events);
        }
      }
    } catch (err) {
      // silencioso, o widget de mapa ja mostra erro
    }
  }

  function startPolling() {
    clearInterval(polling);
    polling = setInterval(pollEvents, 8000);
    pollEvents();
  }

  document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;
    buildMap(mapEl);
    startPolling();
  });
})();
