<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core;

use NazmulIslam\Utility\Domain\ActivityLog\ActivityLogService;
use NazmulIslam\Utility\Domain\ActivityLog\ActivityLogRepository;
use Psr\Container\ContainerInterface;
use NazmulIslam\Utility\Events\ActivityLogEvent;

abstract class Service {

    public ActivityLogService $activityLogService;
    public ActivityLogRepository $activityLogRepository;
    public ContainerInterface $container;
    public $eventDispatcher;

    public function __construct(ActivityLogService $activityLogService, ActivityLogRepository $activityLogRepository, ContainerInterface $container) {
        $this->activityLogService = $activityLogService;
        $this->activityLogRepository = $activityLogRepository;
        $this->container = $container;
        $this->eventDispatcher = $this->container->get('eventDispatcher');
    }

    public function createActivityLog(
            string|int|null $module,
            int $modelId,
            int|null $userId,
            string $message,
            string $action,
            array|null $placeholderValues,
            array|null $addtionalData
            ) {


        $this->eventDispatcher->dispatch(new ActivityLogEvent(
                        activityLogService: $this->activityLogService,
                        activityLogRepository: $this->activityLogRepository,
                        module: $module,
                        modelId: $modelId,
                        userId: $userId,
                        message: $message,
                        action: $action,
                        placeholderValues: $placeholderValues,
                        addtionalData: $addtionalData
                ),
                'activityLog.event');
    }

}
