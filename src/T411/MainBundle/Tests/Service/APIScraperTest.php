<?php

namespace T411\MainBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use T411\MainBundle\Service\APIScraper;

/**
 * Class APIScraperTest.
 *
 * @package   T411\MainBundle\Tests\Services
 */
class APIScraperTest extends WebTestCase
{

    public static function setUpBeforeClass()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        self::$container = $kernel->getContainer();
    }

    public function setUp()
    {
        $this->scraper = self::$container->get('scraper');
    }

    /**
     * @group health
     *
     * @return void
     */
    public function testLogin()
    {
        $this->assertTrue($this->scraper->login());
    }

    /**
     * @group health
     *
     * @return void
     */
    public function testSimpleSearch()
    {
        $this->scraper->login();

        $torrents = $this->scraper->search('avatar');

        $this->assertGreaterThan(0, count($torrents));
        foreach ($torrents as $torrent) {
            $this->assertContains('avatar', strtolower($torrent->getName()));
        }
    }

    /**
     * @group health
     *
     * @return void
     */
    public function testSearchWithCategory()
    {
        $this->scraper->login();
        $expectedId = 433;

        $torrents = $this->scraper->search('harry', $expectedId);

        $this->assertGreaterThan(0, count($torrents));
        foreach ($torrents as $torrent) {
            $this->assertEquals(
                $expectedId,
                $torrent->getCategoryId(),
                "La recherche par livre à renvoyé un resultat qui n'en est pas un."
            );
        }
    }

    /**
     * @group health
     *
     * @return void
     */
    public function testCategories()
    {
        $this->scraper->login();

        $categories = $this->scraper->getCategories();

        $this->assertGreaterThan(0, count($categories));
    }

    /**
     * @var Container
     */
    private static $container;

    /**
     * @var null|APIScraper
     */
    private $scraper;
}
