<?php

declare(strict_types=1);

namespace Test\Api\Smoke;

use \PHPUnit\Framework\TestCase;
use NazmulIslam\Utility\Core\Traits\TestSetupTrait;
use GuzzleHttp\RequestOptions;

class [ClassName]SmokeTest extends TestCase
{
    use TestSetupTrait;
    
    protected function setUp(): void 
    {
        $this->endPointClient = $this->setUpClient();
    }

    /**
     * test all the get endpoints for 200 status code
     */
    public function test_get_request_responce_status_code_has_200() {
        
        $responseSingle = $this->endPointClient->request('GET', '/[RouteGroup]/1');
        $this->assertEquals(200, $responseSingle->getStatusCode());

        $responseAll = $this->endPointClient->request('GET', '/[RouteGroup]/');
        $this->assertEquals(200, $responseAll->getStatusCode());
    }

    /**
     * test if the auth middleware is present to prevent endpoints not being protected
     */
    public function test_has_auth_middleware() {
        
        $response = $this->endPointClientWithoutHeader->request('GET', '/[RouteGroup]/1');
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @dataProvider testValidateForValidFieldTypesDataOfRequiredFieldsData
     */
    public function test_validation_of_required_fields_in_validation_middleware($postData) {
        
        $createResponse = $this->endPointClient->request('POST', '/[RouteGroup]/', [
            RequestOptions::JSON => $postData
        ]);

        $this->assertEquals(200, $createResponse->getStatusCode());

        $updateResponse = $this->endPointClient->request('POST', '/[RouteGroup]/1', [
            RequestOptions::JSON => $postData
        ]);

        $this->assertEquals(200, $updateResponse->getStatusCode());
    }

    /**
     * @dataProvider testDataType
     */
    public function test_validation_for_fields_are_of_correct_type_in_validation_middleware($postData) {
        
        $responseCreate = $this->endPointClient->request('POST', '/[RouteGroup]/', [
            RequestOptions::JSON => $postData
        ]);

        $this->assertEquals(200, $responseCreate->getStatusCode());

        $responseUpdate = $this->endPointClient->request('PUT', '/[RouteGroup]/1', [
            RequestOptions::JSON => $postData
        ]);

        $this->assertEquals(200, $responseUpdate->getStatusCode());
    }

    public function testValidateForValidFieldTypesDataOfRequiredFieldsData()
    {
        return [
            
            
            // valid
            [
                [
                    '[title_field]' => 'string'
                ]
            ]
        ];
    }

    public function testValidattionForValidFieldTypesOfData()
    {
        return [

            // type tests
            [
                [
                    '[title_field]' => 'string'
                ]
            ]
           
        ];
    }
}
