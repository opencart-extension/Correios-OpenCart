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
    $this->services = [
      new \ValdeirPsr\Correios\Service('4014', 'Sedex'),
      new \ValdeirPsr\Correios\Service('4510', 'PAC'),
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
        'weight_class_id' => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1,
        'length_class_id' => 1
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
    $weightClassId, 
    $length, 
    $width, 
    $height, 
    $lengthClassId
  ): void {
    $this->expectException(UnexpectedValueException::class);
    
    new \ValdeirPsr\Correios\Correios($this->services[0], [
      'postcode' => $this->postcodeValid
    ], [
      [
        'shipping'        => $shipping,
        'quantity'        => $qnty,
        'price'           => $price,
        'total'           => $total,
        'weight'          => $weight,
        'weight_class_id' => $weightClassId,
        'length'          => $length,
        'width'           => $width,
        'height'          => $height,
        'length_class_id' => $lengthClassId
      ]
    ]);
  }

  /**
   * @dataProvider discountInvalidProvider
   */
  public function testDiscountInvalid($discount)
  {
    $this->expectException(UnexpectedValueException::class);
    $this->correios->setDiscount($discount);
  }

  /**
   * @dataProvider discountValidProvider
   */
  public function testDiscountValid($discount, $expect)
  {
    $this->correios->setDiscount($discount);
    $this->assertEquals($this->correios->getDiscount(), $expect);
  }

  /**
   * @dataProvider daysAdditionalInvalidProvider
   */
  public function testDaysAdditionalInvalid($days)
  {
    $this->expectException(UnexpectedValueException::class);
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
    }

    /** Compara os valores */
    $vPrice = $expectPriceTotal === $priceTotal;
    $vExpectDays = $expectDays === $daysTotal;

    $this->assertTrue($vPrice && $vExpectDays);
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
        'weight_class_id' => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1,
        'length_class_id' => 1
      ],
      [
        'shipping'        => '01001000',
        'quantity'        => -1,
        'price'           => 1,
        'total'           => 1,
        'weight'          => 1,
        'weight_class_id' => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1,
        'length_class_id' => 1
      ],
      [
        'shipping'        => 'AAAAAAA',
        'quantity'        => 1,
        'price'           => 1,
        'total'           => 1,
        'weight'          => 1,
        'weight_class_id' => 1,
        'length'          => 1,
        'width'           => 1,
        'height'          => 1,
        'length_class_id' => 1
      ]
    ];
  }

  public function discountInvalidProvider()
  {
    return [
      ['AAA'],
      ['0123,'],
      ['12,15'],
      ['12A15']
    ];
  }

  public function discountValidProvider()
  {
    return [
      [100, 100.00],
      [100.1, 100.10],
      [100.19, 100.19],
      ['0159', 159.00],
      ['10.15', 10.15],
      [PHP_INT_MAX, number_format(PHP_INT_MAX, 2, '.', '')]
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
            'weight_class_id' => '1',
            'length' => '16',
            'width' => '11',
            'height' => '1',
            'length_class_id' => '1',
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
            'length_class_id' => '1',
            'weight_class_id' => '1',
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
            'length_class_id' => '1',
            'weight_class_id' => '1',
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
            'length_class_id' => '1',
            'weight_class_id' => '1',
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
            'length_class_id' => '1',
            'weight_class_id' => '1',
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
            'length_class_id' => '1',
            'weight_class_id' => '1',
          ]
        ],
        14,
        3071.88 //2665.12 + 45.18 + 361.58
      ]
    ];
  }
}