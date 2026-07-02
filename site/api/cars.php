<?php
/**
 * Bilen CMS - API: Filtered car listings (AJAX HTML partial)
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/functions.php';

$filters = buildCarFilters($_GET);
$result = getCars($filters);
$cars = $result['cars'];

if (empty($cars)) {
    echo '<div class="no-results"><i class="bi bi-search display-4 text-muted"></i><p>' . __t('no_results') . '</p></div>';
    exit;
}

foreach ($cars as $car) {
    require dirname(__DIR__) . '/includes/car-card.php';
}