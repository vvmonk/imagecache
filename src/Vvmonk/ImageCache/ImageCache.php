<?php

namespace Vvmonk\ImageCache;

use Intervention\Image\ImageManagerStatic as Image;

class ImageCache
{
    /**
     * Create new image (resize); return new image name or false
     *
     * @param $filename
     * @param $width
     * @param $height
     * @return mixed
     */
    public static function create($filename, $width, $height)
    {
        $images_path = self::getImagesDirectory();
        $cache_path = self::getCacheDirectory();

        if (!is_file($images_path . $filename)) {
            if (is_file($images_path . config('image_cache.no_image'))) {
                $filename =  config('imagecache.image_directory') . DIRECTORY_SEPARATOR . config('image_cache.no_image');
            } else {
                return false;
            }
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $image_old = $filename;

        $image_new = substr($filename, 0, strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

        if (!is_file($cache_path . $image_new) || (filectime($images_path . $image_old) > filectime($cache_path . $image_new))) {
            list($width_orig, $height_orig, $image_type) = getimagesize($images_path . $image_old);

            if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
                return $images_path . $image_old;
            }

            $path = NULL;

            $directories = explode(DIRECTORY_SEPARATOR, dirname($image_new));

            foreach ($directories as $directory) {
                $path = $path . DIRECTORY_SEPARATOR . $directory;

                if (!is_dir($cache_path . $path)) {
                    @mkdir(  $cache_path . $path, 0777);
                }
            }

            if ($width_orig != $width || $height_orig != $height) {
                $image = Image::make($images_path . $image_old);

                $image->fit($width, $height);

                $image->save($cache_path . $image_new);
            } else {
                copy($images_path . $image_old, $cache_path . $image_new);
            }
        }

        $path_parts = explode('/', $image_new);
        $new_image = implode('/', array_map('rawurlencode', $path_parts));

        return $new_image;
    }

    /**
     * Get path to images directory
     *
     * @return string
     */
    static function getImagesDirectory()
    {
        if(config('imagecache.image_directory')) {
            $images_path = config('imagecache.image_directory') . DIRECTORY_SEPARATOR;
        } else {
            $images_path = storage_path();
        }

        if(!file_exists($images_path)) {
            mkdir($images_path);
        }

        return $images_path;
    }

    /**
     * Get path to cached images directory
     *
     * @return string
     */
    static function getCacheDirectory()
    {
        if(config('imagecache.cache_directory')) {
            $cache_path = public_path() . DIRECTORY_SEPARATOR . config('imagecache.cache_directory') . DIRECTORY_SEPARATOR;
        } else {
            $cache_path = public_path() . DIRECTORY_SEPARATOR;
        }

        if(!file_exists($cache_path)) {
            mkdir($cache_path);
        }

        return $cache_path;
    }
}