<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class AlbumController
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class AlbumController extends Controller
{

    /**
     * @ParamConverter("album", class="App:Album")
     * @Route("/api/albums/{id}/link", options={"expose"=true}, name="album_link_to_images")
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function linkAlbumAction(Request $request, Album $album)
    {
        $data = json_decode($request->getContent(), true);
        if (!$data[0]['images']) {
            throw new NotFoundHttpException("No correct data send");
        }

        foreach ($data[0]['images'] as $data_image) {
            if(!$data_image['id']) {
                throw new NotFoundHttpException('No correct data send');
            }

            $image = $this->getDoctrine()
                ->getRepository(Image::class)
                ->findOneById($data_image['id']);

            if(!$image) {
                throw new NotFoundHttpException('No image found');
            }

            $minus_album = $image->getAlbum();
            if($minus_album && ($minus_amount = $minus_album->getAmount()) > 0) {
                $minus_album->setAmount($minus_amount - 1);
            }


            $image->setAlbum($album);
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);

            $amount = $album->getAmount() + 1;
            $album->setAmount($amount);
            $em->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/api/albums/create", options={"expose"=true}, name="albums_create")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createAlbumAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (!$data['category']) {
            throw new NotFoundHttpException("No correct data send");
        }

        $album = $this->getDoctrine()
            ->getRepository(Album::class)
            ->findOneByName($data['category']);

        if($album) {
            throw new NotFoundHttpException('Category already exists.');
        }

        $album = new Album();
        $album->setName($data['category']);
        $album->setAmount(0);
        $album->setUpdatedAt(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($album);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @ParamConverter("album", class="App:Album")
     * @Route("/api/albums/{id}/images", options={"expose"=true}, name="album_images")
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\Serializer\Exception\NotEncodableValueException
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     */
    public function viewAlbumImagesAction(Request $request, Album $album)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['from'], $data['max'])) {
            throw new NotFoundHttpException("No from/max set");
        }

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        return new JsonResponse(
            $serializer->serialize(
                $this->getDoctrine()->getRepository(Image::class)->findAlbumPage($data['from'], $data['max'], $album->getId()),
                'json'
            )
        );
    }

    /**
     *  @Route("/api/albums", options={"expose"=true}, name="all_albums")
     */
    public function albumsAction()
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        return new JsonResponse(
            $serializer->serialize(
                $this->getDoctrine()->getRepository(Album::class)->findAll(),
                'json'
            )
        );
    }

}