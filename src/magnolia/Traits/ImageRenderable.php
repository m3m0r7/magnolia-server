<?php
namespace Magnolia\Traits;

trait ImageRenderable
{
    protected function renderBlackScreen()
    {
        static $image = null;
        if ($image !== null) {
            return $image;
        }
        $image = imagecreatetruecolor(640, 480);
        imagefill($image, 0, 0, imagecolorallocate($image, 0, 0, 0));
        ob_start();
        imagejpeg($image);
        return $image = ob_get_clean();
    }
}