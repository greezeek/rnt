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

    $name = uniqid(mt_rand(), true) . '.jpg';
    $path = sys_get_temp_dir() . '/' . $name;
    
    shell_exec(sprintf('convert "%s" -resize 500 "%s" ', $file->getRealPath(), $path));

    return new UploadedFile($path, $name);
});

$c['generate.gif'] = $c->protect(function ($id, $startTs, $endTs) use ($c) {

    if ($startTs > $endTs) {
        throw new \Exception(sprintf('Start "%s" cannot be greater or equal End "%s" ', $startTs, $endTs));
    }

    /** @var \Doctrine\DBAL\Connection $db */
    $db = $c['db'];
    $stmt = $db->executeQuery('SELECT session_id, name FROM media WHERE :start <= date AND date <= :end AND session_id = :id ORDER BY date', [
        'start' => $startTs,
        'end' => $endTs,
        'id' => $id
    ]);
    
    $name = null;
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($media) !== 0) {
        $animation = new Imagick();
        $animation->setFormat("GIF");

        foreach ($media as $photo) {
            $frame = new Imagick($c['img_dir'] . $photo['name']);
            $frame->thumbnailImage(400, 300);
            $animation->addImage($frame);
            $animation->setImageDelay(50);
            $animation->nextImage();
        }

        $name = uniqid(mt_rand(), true) . '.gif';
        $animation->writeImages($c['img_dir'] . $name, true);
    }
    
    return $name;
});

$c['generate.thumb'] = $c->protect(function($id, $startTs, $endTs) use ($c) {
    
    /** @var \Doctrine\DBAL\Connection $db */
    $db = $c['db'];
    $stmt = $db->executeQuery('SELECT name FROM media WHERE :start <= date AND date <= :end AND session_id = :id ORDER BY date LIMIT 1', [
        'start' => $startTs,
        'end' => $endTs,
        'id' => $id
    ]);
    $name = $stmt->fetchColumn();
    $frame = new Imagick($c['img_dir'] . $name);
    $frame->thumbnailImage(412, 232);

    $name = uniqid(mt_rand(), true) . '.jpg';
    $frame->writeImages($c['img_dir'] . $name, true);
    
    return $name;
});

//$c['generate.thumb'](15, new \DateTime('2015-01-01 00:00:00'), new \DateTime('2016-12-01 23:59:59'));
//$c['generate.gif'](15, new \DateTime('2015-01-01 00:00:00'), new \DateTime('2016-12-01 23:59:59'));