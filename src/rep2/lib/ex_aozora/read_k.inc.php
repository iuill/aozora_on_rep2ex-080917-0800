<?php
/**
 * 青空文庫用改造 - 青空文庫ファイルを表示
 */

// {{{ ヘッダ

$ch_title = htmlspecialchars($channel['title'], ENT_QUOTES, 'Shift_JIS', false);

echo <<<EOH
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<style type="text/css">
  strong { color: blueviolet; }
  hr.newpage {
    color: #0000FF;
    background-color: #0000FF;
    /* height: 1px; */
    /* border: 1px; */
    border-style: dashed;
    }
</style>
<title>{$title}</title>
</head>
<body{$_conf['k_colors']}>
EOH;

// 青空文庫の読み取りに失敗したとき
if (count($aozora_errors) > 0) {
    echo '<p><b>青空文庫 Error</b></p>';
    echo '<ul><li>';
    echo implode('</li><li>', array_map('htmlspecialchars', $aozora_errors));
    echo '</li></ul>';
    
    echo '</body></html>';
    exit;
}

// 表示するページの配列
$pages = array();

// ページ番号
$pagenum = 0;


$pagechars = 0; // ページ内の文字数(リミット到達時にページ追加判断用)
$page = array(); // ページごとの文字列配列
$pageadded = false; // ページを追加したかのフラグ
$desc = false; // 記号説明のとこのフラグ
$a = 0;

define('MAX_LEN', 4000); // 1画面あたりの最大文字数

// 内容を解析して、文字数毎にページ分割
foreach($rawdata as $line) {

    // 表示には実際には関係ない行でもカウント(行番号は1からカウントする)
    $a++;
    
    $str1 = '';
    $ruby_replaced = false;
    if($desc == false && strcmp($line, '') != 0) {
        // ルビ処理("｜"で区切るものは、"｜"から"《"までの文字にルビを付加)
        // preg_match()だとマルチバイトの処理がうまくできないみたい
        // else if(preg_match('/｜([^｜]+)[《]([^》]+)[》]/', $line, $match)) {
        $result = '';
        if(($result = mb_ereg_replace('｜([^｜]+)[《]([^》]+)[》]', '<ruby><rb>\1</rb><rp>(</rp><rt>\2</rt><rp>)</rp>', $line)) != false) {
            $str1 = $result;
        }
        // ルビ処理("｜"で区切らないものは直前の1文字だけルビを付加)
        if(($result = mb_ereg_replace('([^《》])[《]([^》]+)[》]', '<ruby><rb>\1</rb><rp>(</rp><rt>\2</rt><rp>)</rp>', $str1)) != false) {
            $str1 = $result;
        }
        
        // 傍点・傍線の処理(うまい正規表現がわからないので二段階に分けて処理)
        // ※ 置換回数制限なしだとうまく動かないので、行あたりの最大置換回数の制限あり
        // ※ 傍点処理中に傍線処理が入るようなケースは無視
        $replaced_max = 30;
        // まずは傍点
        for($repleced_num = 0; $repleced_num < $replaced_max; $repleced_num++) {
            if(mb_ereg('［＃「([^」]+)」に傍点］', $str1, $match) > 0) {
            
                $pattern = $match[1] . '［＃「' . $match[1] . '」に傍点］';
                
                // auだと <strong> タグは無視されるみたいなので、スタイルシートで色変更も併用
                if(($result = mb_ereg_replace($pattern, '<strong>' . $match[1] . '</strong>',  $str1)) != false) {
                    $str1 = $result;
                }
            }
        }
        // お次は傍線
        for($repleced_num = 0; $repleced_num < $replaced_max; $repleced_num++) {
            if(mb_ereg('［＃「([^」]+)」に傍線］', $str1, $match) > 0) {
            
                $pattern = $match[1] . '［＃「' . $match[1] . '」に傍線］';
                
                if(($result = mb_ereg_replace($pattern, '<u>' . $match[1] . '</u>',  $str1)) != false) {
                    $str1 = $result;
                }
            }
        }
        
        if(strcmp($str1, $line) != 0){
            $ruby_replaced = true;
        }
    }
    
    $imgpath = '';
    $nametag = '';
    $tmp = '';
    // 記号の説明のとこは文字数をカウントしない
    // また、本文に関係ある部分はカウント
    $linelen = mb_strlen($line);
    
    if($ruby_replaced == true){
        $tmp = $nametag . $str1 . '<br>';
    }
    // 記号の説明部分は無視するようにする
    else if ( preg_match('/^[-]{40,}\s*$/', $line, $match) ) {
        if($desc == false) {
            $desc = true;
            // 記号の説明のとこはカウントしない
            $linelen = 0;
        }else {
            $desc = false;
            $tmp = '';
            // この段階ではまだ"-----------"ってやつなのでカウントしない
            $linelen = 0;
        }
    }
    else if($desc == true) {
        $tmp = '';
        $linelen = 0;
    }
    // 画像へのリンクがあるかチェック
    // 画像へのリンクであれば、ファイルが実際に存在するかチェックし、リンクを生成する。
    // 画像へのリンクでなければそのまま表示する。
    else if( preg_match('/^\s*<img\s*src\s*=\s*"?\/?(.+(?:jpg|jpeg|gif|png))"?>\s*$/i', $line, $match) ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            $tmp = $nametag . '<a href="' . $url . '">' . htmlspecialchars('<' . $match[1] . '>', ENT_QUOTES, 'Shift_JIS', false) . '</a><br>';
        }else {
            $tmp = $nametag . htmlspecialchars('<' . $match[1] . '> (参照失敗)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // 画像へのリンクがあるかチェック2［＃挿絵（xxxxx.jpg）入る］
    else if( mb_ereg('^\s*［＃挿絵（"?\/?(.+(?:jpg|jpeg|gif|png))"?）入る］\s*$', $line, $match) > 0 ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            $tmp = $nametag . '<a href="' . $url . '">' . htmlspecialchars('<' . $match[1] . '>', ENT_QUOTES, 'Shift_JIS', false) . '</a><br>';
        }else {
            $tmp = $nametag . htmlspecialchars('<' . $match[1] . '> (参照失敗)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // 改ページのチェック
    else if(mb_ereg('^\s*(?:［＃改頁］|［＃改ページ］|［＃改段］|［＃改丁］)\s*$', $line, $match) > 0 ) {
        $tmp = '<hr class="newpage">';
    }
    else {
        $tmp = $nametag . $line . '<br>';
    }

    // 行を追加。
    // 必要があればページを新規追加
    if($pagechars + $linelen > MAX_LEN) {
        $pages[] = $page;
        $pageadded = true;
        
        $page = array();
        $page[] = $tmp;
        $pagechars = $linelen;
    }else {
        $page[] = $tmp;
        $pagechars += $linelen;
        $pageadded = false;

    }
    
    // GETパラメータ内の $l=xx と現在の行が一致したとき、
    // この時点でのページを表示ページとする
    if(strcmp($a, $l) == 0) {
        if($pageadded == true) {
            $pagenum = count($pages);
        }else {
            $pagenum = count($pages);
        }
    }
}

// 最後の行を処理したときに文字数の関係でページを追加してない場合、
// 未処理のページを追加する。
if(count($page) > 0) {
    $pages[] = $page;
    $pageadded = true;
}

// $lが0未満のときは最初のページを指定したとみなす
// テキスト内の行数より大きい値を指定されたときは最後のページを指定されたものとみなす
if ($l < 0 || strcmp($l, '') == 0) {
    $pagenum = 0;
}else if (count($rawdata) < $l) {
    $pagenum = count($pages) - 1;
}

$prev = 0; // 前ページの最初の行番号($l=xxの値)
if (0 < $pagenum && $pagenum < count($pages)) {
    // $prev算出
    $a = 0;
    while($a < $pagenum-1) {
        $prev += count($pages[$a]);
        $a++;
    }
    
    if($prev != 0) {
        $prev++;
    }
    if($pagenum == 1)
    {
        $prev = 0;
    }
}
$next = 0; // 次ページの最初の行番号($l=xxの値)
if(0 <= $pagenum && $pagenum < count($pages)-1 ) {
    // $next算出
    if($pagenum == 0) {
            $next = count($pages[0])+1;
    }
    else {
        $a = 0;
        while($a <= $pagenum) {
            $next += count($pages[$a]);
            $a++;
        }
        
        $next++;
    }
}

$prevstr = '';
if(0 < $pagenum ) {
    $prevstr = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $prev . '"' . $_conf['k_accesskey_at']['prev'] . '>' . $_conf['k_accesskey_st']['prev']. '前</a>';
}
$nextstr = '';
if($pagenum < count($pages) - 1) {
    $nextstr = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $next . '"' . $_conf['k_accesskey_at']['next'] . '>' . $_conf['k_accesskey_st']['next']. '次</a>';
}


// --------------------------
//    指定のページ内容表示
// --------------------------

// 栞が登録されている旨表示
if (strcmp($s_reg, 1) == 0){
    $shiori_str_h = '栞を登録しました。 ';
}
else if (strcmp($s_del, 1) == 0){
    $shiori_str_h_del = '栞を削除しました。';
}
else if ($shiorino > 0) {
    $shiori_str_h = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $shiorino . '">栞表示</a> ';
    $shiori_str_del = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $l . '&s_del=1"' . $_conf['k_accesskey_at']['del_shiori'] . '>' . $_conf['k_accesskey_st']['del_shiori'] . '栞削除</a> ';
}

// 栞登録用リンク
{
    $shiori_str_f = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $l . '&s_reg=1"' . $_conf['k_accesskey_at']['reg_shiori'] . '>' . $_conf['k_accesskey_st']['reg_shiori'] . '栞登録</a> ';
}

// 現在のページ番号と最大ページ数表示
$cur_page_str = '[' . $pagenum . '/' . (count($pages)-1) . ']';

echo <<<EOH
{$_info_msg_ht}
<a name="above"></a>
<h1>{$title}</h1>
{$shiori_str_h}
{$shiori_str_h_del}
{$cur_page_str}
<a href="#bottom"{$_conf['k_accesskey_at']['bottom']}>{$_conf['k_accesskey_st']['bottom']}▼</a>
<hr>
EOH;

foreach ($pages[$pagenum] as $line) {
   echo $line;
}

// }}}
// {{{ フッタ
echo <<<EOH
<br>
<hr>
<a name="bottom"></a>
<div class="center">
{$shiori_str_f}
{$prevstr}
{$cur_page_str}
{$nextstr}
{$shiori_str_del}
<a href="#above"{$_conf['k_accesskey_at']['above']}>{$_conf['k_accesskey_st']['above']}▲</a>
<hr>
<a href="subject_aozora.php"{$_conf['k_accesskey_at'][9]}>{$_conf['k_accesskey_st'][9]}青空文庫</a>
{$_conf['k_to_index_ht']}
</div>
EOH;

echo '</body></html>';


// }}}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
