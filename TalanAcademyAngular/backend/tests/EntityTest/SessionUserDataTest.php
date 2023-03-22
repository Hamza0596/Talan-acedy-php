<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

class SessionUserDataTest extends TestCase
{
        public function testSessionUserCreate()
        {
            $sessionUser = new SessionUserData();
            $session = new Session();
            $sessionUser->setSession($session);
            $candidature = new Candidature();
            $sessionUser->setCandidature($candidature);
            $user = new User();
            $sessionUser->setUser($user);
            $sessionUser->setNbrJoker(5);
            $sessionUser->setRepoGit(base64_encode('https://www.google.com/search?q=github&rlz=1C1GCEU'));
            $sessionUser->setRepoGit('https://www.google.com/search?q=github&rlz=1C1GCEU');
            $sessionUser->setProfilSlack(base64_encode('https://www.google.com/search?q=slack&rlz=1C1GCEU'));
            $sessionUser->setProfilSlack('https://www.google.com/search?q=slack&rlz=1C1GCEU');
            $sessionUser->setSubscriptionDate(new \DateTime('31-07-2019'));
            $sessionUser->setStatus('Apprenti');
            $sessionUser->setMission(true);
            $sessionUser->setInteractionSlack(1);
            $mentorsAppreciation = new MentorsAppreciation();
            $mentorsAppreciation1 = new MentorsAppreciation();
            $sessionUser->addMentorsAppreciation($mentorsAppreciation);
            $sessionUser->addMentorsAppreciation($mentorsAppreciation1);
            $sessionUser->removeMentorsAppreciation($mentorsAppreciation1);
            $this->assertEquals(1, $sessionUser->getMentorsAppreciations()->count());
            $this->assertInstanceOf(MentorsAppreciation::class,$sessionUser->getMentorsAppreciations()[0]);

            $this->assertEquals(null, $sessionUser->getId());
            $this->assertEquals(5, $sessionUser->getNbrJoker());
            $this->assertInstanceOf(Session::class, $sessionUser->getSession());
            $this->assertInstanceOf(Candidature::class, $sessionUser->getCandidature());
            $this->assertInstanceOf(User::class, $sessionUser->getUser());
            $this->assertEquals('Apprenti',$sessionUser->getStatus());
            $this->assertEquals('https://www.google.com/search?q=github&rlz=1C1GCEU',$sessionUser->getRepoGit());
            $this->assertEquals('https://www.google.com/search?q=slack&rlz=1C1GCEU',$sessionUser->getProfilSlack());
            $this->assertEquals(true,$sessionUser->getMission());
            $this->assertEquals(1,$sessionUser->getInteractionSlack());
            $this->assertEquals(new \DateTime('31-07-2019'),$sessionUser->getSubscriptionDate());
        }
}
