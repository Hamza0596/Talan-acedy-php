<?php


namespace App\Service;

use App\Entity\Candidature;
use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CvUploadService
{
    private $targetDirectory;
    private $fileSystem;

    public function __construct($targetDirectory, Filesystem $filesystem)
    {
        $this->targetDirectory = $targetDirectory;
        $this->fileSystem = $filesystem;
    }

    public function upload(UploadedFile $file, User $user, Candidature $candidature)
    {
        $fileName = $user->getFirstName() . '_' . $user->getLastName()  . '_' . $candidature->getId() . '.' . $file->guessExtension();

        if ($candidature->getCv()) {
            try {
                $this->fileSystem->remove($candidature->getCv());
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
