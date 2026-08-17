<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gamelendar</title>
    </head>
<body>
<style>
html,
body {
    margin: 0;
    padding: 0;
    background: #101216;
    color: #e6e8eb;
    font-family: Arial, Helvetica, sans-serif;
}

/* Main container */
.notable-releases {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px;
    box-sizing: border-box;
    background: #101216;
}

/* Main title */
.notable-releases h2 {
    margin: 0 0 30px;
    padding: 0 0 18px;

    font-size: 28px;
    font-weight: 600;
    color: #ffffff;

    border-bottom: 1px solid #2b2f36;
}

/* Section title */
.notable-releases h3 {
    display: flex;
    align-items: center;

    margin: 30px 0 12px;
    padding: 0 0 10px;

    font-size: 18px;
    font-weight: 600;

    color: #dfe3e8;

    border-bottom: 1px solid #2b2f36;
}

.notable-releases h3:first-of-type {
    margin-top: 0;
}

/* Upcoming section */
.notable-releases h3:first-of-type {
    color: #6ea8ff;
}

/* Release item */
.release-item {
    display: flex;
    align-items: center;

    min-height: 48px;
    margin: 0 0 6px;
    padding: 7px 10px;

    box-sizing: border-box;

    background: #191c21;
    border: 1px solid #272b31;
    border-radius: 6px;

    transition:
        background 0.15s ease,
        border-color 0.15s ease,
        transform 0.15s ease;
}

/* Hover */
.release-item:hover {
    background: #20242a;
    border-color: #3a4048;
    transform: translateX(3px);
}

/* Date */
.release-item strong {
    display: flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 115px;
    min-height: 30px;

    margin-right: 12px;
    padding: 0 8px;

    box-sizing: border-box;

    background: #252a31;
    border: 1px solid #30353d;
    border-radius: 4px;

    color: #aeb5bf;

    font-size: 13px;
    font-weight: 500;
    line-height: 1;
    white-space: nowrap;
}

/* Game link */
.release-item a {
    min-width: 0;

    color: #75aefc;

    font-size: 15px;
    font-weight: 500;
    line-height: 1.4;

    text-decoration: none;

    transition: color 0.15s ease;
}

.release-item a:hover {
    color: #a9cbff;
    text-decoration: underline;
}

/* Upcoming items */
.notable-releases h3:first-of-type + .release-item {
    border-left: 3px solid #4d8eea;
}

/* Released section */
.notable-releases h3:nth-of-type(2) {
    margin-top: 35px;
    color: #9aa1aa;
}

.notable-releases h3:nth-of-type(2) + .release-item {
    border-left: 3px solid #555b63;
}

/* Released links slightly muted */
.notable-releases h3:nth-of-type(2) ~ .release-item a {
    color: #b1b7bf;
}

.notable-releases h3:nth-of-type(2) ~ .release-item a:hover {
    color: #e0e3e7;
}

/* Released dates */
.notable-releases h3:nth-of-type(2) ~ .release-item strong {
    color: #8f969f;
    background: #202329;
}

/* Empty / error messages */
.notable-releases p {
    margin: 15px 0;
    padding: 15px;

    color: #aeb4bc;

    background: #191c21;
    border: 1px solid #2b2f36;
    border-radius: 6px;
}

/* Scrollbar */
.notable-releases ::-webkit-scrollbar {
    width: 8px;
}

.notable-releases ::-webkit-scrollbar-track {
    background: #101216;
}

.notable-releases ::-webkit-scrollbar-thumb {
    background: #363b43;
    border-radius: 4px;
}

.notable-releases ::-webkit-scrollbar-thumb:hover {
    background: #464d57;
}

/* Mobile */
@media (max-width: 600px) {

    .notable-releases {
        padding: 20px 12px;
    }

    .notable-releases h2 {
        margin-bottom: 24px;
        font-size: 23px;
    }

    .notable-releases h3 {
        font-size: 17px;
    }

    .release-item {
        align-items: flex-start;
        flex-direction: column;

        gap: 8px;

        padding: 10px 12px;
    }

    .release-item strong {
        flex: none;

        width: auto;
        min-height: 28px;

        margin-right: 0;
        padding: 0 9px;
    }

    .release-item a {
        font-size: 14px;
        line-height: 1.5;
    }
}
</style>
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
    echo '<h3>Скоро выйдут</h3>';

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
    echo '<h3>Вышли</h3>';

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
