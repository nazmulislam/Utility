<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core\Interfaces;

/**
 * Interface ComponentInterface. Defines the methods required by each Component
 * @package NazmulIslam\Utility\Core\Interfaces
 */
interface ComponentInterface
{
    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool;

    /**
     * @return bool
     */
    public function delete(): bool;

    /**
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool;
}