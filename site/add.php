<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editCar = null;
$existingImages = [];
$successData = $_SESSION['listing_success'] ?? null;
unset($_SESSION['listing_success']);

if ($editId > 0) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = url('add.php?edit=' . $editId);
        redirect(url('admin/login.php'));
    }
    $editCar = Database::fetchOne('SELECT * FROM cars WHERE id = ?', 'i', [$editId]);
    if (!$editCar || !isListingOwner($editCar)) {
        http_response_code(403);
        $pageTitle = __t('access_denied');
        require __DIR__ . '/includes/header.php';
        echo '<div class="container-im im-page-head"><p>' . e(__t('access_denied')) . '</p></div>';
        require __DIR__ . '/includes/footer.php';
        exit;
    }
    $existingImages = array_map(static function (array $img): array {
        return [
            'id' => (int)$img['id'],
            'url' => getCarImageUrl($img['filename'], (int)$img['car_id']),
            'is_main' => (int)$img['is_main'],
        ];
    }, getListingImages($editId));
}

$brands = Database::fetchAll('SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name');
$models = $editCar ? getModelsByBrand((int)$editCar['brand_id']) : [];
$error = '';
$isDraftMode = !empty($editCar['is_draft']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    $action = sanitize($_POST['form_action'] ?? 'publish');
    $isDraft = $action === 'draft';
    $name = sanitize($_POST['seller_name'] ?? '');
    $email = sanitize($_POST['seller_email'] ?? '');
    $phone = sanitize($_POST['seller_phone'] ?? '');
    $brandId = (int)($_POST['brand_id'] ?? 0);
    $modelId = (int)($_POST['model_id'] ?? 0);
    $year = (int)($_POST['year'] ?? 0);
    $price = (int)($_POST['price_usd'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');

    if (!$editCar && ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $error = __t('add_error_contact');
    } elseif (!$isDraft && ($brandId <= 0 || $modelId <= 0 || $year < 1990 || $price <= 0)) {
        $error = __t('add_error_car');
    } else {
        if ($title === '' && $brandId > 0 && $modelId > 0) {
            $brand = Database::fetchOne('SELECT name FROM brands WHERE id = ?', 'i', [$brandId]);
            $model = Database::fetchOne('SELECT name FROM models WHERE id = ?', 'i', [$modelId]);
            $title = trim(($brand['name'] ?? '') . ' ' . ($model['name'] ?? '') . ' ' . ($year ?: date('Y')));
        }

        $payload = [
            'brand_id' => $brandId,
            'model_id' => $modelId,
            'title' => $title,
            'slug' => slugify($title !== '' ? $title : __t('draft_listing')),
            'year' => $year,
            'price_usd' => $price,
            'mileage' => !empty($_POST['mileage']) ? (int)$_POST['mileage'] : null,
            'body_type' => sanitize($_POST['body_type'] ?? 'sedan'),
            'transmission' => sanitize($_POST['transmission'] ?? 'automatic'),
            'fuel_type' => sanitize($_POST['fuel_type'] ?? 'petrol'),
            'drive_type' => sanitize($_POST['drive_type'] ?? 'fwd'),
            'color' => sanitize($_POST['color'] ?? ''),
            'region' => sanitize($_POST['region'] ?? ''),
            'city' => sanitize($_POST['city'] ?? ''),
            'description' => $_POST['description'] ?? '',
            'listing_type' => 'car',
            'is_draft' => $isDraft,
        ];

        $photoErrors = [];

        if ($editCar) {
            $carId = savePublicListing($payload, (int)$_SESSION['user_id'], $editId);
            if (!empty($_FILES['photos'])) {
                $uploadResult = processListingPhotoUploads($carId, $_FILES['photos']);
                $photoErrors = $uploadResult['errors'];
            }
            $_SESSION['listing_success'] = [
                'mode' => $isDraft ? 'draft' : 'updated',
                'car_id' => $carId,
                'photo_errors' => $photoErrors,
            ];
            redirect(url('add.php?edit=' . $carId . '&saved=1'));
        }

        $account = findOrCreateListingUser($email, $name, $phone);
        $carId = savePublicListing($payload, (int)$account['user']['id']);
        if (!empty($_FILES['photos'])) {
            $uploadResult = processListingPhotoUploads($carId, $_FILES['photos']);
            $photoErrors = $uploadResult['errors'];
        } elseif (!$isDraft) {
            Database::insert(
                'INSERT INTO car_images (car_id, filename, is_main) VALUES (?, ?, 1)',
                'is',
                [$carId, 'placeholder-1.jpg']
            );
        }
        loginUser($account['user']);
        $_SESSION['listing_success'] = [
            'mode' => $isDraft ? 'draft_created' : 'created',
            'car_id' => $carId,
            'username' => $account['user']['username'],
            'password' => $account['password'],
            'is_new' => $account['is_new'],
            'photo_errors' => $photoErrors,
        ];
        redirect(url($isDraft ? 'add.php?edit=' . $carId . '&saved=1' : 'add.php?success=1'));
    }
}

$pageTitle = $editCar ? __t('edit_listing') : __t('add_listing');
$pageDescription = __t('add_listing_intro');
$extraCss = asset('css/listing-wizard.css') . '?v=1';
$extraJs = asset('js/filters.js');
$extraJs2 = asset('js/listing-wizard.js') . '?v=1';
$crumbs = [
    ['name' => __t('home'), 'url' => absoluteUrl(url())],
    ['name' => $pageTitle, 'url' => absoluteUrl(url('add.php'))],
];
$extraSchema = renderBreadcrumbSchema($crumbs);
require __DIR__ . '/includes/header.php';
?>

<div class="container-im im-page-head">
    <h1><?= e($pageTitle) ?></h1>
    <p class="im-page-sub"><?= e(__t('add_listing_intro')) ?></p>
    <?php if ($isDraftMode): ?>
    <p class="im-alert" style="margin-top:12px;background:#fef3c7;color:#92400e;border-radius:8px;padding:10px 14px;">
        <i class="bi bi-pencil-square"></i> <?= e(__t('draft_mode_notice')) ?>
    </p>
    <?php endif; ?>
</div>

<section class="container-im im-add-section">
    <?php if ($successData || !empty($_GET['saved'])): ?>
    <div class="im-success-box">
        <?php
        $mode = $successData['mode'] ?? '';
        if ($mode === 'created'): ?>
        <h2><i class="bi bi-check-circle-fill"></i> <?= e(__t('add_success_title')) ?></h2>
        <p><?= e(__t('add_success_text')) ?></p>
        <?php if (!empty($successData['is_new']) && !empty($successData['password'])): ?>
        <div class="im-credentials">
            <p><strong><?= e(__t('login')) ?>:</strong> <code><?= e($successData['username']) ?></code></p>
            <p><strong><?= e(__t('password')) ?>:</strong> <code><?= e($successData['password']) ?></code></p>
            <p class="im-credentials-note"><?= e(__t('add_save_credentials')) ?></p>
        </div>
        <?php endif; ?>
        <?php elseif ($mode === 'draft' || $mode === 'draft_created'): ?>
        <h2><i class="bi bi-file-earmark-text"></i> <?= e(__t('draft_success_title')) ?></h2>
        <p><?= e(__t('draft_success_text')) ?></p>
        <?php else: ?>
        <h2><i class="bi bi-check-circle-fill"></i> <?= e(__t('edit_success_title')) ?></h2>
        <?php endif; ?>
        <?php if (!empty($successData['photo_errors'])): ?>
        <div class="im-alert im-alert-error" style="margin-top:12px">
            <?php foreach ($successData['photo_errors'] as $pe): ?>
            <div><?= e($pe) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="im-success-actions">
            <?php if ($mode !== 'draft' && $mode !== 'draft_created'): ?>
            <a href="<?= e(carUrl((int)($successData['car_id'] ?? $editId), '')) ?>" class="im-btn im-btn-primary"><?= e(__t('view_listing')) ?></a>
            <?php endif; ?>
            <?php if ($mode === 'draft' || $mode === 'draft_created'): ?>
            <a href="<?= url('add.php?edit=' . (int)($successData['car_id'] ?? $editId)) ?>" class="im-btn im-btn-primary"><?= e(__t('continue_editing')) ?></a>
            <?php endif; ?>
            <a href="<?= url('my-listings.php') ?>" class="im-btn"><?= e(__t('my_listings')) ?></a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="im-alert im-alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (!$successData): ?>
    <div class="im-wizard-layout">
        <div class="im-wizard-main">
            <form method="post" enctype="multipart/form-data" class="im-add-form" id="publicListingForm" data-step="1">
                <?= csrfField() ?>
                <input type="hidden" name="form_action" id="formAction" value="publish">

                <div class="im-wizard-steps" role="tablist">
                    <?php if (!$editCar): ?>
                    <button type="button" class="im-wizard-step is-active" data-step="1">
                        <span>1</span> <?= e(__t('step_contact')) ?>
                    </button>
                    <?php endif; ?>
                    <button type="button" class="im-wizard-step<?= $editCar ? ' is-active' : '' ?>" data-step="2">
                        <span><?= $editCar ? '1' : '2' ?></span> <?= e(__t('step_details')) ?>
                    </button>
                    <button type="button" class="im-wizard-step" data-step="3">
                        <span><?= $editCar ? '2' : '3' ?></span> <?= e(__t('step_photos')) ?>
                    </button>
                    <button type="button" class="im-wizard-step" data-step="4">
                        <span><?= $editCar ? '3' : '4' ?></span> <?= e(__t('step_review')) ?>
                    </button>
                </div>

                <?php if (!$editCar): ?>
                <div class="im-wizard-panel is-active" data-panel="1">
                    <h2><?= e(__t('seller_contact')) ?></h2>
                    <p class="im-add-hint"><?= e(__t('add_account_hint')) ?></p>
                    <div class="im-add-grid">
                        <div class="im-field">
                            <label><?= e(__t('seller_name')) ?> *</label>
                            <input type="text" name="seller_name" required value="<?= e($_POST['seller_name'] ?? '') ?>">
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('seller_email')) ?> *</label>
                            <input type="email" name="seller_email" required value="<?= e($_POST['seller_email'] ?? '') ?>">
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('seller_phone')) ?> *</label>
                            <input type="tel" name="seller_phone" required value="<?= e($_POST['seller_phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="im-wizard-panel<?= $editCar ? ' is-active' : '' ?>" data-panel="2">
                    <h2><?= e(__t('car_details')) ?></h2>
                    <p class="im-add-hint"><?= e(__t('step_details_hint')) ?></p>
                    <div class="im-add-grid">
                        <div class="im-field">
                            <label><?= e(__t('brand')) ?> *</label>
                            <select name="brand_id" id="brandSelectAdd" class="js-brand-select" data-model-target="modelSelectAdd" required>
                                <option value=""><?= e(__t('all')) ?></option>
                                <?php foreach ($brands as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"<?= (int)($editCar['brand_id'] ?? $_POST['brand_id'] ?? 0) === (int)$b['id'] ? ' selected' : '' ?>><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('model')) ?> *</label>
                            <select name="model_id" id="modelSelectAdd" class="js-model-select" data-all-label="<?= e(__t('all')) ?>" required>
                                <option value=""><?= e(__t('all')) ?></option>
                                <?php foreach ($models as $m): ?>
                                <option value="<?= (int)$m['id'] ?>"<?= (int)($editCar['model_id'] ?? $_POST['model_id'] ?? 0) === (int)$m['id'] ? ' selected' : '' ?>><?= e($m['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('title')) ?></label>
                            <input type="text" name="title" placeholder="<?= e(__t('title_auto')) ?>" value="<?= e($editCar['title'] ?? $_POST['title'] ?? '') ?>">
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('year')) ?> *</label>
                            <input type="number" name="year" min="1990" max="2030" required value="<?= (int)($editCar['year'] ?? $_POST['year'] ?? date('Y')) ?>">
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('price')) ?> (USD) *</label>
                            <input type="number" name="price_usd" min="1" required value="<?= (int)($editCar['price_usd'] ?? $_POST['price_usd'] ?? 0) ?>">
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('mileage')) ?></label>
                            <input type="number" name="mileage" min="0" value="<?= (int)($editCar['mileage'] ?? $_POST['mileage'] ?? 0) ?>">
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('city')) ?></label>
                            <input type="text" name="city" value="<?= e($editCar['city'] ?? $_POST['city'] ?? '') ?>">
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('region')) ?></label>
                            <select name="region">
                                <option value=""><?= e(__t('all')) ?></option>
                                <?php foreach (getRegions() as $r): ?>
                                <option value="<?= e($r) ?>"<?= ($editCar['region'] ?? $_POST['region'] ?? '') === $r ? ' selected' : '' ?>><?= e($r) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('body')) ?></label>
                            <select name="body_type">
                                <?php foreach (getBodyTypes() as $bt): ?>
                                <option value="<?= $bt ?>"<?= ($editCar['body_type'] ?? 'sedan') === $bt ? ' selected' : '' ?>><?= e(__t($bt)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('transmission')) ?></label>
                            <select name="transmission">
                                <?php foreach (getTransmissions() as $tr): ?>
                                <option value="<?= $tr ?>"<?= ($editCar['transmission'] ?? 'automatic') === $tr ? ' selected' : '' ?>><?= e(__t($tr)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('engine')) ?></label>
                            <select name="fuel_type">
                                <?php foreach (getFuelTypes() as $f): ?>
                                <option value="<?= $f ?>"<?= ($editCar['fuel_type'] ?? 'petrol') === $f ? ' selected' : '' ?>><?= e(__t($f)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('drive')) ?></label>
                            <select name="drive_type">
                                <?php foreach (getDriveTypes() as $d): ?>
                                <option value="<?= $d ?>"<?= ($editCar['drive_type'] ?? 'fwd') === $d ? ' selected' : '' ?>><?= e(__t($d)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="im-field">
                            <label><?= e(__t('color')) ?></label>
                            <input type="text" name="color" value="<?= e($editCar['color'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="im-field" style="margin-top:12px">
                        <label><?= e(__t('description')) ?></label>
                        <textarea name="description" rows="5" placeholder="<?= e(__t('description_placeholder')) ?>"><?= e($editCar['description_' . getCurrentLang()] ?? $editCar['description_en'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="im-wizard-panel" data-panel="3">
                    <h2><?= e(__t('listing_photos')) ?></h2>
                    <p class="im-add-hint"><?= e(__t('photos_hint')) ?></p>
                    <div class="im-photo-dropzone" id="photoDropzone">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p><?= e(__t('drag_photos')) ?></p>
                        <small><?= e(__t('photos_limits')) ?></small>
                        <input type="file" name="photos[]" id="photoInput" accept="image/jpeg,image/png,image/webp" multiple hidden>
                    </div>
                    <div class="im-photo-grid" id="photoGrid" aria-live="polite"></div>
                </div>

                <div class="im-wizard-panel" data-panel="4">
                    <h2><?= e(__t('step_review')) ?></h2>
                    <p class="im-add-hint"><?= e(__t('review_hint')) ?></p>
                    <div id="wizardReviewSummary" class="im-wizard-preview-card">
                        <div class="im-wizard-preview-media"></div>
                        <div class="im-wizard-preview-body">
                            <h3 class="im-wizard-preview-title">—</h3>
                            <div class="im-wizard-preview-price">—</div>
                            <div class="im-wizard-preview-meta">—</div>
                        </div>
                    </div>
                </div>

                <div class="im-wizard-sticky-bar">
                    <div class="im-wizard-nav">
                        <button type="button" class="im-btn" id="wizardPrev" disabled><i class="bi bi-arrow-left"></i> <?= e(__t('wizard_back')) ?></button>
                        <button type="button" class="im-btn im-btn-primary" id="wizardNext"><?= e(__t('wizard_next')) ?> <i class="bi bi-arrow-right"></i></button>
                    </div>
                    <button type="button" class="im-btn" id="previewListingBtn"><i class="bi bi-eye"></i> <?= e(__t('preview_listing')) ?></button>
                    <button type="submit" class="im-btn" formnovalidate onclick="document.getElementById('formAction').value='draft'">
                        <i class="bi bi-file-earmark"></i> <?= e(__t('save_draft')) ?>
                    </button>
                    <button type="submit" class="im-btn im-btn-primary im-btn-lg" onclick="document.getElementById('formAction').value='publish'">
                        <i class="bi bi-send"></i> <?= e($editCar && !$isDraftMode ? __t('save_changes') : __t('publish_listing')) ?>
                    </button>
                    <?php if ($editCar): ?>
                    <a href="<?= url('my-listings.php') ?>" class="im-btn"><?= e(__t('cancel')) ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <aside class="im-wizard-side" aria-label="<?= e(__t('preview_listing')) ?>">
            <div class="im-wizard-preview-card" id="wizardPreviewCard">
                <div class="im-wizard-preview-media"></div>
                <div class="im-wizard-preview-body">
                    <h3 class="im-wizard-preview-title">—</h3>
                    <div class="im-wizard-preview-price">—</div>
                    <div class="im-wizard-preview-meta">—</div>
                </div>
            </div>
        </aside>
    </div>

    <div class="im-preview-modal" id="previewModal" aria-hidden="true" role="dialog" aria-labelledby="previewModalTitle">
        <div class="im-preview-modal-dialog">
            <div class="im-preview-modal-head">
                <h2 id="previewModalTitle"><?= e(__t('preview_listing')) ?></h2>
                <button type="button" class="im-btn" data-close-preview aria-label="<?= e(__t('preview_close')) ?>">&times;</button>
            </div>
            <div class="im-preview-modal-body">
                <div class="im-preview-gallery">
                    <div class="im-preview-gallery-main"><img src="" alt=""></div>
                    <div class="im-preview-thumbs"></div>
                </div>
                <h3 class="im-preview-modal-title">—</h3>
                <div class="im-preview-modal-price im-wizard-preview-price">—</div>
                <div class="im-preview-modal-specs im-wizard-preview-meta"></div>
                <p class="im-preview-modal-desc" style="margin-top:12px;color:var(--im-gray-500);line-height:1.6"></p>
            </div>
        </div>
    </div>

    <script>
    window.BILEN_LISTING_WIZARD = {
        carId: <?= (int)($editCar['id'] ?? 0) ?>,
        csrf: <?= json_encode(generateCsrfToken(), JSON_UNESCAPED_UNICODE) ?>,
        csrfName: <?= json_encode(CSRF_TOKEN_NAME, JSON_UNESCAPED_UNICODE) ?>,
        maxPhotos: 20,
        draftLabel: <?= json_encode(__t('draft_listing'), JSON_UNESCAPED_UNICODE) ?>,
        mainLabel: <?= json_encode(__t('photo_main'), JSON_UNESCAPED_UNICODE) ?>,
        setMainLabel: <?= json_encode(__t('set_main_photo'), JSON_UNESCAPED_UNICODE) ?>,
        removeLabel: <?= json_encode(__t('photo_remove'), JSON_UNESCAPED_UNICODE) ?>,
        noPhotoLabel: <?= json_encode(__t('no_photo_yet'), JSON_UNESCAPED_UNICODE) ?>,
        existingImages: <?= json_encode($existingImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        labels: {
            brand: <?= json_encode(__t('brand'), JSON_UNESCAPED_UNICODE) ?>,
            model: <?= json_encode(__t('model'), JSON_UNESCAPED_UNICODE) ?>,
            year: <?= json_encode(__t('year'), JSON_UNESCAPED_UNICODE) ?>,
            mileage: <?= json_encode(__t('mileage'), JSON_UNESCAPED_UNICODE) ?>,
            city: <?= json_encode(__t('city'), JSON_UNESCAPED_UNICODE) ?>,
            transmission: <?= json_encode(__t('transmission'), JSON_UNESCAPED_UNICODE) ?>,
            fuel: <?= json_encode(__t('engine'), JSON_UNESCAPED_UNICODE) ?>
        }
    };
    </script>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>