<?php
/**
 * DayAgeHelper
 * 
 * ブログ記事の投稿日時点における、対象（ペットや子供など）の年齢・月齢・日数を自動計算して出力するヘルパー
 * baserCMS 4系専用プラグイン
 *
 * @package    DayAge
 * @author     HATTA
 * @license    MIT License
 * @link       https://hattantoco.com
 */

class DayAgeHelper extends AppHelper {
    public function day_age($postDate, $categoryName, $tags = []) {
        // 設定ファイル（setting.php）から設定値をダイレクトに取得
        $targetCategory = Configure::read('DayAge.target_category');
        $targetTags = Configure::read('DayAge.target_tags');

        // 1文字ずつHTMLエスケープするためのクロージャ（安全な出力のため）
        $escape = function($str) {
            return h($str);
        };

        // 1. カテゴリー判定
        // カテゴリー名、対象のタグデータ、タグのいずれかが空、またはカテゴリー名が一致しない場合は、元のタグを配列形式にしてそのまま出力
        if (empty($targetCategory) || $categoryName !== $targetCategory || empty($tags) || empty($targetTags)) {
            if (is_string($tags)) {
                $tags = strip_tags($tags);
                $tagNames = explode(',', $tags);
            } else {
                $tagNames = Hash::extract($tags, '{n}.name');
            }
            // 前後の空白を削除して、HTMLエスケープした配列を返す
            return array_map($escape, array_map('trim', $tagNames));
        }

        // 2. タグの処理（名前 => 誕生日）
        $post_date = new DateTime($postDate);
        $outputs = [];

        // $this->Blog->getTag() の結果（HTML文字列）が渡ってきた場合でも動くように考慮
        if (is_string($tags)) {
            // HTMLタグを除去して配列化
            $tags = strip_tags($tags);
            $tagNames = explode(',', $tags); // 念のためカンマ区切りなどで対応
        } else {
            // 配列データで届いた場合
            $tagNames = Hash::extract($tags, '{n}.name');
        }

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName); // 前後の空白削除

            if (isset($targetTags[$tagName])) {
                $birthday = new DateTime($targetTags[$tagName]);
                if ($post_date < $birthday) continue;

                $interval = $birthday->diff($post_date);
                
                $ageStr = $tagName . "・";
                if ($interval->y >= 1) $ageStr .= $interval->y . "歳";
                if ($interval->m >= 1) $ageStr .= $interval->m . "ヶ月";
                // 0歳0ヶ月の場合は必ず「0日」と出るように、または1日以上の場合
                if ($interval->d >= 1 || ($interval->y == 0 && $interval->m == 0)) {
                    $ageStr .= $interval->d . "日";
                }
                
                // View側でeach出力（HTML化）するため、ここではエスケープして配列に格納
                $outputs[] = h($ageStr);
            } else {
                // 登録されていない汎用タグだった場合、そのままタグ名を出す
                $outputs[] = h($tagName);
            }
        }

        // 変更点：文字列ではなく、配列のまま返却する
        return $outputs;
    }
}
