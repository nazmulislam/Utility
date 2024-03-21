<?php

declare(strict_types=1);

namespace Test\Api\Domain\[DomainFolder];

use \PHPUnit\Framework\TestCase;
use NazmulIslam\Utility\Core\Traits\TestSetupTrait;
use GuzzleHttp\RequestOptions;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Repository;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Controller;

class [ClassName]FunctionalTest extends TestCase
{
    use TestSetupTrait;

    public [ClassName]Controller $controller;

    protected function setUp(): void 
    {
        // client responce with headers
        $this->endPointClient = $this->setUpClient();
        // client responce without headers
        $this->endPointClientWithoutHeader = $this->setUpClientWithoutHeader();
    }

    public function test_get_valid_inputs() {
        
        $response = $this->endPointClient->request('GET', '/[table_name]/1');
        
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('[primary_key_id]', $data['record']);
        $this->assertArrayHasKey('[title_field]', $data['record']);
       
    }

    /**
     * @dataProvider validInputData
     */
    public function test_post_create_responce_valid($inputData) {
        
        $response = $this->endPointClient->request('POST', '/[table_name]/', [
            RequestOptions::JSON => $inputData
        ]);

        $resultData = json_decode($response->getBody()->getContents(), true);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // check if all the inputs are same as the data created in the output
        foreach($inputData as $key => $val){

            // to check if the input keys match the responce keys
            $this->assertArrayHasKey($key, $resultData['record']);

            // to check if input data matches the responce data
            $this->assertEquals($val, $resultData['record'][$key]);
        }
    }

    /**
     * @dataProvider inValidInputData
     */
    public function test_post_create_responce_invalid($inputData) {
        
        $response = $this->endPointClient->request('POST', '/[table_name]/', [
            RequestOptions::JSON => $inputData
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    
    public function validInputData()
    {
        return [
            // valid
            [
                [
                    
                    '[title_field]' => 'this is test data',
                    
                ]
            ]
        ];
    }

    public function inValidInputData()
    {
        return [
            // required tests
            [
                [
                    
                    '[title_field]' => 'test a',
                    
                ]
            ]
            
        ];
    }
}
