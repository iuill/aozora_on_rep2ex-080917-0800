<?php
/**
 * �󕶌ɗp���� - �󕶌Ƀt�@�C����\��
 */

// {{{ �w�b�_

$ch_title = htmlspecialchars($channel['title'], ENT_QUOTES, 'Shift_JIS', false);

echo <<<EOH
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<style type="text/css">
  strong { color: blueviolet; }
  hr.newpage {
    color: #0000FF;
    background-color: #0000FF;
    /* height: 1px; */
    /* border: 1px; */
    border-style: dashed;
    }
</style>
<title>{$title}</title>
</head>
<body{$_conf['k_colors']}>
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

// �\������y�[�W�̔z��
$pages = array();

// �y�[�W�ԍ�
$pagenum = 0;


$pagechars = 0; // �y�[�W���̕�����(���~�b�g���B���Ƀy�[�W�ǉ����f�p)
$page = array(); // �y�[�W���Ƃ̕�����z��
$pageadded = false; // �y�[�W��ǉ��������̃t���O
$desc = false; // �L�������̂Ƃ��̃t���O
$a = 0;

define('MAX_LEN', 4000); // 1��ʂ�����̍ő啶����

// ���e����͂��āA���������Ƀy�[�W����
foreach($rawdata as $line) {

    // �\���ɂ͎��ۂɂ͊֌W�Ȃ��s�ł��J�E���g(�s�ԍ���1����J�E���g����)
    $a++;
    
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
                
                // au���� <strong> �^�O�͖��������݂����Ȃ̂ŁA�X�^�C���V�[�g�ŐF�ύX�����p
                if(($result = mb_ereg_replace($pattern, '<strong>' . $match[1] . '</strong>',  $str1)) != false) {
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
    $nametag = '';
    $tmp = '';
    // �L���̐����̂Ƃ��͕��������J�E���g���Ȃ�
    // �܂��A�{���Ɋ֌W���镔���̓J�E���g
    $linelen = mb_strlen($line);
    
    if($ruby_replaced == true){
        $tmp = $nametag . $str1 . '<br>';
    }
    // �L���̐��������͖�������悤�ɂ���
    else if ( preg_match('/^[-]{40,}\s*$/', $line, $match) ) {
        if($desc == false) {
            $desc = true;
            // �L���̐����̂Ƃ��̓J�E���g���Ȃ�
            $linelen = 0;
        }else {
            $desc = false;
            $tmp = '';
            // ���̒i�K�ł͂܂�"-----------"���Ă�Ȃ̂ŃJ�E���g���Ȃ�
            $linelen = 0;
        }
    }
    else if($desc == true) {
        $tmp = '';
        $linelen = 0;
    }
    // �摜�ւ̃����N�����邩�`�F�b�N
    // �摜�ւ̃����N�ł���΁A�t�@�C�������ۂɑ��݂��邩�`�F�b�N���A�����N�𐶐�����B
    // �摜�ւ̃����N�łȂ���΂��̂܂ܕ\������B
    else if( preg_match('/^\s*<img\s*src\s*=\s*"?\/?(.+(?:jpg|jpeg|gif|png))"?>\s*$/i', $line, $match) ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            $tmp = $nametag . '<a href="' . $url . '">' . htmlspecialchars('<' . $match[1] . '>', ENT_QUOTES, 'Shift_JIS', false) . '</a><br>';
        }else {
            $tmp = $nametag . htmlspecialchars('<' . $match[1] . '> (�Q�Ǝ��s)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // �摜�ւ̃����N�����邩�`�F�b�N2�m���}�G�ixxxxx.jpg�j����n
    else if( mb_ereg('^\s*�m���}�G�i"?\/?(.+(?:jpg|jpeg|gif|png))"?�j����n\s*$', $line, $match) > 0 ) {
        $imgpath = $_conf['dat_dir'] . DIRECTORY_SEPARATOR . 'p2_aozora' . DIRECTORY_SEPARATOR . 'text'. DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $match[1];
        if(file_exists($imgpath)) {
            $url = 'aas_aozora.php?book=' . urlencode($book) . '&img=' . urlencode($match[1]);
            $tmp = $nametag . '<a href="' . $url . '">' . htmlspecialchars('<' . $match[1] . '>', ENT_QUOTES, 'Shift_JIS', false) . '</a><br>';
        }else {
            $tmp = $nametag . htmlspecialchars('<' . $match[1] . '> (�Q�Ǝ��s)', ENT_QUOTES, 'Shift_JIS', false) . '<br>';
        }
    }
    // ���y�[�W�̃`�F�b�N
    else if(mb_ereg('^\s*(?:�m�����Łn|�m�����y�[�W�n|�m�����i�n|�m�������n)\s*$', $line, $match) > 0 ) {
        $tmp = '<hr class="newpage">';
    }
    else {
        $tmp = $nametag . $line . '<br>';
    }

    // �s��ǉ��B
    // �K�v������΃y�[�W��V�K�ǉ�
    if($pagechars + $linelen > MAX_LEN) {
        $pages[] = $page;
        $pageadded = true;
        
        $page = array();
        $page[] = $tmp;
        $pagechars = $linelen;
    }else {
        $page[] = $tmp;
        $pagechars += $linelen;
        $pageadded = false;

    }
    
    // GET�p�����[�^���� $l=xx �ƌ��݂̍s����v�����Ƃ��A
    // ���̎��_�ł̃y�[�W��\���y�[�W�Ƃ���
    if(strcmp($a, $l) == 0) {
        if($pageadded == true) {
            $pagenum = count($pages);
        }else {
            $pagenum = count($pages);
        }
    }
}

// �Ō�̍s�����������Ƃ��ɕ������̊֌W�Ńy�[�W��ǉ����ĂȂ��ꍇ�A
// �������̃y�[�W��ǉ�����B
if(count($page) > 0) {
    $pages[] = $page;
    $pageadded = true;
}

// $l��0�����̂Ƃ��͍ŏ��̃y�[�W���w�肵���Ƃ݂Ȃ�
// �e�L�X�g���̍s�����傫���l���w�肳�ꂽ�Ƃ��͍Ō�̃y�[�W���w�肳�ꂽ���̂Ƃ݂Ȃ�
if ($l < 0 || strcmp($l, '') == 0) {
    $pagenum = 0;
}else if (count($rawdata) < $l) {
    $pagenum = count($pages) - 1;
}

$prev = 0; // �O�y�[�W�̍ŏ��̍s�ԍ�($l=xx�̒l)
if (0 < $pagenum && $pagenum < count($pages)) {
    // $prev�Z�o
    $a = 0;
    while($a < $pagenum-1) {
        $prev += count($pages[$a]);
        $a++;
    }
    
    if($prev != 0) {
        $prev++;
    }
    if($pagenum == 1)
    {
        $prev = 0;
    }
}
$next = 0; // ���y�[�W�̍ŏ��̍s�ԍ�($l=xx�̒l)
if(0 <= $pagenum && $pagenum < count($pages)-1 ) {
    // $next�Z�o
    if($pagenum == 0) {
            $next = count($pages[0])+1;
    }
    else {
        $a = 0;
        while($a <= $pagenum) {
            $next += count($pages[$a]);
            $a++;
        }
        
        $next++;
    }
}

$prevstr = '';
if(0 < $pagenum ) {
    $prevstr = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $prev . '"' . $_conf['k_accesskey_at']['prev'] . '>' . $_conf['k_accesskey_st']['prev']. '�O</a>';
}
$nextstr = '';
if($pagenum < count($pages) - 1) {
    $nextstr = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $next . '"' . $_conf['k_accesskey_at']['next'] . '>' . $_conf['k_accesskey_st']['next']. '��</a>';
}


// --------------------------
//    �w��̃y�[�W���e�\��
// --------------------------

// �x���o�^����Ă���|�\��
if (strcmp($s_reg, 1) == 0){
    $shiori_str_h = '�x��o�^���܂����B ';
}
else if (strcmp($s_del, 1) == 0){
    $shiori_str_h_del = '�x���폜���܂����B';
}
else if ($shiorino > 0) {
    $shiori_str_h = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $shiorino . '">�x�\��</a> ';
    $shiori_str_del = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $l . '&s_del=1"' . $_conf['k_accesskey_at']['del_shiori'] . '>' . $_conf['k_accesskey_st']['del_shiori'] . '�x�폜</a> ';
}

// �x�o�^�p�����N
{
    $shiori_str_f = '<a href="read_aozora.php?book=' . urlencode($book) . '&l=' . $l . '&s_reg=1"' . $_conf['k_accesskey_at']['reg_shiori'] . '>' . $_conf['k_accesskey_st']['reg_shiori'] . '�x�o�^</a> ';
}

// ���݂̃y�[�W�ԍ��ƍő�y�[�W���\��
$cur_page_str = '[' . $pagenum . '/' . (count($pages)-1) . ']';

echo <<<EOH
{$_info_msg_ht}
<a name="above"></a>
<h1>{$title}</h1>
{$shiori_str_h}
{$shiori_str_h_del}
{$cur_page_str}
<a href="#bottom"{$_conf['k_accesskey_at']['bottom']}>{$_conf['k_accesskey_st']['bottom']}��</a>
<hr>
EOH;

foreach ($pages[$pagenum] as $line) {
   echo $line;
}

// }}}
// {{{ �t�b�^
echo <<<EOH
<br>
<hr>
<a name="bottom"></a>
<div class="center">
{$shiori_str_f}
{$prevstr}
{$cur_page_str}
{$nextstr}
{$shiori_str_del}
<a href="#above"{$_conf['k_accesskey_at']['above']}>{$_conf['k_accesskey_st']['above']}��</a>
<hr>
<a href="subject_aozora.php"{$_conf['k_accesskey_at'][9]}>{$_conf['k_accesskey_st'][9]}�󕶌�</a>
{$_conf['k_to_index_ht']}
</div>
EOH;

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
