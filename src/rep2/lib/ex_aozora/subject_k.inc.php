<?php
/**
 * �Ǝ�����: �󕶌ɂ̃t�@�C���ꗗ(PC)
 * URL�\��: read_aozora.php?book=[�t�H���_��\�t�@�C����]��URL�G���R�[�h
 */

// {{{ �w�b�_

echo <<<EOH
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<title>�󕶌�(text)</title>
</head>
<body{$_conf['k_colors']}>
{$_info_msg_ht}
<p><b>{$title}</b></p>
<hr>\n
EOH;

// �󕶌Ƀf�[�^�f�B���N�g���̃X�L�����Ɏ��s�����Ƃ�
if (!$aozora_scanDirSuccess) {
    echo '�f�[�^�f�B���N�g���̃X�L�����Ɏ��s���܂����B</body></html>';
    exit;
}


// }}}
// {{{ ���o��

reset($items);
$roopcounter1 = 0;
while($roopcounter1 < count($items)){
    // $items�̃f�[�^�\����
    //   $items[�f�B���N�g����(stirng�^)][���ɑΉ�����t�@�C���ꗗ(array�^)]
    // �Ƃ����`
    
    // �t�H���_�z���̃t�@�C���ꗗ�z������o��
    $roopcounter2 = 0;
    $jjj = $items[$roopcounter1][1];

    // �t�H���_���\��
    if(count($jjj) > 0){
        $r = 'r1';
        echo '�E' . $items[$roopcounter1][0] . '<br>';

        while($roopcounter2 < count($jjj)){
            
            // �t�@�C�����\��
            $param = $items[$roopcounter1][0] . DIRECTORY_SEPARATOR . $jjj[$roopcounter2];
            $param = urlencode($param);
            $link_orig = 'read_aozora.php?book=' . $param . $_conf['k_at_a'];
            echo '�@+<a href="' . $link_orig . '">' . $jjj[$roopcounter2] . '</a><br>';

            $roopcounter2=$roopcounter2+1;
        }
    }
    $roopcounter1=$roopcounter1+1;
}

// }}}
// {{{ �t�b�^

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
