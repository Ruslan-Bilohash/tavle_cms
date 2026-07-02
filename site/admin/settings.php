<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    $keys = [
        'site_name', 'site_tagline', 'site_email', 'site_phone', 'usd_rate',
        'currency_code', 'currency_symbol', 'secondary_currency_code', 'secondary_currency_rate',
        'cookie_consent_enabled', 'cookie_consent_text_uk', 'cookie_consent_text_en',
        'cookie_consent_text_ru', 'cookie_consent_text_no',
        'seo_title_uk', 'seo_title_en', 'seo_title_ru', 'seo_title_no',
        'seo_description_uk', 'seo_description_en', 'seo_description_ru', 'seo_description_no',
        'seo_keywords_uk', 'seo_keywords_en', 'seo_keywords_ru', 'seo_keywords_no',
        'google_analytics', 'items_per_page',
    ];

    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            setSetting($key, sanitize($_POST[$key]));
        }
    }

    setSetting('cookie_consent_enabled', isset($_POST['cookie_consent_enabled']) ? '1' : '0');
    setSetting('show_secondary_price', isset($_POST['show_secondary_price']) ? '1' : '0');

    adminFlash('success', __a('saved'));
    redirect('/admin/settings.php?lang=' . getAdminLang());
}

$adminTitle = __a('settings');
require __DIR__ . '/includes/header.php';
?>

<h2 class="mb-4"><?= __a('settings') ?></h2>

<form method="post">
    <?= csrfField() ?>

    <div class="card mb-4">
        <div class="card-header"><?= __a('general') ?></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label"><?= __a('site_name') ?></label><input type="text" name="site_name" class="form-control" value="<?= e(getSetting('site_name')) ?>"></div>
                <div class="col-md-6"><label class="form-label"><?= __a('tagline') ?></label><input type="text" name="site_tagline" class="form-control" value="<?= e(getSetting('site_tagline')) ?>"></div>
                <div class="col-md-4"><label class="form-label"><?= __a('email') ?></label><input type="email" name="site_email" class="form-control" value="<?= e(getSetting('site_email')) ?>"></div>
                <div class="col-md-4"><label class="form-label"><?= __a('phone') ?></label><input type="text" name="site_phone" class="form-control" value="<?= e(getSetting('site_phone')) ?>"></div>
                <div class="col-md-4"><label class="form-label"><?= __a('per_page') ?></label><input type="number" name="items_per_page" class="form-control" value="<?= e(getSetting('items_per_page', '12')) ?>"></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><?= __a('currency') ?></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?= __a('currency_code') ?></label>
                    <select name="currency_code" class="form-select">
                        <?php foreach (['USD','EUR','UAH','NOK','GBP'] as $c): ?>
                        <option value="<?= $c ?>"<?= getSetting('currency_code', 'USD') === $c ? ' selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label"><?= __a('currency_symbol') ?></label><input type="text" name="currency_symbol" class="form-control" value="<?= e(getSetting('currency_symbol', '$')) ?>"></div>
                <div class="col-md-3">
                    <label class="form-label"><?= __a('secondary_currency') ?></label>
                    <select name="secondary_currency_code" class="form-select">
                        <?php foreach (['EUR','USD','UAH','NOK','GBP'] as $c): ?>
                        <option value="<?= $c ?>"<?= getSetting('secondary_currency_code', 'EUR') === $c ? ' selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label"><?= __a('secondary_rate') ?></label><input type="text" name="secondary_currency_rate" class="form-control" value="<?= e(getSetting('secondary_currency_rate', '0.92')) ?>"></div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="show_secondary_price" class="form-check-input" id="show_secondary_price"<?= getSetting('show_secondary_price', '1') === '1' ? ' checked' : '' ?>>
                        <label class="form-check-label" for="show_secondary_price"><?= __a('show_secondary') ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><?= __a('seo') ?></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">SEO Title (UK)</label><input type="text" name="seo_title_uk" class="form-control" value="<?= e(getSetting('seo_title_uk')) ?>"></div>
                <div class="col-md-6"><label class="form-label">SEO Title (EN)</label><input type="text" name="seo_title_en" class="form-control" value="<?= e(getSetting('seo_title_en')) ?>"></div>
                <div class="col-md-6"><label class="form-label">SEO Title (RU)</label><input type="text" name="seo_title_ru" class="form-control" value="<?= e(getSetting('seo_title_ru')) ?>"></div>
                <div class="col-md-6"><label class="form-label">SEO Title (NO)</label><input type="text" name="seo_title_no" class="form-control" value="<?= e(getSetting('seo_title_no')) ?>"></div>
                <div class="col-md-6"><label class="form-label">SEO Description (UK)</label><textarea name="seo_description_uk" class="form-control" rows="2"><?= e(getSetting('seo_description_uk')) ?></textarea></div>
                <div class="col-md-6"><label class="form-label">SEO Description (EN)</label><textarea name="seo_description_en" class="form-control" rows="2"><?= e(getSetting('seo_description_en')) ?></textarea></div>
                <div class="col-md-6"><label class="form-label">SEO Description (RU)</label><textarea name="seo_description_ru" class="form-control" rows="2"><?= e(getSetting('seo_description_ru')) ?></textarea></div>
                <div class="col-md-6"><label class="form-label">SEO Description (NO)</label><textarea name="seo_description_no" class="form-control" rows="2"><?= e(getSetting('seo_description_no')) ?></textarea></div>
                <div class="col-md-6"><label class="form-label">SEO Keywords (UK)</label><input type="text" name="seo_keywords_uk" class="form-control" value="<?= e(getSetting('seo_keywords_uk')) ?>"></div>
                <div class="col-md-6"><label class="form-label">SEO Keywords (EN)</label><input type="text" name="seo_keywords_en" class="form-control" value="<?= e(getSetting('seo_keywords_en')) ?>"></div>
                <div class="col-md-6"><label class="form-label">SEO Keywords (RU)</label><input type="text" name="seo_keywords_ru" class="form-control" value="<?= e(getSetting('seo_keywords_ru')) ?>"></div>
                <div class="col-md-6"><label class="form-label">SEO Keywords (NO)</label><input type="text" name="seo_keywords_no" class="form-control" value="<?= e(getSetting('seo_keywords_no')) ?>"></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><?= __a('cookies') ?></div>
        <div class="card-body">
            <div class="form-check mb-3">
                <input type="checkbox" name="cookie_consent_enabled" class="form-check-input" id="cookie_consent_enabled"<?= getSetting('cookie_consent_enabled', '1') === '1' ? ' checked' : '' ?>>
                <label class="form-check-label" for="cookie_consent_enabled">Cookie banner</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">UK</label><textarea name="cookie_consent_text_uk" class="form-control" rows="2"><?= e(getSetting('cookie_consent_text_uk')) ?></textarea></div>
                <div class="col-md-6"><label class="form-label">EN</label><textarea name="cookie_consent_text_en" class="form-control" rows="2"><?= e(getSetting('cookie_consent_text_en')) ?></textarea></div>
                <div class="col-md-6"><label class="form-label">RU</label><textarea name="cookie_consent_text_ru" class="form-control" rows="2"><?= e(getSetting('cookie_consent_text_ru')) ?></textarea></div>
                <div class="col-md-6"><label class="form-label">NO</label><textarea name="cookie_consent_text_no" class="form-control" rows="2"><?= e(getSetting('cookie_consent_text_no')) ?></textarea></div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><?= __a('save') ?></button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>