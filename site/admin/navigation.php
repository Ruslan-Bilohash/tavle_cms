<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    foreach (['top', 'header'] as $menu) {
        $items = [];
        $labels = $_POST["{$menu}_label"] ?? [];
        $urls = $_POST["{$menu}_url"] ?? [];
        $count = count($labels['uk'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            $uk = trim($labels['uk'][$i] ?? '');
            if ($uk === '') continue;
            $items[] = [
                'url' => trim($urls[$i] ?? ''),
                'label' => [
                    'uk' => $uk,
                    'en' => trim($labels['en'][$i] ?? $uk),
                    'ru' => trim($labels['ru'][$i] ?? $uk),
                    'no' => trim($labels['no'][$i] ?? $uk),
                ],
            ];
        }
        setSetting('nav_' . $menu, json_encode($items, JSON_UNESCAPED_UNICODE));
    }
    adminFlash('success', __a('saved'));
    redirect('/admin/navigation.php?lang=' . getAdminLang());
}

$adminTitle = __a('navigation');
$navTop = getNavMenu('top');
$navHeader = getNavMenu('header');

require __DIR__ . '/includes/header.php';
?>

<h2 class="mb-4"><?= __a('navigation') ?></h2>
<p class="text-muted small mb-4"><?= __a('menu_hint') ?></p>

<form method="post">
    <?= csrfField() ?>

    <?php foreach (['top' => 'top_menu', 'header' => 'header_menu'] as $key => $labelKey): ?>
    <?php $items = $key === 'top' ? $navTop : $navHeader; ?>
    <div class="card mb-4">
        <div class="card-header"><?= __a($labelKey) ?></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle" id="table-<?= $key ?>">
                    <thead>
                        <tr>
                            <th>UK</th><th>EN</th><th>RU</th><th>NO</th><th><?= __a('menu_url') ?></th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <?php foreach (['uk','en','ru','no'] as $l): ?>
                            <td><input type="text" name="<?= $key ?>_label[<?= $l ?>][]" class="form-control form-control-sm" value="<?= e($item['label'][$l] ?? '') ?>"></td>
                            <?php endforeach; ?>
                            <td><input type="text" name="<?= $key ?>_url[]" class="form-control form-control-sm" value="<?= e($item['url'] ?? '') ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addNavRow('<?= $key ?>')"><?= __a('add_item') ?></button>
        </div>
    </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary"><?= __a('save') ?></button>
</form>

<script>
function addNavRow(key) {
    var tbody = document.querySelector('#table-' + key + ' tbody');
    var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="' + key + '_label[uk][]" class="form-control form-control-sm"></td>'
        + '<td><input type="text" name="' + key + '_label[en][]" class="form-control form-control-sm"></td>'
        + '<td><input type="text" name="' + key + '_label[ru][]" class="form-control form-control-sm"></td>'
        + '<td><input type="text" name="' + key + '_label[no][]" class="form-control form-control-sm"></td>'
        + '<td><input type="text" name="' + key + '_url[]" class="form-control form-control-sm"></td>'
        + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()">×</button></td>';
    tbody.appendChild(tr);
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>