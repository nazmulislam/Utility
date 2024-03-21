<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Domain\[DomainFolder];

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class [ClassName]CacheKeys
{
  
  const GET_[CONSTANTS]_ALL_TABLE = [
    'key' => 'sampleRepository.getAllSampleTable',
    'expiresAfter' => 86400,
    'isEnabled' => true
  ];
  const GET_[CONSTANTS]_ALL = [
    'key' => 'sampleRepository.getSample',
    'expiresAfter' => 86400,
    'isEnabled' => true
  ];

  const [CONSTANTS]_TAG = 'sampleTag';
}
