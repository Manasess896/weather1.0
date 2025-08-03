<?php
require_once 'includes/config.php';

// Pagination settings
$articlesPerPage = 12;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $articlesPerPage;

function fetchWeatherNews($extended = false)
{
  $rssUrl = "https://news.google.com/rss/search?q=weather&hl=en&gl=US&ceid=US:en";

  // For extended results, we can try different RSS endpoints or parameters
  if ($extended) {
    // Add more comprehensive search terms for extended results
    $searchTerms = ['weather', 'climate', 'forecast', 'meteorology', 'storm', 'hurricane', 'temperature'];
    $allItems = [];

    foreach ($searchTerms as $term) {
      $extendedUrl = "https://news.google.com/rss/search?q=" . urlencode($term) . "&hl=en&gl=US&ceid=US:en";
      $items = fetchSingleRssFeed($extendedUrl);
      if ($items) {
        // Convert SimpleXMLElement to array for merging
        $itemsArray = [];
        foreach ($items as $item) {
          $itemsArray[] = $item;
        }
        $allItems = array_merge($allItems, $itemsArray);
      }
    }

    // Remove duplicates based on title similarity
    $uniqueItems = [];
    $seenTitles = [];

    foreach ($allItems as $item) {
      $title = (string)$item->title;
      $titleKey = strtolower(preg_replace('/[^a-z0-9]/', '', $title));

      if (!in_array($titleKey, $seenTitles)) {
        $seenTitles[] = $titleKey;
        $uniqueItems[] = $item;
      }
    }

    return $uniqueItems;
  }

  // For non-extended results, convert SimpleXMLElement to array
  $singleFeedItems = fetchSingleRssFeed($rssUrl);
  if ($singleFeedItems) {
    $itemsArray = [];
    foreach ($singleFeedItems as $item) {
      $itemsArray[] = $item;
    }
    return $itemsArray;
  }

  return false;
}

function fetchSingleRssFeed($rssUrl)
{
  try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rssUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Weather App News Reader 1.0');

    $rssContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $rssContent) {
      $rss = simplexml_load_string($rssContent);

      if ($rss && isset($rss->channel->item)) {
        return $rss->channel->item;
      }
    }

    return false;
  } catch (Exception $e) {
    error_log("Weather news fetch error: " . $e->getMessage());
    return false;
  }
}

// Fetch extended news for pagination
$allNewsItems = fetchWeatherNews(true);
$totalArticles = $allNewsItems ? count($allNewsItems) : 0;
$totalPages = ceil($totalArticles / $articlesPerPage);

// Get items for current page
$newsItems = false;
if ($allNewsItems && $totalArticles > 0) {
  $newsItems = array_slice($allNewsItems, $offset, $articlesPerPage);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weather News | Weather App</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/style-fixes.css">
  <style>
    .news-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .page-header {
      text-align: center;
      margin-bottom: 40px;
      padding: 40px 20px;
      background: linear-gradient(135deg, teal, green);
      border-radius: 15px;
      color: white;
      box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .page-header h1 {
      font-size: 2.5em;
      margin: 0 0 10px 0;
      font-weight: 600;
    }

    .page-header p {
      font-size: 1.1em;
      margin: 0;
      opacity: 0.9;
    }

    .news-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .news-item {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      border-left: 4px solid teal;
    }

    .news-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .news-title {
      font-size: 1.3em;
      font-weight: 600;
      color: #2c3e50;
      margin: 0 0 15px 0;
      line-height: 1.4;
    }

    .news-description {
      color: #7f8c8d;
      line-height: 1.6;
      margin: 0 0 20px 0;
      font-size: 0.95em;
    }

    .news-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      font-size: 0.85em;
      color: #95a5a6;
    }

    .news-date {
      font-style: italic;
    }

    .news-source {
      background: #ecf0f1;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 0.8em;
    }

    .read-more {
      display: inline-block;
      padding: 8px 16px;
      background: linear-gradient(135deg, #27ae60, #219a52);
      color: white;
      text-decoration: none;
      border-radius: 20px;
      font-size: 0.9em;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .read-more:hover {
      transform: translateY(-1px);
      box-shadow: 0 3px 10px rgba(39, 174, 96, 0.3);
    }

    .error-message {
      text-align: center;
      padding: 60px 20px;
      background: rgba(231, 76, 60, 0.1);
      border-radius: 12px;
      color: #e74c3c;
      border: 1px solid rgba(231, 76, 60, 0.2);
    }

    .error-message h3 {
      margin: 0 0 10px 0;
      font-size: 1.5em;
    }

    .loading {
      text-align: center;
      padding: 60px 20px;
      color: #7f8c8d;
    }

    .loading::after {
      content: '';
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 2px solid #bdc3c7;
      border-top: 2px solid teal;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-left: 10px;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .back-nav {
      text-align: center;
      margin-bottom: 30px;
    }

    .back-button {
      display: inline-block;
      padding: 12px 24px;
      background: linear-gradient(135deg, #95a5a6, #7f8c8d);
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .back-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
    }

    .refresh-button {
      display: inline-block;
      padding: 10px 20px;
      background: linear-gradient(135deg, #f39c12, #d68910);
      color: white;
      text-decoration: none;
      border-radius: 20px;
      font-size: 0.9em;
      font-weight: 500;
      transition: all 0.3s ease;
      margin-left: 15px;
    }

    .refresh-button:hover {
      transform: translateY(-1px);
      box-shadow: 0 3px 10px rgba(243, 156, 18, 0.3);
    }

    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 40px 0;
      gap: 10px;
      flex-wrap: wrap;
    }

    .pagination-info {
      text-align: center;
      margin-bottom: 20px;
      color: #7f8c8d;
      font-size: 0.95em;
    }

    .pagination a,
    .pagination span {
      display: inline-block;
      padding: 10px 15px;
      margin: 0 2px;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      min-width: 40px;
      text-align: center;
    }

    .pagination a {
      background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
      color: #2c3e50;
    }

    .pagination a:hover {
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
    }

    .pagination .current {
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: white;
      box-shadow: 0 3px 8px rgba(52, 152, 219, 0.3);
    }

    .pagination .disabled {
      background: #ecf0f1;
      color: #bdc3c7;
      cursor: not-allowed;
      opacity: 0.6;
    }

    .pagination .nav-button {
      background: linear-gradient(135deg, #95a5a6, #7f8c8d);
      color: white;
      font-weight: 600;
    }

    .pagination .nav-button:hover:not(.disabled) {
      background: linear-gradient(135deg, #7f8c8d, #95a5a6);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(149, 165, 166, 0.3);
    }

    .load-more-container {
      text-align: center;
      margin: 30px 0;
    }

    .load-more-button {
      display: inline-block;
      padding: 12px 30px;
      background: linear-gradient(135deg, #9b59b6, #8e44ad);
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 10px rgba(155, 89, 182, 0.3);
    }

    .load-more-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(155, 89, 182, 0.4);
    }

    @media (max-width: 768px) {
      .news-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .page-header h1 {
        font-size: 2em;
      }

      .news-item {
        padding: 20px;
      }
    }
  </style>
</head>

<body>
  <div class="news-container">
    <div class="page-header">
      <h1>🌦️ Weather News</h1>
      <p>Stay updated with the latest weather news and forecasts from around the world</p>
    </div>

    <div class="back-nav">
      <a href="home" class="back-button">← Back to Weather App</a>
      <a href="news" class="refresh-button">🔄 Refresh News</a>
    </div>

    <?php if ($totalArticles > 0): ?>
      <div class="pagination-info">
        <strong>Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></strong>
        (<?php echo $totalArticles; ?> total articles)
      </div>
    <?php endif; ?>

    <?php if ($newsItems && count($newsItems) > 0): ?>
      <div class="news-grid">
        <?php
        foreach ($newsItems as $item):
          $title = html_entity_decode(strip_tags((string)$item->title));
          $description = html_entity_decode(strip_tags((string)$item->description));
          $link = (string)$item->link;
          $pubDate = isset($item->pubDate) ? date('M j, Y', strtotime((string)$item->pubDate)) : 'Recent';
          $source = 'News Source';
          if (preg_match('/- (.+)$/', $title, $matches)) {
            $source = trim($matches[1]);
            $title = preg_replace('/ - .+$/', '', $title);
          }
          if (strlen($description) > 150) {
            $description = substr($description, 0, 150) . '...';
          }
        ?>
          <article class="news-item">
            <div class="news-meta">
              <span class="news-date"><?php echo htmlspecialchars($pubDate); ?></span>
              <span class="news-source"><?php echo htmlspecialchars($source); ?></span>
            </div>
            <h3 class="news-title"><?php echo htmlspecialchars($title); ?></h3>
            <?php if ($description && $description !== $title): ?>
              <p class="news-description"><?php echo htmlspecialchars($description); ?></p>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener noreferrer" class="read-more">
              Read Full Article →
            </a>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($currentPage > 1): ?>
            <a href="news?page=<?php echo $currentPage - 1; ?>" class="nav-button">← Previous</a>
          <?php else: ?>
            <span class="nav-button disabled">← Previous</span>
          <?php endif; ?>
          <?php
          $startPage = max(1, $currentPage - 2);
          $endPage = min($totalPages, $currentPage + 2);

          if ($startPage > 1): ?>
            <a href="news?page=1">1</a>
            <?php if ($startPage > 2): ?>
              <span>...</span>
            <?php endif;
          endif;

          for ($i = $startPage; $i <= $endPage; $i++):
            if ($i == $currentPage): ?>
              <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
              <a href="news?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif;
          endfor;

          if ($endPage < $totalPages):
            if ($endPage < $totalPages - 1): ?>
              <span>...</span>
            <?php endif; ?>
            <a href="news?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
          <?php endif; ?>
          <?php if ($currentPage < $totalPages): ?>
            <a href="news?page=<?php echo $currentPage + 1; ?>" class="nav-button">Next →</a>
          <?php else: ?>
            <span class="nav-button disabled">Next →</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="error-message">
        <h3>⚠️ Unable to Load Weather News</h3>
        <p>We're having trouble fetching the latest weather news at the moment. Please check your internet connection and try again later.</p>
        <p><a href="news" class="refresh-button" style="margin-top: 15px;">🔄 Try Again</a></p>
      </div>
    <?php endif; ?>
    <?php if ($newsItems && count($newsItems) > 0 && $currentPage < $totalPages): ?>
      <div class="load-more-container">
        <a href="news?page=<?php echo $currentPage + 1; ?>" class="load-more-button">
          📰 Load More Articles (Page <?php echo $currentPage + 1; ?>)
        </a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const newsItems = document.querySelectorAll('.news-item');
      newsItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';

        setTimeout(() => {
          item.style.transition = 'all 0.5s ease';
          item.style.opacity = '1';
          item.style.transform = 'translateY(0)';
        }, index * 100);
      });
    });
    document.querySelectorAll('.read-more').forEach(link => {
      link.addEventListener('click', function() {
        this.innerHTML = 'Opening... ⏳';
        this.style.pointerEvents = 'none';
      });
    });
  </script>
</body>

</html>