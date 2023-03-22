<?php
/**
 * Created by PhpStorm.
 * User: wmhamdi
 * Date: 10/04/2019
 * Time: 08:38
 */


namespace App\Service;


class LevelProfileService
{
    public function levelProfile($user)
    {

        $allowed = ["\x00App\Entity\User\x00id",
            "\x00App\Entity\User\x00email",
            "\x00App\Entity\User\x00roles",
            "\x00App\Entity\User\x00password",
            "\x00App\Entity\User\x00isActivated",
            "\x00App\Entity\User\x00token",
            "\x00App\Entity\User\x00newEmail",
            "\x00App\Entity\User\x00registrationDate",
            "\x00App\Entity\Student\x00session",
            "\x00App\Entity\Student\x00userType",
            "\x00App\Entity\Student\x00status",
            "\x00App\Entity\User\x00image"];
        $attributeProfile = array_filter((array)$user, function ($var) use ($allowed) {
            return !(in_array($var, $allowed));
        },
            ARRAY_FILTER_USE_KEY);
        $attributeNotNullProfile = (array_filter($attributeProfile, function ($var) {
            return $var == !null || $var == !"";
        }));
        return (int)((count($attributeNotNullProfile) / count($attributeProfile)) * 100);
    }


    public function levelCandidature($candidature)
    {
        $allowed = ["\x00App\Entity\Candidature\x00id",
        "\x00App\Entity\Candidature\x00status",
        "\x00App\Entity\Candidature\x00datePostule",
        "\x00App\Entity\Candidature\x00cursus",
        "\x00App\Entity\Candidature\x00candidat",
        "\x00App\Entity\Candidature\x00candidatureStates",
        "\x00App\Entity\Candidature\x00sessionUserData",
        "\x00App\Entity\Candidature\x00linkLinkedin",
        "\x00App\Entity\Candidature\x00preparcoursCandidate",
            ];
        $attributeCandidature = array_filter((array)$candidature, function ($var) use ($allowed) {
            return !(in_array($var, $allowed));
        },
            ARRAY_FILTER_USE_KEY);

        $attributeNotNullCandidature = (array_filter($attributeCandidature, function ($var) {
            return $var == !null || $var == !"" || $var === false;
        }));
        return (int)((count($attributeNotNullCandidature) / count($attributeCandidature)) * 100);

    }
}
