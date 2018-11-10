<?php

namespace App\Controller;

use App\Entity\Image;
use App\Service\ImageFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Class ImageController
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class ImageController extends Controller
{
    /**
     * @Route(
     *     "/images/{image}",
     *     options={"expose"=true},
     *     requirements={"image"=".+"},
     *     name="image_show")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showImageAction(Request $request)
    {
        $image_name_raw = $request->attributes->get('image');

        if (!$image_name_raw) {
            throw new NotFoundHttpException("No attribute found.");
        }

        $uploadDir = $this->get('kernel')->getProjectDir().'/public/image/';

        $name = $uploadDir.$image_name_raw;

        try {
            $fp = fopen($name, 'rb');
        } catch (NotFoundHttpException $e) {
           throw new NotFoundHttpException($e->getMessage(), 404);
        }


        header("Content-Type: image/jpeg");
        header("Content-Length: " . filesize($name));

        fpassthru($fp);
        exit;
    }

    /**
     * @Route("/api/images", options={"expose"=true}, name="gallery_images")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool|\Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\NotEncodableValueException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     */
    public function imagesAction(Request $request)
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
                $this->getDoctrine()->getRepository(Image::class)->findFrontPage($data['from'], $data['max'], isset($data['album']) ? $data['album'] : null),
                'json'
            )
        );
    }

    /**
     * @Route("/api/images/unlinked", options={"expose"=true}, name="gallery_images_unlinked")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\NotEncodableValueException
     * @throws \UnexpectedValueException
     */
    public function imagesAlbumNull()
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        return new JsonResponse(
            $serializer->serialize(
                $this->getDoctrine()->getRepository(Image::class)->findBy(['album' => null]),
                'json'
            )
        );
    }

    /**
     * @Route("/api/images/unlinked/count", options={"expose"=true}, name="gallery_images_unlinked_count")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\NotEncodableValueException
     * @throws \UnexpectedValueException
     */
    public function countImagesAlbumNull()
    {
        return new JsonResponse(
                $this->getDoctrine()->getRepository(Image::class)->countAllAlbumNull()
        );

    }

    /**
     * @Route("/api/image/upload", name="upload_image")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\ImageFunctions $image_functions
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function imageUploadAction(Request $request, ImageFunctions $image_functions)
    {
        $output = array('success' => false);
        $file = $request->files->get('file');

        if (!$file) {
            throw new NotFoundHttpException('No file found');
        }

        $fileName = md5(uniqid()) . '.JPG';

        $imageDir = $this->get('kernel')->getProjectDir() . '/public/image';
        $uploadDir = $imageDir.'/raw/';

        if (!$file->move($uploadDir, $fileName)) {
            $output['message'] = 'File couldn\'t be moved';
            return new JsonResponse($output);
        }

        if (!$image_functions->resizeImage($uploadDir . $fileName, $imageDir . '/resize/' . $fileName)) {
            $output['message'] = 'Couldn\'t resize the image';
            return new JsonResponse($output);
        }

        $file = new \Symfony\Component\HttpFoundation\File\File($imageDir. '/resize/' . $fileName);
        if (!$this->createThumbnail($imageDir, $fileName)) {
            $output['message'] = 'Couldn\'t create a thumbnail';
            return new JsonResponse($output);
        }

        $image = new Image();
        $image->setFilename($file->getFilename());
        $image->setPath($file->getPath());
        $image->setExtensions([$file->getExtension()]);
        $image->setUploadedAt(new \DateTime(date("Y-m-d H:i:s", $file->getCTime())));
        $image->setPrivate(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($image);
        $em->flush();

        $output['success'] = true;
        $output['fileName'] = $fileName;
        $output['id'] = $image->getId();

        return new JsonResponse($output);
    }

    /**
     * @param $image_path
     * @param $file_name
     * @return bool
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    private function createThumbnail($image_path, $file_name)
    {
        $dataManager = $this->container->get('liip_imagine.data.manager');
        $filterManager = $this->container->get('liip_imagine.filter.manager');
        $image = $dataManager->find('my_ratio_down_scale_filter', $file_name);
        $response = $filterManager->applyFilter($image, 'my_thumb');

        $f = fopen($image_path . '/thumb/' . $file_name, 'w');
        fwrite($f, $response->getContent());
        fclose($f);

        return file_exists($image_path . '/thumb/' . $file_name);
    }
}