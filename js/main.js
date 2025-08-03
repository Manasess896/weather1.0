// Global variables from other files 
var allDestinations = [];
var currentPage = 0;
var destinationsPerPage = 6;

$(document).ready(function () {
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
                    // console.log('Received destinations:', allDestinations); // For debugging
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
});