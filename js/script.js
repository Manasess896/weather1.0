$(document).ready(function() {
    // Global variables
    var allDestinations = [];
    var currentPage = 0;
    var destinationsPerPage = 6;
    var map = null;
    var markers = [];
    var favorites = loadFavorites();

    // Add smooth scroll behavior
    $('html').css('scroll-behavior', 'smooth');

    // Removed form animations that were causing issues with options sliding out of view
    $('.form-check-input').on('change', function() {
        // Simple highlight effect without animation
        $(this).closest('.form-check').addClass('selected');
    });

    // Enhanced loading state
    function showLoading() {
        $('#loading').fadeIn(300);
        $('#results-container').fadeOut(300);
        $('body').addClass('loading-state');
    }

    function hideLoading() {
        $('#loading').fadeOut(300);
        $('body').removeClass('loading-state');
    }

    // Smooth card animations on load
    // Modified card animations to be simpler and less movement-based
    function animateCards() {
        $('.destination-card').each(function(index) {
            $(this).css({
                'opacity': '0'
            }).delay(index * 50).animate({
                'opacity': '1'
            }, 400);
        });
    }

   
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

   
    $(document).on('click', '.btn-favorite', function() {
        const city = $(this).data('city');
        const country = $(this).data('country');
        
        if (isFavorite(city, country)) {
            removeFromFavorites(city, country);
            $(this).html('<i class="far fa-heart"></i>');
        } else {
            addToFavorites(city, country);
            $(this).html('<i class="fas fa-heart text-danger"></i>');
            
           
            showNotification(`${city}, ${country} added to favorites!`);
        }
    });

    // Enhanced notification system
    function showNotification(message, type = 'success') {
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        const notification = $(`
            <div class="notification ${type}">
                <i class="${iconClass}" style="margin-right: 0.5rem;"></i>
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        // Add entrance animation
        setTimeout(() => {
            notification.addClass('show');
        }, 100);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 500);
        }, 3000);
        
        // Click to dismiss
        notification.on('click', function() {
            $(this).removeClass('show');
            setTimeout(() => $(this).remove(), 500);
        });
    }

    
    $('#weather-form').on('submit', function(e) {
        e.preventDefault();
        
   
        $('#loading').show();
        $('#results-container').hide();
        
      
        var formData = $(this).serialize();
        

        $.ajax({
            type: 'POST',
            url: 'api/get_recommendations.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
               
                $('#loading').hide();
                
              
                allDestinations = response;
                
               
                currentPage = 0;
             
                displayResults();
            },
            error: function(xhr, status, error) {
              
                $('#loading').hide();
                
             
                alert('Error: ' + error + '. Please try again later.');
                console.error(xhr.responseText);
            }
        });
    });
    
    
    function displayResults() {
       
        var resultsRow = $('#results-row');
        resultsRow.empty();
        
        // Check if we have results
        if (allDestinations.length === 0) {
            resultsRow.html('<div class="col-12 text-center"><h3>No destinations match your criteria. Please try different weather preferences.</h3></div>');
            $('#results-container').show();
            $('#pagination-controls').hide();
            return;
        }
        
        
        var tempUnit = allDestinations[0].temp_unit || '°C';
        
       
        var startIndex = currentPage * destinationsPerPage;
        var endIndex = Math.min(startIndex + destinationsPerPage, allDestinations.length);
        
        
        var pageDestinations = allDestinations.slice(startIndex, endIndex);
        
    
        for (var i = 0; i < pageDestinations.length; i++) {
            var destination = pageDestinations[i];
            
            
            var weatherIcons = getWeatherIcons(destination.current);
            
            
            var displayUnit = destination.current.temp_unit || tempUnit;
            
          
            var forecastHtml = '';
            for (var j = 0; j < destination.forecast.length; j++) {
                var day = destination.forecast[j];
                var dayName = new Date(day.date).toLocaleDateString('en-US', { weekday: 'short' });
                var dayUnit = day.temp_unit || displayUnit;
                
                forecastHtml += `
                    <div class="col-4">
                        <div class="card forecast-card h-100">
                            <div class="card-body p-2 text-center">
                                <h6>${dayName}</h6>
                                <div class="weather-icon-small">
                                    <i class="${getWeatherIconClass(day.condition)}"></i>
                                </div>
                                <p class="mb-0 forecast-temp">${day.temp_max}${dayUnit} / ${day.temp_min}${dayUnit}</p>
                                <small class="forecast-condition">${day.condition}</small>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            
            var activitiesHtml = '<div class="activities-container mt-3"><h6><i class="fas fa-hiking me-2"></i>Suggested Activities:</h6><ul class="mb-0">';
            destination.activities.forEach(function(activity) {
                activitiesHtml += `<li>${activity}</li>`;
            });
            activitiesHtml += '</ul></div>';
            
            
            var travelAdviceHtml = '';
            if (destination.travel_advice) {
                travelAdviceHtml = `
                    <div class="travel-advice-container mt-3">
                        <h6><i class="fas fa-suitcase me-2"></i>Travel Advice:</h6>
                        <p class="mb-0 small">${destination.travel_advice}</p>
                    </div>
                `;
            }
            
          
            var cardHtml = `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow destination-card">
                        <div class="result-title d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 city-title">${destination.city}, ${destination.country}</h5>
                            <button class="btn btn-sm btn-favorite" data-city="${destination.city}" data-country="${destination.country}">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center mb-2">
                                <div class="col-6 text-center">
                                    <div class="temp-display-compact">
                                        ${destination.current.temp}${displayUnit}
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="weather-icon-compact">
                                        <i class="${getWeatherIconClass(destination.current.condition)}"></i>
                                        <div class="current-condition">${destination.current.condition}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="weather-details d-flex justify-content-around mb-2">
                                <div class="weather-data-item"><i class="fas fa-water text-primary"></i> ${destination.current.humidity}%</div>
                                <div class="weather-data-item"><i class="fas fa-wind text-info"></i> ${destination.current.wind_speed} km/h</div>
                            </div>
                            
                            <div class="match-badge text-center mb-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-percentage me-1"></i> Match: ${destination.match_score}%
                                </span>
                            </div>
                            
                            <div class="text-center">
                                <a href="destination.php?city=${encodeURIComponent(destination.city)}&country=${encodeURIComponent(destination.country)}" class="btn btn-sm btn-outline-primary w-100">
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
        
      
        initOrUpdateMap();
        
        
        $('#results-container').show();
        
        $('html, body').animate({
            scrollTop: $("#results-container").offset().top - 100
        }, 800);

        updateFavoritesUI();
    }
    
    function initOrUpdateMap() {
        if (markers.length > 0) {
            markers.forEach(function(marker) {
                marker.remove();
            });
            markers = [];
        }
        
        if (!map) {
            map = L.map('results-map').setView([20, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18
            }).addTo(map);
        }
        
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
            
           
            var marker = L.marker([destination.lat, destination.lng], {
                icon: markerIcon,
                title: destination.city + ', ' + destination.country
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
        
        if (map) {
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }
    });
    

    function updatePagination() {
        var paginationContainer = $('#pagination-controls');
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
                        <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
                            <a class="page-link" href="#" id="prev-page" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
        `;
        
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

    
    $('#favorites-link').on('click', function(e) {
        e.preventDefault();
        showFavoritesModal();
    });
    
    
    $('#clear-favorites').on('click', function() {
        if (confirm('Are you sure you want to remove all favorites?')) {
            favorites = [];
            saveFavorites();
            showFavoritesModal();
            showNotification('All favorites have been cleared');
        }
    });
    
    
    function showFavoritesModal() {
        const favoritesContainer = $('#favorites-container');
        favoritesContainer.empty();
        
        
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
            
            
            $('.remove-favorite').on('click', function() {
                const city = $(this).data('city');
                const country = $(this).data('country');
                removeFromFavorites(city, country);
                showFavoritesModal(); // Refresh modal
                showNotification(`${city}, ${country} removed from favorites`);
            });
            
           
            $('.fetch-weather').on('click', function() {
                const city = $(this).data('city');
                const country = $(this).data('country');
                
                
                $('#favorites-modal').modal('hide');
                
                
                $('#loading').show();
                
               
                fetchSingleCityWeather(city, country);
            });
        }
        
       
        const favoritesModal = new bootstrap.Modal(document.getElementById('favorites-modal'));
        favoritesModal.show();
    }
    
    function fetchSingleCityWeather(city, country) {
        
        $.ajax({
            type: 'POST',
            url: 'api/get_city_weather.php',
            data: { city: city, country: country },
            dataType: 'json',
            success: function(response) {
                
                $('#loading').hide();
                
                if (response.error) {
                    showNotification(response.error);
                    return;
                }
                
              
                allDestinations = [response];
                
                
                currentPage = 0;
                
              // Display results
                displayResults();
            },
            error: function(xhr, status, error) {
               
                $('#loading').hide();
                
              
                showNotification('Error fetching weather data. Please try again later.');
                console.error(xhr.responseText);
            }
        });
    }
});