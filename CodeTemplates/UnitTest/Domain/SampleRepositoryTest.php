<?php

declare(strict_types=1);

namespace Test\Api\Domain\[DomainFolder];

use \PHPUnit\Framework\TestCase;
use \Prophecy\PhpUnit\ProphecyTrait;
use NazmulIslam\Utility\Core\Traits\TestSetupTrait;

use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Repository;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]CacheKeys;

class [ClassName]RepositoryTest extends TestCase
{
    use ProphecyTrait;
    use TestSetupTrait;

    public [ClassName]Repository $repository;
    public $[instanceName]RepositoryProphecy;

    public array $fields = ['*'];
    public int $[parameterId] = 1;

    public array $postInput = [
        '[title_field]' => 'First',
        
    ];

    public array $paginationInput = [
        'page' => 1,
        'per_page' => 1,
        'sort' => '[table_name].created_at|desc',
        'filter' => 'abc'
    ];

    protected function setUp(): void 
    {
        $this->dbSetup([[ClassName]CacheKeys::[CONSTANTS]_TAG]);
        $this->repository = new [ClassName]Repository();
        $this->[instanceName]RepositoryProphecy = $this->prophesize([ClassName]Repository::class);
    }

    public function testGet[ClassName]ListMethod()
    {
        $this->[instanceName]RepositoryProphecy->get[ClassName]List($this->paginationInput, $this->fields)->willReturn([]);

        $this->assertIsArray($this->repository->get[ClassName]List($this->paginationInput, $this->fields));
    }

    public function testCreateMethod()
    {
        $this->[instanceName]RepositoryProphecy->create($this->postInput)->willReturn([]);

        $this->assertIsArray($this->repository->create($this->postInput));
    }

    public function testUpdateMethod()
    {
        $this->[instanceName]RepositoryProphecy->update($this->postInput, $this->[parameterId])->willReturn([]);

        $this->assertIsArray($this->repository->update($this->postInput, $this->[parameterId]));
    }

    public function testDeleteMethod()
    {
        $this->[instanceName]RepositoryProphecy->delete($this->[parameterId])->willReturn([]);

        $this->assertIsArray($this->repository->delete($this->[parameterId]));
    }

    public function testGetDataMethod()
    {
        $this->[instanceName]RepositoryProphecy->getData($this->postInput, $this->fields)->willReturn([]);

        $this->assertIsArray($this->repository->getData($this->postInput, $this->fields));
    }
}
