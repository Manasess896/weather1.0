var map;
var markers = [];

function initOrUpdateMap(destinations) {
  if (markers.length > 0) {
    markers.forEach(function (marker) {
      marker.remove();
    });
    markers = [];
  }

  if (!map) {
    map = L.map("results-map").setView([20, 0], 2);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 18,
    }).addTo(map);
  }

  destinations.forEach(function (destination, index) {
    if (
      !destination ||
      !destination.lat ||
      !destination.lng ||
      !destination.current
    ) {
      console.error("Invalid destination for map marker:", destination);
      return;
    }
    var markerColor = "gray";
    if (destination.match_score >= 80) {
      markerColor = "green";
    } else if (destination.match_score >= 60) {
      markerColor = "blue";
    }

    var markerIcon = L.divIcon({
      className: "custom-div-icon",
      html: `<div class="marker-pin bg-${markerColor}">
                     <span>${
                       index + 1 + currentPage * destinationsPerPage
                     }</span>
                   </div>`,
      iconSize: [30, 42],
      iconAnchor: [15, 42],
    });

    var marker = L.marker([destination.lat, destination.lng], {
      icon: markerIcon,
      title: destination.city + ", " + destination.country,
    }).addTo(map);

    var popupContent = `
            <div class="map-popup">
                <h5>${destination.city}, ${destination.country}</h5>
                <div class="d-flex justify-content-between">
                    <span>${destination.current.temp}°C</span>
                    <span>${destination.current.condition}</span>
                </div>
                <div class="text-center mt-2">
                    <span class="badge bg-success">Match: ${destination.match_score}%</span>
                </div>
            </div>
        `;

    marker.bindPopup(popupContent);
    markers.push(marker);
  });

  if (markers.length > 0) {
    var group = new L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.1));
  }
}

$(document).ready(function () {
  $("#map-view-btn").on("click", function () {
    $(this)
      .addClass("active")
      .removeClass("btn-outline-primary")
      .addClass("btn-primary");
    $("#card-view-btn")
      .removeClass("active")
      .removeClass("btn-primary")
      .addClass("btn-outline-primary");

    $("#results-row").hide();
    $("#map-container").show();

    if (map) {
      setTimeout(function () {
        map.invalidateSize();
      }, 100);
    }
  });
});
