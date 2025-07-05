<?php
// 固定時給
$base_hourly = 1200;
$work_hours = 1;

// メニュー単価（nullは自由入力）
$menus = [
    'キャスドリ' => 1800,
    'カラオケ' => 600,
    'オムライス' => 2200,
    '自分で作ったフード' => null,
    'チェキ' => 1200,
    '宿題チェキ' => 2400,
];

// 変数初期化
$results = [];
$total_sales = 0;
$back_rate = 0;
$total_back = 0;
$ratio = 0;

// POST受信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_hours = floatval($_POST['work_hours'] ?? $work_hours);

    $sales_amounts = [];
    foreach ($menus as $menu => $price) {
        if ($price === null) {
            $custom_price = floatval($_POST['custom_price'][$menu] ?? 0);
            $count = intval($_POST['counts'][$menu] ?? 0);
            $sale = $custom_price * $count;
        } else {
            $count = intval($_POST['counts'][$menu] ?? 0);
            $sale = $price * $count;
        }
        $sales_amounts[$menu] = $sale;
    }
    $total_sales = array_sum($sales_amounts);

    if ($base_hourly > 0 && $work_hours > 0) {
        $ratio = $total_sales / ($base_hourly * $work_hours);

        if ($ratio <= 1.0) {
            $back_rate = 0.05;
        } elseif ($ratio <= 1.5) {
            $back_rate = 0.15;
        } elseif ($ratio <= 2.5) {
            $back_rate = 0.20;
        } elseif ($ratio <= 4.0) {
            $back_rate = 0.30;
        } elseif ($ratio <= 10.0) {
            $back_rate = 0.35;
        } elseif ($ratio <= 20.0) {
            $back_rate = 0.50;
        } else {
            $back_rate = 0.60;
        }

        foreach ($sales_amounts as $menu => $sale) {
            $results[$menu] = $sale * $back_rate;
        }
        $total_back = $total_sales * $back_rate;
    } else {
        $ratio = 0;
        $back_rate = 0;
        $total_back = 0;
        $results = [];
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <title>CashMaid バック率計算</title>
    <link rel="stylesheet" href="css/style.css" />
</head>

<body>
    <h1>CashMaid バック率計算</h1>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <section id="resultSection" class="result-section">
            <h2>🎀 計算結果 🎀</h2>
            <p class="back-rate">バック率：<?= number_format($back_rate * 100, 2) ?>%</p>
            <p class="back-rate">合計売上：<?= number_format($total_sales) ?> 円</p>

            <h3>🧁 メニュー別バック額</h3>
            <ul>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $menu => $back): ?>
                        <li><?= htmlspecialchars($menu) ?>：<?= number_format($back) ?> 円</li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>データなし</li>
                <?php endif; ?>
            </ul>

            <p class="back-amount">💰 合計バック額：<?= number_format($total_back) ?> 円</p>
        </section>
    <?php endif; ?>

    <form id="calcForm" method="post" action="">
        <p>時給: <?= number_format($base_hourly) ?> 円（固定）</p>

        <label>勤務時間（月合計など）:
            <input type="number" name="work_hours" value="<?= htmlspecialchars($work_hours) ?>" step="0.25" min="0.25" required>
        </label>

        <h2>売上メニュー入力</h2>

        <?php foreach ($menus as $menu => $price): ?>
            <label>
                <?= htmlspecialchars($menu) ?>:
                <?php if ($price === null): ?>
                    単価 <input type="number" name="custom_price[<?= htmlspecialchars($menu) ?>]" value="<?= htmlspecialchars($_POST['custom_price'][$menu] ?? '') ?>"
                        step="0.01" min="0" placeholder="単価を入力">
                    個数 <input type="number" name="counts[<?= htmlspecialchars($menu) ?>]" value="<?= htmlspecialchars($_POST['counts'][$menu] ?? 0) ?>" step="1" min="0">
                <?php else: ?>
                    単価 <?= number_format($price) ?> 円
                    個数 <input type="number" name="counts[<?= htmlspecialchars($menu) ?>]" value="<?= htmlspecialchars($_POST['counts'][$menu] ?? 0) ?>" step="1" min="0">
                <?php endif; ?>
            </label>
        <?php endforeach; ?>

        <div class="button-group">
            <button type="submit">✨ バック率計算 ✨</button>
            <button type="button" id="resetBtn" class="reset-btn">🔄 入力リセット</button>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>