<?php 
declare(strict_types=1);
namespace App\Domain\Sample;

use App\Models\App\Model;
use App\Domain\Sample\SampleCacheKeys;
use App\Domain\Sample\SampleRepository;
use App\GUID\GUID;


class SampleObserver {

    public SampleRepository $sampleRepository;

    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    public function saved(Model $model): void
    {
        $this->purgeAllCacheByTag(tags:[SampleCacheKeys::[CONSTANTS]_TAG]);
    }

    public function creating(Model $model): void
    {
        
        $model->GUID = GUID::createGuidForTableIds($model, 'GUID');
    }
    public function created(Model $model): void
    {
        
        $this->purgeAllCacheByTag(tags:[SampleCacheKeys::[CONSTANTS]_TAG]);
    }

    public function deleted(Model $model): void
    {
        $this->purgeAllCacheByTag(tags:[SampleCacheKeys::[CONSTANTS]_TAG]);
    }

    public function updated(Model $model): void
    {
        $this->purgeAllCacheByTag(tags:[SampleCacheKeys::[CONSTANTS]_TAG]);
    }

    public function restored(Model $model): void
    {
        $this->purgeAllCacheByTag(tags:[SampleCacheKeys::[CONSTANTS]_TAG]);
    }

    private function purgeAllCacheByTag(array $tags): void
    {
        $this->sampleRepository->clearCacheByTag(tags: $tags);
    }

}
