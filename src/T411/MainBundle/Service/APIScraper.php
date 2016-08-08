<?php


namespace T411\MainBundle\Service;

use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\CacheInterface;
use Exception;
use RuntimeException;
use T411\MainBundle\Entity\Category;
use T411\MainBundle\Entity\Torrent;
use Zend\Http\ClientStatic;

class HostUnreachable extends Exception
{
    protected $message = 'scraper.error.host-hunreachable';
}

/**
 * Class APIScraper.
 *
 * @package   T411\MainBundle\Service
 */
class APIScraper
{
    /**
     * APIScraper constructor.
     *
     * @param CacheInterface $cache
     * @param string         $t411Url
     * @param string         $username
     * @param string         $password
     */
    public function __construct(CacheInterface $cache, $t411Url, $username, $password)
    {
        $this->cache = $cache;
        $this->t411Url = rtrim($t411Url,'/');
        $this->t411Username = $username;
        $this->t411Password = $password;
        $this->login();
    }

    /**
     * Authentificate to gather a token.
     *
     * @return boolean
     */
    public function login()
    {
        if (!$this->isValidToken()) {
            $response = $this->callJson(
                '/auth',
                [
                    'username' => $this->t411Username,
                    'password' => $this->t411Password
                ],
                'POST'
            );

            if (isset($response->token) && !empty($response->token)) {
                $this->token = $response->token;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @param          $keywords
     * @param int|null $category
     *
     * @return Torrent[]
     */
    public function search($keywords, $category = 0)
    {
        $params = array(
            'offset' => 0,
            'limit'  => 100
        );

        if ($category > 0) {
            $params['cid'] = intval($category);
        }

        $results = $this->callJson('/torrents/search/' . $keywords, $params);

        $torrents = [];
        if (isset($results->torrents)) {
            foreach ($results->torrents as $result) {
                $torrent = new Torrent();
                $torrent->setFromJson($result);
                $torrents [] = $torrent;
            }
        }

        return $torrents;
    }

    /**
     * Download torrent
     *
     * @param integer $tid Torrent id.
     *
     * @return Torrent
     */
    public function download($tid)
    {
        $torrent = $this->getTorrentInfos($tid);
        $content = $this->requestGET($this->t411Url . '/torrents/download/' . intval($tid));
        $torrent->setContent($content);

        return $torrent;
    }

    public function getTorrentInfos($tid)
    {
        $torrent_json = $this->callJson('/torrents/details/' . intval($tid));
        $torrent = new Torrent();
        $torrent->setFromJson($torrent_json);

        return $torrent;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        $categories = $this->cache->get('categories');
        if ($categories) {
            return $categories;
        }

        $json_cats = $this->callJson('/categories/tree/');

        /** @var Category $categories */
        $categories = [];
        foreach ($json_cats as $cid => $cat) {
            if (in_array($cid, [210, 233, 395, 404, 624])) {

                $newCategory = new Category();
                $newCategory->setFromJson($cat);

                $categories[] = $newCategory;
            }
        }

        $this->cache->set('categories', $categories);

        return $categories;
    }

    /**
     * @return Torrent[]
     */
    public function getTop100()
    {
        $this->cache->getAdapter()->setOption('ttl', 3600);
        if ($this->cache->get('top100')) {
            $results = $this->cache->get('top100');
        } else {
            $results = $this->callJson('/torrents/top/100');
            $this->cache->set('top100', $results);
        }

        $torrents = [];
        foreach ($results as $result) {
            $torrent = new Torrent();
            $torrent->setFromJson($result);
            $torrents [] = $torrent;
        }

        return $torrents;
    }

    /**
     * Check if the token is still available.
     *
     * @return boolean
     */
    private function isValidToken()
    {
        if (empty($this->token)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $action
     * @param array  $params
     * @param string $method
     *
     * @return mixed
     */
    public function callJson($action, $params = array(), $method = 'GET')
    {
        if ($method == 'GET') {
            $response = json_decode($this->requestGET($this->t411Url . $action, $params));
        } else {
            $response = json_decode($this->requestPOST($this->t411Url . $action, $params));
        }

        return $response;
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @throws HostUnreachable
     * @return string
     */
    public function requestPOST($url, $params)
    {
        try {
            $response = ClientStatic::post($url, $params, $this->getHeaders())->getBody();
        } catch (RuntimeException $e) {
            throw new HostUnreachable($e->getMessage());
        }

        return $response;
    }

    /**
     * @param string $url
     *
     * @param array  $params
     *
     * @throws HostUnreachable
     * @return string
     */
    public function requestGET($url, $params = array())
    {
        try {
            $response = ClientStatic::get($url, $params, $this->getHeaders())->getBody();
        } catch (RuntimeException $e) {
            throw new HostUnreachable();
        }

        return $response;
    }

    /**
     * @return array
     */
    private function getHeaders()
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36' . ' (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36'
        ];

        if ($this->token) {
            $headers['Authorization'] = $this->token;
        }

        return $headers;
    }

    /**
     * @var null
     */
    private $t411Username = null;

    /**
     * @var null
     */
    private $t411Password = null;


    /**
     * @var null
     */
    private $t411Url = null;
    /**
     * Token for connection.
     *
     * @var null|string
     */
    private $token = null;

    /**
     *
     * @var Cache
     */
    private $cache;
}
