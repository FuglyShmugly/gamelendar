<!DOCTYPE html>
<html>
<!-- 
ГЕЙЛЕНДАРЬ ЗДЕЛОЛ ФУГЛЯ В 2026 ГОДУ!
ХВАТИТ ПОДГЛЯДЫВАТЬ! 
КТО ПРОЧИТАЛ, ТОТ НЕМЫТАЯ ПИПИСЬКА!
-->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GЕЙМЛЕНДАРЬ</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php

function showNotableReleases() {
    $apiUrl = 'https://www.pcgamingwiki.com/w/api.php?action=parse&page=Home&prop=text&format=json';

    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: MyPHPApp/1.0\r\n"
        ]
    ]);

    $json = @file_get_contents($apiUrl, false, $context);

    if ($json === false) {
        echo '<p>Failed to fetch data from PCGamingWiki.</p>';
        return;
    }

    $data = json_decode($json, true);

    if (!isset($data['parse']['text']['*'])) {
        echo '<p>Invalid response from API.</p>';
        return;
    }

    $html = $data['parse']['text']['*'];

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // === FIXED SELECTOR ===
    // Specifically target the card that contains "Notable releases"
    $card = $xpath->query('//div[contains(@class,"home-card") and contains(@class,"upcoming-releases")][.//p[contains(text(),"Notable releases")]]')->item(0);

    if (!$card) {
        echo '<p>Notable releases block not found.</p>';
        return;
    }

    // Get all game rows
    $rows = $xpath->query('.//tr[contains(@class, "table-assignment-body-row")]', $card);

    if ($rows->length === 0) {
        echo '<p>No release data found.</p>';
        return;
    }



    $currentSection = null;

    // We will process the rows in order
    // foreach ($rows as $row) {
        // Detect if we are in Upcoming or Released section
        // by looking at the previous month header (simple approach)
        
        // $dateCell = $xpath->query('.//td[contains(@class, "table-assignment-body-release-date")]', $row)->item(0);
        // $gameLink = $xpath->query('.//td[contains(@class, "table-assignment-body-game")]//a', $row)->item(0);

        // if (!$dateCell || !$gameLink) continue;

        // $fullDate = $dateCell->getAttribute('data-sort-value'); // 2026-08-18
        // $title = trim($gameLink->textContent);
        // $href = $gameLink->getAttribute('href');

        // if (strpos($href, 'http') !== 0) {
        //     $href = 'https://www.pcgamingwiki.com' . $href;
        // }

        // $niceDate = $fullDate ? date('M j', strtotime($fullDate)) : trim($dateCell->textContent);

        // Optional: Group by Upcoming / Released
        // (We can improve this further if needed)
        echo '<div class="notable-releases">';

$upcoming = array();
$released = array();

$today = date('Y-m-d');

foreach ($rows as $row) {
    $dateCell = $xpath->query(
        './/td[contains(@class, "table-assignment-body-release-date")]',
        $row
    )->item(0);

    $gameLink = $xpath->query(
        './/td[contains(@class, "table-assignment-body-game")]//a',
        $row
    )->item(0);

    if (!$dateCell || !$gameLink) {
        continue;
    }

    $fullDate = $dateCell->getAttribute('data-sort-value');
    $title = trim($gameLink->textContent);
    $href = $gameLink->getAttribute('href');

    if (strpos($href, 'http') !== 0) {
        $href = 'https://www.pcgamingwiki.com' . $href;
    }

$months = array(
    1  => 'янв.',
    2  => 'февр.',
    3  => 'мар.',
    4  => 'апр.',
    5  => 'мая',
    6  => 'июн.',
    7  => 'июл.',
    8  => 'авг.',
    9  => 'сент.',
    10 => 'окт.',
    11 => 'нояб.',
    12 => 'дек.'
);

$timestamp = strtotime($fullDate);

$niceDate = $fullDate
    ? date('j', $timestamp) . ' ' . $months[(int)date('n', $timestamp)]
    : trim($dateCell->textContent);

    $item = array(
        'date'  => $niceDate,
        'title' => $title,
        'href'  => $href
    );

    if ($fullDate >= $today) {
        $upcoming[] = $item;
    } else {
        $released[] = $item;
    }
}

/*
 * UPCOMING
 */
if (count($upcoming) > 0) {
    echo '<h3>СЛЕДУЮЩИЕ РЕЛИЗЫ</h3>';

    foreach ($upcoming as $item) {
        echo '<div class="release-item">';
        echo '<strong>' . htmlspecialchars($item['date']) . '</strong>  ';
        echo '<a href="' . htmlspecialchars($item['href']) . '" target="_blank" rel="noopener">';
        echo htmlspecialchars($item['title']);
        echo '</a>';
        echo '</div>';
    }
}

/*
 * RELEASED
 */
if (count($released) > 0) {
    echo '<h3>УЖЕ РЕЛИЗНУЛИСЬ</h3>';

    foreach ($released as $item) {
        echo '<div class="release-item">';
        echo '<strong>' . htmlspecialchars($item['date']) . '</strong>   ';
        echo '<a href="' . htmlspecialchars($item['href']) . '" target="_blank" rel="noopener">';
        echo htmlspecialchars($item['title']);
        echo '</a>';
        echo '</div>';
    }
}

echo '</div>';



}

// Usage
showNotableReleases();
?>

</body>
</html>
