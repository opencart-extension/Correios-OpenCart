<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ServiceTest extends TestCase
{
  private $service;

  protected function setUp(): void
  {
    $this->service = new \ValdeirPsr\Correios\Service('04162', 'Sedex');
    $this->service->setMaximumLength(100);
    $this->service->setMaximumHeight(100);
    $this->service->setMaximumWidth(100);
    $this->service->setMaximumTotalDimension(200);
    $this->service->setMaximumTotalBox(10000);
  }

  public function testClassService()
  {
    $this->assertInstanceOf(\ValdeirPsr\Correios\Service::class, $this->service);
  }

  public function testConstructWithoutParameters()
  {
    $this->expectException(ArgumentCountError::class);
    $service = new \ValdeirPsr\Correios\Service();
  }

  /**
   * @dataProvider invalidContructProvider
   */
  public function testConstructInvalid($code, $name)
  {
    $this->expectException(InvalidArgumentException::class);
    new \ValdeirPsr\Correios\Service($code, $name);
  }

  /**
   * @dataProvider validBoxesProvider
   */
  public function testValidBoxesDefault($weight, $length, $width, $height, $total)
  {
    $box = new \ValdeirPsr\Correios\Box();
    $box->setPostcodeFrom('01001000');
    $box->setPostcodeTo('23078001');
    $box->setWeight($weight);
    $box->setLength($length);
    $box->setWidth($width);
    $box->setHeight($height);
    $box->setTotalBox($total);
    $box->setDeliveryByHand(true);
    $box->setReceiptNotice(true);
    
    $result = $this->service->validate($box);
    $this->assertTrue($result);
  }

  /**
   * @dataProvider invalidBoxesProvider
   */
  public function testValidBoxesCustom($weight, $length, $width, $height, $total)
  {
    $box = new \ValdeirPsr\Correios\Box();
    $box->setPostcodeFrom('01001000');
    $box->setPostcodeTo('23078001');
    $box->setWeight($weight);
    $box->setLength($length);
    $box->setWidth($width);
    $box->setHeight($height);
    $box->setTotalBox($total);
    $box->setDeliveryByHand(false);
    $box->setReceiptNotice(false);
    
    $this->service->setMinimumLength(1);
    $this->service->setMinimumWidth(1);
    $this->service->setMinimumHeight(1);
    $this->service->setMinimumTotalBox(1);

    $this->service->setMaximumLength(10000);
    $this->service->setMaximumWidth(10000);
    $this->service->setMaximumHeight(10000);
    $this->service->setMaximumWeight(10000);
    $this->service->setMaximumTotalDimension(10000);
    $this->service->setMaximumTotalBox(1000000);

    $result = $this->service->validate($box);
    $this->assertTrue($result);
  }

  /**
   * @dataProvider validQuotesProvider
   */
  public function testGetQuote($serviceConfig, $boxConfig, $expected)
  {
    $service = new \ValdeirPsr\Correios\Service($serviceConfig['code'], $serviceConfig['name']);
    $service->setMaximumWeight(60);
    $service->setMaximumTotalBox((int)$serviceConfig['code'] == 4014 ? 10000 : 3000);

    $box = new \ValdeirPsr\Correios\Box();
    $box->setPostcodeFrom($serviceConfig['from']);
    $box->setPostcodeTo($serviceConfig['to']);
    $box->setWeight($boxConfig['weight']);
    $box->setLength($boxConfig['length']);
    $box->setWidth($boxConfig['width']);
    $box->setHeight($boxConfig['height']);
    $box->setTotalBox($boxConfig['total']);
    $box->setDeliveryByHand($boxConfig['hand']);
    $box->setReceiptNotice(true);

    $quote = $service->getQuote($box);

    $this->assertTrue($this->assertQuote($quote, $expected));
  }
  
  /**
   * @dataProvider invalidBoxesProvider
   */
  public function testInvalidBoxes($weight, $length, $width, $height, $total)
  {
    $box = new \ValdeirPsr\Correios\Box();
    $box->setPostcodeFrom('01001000');
    $box->setPostcodeTo('23078001');
    $box->setWeight($weight);
    $box->setLength($length);
    $box->setWidth($width);
    $box->setHeight($height);
    $box->setTotalBox($total);
    $box->setDeliveryByHand(true);
    $box->setReceiptNotice(true);
    
    $result = $this->service->validate($box);
    $this->assertFalse($result);
  }

  public function invalidContructProvider()
  {
    return [
      [null, null],
      [false, false],
      [0, 0],
      ['', ''],

      [null, 'Sedex'],
      [false, 'PAC'],
      [0, 'Sedex 10'],
      ['', 'Sedex Hoje'],

      ['40444', null],
      ['40568', false],
      ['40606', 0],
      ['41106', '']
    ];
  }

  public function validBoxesProvider()
  {
    return [
      [0.3, 16, 11, 2, 100],
      [1, 16, 11, 2, 100],
      [10, 100, 20, 2, 1000],
      [20, 50, 20, 20, 1000],
      [30, 66, 67, 67, 10000],
    ];
  }

  public function invalidBoxesProvider()
  {
    return [
      [0.3, 15, 10, 1, 100],
      
      [1, 15, 11, 2, 100],
      [1, 16, 10, 2, 100],
      [1, 16, 11, 1, 100],

      [10, 100, 1, 100, 1000],
      [20, 100, 100, 20, 1000],
      [30, 67, 67, 67, 10000],

      [30, 66, 67, 67, 100000],
      
      [40, 66, 67, 67, 10000],
    ];
  }

  /**
   * Retorna os valores esperado para os testes.
   * Valores obtidos em 04/05/2020 às 19:28
   *
   * @return void
   */
  public function validQuotesProvider()
  {
    return [
      /**
       * http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?nCdEmpresa=&sDsSenha=&nCdServico=40010&sCepOrigem=96230000&sCepDestino=69358000&nVlPeso=0.3&nCdFormato=1&nVlAltura=2&nVlLargura=11&nVlComprimento=16&nVlDiametro=0&sCdMaoPropria=N&nVlValorDeclarado=100&sCdAvisoRecebimento=S
       */
      [
        [
          'code' => '40010', 
          'name' => 'Sedex',
          'from' => '96230000',
          'to'   => '69358000'
        ], [
          'weight' => 0.3,
          'length' => 16,
          'width' => 11,
          'height' => 2,
          'total' => 100,
          'hand' => false
        ], [
          'serviceCode' => '40010',
          'days' => 11,
          'priceTotal' => 160.54,
          'priceBase' => 152.6,
          'priceDeliveryByHand' => 0.0,
          'priceReceiptNotice' => 6.35,
          'priceInsuranceBox' => 1.59,
          'homeDelivery' => false,
          'deliverySaturday' => false,
          'error' => false
        ]
      ],

      /** http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?nCdEmpresa=&sDsSenha=&nCdServico=40010&sCepOrigem=96230000&sCepDestino=23078001&nVlPeso=1&nCdFormato=1&nVlAltura=2&nVlLargura=11&nVlComprimento=16&nVlDiametro=0&sCdMaoPropria=S&nVlValorDeclarado=100&sCdAvisoRecebimento=S */
      [
        [
          'code' => '40010', 
          'name' => 'Sedex',
          'from' => '96230000',
          'to'   => '23078001'
        ], [
          'weight' => 1,
          'length' => 16,
          'width' => 11,
          'height' => 2,
          'total' => 100,
          'hand' => true
        ], [
          'serviceCode' => '40010',
          'days' => 4,
          'priceTotal' => 90.44,
          'priceBase' => 75.0,
          'priceDeliveryByHand' => 7.5,
          'priceReceiptNotice' => 6.35,
          'priceInsuranceBox' => 1.59,
          'homeDelivery' => true,
          'deliverySaturday' => true,
          'error' => false
        ]
      ],
      
      /** http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?nCdEmpresa=&sDsSenha=&nCdServico=40010&sCepOrigem=23078001&sCepDestino=69358000&nVlPeso=1&nCdFormato=1&nVlAltura=2&nVlLargura=11&nVlComprimento=16&nVlDiametro=0&sCdMaoPropria=S&nVlValorDeclarado=100&sCdAvisoRecebimento=S */
      [
        [
          'code' => '40010', 
          'name' => 'PAC',
          'from' => '23078001',
          'to'   => '69358000'
        ], [
          'weight' => 1,
          'length' => 16,
          'width' => 11,
          'height' => 2,
          'total' => 100,
          'hand' => true
        ], [
          'serviceCode' => '40010',
          'days' => 9,
          'priceTotal' => 176.74,
          'priceBase' => 161.3,
          'priceDeliveryByHand' => 7.5,
          'priceReceiptNotice' => 6.35,
          'priceInsuranceBox' => 1.59,
          'homeDelivery' => false,
          'deliverySaturday' => false,
          'error' => false
        ]
      ],
      
      /** http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?nCdEmpresa=&sDsSenha=&nCdServico=4014&sCepOrigem=96230000&sCepDestino=69358000&nVlPeso=50&nCdFormato=1&nVlAltura=67&nVlLargura=66&nVlComprimento=67&nVlDiametro=0&sCdMaoPropria=S&nVlValorDeclarado=10000&sCdAvisoRecebimento=S */
      [
        [
          'code' => '4014', 
          'name' => 'Sedex | Máximo',
          'from' => '96230000',
          'to'   => '69358000'
        ], [
          'weight' => 50,
          'length' => 67,
          'width' => 66,
          'height' => 67,
          'total' => 10000,
          'hand' => true
        ], [
          'serviceCode' => '4014',
          'days' => 11,
          'priceTotal' => 2960.94,
          'priceBase' => 2747.5,
          'priceDeliveryByHand' => 7.5,
          'priceReceiptNotice' => 6.35,
          'priceInsuranceBox' => 199.59,
          'homeDelivery' => false,
          'deliverySaturday' => false,
          'error' => false
        ]
      ],

      [
        [
          'code' => '04510', 
          'name' => 'PAC | Máximo',
          'from' => '96230000',
          'to'   => '69358000'
        ], [
          'weight' => 50,
          'length' => 67,
          'width' => 66,
          'height' => 67,
          'total' => 3000,
          'hand' => true
        ], [
          'serviceCode' => '4510',
          'days' => 38,
          'priceTotal' => 1499.24,
          'priceBase' => 1425.8,
          'priceDeliveryByHand' => 7.5,
          'priceReceiptNotice' => 6.35,
          'priceInsuranceBox' => 59.59,
          'homeDelivery' => false,
          'deliverySaturday' => false,
          'error' => false
        ]
      ],

      [
        [
          'code' => '04227', 
          'name' => 'PAC Mini Envios',
          'from' => '96230000',
          'to'   => '69358000'
        ], [
          'weight' => 0.3,
          'length' => 16,
          'width' => 11,
          'height' => 2,
          'total' => 100,
          'hand' => false
        ], [
          'serviceCode' => '4227',
          'days' => 40,
          'priceTotal' => 41.2,
          'priceBase' => 33.05,
          'priceDeliveryByHand' => 0.0,
          'priceReceiptNotice' => 6.35,
          'priceInsuranceBox' => 1.8,
          'homeDelivery' => false,
          'deliverySaturday' => false,
          'error' => false
        ],

        [
          [
            'code' => '04227', 
            'name' => 'PAC Mini Envios',
            'from' => '96230000',
            'to'   => '69358000'
          ], [
            'weight' => 0.4,
            'length' => 16,
            'width' => 11,
            'height' => 2,
            'total' => 100,
            'hand' => false
          ], [
            'serviceCode' => '4227',
            'days' => 40,
            'priceTotal' => 0.0,
            'priceBase' => 0.0,
            'priceDeliveryByHand' => 0.0,
            'priceReceiptNotice' => 0.0,
            'priceInsuranceBox' => 0.0,
            'homeDelivery' => false,
            'deliverySaturday' => false,
            'error' => true
          ]
        ]
      ]
    ];
  }

  /**
   * Verifica se o objeto $quote está de acordo com o array
   * $expected
   * 
   * array['expected']
   *    array['serviceCode'] : string
   *    array['days'] : int
   *    array['priceTotal'] : float
   *    array['priceBase'] : float
   *    array['priceDeliveryByHand'] : float
   *    array['priceReceiptNotice'] : float
   *    array['priceInsuranceBox'] : float
   *    array['homeDelivery'] : boolean
   *    array['deliverySaturday'] : boolean
   *    array['error'] : boolean
   *
   * @param \ValdeirPsr\Correio\Quote $quote
   * @param array $expected
   * @return boolean
   */
  private function assertQuote($quote, $expected)
  {
    $isValid['ServiceCode'] = $expected['serviceCode'] === $quote->getServiceCode();
    $isValid['Days'] = $expected['days'] === $quote->getDays();
    $isValid['PriceTotal'] = $expected['priceTotal'] === $quote->getPriceTotal();
    $isValid['PriceBase'] = $expected['priceBase'] === $quote->getPriceBase();
    $isValid['PriceDeliveryByHand'] = $expected['priceDeliveryByHand'] === $quote->getPriceDeliveryByHand();
    $isValid['PriceReceiptNotice'] = $expected['priceReceiptNotice'] === $quote->getPriceReceiptNotice();
    $isValid['PriceInsuranceBox'] = $expected['priceInsuranceBox'] === $quote->getPriceInsuranceBox();
    $isValid['HomeDelivery'] = $expected['homeDelivery'] === $quote->getHomeDelivery();
    $isValid['DeliverySaturday'] = $expected['deliverySaturday'] === $quote->getDeliverySaturday();
    $isValid['Error'] = $expected['error'] === !!$quote->getError();

    $result = array_filter($isValid, function($b) {
      return $b === false;
    });

    return empty($result) === true;
  }
}