<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 29/08/2019
 * Time: 17:37
 */

namespace App\Entity;


interface ProjectSubjectInterface
{
    public function getId(): ?int;

    public function getName(): ?string;

    public function setName(string $name);

    public function getSpecification(): ?string;

    public function setSpecification(?string $specification);

    public function getProject();

    public function setProject(ModuleInterface $module);

    public function getRef(): ?string;

    public function setRef(string $ref);

}