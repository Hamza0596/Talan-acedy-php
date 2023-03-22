<?php


namespace App\Entity;


interface ModuleInterface
{
    public function serializer();

    public function __construct();

    public function getId(): ?int;

    public function getTitle(): ?string;

    public function setTitle(string $title);

    public function getRef(): ?string;

    public function setRef(string $ref);

    public function getDuration(): ?int;

    public function setDuration(?int $duration);

    public function getType(): ?string;

    public function setType(?string $type);

    public function getDescription(): ?string;

    public function setDescription(string $description);

    public function addDayCourse(DayInterface $dayCourse);

    public function removeDayCourse(DayInterface $dayCourse);

    public function getOrderModule(): ?int;

    public function setOrderModule(int $orderModule);

    public function getDayCourses();
}
