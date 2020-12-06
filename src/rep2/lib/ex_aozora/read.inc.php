<?php
/**
 * 独自改造: 青空文庫のファイル内容表示(PC)
 */

// {{{ ヘッダ

echo <<<EOH
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$title}</title>
    <base target="{$_conf['expack.rss.target_frame']}">
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=read&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <style type="text/css">
        hr.newpage {
          color: #0000FF;
          background-color: #0000FF;
          /* height: 1px; */
          /* border: 1px; */
          border-style: dashed;
          }
    </style>
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript">
    //<![CDATA[
    function setWinTitle(){
        if (top != self) {top.document.title=self.document.title;}
    }
    //]]>
    </script>
</head>
<body onload="setWinTitle()">
<h1>{$title}</h1>
<hr>
{$_info_msg_ht}
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

// 行数のカウンタ(表示には実際には関係ない行でもカウント)
$a = 0;
$desc = false; // 記号説明のとこのフラグ

if ($shiorino > 0) {
    echo 'モバイルモードで栞が登録されています(<a href="#' . $shiorino . '">栞にジャンプ</a>)<hr>';
}

// 内容表示
foreach($rawdata as $line) {

    // 表示には実際には関係ない行でもカウント(行番号は1からカウントする)
    $a++;
    
    // ルビ処理("｜"で区切る版)
    // preg_match()だとマルチバイトの処理がうまくできないみたい
    // else if(preg_match('/｜([^｜]+)[《]([^》]+)[》]/', $line, $match)) {
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
                if(($result = mb_ereg_replace($pattern, '<ruby><rb>' . $match[1] . '</rb><rp>(</rp><rt>' . str_repeat("・", mb_strlen($match[1])) . '</rt><rp>)</rp></ruby>',  $str1)) != false) {
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
    $showruby = true; // ルビを表示する場合はtrue。ただしIEだとレイアウトが崩れる。
    $nametag = '<a name="' . $a . '"></a>';
    
    if($ruby_replaced == true && $showruby == true){
        echo $nametag . $str1 . '<br>';
    }
    // 記号の説明部分は無視するようにする
    else if ( preg_match('/^[-]{40,}\s*$/', $line, $match) ) {
        if($desc == false) {
            $desc = true;
        }else {
            $desc = false;
        }
    }
    else if($desc == true) {
    }
    // 画像へのリンクがあるかチェック
    // 画像へのリンクであれば、ファイルが実際に存在するかチェックし、リンクを生成する。
    // 画像へのリンクでなければそのまま表示する。
    else if( preg_match('/^<img\s*src\s*=\s*"?\/?(.+(jpg|jpeg|gif|png))"?>\s*$/i', $line, $match) ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            echo '<br>' . $nametag . '<a href="' . $url . '&zoom=1"><img src="' . $url . '"></a><br>';
        }else {
            echo $nametag . htmlspecialchars('<' . $match[1] . '> (参照失敗)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // 画像へのリンクがあるかチェック2［＃挿絵（xxxxx.jpg）入る］
    else if( mb_ereg('^\s*［＃挿絵（"?\/?(.+(?:jpg|jpeg|gif|png))"?）入る］\s*$', $line, $match) > 0 ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            echo '<br>' . $nametag . '<a href="' . $url . '&zoom=1"><img src="' . $url . '"></a><br>';
        }else {
            echo $nametag . htmlspecialchars('<' . $match[1] . '> (参照失敗)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // 改ページのチェック
    else if(mb_ereg('^\s*(?:［＃改頁］|［＃改ページ］|［＃改段］|［＃改丁］)\s*$', $line, $match) > 0 ) {
        echo '<hr class="newpage" />';
    }
    else {
        echo $nametag . $line . '<br>';
    }
}

// }}}
// {{{ フッタ

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
