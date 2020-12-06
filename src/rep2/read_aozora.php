<?php
/**
 * �Ǝ�����: �󕶌ɂ̃t�@�C���ꗗ(PC)
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��
require_once './conf/conf.inc.php';

$_login->authorize();

// }}}

if ($_conf['view_forced_by_query']) {
    output_add_rewrite_var('b', $_conf['b']);
}

//============================================================
// �ϐ��̏�����
//============================================================
$aozora_errors = array();
$book = $_REQUEST['book'];
$l = $_REQUEST['l'];
$s_reg = $_REQUEST['s_reg']; // �x�o�^�p�t���O
$s_del = $_REQUEST['s_del']; // �x�폜�p�t���O (�{�����ꂾ���ł�POST�ɕύX���ׂ����H)

// $l�̃`�F�b�N
// �K�؂��ۂ�������������A"#����"��URL�ɕt�����ă��_�C���N�g
if ($l != '') {
    if (is_numeric($l) == false && $l <= 0) {
        $aozora_errors[] = '�����Ȉʒu���w�肳��܂����B';
    }
    else {
        if($_conf['ktai'] == false) {
            // GET�p�����[�^�̕������� &l=xx ������
            $getstr = preg_replace('/.*([?].*)(&l=\d+)(&.*|&amp;.*|.*)/i', '\1\3', $_SERVER['REQUEST_URI']);
            // &l=xx�̐����̕������o��
            $num = preg_replace('/.*&l=(\d+)(&.*|&amp;.*|.*)/i', '\1', $_SERVER['REQUEST_URI']);
            
            // URL����A &l=���� ���������AURL�̍Ō���� #���� ��t�����ă��_�C���N�g
            // �f�o�b�O���� header() �̑O�� echo ''; �����s��Ȃ��悤�ɂ��邱�ƁI
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $_SERVER["PHP_SELF"] . $getstr . '#' . $num);
            exit();
        }
    }
}

//===================================================================
// �󕶌Ƀt�@�C���ǂݍ���
//===================================================================

$book_splited = explode("\\", $book);

// GET�œn���ꂽbook�p�����[�^�ɕςȂ̂��������ĂȂ����ȈՃ`�F�b�N
if($book == '' && count($book_splited) != 2)
{
    $aozora_errors[] = '�ς�GET�p�����[�^�����Ă܂���B';
}

$dirname = $book_splited[0];
$fname = $book_splited[1];

// �t�@�C���ǂݍ���
$localpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $book;

if(file_exists($localpath)) {
    require_once P2_LIB_DIR . '/FileCtl.php';
    $rawdata = FileCtl::file_read_lines($localpath);
    // print_r($rawdata);
    if(count($rawdata) <= 0) {
        $aozora_errors[] = '�t�@�C���̓��e����ł��B';
    }
}
else {
    $aozora_errors[] = '�󕶌Ƀt�@�C�������݂��܂���B';
}


//===================================================================
// �x�o�^���� or �x�t�@�C���ǂݍ���
//===================================================================
if(count($aozora_errors) == 0) {
    // �x�t�@�C���̊g���q�� ".bmk" �Ƃ���
    $localpath_shiori = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . basename($fname, '.txt') . '.bmk';
        
    // �x�o�^�̃`�F�b�N
    if (strcmp($s_reg, 1) == 0) {
        $wrtstr = 'l='; // �������ޕ�����(l=xx;�Ȍ`��)
        if(strcmp($l, '') == 0 || $l < 0){
            $wrtstr = $wrtstr . '0;';
        }
        else{
            $wrtstr = $wrtstr . $l . ';';
        }
        
        require_once P2_LIB_DIR . '/FileCtl.php';
        if(FileCtl::file_write_contents($localpath_shiori, $wrtstr) == false) {
            $aozora_errors[] = '�x�o�^�Ɏ��s���܂����B';
        }
    }
    // �x�폜
    else if (strcmp($s_del, 1) == 0) {
        require_once P2_LIB_DIR . '/FileCtl.php';
        if(file_exists($localpath_shiori)) {
            if (unlink ($localpath_shiori) == false) {
                $aozora_errors[] = '�x�폜�Ɏ��s���܂����B';
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
// ���O����
//===================================================================
// �^�C�g����(1�s�ڂ��^�C�g���ɂȂ��ĂȂ��ꍇ������̂łƂ肠�����t�@�C����)
$title = htmlspecialchars($fname, ENT_QUOTES, 'Shift_JIS', false);

$shiorino = -1;
// �x�t�@�C���̃f�[�^�\����
// �L�[�� = �l;
// �̂悤�ɂ���B�O��̋󔒂͌��������B
if(count($rawdata_shiori) > 0) {
    foreach($rawdata_shiori as $line) {
        if(mb_ereg('^\s*l\s*=\s*(-?\d+);\s*$', $line, $match) > 0) {
            $shiorino = $match[1];
        }
    }
}


//============================================================
// HTML�v�����g
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
