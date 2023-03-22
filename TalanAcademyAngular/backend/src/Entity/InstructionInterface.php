<?php


namespace App\Entity;


interface InstructionInterface
{
    public function getId();

    public function getDescription();

    public function setDescription(string $description);

    public function getScale();

    public function setScale(int $scale);


    public function getRef();

    public function setRef(?string $ref);

    public function getDayCourse();

    public function setDayCourse(?DayInterface $dayCourse);

    public function serializer();
}
