<?php


namespace App\Entity;


interface CurriculumInterface
{
    public function addModule(ModuleInterface $module);
    public function getModules();
    public function removeModule(ModuleInterface $module);
}
