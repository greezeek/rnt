<?php

require_once 'bootstrap.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;

$console = new Application();
$console
    ->register('screen')
    ->setDefinition([
        new InputArgument('dir', InputArgument::REQUIRED, 'Screen directory name')
    ])
    ->setDescription('')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($c) {

        /** @var \Doctrine\DBAL\Connection $db */
        $db = $c['db'];
        $fs = new Filesystem();

        while (true) {
            /** @var \Symfony\Component\Finder\SplFileInfo[] $photos */
            $photos = $c['find.photo']($input->getArgument('dir'));
            
            if (count($photos) === 0) {
                $output->writeln('<info>Wait a photo file ...</info>');
                sleep(1);
                continue;
            }

            while (count($photos) !== 0) {
                foreach ($photos as $k => $file) {
                    $uploadedFile = new UploadedFile($file->getRealPath(), $file->getFilename());
                    $uploadedFile = $c['bmp2jpg']($uploadedFile);

                    try {
                        $name = $c['upload']($uploadedFile);
                        preg_match('/(\d{4}-\d{2}-\d{2}) (\d{2}-\d{2}-\d{2})/', $file->getFilename(), $match);
                        $id = $db->query('SELECT MAX(id) FROM session')->fetch(PDO::FETCH_COLUMN);
                        $created = new \DateTime($match[1] . ' ' . str_replace('-', ':', $match[2]));

                        $db->insert('media', [
                            'session_id' => $id,
                            'name' => $name,
                            'date' => $created->getTimestamp()
                        ]);

                        $fs->remove($file->getRealPath());
                        unset($photos[$k]);

                        $output->writeln(sprintf('<info>Process sid "%s", file "%s"</info>', $id, $file->getBasename()));
                    } catch (\Exception $e) {
                        $output->writeln('');
                        $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                        $output->writeln('');
                    }
                }
            }
        }
    });
$console->run();