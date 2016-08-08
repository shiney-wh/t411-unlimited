<?php

namespace T411\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use T411\MainBundle\Service\APIScraper;
use T411\MainBundle\Service\HostUnreachable;
use Zend\Http\ClientStatic;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('T411MainBundle:Default:index.html.twig');
    }

    public function top100Action()
    {
        /** @var APIScraper $scraper */
        $scraper = $this->get('scraper');
        $torrents = $scraper->getTop100();

        return $this->render(
            'T411MainBundle:Default:top100.html.twig',
            array(
                'torrents' => $torrents
            )
        );
    }

    public function suggestMovieAction()
    {
        return $this->render(
            'T411MainBundle:Default:suggestMovie.html.twig',
            ['genres' => $this->get('tmdb')->getGenres()]
        );
    }

    public function downloadAction(Request $request, $tid)
    {
        /** @var APIScraper $scraper */
        $scraper = $this->get('scraper');
        $scraper->login();

        $torrent = $scraper->download($tid);
        $appAnnounce = $request->getScheme() . ':' . $this->generateUrl('t411_main_announce', [], UrlGeneratorInterface::NETWORK_PATH);
        $torrent->setTracker($appAnnounce);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-bittorrent');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $torrent->getName() . '.torrent"');
        $response->setContent($torrent->getContent());

        return $response;
    }

    /**
     * Redirect to the t411 information page
     *
     * @param $tid
     *
     * @return RedirectResponse
     */
    public function ficheAction($tid)
    {
        /** @var APIScraper $scraper */
        $scraper = $this->get('scraper');
        $scraper->login();

        $torrent = $scraper->getTorrentInfos($tid);

        return $this->redirect($this->get('service_container')->getParameter('t411_uri') . 'torrents/' . $torrent->getRewriteName());
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function announceAction(Request $request) {
        $gets               = $request->query->all();
        $gets['event']      = 'complet';
        $gets['downloaded'] = 0;
        $response           = ClientStatic::get($this->geUrlAnnounce(), $gets)->getBody();

        return new Response($response);
    }

    /**
     * @return mixed
     */
    private function geUrlAnnounce() {
        $container = $this->get('service_container');
        return $container->getParameter('t411_tracker') . "/" . $container->getParameter('t411_cheated_key') . "/announce";
    }
}
