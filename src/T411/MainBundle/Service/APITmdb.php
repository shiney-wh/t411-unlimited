<?php


namespace T411\MainBundle\Service;

use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\CacheInterface;

use Tmdb\ApiToken;
use Tmdb\Client;

use Exception;

class HostUnreachable extends Exception
{
    protected $message = 'scraper.error.host-hunreachable';
}

/**
 * Class APIScraper.
 *
 * @package   T411\MainBundle\Service
 */
class APITmdb
{
    public function __construct(CacheInterface $cache, $key)
    {
        $this->key = $key;
        $this->cache = $cache;
    }

    public function getGenres()
    {
        if ($this->cache->has('tmdb-genres')) {
            $genres = $this->cache->get('tmdb-genres');
        } else {
            $client = new Client(new ApiToken($this->key));
            $genres = $client->getGenresApi()->getGenres(['language' => 'fr'])['genres'];
            $this->cache->set('tmdb-genres', $genres);
        }

        return $genres;
    }

    public function getSuggestions($options)
    {
        $keyCache = "tmdb-suggestions-" . sha1(http_build_query($options));
        if($this->cache->has($keyCache)) {
            return $this->cache->get($keyCache);
        }


        $client = new Client(new ApiToken($this->key));
        $params = [
            'language'                 => 'fr',
            'vote_count.gte'           => 35
        ];

        $params['page'] = isset($options['page'])
            ? $options['page']
            : 1;
        $params['vote_average.gte'] = isset($options['mark'])
            ? $options['mark']
            : 1;
        $params['primary_release_date.gte'] = isset($options['year'])
            ? $options['year'] . '-01-01'
            : '1995-01-01';
        if(isset($options['genres']) && count($options['genres']) > 0){
            $params['with_genres'] = implode(',', $options['genres']);
        }

        $response = $client->getDiscoverApi()->discoverMovies($params);
        $this->cache->set($keyCache, $response);
        
        return $response;
    }

    /**
     * Token for connection.
     *
     * @var null|string
     */
    private $key = null;

    /**
     *
     * @var Cache
     */
    private $cache;
}
