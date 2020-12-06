<?php
/**
 * �Ǝ�����: �󕶌ɂ̃t�@�C���ꗗ(PC)
 * URL�\��: read_aozora.php?book=[�t�H���_��\�t�@�C����]��URL�G���R�[�h
 */

// {{{ �\���p�ϐ�

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
// {{{ �c�[���o�[

if ($aozora_scanDirSuccess) {

    // �c�[���o�[���ʕ��i
    $rss_toolbar_ht = <<<EOP
<span class="itatitle"><a class="aitatitle" title="�󕶌� text"{$onmouse_popup}><b>�󕶌� text</b></a></span></td>
<td class="toolbar-anchor"><span class="time">{$reloaded_time}</span>
EOP;

}

// }}}
// {{{ �w�b�_

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

// �󕶌Ƀf�[�^�f�B���N�g���̃X�L�����Ɏ��s�����Ƃ�
if (!$aozora_scanDirSuccess) {
    echo "<h1>�f�[�^�f�B���N�g���̃X�L�����Ɏ��s���܂����B</h1></body></html>";
    exit;
}

echo <<<EOTB
<table id="sbtoolbar1" class="toolbar" cellspacing="0"><tbody><tr>
<td class="toolbar-title">{$rss_toolbar_ht}<a class="toolanchor" href="#sbtoolbar2" target="_self">��</a></td>
</tr></tbody></table>
<table class="threadlist" cellspacing="0">

EOTB;

// }}}
// {{{ ���o��

$description_column_ht = '<th class="tl">��i��</th>';

echo <<<EOP
<thead><tr class="tableheader">
<th class="tu"></th>{$description_column_ht}</tr></thead>
<tbody>\n
EOP;


// ================================================================
// �t�H���_����уt�@�C���ꗗ�̕\��
// �t�H���_�z����txt�t�@�C�������݂��Ȃ��t�H���_�͕\�����Ȃ�
// ================================================================

reset($items);
$roopcounter1 = 0;
while($roopcounter1 < count($items)){
    // $items�̃f�[�^�\����
    //   $items[�f�B���N�g����(stirng�^)][���ɑΉ�����t�@�C���ꗗ(array�^)]
    // �Ƃ����`

    // �C���f���g
    $description_ht = "<td class=\"tu\"></td>";
    
    // �t�H���_�z���̃t�@�C���ꗗ�z������o��
    $roopcounter2 = 0;
    $jjj = $items[$roopcounter1][1];

    // �t�H���_���\��
    if(count($jjj) > 0){
        $r = 'r1';
        echo <<<EOP
        <tr class="{$r}">{$description_ht}<td class="tl">�E {$items[$roopcounter1][0]}</td></tr>\n
EOP;

        while($roopcounter2 < count($jjj)){
            
            // �t�@�C�����\��
            $r = 'r2';
            $param = $items[$roopcounter1][0] . DIRECTORY_SEPARATOR . $jjj[$roopcounter2];
            $param = urlencode($param);
            $link_orig = 'read_aozora.php?book=' . $param;
            echo <<<EOP
            <tr class="{$r}">{$description_ht}<td class="tl">�@�@���� <a id="tt{$i}" class="thre_title" href="{$link_orig}">{$jjj[$roopcounter2]}</a></td></tr>\n
EOP;

            $roopcounter2=$roopcounter2+1;
        }
    }
    $roopcounter1=$roopcounter1+1;
}
// ================================================================
// �����܂�
// ================================================================


// }}}
// {{{ �t�b�^

echo <<<EOF
</tbody>
</table>
<table id="sbtoolbar2" class="toolbar" cellspacing="0"><tbody><tr>
<td class="toolbar-title">{$rss_toolbar_ht}<a class="toolanchor" href="#sbtoolbar1" target="_self">��</a></td>
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
