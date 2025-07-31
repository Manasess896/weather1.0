# WeatherVoyager

WeatherVoyager is a web application that helps travelers find destinations with their ideal weather conditions. By selecting preferred weather parameters, users receive personalized travel destination recommendations.

## Features

- **Weather Preference Selection**: Choose your ideal weather conditions like sunny, cool breeze, low humidity, etc.
- **Personalized Recommendations**: Get destination recommendations based on your weather preferences
- **Detailed Weather Information**: View detailed current and forecasted weather data for recommended locations
- **Interactive Maps**: Visualize recommended destinations on an interactive map
- **Contact Form**: Reach out to the team with questions, feedback, or suggestions

## Technical Stack

- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5
- **Backend**: PHP
- **APIs**: Weather data APIs
- **Maps**: Leaflet.js for interactive maps
- **Security**: reCAPTCHA integration for form protection

## Project Structure

```
weather1.0/
├── index.php                  # Home page
├── pages/                     # Additional pages
│   ├── about.php              # About page
│   └── contact.php            # Contact page
├── api/                       # API endpoints
│   ├── get_recommendations.php # Weather recommendations API
│   └── get_city_weather.php   # City weather data API
├── includes/                  # Shared PHP code
│   └── config.php             # Configuration settings
├── config/                    # Configuration files
│   └── env_loader.php         # Environment variables loader
├── css/                       # Stylesheets
│   └── styles.css             # Custom styles
└── .htaccess                  # URL rewriting rules
```

## Installation

1. Clone the repository to your web server's document root (e.g., `htdocs` for XAMPP)
2. Configure your environment variables (API keys, etc.)
3. Ensure your web server has mod_rewrite enabled for clean URLs
4. Access the application through your web browser

## Configuration

Create a `.env` file in the root directory with the following variables:
```
WEATHER_API_KEY=your_weather_api_key
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key
```

## URL Structure

With clean URLs enabled, the site uses the following URL patterns:
- Home: `/weather1.0/home`
- About: `/weather1.0/about`
- Contact: `/weather1.0/contact`

## Development

To contribute to this project:
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull RequesFor questions or support, please use the contact form on the website or reach out to the development team directly.
