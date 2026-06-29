 # DayAge プラグイン
ブログ記事の投稿日時点における、ペットや子供の**年齢・月齢・日数を自動計算してタグとして出力する**baserCMS 4系専用のプラグインです。<br>
対象カテゴリーの判定や、名前と誕生日のデータ管理を設定ファイル（`setting.php`）に集約しているため、プログラムを直接書き換えることなく安全かつスマートに管理できます。

---

## 特徴

* **設定ファイル一括管理**: ターゲットカテゴリーや誕生日データは `Config/setting.php` で綺麗に分離。
* **汎用的な複数対象対応**: ペットだけでなく、子供の成長記録など「名前と誕生日」があれば何でも対応可能。
* **foreachループ出力対応**: ヘルパーからは配列データが返却されるため、View側で自由なHTMLデザイン（`<li>` や `<span>` バッジ等）にループ出力できます。
* **堅牢なフォールバック設計**: 
プラグインが無効化されていても画面が真っ白（エラー）になりません。<br>
対象外のカテゴリー記事や未登録の汎用タグ（例：「散歩」など）が含まれている場合は、自動的に通常のブログタグの「散歩」を出力し、安全にフォールバックします。

## ファイル構成
```text
app/Plugin/DayAge/
├── config.php                 # プラグイン基本情報
├── README.md                  # 本ドキュメント
├── VERSION.txt 
├── Config/
│   └── setting.php            # 設定ファイル
└── View/
    └── Helper/
        └── DayAgeHelper.php   # ヘルパー本体

```

## プラグインの配置と有効化
1. 本リポジトリをダウンロードし、フォルダ名を `DayAge` にします（大文字小文字に注意してください）。
2. baserCMSの `/app/Plugin/` ディレクトリ直下に配置します。
3. baserCMS管理画面の「プラグイン管理」を開き、**「DayAge」** の **「インストール」** ボタンをクリックします。

---

## 設定方法

プラグイン内の `Config/setting.php` を開き、対象とするブログのカテゴリー名と、計算対象にしたい「タグ名（名前）」および「誕生日（YYYY-MM-DD）」を指定します。

```php
<?php
/**
 * DayAge プラグイン用設定ファイル
 */
$config['DayAge'] = [
    // 年齢計算を有効にするブログのカテゴリー名
    // 対象とするカテゴリー名に変更してください。
    'target_category' => '愛犬日記',

    // 対象のタグデータ（ブログタグ名 => 誕生日）
    // 対象とするブログタグ名（例：ペットや子供の名前）と誕生日に変更してください。
    'target_tags' => [
        '小次郎' => '2026-01-01',
        'ムサシ' => '2026-01-15',
    ]
];

```

---

## 画面（View）への組み込み方

ブログのテーマファイル（例: `Blog/default/single.php` など、タグを表示させたい場所）に以下のコードを記述します。<br>
後は、`.post-tags-list` `.tag-item` を適宜cssで整えてください。

```php
<?php
// 1. プラグインの有効化チェックと配列データの取得
$dayAgeTags = [];
if (CakePlugin::loaded('DayAge')) {
    $DayAge = $this->Helpers->load('DayAge.DayAge');
    $dayAgeTags = $DayAge->day_age(
        $this->Blog->getPostDate($post),
        $this->Blog->getCategory($post, ['link' => false]),
        $this->Blog->getTag($post, ['link' => false])
    );
} else {
    // プラグイン無効時は通常のタグを取得して配列化
    $rawTags = strip_tags($this->Blog->getTag($post, ['link' => false]));
    $dayAgeTags = array_filter(array_map('trim', explode(',', $rawTags)));
}
?>

<!-- 2. 配列が存在する場合に each (foreach) でループ出力 -->
<?php if (!empty($dayAgeTags)): ?>
    <ul class="post-tags-list">
        <?php foreach ($dayAgeTags as $tag): ?>
            <li class="tag-item"><?php echo $tag; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
```

---

## 出力イメージ

* **対象カテゴリー（例：愛犬日記）かつ登録済みタグ（小次郎）の場合:**
`小次郎・2歳3ヶ月15日`
* **対象カテゴリーだが、未登録の汎用タグ（例：散歩）の場合:**
`散歩`（そのままフォールバック出力）
* **対象外のカテゴリー（例：お知らせ）の場合:**
すべてのタグが通常のブログタグとしてそのまま出力されます。

---

## ライセンス

本プラグインは **[MIT License](https://opensource.org)** のもとで公開されています。個人・商用問わず、自由に変形・再配布していただいて構いません。
