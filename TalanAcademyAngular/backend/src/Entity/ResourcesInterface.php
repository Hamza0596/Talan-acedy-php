<?php


namespace App\Entity;


interface ResourcesInterface
{
    public function getId();

    public function getUrl();

    public function setUrl(string $url);

    public function getRef();

    public function setRef(string $ref);

    public function getTitle();

    public function setTitle(string $title);

    public function serializer();

    public function getDay();

    public function setDay(DayInterface $day);

}
