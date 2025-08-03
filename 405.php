<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>405 - Method Not Allowed | Weather App</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .error-container {
      max-width: 600px;
      margin: 100px auto;
      text-align: center;
      padding: 40px 20px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .error-code {
      font-size: 120px;
      font-weight: bold;
      color: #e74c3c;
      margin: 0;
      line-height: 1;
    }

    .error-message {
      font-size: 24px;
      color: #2c3e50;
      margin: 20px 0;
    }

    .error-description {
      font-size: 16px;
      color: #7f8c8d;
      margin: 20px 0 40px;
      line-height: 1.5;
    }

    .back-button {
      display: inline-block;
      padding: 12px 30px;
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
    }

    .back-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(52, 152, 219, 0.4);
    }

    .weather-icon {
      font-size: 80px;
      margin: 20px 0;
      opacity: 0.7;
    }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="weather-icon">🌩️</div>
    <h1 class="error-code">405</h1>
    <h2 class="error-message">Method Not Allowed</h2>
    <p class="error-description">
      Sorry, the HTTP method you used is not allowed for this resource.
      This endpoint only accepts specific request methods.
    </p>
    <a href="home" class="back-button">← Back to Weather App</a>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.querySelector('.error-container');
      container.style.opacity = '0';
      container.style.transform = 'translateY(30px)';

      setTimeout(() => {
        container.style.transition = 'all 0.5s ease';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0)';
      }, 100);
    });
  </script>
</body>

</html>