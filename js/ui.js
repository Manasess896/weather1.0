// --- Weather Thresholds (researched, global standards) ---
const EXTREME_HOT_TEMP_C = 35; // >35°C
const EXTREME_COLD_TEMP_C = 0; // <0°C
const HEAVY_RAIN_MM_PER_HOUR = 1.5; // >=1.5mm/hr
const HEAVY_RAIN_MM_PER_3H = 4.5; // >=4.5mm/3hr
const STRONG_WIND_KMH = 40; // >=40 km/h
const HIGH_HUMIDITY = 85; // >=85%
const NORMAL_TEMP_MIN_C = 15;
const NORMAL_TEMP_MAX_C = 30;
const NORMAL_HUMIDITY_MIN = 30;
const NORMAL_HUMIDITY_MAX = 70;
const NORMAL_WIND_KMH = 28;

function showNotification(message, type) {
  type = type || "success";
  const iconClass =
    type === "success" ? "fas fa-check-circle" : "fas fa-exclamation-triangle";
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
    setTimeout(() => notification.remove(), 500);
  }, 3000);

  notification.on("click", function () {
    $(this).removeClass("show");
    setTimeout(() => $(this).remove(), 500);
  });
}

function getWeatherIconClass(condition) {
  if (typeof condition !== "string") {
    return "fas fa-question-circle text-muted";
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
    return "fas fa-bolt text-danger";
  } else if (
    condition.includes("mist") ||
    condition.includes("fog") ||
    condition.includes("haze")
  ) {
    return "fas fa-smog text-muted";
  } else {
    return "fas fa-question-circle text-muted";
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
      '<i class="fas fa-thermometer-quarter text-info" title="Cold"></i>'
    );
  }

  if (weatherData.humidity < 30) {
    icons.push(
      '<i class="fas fa-tint-slash text-muted" title="Low Humidity"></i>'
    );
  } else if (weatherData.humidity > 80) {
    icons.push(
      '<i class="fas fa-tint text-primary" title="High Humidity"></i>'
    );
  }

  if (weatherData.wind_speed > 20) {
    icons.push('<i class="fas fa-wind text-info" title="Windy"></i>');
  }

  return icons.join("");
}

function getExtremeWeatherBadge(weatherData) {
  let badges = [];

  if (weatherData.temp >= 35) {
    badges.push(
      '<span class="badge bg-danger ms-1" title="Extreme Heat"><i class="fas fa-fire"></i></span>'
    );
  }
  if (weatherData.temp <= 5) {
    badges.push(
      '<span class="badge bg-info ms-1" title="Extreme Cold"><i class="fas fa-snowflake"></i></span>'
    );
  }

  if (weatherData.wind_speed >= 25) {
    badges.push(
      '<span class="badge bg-warning ms-1" title="Strong Wind"><i class="fas fa-wind"></i></span>'
    );
  }

  if (weatherData.humidity >= 85) {
    badges.push(
      '<span class="badge bg-primary ms-1" title="Very High Humidity"><i class="fas fa-tint"></i></span>'
    );
  }
  // Check for thunderstorms
  if (
    weatherData.condition.toLowerCase().includes("thunder") ||
    weatherData.condition.toLowerCase().includes("storm")
  ) {
    badges.push(
      '<span class="badge bg-warning ms-1" title="Thunderstorms"><i class="fas fa-bolt"></i></span>'
    );
  }

  if (weatherData.condition.toLowerCase().includes("snow")) {
    badges.push(
      '<span class="badge bg-light text-dark ms-1" title="Snowy"><i class="fas fa-snowflake"></i></span>'
    );
  }

  return badges.join("");
}

// Map invalidation function for fixing map rendering issues
function invalidateMapSize() {
  if (typeof map !== "undefined" && map) {
    setTimeout(function () {
      map.invalidateSize();
    }, 100);
  }
}

$(document).ready(function () {
  $("#card-view-btn").on("click", function () {
    $(this)
      .addClass("active")
      .removeClass("btn-outline-primary")
      .addClass("btn-primary");
    $("#map-view-btn")
      .removeClass("active")
      .removeClass("btn-primary")
      .addClass("btn-outline-primary");

    $("#results-row").show();
    $("#map-container").hide();
  });

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
    invalidateMapSize();
  });

  const minTempInput = $("#min_temp");
  const maxTempInput = $("#max_temp");
  const tempUnitInputs = $('input[name="temp_unit"]');
  const preferenceCheckboxes = $('input[name="preferences[]"]');
  const clearBtn = $("#clear-preferences-btn");

  // Weather preference constants - using centralized rules
  const PREF = {
    HOT_DRY: "hot-dry",
    WARM_SUNNY: "warm-sunny",
    COLD_SNOWY: "cold-snowy",
    MILD_RAINY: "mild-rainy",
    COOL_HUMID: "cool-humid",
    BALANCED: "balanced",
  };

  function cToF(c) {
    return celsiusToFahrenheit(c);
  }

  function fToC(f) {
    return fahrenheitToCelsius(f);
  }

  function getUnit() {
    return tempUnitInputs.filter(":checked").val();
  }

  function setTempLimits(min, max) {
    min = parseFloat(min);
    max = parseFloat(max);

    minTempInput.attr("min", min);
    minTempInput.attr("max", max);
    maxTempInput.attr("min", min);
    maxTempInput.attr("max", max);

    if (parseFloat(minTempInput.val()) < min) minTempInput.val(min);
    if (parseFloat(maxTempInput.val()) > max) maxTempInput.val(max);
    if (parseFloat(maxTempInput.val()) < min) maxTempInput.val(min);
    if (parseFloat(minTempInput.val()) > max) minTempInput.val(max);
  }

  function resetTempLimits() {
    const unit = getUnit();
    const defaultRange = WeatherRulesUtils.getTemperatureRange("default", unit);
    setTempLimits(defaultRange.min, defaultRange.max);
  }

  function updatePreferenceStates() {
    const checked = preferenceCheckboxes
      .filter(":checked")
      .map(function () {
        return this.value;
      })
      .get();

    preferenceCheckboxes.prop("disabled", false);
    const unit = getUnit();

    let activePreference = null;
    const preferenceOrder = [
      PREF.HOT_DRY,
      PREF.WARM_SUNNY,
      PREF.COLD_SNOWY,
      PREF.MILD_RAINY,
      PREF.COOL_HUMID,
      PREF.BALANCED,
    ];
    for (const preference of preferenceOrder) {
      if (checked.includes(preference)) {
        activePreference = preference;
        break;
      }
    }

    if (activePreference) {
      const conflicts = WeatherRulesUtils.getConflicts(activePreference);
      conflicts.forEach((conflict) => {
        preferenceCheckboxes
          .filter(`[value='${conflict}']`)
          .prop("disabled", true);
      });

      const range = WeatherRulesUtils.getTemperatureRange(
        activePreference,
        unit
      );
      setTempLimits(range.min, range.max);
    } else {
      const defaultRange = WeatherRulesUtils.getTemperatureRange(
        "default",
        unit
      );
      setTempLimits(defaultRange.min, defaultRange.max);
    }
  }
  preferenceCheckboxes.on("change", function () {
    const val = this.value;
    if (this.checked) {
      const conflicts = WeatherRulesUtils.getConflicts(val);
      let deselectedConflicts = [];

      conflicts.forEach((conflict) => {
        const conflictCheckbox = preferenceCheckboxes.filter(
          `[value='${conflict}']`
        );
        if (conflictCheckbox.is(":checked")) {
          conflictCheckbox.prop("checked", false);
          deselectedConflicts.push(conflict.replace("_", " "));
        }
      });

      if (deselectedConflicts.length > 0) {
        Swal.fire({
          icon: "info",
          title: "Conflicting Preferences Removed",
          text: `"${val.replace(
            "_",
            " "
          )}" conflicts with: ${deselectedConflicts.join(
            ", "
          )}. These have been automatically deselected.`,
          confirmButtonColor: "#14b8a6",
          timer: 3000,
        });
      }
    }
    updatePreferenceStates();
  });
  tempUnitInputs.on("change", function () {
    const newUnit = getUnit();
    const currentMinVal = parseFloat(minTempInput.val()) || 0;
    const currentMaxVal = parseFloat(maxTempInput.val()) || 0;
    const currentUnit = tempUnitInputs.not(this).val();

    if (currentMinVal !== 0 && currentMaxVal !== 0) {
      if (newUnit === "fahrenheit" && currentUnit === "celsius") {
        minTempInput.val(cToF(currentMinVal));
        maxTempInput.val(cToF(currentMaxVal));
      } else if (newUnit === "celsius" && currentUnit === "fahrenheit") {
        minTempInput.val(fToC(currentMinVal));
        maxTempInput.val(fToC(currentMaxVal));
      }
    }

    updatePreferenceStates();
  });
  minTempInput.on("change", function () {
    const min = parseFloat($(this).attr("min"));
    const max = parseFloat($(this).attr("max"));
    let val = parseFloat($(this).val());
    if (val < min) {
      $(this).val(min);
      Swal.fire("Temperature Too Low", `Minimum allowed is ${min}°.`, "info");
    } else if (val > max) {
      $(this).val(max);
      Swal.fire("Temperature Too High", `Maximum allowed is ${max}°.`, "info");
    }
  });
  maxTempInput.on("change", function () {
    const min = parseFloat($(this).attr("min"));
    const max = parseFloat($(this).attr("max"));
    let val = parseFloat($(this).val());
    if (val < min) {
      $(this).val(min);
      Swal.fire("Temperature Too Low", `Minimum allowed is ${min}°.`, "info");
    } else if (val > max) {
      $(this).val(max);
      Swal.fire("Temperature Too High", `Maximum allowed is ${max}°.`, "info");
    }
  });

  clearBtn.on("click", function () {
    preferenceCheckboxes.prop("checked", false).prop("disabled", false);
    resetTempLimits();
  });

  updatePreferenceStates();
});
