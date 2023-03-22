<?php


namespace App\Service;

use App\Entity\User;
use Intervention\Image\ImageManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUserUploadService
{
    private $targetDirectory;
    private $fileSystem;
    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct($targetDirectory, Filesystem $filesystem,ParameterBagInterface $params)
    {
        $this->targetDirectory = $targetDirectory;
        $this->fileSystem = $filesystem;
        $this->params = $params;
    }

    public function upload(UploadedFile $file, User $user)
    {
        $fileName = $user->getLastName() . '_' . $user->getFirstName() . '_' . $user->getId(). '.' . $file->guessExtension();

        if ($user->getImage()) {
            try {
                $this->fileSystem->remove($this->params->get('image_user_directory').$user->getImage());
            } catch (FileException $e) {
                $e->getMessage();
            }
        }
        try {
            $file->move($this->getTargetDirectory(), $fileName);
            $imageManager = new ImageManager();
            $image = $imageManager->make($this->params->get('image_user_directory').$fileName);
            $image->resize(450, 400);
            $image->save($this->params->get('image_user_directory') . $fileName);
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
