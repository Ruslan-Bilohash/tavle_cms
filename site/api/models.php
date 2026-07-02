<?php
/**
 * Bilen CMS - API: Get models by brand (AJAX)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/functions.php';

$brandId = (int)($_GET['brand_id'] ?? 0);

if ($brandId <= 0) {
    echo json_encode([]);
    exit;
}

$models = getModelsByBrand($brandId);
echo json_encode($models, JSON_UNESCAPED_UNICODE);