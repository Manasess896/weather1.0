$(document).ready(function () {
    // Global variables
    var allDestinations = [];
    var currentPage = 0;
    var destinationsPerPage = 6;
    var map;
    var markers = [];
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

    function showNotification(message, type) {
        type = type || 'success';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        const notification = $(`
            <div class="notification ${type}">
                <i class="${iconClass}" style="margin-right: 0.5rem;"></i>
                ${message}
            </div>
        `);

        $('body').append(notification);

        setTimeout(() => {
            notification.addClass('show');
        }, 100);

        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 500);
        }, 3000);

        notification.on('click', function () {
            $(this).removeClass('show');
            setTimeout(() => $(this).remove(), 500);
        });
    }

    function getWeatherIconClass(condition) {
        if (typeof condition !== 'string') {
            return 'fas fa-question-circle text-muted';
        }
        condition = condition.toLowerCase();

        if (condition.includes('clear') || condition.includes('sunny')) {
            return 'fas fa-sun text-warning';
        } else if (condition.includes('cloud')) {
            return 'fas fa-cloud text-secondary';
        } else if (condition.includes('rain') || condition.includes('drizzle')) {
            return 'fas fa-cloud-showers-heavy text-primary';
        } else if (condition.includes('snow')) {
            return 'fas fa-snowflake text-info';
        } else if (condition.includes('thunderstorm')) {
            return 'fas fa-bolt text-danger';
        } else if (condition.includes('mist') || condition.includes('fog') || condition.includes('haze')) {
            return 'fas fa-smog text-muted';
        } else {
            return 'fas fa-question-circle text-muted';
        }
    }

    function getWeatherIcons(weatherData) {
        var icons = [];

        if (weatherData.temp > 25) {
            icons.push('<i class="fas fa-thermometer-three-quarters text-danger" title="Hot"></i>');
        } else if (weatherData.temp < 10) {
            icons.push('<i class="fas fa-thermometer-quarter text-info" title="Cold"></i>');
        }

        if (weatherData.humidity < 30) {
            icons.push('<i class="fas fa-tint-slash text-muted" title="Low Humidity"></i>');
        } else if (weatherData.humidity > 80) {
            icons.push('<i class="fas fa-tint text-primary" title="High Humidity"></i>');
        }

        if (weatherData.wind_speed > 20) {
            icons.push('<i class="fas fa-wind text-info" title="Windy"></i>');
        }

        return icons.join('');
    }

    function isFavorite(city, country) {
        return favorites.some(fav => fav.city === city && fav.country === country);
    }

    function addToFavorites(city, country) {
        if (!isFavorite(city, country)) {
            favorites.push({ city, country });
            localStorage.setItem('favorites', JSON.stringify(favorites));
        }
    }

    function removeFromFavorites(city, country) {
        favorites = favorites.filter(fav => fav.city !== city || fav.country !== country);
        localStorage.setItem('favorites', JSON.stringify(favorites));
    }

    function updateFavoritesUI() {
        $('.btn-favorite').each(function () {
            const city = $(this).data('city');
            const country = $(this).data('country');
            if (isFavorite(city, country)) {
                $(this).html('<i class="fas fa-heart text-danger"></i>');
            } else {
                $(this).html('<i class="far fa-heart"></i>');
            }
        });
    }

    function showFavoritesModal() {
        const favoritesContainer = $('#favorites-container');
        favoritesContainer.empty();

        if (favorites.length === 0) {
            favoritesContainer.html('<p>You have no favorite destinations yet.</p>');
        } else {
            const list = $('<ul class="list-group"></ul>');
            favorites.forEach(fav => {
                const listItem = $(`<li class="list-group-item d-flex justify-content-between align-items-center">${fav.city}, ${fav.country}</li>`);
                const viewButton = $(`<button class="btn btn-sm btn-outline-primary view-favorite-btn">View</button>`);
                viewButton.on('click', function() {
                    fetchSingleCityWeather(fav.city, fav.country);
                    $('#favorites-modal').modal('hide');
                });
                listItem.append(viewButton);
                list.append(listItem);
            });
            favoritesContainer.append(list);
        }

        const favoritesModal = new bootstrap.Modal(document.getElementById('favorites-modal'));
        favoritesModal.show();
    }

    function fetchSingleCityWeather(city, country) {
        $('#loading').show();
        $('#results-container').hide();

        $.ajax({
            type: 'POST',
            url: 'api/get_city_weather.php',
            data: { city: city, country: country },
            dataType: 'json',
            success: function(response) {
                $('#loading').hide();
                if (response && !response.error) {
                    allDestinations = [response];
                    currentPage = 0;
                    displayResults();
                } else {
                    alert('Error: ' + (response.error || 'Could not fetch weather data.'));
                }
            },
            error: function(xhr, status, error) {
                $('#loading').hide();
                alert('Error: ' + error + '. Please try again later.');
                console.error(xhr.responseText);
            }
        });
    }

    function initOrUpdateMap(destinations) {
        if (markers.length > 0) {
            markers.forEach(function (marker) {
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

        destinations.forEach(function (destination, index) {
            if (!destination || !destination.lat || !destination.lng || !destination.current) {
                console.error('Invalid destination for map marker:', destination);
                return;
            }
            var markerColor = 'gray';
            if (destination.match_score >= 80) {
                markerColor = 'green';
            } else if (destination.match_score >= 60) {
                markerColor = 'blue';
            }

            var markerIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="marker-pin bg-${markerColor}">
                         <span>${index + 1 + (currentPage * destinationsPerPage)}</span>
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
            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link page-number" href="#" data-page="${i}">${i + 1}</a></li>`;
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

        $('.page-number').on('click', function (e) {
            e.preventDefault();
            currentPage = parseInt($(this).data('page'));
            displayResults();
        });

        $('#prev-page').on('click', function (e) {
            e.preventDefault();
            if (currentPage > 0) {
                currentPage--;
                displayResults();
            }
        });

        $('#next-page').on('click', function (e) {
            e.preventDefault();
            var totalPages = Math.ceil(allDestinations.length / destinationsPerPage);
            if (currentPage < totalPages - 1) {
                currentPage++;
                displayResults();
            }
        });
    }

    function displayResults() {
        var resultsRow = $('#results-row');
        resultsRow.empty();

        if (allDestinations.length === 0) {
            resultsRow.html('<div class="col-12 text-center"><h3>No destinations match your criteria. Please try different weather preferences.</h3></div>');
            $('#results-container').show();
            $('#pagination-controls').hide();
            return;
        }

        var startIndex = currentPage * destinationsPerPage;
        var endIndex = Math.min(startIndex + destinationsPerPage, allDestinations.length);
        var pageDestinations = allDestinations.slice(startIndex, endIndex);

        for (var i = 0; i < pageDestinations.length; i++) {
            var destination = pageDestinations[i];

            if (!destination || !destination.current) {
                console.error('Invalid destination object found:', destination);
                continue;
            }

            var displayUnit = destination.current.temp_unit || '°C';
            
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
                                <a href="city?city=${encodeURIComponent(destination.city)}&country=${encodeURIComponent(destination.country)}" class="btn btn-sm btn-outline-primary w-100">
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
        $('#results-container').show();
        $('html, body').animate({
            scrollTop: $("#results-container").offset().top - 100
        }, 800);
        updateFavoritesUI();
    }
    $('#weather-form').on('submit', function (e) {
        e.preventDefault();

        $('#loading').show();
        $('#results-container').hide();

        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: 'api/get_recommendations.php',
            data: formData,
            dataType: 'json',
            success: function (response) {
                $('#loading').hide();
                if (response && Array.isArray(response.destinations)) {
                    allDestinations = response.destinations;
                    // console.log('Received destinations:', allDestinations); 
                    currentPage = 0;
                    displayResults();
                } else {
                    allDestinations = [];
                    console.error("Invalid response structure:", response);
                    alert('Error: Received invalid data from the server. Please try again later.');
                    displayResults();
                }
            },
            error: function (xhr, status, error) {
                $('#loading').hide();
                alert('Error: ' + error + '. Please try again later.');
                console.error(xhr.responseText);
            }
        });
    });

    $('#card-view-btn').on('click', function () {
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#map-view-btn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');

        $('#results-row').show();
        $('#map-container').hide();
    });

    $('#map-view-btn').on('click', function () {
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#card-view-btn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');

        $('#results-row').hide();
        $('#map-container').show();

        if (map) {
            setTimeout(function () {
                map.invalidateSize();
            }, 100);
        }
    });

    $(document).on('click', '.btn-favorite', function () {
        const city = $(this).data('city');
        const country = $(this).data('country');

        if (isFavorite(city, country)) {
            removeFromFavorites(city, country);
            $(this).html('<i class="far fa-heart"></i>');
            showNotification(`${city}, ${country} removed from favorites!`, 'error');
        } else {
            addToFavorites(city, country);
            $(this).html('<i class="fas fa-heart text-danger"></i>');
            showNotification(`${city}, ${country} added to favorites!`);
        }
    });

    $('#favorites-link').on('click', function (e) {
        e.preventDefault();
        showFavoritesModal();
    });

    $('#clear-favorites').on('click', function () {
        if (confirm('Are you sure you want to remove all favorites?')) {
            favorites = [];
            localStorage.setItem('favorites', JSON.stringify([]));
            showFavoritesModal();
            updateFavoritesUI();
        }
    });
});