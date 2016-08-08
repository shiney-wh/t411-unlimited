<?php

namespace T411\MainBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use T411\MainBundle\Service\APIScraper;

class RestController extends Controller
{
    public function torrentsAction(Request $request)
    {
        try {
            /** @var APIScraper $scraper */
            $scraper = $this->get('scraper');
            $scraper->login();
            $torrents = $scraper->search(
                $request->get('q', ''),
                $request->get('cat', 0)
            );

            $torrentsArray = array();
            foreach ($torrents as $torrent) {
                $torrentsArray [] = $torrent->toArray();
            }

            return $this->sendJsonSuccess('results', $torrentsArray);
        } catch (Exception $e) {
            return $this->sendJsonError('error', "T411 est indisponible. Réessayez plus tard. {$e->getMessage()}");
        }
    }

    public function suggestMoviesAction(Request $request)
    {

        $genres = [];
        foreach($request->get('genres', []) as $genre) {
            if(!empty($genre) && 'undefined' !== $genre) {
                $genres []= $genre;
            }
        }

        $options = [
            'page'  => $request->get('page', 1),
            'mark'   => $request->get('mark', 1),
            'year'   => $request->get('year', 1995),
            'genres' => $genres
        ];



        $response = $this->get('tmdb')->getSuggestions($options);

        return $this->sendJsonSuccess(
            'results',
            [
                'movies'      => $response['results'],
                'page'        => $response['page'],
                'total_pages' => $response['total_pages']
            ]
        );
    }

    /**
     * Send Error response json encoded.
     *
     * @param string       $message Error message.
     * @param string|array $data    Optional Array datas.
     *
     * @return Response
     */
    protected function sendJsonError($message, $data = null)
    {
        return $this->sendJson(false, $message, $data);
    }

    /**
     * Send Success response json encoded.
     *
     * @param string       $message Success message.
     * @param string|array $data    Optional Array datas.
     *
     * @return Response
     */
    protected function sendJsonSuccess($message, $data = null)
    {
        return $this->sendJson(true, $message, $data);
    }

    /**
     * Send Json encoded Response.
     *
     * @param boolean      $success Is a success Json.
     * @param string       $message Success message.
     * @param array|string $data    Optional Array datas.
     *
     * @return Response
     */
    protected function sendJson($success, $message, $data = null)
    {
        $jsonMessage = json_encode(
            array(
                'success' => (bool) $success,
                'message' => $message,
                'data'    => $data
            )
        );

        $response = new Response($jsonMessage);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        if (!$success) {
            $response->setStatusCode(500);
        }

        return $response;
    }
}
