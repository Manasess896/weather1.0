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

$(document).ready(function () {
    $('#card-view-btn').on('click', function () {
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#map-view-btn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');

        $('#results-row').show();
        $('#map-container').hide();
    });
});