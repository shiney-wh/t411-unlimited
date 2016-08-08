<?php

namespace T411\MainBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Zend\Http\ClientStatic;

/**
 * Class TrackerTest.
 *
 * @group     health
 * @package   T411\MainBundle\Tests
 */
class TrackerTest extends WebTestCase
{

    public static function setUpBeforeClass()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        self::$container = $kernel->getContainer();
        self::$tracker = self::$container->getParameter('cheated_tracker');
    }

    public function testTrackerConnexion()
    {
        try {
            $response = ClientStatic::get(self::$tracker);
            $this->assertEquals(404, $response->getStatusCode());
        } catch (\Exception $e) {
            $this->fail("Connexion au tracker impossible " .  self::$tracker);
        }
    }

    /**
     * @var Container
     */
    private static $container;

    /**
     * @var string
     */
    private static $tracker;
}
