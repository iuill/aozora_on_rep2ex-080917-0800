<?php
/**
 * 独自改造: 青空文庫のファイル一覧(PC)
 */

// {{{ p2基本設定読み込み&認証
require_once './conf/conf.inc.php';

$_login->authorize();

// }}}

if ($_conf['view_forced_by_query']) {
    output_add_rewrite_var('b', $_conf['b']);
}

//============================================================
// 変数の初期化
//============================================================
$aozora_errors = array();
$book = $_REQUEST['book'];
$l = $_REQUEST['l'];
$s_reg = $_REQUEST['s_reg']; // 栞登録用フラグ
$s_del = $_REQUEST['s_del']; // 栞削除用フラグ (本来これだけでもPOSTに変更すべきか？)

// $lのチェック
// 適切っぽい数字だったら、"#数字"をURLに付加してリダイレクト
if ($l != '') {
    if (is_numeric($l) == false && $l <= 0) {
        $aozora_errors[] = '無効な位置を指定されました。';
    }
    else {
        if($_conf['ktai'] == false) {
            // GETパラメータの部分から &l=xx を除去
            $getstr = preg_replace('/.*([?].*)(&l=\d+)(&.*|&amp;.*|.*)/i', '\1\3', $_SERVER['REQUEST_URI']);
            // &l=xxの数字の部分取り出し
            $num = preg_replace('/.*&l=(\d+)(&.*|&amp;.*|.*)/i', '\1', $_SERVER['REQUEST_URI']);
            
            // URLから、 &l=数字 を除去し、URLの最後尾に #数字 を付加してリダイレクト
            // デバッグ等で header() の前で echo ''; 等を行わないようにすること！
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $_SERVER["PHP_SELF"] . $getstr . '#' . $num);
            exit();
        }
    }
}

//===================================================================
// 青空文庫ファイル読み込み
//===================================================================

$book_splited = explode("\\", $book);

// GETで渡されたbookパラメータに変なのが混じってないか簡易チェック
if($book == '' && count($book_splited) != 2)
{
    $aozora_errors[] = '変なGETパラメータ送られてますよ。';
}

$dirname = $book_splited[0];
$fname = $book_splited[1];

// ファイル読み込み
$localpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $book;

if(file_exists($localpath)) {
    require_once P2_LIB_DIR . '/FileCtl.php';
    $rawdata = FileCtl::file_read_lines($localpath);
    // print_r($rawdata);
    if(count($rawdata) <= 0) {
        $aozora_errors[] = 'ファイルの内容が空です。';
    }
}
else {
    $aozora_errors[] = '青空文庫ファイルが存在しません。';
}


//===================================================================
// 栞登録処理 or 栞ファイル読み込み
//===================================================================
if(count($aozora_errors) == 0) {
    // 栞ファイルの拡張子は ".bmk" とする
    $localpath_shiori = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . basename($fname, '.txt') . '.bmk';
        
    // 栞登録のチェック
    if (strcmp($s_reg, 1) == 0) {
        $wrtstr = 'l='; // 書き込む文字列(l=xx;な形式)
        if(strcmp($l, '') == 0 || $l < 0){
            $wrtstr = $wrtstr . '0;';
        }
        else{
            $wrtstr = $wrtstr . $l . ';';
        }
        
        require_once P2_LIB_DIR . '/FileCtl.php';
        if(FileCtl::file_write_contents($localpath_shiori, $wrtstr) == false) {
            $aozora_errors[] = '栞登録に失敗しました。';
        }
    }
    // 栞削除
    else if (strcmp($s_del, 1) == 0) {
        require_once P2_LIB_DIR . '/FileCtl.php';
        if(file_exists($localpath_shiori)) {
            if (unlink ($localpath_shiori) == false) {
                $aozora_errors[] = '栞削除に失敗しました。';
            }
        }
    }
    else {
        if(file_exists($localpath_shiori)) {
            $rawdata_shiori = FileCtl::file_read_lines($localpath_shiori);
        }
    }
}


//===================================================================
// 事前処理
//===================================================================
// タイトル名(1行目がタイトルになってない場合もあるのでとりあえずファイル名)
$title = htmlspecialchars($fname, ENT_QUOTES, 'Shift_JIS', false);

$shiorino = -1;
// 栞ファイルのデータ構造は
// キー名 = 値;
// のようにする。前後の空白は原則無視。
if(count($rawdata_shiori) > 0) {
    foreach($rawdata_shiori as $line) {
        if(mb_ereg('^\s*l\s*=\s*(-?\d+);\s*$', $line, $match) > 0) {
            $shiorino = $match[1];
        }
    }
}


//============================================================
// HTMLプリント
//============================================================


echo $_conf['doctype'];
include P2OWNEX_LIB_DIR . '/' . ($_conf['ktai'] ? 'read_k' : 'read') . '.inc.php';

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
