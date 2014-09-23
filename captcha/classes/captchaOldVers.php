<?php

define('API_SERVER_NAME', 'http://goncharenko.biz');
define('API_SERVER_URL', API_SERVER_NAME . '/api-captcha');

$requestMetod = $_SERVER['REQUEST_METHOD'];

switch($requestMetod) {
    case 'GET':
        getHtmlContent();
        break;

    case 'POST':
        checkCaptcha();
        break;

    default:
        return false;
}

function getHtmlContent() {

    $width = 120;
    $height = 40;
    $font_size = 14;
    $let_amount = 6;
    $fon_let_amount = 25;
    $font = ROOT . "/font/B52.ttf";

    $letters = array(
        'a', 'b', 'c', 'd', 'e', 'f','g', 'h', 'j', 'k', 'm', 'n', 'p', 'q','r', 's', 't','u', 'v', 'w', 'x', 'y', 'z',
        2, 3, 4, 5, 6, 7, 8, 9
    );

    $colors = array("00");

    $src = imagecreatetruecolor($width,$height);
    $fon = imagecolorallocate($src,255,255,255);
    imagefill($src,10,10,$fon);

    for($i=0;$i < $fon_let_amount;$i++)
    {
        $color = imagecolorallocatealpha($src,rand(0,0),rand(0,0),rand(0,0),100);
        $letter = $letters[rand(0,sizeof($letters)-1)];
        $size = rand($font_size-2,$font_size+2);
        imagettftext($src,$size,rand(0,45),
            rand($width*0.1,$width-$width*0.1),
            rand($height*0.2,$height),$color,$font,$letter);
    }

    $code = array();

    for($i=0;$i < $let_amount;$i++)
    {
        $color = imagecolorallocatealpha($src,$colors[rand(0,sizeof($colors)-1)],
            $colors[rand(0,sizeof($colors)-1)],
            $colors[rand(0,sizeof($colors)-1)],rand(20,40));
        $letter = $letters[rand(0,sizeof($letters)-1)];
        $size = rand($font_size*2-2,$font_size*2+2);
        $x = ($i+1)*$font_size + rand(1,5);
        $y = ($height*0.7) + rand(0,5);
        $code[] = $letter;
        imagettftext($src,$size,rand(0,15),$x,$y,$color,$font,$letter);
    }

    $code = implode("",$code);

    $key = getHashByCode($code);
    $imgFolder = 'img/captcha/';
    $ext = 'png';

    $imgPath = $imgFolder . $key . '.' . $ext;

    imagepng($src, ROOT . '/' . $imgPath);
    imagedestroy($src);

    header ("Content-type: text/html");

    echo '<div style="border: 1px solid grey; display: block; float: left; padding: 5px;">'
        . '<img src="' . API_SERVER_NAME . '/' . $imgPath . '" style="float: left; border: 1px solid grey;"/>'
        . '<div style="float: left; margin: 0px 0px 0px 10px; padding: 0px;">'
        . '<p style="color: grey; margin: 0px; padding: 0px; font-family: Verdana; font-size: 11px;">type the code here:</p>'
        . '<input type="hidden" name="key" value="' .$key. '" />'
        . '<input type="text" name="code" value="" style="float: left; font-size: 14px; margin: 3px 0px 0px; width: 109px; padding: 2px 5px;"/>'
        . '</div>'
        . '</div>';
}

function checkCaptcha(){

    if(!array_key_exists('key', $_POST) || !array_key_exists('code', $_POST)) {
        $response = array('status' => 'faild', 'errors' => 'Empty key or code');
        sendResponse($response);
    }

    $key  = $_POST['key'];
    $code = $_POST['code'];

    if (!checkCaptchaCode($key, $code)) {
        $response = array('status' => 'faild', 'errors' => 'Captcha code is not correct');
        sendResponse($response);
    }

    $response = array('status' => 'success');
    sendResponse($response);
}

function checkCaptchaCode($key, $code) {

    $hashByCode = getHashByCode($code);

    if ($hashByCode != $key) {
        return false;
    }

    return true;
}

function sendResponse($response){
    exit(json_encode($response));
}

function getHashByCode($code){
    return sha1($code);
}