<?php
/**
 * 独自改造: 青空文庫のファイル一覧(PC)
 * URL構成: read_aozora.php?book=[フォルダ名\ファイル名]のURLエンコード
 */

// {{{ 表示用変数

if ($atom) {
    $atom_q = '&amp;atom=1';
    $atom_ht = '<input type="hidden" name="atom" value="1">';
    $atom_chk = ' checked';
} else {
    $atom_q = '';
    $atom_ht = '';
    $atom_chk = '';
}
if ($mtime) {
    $mtime_q = '&amp;mt=' . $mtime;
} else {
    $mtime_q = '';
}

// }}}
// {{{ ツールバー

if ($aozora_scanDirSuccess) {

    // ツールバー共通部品
    $rss_toolbar_ht = <<<EOP
<span class="itatitle"><a class="aitatitle" title="青空文庫 text"{$onmouse_popup}><b>青空文庫 text</b></a></span></td>
<td class="toolbar-anchor"><span class="time">{$reloaded_time}</span>
EOP;

}

// }}}
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
    <link rel="stylesheet" type="text/css" href="css.php?css=subject&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    {$popup_header}
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript">
    //<![CDATA[
    function setWinTitle(){
        if (top != self) {top.document.title=self.document.title;}
    }
    //]]>
    </script>
</head>
<body onload="setWinTitle();">
{$_info_msg_ht}

EOH;

// 青空文庫データディレクトリのスキャンに失敗したとき
if (!$aozora_scanDirSuccess) {
    echo "<h1>データディレクトリのスキャンに失敗しました。</h1></body></html>";
    exit;
}

echo <<<EOTB
<table id="sbtoolbar1" class="toolbar" cellspacing="0"><tbody><tr>
<td class="toolbar-title">{$rss_toolbar_ht}<a class="toolanchor" href="#sbtoolbar2" target="_self">▼</a></td>
</tr></tbody></table>
<table class="threadlist" cellspacing="0">

EOTB;

// }}}
// {{{ 見出し

$description_column_ht = '<th class="tl">作品名</th>';

echo <<<EOP
<thead><tr class="tableheader">
<th class="tu"></th>{$description_column_ht}</tr></thead>
<tbody>\n
EOP;


// ================================================================
// フォルダおよびファイル一覧の表示
// フォルダ配下にtxtファイルが存在しないフォルダは表示しない
// ================================================================

reset($items);
$roopcounter1 = 0;
while($roopcounter1 < count($items)){
    // $itemsのデータ構造は
    //   $items[ディレクトリ名(stirng型)][←に対応するファイル一覧(array型)]
    // という形

    // インデント
    $description_ht = "<td class=\"tu\"></td>";
    
    // フォルダ配下のファイル一覧配列を取り出し
    $roopcounter2 = 0;
    $jjj = $items[$roopcounter1][1];

    // フォルダ名表示
    if(count($jjj) > 0){
        $r = 'r1';
        echo <<<EOP
        <tr class="{$r}">{$description_ht}<td class="tl">・ {$items[$roopcounter1][0]}</td></tr>\n
EOP;

        while($roopcounter2 < count($jjj)){
            
            // ファイル名表示
            $r = 'r2';
            $param = $items[$roopcounter1][0] . DIRECTORY_SEPARATOR . $jjj[$roopcounter2];
            $param = urlencode($param);
            $link_orig = 'read_aozora.php?book=' . $param;
            echo <<<EOP
            <tr class="{$r}">{$description_ht}<td class="tl">　　└─ <a id="tt{$i}" class="thre_title" href="{$link_orig}">{$jjj[$roopcounter2]}</a></td></tr>\n
EOP;

            $roopcounter2=$roopcounter2+1;
        }
    }
    $roopcounter1=$roopcounter1+1;
}
// ================================================================
// ここまで
// ================================================================


// }}}
// {{{ フッタ

echo <<<EOF
</tbody>
</table>
<table id="sbtoolbar2" class="toolbar" cellspacing="0"><tbody><tr>
<td class="toolbar-title">{$rss_toolbar_ht}<a class="toolanchor" href="#sbtoolbar1" target="_self">▲</a></td>
</tr></tbody></table>
</body>
</html>
EOF;

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
