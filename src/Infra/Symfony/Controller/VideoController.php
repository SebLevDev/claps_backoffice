<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Persistance\Doctrine\Entity\Video;
use Infra\Symfony\Form\Type\SearchVideoType;
use Infra\Symfony\Persistance\Doctrine\Repository\EventRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\VideoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/video')]
class VideoController extends BaseController
{
    #[Route('/', name:'app_video_index')]
    public function indexAction(VideoRepository $videoRepository): Response
    {
        $countries = $videoRepository->getCountryList();
        $form = $this->createForm(SearchVideoType::class, null, [
            'countries' => $countries
        ]);
        $videos = $videoRepository->findLastVideos(500);

        return $this->render('member/video/index.html.twig', [
            'videos' => $videos,
            'searchVideoform' => $form->createView(),
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    #[Route('/search', name:'app_video_search')]
    public function searchAction(VideoRepository $videoRepository): Response
    {
        $form = $this->createForm(SearchVideoType::class);
        $videos = $videoRepository->filterAll($this->getSqlParameterBag());

        return $this->render('member/video/search.html.twig', [
            'videos' => $videos,
            'searchVideoform' => $form->createView(),
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    #[Route('/{id}', name:'app_video_show', requirements: ['id' => '\d+'])]
    public function showAction(Video $video): Response
    {
        return $this->render('member/video/show.html.twig', [
            'video' => $video,
            'breadcrumb' => $this->getBreadcurmb()
        ]);
    }

    private function getBreadcurmb(): array
    {
        $breadcrumb = [];
        $breadcrumb['items'][] = ['title'=> 'Home', 'url' => '/'];
        $breadcrumb['items'][] = ['title'=> 'Media', 'url' => $this->generateUrl('app_media_index')];
        $breadcrumb['items'][] = ['title'=> 'Video'];

        return $breadcrumb;
    }

    #[Route('/feed', name:'app_video_feed')]
    public function feedAction(EventRepository $eventRepository): JsonResponse
    {
        $data = [];
        $events = $eventRepository->findBy([], ['date' => 'DESC']);

        foreach ($events as $event) {
            if (!$event->getVideos()->isEmpty()) {
                $eventData = [
                    'id' => $event->getId(),
                    'title' => $event->getName(),
                    'description' => $event->getVenue() .' '. $event->getDate()?->format('C'),
                    'image' => "https://www.vilagfa.be/fr/wp-content/uploads/sites/2/2015/05/plancher1.jpg",
                ];
                foreach ($event->getVideos() as $video) {
                    $videoData = [
                        'id' => $video->getId(),
                        'title' => $video->getName(),
                        'youtube' => $video->getUrl(),
                        'director' => $video->getCountry(),
                        'year' => 2022,
                        'ratings' => null,
                        'duration' => 780,
                        'description' => $event->getVenue() .' '. $event->getDate()?->format('C'),
                        'url' => "https://android-tv-classics.firebaseapp.com/content/le_voyage_dans_la_lun/media_le_voyage_dans_la_lun.mp4",
                        'art' => "https://img.youtube.com/vi/" .$video->getYoutubeId(). "/sddefault.jpg",
                    ];
                    $eventData['items'][] = $videoData;
                }
                $data['feed'][] = $eventData;
            }

        }
        $data['backgrounds'][] = 'https://www.vilagfa.be/fr/wp-content/uploads/sites/2/2015/05/plancher1.jpg';
        $data['backgrounds'][] = 'https://www.vilagfa.be/fr/wp-content/uploads/sites/2/2015/05/plancher1.jpg';
        $data['backgrounds'][] = 'https://www.vilagfa.be/fr/wp-content/uploads/sites/2/2015/05/plancher1.jpg';

        return new JsonResponse($data);
    }
}
