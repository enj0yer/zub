<?php

namespace Tests;

use Enj0yer\Zub\UrlBuilder;
use PHPUnit\Framework\TestCase;

function resultMessage(array $data): string {
    $result = [];
    foreach ($data as $key => $value) {
        $result[] = "$key: " . ((is_array($value)) ? ('{ ' .resultMessage($value) . ' }') : "$value");
    }
    return '[ ' . implode(" | ", $result) . ' ]';
}

class UrlBuilderTest extends TestCase {

    public function testEmptyUrl() {
        $this->assertEquals("", UrlBuilder::new("")->get(), "result must be an empty string");
    }

    public function testEmptyArrayOfUrlParts() {
        $this->assertEquals("", UrlBuilder::new(...[])->get(), "result must be an empty string");
    }

    public function testUrlsWithoutParameters() {
        $cases = [
            [
                'input' => 'http://mysite.org/home',
                'expected' => 'http://mysite.org/home'
            ],
            [
                'input' => 'https://mysite.org/home',
                'expected' => 'https://mysite.org/home',
            ],
            [
                'input' => 'http:///mYsItE.org///home////',
                'expected' => 'http://mYsItE.org/home/'
            ],
            [
                'input' => 'https:///mysite.org///home////',
                'expected' => 'https://mysite.org/home/'
            ],
            [
                'input' => 'https://home/',
                'expected' => 'https://home/'
            ]
        ];

        foreach ($cases as $case) {
            $result = UrlBuilder::new($case['input'])->get();
            $this->assertEquals($case['expected'], $result, resultMessage(array_merge($case, ['result' => $result])));
        }
    }

    public function testUrlsWithQueryParameters() {
        $cases = [
            [
                'input' => '',
                'expected' => '',
                'queryParams' => [],
            ],
            [
                'input' => '',
                'expected' => '',
                'queryParams' => [
                    'q' => 'cat'
                ],
            ],
            [
                'input' => 'http://mysite.org/search',
                'expected' => 'http://mysite.org/search?q=cat',
                'queryParams' => [
                    'q' => 'cat'
                ],
            ],
            [
                'input' => 'http:////////mysite.org////search///',
                'expected' => 'http://mysite.org/search?q=cat',
                'queryParams' => [
                    'q' => 'cat',
                ],
            ],
            [  
                'input' => 'https://mysite.org/search',
                'expected' => 'https://mysite.org/search?q=cat&filters=black,white',
                'queryParams' => [
                    'q' => 'cat',
                    'filters' => 'black,white'
                ],
            ],
            [  
                'input' => 'https://mysite.org/search',
                'expected' => 'https://mysite.org/search?q=cat&filters=black&filters=white',
                'queryParams' => [
                    'q' => 'cat',
                    'filters' => ['black', 'white']
                ],
            ],
            [  
                'input' => 'https://///mysite.org////search/',
                'expected' => 'https://mysite.org/search?q=cat&w=dog&e=horse&r=penguin&t=mouse&filters=black&filters=white&filters=grey',
                'queryParams' => [
                    'q' => 'cat',
                    'w' => 'dog',
                    'e' => 'horse',
                    'r' => 'penguin',
                    't' => 'mouse',
                    'filters' => ['black', 'white', 'grey']
                ],
            ],
        ];

        foreach ($cases as $case) {
            $result = UrlBuilder::new($case['input'])
                ->withQueryParameters($case['queryParams'])->get();
            $this->assertEquals($case['expected'], $result, resultMessage(array_merge($case, ['result' => $result])));
        }
    }

    public function testUrlsWithUrlParameters() {
        $cases = [
            [
                'input' => '',
                'expected' => '',
                'urlParams' => [],
            ],
            [
                'input' => '',
                'expected' => '',
                'urlParams' => [
                    'filename' => 'some.exe'
                ],
            ],
            [
                'input' => 'http://mysite.org/download/{filename}',
                'expected' => 'http://mysite.org/download/{filename}',
                'urlParams' => [],
            ],
            [
                'input' => 'http://mysite.org/download/{filename}',
                'expected' => 'http://mysite.org/download/penguin.gif',
                'urlParams' => [
                    'filename' => "penguin.gif"
                ],
            ],
            [
                'input' => 'https://mysite.org/{storage}/download/{filename}/',
                'expected' => 'https://mysite.org/4992182d-f92b-4cce-aa1c-6a4be6d2bd1a/download/penguin.gif/',
                'urlParams' => [
                    'storage' => '4992182d-f92b-4cce-aa1c-6a4be6d2bd1a',
                    'filename' => "penguin.gif"
                ],
            ],
            [
                'input' => 'https://mysite.org/download/{filename}/',
                'expected' => 'https://mysite.org/download/penguin.gif/',
                'urlParams' => [
                    'storage' => '4992182d-f92b-4cce-aa1c-6a4be6d2bd1a',
                    'filename' => "penguin.gif"
                ],
            ],
            
        ];

        foreach ($cases as $case) {
            $result = UrlBuilder::new($case['input'])
                ->withUrlParameters($case['urlParams'])->get();
            $this->assertEquals($case['expected'], $result, resultMessage(array_merge($case, ['result' => $result])));
        }
    }

    public function testUrlsWithAnyUrlOrQueryParameters() {
        $cases = [
            [
                'input' => 'http://mysite.org/download/{filename}',
                'expected' => 'http://mysite.org/download/{filename}',
                'urlParams' => [],
                'queryParams' => []
            ],
            [
                'input' => 'http://mysite.org/download/{filename}',
                'expected' => 'http://mysite.org/download/penguin.gif',
                'urlParams' => [
                    'filename' => 'penguin.gif',
                    'storage' => '4992182d-f92b-4cce-aa1c-6a4be6d2bd1a',

                ],
                'queryParams' => []
            ],
            [
                'input' => 'http://mysite.org/{storage}/download/{filename}',
                'expected' => 'http://mysite.org/4992182d-f92b-4cce-aa1c-6a4be6d2bd1a/download/penguin.gif?quality=good&timeout=30',
                'urlParams' => [
                    'filename' => 'penguin.gif',
                    'storage' => '4992182d-f92b-4cce-aa1c-6a4be6d2bd1a',

                ],
                'queryParams' => [
                    'quality' => 'good',
                    'timeout' => 30
                ]
            ],
            [
                'input' => 'http://mysite.org/{storage}/{}download/{filename}',
                'expected' => 'http://mysite.org/4992182d-f92b-4cce-aa1c-6a4be6d2bd1a/{}download/penguin.gif?quality=good&timeout=30&filename=horse.zip&replacement={filename}',
                'urlParams' => [
                    '' => 'bullshit',
                    'filename' => 'penguin.gif',
                    'storage' => '4992182d-f92b-4cce-aa1c-6a4be6d2bd1a',
                ],
                'queryParams' => [
                    'quality' => 'good',
                    'timeout' => 30,
                    'filename' => 'horse.zip',
                    'replacement' => '{filename}'
                ]
            ],
        ];
        
        foreach ($cases as $case) {
            // First apply url parameters, then query parameters
            $result = UrlBuilder::new($case['input'])
                ->withUrlParameters($case['urlParams'])
                ->withQueryParameters($case['queryParams'])->get();

            $this->assertEquals($case['expected'], $result, resultMessage(array_merge($case, ['result' => $result, 'order' => 'url first'])));
            
            
            // First apply query parameters, then url parameters
            $result = UrlBuilder::new($case['input'])
                ->withQueryParameters($case['queryParams'])
                ->withUrlParameters($case['urlParams'])->get();

            $this->assertEquals($case['expected'], $result, resultMessage(array_merge($case, ['result' => $result, 'order' => 'query first'])));
        }
    }
}