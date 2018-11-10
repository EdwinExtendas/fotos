<?php

namespace App\Service;

/**
 * Class ImageFunctions
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class ImageFunctions
{
    /**
     * @param $source_image_path
     * @param $resize_image_path
     * @param int $MAX_WIDTH
     * @param int $MAX_HEIGHT
     * @return bool
     */
    public function resizeImage($source_image_path, $resize_image_path, $MAX_WIDTH = 1920, $MAX_HEIGHT = 1080)
    {

        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($source_image_path);
                break;
        }
        if ($source_gd_image === false) {
            return false;
        }

        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $MAX_WIDTH / $MAX_HEIGHT;
        if ($source_image_width <= $MAX_WIDTH && $source_image_height <= $MAX_HEIGHT) {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($MAX_HEIGHT * $source_aspect_ratio);
            $thumbnail_image_height = $MAX_HEIGHT;
        } else {
            $thumbnail_image_width = $MAX_WIDTH;
            $thumbnail_image_height = (int) ($MAX_WIDTH / $source_aspect_ratio);
        }

        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        imagejpeg($thumbnail_gd_image, $resize_image_path, 100);
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);

        return true;
    }
}