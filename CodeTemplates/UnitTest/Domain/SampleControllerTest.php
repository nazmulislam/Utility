<?php

declare(strict_types=1);

namespace Test\Api\Domain\[DomainFolder];

use \PHPUnit\Framework\TestCase;
use \Prophecy\PhpUnit\ProphecyTrait;
use App\Domain\[DomainFolder]\[ClassName]Repository;
use App\Domain\[DomainFolder]\[ClassName]Controller;
use App\Domain\[DomainFolder]\[ClassName]Service;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;

class [ClassName]ControllerTest extends TestCase
{
    use ProphecyTrait;

    public [ClassName]Controller $controller;
    public $[instanceName]RepositoryProphecy;
    public $[instanceName]ServiceProphecy;
    public $responseProphecy;
    public $requestProphecy;

    public array $paginationInput = [
        'page' => 1,
        'per_page' => 1,
        'sort' => '[table_name].created_at|desc',
        'filter' => 'abc'
    ];

    public int $[parameterId]= 1;

    protected function setUp(): void 
    {
        
        /**
         * Should be in trait
         */
        $this->controller = new [ClassName]Controller();

        $this->[instanceName]RepositoryProphecy = $this->prophesize([ClassName]Repository::class);
        $this->[instanceName]ServiceProphecy = $this->prophesize([ClassName]Service::class);

        $this->requestProphecy = $this->setRequestProphesy();
        $this->responseProphecy = $this->setResponseProphesy();
    }

    /**
     * Should be in trait
     */
    public function setRequestProphesy(array $input)
    {
        $request = $this->prophesize(Request::class);
        $request->getParsedBody()->willReturn($input);

        return $request;
    }

    /**
     * Should be in trait
     */
    public function setResponseProphesy()
    {
        $responseProphecy = $this->prophesize(Response::class);
        $responseProphecy->getBody()->shouldBeCalled()->willReturn($this->prophesize(StreamInterface::class)->reveal());
        $responseProphecy->withHeader('Content-Type', 'application/json')->shouldBeCalled()->willReturn($responseProphecy->reveal());
        $responseProphecy->withStatus(200)->shouldBeCalled()->willReturn($responseProphecy->reveal());

        return $responseProphecy;
    }

    public function testGetListPaginatedMethod()
    {
        $this->requestProphecy = $this->setRequestProphesy($this->paginationInput);
        $this->requestProphecy->getQueryParams()->shouldBeCalled()->willReturn([]);

        $this->[instanceName]ServiceProphecy->getListPaginated($this->paginationInput, $this->[instanceName]RepositoryProphecy->reveal())->shouldBeCalled()->willReturn([]);

        $this->assertSame($this->responseProphecy->reveal(), $this->controller->getListPaginated(
                $this->requestProphecy->reveal(), 
                $this->responseProphecy->reveal(), 
                $this->[instanceName]ServiceProphecy->reveal(), 
                $this->[instanceName]RepositoryProphecy->reveal()
            )
        );
    }

    public function testCreateMethod()
    {
        $this->[instanceName]ServiceProphecy->create([], $this->[instanceName]RepositoryProphecy->reveal())->shouldBeCalled()->willReturn([]);

        $this->assertSame($this->responseProphecy->reveal(), $this->controller->create(
                $this->requestProphecy->reveal(), 
                $this->responseProphecy->reveal(), 
                $this->[instanceName]ServiceProphecy->reveal(), 
                $this->[instanceName]RepositoryProphecy->reveal()
            )
        );
    }

    public function testUpdateMethod()
    {
        $this->[instanceName]ServiceProphecy->update([], $this->[parameterId], $this->[instanceName]RepositoryProphecy->reveal())->shouldBeCalled()->willReturn([]);

        $this->assertSame($this->responseProphecy->reveal(), $this->controller->update(
                $this->requestProphecy->reveal(), 
                $this->responseProphecy->reveal(), 
                $this->[instanceName]ServiceProphecy->reveal(), 
                $this->[parameterId],
                $this->[instanceName]RepositoryProphecy->reveal()
            )
        );
    }

    public function testDeleteMethod()
    {
        $this->[instanceName]ServiceProphecy->delete($this->[parameterId], $this->[instanceName]RepositoryProphecy->reveal())->shouldBeCalled()->willReturn([]);

        $this->assertSame($this->responseProphecy->reveal(), $this->controller->delete(
                $this->responseProphecy->reveal(), 
                $this->[instanceName]ServiceProphecy->reveal(), 
                $this->[parameterId],
                $this->[instanceName]RepositoryProphecy->reveal()
            )
        );
    }

}
