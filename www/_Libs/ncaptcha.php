<?php

session_start();
$width = 200;
$height = 40;

// ������� ����������� �����
$_SESSION['captcha'] = rand (10000, 99999);

// ������� 100 � ������, 40 � ������
$img = imagecreatetruecolor ($width, $height);
if (!$img) die ('error');

$str = (string) $_SESSION['captcha'];
$start = rand( 10, 40 );

// �������� ����� ������
$fill = imagecolorallocate ($img, 255, 255, 255);
imagefill ($img, 1, 1, $fill);

for ($i = 0; $i < 5; $i++){
	$angle = rand( -15, 15 );
	$color = imagecolorallocate( $img, rand( 70, 200 ), rand( 70, 200 ), rand( 70, 200 ) );
	imagettftext( $img, 20, $angle, $i * 18 + $start, 30, $color, '../_Images/fonts/verdana.ttf', $str[$i] );
}

// ����������� ������� ������� (������ #1)
$shum = rand (8, 12);
for ($i = 0; $i < $shum; $i++){
	$fill = imagecolorallocate ($img, rand(0, 255), rand(0, 255), rand(0, 255));
	imageline ($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $fill);
}

// ��������� ����� �� ������ captcha.ru/captchas/multiwave
$img2 = imagecreatetruecolor ($width, $height);

// ��������� ��������� (����� �������������������� � ��������������):
// �������
$rand1 = mt_rand(700000, 1000000) / 15000000;
$rand2 = mt_rand(700000, 1000000) / 15000000;
$rand3 = mt_rand(700000, 1000000) / 15000000;
$rand4 = mt_rand(700000, 1000000) / 15000000;
// ����
$rand5 = mt_rand(0, 3141592) / 1000000;
$rand6 = mt_rand(0, 3141592) / 1000000;
$rand7 = mt_rand(0, 3141592) / 1000000;
$rand8 = mt_rand(0, 3141592) / 1000000;
// ���������
$rand9 = mt_rand(400, 600) / 100;
$rand10 = mt_rand(400, 600) / 100;
 
for($x = 0; $x < $width; $x++){
  for($y = 0; $y < $height; $y++){
    // ���������� �������-�����������.
    $sx = $x + ( sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6) ) * $rand9;
    $sy = $y + ( sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8) ) * $rand10;
 
    // ���������� �� ��������� �����������
    if($sx < 0 || $sy < 0 || $sx >= $width - 1 || $sy >= $height - 1){ 
      $color = 255;
      $color_x = 255;
      $color_y = 255;
      $color_xy = 255;
    }else{ // ����� ��������� ������� � ��� 3-� ������� ��� ������� �������������
      $color = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
      $color_x = (imagecolorat($img, $sx + 1, $sy) >> 16) & 0xFF;
      $color_y = (imagecolorat($img, $sx, $sy + 1) >> 16) & 0xFF;
      $color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
    }

    // ���������� ������ �����, ����� ������� ������� ����������
    if($color == $color_x && $color == $color_y && $color == $color_xy){
      $newcolor=$color;
    }else{
      $frsx = $sx - floor($sx); //���������� ��������� ����������� �� ������
      $frsy = $sy - floor($sy);
      $frsx1 = 1 - $frsx;
      $frsy1 = 1 - $frsy;

      // ���������� ����� ������ ������� ��� ��������� �� ����� ��������� ������� � ��� �������
      $newcolor = floor( $color    * $frsx1 * $frsy1 +
                         $color_x  * $frsx  * $frsy1 +
                         $color_y  * $frsx1 * $frsy  +
                         $color_xy * $frsx  * $frsy );
    }
    imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
  }
}

header ('Content-type: image/gif');
imagegif ($img2);
?>