<?php

/* SimpleImage, minified version */

/**
 * Class SimpleImage
 * all methods to work with image, minified. prefer to work locally.
 * use like:
 * $i = new SimpleImage();
 * $i->fromFile($preFile)->autoOrient()->bestFit(320, 240)->overlay("watermark.png")->toFile($newFile);
 */
class SimpleImage {
    const ERR_FILE_NOT_FOUND = 1, ERR_FONT_FILE = 2, ERR_FREETYPE_NOT_ENABLED = 3,
        ERR_GD_NOT_ENABLED = 4, ERR_INVALID_COLOR = 5, ERR_INVALID_DATA_URI = 6,
        ERR_INVALID_IMAGE = 7, ERR_LIB_NOT_LOADED = 8, ERR_UNSUPPORTED_FORMAT = 9,
        ERR_WEBP_NOT_ENABLED = 10, ERR_WRITE = 11;
    private $image, $mimeType, $exif;
    public function __construct($image = null) {
        if (extension_loaded('gd')) {
            ini_set('gd.jpeg_ignore_warning', 1);
        } else {
            throw new \Exception('Required extension GD is not loaded.', self::
            ERR_GD_NOT_ENABLED);
        }
        if (preg_match('/^data:(.*?);/', $image)) {
            $this->fromDataUri($image);
        } elseif ($image) {
            $this->fromFile($image);
        }
    }

    public function __destruct() {
        if ($this->image !== null && get_resource_type($this->image) === 'gd') {
            imagedestroy($this->image);
        }
    }

    public function fromDataUri($uri) {
        preg_match('/^data:(.*?);/', $uri, $matches);
        if (!count($matches)) {
            throw new \Exception('Invalid data URI.', self::ERR_INVALID_DATA_URI);
        }
        $this->mimeType = $matches[1];
        if (!preg_match('/^image\/(gif|jpeg|png)$/', $this->mimeType)) {
            throw new \Exception('Unsupported format: ' . $this->mimeType, self::
            ERR_UNSUPPORTED_FORMAT);
        }
        $uri = base64_decode(preg_replace('/^data:(.*?);base64,/', '', $uri));
        $this->image = imagecreatefromstring($uri);
        if (!$this->image) {
            throw new \Exception("Invalid image data.", self::ERR_INVALID_IMAGE);
        }
        return $this;
    }
    public function fromFile($file) {
        $handle = @fopen($file, 'r');
        if ($handle === false) {
            throw new \Exception("File not found: $file", self::ERR_FILE_NOT_FOUND);
        }
        fclose($handle);
        $info = getimagesize($file);
        if ($info === false) {
            throw new \Exception("Invalid image file: $file", self::ERR_INVALID_IMAGE);
        }
        $this->mimeType = $info['mime'];
        switch ($this->mimeType) {
            case 'image/gif':
                $gif = imagecreatefromgif($file);
                if ($gif) {
                    $width = imagesx($gif);
                    $height = imagesy($gif);
                    $this->image = imagecreatetruecolor($width, $height);
                    $transparentColor = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
                    imagecolortransparent($this->image, $transparentColor);
                    imagefill($this->image, 0, 0, $transparentColor);
                    imagecopy($this->image, $gif, 0, 0, 0, 0, $width, $height);
                    imagedestroy($gif);
                }
                break;
            case 'image/jpeg':
                $this->image = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $this->image = imagecreatefrompng($file);
                break;
            case 'image/webp':
                $this->image = imagecreatefromwebp($file);
                break;
        }
        if (!$this->image) {
            throw new \Exception("Unsupported image: $file", self::ERR_UNSUPPORTED_FORMAT);
        }
        //imagepalettetotruecolor($this->image);
        if ($this->mimeType === 'image/jpeg' && function_exists('exif_read_data')) {
            $this->exif = @exif_read_data($file);
        }
        return $this;
    }
    public function fromNew($width, $height, $color = 'transparent') {
        $this->image = imagecreatetruecolor($width, $height);
        $this->mimeType = 'image/png';
        $this->fill($color);
        return $this;
    }
    public function fromString($string) {
        return $this->fromFile('data://;base64,' . base64_encode($string));
    }
    private function generate($mimeType = null, $quality = 100) {
        $mimeType = $mimeType ? : $this->mimeType;
        $quality = self::keepWithin($quality, 0, 100);
        ob_start();
        switch ($mimeType) {
            case 'image/gif':
                imagesavealpha($this->image, true);
                imagegif($this->image, null);
                break;
            case 'image/jpeg':
                imageinterlace($this->image, true);
                imagejpeg($this->image, null, $quality);
                break;
            case 'image/png':
                imagesavealpha($this->image, true);
                imagepng($this->image, null, round(9 * $quality / 100));
                break;
            case 'image/webp':
                if (!function_exists('imagewebp')) {
                    throw new \Exception('WEBP support is not enabled in your version of PHP.', self::
                    ERR_WEBP_NOT_ENABLED);
                }
                imagesavealpha($this->image, true);
                imagewebp($this->image, null, $quality);
                break;
            default:
                throw new \Exception('Unsupported format: ' . $mimeType, self::
                ERR_UNSUPPORTED_FORMAT);
        }
        $data = ob_get_contents();
        ob_end_clean();
        return array('data' => $data, 'mimeType' => $mimeType);
    }
    public function toDataUri($mimeType = null, $quality = 100) {
        $image = $this->generate($mimeType, $quality);
        return 'data:' . $image['mimeType'] . ';base64,' . base64_encode($image['data']);
    }
    public function toDownload($filename, $mimeType = null, $quality = 100) {
        $image = $this->generate($mimeType, $quality);
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Length: ' . strlen($image['data']));
        header('Content-Transfer-Encoding: Binary');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        echo $image['data'];
        return $this;
    }
    public function toFile($file, $mimeType = null, $quality = 100) {
        $image = $this->generate($mimeType, $quality);
        if (!file_put_contents($file, $image['data'])) {
            throw new \Exception("Failed to write image to file: $file", self::ERR_WRITE);
        }
        return $this;
    }
    public function toScreen($mimeType = null, $quality = 100) {
        $image = $this->generate($mimeType, $quality);
        header('Content-Type: ' . $image['mimeType']);
        echo $image['data'];
        return $this;
    }
    public function toString($mimeType = null, $quality = 100) {
        $gen = $this->generate($mimeType, $quality);
        return $gen['data'];
    }
    private static function keepWithin($value, $min, $max) {
        if ($value < $min) return $min;
        if ($value > $max) return $max;
        return $value;
    }
    public function getAspectRatio() {
        return $this->getWidth() / $this->getHeight();
    }
    public function getExif() {
        return isset($this->exif) ? $this->exif : null;
    }
    public function getHeight() {
        return (int)imagesy($this->image);
    }
    public function getMimeType() {
        return $this->mimeType;
    }
    public function getOrientation() {
        $width = $this->getWidth();
        $height = $this->getHeight();
        if ($width > $height) return 'landscape';
        if ($width < $height) return 'portrait';
        return 'square';
    }
    public function getWidth() {
        return (int)imagesx($this->image);
    }
    private static function imageCopyMergeAlpha($dstIm, $srcIm, $dstX, $dstY, $srcX,
                                                $srcY, $srcW, $srcH, $pct) {
        if ($pct < 100) {
            imagealphablending($srcIm, false);
            imagefilter($srcIm, IMG_FILTER_COLORIZE, 0, 0, 0, 127 * ((100 - $pct) / 100));
        }
        imagecopy($dstIm, $srcIm, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH);
        return true;
    }
    public function bestFit($maxWidth, $maxHeight) {
        if ($this->getOrientation() === 'portrait') {
            $height = $maxHeight;
            $width = $maxHeight * $this->getAspectRatio();
        } else {
            $width = $maxWidth;
            $height = $maxWidth / $this->getAspectRatio();
        }
        if ($width > $maxWidth) {
            $width = $maxWidth;
            $height = $width / $this->getAspectRatio();
        }
        if ($height > $maxHeight) {
            $height = $maxHeight;
            $width = $height * $this->getAspectRatio();
        }
        return $this->resize($width, $height);
    }
    public function crop($x1, $y1, $x2, $y2) {
        $x1 = self::keepWithin($x1, 0, $this->getWidth());
        $x2 = self::keepWithin($x2, 0, $this->getWidth());
        $y1 = self::keepWithin($y1, 0, $this->getHeight());
        $y2 = self::keepWithin($y2, 0, $this->getHeight());
        $this->image = imagecrop(
            $this->image,
            array(
                'x' => min($x1, $x2), 'y' => min($y1, $y2), 'width' => abs($x2 - $x1), 'height' => abs($y2 - $y1)
            )
        );
        return $this;
    }
    public function fitToHeight($height) {
        return $this->resize(null, $height);
    }
    public function fitToWidth($width) {
        return $this->resize($width, null);
    }
    public function flip($direction) {
        switch ($direction) {
            case 'x':
                imageflip($this->image, IMG_FLIP_HORIZONTAL);
                break;
            case 'y':
                imageflip($this->image, IMG_FLIP_VERTICAL);
                break;
            case 'both':
                imageflip($this->image, IMG_FLIP_BOTH);
                break;
        }
        return $this;
    }
    public function maxColors($max, $dither = true) {
        imagetruecolortopalette($this->image, $dither, max(1, $max));
        return $this;
    }
    public function overlay($overlay, $anchor = 'center', $opacity = 1, $xOffset = 0,
                            $yOffset = 0) {
        if (!($overlay instanceof SimpleImage)) {
            $overlay = new SimpleImage($overlay);
        }
        $opacity = self::keepWithin($opacity, 0, 1) * 100;
        switch ($anchor) {
            case 'top left':
                $x = $xOffset;
                $y = $yOffset;
                break;
            case 'top right':
                $x = $this->getWidth() - $overlay->getWidth() + $xOffset;
                $y = $yOffset;
                break;
            case 'top':
                $x = ($this->getWidth() / 2) - ($overlay->getWidth() / 2) + $xOffset;
                $y = $yOffset;
                break;
            case 'bottom left':
                $x = $xOffset;
                $y = $this->getHeight() - $overlay->getHeight() + $yOffset;
                break;
            case 'bottom right':
                $x = $this->getWidth() - $overlay->getWidth() + $xOffset;
                $y = $this->getHeight() - $overlay->getHeight() + $yOffset;
                break;
            case 'bottom':
                $x = ($this->getWidth() / 2) - ($overlay->getWidth() / 2) + $xOffset;
                $y = $this->getHeight() - $overlay->getHeight() + $yOffset;
                break;
            case 'left':
                $x = $xOffset;
                $y = ($this->getHeight() / 2) - ($overlay->getHeight() / 2) + $yOffset;
                break;
            case 'right':
                $x = $this->getWidth() - $overlay->getWidth() + $xOffset;
                $y = ($this->getHeight() / 2) - ($overlay->getHeight() / 2) + $yOffset;
                break;
            default:
                $x = ($this->getWidth() / 2) - ($overlay->getWidth() / 2) + $xOffset;
                $y = ($this->getHeight() / 2) - ($overlay->getHeight() / 2) + $yOffset;
                break;
        }
        self::imageCopyMergeAlpha($this->image, $overlay->image, $x, $y, 0, 0, $overlay->
        getWidth(), $overlay->getHeight(), $opacity);
        return $this;
    }
    public function resize($width = null, $height = null) {
        if (!$width && !$height) {
            return $this;
        }
        if ($width && !$height) {
            $height = $width / $this->getAspectRatio();
        }
        if (!$width && $height) {
            $width = $height * $this->getAspectRatio();
        }
        if ($this->getWidth() === $width && $this->getHeight() === $height) {
            return $this;
        }
        $newImage = imagecreatetruecolor($width, $height);
        $transparentColor = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagecolortransparent($newImage, $transparentColor);
        imagefill($newImage, 0, 0, $transparentColor);
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->
        getWidth(), $this->getHeight());
        $this->image = $newImage;
        return $this;
    }
    public function rotate($angle, $backgroundColor = 'transparent') {
        $backgroundColor = $this->allocateColor($backgroundColor);
        $this->image = imagerotate($this->image, -(self::keepWithin($angle, -360, 360)),
            $backgroundColor);
        return $this;
    }
    public function text($text, $options, &$boundary = null) {
        if (!function_exists('imagettftext')) {
            throw new \Exception('Freetype support is not enabled in your version of PHP.',
                self::ERR_FREETYPE_NOT_ENABLED);
        }
        $options = array_merge(
            array('fontFile' => null, 'size' => 12, 'color' => 'black', 'anchor' => 'center', 'xOffset' => 0, 'yOffset' => 0, 'shadow' => null)
            , $options);
        $fontFile = $options['fontFile'];
        $size = ($options['size'] / 96) * 72;
        $color = $this->allocateColor($options['color']);
        $anchor = $options['anchor'];
        $xOffset = $options['xOffset'];
        $yOffset = $options['yOffset'];
        $angle = 0;
        $box = imagettfbbox($size, $angle, $fontFile, $text);
        if (!$box) {
            throw new \Exception("Unable to load font file: $fontFile", self::ERR_FONT_FILE);
        }
        $boxWidth = abs($box[6] - $box[2]);
        $boxHeight = $options['size'];
        $box = imagettfbbox($size, $angle, $fontFile, 'X');
        $capHeight = abs($box[7] - $box[1]);
        $box = imagettfbbox($size, $angle, $fontFile, 'X Qgjpqy');
        $fullHeight = abs($box[7] - $box[1]);
        $descenderHeight = $fullHeight - $capHeight;
        switch ($anchor) {
            case 'top left':
                $x = $xOffset;
                $y = $yOffset + $boxHeight;
                break;
            case 'top right':
                $x = $this->getWidth() - $boxWidth + $xOffset;
                $y = $yOffset + $boxHeight;
                break;
            case 'top':
                $x = ($this->getWidth() / 2) - ($boxWidth / 2) + $xOffset;
                $y = $yOffset + $boxHeight;
                break;
            case 'bottom left':
                $x = $xOffset;
                $y = $this->getHeight() - $boxHeight + $yOffset + $boxHeight;
                break;
            case 'bottom right':
                $x = $this->getWidth() - $boxWidth + $xOffset;
                $y = $this->getHeight() - $boxHeight + $yOffset + $boxHeight;
                break;
            case 'bottom':
                $x = ($this->getWidth() / 2) - ($boxWidth / 2) + $xOffset;
                $y = $this->getHeight() - $boxHeight + $yOffset + $boxHeight;
                break;
            case 'left':
                $x = $xOffset;
                $y = ($this->getHeight() / 2) - (($boxHeight / 2) - $boxHeight) + $yOffset;
                break;
            case 'right';
                $x = $this->getWidth() - $boxWidth + $xOffset;
                $y = ($this->getHeight() / 2) - (($boxHeight / 2) - $boxHeight) + $yOffset;
                break;
            default:
                $x = ($this->getWidth() / 2) - ($boxWidth / 2) + $xOffset;
                $y = ($this->getHeight() / 2) - (($boxHeight / 2) - $boxHeight) + $yOffset;
                break;
        }
        $x = (int)round($x);
        $y = (int)round($y);
        $boundary = array('x1' => $x, 'y1' => $y - $boxHeight, 'x2' => $x + $boxWidth, 'y2' =>
            $y, 'width' => $boxWidth, 'height' => $boxHeight);
        if (is_array($options['shadow'])) {
            imagettftext($this->image, $size, $angle, $x + $options['shadow']['x'], $y + $options['shadow']['y'] -
                $descenderHeight, $this->allocateColor($options['shadow']['color']), $fontFile,
                $text);
        }
        imagettftext($this->image, $size, $angle, $x, $y - $descenderHeight, $color, $fontFile,
            $text);
        return $this;
    }
    public function thumbnail($width, $height, $anchor = 'center') {
        $currentRatio = $this->getHeight() / $this->getWidth();
        $targetRatio = $height / $width;
        if ($targetRatio > $currentRatio) {
            $this->resize(null, $height);
        } else {
            $this->resize($width, null);
        }
        switch ($anchor) {
            case 'top':
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = 0;
                $y2 = $height;
                break;
            case 'bottom':
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            case 'left':
                $x1 = 0;
                $x2 = $width;
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
            case 'right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
            case 'top left':
                $x1 = 0;
                $x2 = $width;
                $y1 = 0;
                $y2 = $height;
                break;
            case 'top right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = 0;
                $y2 = $height;
                break;
            case 'bottom left':
                $x1 = 0;
                $x2 = $width;
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            case 'bottom right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            default:
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
        }
        return $this->crop($x1, $y1, $x2, $y2);
    }
    public function arc($x, $y, $width, $height, $start, $end, $color, $thickness =
    1) {
        $color = $this->allocateColor($color);
        if ($thickness === 'filled') {
            imagesetthickness($this->image, 1);
            imagefilledarc($this->image, $x, $y, $width, $height, $start, $end, $color,
                IMG_ARC_PIE);
        } else {
            imagesetthickness($this->image, $thickness);
            imagearc($this->image, $x, $y, $width, $height, $start, $end, $color);
        }
        return $this;
    }
    public function border($color, $thickness = 1) {
        $x1 = 0;
        $y1 = 0;
        $x2 = $this->getWidth() - 1;
        $y2 = $this->getHeight() - 1;
        for ($i = 0; $i < $thickness; $i++) {
            $this->rectangle($x1++, $y1++, $x2--, $y2--, $color);
        }
        return $this;
    }
    public function dot($x, $y, $color) {
        $color = $this->allocateColor($color);
        imagesetpixel($this->image, $x, $y, $color);
        return $this;
    }
    public function ellipse($x, $y, $width, $height, $color, $thickness = 1) {
        $color = $this->allocateColor($color);
        if ($thickness === 'filled') {
            imagesetthickness($this->image, 1);
            imagefilledellipse($this->image, $x, $y, $width, $height, $color);
        } else {
            imagesetthickness($this->image, 1);
            $i = 0;
            while ($i++ < $thickness * 2 - 1) {
                imageellipse($this->image, $x, $y, --$width, $height--, $color);
            }
        }
        return $this;
    }
    public function fill($color) {
        $this->rectangle(0, 0, $this->getWidth(), $this->getHeight(), 'white', 'filled');
        $color = $this->allocateColor($color);
        imagefill($this->image, 0, 0, $color);
        return $this;
    }
    public function line($x1, $y1, $x2, $y2, $color, $thickness = 1) {
        $color = $this->allocateColor($color);
        imagesetthickness($this->image, $thickness);
        imageline($this->image, $x1, $y1, $x2, $y2, $color);
        return $this;
    }
    public function polygon($vertices, $color, $thickness = 1) {
        $color = $this->allocateColor($color);
        $points = array();
        foreach ($vertices as $vals) {
            $points[] = $vals['x'];
            $points[] = $vals['y'];
        }
        if ($thickness === 'filled') {
            imagesetthickness($this->image, 1);
            imagefilledpolygon($this->image, $points, count($vertices), $color);
        } else {
            imagesetthickness($this->image, $thickness);
            imagepolygon($this->image, $points, count($vertices), $color);
        }
        return $this;
    }
    public function rectangle($x1, $y1, $x2, $y2, $color, $thickness = 1) {
        $color = $this->allocateColor($color);
        if ($thickness === 'filled') {
            imagesetthickness($this->image, 1);
            imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $color);
        } else {
            imagesetthickness($this->image, $thickness);
            imagerectangle($this->image, $x1, $y1, $x2, $y2, $color);
        }
        return $this;
    }
    public function roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, $thickness =
    1) {
        if ($thickness === 'filled') {
            $this->rectangle($x1 + $radius + 1, $y1, $x2 - $radius - 1, $y2, $color,
                'filled');
            $this->rectangle($x1, $y1 + $radius + 1, $x1 + $radius, $y2 - $radius - 1, $color,
                'filled');
            $this->rectangle($x2 - $radius, $y1 + $radius + 1, $x2, $y2 - $radius - 1, $color,
                'filled');
            $this->arc($x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color,
                'filled');
            $this->arc($x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color,
                'filled');
            $this->arc($x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color,
                'filled');
            $this->arc($x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 360, 90, $color,
                'filled');
        } else {
            $this->line($x1 + $radius, $y1, $x2 - $radius, $y1, $color, $thickness);
            $this->line($x1 + $radius, $y2, $x2 - $radius, $y2, $color, $thickness);
            $this->line($x1, $y1 + $radius, $x1, $y2 - $radius, $color, $thickness);
            $this->line($x2, $y1 + $radius, $x2, $y2 - $radius, $color, $thickness);
            $this->arc($x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color,
                $thickness);
            $this->arc($x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color,
                $thickness);
            $this->arc($x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color,
                $thickness);
            $this->arc($x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 360, 90, $color,
                $thickness);
        }
        return $this;
    }
    private function allocateColor($color) {
        $color = self::normalizeColor($color);
        $index = imagecolorexactalpha($this->image, $color['red'], $color['green'], $color['blue'],
            127 - ($color['alpha'] * 127));
        if ($index > -1) {
            return $index;
        }
        return imagecolorallocatealpha($this->image, $color['red'], $color['green'], $color['blue'],
            127 - ($color['alpha'] * 127));
    }
}
