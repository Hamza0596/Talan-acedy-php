<?php


namespace App\Entity;


interface DayInterface
{
    const VALIDATING_DAY = 'jour-validant';
    const CORRECTION_DAY = 'jour-correction';
    const NORMAL_DAY = 'jour-normal';

    public function __construct();

    public function getId(): ?int;

    public function getDescription(): ?string;

    public function setDescription(string $description);

    public function getStatus(): ?string;

    public function setStatus(string $status);

    public function getReference(): ?string;

    public function getSynopsis(): ?string;

    public function setSynopsis(?string $synopsis);

    public function setReference(string $reference);

    public function getOrdre();

    public function setOrdre($ordre): void;

    public function getModule(): ?ModuleInterface;

    public function setModule(?ModuleInterface $module);

    public function getResources();

    public function addResource(ResourcesInterface $resource);

    public function removeResource(ResourcesInterface $resource);

    public function getActivityCourses();

    public function addActivityCourses(ActivityInterface $activityCursus);

    public function removeActivityCourses(ActivityInterface $activityCursus);

    public function getOrders();

    public function addOrder(InstructionInterface $order);

    public function removeOrder(InstructionInterface $order);

}
