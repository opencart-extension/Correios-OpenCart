<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BoxTest extends TestCase
{
  private $box;

  protected function setUp(): void
  {
    $this->box = new \ValdeirPsr\Correios\Box;
  }

  public function testClassBox()
  {
    $this->assertInstanceOf(\ValdeirPsr\Correios\Box::class, $this->box);
  }

  public function testContractCode()
  {
    $this->box->setContractCode('  My Code \'Contract       ');

    $this->assertEquals("My Code 'Contract", $this->box->getContractCode());
  }

  public function testContractPassword()
  {
    $this->box->setContractPassword('  M-_C*od%e \'Co#   ');

    $this->assertEquals("M-_C*od%e 'Co#", $this->box->getContractPassword());
  }

  /**
   * @dataProvider postcodeValidProvider
   */
  public function testPostcodeFrom($postcode)
  {
    $postcodeF = preg_replace("/\D/", "", $postcode);

    $this->box->setPostcodeFrom($postcode);
    $this->assertEquals($postcodeF, $this->box->getPostcodeFrom());
  }
  
  /**
   * @dataProvider postcodeValidProvider
   */
  public function testPostcodeTo($postcode)
  {
    $postcodeF = preg_replace("/\D/", "", $postcode);

    $this->box->setPostcodeTo($postcode);
    $this->assertEquals($postcodeF, $this->box->getPostcodeTo());
  }

  /**
   * @dataProvider postcodeInvalidValidProvider
   */
  public function testPostcodeFromInvalid($postcode)
  {
    $this->expectException(UnexpectedValueException::class);
    $this->box->setPostcodeFrom($postcode);
  }

  /**
   * @dataProvider postcodeInvalidValidProvider
   */
  public function testPostcodeToInvalid($postcode)
  {
    $this->expectException(UnexpectedValueException::class);
    $this->box->setPostcodeTo($postcode);
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testWeigthValid($value, $expected)
  {
    $this->box->setWeight($value);
    $this->assertSame($expected, $this->box->getWeight());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testLengthValid($value, $expected)
  {
    $this->box->setLength($value);
    $this->assertSame($expected, $this->box->getLength());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testWidthValid($value, $expected)
  {
    $this->box->setWidth($value);
    $this->assertSame($expected, $this->box->getWidth());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testHeightValid($value, $expected)
  {
    $this->box->setHeight($value);
    $this->assertSame($expected, $this->box->getHeight());
  }

  /**
   * @dataProvider booleanValidProvider
   */
  public function testDeliveryByHand($value, $expected)
  {
    $this->box->setDeliveryByHand($value);
    $this->assertSame($expected, $this->box->getDeliveryByHand());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testTotalBoxValid($value, $expected)
  {
    $this->box->setTotalBox($value);
    $this->assertSame($expected, $this->box->getTotalBox());
  }

  /**
   * @dataProvider booleanValidProvider
   */
  public function testReceiptNotice($value, $expected)
  {
    $this->box->setReceiptNotice($value);
    $this->assertSame($expected, $this->box->getReceiptNotice());
  }

  public function postcodeValidProvider()
  {
    return [
      ['13035680'],
      ['13.035680'],
      ['13035-680'],
      ['13.035-680'],
      ['13.035.680'],
      ['13-035-680']
    ];
  }

  public function postcodeInvalidValidProvider()
  {
    return [
      ['130356800'],
      ['13.03568'],
      ['13O35A680'],
      ['13.'],
      ['01010000 01010011 01010010']
    ];
  }

  public function floatValidProvider()
  {
    return [
      ['100', 100.00],
      ['100.1', 100.10],
      ['100.11', 100.11],
      [12.5549649496496494544, 12.55],
      [PHP_INT_MAX, floatval(number_format(PHP_INT_MAX, 2, '.', ''))]
    ];
  }

  public function booleanValidProvider()
  {
    return [
      ['valdeir_psr', true],
      ['1', true],
      [1, true],
      [true, true],
      [0, false],
      [null, false],
      ['', false],
      [false, false]
    ];
  }
}