<?php
/**
 * �Ǝ�����: �󕶌ɂ̃t�@�C���ꗗ��\��
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once './conf/conf.inc.php';

$_login->authorize();

// }}}

//============================================================
// �ϐ��̏�����
//============================================================

$_info_msg_ht = '';
$items = array();

$num = trim($_REQUEST['num']);

//===================================================================
// �󕶌Ƀt�@�C���ꗗ�擾
//===================================================================

$localpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR;

// �ۑ��p�f�B���N�g�����Ȃ���΂���(�Ō���̓t�@�C�����Œ�ō쐬����Ă�炵���̂œK����tmp����������)
if (!is_dir($localpath)) {
    require_once P2_LIB_DIR . '/FileCtl.php';
    FileCtl::mkdir_for($localpath . 'tmp');
}


$aozora_scanDirSuccess = false;

// �t�H���_�ꗗ�擾( E_WARNING �G���[��}�����邽�� opendir() ��@��t��)
if (($dh = @opendir($localpath)) == true) {
    while ($entrydir = readdir($dh)) {
        $bunkoDir = $localpath . DIRECTORY_SEPARATOR . $entrydir;
        if (is_dir($bunkoDir) && $entrydir != "." && $entrydir != ".." ) {
            
            // �t�@�C���ꗗ���擾���āA�񎟌��z��Ɋi�[
            if (($fh = @opendir($bunkoDir)) == true) {
                $files = array();
                while ($entryf = readdir($fh)) {
                    $bunkoF = $bunkoDir . DIRECTORY_SEPARATOR . $entryf;
// �f�o�b�O�pecho
// echo $bunkoF . '<br>';
                    // txt�t�@�C���ȊO�͖���
                    if (is_file($bunkoF) && ereg(".+\.txt", $entryf) == true) {
// �f�o�b�O�pecho
// echo '�����������]�݂̃e�L�X�g�t�@�C��<br>';
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
    // ��`�E�E�E
    $aozora_scanDirSuccess = false;
    $items[] = 'Not Opened: Aozora Data Directory! [' . $localpath . ']';
}

//===================================================================
// HTML�\���p�ϐ��̐ݒ�
//===================================================================

//�^�C�g��
//$title = isset($items['title']) ? htmlspecialchars($items['title'], ENT_QUOTES, 'Shift_JIS', false) : '';

//�X�V����
$reloaded_time = date('m/d G:i:s');


//============================================================
// HTML�v�����g
//============================================================

echo $_conf['doctype'];
include P2OWNEX_LIB_DIR . '/' . ($_conf['ktai'] ? 'subject_k' : 'subject') . '.inc.php';

// {{{ rss_link2ch_callback()

/**
 * 2ch,bbspink�������N��p2�œǂނ��߂̃R�[���o�b�N�֐�
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
