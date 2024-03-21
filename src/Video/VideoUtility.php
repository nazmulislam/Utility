<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Video;

/**
 * Description of ImageUtility
 *
 * @author nazmulislam
 */
class VideoUtility
{
    public string $video;
    public int $newHeight;
    public int $newWidth;
    public int $maxHeight;
    public int $maxWidth;
    public int $originalHeight;
    public int $orginalWidth;
    public float $originalRatio;
    public string $destination;
    public string $mimeType;
    public $newImage;

    public $temp;

    public function __construct(string $video, string $destination, string $mimeType)
    {
        $this->video = __DIR__.'/../../../../../' . $video;
        $this->destination = __DIR__.'/../../../../../'.$destination;
        $this->mimeType = $mimeType;

    }
    /**
     *
     * @return void
     */
    private function getOriginalHeightWidth(): void
    {
        list($this->orginalWidth, $this->originalHeight) = \getimagesize($this->video);
        $this->originalRatio = $this->orginalWidth / $this->originalHeight;
    }
    /**
     *
     * @return void
     */
    private function getNewHeightWidth(): void
    {
        if ($this->maxWidth / $this->maxHeight > $this->originalRatio)
        {
            $this->maxWidth = $this->maxHeight * $this->originalRatio;
        } else
        {
            $this->maxHeight = $this->maxWidth / $this->originalRatio;
        }
    }

    /**
     * Creates new thumbnail
     * @return bool
     */
    public function createVideoSize(int $maxWidth =200,$maxHeight = 200): bool
    {
        $this->maxHeight = $maxHeight;
        $this->maxWidth = $maxWidth;
        $this->getOriginalHeightWidth();
        $this->getNewHeightWidth();

        if (file_exists($this->destination))
        {
            return true;
        } else
        {
            return false;
        }
    }

}
