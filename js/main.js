// Global variables from other files
var allDestinations = [];
var currentPage = 0;
var destinationsPerPage = 6;
let favorites = JSON.parse(localStorage.getItem("favorites")) || [];

$(document).ready(function () {
  loadPreferences();

  // Using centralized weather rules - no local preferenceRules needed

  function applyPreferenceRules(selectedPreference) {
    // Use centralized weather rules
    const conflicts = WeatherRulesUtils.getConflicts(selectedPreference);
    const message = WeatherRulesUtils.getMessage(selectedPreference);

    const isChecked = $(`#tab-${selectedPreference}`).is(":checked");
    if (!isChecked) return;

    let adjustmentsMade = false;

    // Handle conflicts
    conflicts.forEach((conflict) => {
      if ($(`#tab-${conflict}`).is(":checked")) {
        $(`#tab-${conflict}`).prop("checked", false);
        adjustmentsMade = true;
      }
    });

    // Get current temperatures and unit
    const minTempInput = $("#min_temp");
    const maxTempInput = $("#max_temp");
    let currentMinTemp = parseFloat(minTempInput.val());
    let currentMaxTemp = parseFloat(maxTempInput.val());
    const selectedUnit =
      $('input[name="temp_unit"]:checked').val() === "fahrenheit"
        ? "fahrenheit"
        : "celsius";

    // Get temperature range for this preference
    const range = WeatherRulesUtils.getTemperatureRange(
      selectedPreference,
      selectedUnit
    );

    // Apply temperature limits if they exist and are different from default
    if (range && range.min !== undefined && range.max !== undefined) {
      const minLimit = parseFloat(range.min);
      const maxLimit = parseFloat(range.max);

      if (currentMinTemp < minLimit) {
        minTempInput.val(minLimit);
        adjustmentsMade = true;
      }

      if (currentMaxTemp > maxLimit) {
        maxTempInput.val(maxLimit);
        adjustmentsMade = true;
      }

      // For preferences with both min and max, adjust if outside the range
      if (currentMinTemp < minLimit || currentMaxTemp > maxLimit) {
        minTempInput.val(Math.max(minLimit, currentMinTemp));
        maxTempInput.val(Math.min(maxLimit, currentMaxTemp));
        adjustmentsMade = true;
      }
    }

    if (adjustmentsMade && message) {
      Swal.fire({
        icon: "info",
        title: "Preferences Adjusted",
        text: message,
        confirmButtonColor: "#14b8a6",
      });
    }
  }

  // Apply preference rules to all weather preferences using centralized rules
  const weatherPreferences = [
    "hot-dry",
    "warm-sunny",
    "cold-snowy",
    "mild-rainy",
    "cool-humid",
    "balanced",
  ];

  weatherPreferences.forEach((preference) => {
    $(`#tab-${preference}`).on("change", function () {
      applyPreferenceRules(preference);
    });
  });
  $("#min_temp, #max_temp").on("change", function () {
    const minTemp = parseFloat($("#min_temp").val());
    const maxTemp = parseFloat($("#max_temp").val());

    if (minTemp > maxTemp) {
      Swal.fire({
        icon: "error",
        title: "Invalid Temperature Range",
        text: "Minimum temperature cannot be greater than the maximum temperature.",
        confirmButtonColor: "#14b8a6",
      });
      $("#min_temp").val(maxTemp);
    }
  });

  function showNotification(message, type) {
    type = type || "success";
    const iconClass =
      type === "success"
        ? "fas fa-check-circle"
        : "fas fa-exclamation-triangle";
    const notification = $(`
            <div class="notification ${type}">
                <i class="${iconClass}" style="margin-right: 0.5rem;"></i>
                ${message}
            </div>
        `);

    $("body").append(notification);

    setTimeout(() => {
      notification.addClass("show");
    }, 100);

    setTimeout(() => {
      notification.removeClass("show");
      setTimeout(() => {
        notification.remove();
      }, 500);
    }, 3000);

    notification.on("click", function () {
      notification.removeClass("show");
      setTimeout(() => {
        notification.remove();
      }, 500);
    });
  }

  function getWeatherIconClass(condition) {
    if (typeof condition !== "string") {
      return "fas fa-question-circle";
    }
    condition = condition.toLowerCase();

    if (condition.includes("clear") || condition.includes("sunny")) {
      return "fas fa-sun text-warning";
    } else if (condition.includes("cloud")) {
      return "fas fa-cloud text-secondary";
    } else if (condition.includes("rain") || condition.includes("drizzle")) {
      return "fas fa-cloud-showers-heavy text-primary";
    } else if (condition.includes("snow")) {
      return "fas fa-snowflake text-info";
    } else if (condition.includes("thunderstorm")) {
      return "fas fa-bolt text-warning";
    } else if (
      condition.includes("mist") ||
      condition.includes("fog") ||
      condition.includes("haze")
    ) {
      return "fas fa-smog text-muted";
    } else {
      return "fas fa-cloud-sun text-secondary";
    }
  }

  function getWeatherIcons(weatherData) {
    var icons = [];

    if (weatherData.temp > 25) {
      icons.push(
        '<i class="fas fa-thermometer-three-quarters text-danger" title="Hot"></i>'
      );
    } else if (weatherData.temp < 10) {
      icons.push(
        '<i class="fas fa-thermometer-empty text-info" title="Cold"></i>'
      );
    }

    if (weatherData.humidity < 30) {
      icons.push(
        '<i class="fas fa-tint-slash text-warning" title="Low Humidity"></i>'
      );
    } else if (weatherData.humidity > 70) {
      icons.push(
        '<i class="fas fa-tint text-primary" title="High Humidity"></i>'
      );
    }

    if (weatherData.wind_speed > 20) {
      icons.push('<i class="fas fa-wind text-info" title="Windy"></i>');
    }

    return icons.join("");
  }

  function isFavorite(city, country) {
    return favorites.some(
      (fav) => fav.city === city && fav.country === country
    );
  }

  function addToFavorites(city, country) {
    if (!isFavorite(city, country)) {
      favorites.push({ city, country });
      localStorage.setItem("favorites", JSON.stringify(favorites));
      showNotification(`${city} added to favorites!`, "success");
      updateFavoritesUI();
    }
  }

  function removeFromFavorites(city, country) {
    favorites = favorites.filter(
      (fav) => fav.city !== city || fav.country !== country
    );
    localStorage.setItem("favorites", JSON.stringify(favorites));
    showNotification(`${city} removed from favorites.`, "info");
    updateFavoritesUI();
  }

  function updateFavoritesUI() {
    $(".btn-favorite").each(function () {
      const city = $(this).data("city");
      const country = $(this).data("country");
      if (isFavorite(city, country)) {
        $(this).find("i").removeClass("far").addClass("fas text-danger");
      } else {
        $(this).find("i").removeClass("fas text-danger").addClass("far");
      }
    });
  }

  function showFavoritesModal() {
    const favoritesContainer = $("#favorites-container");
    favoritesContainer.empty();

    if (favorites.length === 0) {
      favoritesContainer.html("<p>You have no favorite destinations yet.</p>");
    } else {
      const list = $('<ul class="list-group"></ul>');
      favorites.forEach((fav) => {
        const listItem = $(`
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="destination.php?city=${encodeURIComponent(
                          fav.city
                        )}&country=${encodeURIComponent(fav.country)}">${
          fav.city
        }, ${fav.country}</a>
                        <button class="btn btn-sm btn-danger remove-fav-from-modal" data-city="${
                          fav.city
                        }" data-country="${
          fav.country
        }"><i class="fas fa-trash"></i></button>
                    </li>
                `);
        list.append(listItem);
      });
      favoritesContainer.append(list);
    }

    const favoritesModal = new bootstrap.Modal(
      document.getElementById("favorites-modal")
    );
    favoritesModal.show();
  }

  function fetchSingleCityWeather(city, country) {
    $("#loading").show();
    $("#results-container").hide();

    $.ajax({
      type: "POST",
      url: "api/get_city_weather.php",
      data: { city: city, country: country },
      dataType: "json",
      success: function (response) {
        $("#loading").hide();
        if (response && response.city) {
          allDestinations = [response];
          currentPage = 0;
          displayResults();
        } else {
          alert("Error: " + (response.error || "Could not fetch city data."));
        }
      },
      error: function (xhr, status, error) {
        $("#loading").hide();
        alert("Error: " + error + ". Please try again later.");
        console.error(xhr.responseText);
      },
    });
  }

  function updatePagination() {
    var paginationContainer = $("#pagination-controls");
    paginationContainer.empty();

    if (allDestinations.length <= destinationsPerPage) {
      paginationContainer.hide();
      return;
    }

    var totalPages = Math.ceil(allDestinations.length / destinationsPerPage);

    var paginationHtml = `
            <div class="d-flex justify-content-center">
                <nav aria-label="Destination results pages">
                    <ul class="pagination">
                        <li class="page-item ${
                          currentPage === 0 ? "disabled" : ""
                        }">
                            <a class="page-link" href="#" id="prev-page" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
        `;

    for (var i = 0; i < totalPages; i++) {
      paginationHtml += `<li class="page-item ${
        i === currentPage ? "active" : ""
      }"><a class="page-link page-number" href="#" data-page="${i}">${
        i + 1
      }</a></li>`;
    }

    paginationHtml += `
                        <li class="page-item ${
                          currentPage === totalPages - 1 ? "disabled" : ""
                        }">
                            <a class="page-link" href="#" id="next-page" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        `;

    paginationContainer.html(paginationHtml);
    paginationContainer.show();

    $(".page-number").on("click", function (e) {
      e.preventDefault();
      currentPage = parseInt($(this).data("page"));
      displayResults();
    });

    $("#prev-page").on("click", function (e) {
      e.preventDefault();
      if (currentPage > 0) {
        currentPage--;
        displayResults();
      }
    });

    $("#next-page").on("click", function (e) {
      e.preventDefault();
      var totalPages = Math.ceil(allDestinations.length / destinationsPerPage);
      if (currentPage < totalPages - 1) {
        currentPage++;
        displayResults();
      }
    });
  }

  function displayResults() {
    var resultsRow = $("#results-row");
    resultsRow.empty();

    if (allDestinations.length === 0) {
      resultsRow.html(
        '<div class="col-12 text-center"><h3>No destinations match your criteria. Please try different weather preferences.</h3></div>'
      );
      $("#results-container").show();
      $("#pagination-controls").hide();
      return;
    }

    var startIndex = currentPage * destinationsPerPage;
    var endIndex = Math.min(
      startIndex + destinationsPerPage,
      allDestinations.length
    );
    var pageDestinations = allDestinations.slice(startIndex, endIndex);

    for (var i = 0; i < pageDestinations.length; i++) {
      var destination = pageDestinations[i];

      if (!destination || !destination.current) {
        console.error("Invalid destination data:", destination);
        continue;
      }

      var displayUnit = destination.current.temp_unit || "°C";

      var cardHtml = `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow destination-card">
                        <div class="result-title d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 city-title">${destination.city}, ${
        destination.country
      }</h5>
                            <button class="btn btn-sm btn-favorite" data-city="${
                              destination.city
                            }" data-country="${destination.country}">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center mb-2">
                                <div class="col-6 text-center">
                                    <div class="temp-display-compact">
                                        ${
                                          destination.current.temp
                                        }${displayUnit}
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="weather-icon-compact">
                                        <i class="${getWeatherIconClass(
                                          destination.current.condition
                                        )}"></i>
                                        <div class="current-condition">${
                                          destination.current.condition
                                        }</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="weather-details d-flex justify-content-around mb-2">
                                <div class="weather-data-item"><i class="fas fa-water text-primary"></i> ${
                                  destination.current.humidity
                                }%</div>
                                <div class="weather-data-item"><i class="fas fa-wind text-info"></i> ${
                                  destination.current.wind_speed
                                } km/h</div>
                            </div>
                            
                            <div class="match-badge text-center mb-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-percentage me-1"></i> Match: ${
                                      destination.match_score
                                    }%
                                </span>
                                ${getExtremeWeatherBadge(destination.current)}
                            </div>
                            
                            <div class="text-center">
                                <a href="destination.php?city=${encodeURIComponent(
                                  destination.city
                                )}&country=${encodeURIComponent(
        destination.country
      )}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-info-circle me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
      resultsRow.append(cardHtml);
    }

    updatePagination();
    initOrUpdateMap(pageDestinations);
    $("#results-container").show();

    // Fix map rendering in fragments - invalidate size when map container is visible
    if ($("#map-container").is(":visible")) {
      setTimeout(function () {
        if (map) {
          map.invalidateSize();
        }
      }, 100);
    }

    $("html, body").animate(
      {
        scrollTop: $("#results-container").offset().top - 100,
      },
      800
    );
    updateFavoritesUI();
  }

  function savePreferences() {
    const preferences = {
      min_temp: $("#min_temp").val(),
      max_temp: $("#max_temp").val(),
      temp_unit: $('input[name="temp_unit"]:checked').val(),
      preferences: $('input[name="preferences[]"]:checked')
        .map(function () {
          return $(this).val();
        })
        .get(),
      continents: $('input[name="continents[]"]:checked')
        .map(function () {
          return $(this).val();
        })
        .get(),
    };
    localStorage.setItem("weatherPreferences", JSON.stringify(preferences));
  }

  function loadPreferences() {
    const preferences = JSON.parse(localStorage.getItem("weatherPreferences"));
    if (preferences) {
      $("#min_temp").val(preferences.min_temp);
      $("#max_temp").val(preferences.max_temp);
      $(`input[name="temp_unit"][value="${preferences.temp_unit}"]`).prop(
        "checked",
        true
      );

      $('input[name="preferences[]"]').prop("checked", false);
      if (preferences.preferences) {
        preferences.preferences.forEach(function (pref) {
          $(`input[name="preferences[]"][value="${pref}"]`).prop(
            "checked",
            true
          );
        });
      }

      $('input[name="continents[]"]').prop("checked", false);
      if (preferences.continents) {
        preferences.continents.forEach(function (continent) {
          $(`input[name="continents[]"][value="${continent}"]`).prop(
            "checked",
            true
          );
        });
      }
    }
  }

  function clearPreferences() {
    localStorage.removeItem("weatherPreferences");
    $('input[name="continents[]"]').prop("checked", false);
    $('input[name="preferences[]"]').prop("checked", false);
    $("#celsius").prop("checked", true);
    $("#min_temp").val("15");
    $("#max_temp").val("30");
  }

  $("#weather-form").on("submit", function (e) {
    e.preventDefault();

    savePreferences();

    $("#loading").show();
    $("#results-container").hide();

    var formData = $(this).serialize();

    $.ajax({
      type: "POST",
      url: "api/get_recommendations.php",
      data: formData,
      dataType: "json",
      success: function (response) {
        $("#loading").hide();
        if (response && Array.isArray(response.destinations)) {
          allDestinations = response.destinations;
          currentPage = 0;
          displayResults();
        } else {
          alert("Error: Invalid response from server.");
          console.error(response);
        }
      },
      error: function (xhr, status, error) {
        $("#loading").hide();
        alert("Error: " + error + ". Please try again later.");
        console.error(xhr.responseText);
      },
    });
  });

  $("#clear-preferences-btn").on("click", function () {
    clearPreferences();
    showNotification("Preferences have been cleared.", "success");
  });

  $(document).on("click", ".btn-favorite", function () {
    const city = $(this).data("city");
    const country = $(this).data("country");

    if (isFavorite(city, country)) {
      removeFromFavorites(city, country);
    } else {
      addToFavorites(city, country);
    }
  });

  $("#favorites-link").on("click", function (e) {
    e.preventDefault();
    showFavoritesModal();
  });

  $("#clear-favorites").on("click", function () {
    if (confirm("Are you sure you want to remove all favorites?")) {
      favorites = [];
      localStorage.setItem("favorites", JSON.stringify(favorites));
      showFavoritesModal();
      updateFavoritesUI();
    }
  });

  $(document).on("click", ".remove-fav-from-modal", function () {
    const city = $(this).data("city");
    const country = $(this).data("country");
    removeFromFavorites(city, country);
    showFavoritesModal();
  });
});
