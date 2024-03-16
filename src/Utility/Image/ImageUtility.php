<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Image;

/**
 * Description of ImageUtility
 *
 * @author nazmulislam
 */
class ImageUtility
{
    public string $image;
    public int|float $newHeight;
    public int|float $newWidth;
    public int|float $maxHeight;
    public int|float $maxWidth;
    public int|float $originalHeight;
    public int|float $orginalWidth;
    public float $originalRatio;
    public string $destination;
     public string $mimeType;
    public $newImage;

    public $temp;

    public function __construct()
    {
       

    }
    public function setDefaults(string $image, string $destination, string $mimeType)
    {
        $this->image = __DIR__ . '/../../../' . $image;
        $this->destination = __DIR__.'/../../../'.$destination;
        $this->mimeType = $mimeType;
    }
    /**
     *
     * @return void
     */
    private function getOriginalHeightWidth(): void
    {
        list($this->orginalWidth, $this->originalHeight) = \getimagesize($this->image);
        $this->originalRatio = $this->orginalWidth / $this->originalHeight;
    }
    /**
     *
     * @return void
     */
    private function getNewHeightWidth(): void
    {
        if (($this->maxWidth / $this->maxHeight) > $this->originalRatio)
        {
            $this->maxWidth = (int)($this->maxHeight * $this->originalRatio);
        } else
        {
            $this->maxHeight = (int)($this->maxWidth / $this->originalRatio);
        }
    }

    /**
     * Creates new thumbnail
     * @return bool
     */
    public function createImageThumbnail(int $maxWidth =200, int $maxHeight = 200): bool
    {
        $this->maxHeight = $maxHeight;
        $this->maxWidth = $maxWidth;
        $this->getOriginalHeightWidth();
        $this->getNewHeightWidth();
        $this->newImage = \imagecreatetruecolor((int)$this->maxWidth, (int)$this->maxHeight);

        if($this->mimeType == 'image/jpeg')
        {
           // \NazmulIslam\Utility\Logger\Logger::debug('create JPEG',[]);
            $this->createJPEG();
        }

        if($this->mimeType == 'image/jpg')
        {
            //\NazmulIslam\Utility\Logger\Logger::debug('create JPG',[]);
            $this->createJPEG();
        }

        if($this->mimeType == 'image/png')
        {
            //\NazmulIslam\Utility\Logger\Logger::debug('create PNG',[]);
            $this->createPNG();
        }

        if($this->mimeType == 'image/gif')
        {
            //\NazmulIslam\Utility\Logger\Logger::debug('create GIF',[]);
            $this->temp = imagecreatefromgif($this->image);
        }

        \imagedestroy($this->newImage);

        if (file_exists($this->destination))
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     *
     * @return ImageUtility
     */
    private function createJPEG():ImageUtility
    {
        $this->temp = \imagecreatefromjpeg($this->image);
        $this->reSampleImage();
        
        \imagejpeg($this->newImage, $this->destination, 100);
        
        return $this;
    }

    /**
     *
     * @return ImageUtility
     */
    private function createPNG():ImageUtility
    {
        $this->temp = \imagecreatefrompng($this->image);
        \imagealphablending($this->newImage, false);
        \imagesavealpha($this->newImage,true);
        $transparent = \imagecolorallocatealpha($this->newImage, 255, 255, 255, 127);
        \imagefilledrectangle($this->temp, 0, 0, $this->maxWidth, $this->maxHeight, $transparent);
        
        $this->reSampleImage();
        \imagepng($this->newImage, $this->destination, 9);

        return $this;
    }
    /**
     *
     * @return ImageUtility
     */
    private function createGIF():ImageUtility
    {

       $this->setTransparency($this->newImage,$this->temp);
       \imagegif($this->newImage, $this->destination, 100);

        return $this;
    }
    /**
     *
     * @return ImageUtility
     */
    private function reSampleImage():ImageUtility
    {
        \imagecopyresampled($this->newImage, $this->temp, 0, 0, 0, 0, (int)ceil($this->maxWidth),(int)ceil($this->maxHeight), (int)ceil($this->orginalWidth), (int)ceil($this->originalHeight));
        return $this;
    }
    
    // Function to assist the PNG images, sets the transparency in the image
    /**
     *
     * @return ImageUtility
     */
    private function setTransparency():ImageUtility
    {
        $transparencyIndex = \imagecolortransparent($this->temp);
        $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);
        if($transparencyIndex >= 0){
            $transparencyColor = \imagecolorsforindex($this->temp, $transparencyIndex);
        }
        $transparencyIndex = \imagecolorallocate($this->newImage, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
        \imagefill($this->newImage, 0, 0, $transparencyIndex);
        \imagecolortransparent($this->newImage, $transparencyIndex);

        return $this;
    }

}
