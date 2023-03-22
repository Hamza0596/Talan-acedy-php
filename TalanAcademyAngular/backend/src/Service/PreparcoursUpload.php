<?php


namespace App\Service;
use App\Entity\Preparcours;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PreparcoursUpload
{
    private $targetDirectory;
    private $fileSystem;

    public function __construct($targetDirectory, Filesystem $filesystem)
    {

        $this->targetDirectory = $targetDirectory;
        $this->fileSystem = $filesystem;
    }

    public function upload(UploadedFile $file, Preparcours $preparcours)
    {

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        if ($preparcours->getPDF()) {
            try {
                $this->fileSystem->remove($preparcours->getPDF());
            } catch (FileException $e) {
                $e->getMessage();
            }
        }

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            $e->getMessage();
        }


        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}

