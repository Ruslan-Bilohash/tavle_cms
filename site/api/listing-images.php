<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
        http_response_code(403);
        echo json_encode(['error' => 'csrf']);
        exit;
    }
    $carId = (int)($_POST['car_id'] ?? 0);
    $car = Database::fetchOne('SELECT * FROM cars WHERE id = ?', 'i', [$carId]);
    if (!$car || !canManageListingImages($car)) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
    if (empty($_FILES['photos'])) {
        http_response_code(400);
        echo json_encode(['error' => 'no_files']);
        exit;
    }
    $result = processListingPhotoUploads($carId, $_FILES['photos']);
    $images = getListingImages($carId);
    echo json_encode([
        'uploaded' => $result['uploaded'],
        'errors' => $result['errors'],
        'images' => array_map(static function (array $img): array {
            return [
                'id' => (int)$img['id'],
                'url' => getCarImageUrl($img['filename'], (int)$img['car_id']),
                'is_main' => (int)$img['is_main'],
            ];
        }, $images),
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
$token = $input[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

if ($method === 'DELETE') {
    if (!verifyCsrfToken($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'csrf']);
        exit;
    }
    $imageId = (int)($input['image_id'] ?? 0);
    if ($imageId <= 0 || !deleteListingImage($imageId, $userId)) {
        http_response_code(400);
        echo json_encode(['error' => 'delete_failed']);
        exit;
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'PATCH') {
    if (!verifyCsrfToken($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'csrf']);
        exit;
    }
    $imageId = (int)($input['image_id'] ?? 0);
    if ($imageId <= 0 || !setListingMainImage($imageId, $userId)) {
        http_response_code(400);
        echo json_encode(['error' => 'main_failed']);
        exit;
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'method_not_allowed']);