<?php

// 読み込むテキストファイル（SBI証券ページをコピーしたもの）
$filename = "sbi_assets.txt"; // 手動でコピーしたテキスト

// ファイルの内容を取得
$text = file_get_contents($filename);

// **取得したい項目**
$categories = [
    "国内株式\(現物\)" => "国内株式",
    "米国株式" => "米国株式",
    "投資信託" => "投資信託",
    "預り金\(円\)" => "預り金(円)",
    "預り金\(米ドル\)" => "預り金(米ドル)",
    "合計" => "SBI証券My資産トップ (預り金含む)の評価額と損益"
];

// **初期データセット**
$assets = [];
foreach ($categories as $key => $label) {
    $assets[$label] = [
        '項目' => $label,
        '評価額' => "",
        '評価損益' => ""
    ];
}

// **評価額と評価損益を取得（改行に対応）**
$pattern = '/(' . implode('|', array_keys($categories)) . ')\s+([\d,]+円)(?:\s*\n\s*([\+\-–\d,]+円))?/u';
preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

$previous_value = ""; // 直前の評価額を保存
$previous_profit = ""; // 直前の評価損益を保存

foreach ($matches as $match) {
    $key = $categories[$match[1]] ?? $match[1];
    if (isset($assets[$key])) {
        $assets[$key]['評価額'] = trim($match[2]);
        if (!empty($match[3])) {
            $assets[$key]['評価損益'] = trim($match[3]);
        }
    }

    // 「国内株式」の評価額が取得できない場合、米国株式の前の数字を使用
    if ($key === "米国株式" && empty($assets["国内株式"]['評価額'])) {
        $assets["国内株式"]['評価額'] = $previous_value;
        $assets["国内株式"]['評価損益'] = $previous_profit; // ここで評価損益も取得
    }

    // 直前の評価額・評価損益を保存
    $previous_value = trim($match[2]);
    $previous_profit = trim($match[3] ?? "");
}

// **出力順**
$order = [
    "国内株式",
    "米国株式",
    "投資信託",
    "預り金(円)",
    "預り金(米ドル)",
    "SBI証券My資産トップ (預り金含む)の評価額と損益"
];

// **CSVとJSONに保存**
$json_file = "sbi_assets.json";
file_put_contents($json_file, json_encode(array_values($assets), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$csv_file = "sbi_assets.csv";
$fp = fopen($csv_file, "w");
fputcsv($fp, ["項目", "評価額", "評価損益"]); // ヘッダー
foreach ($order as $key) {
    if (isset($assets[$key])) {
        fputcsv($fp, $assets[$key]);
    }
}
fclose($fp);

echo "データの解析が完了しました(^^♪\n";
echo "JSON: $json_file\n";
echo "CSV: $csv_file\n";

?>
