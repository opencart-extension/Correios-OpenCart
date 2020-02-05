<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CorreiosTest extends TestCase
{
  private $postcodeValid = '01001000';

  protected $correios;

  protected function setUp() : void
  {
    $this->correios = new \ValdeirPsr\Correios\Correios([
      'postcode' => $this->postcodeValid
    ], [
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
    ]);
  }

  public function testAddressInvalidConstructor(): void
  {
    $this->expectException(InvalidArgumentException::class);
    new \ValdeirPsr\Correios\Correios([], []);
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
    
    new \ValdeirPsr\Correios\Correios([
      'postcode' => $this->postcodeValid
    ], [
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
}