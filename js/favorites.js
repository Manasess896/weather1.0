$(document).ready(function () {
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

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

    window.updateFavoritesUI = function() {
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
                    // These are global and will be picked up by displayResults you know the other file 
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