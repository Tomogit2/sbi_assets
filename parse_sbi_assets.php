<?php

// 読み込むテキストファイル（コピーしたSBI証券のデータ）
$filename = "sbi_assets.txt"; // 事前に手動でコピーしたもの

// ファイルの内容を取得
$text = file_get_contents($filename);

// 正規表現パターン（評価額と評価損益を取得）
$pattern = '/(国内株式\(現物\)|米国株式|投資信託|預り金\(円\)|預り金\(米ドル\))\s+([\d,]+円?)\s+([\+\-–]?[\d,]+円?)/u';

// マッチング結果を格納
preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

// データ格納用
$assets = [];

foreach ($matches as $match) {
    $assets[] = [
        '項目' => trim($match[1]),
        '評価額' => trim($match[2]),
        '評価損益' => trim($match[3])
    ];
}

// JSON形式で保存
$json_file = "sbi_assets.json";
file_put_contents($json_file, json_encode($assets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// CSV形式で保存
$csv_file = "sbi_assets.csv";
$fp = fopen($csv_file, "w");
fputcsv($fp, ["項目", "評価額", "評価損益"]); // ヘッダー
foreach ($assets as $row) {
    fputcsv($fp, $row);
}
fclose($fp);

echo "データの解析が完了しました。\n";
echo "JSON: $json_file\n";
echo "CSV: $csv_file\n";

?>
