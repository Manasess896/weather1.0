<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>WeatherVoyager - Find Your Perfect Destination</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <!-- leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin="" />
  <!-- css -->
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/style-fixes.css">
  <link rel="stylesheet" href="css/extreme-weather.css">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #0d9488, #14b8a6);">
    <div class="container">
      <a class="navbar-brand" href="home">
        <i class="fas fa-cloud-sun-rain me-2"></i>
        WeatherVoyager
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active" href="home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="news">Weather News</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php">Contact</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="py-5 text-center hero-section" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white;">
    <div class="container">
      <h1 class="display-4" style="color: #14b8a6;">Find Your Perfect Weather Destination</h1>
      <p class="lead" style="color: #5eead4;">Tell us your ideal weather, and we'll find the best cities for your next trip.</p>
    </div>
  </div>


  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow" style="background: #1a1a1a; border: 1px solid #14b8a6;">
          <div class="card-body" style="padding: 2rem;">
            <h2 class="card-title text-center mb-4" style="color: #14b8a6;">What's your ideal weather?</h2>
            <form id="weather-form" method="POST" action="api/recommendations">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="min_temp" class="form-label" style="color: #5eead4;">Min Temperature <span id="min-temp-unit">(°C)</span></label>
                  <input type="number" class="form-control" id="min_temp" name="min_temp" value="15">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="max_temp" class="form-label" style="color: #5eead4;">Max Temperature <span id="max-temp-unit">(°C)</span></label>
                  <input type="number" class="form-control" id="max_temp" name="max_temp" value="30">
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" style="color: #5eead4;">Temperature Unit</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="temp_unit" id="celsius" value="celsius" checked>
                  <label class="form-check-label" for="celsius" style="color: white;">Celsius (°C)</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="temp_unit" id="fahrenheit" value="fahrenheit">
                  <label class="form-check-label" for="fahrenheit" style="color: white;">Fahrenheit (°F)</label>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" style="color: #5eead4;">Weather Preferences</label>
                <div class="d-flex flex-wrap gap-2" id="weather-preferences-tabs">
                  <?php
                  $preferences = [
                    'hot-dry' => ['label' => 'Hot and Dry', 'icon' => 'fa-thermometer-full', 'hint' => 'Temperature range: 28–40°C (82–104°F), Low humidity, No rain'],
                    'warm-sunny' => ['label' => 'Warm and Sunny', 'icon' => 'fa-sun', 'hint' => 'Temperature range: 21–28°C (70–82°F), Low humidity, No rain'],
                    'cold-snowy' => ['label' => 'Cold and Snowy', 'icon' => 'fa-snowflake', 'hint' => 'Temperature range: -10–5°C (14–41°F), High humidity, Snow'],
                    'mild-rainy' => ['label' => 'Mild and Rainy', 'icon' => 'fa-cloud-showers-heavy', 'hint' => 'Temperature range: 15–25°C (59–77°F), High humidity, Rain'],
                    'cool-humid' => ['label' => 'Cool and Humid', 'icon' => 'fa-tint', 'hint' => 'Temperature range: 10–20°C (50–68°F), High humidity, Occasional rain'],
                    'balanced' => ['label' => 'Balanced', 'icon' => 'fa-thermometer-half', 'hint' => 'Temperature range: 18–28°C (64–82°F), Moderate humidity, Occasional rain'],
                  ];
                  foreach ($preferences as $key => $details) {
                    echo '<input type="checkbox" class="btn-check" name="preferences[]" value="' . $key . '" id="tab-' . $key . '" autocomplete="off">';
                    echo '<label class="btn btn-outline-info preference-tab mb-1" for="tab-' . $key . '" title="' . $details['hint'] . '"><i class="fas ' . $details['icon'] . ' me-2"></i>' . $details['label'] . '</label>';
                  }
                  ?>
                </div>
              </div>
              <div class="mb-4">
                <label class="form-label" style="color: #5eead4;">Continents</label>
                <div class="d-flex flex-wrap gap-2">
                  <?php
                  $continents = [
                    'europe' => 'Europe',
                    'north_america' => 'North America',
                    'asia' => 'Asia',
                    'australia_oceania' => 'Australia/Oceania',
                    'africa' => 'Africa',
                    'south_central_america' => 'South/Central America'
                  ];
                  foreach ($continents as $key => $label) {
                    echo '<input type="checkbox" class="btn-check" name="continents[]" value="' . $key . '" id="continent-' . $key . '" autocomplete="off">';
                    echo '<label class="btn btn-outline-primary preference-tab mb-1" for="continent-' . $key . '">' . $label . '</label>';
                  }
                  ?>
                </div>
              </div>
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg" style="background-color: #14b8a6; border-color: #14b8a6;">
                  <i class="fas fa-search me-2"></i>Find My Destination
                </button>
                <button type="button" id="clear-preferences-btn" class="btn btn-secondary btn-lg">
                  <i class="fas fa-times-circle me-2"></i>Clear
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container mb-5" id="results-container" style="display: none;">
    <h2 class="text-center mb-4" style="color: #14b8a6;">Your Top Destination Matches</h2>
    <div class="row mb-4">
      <div class="col-12 text-center">
        <div class="btn-group" role="group" aria-label="View toggle">
          <button type="button" class="btn btn-primary active" id="card-view-btn">Card View</button>
          <button type="button" class="btn btn-outline-primary" id="map-view-btn">Map View</button>
        </div>
      </div>
    </div>
    <div class="row" id="results-row">
    </div>
    <div id="map-container" style="display:none;">
      <div id="results-map" style="height: 500px; width: 100%;"></div>
    </div>
    <div class="mt-4" id="pagination-controls">
    </div>
  </div>
  <div id="loading" class="text-center py-5" style="display: none;">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Finding your perfect weather destinations...</p>
  </div>
  <footer class="py-4 mt-5" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white;">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5><i class="fas fa-cloud-sun-rain me-2" style="color: #14b8a6;"></i> WeatherVoyager</h5>
          <p style="color: #5eead4;">Finding your perfect weather destination since 2025.</p>
          <p style="color: #5eead4; font-size: 0.9em;">Created by <a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/home" target="_blank" style="color: #14b8a6; text-decoration: none;">Code Craft Website Solutions</a></p>
        </div>
        <div class="col-md-3">
          <h5 style="color: #14b8a6;">Links</h5>
          <ul class="list-unstyled">
            <li><a href="home" style="color: #5eead4; text-decoration: none;">Home</a></li>
            <li><a href="news" style="color: #5eead4; text-decoration: none;">Weather News</a></li>
            <li><a href="about" style="color: #5eead4; text-decoration: none;">About</a></li>
            <li><a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php" style="color: #5eead4; text-decoration: none;">Contact</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h5 style="color: #14b8a6;">Data Sources</h5>
          <ul class="list-unstyled">
            <li style="color: #5eead4;">OpenWeather API</li>

            <li style="color: #5eead4;">Google News RSS</li>
          </ul>
        </div>
      </div>
      <div class="text-center mt-3">
        <p class="mb-0" style="color: #5eead4;">&copy; 2025 WeatherVoyager. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>

  <script src="js/weather-rules.js"></script>
  <script src="js/ui.js"></script>
  <script src="js/favorites.js"></script>
  <script src="js/map.js"></script>
  <script src="js/main.js"></script>

  <script>
    $(document).ready(function() {
      if ($('#min-temp-feedback').length === 0) {
        $('#min_temp').after('<small class="text-muted" id="min-temp-feedback" style="color: #94a3b8 !important;"></small>');
      }
      if ($('#max-temp-feedback').length === 0) {
        $('#max_temp').after('<small class="text-muted" id="max-temp-feedback" style="color: #94a3b8 !important;"></small>');
      }

      function updateFeedback() {
        const selectedUnit = $('input[name="temp_unit"]:checked').val();
        const minTemp = parseFloat($('#min_temp').val()) || 0;
        const maxTemp = parseFloat($('#max_temp').val()) || 0;
        const minFeedback = $('#min-temp-feedback');
        const maxFeedback = $('#max-temp-feedback');
        if (selectedUnit === 'celsius') {
          const minF = celsiusToFahrenheit(minTemp);
          const maxF = celsiusToFahrenheit(maxTemp);
          minFeedback.text(`= ${minF}°F`);
          maxFeedback.text(`= ${maxF}°F`);
        } else {
          const minC = fahrenheitToCelsius(minTemp);
          const maxC = fahrenheitToCelsius(maxTemp);
          minFeedback.text(`= ${minC}°C`);
          maxFeedback.text(`= ${maxC}°C`);
        }
      }

      function setTempInputsForPreference(preference) {
        const selectedUnit = $('input[name="temp_unit"]:checked').val();
        const range = WeatherRulesUtils.getTemperatureRange(preference, selectedUnit);
        const minInput = $('#min_temp');
        const maxInput = $('#max_temp');
        minInput.attr('min', range.min);
        minInput.attr('max', range.max);
        maxInput.attr('min', range.min);
        maxInput.attr('max', range.max);
        minInput.val(range.min);
        maxInput.val(range.max);
        minInput.attr('placeholder', `${range.min}° to ${range.max}°`);
        maxInput.attr('placeholder', `${range.min}° to ${range.max}°`);
        updateFeedback();
      }
      function resetTempInputs() {
        const selectedUnit = $('input[name="temp_unit"]:checked').val();
        const defaultRange = WeatherRulesUtils.getTemperatureRange('default', selectedUnit);
        const minInput = $('#min_temp');
        const maxInput = $('#max_temp');
        minInput.attr('min', defaultRange.min);
        minInput.attr('max', defaultRange.max);
        maxInput.attr('min', defaultRange.min);
        maxInput.attr('max', defaultRange.max);
        minInput.val(15);
        maxInput.val(30);
        minInput.attr('placeholder', '');
        maxInput.attr('placeholder', '');
        updateFeedback();
      }
      function validateTemperatureRanges() {
        const selectedPreferences = $('input[name="preferences[]"]:checked').map(function() {
          return $(this).val();
        }).get();
        const selectedUnit = $('input[name="temp_unit"]:checked').val();
        const minTempInput = $('#min_temp');
        const maxTempInput = $('#max_temp');
        const minTemp = parseFloat(minTempInput.val()) || 0;
        const maxTemp = parseFloat(maxTempInput.val()) || 0;
        const validation = WeatherRulesUtils.validateTemperatures(minTemp, maxTemp, selectedPreferences, selectedUnit);
        return validation;
      }
      function autoAdjustTemperature(preference) {
        const selectedUnit = $('input[name="temp_unit"]:checked').val();
        const adjustment = WeatherRulesUtils.getAutoAdjustment(preference, selectedUnit);
        if (!adjustment) return;
        const minTempInput = $('#min_temp');
        const maxTempInput = $('#max_temp');
        const currentMin = parseFloat(minTempInput.val()) || 0;
        const currentMax = parseFloat(maxTempInput.val()) || 0;
        const suggestedMin = adjustment.min;
        const suggestedMax = adjustment.max;
        let needsAdjustment = false;
        let adjustmentMessage = '';
        const range = WeatherRulesUtils.getTemperatureRange(preference, selectedUnit);
        if (preference === 'hot-dry' && (currentMin < range.min || currentMax < range.min)) {
          needsAdjustment = true;
          adjustmentMessage = `For "Hot and Dry" weather, we recommend temperatures between ${suggestedMin}°${selectedUnit.charAt(0).toUpperCase()} and ${suggestedMax}°${selectedUnit.charAt(0).toUpperCase()}.`;
        } else if (preference === 'warm-sunny' && (currentMin < range.min || currentMax < range.min)) {
          needsAdjustment = true;
          adjustmentMessage = `For "Warm and Sunny" weather, we recommend temperatures between ${suggestedMin}°${selectedUnit.charAt(0).toUpperCase()} and ${suggestedMax}°${selectedUnit.charAt(0).toUpperCase()}.`;
        } else if (preference === 'cold-snowy' && (currentMin > range.max || currentMax > range.max)) {
          needsAdjustment = true;
          adjustmentMessage = `For "Cold and Snowy" weather, we recommend temperatures between ${suggestedMin}°${selectedUnit.charAt(0).toUpperCase()} and ${suggestedMax}°${selectedUnit.charAt(0).toUpperCase()}.`;
        } else if (preference === 'mild-rainy' && (currentMin < range.min || currentMax > range.max)) {
          needsAdjustment = true;
          adjustmentMessage = `For "Mild and Rainy" weather, we recommend temperatures between ${suggestedMin}°${selectedUnit.charAt(0).toUpperCase()} and ${suggestedMax}°${selectedUnit.charAt(0).toUpperCase()}.`;
        } else if (preference === 'cool-humid' && (currentMin < range.min || currentMax > range.max)) {
          needsAdjustment = true;
          adjustmentMessage = `For "Cool and Humid" weather, we recommend temperatures between ${suggestedMin}°${selectedUnit.charAt(0).toUpperCase()} and ${suggestedMax}°${selectedUnit.charAt(0).toUpperCase()}.`;
        } else if (preference === 'balanced' && (currentMin < range.min || currentMax > range.max)) {
          needsAdjustment = true;
          adjustmentMessage = `For "Balanced" weather, we recommend temperatures between ${suggestedMin}°${selectedUnit.charAt(0).toUpperCase()} and ${suggestedMax}°${selectedUnit.charAt(0).toUpperCase()}.`;
        }

        if (needsAdjustment) {
          Swal.fire({
            title: 'Temperature Adjustment Suggested',
            text: adjustmentMessage + ' Would you like to adjust automatically?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Adjust',
            cancelButtonText: 'Keep Current',
            confirmButtonColor: '#14b8a6',
            cancelButtonColor: '#6c757d'
          }).then((result) => {
            if (result.isConfirmed) {
              minTempInput.val(suggestedMin);
              maxTempInput.val(suggestedMax);
              updateFeedback();

              Swal.fire({
                title: 'Temperatures Adjusted!',
                text: `Temperature range set to ${suggestedMin}°${selectedUnit.charAt(0).toUpperCase()} - ${suggestedMax}°${selectedUnit.charAt(0).toUpperCase()}`,
                icon: 'success',
                confirmButtonColor: '#14b8a6',
                timer: 2000
              });
            }
          });
        }
      }
      function checkConflictingPreferences(changedPreference) {
        const conflicts = WeatherRulesUtils.getConflicts(changedPreference);
        const conflictingSelected = [];
        conflicts.forEach(conflictPref => {
          if ($(`#tab-${conflictPref}`).is(':checked')) {
            conflictingSelected.push(conflictPref);
          }
        });
        if (conflictingSelected.length > 0) {
          conflictingSelected.forEach(pref => {
            $(`#tab-${pref}`).prop('checked', false);
          });
          const conflictNames = conflictingSelected.map(pref => {
            return $(`label[for="tab-${pref}"]`).text().trim();
          });
          Swal.fire({
            title: 'Preference Conflict Resolved',
            text: `"${$(`label[for="tab-${changedPreference}"]`).text().trim()}" conflicts with "${conflictNames.join(', ')}". The conflicting preferences have been automatically removed.`,
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#14b8a6'
          });
        }
        setTimeout(() => {
          autoAdjustTemperature(changedPreference);
        }, 100);
      }
      function showTemperatureValidation() {
        const validation = validateTemperatureRanges();

        if (validation.errors.length > 0) {
          Swal.fire({
            title: 'Temperature Configuration Error',
            html: validation.errors.join('<br><br>'),
            icon: 'error',
            confirmButtonText: 'Fix Settings',
            confirmButtonColor: '#dc3545'
          });
          return false;
        }

        if (validation.warnings.length > 0) {
          Swal.fire({
            title: 'Temperature Recommendations',
            html: validation.warnings.join('<br><br>'),
            icon: 'warning',
            confirmButtonText: 'Got it',
            confirmButtonColor: '#14b8a6'
          });
        }

        return true;
      }

      function celsiusToFahrenheit(c) {
      
        if (typeof c !== 'number' || isNaN(c)) return '';
        return Math.round((c * 9 / 5 + 32) * 100) / 100;
      }

      function fahrenheitToCelsius(f) {
   
        if (typeof f !== 'number' || isNaN(f)) return '';
        return Math.round(((f - 32) * 5 / 9) * 100) / 100;
      }

      $('input[name="preferences[]"]').on('change', function() {
        const checked = $(this).is(':checked');
        const pref = $(this).val();
        if (checked) {

          $('input[name="preferences[]"]').not(this).prop('checked', false).prop('disabled', true);
          setTempInputsForPreference(pref);
        } else {

          $('input[name="preferences[]"]').prop('disabled', false);
          resetTempInputs();
        }
      });

      $('input[name="temp_unit"]').on('change', function() {
        const selectedUnit = $(this).val();
        const minTempInput = $('#min_temp');
        const maxTempInput = $('#max_temp');
        const minTempUnit = $('#min-temp-unit');
        const maxTempUnit = $('#max-temp-unit');

        let currentMinTemp = parseFloat(minTempInput.val());
        let currentMaxTemp = parseFloat(maxTempInput.val());
        let currentUnit = minTempInput.attr('data-unit') || 'celsius';

        if (selectedUnit === 'fahrenheit' && currentUnit !== 'fahrenheit') {
  
          minTempInput.val(celsiusToFahrenheit(currentMinTemp)).attr('data-unit', 'fahrenheit');
          maxTempInput.val(celsiusToFahrenheit(currentMaxTemp)).attr('data-unit', 'fahrenheit');
          minTempUnit.text('(°F)');
          maxTempUnit.text('(°F)');
        } else if (selectedUnit === 'celsius' && currentUnit !== 'celsius') {
          minTempInput.val(fahrenheitToCelsius(currentMinTemp)).attr('data-unit', 'celsius');
          maxTempInput.val(fahrenheitToCelsius(currentMaxTemp)).attr('data-unit', 'celsius');
          minTempUnit.text('(°C)');
          maxTempUnit.text('(°C)');
        }
        updateFeedback();
        setTimeout(() => {
          showTemperatureValidation();
        }, 300);
      });

      $('#min_temp, #max_temp').on('input', function() {
        updateFeedback();
      });

      $('#min_temp, #max_temp').on('blur', function() {
        setTimeout(() => {
          showTemperatureValidation();
        }, 300);
      });

      $('#weather-form').on('submit', function(e) {
        const validation = validateTemperatureRanges();
        const minTemp = parseFloat($('#min_temp').val()) || 0;
        const maxTemp = parseFloat($('#max_temp').val()) || 0;

        if (validation.errors.length > 0) {
          e.preventDefault();
          Swal.fire({
            title: 'Cannot Submit - Configuration Errors',
            html: validation.errors.join('<br><br>'),
            icon: 'error',
            confirmButtonText: 'Fix Settings',
            confirmButtonColor: '#dc3545'
          });
          return;
        }

        if (minTemp >= maxTemp) {
          e.preventDefault();
          Swal.fire({
            title: 'Temperature Range Error',
            text: 'Minimum temperature must be lower than maximum temperature.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
          });
          return;
        }
        if (validation.warnings.length > 0) {
          e.preventDefault();
          Swal.fire({
            title: 'Temperature Recommendations',
            html: validation.warnings.join('<br><br>') + '<br><br>Do you want to continue anyway?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Continue',
            cancelButtonText: 'Adjust Settings',
            confirmButtonColor: '#14b8a6',
            cancelButtonColor: '#6c757d'
          }).then((result) => {
            if (result.isConfirmed) {
              $(this).off('submit').submit();
            }
          });
        }
      });
      $('#clear-preferences-btn').on('click', function() {
        $('input[name="preferences[]"]').prop('checked', false).prop('disabled', false);
        $('input[name="continents[]"]').prop('checked', false);
        resetTempInputs();
        $('#celsius').prop('checked', true);
        $('#fahrenheit').prop('checked', false);
        $('#min-temp-unit').text('(°C)');
        $('#max-temp-unit').text('(°C)');
        $('#min_temp, #max_temp').attr('data-unit', 'celsius');
        Swal.fire({
          title: 'Preferences Cleared',
          text: 'All preferences and settings have been reset to defaults.',
          icon: 'success',
          confirmButtonColor: '#14b8a6',
          timer: 1500
        });
      });
      $('#min_temp, #max_temp').attr('data-unit', 'celsius');
      updateFeedback();
    });
  </script>
</body>

</html>