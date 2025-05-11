$(document).ready(function() {
    // Global variables for pagination and map
    var allDestinations = [];
    var currentPage = 0;
    var destinationsPerPage = 6;
    var map = null;
    var markers = [];
    var favorites = loadFavorites();

    // Favorites Management Functions
    function loadFavorites() {
        const saved = localStorage.getItem('weathervoyager_favorites');
        return saved ? JSON.parse(saved) : [];
    }

    function saveFavorites() {
        localStorage.setItem('weathervoyager_favorites', JSON.stringify(favorites));
    }

    function addToFavorites(city, country) {
        if (!isFavorite(city, country)) {
            favorites.push({ city, country });
            saveFavorites();
        }
    }

    function removeFromFavorites(city, country) {
        favorites = favorites.filter(f => !(f.city === city && f.country === country));
        saveFavorites();
    }

    function isFavorite(city, country) {
        return favorites.some(f => f.city === city && f.country === country);
    }

    // Update favorite buttons after rendering
    function updateFavoritesUI() {
        $('.btn-favorite').each(function() {
            const city = $(this).data('city');
            const country = $(this).data('country');
            
            if (isFavorite(city, country)) {
                $(this).html('<i class="fas fa-heart text-danger"></i>');
            } else {
                $(this).html('<i class="far fa-heart"></i>');
            }
        });
    }

    // Handle favorite button clicks
    $(document).on('click', '.btn-favorite', function() {
        const city = $(this).data('city');
        const country = $(this).data('country');
        
        if (isFavorite(city, country)) {
            removeFromFavorites(city, country);
            $(this).html('<i class="far fa-heart"></i>');
        } else {
            addToFavorites(city, country);
            $(this).html('<i class="fas fa-heart text-danger"></i>');
            
            // Show a small notification
            showNotification(`${city}, ${country} added to favorites!`);
        }
    });

    // Show floating notification
    function showNotification(message) {
        const notification = $(`<div class="notification">${message}</div>`);
        $('body').append(notification);
        
        setTimeout(() => {
            notification.addClass('show');
            
            setTimeout(() => {
                notification.removeClass('show');
                setTimeout(() => notification.remove(), 500);
            }, 2000);
        }, 100);
    }

    // Handle form submission
    $('#weather-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading indicator
        $('#loading').show();
        $('#results-container').hide();
        
        // Get form data
        var formData = $(this).serialize();
        
        // Make AJAX request to backend
        $.ajax({
            type: 'POST',
            url: 'api/get_recommendations.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                // Hide loading indicator
                $('#loading').hide();
                
                // Store all destinations globally
                allDestinations = response;
                
                // Reset to first page
                currentPage = 0;
                
                // Display results
                displayResults();
            },
            error: function(xhr, status, error) {
                // Hide loading indicator
                $('#loading').hide();
                
                // Show error message
                alert('Error: ' + error + '. Please try again later.');
                console.error(xhr.responseText);
            }
        });
    });
    
    // Function to display results with pagination
    function displayResults() {
        // Clear previous results
        var resultsRow = $('#results-row');
        resultsRow.empty();
        
        // Check if we have results
        if (allDestinations.length === 0) {
            resultsRow.html('<div class="col-12 text-center"><h3>No destinations match your criteria. Please try different weather preferences.</h3></div>');
            $('#results-container').show();
            $('#pagination-controls').hide();
            return;
        }
        
        // Get the temperature unit from the first destination (they all use the same unit)
        var tempUnit = allDestinations[0].temp_unit || '°C';
        
        // Calculate start and end indices for current page
        var startIndex = currentPage * destinationsPerPage;
        var endIndex = Math.min(startIndex + destinationsPerPage, allDestinations.length);
        
        // Get destinations for current page
        var pageDestinations = allDestinations.slice(startIndex, endIndex);
        
        // Loop through destinations for this page
        for (var i = 0; i < pageDestinations.length; i++) {
            var destination = pageDestinations[i];
            
            // Create weather icons and labels based on conditions
            var weatherIcons = getWeatherIcons(destination.current);
            
            // Get temperature unit from the destination
            var displayUnit = destination.current.temp_unit || tempUnit;
            
            // Create forecast cards
            var forecastHtml = '';
            for (var j = 0; j < destination.forecast.length; j++) {
                var day = destination.forecast[j];
                var dayName = new Date(day.date).toLocaleDateString('en-US', { weekday: 'short' });
                var dayUnit = day.temp_unit || displayUnit;
                
                forecastHtml += `
                    <div class="col">
                        <div class="card forecast-card h-100">
                            <div class="card-body p-2 text-center">
                                <h6>${dayName}</h6>
                                <div class="weather-icon">
                                    <i class="${getWeatherIconClass(day.condition)}"></i>
                                </div>
                                <p class="mb-0">${day.temp_max}${dayUnit} / ${day.temp_min}${dayUnit}</p>
                                <small>${day.condition}</small>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Create activities section
            var activitiesHtml = '<div class="activities-container mt-3"><h6><i class="fas fa-hiking me-2"></i>Suggested Activities:</h6><ul class="mb-0">';
            destination.activities.forEach(function(activity) {
                activitiesHtml += `<li>${activity}</li>`;
            });
            activitiesHtml += '</ul></div>';
            
            // Create travel advice section if available
            var travelAdviceHtml = '';
            if (destination.travel_advice) {
                travelAdviceHtml = `
                    <div class="travel-advice-container mt-3">
                        <h6><i class="fas fa-suitcase me-2"></i>Travel Advice:</h6>
                        <p class="mb-0 small">${destination.travel_advice}</p>
                    </div>
                `;
            }
            
            // Build destination card
            var cardHtml = `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow destination-card">
                        <div class="result-title d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${destination.city}, ${destination.country}</h5>
                            <button class="btn btn-sm btn-favorite" data-city="${destination.city}" data-country="${destination.country}">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="temp-display">
                                    ${destination.current.temp}${displayUnit}
                                </div>
                                <div class="weather-icon text-center">
                                    <i class="${getWeatherIconClass(destination.current.condition)} fa-2x"></i>
                                    <div>${destination.current.condition}</div>
                                </div>
                            </div>
                            
                            <div class="weather-labels-container d-flex flex-wrap">
                                <div class="weather-label"><i class="fas fa-water text-primary"></i> ${destination.current.humidity}%</div>
                                <div class="weather-label"><i class="fas fa-wind text-info"></i> ${destination.current.wind_speed} km/h</div>
                                <div class="weather-label"><i class="fas fa-sun text-warning"></i> UV: ${destination.current.uv_index}</div>
                            </div>
                            
                            <h6 class="mt-4">3-Day Forecast</h6>
                            <div class="row row-cols-3 g-2">
                                ${forecastHtml}
                            </div>
                            
                            ${activitiesHtml}
                            
                            ${travelAdviceHtml}
                            
                            <div class="mt-3">
                                <h6>Weather Match Score:</h6>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: ${destination.match_score}%" 
                                        aria-valuenow="${destination.match_score}" aria-valuemin="0" aria-valuemax="100">
                                        ${destination.match_score}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            resultsRow.append(cardHtml);
        }
        
        // Update pagination display
        updatePagination();
        
        // Initialize or update the map
        initOrUpdateMap();
        
        // Show results container
        $('#results-container').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $("#results-container").offset().top - 100
        }, 800);

        // After rendering all destination cards
        updateFavoritesUI();
    }
    
    // Function to initialize or update the map
    function initOrUpdateMap() {
        // Clear existing markers
        if (markers.length > 0) {
            markers.forEach(function(marker) {
                marker.remove();
            });
            markers = [];
        }
        
        // Initialize map if it doesn't exist
        if (!map) {
            map = L.map('results-map').setView([20, 0], 2);
            
            // Add tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18
            }).addTo(map);
        }
        
        // Add markers for all destinations
        allDestinations.forEach(function(destination, index) {
            var markerColor = index < 3 ? 'green' : (index < 6 ? 'blue' : 'gray');
            var markerIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="marker-pin bg-${markerColor}">
                         <span>${index + 1}</span>
                       </div>`,
                iconSize: [30, 42],
                iconAnchor: [15, 42]
            });
            
            // Create the marker
            var marker = L.marker([destination.lat, destination.lng], {
                icon: markerIcon,
                title: destination.city + ', ' + destination.country
            }).addTo(map);
            
            // Create popup content
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
            
            // Add popup to marker
            marker.bindPopup(popupContent);
            markers.push(marker);
        });
        
        // Fit map to show all markers
        if (markers.length > 0) {
            var group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    // Handle view toggle buttons
    $('#card-view-btn').on('click', function() {
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#map-view-btn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');
        
        $('#results-row').show();
        $('#map-container').hide();
    });
    
    $('#map-view-btn').on('click', function() {
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#card-view-btn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');
        
        $('#results-row').hide();
        $('#map-container').show();
        
        // Trigger a resize event to make sure the map renders correctly
        if (map) {
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }
    });
    
    // Function to update pagination controls
    function updatePagination() {
        var paginationContainer = $('#pagination-controls');
        paginationContainer.empty();
        
        if (allDestinations.length <= destinationsPerPage) {
            paginationContainer.hide();
            return;
        }
        
        // Calculate total pages
        var totalPages = Math.ceil(allDestinations.length / destinationsPerPage);
        
        // Create pagination HTML
        var paginationHtml = `
            <div class="d-flex justify-content-center">
                <nav aria-label="Destination results pages">
                    <ul class="pagination">
                        <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
                            <a class="page-link" href="#" id="prev-page" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
        `;
        
        // Add page numbers
        for (var i = 0; i < totalPages; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link page-number" href="#" data-page="${i}">${i + 1}</a>
                </li>
            `;
        }
        
        paginationHtml += `
                        <li class="page-item ${currentPage === totalPages - 1 ? 'disabled' : ''}">
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
        
        // Add event handlers for pagination controls
        $('.page-number').on('click', function(e) {
            e.preventDefault();
            currentPage = parseInt($(this).data('page'));
            displayResults();
        });
        
        $('#prev-page').on('click', function(e) {
            e.preventDefault();
            if (currentPage > 0) {
                currentPage--;
                displayResults();
            }
        });
        
        $('#next-page').on('click', function(e) {
            e.preventDefault();
            if (currentPage < totalPages - 1) {
                currentPage++;
                displayResults();
            }
        });
    }
    
    // Helper function to get weather icon class based on condition
    function getWeatherIconClass(condition) {
        condition = condition.toLowerCase();
        
        if (condition.includes('clear') || condition.includes('sunny')) {
            return 'fas fa-sun text-warning';
        } else if (condition.includes('cloud') && condition.includes('partly')) {
            return 'fas fa-cloud-sun text-info';
        } else if (condition.includes('cloud')) {
            return 'fas fa-cloud text-secondary';
        } else if (condition.includes('rain') || condition.includes('drizzle')) {
            return 'fas fa-cloud-rain text-info';
        } else if (condition.includes('thunder') || condition.includes('storm')) {
            return 'fas fa-bolt text-warning';
        } else if (condition.includes('snow')) {
            return 'fas fa-snowflake text-info';
        } else if (condition.includes('mist') || condition.includes('fog')) {
            return 'fas fa-smog text-secondary';
        } else {
            return 'fas fa-sun text-warning'; // default
        }
    }
    
    // Helper function to get multiple weather icons/details
    function getWeatherIcons(weatherData) {
        var icons = [];
        
        if (weatherData.temp > 25) {
            icons.push('<span class="badge bg-danger badge-weather"><i class="fas fa-temperature-high me-1"></i> Hot</span>');
        } else if (weatherData.temp < 10) {
            icons.push('<span class="badge bg-info badge-weather"><i class="fas fa-temperature-low me-1"></i> Cold</span>');
        } else {
            icons.push('<span class="badge bg-success badge-weather"><i class="fas fa-temperature-low me-1"></i> Pleasant</span>');
        }
        
        if (weatherData.humidity < 30) {
            icons.push('<span class="badge bg-warning badge-weather"><i class="fas fa-water me-1"></i> Dry</span>');
        } else if (weatherData.humidity > 70) {
            icons.push('<span class="badge bg-info badge-weather"><i class="fas fa-water me-1"></i> Humid</span>');
        }
        
        if (weatherData.wind_speed > 20) {
            icons.push('<span class="badge bg-secondary badge-weather"><i class="fas fa-wind me-1"></i> Windy</span>');
        }
        
        return icons.join('');
    }

    // Handle favorites link click
    $('#favorites-link').on('click', function(e) {
        e.preventDefault();
        showFavoritesModal();
    });
    
    // Handle clear favorites button click
    $('#clear-favorites').on('click', function() {
        if (confirm('Are you sure you want to remove all favorites?')) {
            favorites = [];
            saveFavorites();
            showFavoritesModal();
            showNotification('All favorites have been cleared');
        }
    });
    
    // Function to show favorites modal
    function showFavoritesModal() {
        const favoritesContainer = $('#favorites-container');
        favoritesContainer.empty();
        
        // Check if we have any favorites
        if (favorites.length === 0) {
            favoritesContainer.html(`
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="far fa-heart text-muted fa-4x"></i>
                    </div>
                    <h4>You haven't saved any favorite destinations yet</h4>
                    <p class="text-muted">When you find a destination you like, click the heart icon to save it here.</p>
                </div>
            `);
        } else {
            // Create a card for each favorite
            let favoritesHtml = '<div class="row">';
            
            favorites.forEach(function(favorite) {
                favoritesHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title">${favorite.city}, ${favorite.country}</h5>
                                    <button class="btn btn-sm btn-outline-danger remove-favorite" data-city="${favorite.city}" data-country="${favorite.country}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-primary fetch-weather" data-city="${favorite.city}" data-country="${favorite.country}">
                                        <i class="fas fa-cloud-sun me-1"></i> Check Current Weather
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            favoritesHtml += '</div>';
            favoritesContainer.html(favoritesHtml);
            
            // Add event handlers for remove buttons
            $('.remove-favorite').on('click', function() {
                const city = $(this).data('city');
                const country = $(this).data('country');
                removeFromFavorites(city, country);
                showFavoritesModal(); // Refresh modal
                showNotification(`${city}, ${country} removed from favorites`);
            });
            
            // Add event handlers for fetch weather buttons
            $('.fetch-weather').on('click', function() {
                const city = $(this).data('city');
                const country = $(this).data('country');
                
                // Close modal
                $('#favorites-modal').modal('hide');
                
                // Show loading indicator
                $('#loading').show();
                
                // Fetch weather for this specific city
                fetchSingleCityWeather(city, country);
            });
        }
        
        // Show the modal
        const favoritesModal = new bootstrap.Modal(document.getElementById('favorites-modal'));
        favoritesModal.show();
    }
    
    // Function to fetch weather for a single city from favorites
    function fetchSingleCityWeather(city, country) {
        // Find city coordinates from the cities list in config
        $.ajax({
            type: 'POST',
            url: 'api/get_city_weather.php',
            data: { city: city, country: country },
            dataType: 'json',
            success: function(response) {
                // Hide loading indicator
                $('#loading').hide();
                
                if (response.error) {
                    showNotification(response.error);
                    return;
                }
                
                // Store result in global variable
                allDestinations = [response];
                
                // Reset to first page
                currentPage = 0;
                
                // Display results
                displayResults();
            },
            error: function(xhr, status, error) {
                // Hide loading indicator
                $('#loading').hide();
                
                // Show error message
                showNotification('Error fetching weather data. Please try again later.');
                console.error(xhr.responseText);
            }
        });
    }
});