<?php
/**
 * �󕶌ɂŉ摜�𖄂ߍ��ނ��߂����̂���(���ɂ����Ƃ������@�����邩������Ȃ�)
 * 
 * Dependencies:
 * - PHP Version: 4.2.0 or newer (rep2-expack requires 4.4.1 or newer)
 * - PHP Extension: gd (with FreeType 2)
 * - PHP Extension: mbstring
 * - PHP Extension: pcre
 *
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once './conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.aas.enabled']) {
    p2die('AAS�������ł��B�󕶌ɂɉ摜�𖄂ߍ��ނ��߂ɕK�v�ł��B', 'conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B');
}


// }}}
// {{{ �O����

// ���`�F�b�N
$errors = array();
$font = $_conf['expack.aas.font_path'];

if (!extension_loaded('gd')) {
    $errors[] = 'PHP��GD�@�\�g���������ł��B';
}

// GET�p�����[�^�ǂݍ���
$book = $_REQUEST['book'];
$img = $_REQUEST['img'];
$zoom = $_REQUEST['zoom'];
$rotate = $_REQUEST['rotate'];


// GET�œn���ꂽbook�p�����[�^�ɕςȂ̂��������ĂȂ����ȈՃ`�F�b�N
$book_splited = explode("\\", $book);
if(count($book_splited) != 2) {
    $errors[] = '�ς�GET�p�����[�^�����Ă܂���H';
}
$dirname = $book_splited[0];


$localimgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $img;
if(file_exists($localimgpath) == false) {
    $errors[] = '�摜�H���݂��܂���ˁE�E�E�B' . $localimgpath;
}

if($zoom != '' && is_numeric($zoom) != true) {
    $errors[] = '�{���̎w�肪�����E�E�E�B';
}

// �G���[���b�Z�[�W��\�����ďI��
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
// {{{ ���C������


// �摜�T�C�Y������
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


// �C���[�W�쐬
list( $width_raw, $height_raw, $type, $attr) = GetImageSize($localimgpath);
$image = ImageCreateFromJpeg("$localimgpath");

// ���T�C�Y
// ���T�C�Y�p�̊֐��������B
// imagecopyresized() :
// imagecopyresampled() : �k�����ɂ͂������̕������ꂢ�݂����B�������T�C�Y������������Ƌt���ʁH
if($zoom > 0 && $zoom != 1) {
    $width = $width_raw * $zoom;
    $height = $height_raw * $zoom;
    $dst_image = imagecreatetruecolor($width, $height);
    $result = imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $width, $height, $width_raw, $height_raw);
    imagedestroy($image);
    $image = $dst_image;
}else {
    if($default_width < $width_raw || $default_height < $height_raw) {
        // �A�X�y�N�g����ێ����ďk������
        
        $ratio = $width_raw / $height_raw;
        
        $diff_w = $width_raw - $default_width;
        $diff_h = $height_raw - $default_height;
        
        // ���傫�������o�Ă�����̕ӂ���ɃT�C�Y�v�Z(�ȈՔ�)
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

// ��]
/* �Ƃ肠�����悭�킩���ĂȂ��̂ŃR�����g�A�E�g
if ($rotate) {
    $new_image = imagerotate($image, 270, $bgcolor);
    // Bug #24155 (gdImageRotate270 rotation problem).
    //$new_image = imagerotate(imagerotate($image, 180, $bgcolor), 90, $bgcolor);
    imagedestroy($image);
    $image = $new_image;
}
*/


// �摜���o��
if (!headers_sent()) {
 	header('Content-Type: image/jpeg');
    imagejpeg($image);
    imagedestroy($image);
}

exit;


// }}}
// {{{ aas_parseColor()

/**
 * 3���܂���6����16�i���\�L�̐F�w��� array(int, int, int) �ɕϊ����ĕԂ�
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
