<?php

namespace T411\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestControllerTest extends WebTestCase
{
    public function testSearch()
    {
        $client = static::createClient();

        $client->request('GET', '/rest/search');
    }

}
