<?php
/**
 * �󕶌ɗp���� - �󕶌Ƀ��j���[��\������̂�
 */

// require_once P2EX_LIB_DIR . '/rss/common.inc.php';

if ($_conf['ktai']) {
    print_list_k();
} else {
    print_list();
}

// {{{ print_list()

/**
 * �󕶌Ƀ��j���[�\���̂�
 */
function print_list()
{
    global $_conf;

    echo "<div class=\"menu_cate\">\n";
    echo "<b class=\"menu_cate\" onclick=\"showHide('c_rss');\">�󕶌�</b>\n";

    echo "\t<div class=\"itas\" id=\"c_rss\">\n";

    // echo "\t\t�@�i����ہj\n";
    echo "\t�@<a href=\"subject_aozora.php\">�e�L�X�g</a><br>\n";

    echo "\t</div>\n";
    echo "</div>\n";
    flush();

}

// }}}
// {{{ print_list_k()

/**
 * �o�^����Ă���RSS�ꗗ��\���i�g�їp�j
 */
function print_list_k()
{
    global $_conf;


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
