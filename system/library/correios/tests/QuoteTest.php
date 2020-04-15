<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class QuoteTest extends TestCase
{
  private $quote;

  protected function setUp() : void
  {
    $this->quote = new \ValdeirPsr\Correios\Quote;
  }

  public function testInstanceOfClass()
  {
    $this->assertInstanceOf(\ValdeirPsr\Correios\Quote::class, $this->quote);
  }

  /**
   * @dataProvider intValidProvider
   */
  public function testDays($value, $expect)
  {
    $this->quote->setDays($value);
    $this->assertSame($expect, $this->quote->getDays());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testPriceTotal($value, $expect)
  {
    $this->quote->setPriceTotal($value);
    $this->assertSame($expect, $this->quote->getPriceTotal());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testPriceBase($value, $expect)
  {
    $this->quote->setPriceBase($value);
    $this->assertSame($expect, $this->quote->getPriceBase());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testPriceDeliveryByHand($value, $expect)
  {
    $this->quote->setPriceDeliveryByHand($value);
    $this->assertSame($expect, $this->quote->getPriceDeliveryByHand());
  }

  /**
   * @dataProvider floatValidProvider
   */
  public function testPriceReceiptNotice($value, $expect)
  {
    $this->quote->setPriceReceiptNotice($value);
    $this->assertSame($expect, $this->quote->getPriceReceiptNotice());
  }

  public function intValidProvider()
  {
    return [
      ['123456', 123456],
      [0x7E4, 2020],
      [123, 123],
      [123.45, 123],
      [PHP_INT_MAX, PHP_INT_MAX]
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
}