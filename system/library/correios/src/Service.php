<?php

namespace ValdeirPsr\Correios;

class Service
{
  /** @var string */
  protected $name;
  
  /** @var string */
  protected $code;
  
  /** @var float cm */
  protected $minimumLength = 16;
  
  /** @var float cm */
  protected $minimumWidth = 11;
  
  /** @var float cm */
  protected $minimumHeight = 2;

  /** @var float R$ */
  protected $minimumTotalBox = 20.5;
  
  /** @var float KG */
  protected $minimumWeight = 0.1;
  
  /** @var float cm */
  protected $maximumLength = 100;
  
  /** @var float cm */
  protected $maximumWidth = 100;
  
  /** @var float cm */
  protected $maximumHeight = 100;
  
  /** @var float KG */
  protected $maximumWeight = 30;

  /** @var int Length + Width + Height */
  protected $maximumTotalDimension = 200;

  /** @var float R$ */
  protected $maximumTotalBox = 3000;

  /**
   * Cria uma instância do objeto
   *
   * @param string $code
   * @param string $name
   */
  public function __construct($code, $name)
  {
    if (empty($code)) throw new \InvalidArgumentException('Field code is required');
    if (empty($name)) throw new \InvalidArgumentException('Field name is required');

    $this->code = $code;
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * @param float $value
   */
  public function setMinimumLength($value)
  {
    $this->minimumLength = floatval($value);
  }

  public function getMinimumLength()
  {
    return $this->minimumLength;
  }

  /**
   * @param float $value
   */
  public function setMinimumWidth($value)
  {
    $this->minimumWidth = floatval($value);
  }

  public function getMinimumWidth()
  {
    return $this->minimumWidth;
  }

  /**
   * @param float $value
   */
  public function setMinimumHeight($value)
  {
    $this->minimumHeight = floatval($value);
  }

  /**
   * @param float $value
   */
  public function getMinimumHeight()
  {
    return $this->minimumHeight;
  }

  /**
   * @param float $value
   */
  public function setMinimumTotalBox($value)
  {
    $this->minimumTotalBox = floatval($value);
  }

  /**
   * @param float $value
   */
  public function getMinimumTotalBox()
  {
    return $this->minimumTotalBox;
  }

  /**
   * @param float $value
   */
  public function setMaximumLength($value)
  {
    $this->maximumLength = floatval($value);
  }

  /**
   * @param float $value
   */
  public function getMaximumLength()
  {
    return $this->maximumLength;
  }

  /**
   * @param float $value
   */
  public function setMaximumWidth($value)
  {
    $this->maximumWidth = floatval($value);
  }

  /**
   * @param float $value
   */
  public function getMaximumWidth()
  {
    return $this->maximumWidth;
  }

  /**
   * @param float $value
   */
  public function setMaximumHeight($value)
  {
    $this->maximumHeight = floatval($value);
  }

  /**
   * @param float $value
   */
  public function getMaximumHeight()
  {
    return $this->maximumHeight;
  }

  /**
   * @param float $value
   */
  public function setMaximumWeight($value)
  {
    $this->maximumWeight = $value;
  }

  /**
   * @param float $value
   */
  public function getMaximumWeight()
  {
    return $this->maximumWeight;
  }

  /**
   * @param float $value
   */
  public function getMinimumWeight()
  {
    return $this->minimumWeight;
  }

  /**
   * @param float $value
   */
  public function setMaximumTotalDimension($value)
  {
    $this->maximumTotalDimension = floatval($value);
  }

  /**
   * @param float $value
   */
  public function getMaximumTotalDimension()
  {
    return $this->maximumTotalDimension;
  }

  /**
   * @param float $value
   */
  public function setMaximumTotalBox($value)
  {
    $this->maximumTotalBox = floatval($value);
  }

  /**
   * @param float $value
   */
  public function getMaximumTotalBox()
  {
    return $this->maximumTotalBox;
  }

  /**
   * @param \ValdeirPsr\Correios\Box $box
   * @return Quote
   */
  public function getQuote(Box $box)
  {
    if (!$this->validate($box)) throw new \InvalidArgumentException('Box invalid');

    $query = [
      'nCdEmpresa' => $box->getContractCode(),
      'sDsSenha' => $box->getContractPassword(),
      'nCdServico' => $this->code,
      'sCepOrigem' => $box->getPostcodeFrom(),
      'sCepDestino' => $box->getPostcodeTo(),
      'nVlPeso' => $box->getWeight(),
      'nCdFormato' => 1,
      'nVlAltura' => $box->getHeight(),
      'nVlLargura' => $box->getWidth(),
      'nVlComprimento' => $box->getLength(),
      'nVlDiametro' => 0,
      'sCdMaoPropria' => $box->getDeliveryByHand() ? 'S' : 'N',
      'nVlValorDeclarado' => $box->getTotalBox(),
      'sCdAvisoRecebimento' => $box->getReceiptNotice() ? 'S' : 'N',
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => sprintf('http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?%s', http_build_query($query)),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTPGET => true,
      CURLOPT_USERAGENT => 'Correios for Opencart <https://www.valdeirsantana.com.br>'
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);

    if ($error) throw new \RuntimeException($error);

    $xml = new \SimpleXMLElement($response);
    $service = $xml->Servicos->cServico;

    $quote = new Quote;
    $quote->setServiceCode(strval($service->Codigo));
    $quote->setDays((int)$service->PrazoEntrega);
    $quote->setPriceTotal($this->formatMoney($service->Valor));
    $quote->setPriceBase($this->formatMoney($service->ValorSemAdicionais));
    $quote->setPriceDeliveryByHand($this->formatMoney($service->ValorMaoPropria));
    $quote->setPriceReceiptNotice($this->formatMoney($service->ValorAvisoRecebimento));
    $quote->setPriceInsuranceBox($this->formatMoney($service->ValorValorDeclarado));
    $quote->setHomeDelivery(strval($service->EntregaDomiciliar) === 'S');
    $quote->setDeliverySaturday(strval($service->EntregaSabado) === 'S');
    $quote->setError(strval($service->MsgErro));
    
    return $quote;
  }

  /**
   * @param \ValdeirPsr\Correios\Box $box
   * @return boolean
   */
  public function validate(Box $box, $validateMinimum = true)
  {
    $vLength     = ($box->getLength() <= $this->maximumLength);
    $vWidth      = ($box->getWidth() <= $this->maximumWidth);
    $vHeight     = ($box->getHeight() <= $this->maximumHeight);
    $vSum        = ($box->getLength() + $box->getWidth() + $box->getHeight()) <= $this->maximumTotalDimension;
    $vPriceTotal = ($box->getTotalBox() <= $this->maximumTotalBox);
    $vWeight     = ($box->getWeight() <= $this->maximumWeight);

    if ($validateMinimum) {
      $vPriceTotal = ($box->getTotalBox() >= $this->minimumTotalBox) && $vPriceTotal;
      $vLength     = ($box->getLength() >= $this->minimumLength) && $vLength;
      $vWidth      = ($box->getWidth() >= $this->minimumWidth) && $vWidth;
      $vHeight     = ($box->getHeight() >= $this->minimumHeight) && $vHeight;
    }

    $vPostcodeFrom = !empty($box->getPostcodeFrom());
    
    return $vLength 
      && $vWidth 
      && $vHeight 
      && $vSum 
      && $vPriceTotal 
      && $vWeight
      && $vPostcodeFrom;
  }

  private function formatMoney($value)
  {
    return floatval(str_replace(",", ".", str_replace(".", "", $value)));
  }
}