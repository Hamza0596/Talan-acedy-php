<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 21/06/2019
 * Time: 16:39
 */

namespace App\Tests\ServiceTest;


use App\Service\ToolBoxService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ToolBoxServiceTest extends WebTestCase
{
    public function testToolBox()
    {
        $service = new ToolBoxService();
        $string = $service->replaceSpecialChars('test{}string');
        $this->assertEquals('test__string', $string);

    }
}