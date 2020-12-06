<?php
/**
 * 独自改造: 青空文庫のファイル一覧(PC)
 * URL構成: read_aozora.php?book=[フォルダ名\ファイル名]のURLエンコード
 */

// {{{ ヘッダ

echo <<<EOH
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<title>青空文庫(text)</title>
</head>
<body{$_conf['k_colors']}>
{$_info_msg_ht}
<p><b>{$title}</b></p>
<hr>\n
EOH;

// 青空文庫データディレクトリのスキャンに失敗したとき
if (!$aozora_scanDirSuccess) {
    echo 'データディレクトリのスキャンに失敗しました。</body></html>';
    exit;
}


// }}}
// {{{ 見出し

reset($items);
$roopcounter1 = 0;
while($roopcounter1 < count($items)){
    // $itemsのデータ構造は
    //   $items[ディレクトリ名(stirng型)][←に対応するファイル一覧(array型)]
    // という形
    
    // フォルダ配下のファイル一覧配列を取り出し
    $roopcounter2 = 0;
    $jjj = $items[$roopcounter1][1];

    // フォルダ名表示
    if(count($jjj) > 0){
        $r = 'r1';
        echo '・' . $items[$roopcounter1][0] . '<br>';

        while($roopcounter2 < count($jjj)){
            
            // ファイル名表示
            $param = $items[$roopcounter1][0] . DIRECTORY_SEPARATOR . $jjj[$roopcounter2];
            $param = urlencode($param);
            $link_orig = 'read_aozora.php?book=' . $param . $_conf['k_at_a'];
            echo '　+<a href="' . $link_orig . '">' . $jjj[$roopcounter2] . '</a><br>';

            $roopcounter2=$roopcounter2+1;
        }
    }
    $roopcounter1=$roopcounter1+1;
}

// }}}
// {{{ フッタ

echo <<<EOF
<hr>
<div class="center">
{$_conf['k_to_index_ht']}
</div>
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
