<?php
/**
 * 青空文庫で画像を埋め込むためだけのもの(他にもっといい方法があるかもしれない)
 * 
 * Dependencies:
 * - PHP Version: 4.2.0 or newer (rep2-expack requires 4.4.1 or newer)
 * - PHP Extension: gd (with FreeType 2)
 * - PHP Extension: mbstring
 * - PHP Extension: pcre
 *
 */

// {{{ p2基本設定読み込み&認証

require_once './conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.aas.enabled']) {
    p2die('AASが無効です。青空文庫に画像を埋め込むために必要です。', 'conf/conf_admin_ex.inc.php の設定を変えてください。');
}


// }}}
// {{{ 前処理

// 環境チェック
$errors = array();
$font = $_conf['expack.aas.font_path'];

if (!extension_loaded('gd')) {
    $errors[] = 'PHPのGD機能拡張が無効です。';
}

// GETパラメータ読み込み
$book = $_REQUEST['book'];
$img = $_REQUEST['img'];
$zoom = $_REQUEST['zoom'];
$rotate = $_REQUEST['rotate'];


// GETで渡されたbookパラメータに変なのが混じってないか簡易チェック
$book_splited = explode("\\", $book);
if(count($book_splited) != 2) {
    $errors[] = '変なGETパラメータ送られてますよ？';
}
$dirname = $book_splited[0];


$localimgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $img;
if(file_exists($localimgpath) == false) {
    $errors[] = '画像？存在しませんね・・・。' . $localimgpath;
}

if($zoom != '' && is_numeric($zoom) != true) {
    $errors[] = '倍率の指定が無効・・・。';
}

// エラーメッセージを表示して終了
if (count($errors) > 0) {
    P2Util::header_nocache();
    echo '<html>';
    echo '<head><title>AAS_Aozora Error</title></head>';
    echo '<body>';
    echo '<p><b>AAS_Aozora Error</b></p>';
echo 'book: ' . $book . '<br>';
echo 'img: ' . $img . '<br>';
echo 'zoom: ' . $zoom . '<br>';
echo is_numeric($zoom)==true ? 'true':'false';echo '<br>';
echo 'rotate: ' . $rotate . '<br>';
    echo '<ul><li>';
    echo implode('</li><li>', array_map('htmlspecialchars', $errors));
    echo '</li></ul>';
    echo '</body>';
    echo '</html>';
    exit;
}

// }}}
// {{{ メイン処理


// 画像サイズを決定
if ($inline) {
    $default_width  = $_conf['expack.aas.image_width_il'];
    $default_height = $_conf['expack.aas.image_height_il'];
} elseif (!$_conf['ktai']) {
    $default_width  = $_conf['expack.aas.image_width_pc'];
    $default_height = $_conf['expack.aas.image_height_pc'];
} else {
    $default_width  = $_conf['expack.aas.image_width'];
    $default_height = $_conf['expack.aas.image_height'];
}
if ($rotate) {
    list($default_width, $default_height) = array($default_height, $default_width);
}


// イメージ作成
list( $width_raw, $height_raw, $type, $attr) = GetImageSize($localimgpath);
$image = ImageCreateFromJpeg("$localimgpath");

// リサイズ
// リサイズ用の関数が二つある。
// imagecopyresized() :
// imagecopyresampled() : 縮小時にはこっちの方がきれいみたい。しかしサイズが小さすぎると逆効果？
if($zoom > 0 && $zoom != 1) {
    $width = $width_raw * $zoom;
    $height = $height_raw * $zoom;
    $dst_image = imagecreatetruecolor($width, $height);
    $result = imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $width, $height, $width_raw, $height_raw);
    imagedestroy($image);
    $image = $dst_image;
}else {
    if($default_width < $width_raw || $default_height < $height_raw) {
        // アスペクト比を維持して縮小する
        
        $ratio = $width_raw / $height_raw;
        
        $diff_w = $width_raw - $default_width;
        $diff_h = $height_raw - $default_height;
        
        // より大きく差が出ている方の辺を基準にサイズ計算(簡易版)
        if($diff_w > $diff_h && $diff_h > 0) {
            $width = $default_width;
            $height = $default_width / $ratio;
        }else if($diff_h > $diff_w && $diff_w > 0) {
            $width = $default_height * $ratio;
            $height = $default_height;
        }else {
            $width = $width_raw;
            $height = $height_raw;
        }
        
        $dst_image = imagecreatetruecolor($width, $height);
        $result = imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $width, $height, $width_raw, $height_raw);
        imagedestroy($image);
        $image = $dst_image;
    }
}

// 回転
/* とりあえずよくわかってないのでコメントアウト
if ($rotate) {
    $new_image = imagerotate($image, 270, $bgcolor);
    // Bug #24155 (gdImageRotate270 rotation problem).
    //$new_image = imagerotate(imagerotate($image, 180, $bgcolor), 90, $bgcolor);
    imagedestroy($image);
    $image = $new_image;
}
*/


// 画像を出力
if (!headers_sent()) {
 	header('Content-Type: image/jpeg');
    imagejpeg($image);
    imagedestroy($image);
}

exit;


// }}}
// {{{ aas_parseColor()

/**
 * 3桁または6桁の16進数表記の色指定を array(int, int, int) に変換して返す
 */
function aas_parseColor($hex)
{
    if (!preg_match('/^#?(?:[[:xdigit:]]{3}|[[:xdigit:]]{6})$/', $hex)) {
        return false;
    }
    if ($hex[0] == '#') {
        $dec = hexdec(substr($hex, 1));
    } else {
        $dec = hexdec($hex);
    }
    if (strlen($hex) < 6) {
        $r = ($dec & 0xf00) >> 8;
        $g = ($dec & 0xf0) >> 4;
        $b = $dec & 0xf;
        return array(($r << 4) | $r, ($g << 4) | $g, ($b << 4) | $b);
    } else {
        return array(($dec & 0xff0000) >> 16, ($dec & 0xff00) >> 8, $dec & 0xff);
    }
}


// }}}
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
