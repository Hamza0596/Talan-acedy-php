<?php


namespace App\Entity;


interface ActivityInterface
{
    public function getId();

    public function setId($id);

    public function getTitle();

    public function setTitle(string $title = null);

    public function getContent();

    public function setContent(string $content);

    public function getReference();

    public function setReference(string $reference);

    public function serializer();

    public function getDay();

    public function setDay(?DayInterface $day);

}
