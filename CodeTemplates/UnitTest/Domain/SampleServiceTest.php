<?php

declare(strict_types=1);

namespace Test\Api\Domain\[DomainFolder];

use \PHPUnit\Framework\TestCase;
use \Prophecy\PhpUnit\ProphecyTrait;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Repository;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]CacheKeys;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Service;

use NazmulIslam\Utility\Domain\ActivityLog\ActivityLogService;
use NazmulIslam\Utility\Domain\ActivityLog\ActivityLogRepository;
use Psr\Container\ContainerInterface;
use NazmulIslam\Utility\Core\Traits\TestSetupTrait;

class [ClassName]ServiceTest extends TestCase
{
    use ProphecyTrait;
    use TestSetupTrait;

    public [ClassName]Service $service;
    public $[instanceName]RepositoryProphecy;
    public $[instanceName]ServiceProphecy;

    public array $paginationInput = [
        'page' => 1,
        'per_page' => 1,
        'sort' => '[table_name].created_at|desc',
        'filter' => 'abc'
    ];

    public array $fields = [
        '[table_name].[primary_key_id]',
        '[table_name].[table_name]_guid',
        
        '[table_name].[title_field]',
        
    ];

    public array $postInput = [
        '[title_field]' => 'Second',
       
    ];

    public int $[parameterId] = 1;

    public function setNewService()
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        return new [ClassName]Service(new ActivityLogService(), new ActivityLogRepository(), $containerProphecy->reveal());
    }

    protected function setUp(): void 
    {
        $this->dbSetup([[ClassName]CacheKeys::ASSESSMENT_TAG]);
        $this->service = $this->setNewService();

        $this->[instanceName]RepositoryProphecy = $this->prophesize([ClassName]Repository::class);
        $this->[instanceName]ServiceProphecy = $this->prophesize([ClassName]Service::class);
    }

    public function testGet[ClassName]ListMethod()
    {
        $this->[instanceName]RepositoryProphecy->get[ClassName]List($this->postInput, $this->fields)->shouldBeCalled()->willReturn([]);

        $this->assertSame([], $this->service->get[ClassName]List($this->postInput, $this->[instanceName]RepositoryProphecy->reveal()));
    }

    public function testCreateMethod()
    {
        $this->[instanceName]RepositoryProphecy->create($this->postInput)->shouldBeCalled()->willReturn([]);
        $this->[instanceName]ServiceProphecy->create($this->postInput, $this->[instanceName]RepositoryProphecy->reveal())->willReturn([]);

        $this->assertIsArray($this->service->create($this->postInput, $this->[instanceName]RepositoryProphecy->reveal()));
    }

    public function testUpdateMethod()
    {
        $return = [
            'modelAfterUpdate' => []
        ];

        $this->[instanceName]RepositoryProphecy->update($this->postInput, $this->[parameterId])->shouldBeCalled()->willReturn($return);
        $this->[instanceName]ServiceProphecy->update($this->postInput, $this->[parameterId])->willReturn($return);

        $this->assertIsArray($this->service->update($this->postInput, $this->[parameterId], $this->[instanceName]RepositoryProphecy->reveal()));
    }

    public function testDeleteMethod()
    {
        $this->[instanceName]RepositoryProphecy->delete($this->[parameterId])->shouldBeCalled()->willReturn([]);
        $this->[instanceName]ServiceProphecy->delete($this->[parameterId])->willReturn([]);

        $this->assertIsArray($this->service->delete($this->[parameterId], $this->[instanceName]RepositoryProphecy->reveal()));
    }

    public function testGet[ClassName]Method()
    {
        $this->[instanceName]ServiceProphecy->get[ClassName]($this->[parameterId])->willReturn([]);

        $this->assertIsArray($this->service->get[ClassName]($this->[parameterId]));
    }

    public function testGetSelectListMethod()
    {
        $this->[instanceName]ServiceProphecy->getSelectList($this->[parameterId])->willReturn([]);

        $this->assertIsArray($this->service->getSelectList($this->[parameterId]));
    }
}
