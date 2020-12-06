<?php
/**
 * 独自改造: 青空文庫のファイル一覧を表示
 */

// {{{ p2基本設定読み込み&認証

require_once './conf/conf.inc.php';

$_login->authorize();

// }}}

//============================================================
// 変数の初期化
//============================================================

$_info_msg_ht = '';
$items = array();

$num = trim($_REQUEST['num']);

//===================================================================
// 青空文庫ファイル一覧取得
//===================================================================

$localpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR;

// 保存用ディレクトリがなければつくる(最後尾はファイル名固定で作成されてるらしいので適当にtmpをくっつける)
if (!is_dir($localpath)) {
    require_once P2_LIB_DIR . '/FileCtl.php';
    FileCtl::mkdir_for($localpath . 'tmp');
}


$aozora_scanDirSuccess = false;

// フォルダ一覧取得( E_WARNING エラーを抑制するため opendir() に@を付加)
if (($dh = @opendir($localpath)) == true) {
    while ($entrydir = readdir($dh)) {
        $bunkoDir = $localpath . DIRECTORY_SEPARATOR . $entrydir;
        if (is_dir($bunkoDir) && $entrydir != "." && $entrydir != ".." ) {
            
            // ファイル一覧を取得して、二次元配列に格納
            if (($fh = @opendir($bunkoDir)) == true) {
                $files = array();
                while ($entryf = readdir($fh)) {
                    $bunkoF = $bunkoDir . DIRECTORY_SEPARATOR . $entryf;
// デバッグ用echo
// echo $bunkoF . '<br>';
                    // txtファイル以外は無視
                    if (is_file($bunkoF) && ereg(".+\.txt", $entryf) == true) {
// デバッグ用echo
// echo '↑↑↑↑お望みのテキストファイル<br>';
                        $files[] = $entryf;
                    }
                }
                $items[] = array($entrydir, $files);
                closedir($fh);
            }
		}
	}
	closedir($dh);
    $aozora_scanDirSuccess = true;
} else {
    // ん～・・・
    $aozora_scanDirSuccess = false;
    $items[] = 'Not Opened: Aozora Data Directory! [' . $localpath . ']';
}

//===================================================================
// HTML表示用変数の設定
//===================================================================

//タイトル
//$title = isset($items['title']) ? htmlspecialchars($items['title'], ENT_QUOTES, 'Shift_JIS', false) : '';

//更新時刻
$reloaded_time = date('m/d G:i:s');


//============================================================
// HTMLプリント
//============================================================

echo $_conf['doctype'];
include P2OWNEX_LIB_DIR . '/' . ($_conf['ktai'] ? 'subject_k' : 'subject') . '.inc.php';

// {{{ rss_link2ch_callback()

/**
 * 2ch,bbspink内リンクをp2で読むためのコールバック関数
 */
function rss_link2ch_callback($s)
{
    global $_conf;
    return "{$_conf['read_php']}?host={$s[1]}&amp;bbs={$s[3]}&amp;key={$s[4]}&amp;ls={$s[6]}";
}

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
