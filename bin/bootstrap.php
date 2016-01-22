<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pimple\Container;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Finder\Finder;

$c = new Container();
$c['web_dav.host'] = 'http://rnt.test.shot.x340.org:5285/';
$c['web_dav.user'] = 'rnt';
$c['web_dav.pass'] = 'rntpass';
$c['img_dir'] = __DIR__ . '/../htdocs/images/';

$c['db'] = function () {
    return DriverManager::getConnection([
        'url' => 'mysql://rnt:rnt@rnt.test.shot.x340.org/rnt'
    ], new Configuration());
};

$c['upload'] = $c->protect(function (UploadedFile $file) use ($c) {
    $name = uniqid(mt_rand(), true) . '.' . $file->getClientOriginalExtension();
    $uploaded = $c['web_dav.host'] . $name;
    $fp = fopen($file->getRealPath(), 'r');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploaded);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_USERPWD, $c['web_dav.user'] . ':' . $c['web_dav.pass']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_PUT, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, $file->getSize());

    curl_exec($ch);
    $info = curl_getinfo($ch);

    curl_close($ch);
    fclose($fp);

    if ($info['http_code'] >= 300 || $info['http_code'] < 200) {
        throw new \Exception(sprintf('Failed to upload file "%s", http code "%s" ', $file->getClientOriginalName(), $info['http_code']));
    }

    return $name;
});

$c['find.photo'] = $c->protect(function ($dir) {
    $photos = [];
    $finder = new Finder();

    /** @var \Symfony\Component\Finder\SplFileInfo $file */
    foreach ($finder->in($dir)->files()->name('*.bmp') as $file) {
        $photos[] = $file;
    }

    return $photos;
});

$c['generate.jpg'] = $c->protect(function (UploadedFile $file) {
    $p_sFile = $file->getRealPath();

    //    Load the image into a string 
    $file = fopen($p_sFile, "rb");
    $read = fread($file, 10);
    while (!feof($file) && ($read <> ""))
        $read .= fread($file, 1024);

    $temp = unpack("H*", $read);
    $hex = $temp[1];
    $header = substr($hex, 0, 108);
    $width = $height = 0;

    //    Process the header 
    //    Structure: http://www.fastgraph.com/help/bmp_header_format.html 
    if (substr($header, 0, 4) == "424d") {
        //    Cut it in parts of 2 bytes 
        $header_parts = str_split($header, 2);

        //    Get the width        4 bytes 
        $width = hexdec($header_parts[19] . $header_parts[18]);

        //    Get the height        4 bytes 
        $height = hexdec($header_parts[23] . $header_parts[22]);

        //    Unset the header params 
        unset($header_parts);
    }

    //    Define starting X and Y 
    $x = 0;
    $y = 1;

    //    Create newimage 
    $image = imagecreatetruecolor($width, $height);

    //    Grab the body from the image 
    $body = substr($hex, 108);

    //    Calculate if padding at the end-line is needed 
    //    Divided by two to keep overview. 
    //    1 byte = 2 HEX-chars 
    $body_size = (strlen($body) / 2);
    $header_size = ($width * $height);

    //    Use end-line padding? Only when needed 
    $usePadding = ($body_size > ($header_size * 3) + 4);

    //    Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption 
    //    Calculate the next DWORD-position in the body 
    for ($i = 0; $i < $body_size; $i += 3) {
        //    Calculate line-ending and padding 
        if ($x >= $width) {
            //    If padding needed, ignore image-padding 
            //    Shift i to the ending of the current 32-bit-block 
            if ($usePadding)
                $i += $width % 4;

            //    Reset horizontal position 
            $x = 0;

            //    Raise the height-position (bottom-up) 
            $y++;

            //    Reached the image-height? Break the for-loop 
            if ($y > $height)
                break;
        }

        //    Calculation of the RGB-pixel (defined as BGR in image-data) 
        //    Define $i_pos as absolute position in the body 
        $i_pos = $i * 2;
        $r = hexdec($body[$i_pos + 4] . $body[$i_pos + 5]);
        $g = hexdec($body[$i_pos + 2] . $body[$i_pos + 3]);
        $b = hexdec($body[$i_pos] . $body[$i_pos + 1]);

        //    Calculate and draw the pixel 
        $color = imagecolorallocate($image, $r, $g, $b);
        imagesetpixel($image, $x, $height - $y, $color);

        //    Raise the horizontal position 
        $x++;
    }

    //    Unset the body / free the memory 
    unset($body);
    $name = uniqid(mt_rand(), true) . '.jpg';
    $path = sys_get_temp_dir() . '/' . $name;
    imagejpeg($image, $path, 95);

    return new UploadedFile($path, $name);
});

$c['generate.gif'] = $c->protect(function ($id, \DateTime $start, \DateTime $end) use ($c) {
    $startTs = $start->getTimestamp();
    $endTs = $end->getTimestamp();

    if ($startTs > $endTs) {
        throw new \Exception(sprintf('Start "%s" cannot be greater or equal End "%s" ', $startTs, $endTs));
    }

    /** @var \Doctrine\DBAL\Connection $db */
    $db = $c['db'];
<<<<<<< HEAD
    $stmt = $db->executeQuery('SELECT session_id, name FROM media WHERE :start <= date AND date <= :end AND session_id = :id ORDER BY id', [
        'start' => $startTs,
        'end' => $endTs,
        'id' => $id . 123
    ]);
    
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
=======
    $stmt = $db->executeQuery('SELECT session_id, name FROM media WHERE :start <= date AND date <= :end AND session_id = :id ORDER BY date', [
        'start' => $startTs,
        'end' => $endTs,
        'id' => $id
    ]);
    
    $name = null;
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
>>>>>>> bfa4bd89b4b4f8dff3ccaba36b21c0040c767c3a
    if (count($media) !== 0) {
        $animation = new Imagick();
        $animation->setFormat("GIF");

        foreach ($media as $photo) {
            $frame = new Imagick($c['img_dir'] . $photo['name']);
<<<<<<< HEAD
            $frame->thumbnailImage(176, 144);
=======
            $frame->thumbnailImage(400, 300);
>>>>>>> bfa4bd89b4b4f8dff3ccaba36b21c0040c767c3a
            $animation->addImage($frame);
            $animation->setImageDelay(100);
            $animation->nextImage();
        }

        $name = uniqid(mt_rand(), true) . '.gif';
        $animation->writeImages($c['img_dir'] . $name, true);
    }
<<<<<<< HEAD
});


$c['generate.gif'](1, new \DateTime('2015-01-01 00:00:00'), new \DateTime('2016-12-01 23:59:59'));
=======
    
    return $name;
});

//$c['generate.gif'](1, new \DateTime('2015-01-01 00:00:00'), new \DateTime('2016-12-01 23:59:59'));
>>>>>>> bfa4bd89b4b4f8dff3ccaba36b21c0040c767c3a
