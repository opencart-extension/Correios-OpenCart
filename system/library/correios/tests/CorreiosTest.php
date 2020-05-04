<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CorreiosTest extends TestCase
{
  private $postcodeValid = '01001000';

  protected $correios;
  protected $services = [];

  protected function setUp() : void
  {
    $sedex = new \ValdeirPsr\Correios\Service('4014', 'Sedex');
    $sedex->setMaximumTotalBox(10000);

    $pac = new \ValdeirPsr\Correios\Service('4510', 'PAC');

    $this->services = [
      $sedex,
      $pac,
    ];

    $this->correios = new \ValdeirPsr\Correios\Correios($this->services[0], [
      'postcode' => $this->postcodeValid
    ], [
      [
        'shipping'        => $this->postcodeValid,
        'quantity'        => 1,
        'price'           => 1,
        'total'           => 1,
        'weight'          => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1
      ]
    ]);
  }

  public function testAddressInvalidConstructor(): void
  {
    $this->expectException(InvalidArgumentException::class);
    new \ValdeirPsr\Correios\Correios([], [], []);
  }

  /**
   * @dataProvider productsInvalidProvider
   */
  public function testProductsInvalidConstructor(
    $shipping, 
    $qnty, 
    $price, 
    $total, 
    $weight, 
    $length, 
    $width, 
    $height
  ): void {
    $this->expectException(InvalidArgumentException::class);
    
    new \ValdeirPsr\Correios\Correios($this->services[0], [
      'postcode' => $this->postcodeValid
    ], [
      [
        'shipping'        => $shipping,
        'quantity'        => $qnty,
        'price'           => $price,
        'total'           => $total,
        'weight'          => $weight,
        'length'          => $length,
        'width'           => $width,
        'height'          => $height
      ]
    ]);
  }

  /**
   * @dataProvider discountInvalidProvider
   */
  public function testDiscountInvalid($discount, $type)
  {
    $this->expectException(InvalidArgumentException::class);
    $this->correios->setDiscount($discount, $type);
  }

  /**
   * @dataProvider discountValidProvider
   */
  public function testDiscountValid($discount, $type, $expect)
  {
    $this->correios->setDiscount($discount, $type);
    $this->assertEquals($this->correios->getDiscount(), $expect);
  }

  /**
   * @dataProvider daysAdditionalInvalidProvider
   */
  public function testDaysAdditionalInvalid($days)
  {
    $this->expectException(InvalidArgumentException::class);
    $this->correios->setDaysAdditional($days);
  }
  
  /**
   * @dataProvider daysAdditionalValidProvider
   */
  public function testDaysAdditionalValid($days)
  {
    $this->correios->setDaysAdditional($days);

    $this->assertEquals($this->correios->getDaysAdditional(), $days);
  }

  /**
   * @param array $products
   * @param int $day Prazo de entrega
   * @param float $price Valor total da entrega
   * 
   * @dataProvider quotesValidProvider
   */
  public function testQuoteWithProductsValid($products, $expectDays, $expectPriceTotal)
  {
    $priceTotal = $daysTotal = 0;

    foreach($this->services as $service) {
      $correios = new \ValdeirPsr\Correios\Correios($service, [
        'postcode' => '01001000'
      ], $products);
  
      try {
        $quotes = $correios->getQuote();
    
        /** Soma todos os valores */
        $priceTotal += array_reduce($quotes, function($a, $b) {
          return $a += $b->getPriceTotal();
        }, 0);
    
        /** Captura o maior prazo */
        $daysTotal = array_reduce($quotes, function($a, $b) {
          $days = $b->getDays();
          return $a > $days ? $a : $days;
        }, $daysTotal);
      } catch (InvalidArgumentException $e) {
        /** Box inválid */
        continue;
      }
    }

   $this->assertEquals([
     $expectPriceTotal,
     $expectDays
   ], [
     $priceTotal,
     $daysTotal
   ]);
  }

  /**
   * @param array $products
   * @param int $day Prazo de entrega
   * @param float $price Valor total da entrega
   * 
   * @dataProvider quotesValidProvider
   */
  public function testQuoteWithProductsValidWithDaysAdditional($products, $expectDays)
  {
    /** Aumenta o prazo em 10 dias */
    $expectDays += 10;
    $daysTotal = 10;

    foreach($this->services as $service) {
      $correios = new \ValdeirPsr\Correios\Correios($service, [
        'postcode' => '01001000'
      ], $products);
      
      $correios->setDaysAdditional(10);
  
      try {
        $quotes = $correios->getQuote();
    
        /** Captura o maior prazo */
        $daysTotal = array_reduce($quotes, function($a, $b) {
          $days = $b->getDays();
          return $a > $days ? $a : $days;
        }, $daysTotal);
      } catch (InvalidArgumentException $e) {
        /** Box inválid */
        continue;
      }
    }

    $this->assertEquals($expectDays, $daysTotal);
  }

  /**
   * @param array $products
   * @param int $day Prazo de entrega
   * @param float $price Valor total da entrega
   * 
   * @dataProvider quotesAllValidProvider
   */
  public function testQuoteWithProductsValidAndDiscountAndServicesInvalid($products)
  {
    $this->expectException(\UnexpectedValueException::class);

    $this->services = [new \ValdeirPsr\Correios\Service('invalid', 'Inválido')];

    $priceTotal = 0;

    foreach($this->services as $service) {
      $correios = new \ValdeirPsr\Correios\Correios($service, [
        'postcode' => '01001000'
      ], $products);
      
      $correios->setDiscount(10);
  
      try {
        $correios->getQuote();
      } catch (InvalidArgumentException $e) {
        /** Box inválid */
        continue;
      }
    }
  }

  /**
   * @param array $products
   * @param int $day Prazo de entrega
   * @param float $price Valor total da entrega
   * 
   * @dataProvider quotesAllValidProvider
   */
  public function testQuoteWithProductsValidAndDaysAdditionalAndServicesInvalid($products)
  {
    $this->expectException(\UnexpectedValueException::class);

    $this->services = [new \ValdeirPsr\Correios\Service('invalid', 'Inválido')];

    $priceTotal = 0;

    foreach($this->services as $service) {
      $correios = new \ValdeirPsr\Correios\Correios($service, [
        'postcode' => '01001000'
      ], $products);
      
      $correios->setDaysAdditional(10);
  
      try {
        $correios->getQuote();
      } catch (InvalidArgumentException $e) {
        /** Box inválid */
        continue;
      }
    }
  }

  /**
   * @param array $products
   * @param int $day Prazo de entrega
   * @param float $price Valor total da entrega
   * 
   * @dataProvider quotesValidDiscountProvider
   */
  public function testQuoteWithProductsValidWithDiscount($products, $discount, $discountType, $expectPriceTotal)
  {
    $priceTotal = 0;

    foreach($this->services as $service) {
      $correios = new \ValdeirPsr\Correios\Correios($service, [
        'postcode' => '01001000'
      ], $products);
      
      $correios->setDiscount($discount, $discountType);
  
      try {
        $quotes = $correios->getQuote();
    
        /** Soma todos os valores */
        $priceTotal += array_reduce($quotes, function($a, $b) {
          return $a += $b->getPriceTotal();
        }, 0);
      } catch (InvalidArgumentException $e) {
        /** Box inválid */
        continue;
      }
    }

    $this->assertEquals($expectPriceTotal, $priceTotal);
  }

  public function productsInvalidProvider()
  {
    return [
      [
        'shipping'        => '010010',
        'quantity'        => 1,
        'price'           => 1,
        'total'           => 1,
        'weight'          => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1
      ],
      [
        'shipping'        => '01001000',
        'quantity'        => -1,
        'price'           => 1,
        'total'           => 1,
        'weight'          => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1
      ],
      [
        'shipping'        => 'AAAAAAA',
        'quantity'        => 1,
        'price'           => 1,
        'total'           => 1,
        'weight'          => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1
      ]
    ];
  }

  public function discountInvalidProvider()
  {
    return [
      ['AAA', 'f'],
      ['0123,', 'f'],
      ['12,15', 'p'],
      ['12A15', 'p'],

      [1, true],
      [10, false],
      [50, 'A'],
      [100, null],
    ];
  }

  public function discountValidProvider()
  {
    return [
      [0, 'f', 0],
      [100, 'f', 100.00],
      [100.1, 'F', 100.10],
      [100.19, "\x46", 100.19],
      ['0159', 'p', 159.00],
      ['10.15', 'P', 10.15],
      [PHP_INT_MAX, "\x70", number_format(PHP_INT_MAX, 2, '.', '')]
    ];
  }

  public function daysAdditionalInvalidProvider()
  {
    return [
      [21.5],
      ['15.8'],
      [14.98],
      ['ABC'],
      ['1A3549'],
      ['0x1A']
    ];
  }

  public function daysAdditionalValidProvider()
  {
    return [
      [21],
      ['15'],
      [PHP_INT_MAX],
      [-1],
      [0x1A]
    ];
  }

  /**
   * Valores cotados no dia 02/05/2020
   */
  public function quotesValidProvider()
  {
    return [
      [
        [
          [
            'shipping' => '23078001',
            'quantity' => '20',
            'price' => '100',
            'total' => '100',
            'weight' => '1',
            'length' => '16',
            'width' => '11',
            'height' => '1',
          ]
        ],
        8,
        650.92
      ],

      [
        [
          [
            'shipping' => '23078001',
            'quantity' => '1',
            'price' => '1000',
            'total' => '1000',
            'weight' => '2',
            'length' => '30',
            'width' => '30',
            'height' => '30',
          ]
        ],
        8,
        112.38
      ],

      [
        [
          [
            'shipping' => '23078001',
            'quantity' => '2',
            'price' => '500',
            'total' => '1000',
            'weight' => '1',
            'length' => '40',
            'width' => '40',
            'height' => '40',
          ]
        ],
        8,
        395.76
      ],

      [
        [
          [
            'shipping' => '23078001',
            'quantity' => '10',
            'price' => '500',
            'total' => '50000',
            'weight' => '1',
            'length' => '20',
            'width' => '21',
            'height' => '22',
          ],

          [
            'shipping' => '01001001',
            'quantity' => '1',
            'price' => '100',
            'total' => '100',
            'weight' => '0.3',
            'length' => '16',
            'width' => '11',
            'height' => '10',
          ],

          [
            'shipping' => '58111232',
            'quantity' => '1',
            'price' => '2500',
            'total' => '2500',
            'weight' => '5',
            'length' => '21',
            'width' => '30',
            'height' => '28',
          ]
        ],
        14,
        3071.88 //2665.12 + 45.18 + 361.58
      ],

      [
        [
          [
            'shipping' => '58111232',
            'quantity' => '10',
            'price' => '500',
            'total' => '500',
            'weight' => '5',
            'length' => '16',
            'width' => '16',
            'height' => '2',
          ],
      
          [
            'shipping' => '58111232',
            'quantity' => '1',
            'price' => '3500',
            'total' => '3500',
            'weight' => '5',
            'length' => '16',
            'width' => '16',
            'height' => '16',
          ]
        ],
        11,
        2528.97, //Ignore o serviço PAC, pois há produto que excete os limites mínimos,

        [
          [
            [
              'shipping' => '96230000',
              'quantity' => '1',
              'price' => '500',
              'total' => '500',
              'weight' => '5',
              'length' => '14', //Valor inválido. O código deve alterar para o mínimo: 15
              'width' => '11',
              'height' => '2',
            ]
          ],
          11,
          168.8
        ],

        [
          [
            [
              'shipping' => '96230000',
              'quantity' => '1',
              'price' => '20.5',
              'total' => '20.5',
              'weight' => '5',
              'length' => '15',
              'width' => '10', //Valor inválido. O código deve alterar para o mínimo: 11
              'height' => '2',
            ]
          ],
          11,
          168.8
        ],

        [
          [
            [
              'shipping' => '96230000',
              'quantity' => '1',
              'price' => '20.5',
              'total' => '20.5',
              'weight' => '5',
              'length' => '15',
              'width' => '11',
              'height' => '1', //Valor inválido. O código deve alterar para o mínimo: 2
            ]
          ],
          11,
          168.8
        ],

        [
          [
            [
              'shipping' => '96230000',
              'quantity' => '1',
              'price' => '1', //Valor inválido. O código deve alterar para o mínimo: 20.5
              'total' => '1', //Valor inválido. O código deve alterar para o mínimo: 20.5
              'weight' => '5',
              'length' => '15',
              'width' => '11',
              'height' => '1',
            ]
          ],
          11,
          187.98
        ]
      ]
    ];
  }

  /**
   * Valores cotados no dia 02/05/2020
   */
  public function quotesAllValidProvider()
  {
    return [
      [
        [
          [
            'shipping' => '23078001',
            'quantity' => '20',
            'price' => '100',
            'total' => '100',
            'weight' => '1',
            'length' => '16',
            'width' => '11',
            'height' => '1',
          ]
        ]
      ],

      [
        [
          [
            'shipping' => '23078001',
            'quantity' => '1',
            'price' => '1000',
            'total' => '1000',
            'weight' => '2',
            'length' => '30',
            'width' => '30',
            'height' => '30',
          ]
        ]
      ]
    ];
  }

  /**
   * Valores cotados no dia 04/05/2020
   */
  public function quotesValidDiscountProvider()
  {
    return [
      [
        [
          [
            'shipping' => '23078001',
            'quantity' => '2',
            'price' => '500',
            'total' => '1000',
            'weight' => '1',
            'length' => '40',
            'width' => '40',
            'height' => '40',
          ]
        ],
        25,
        'p',
        296.82
      ],

      [
        [
          [
            'shipping' => '58111232',
            'quantity' => '1',
            'price' => '2500',
            'total' => '2500',
            'weight' => '5',
            'length' => '21',
            'width' => '30',
            'height' => '28',
          ]
        ],
        25,
        'f',
        311.58
      ],

      [
        [      
          [
            'shipping' => '58111232',
            'quantity' => '1',
            'price' => '3500',
            'total' => '3500',
            'weight' => '5',
            'length' => '16',
            'width' => '16',
            'height' => '16',
          ]
        ],
        99,
        'p',
        2.65
      ]
    ];
  }
}