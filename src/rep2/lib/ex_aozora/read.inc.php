<?php
/**
 * �Ǝ�����: �󕶌ɂ̃t�@�C�����e�\��(PC)
 */

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
    <link rel="stylesheet" type="text/css" href="css.php?css=read&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <style type="text/css">
        hr.newpage {
          color: #0000FF;
          background-color: #0000FF;
          /* height: 1px; */
          /* border: 1px; */
          border-style: dashed;
          }
    </style>
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript">
    //<![CDATA[
    function setWinTitle(){
        if (top != self) {top.document.title=self.document.title;}
    }
    //]]>
    </script>
</head>
<body onload="setWinTitle()">
<h1>{$title}</h1>
<hr>
{$_info_msg_ht}
EOH;

// �󕶌ɂ̓ǂݎ��Ɏ��s�����Ƃ�
if (count($aozora_errors) > 0) {
    echo '<p><b>�󕶌� Error</b></p>';
    echo '<ul><li>';
    echo implode('</li><li>', array_map('htmlspecialchars', $aozora_errors));
    echo '</li></ul>';
    
    echo '</body></html>';
    exit;
}

// �s���̃J�E���^(�\���ɂ͎��ۂɂ͊֌W�Ȃ��s�ł��J�E���g)
$a = 0;
$desc = false; // �L�������̂Ƃ��̃t���O

if ($shiorino > 0) {
    echo '���o�C�����[�h�Şx���o�^����Ă��܂�(<a href="#' . $shiorino . '">�x�ɃW�����v</a>)<hr>';
}

// ���e�\��
foreach($rawdata as $line) {

    // �\���ɂ͎��ۂɂ͊֌W�Ȃ��s�ł��J�E���g(�s�ԍ���1����J�E���g����)
    $a++;
    
    // ���r����("�b"�ŋ�؂��)
    // preg_match()���ƃ}���`�o�C�g�̏��������܂��ł��Ȃ��݂���
    // else if(preg_match('/�b([^�b]+)[�s]([^�t]+)[�t]/', $line, $match)) {
    $str1 = '';
    $ruby_replaced = false;
    if($desc == false && strcmp($line, '') != 0) {
        // ���r����("�b"�ŋ�؂���̂́A"�b"����"�s"�܂ł̕����Ƀ��r��t��)
        // preg_match()���ƃ}���`�o�C�g�̏��������܂��ł��Ȃ��݂���
        // else if(preg_match('/�b([^�b]+)[�s]([^�t]+)[�t]/', $line, $match)) {
        $result = '';
        if(($result = mb_ereg_replace('�b([^�b]+)[�s]([^�t]+)[�t]', '<ruby><rb>\1</rb><rp>(</rp><rt>\2</rt><rp>)</rp>', $line)) != false) {
            $str1 = $result;
        }
        // ���r����("�b"�ŋ�؂�Ȃ����̂͒��O��1�����������r��t��)
        if(($result = mb_ereg_replace('([^�s�t])[�s]([^�t]+)[�t]', '<ruby><rb>\1</rb><rp>(</rp><rt>\2</rt><rp>)</rp>', $str1)) != false) {
            $str1 = $result;
        }
        
        // �T�_�E�T���̏���(���܂����K�\�����킩��Ȃ��̂œ�i�K�ɕ����ď���)
        // �� �u���񐔐����Ȃ����Ƃ��܂������Ȃ��̂ŁA�s������̍ő�u���񐔂̐�������
        // �� �T�_�������ɖT������������悤�ȃP�[�X�͖���
        $replaced_max = 30;
        // �܂��͖T�_
        for($repleced_num = 0; $repleced_num < $replaced_max; $repleced_num++) {
            if(mb_ereg('�m���u([^�v]+)�v�ɖT�_�n', $str1, $match) > 0) {
            
                $pattern = $match[1] . '�m���u' . $match[1] . '�v�ɖT�_�n';
                if(($result = mb_ereg_replace($pattern, '<ruby><rb>' . $match[1] . '</rb><rp>(</rp><rt>' . str_repeat("�E", mb_strlen($match[1])) . '</rt><rp>)</rp></ruby>',  $str1)) != false) {
                    $str1 = $result;
                }
            }
        }
        // �����͖T��
        for($repleced_num = 0; $repleced_num < $replaced_max; $repleced_num++) {
            if(mb_ereg('�m���u([^�v]+)�v�ɖT���n', $str1, $match) > 0) {
            
                $pattern = $match[1] . '�m���u' . $match[1] . '�v�ɖT���n';
                
                if(($result = mb_ereg_replace($pattern, '<u>' . $match[1] . '</u>',  $str1)) != false) {
                    $str1 = $result;
                }
            }
        }
        
        if(strcmp($str1, $line) != 0){
            $ruby_replaced = true;
        }
    }
    
    $imgpath = '';
    $showruby = true; // ���r��\������ꍇ��true�B������IE���ƃ��C�A�E�g�������B
    $nametag = '<a name="' . $a . '"></a>';
    
    if($ruby_replaced == true && $showruby == true){
        echo $nametag . $str1 . '<br>';
    }
    // �L���̐��������͖�������悤�ɂ���
    else if ( preg_match('/^[-]{40,}\s*$/', $line, $match) ) {
        if($desc == false) {
            $desc = true;
        }else {
            $desc = false;
        }
    }
    else if($desc == true) {
    }
    // �摜�ւ̃����N�����邩�`�F�b�N
    // �摜�ւ̃����N�ł���΁A�t�@�C�������ۂɑ��݂��邩�`�F�b�N���A�����N�𐶐�����B
    // �摜�ւ̃����N�łȂ���΂��̂܂ܕ\������B
    else if( preg_match('/^<img\s*src\s*=\s*"?\/?(.+(jpg|jpeg|gif|png))"?>\s*$/i', $line, $match) ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            echo '<br>' . $nametag . '<a href="' . $url . '&zoom=1"><img src="' . $url . '"></a><br>';
        }else {
            echo $nametag . htmlspecialchars('<' . $match[1] . '> (�Q�Ǝ��s)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // �摜�ւ̃����N�����邩�`�F�b�N2�m���}�G�ixxxxx.jpg�j����n
    else if( mb_ereg('^\s*�m���}�G�i"?\/?(.+(?:jpg|jpeg|gif|png))"?�j����n\s*$', $line, $match) > 0 ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            echo '<br>' . $nametag . '<a href="' . $url . '&zoom=1"><img src="' . $url . '"></a><br>';
        }else {
            echo $nametag . htmlspecialchars('<' . $match[1] . '> (�Q�Ǝ��s)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // ���y�[�W�̃`�F�b�N
    else if(mb_ereg('^\s*(?:�m�����Łn|�m�����y�[�W�n|�m�����i�n|�m�������n)\s*$', $line, $match) > 0 ) {
        echo '<hr class="newpage" />';
    }
    else {
        echo $nametag . $line . '<br>';
    }
}

// }}}
// {{{ �t�b�^

echo '</body></html>';


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
