<?php

namespace ValdeirPsr\Correios;

/**
 * Classe responsável por armazenar os dados de cada caixa.
 * Desta forma, é possível armazenar múltiplos produtos
 * numa mesma embalagem.
 * 
 * @author Valdeir Santana <https://www.valdeirsantana.com.br>
 */
class Box
{
  /**
   * Usuário do contrato com o Correios
   * @var string
   */
  private $contractCode;

  /**
   * Senha do contrato com o Correios
   * @var string
   */
  private $contractPassword;

  /**
   * CEP de Origem (apenas números)
   * @var string
   */
  private $postcodeFrom;

  /**
   * CEP de Destino (apenas números)
   * @var string
   */
  private $postcodeTo;

  /** @var float */
  private $weight;

  /** @var float */
  private $length;

  /** @var float */
  private $width;

  /** @var float */
  private $height;

  /** @var bool */
  private $deliverByHand;

  /** @var float */
  private $totalBox;

  /** @var bool */
  private $receiptNotice;

  /**
   * @param string $value
   */
  public function setContractCode($value)
  {
    $this->contractCode = trim($value);
  }

  /**
   * @return string
   */
  public function getContractCode()
  {
    return $this->contractCode;
  }

  /**
   * @param string $value
   */
  public function setContractPassword($value)
  {
    $this->contractPassword = trim($value);
  }

  /**
   * @return string
   */
  public function getContractPassword()
  {
    return $this->contractPassword;
  }

  /**
   * @param string $value
   */
  public function setPostcodeFrom($value)
  {
    $postcode = preg_replace("/\D/", "", $value);

    if (!preg_match("/^\d{8}$/", $postcode)) {
      throw new \UnexpectedValueException("{$value} invalid");
    }

    $this->postcodeFrom = preg_replace("/\D/", "", $value);
  }

  /**
   * @return string
   */
  public function getPostcodeFrom()
  {
    return $this->postcodeFrom;
  }

  /**
   * @param string $value
   */
  public function setPostcodeTo($value)
  {
    $postcode = preg_replace("/\D/", "", $value);

    if (!preg_match("/^\d{8}$/", $postcode)) {
      throw new \UnexpectedValueException("{$value} invalid");
    }

    $this->postcodeTo = preg_replace("/\D/", "", $value);
  }

  /**
   * @return string
   */
  public function getPostcodeTo()
  {
    return $this->postcodeTo;
  }

  /**
   * Informa o peso da caixa
   * @param float $value
   * @throws \UnexpectedValueException
   */
  public function setWeight($value)
  {
    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
      throw new \UnexpectedValueException("{$value} invalid");
    }

    $this->weight = floatval(number_format($value, 2, ".", ""));
  }

  /**
   * @return float
   */
  public function getWeight()
  {
    return $this->weight;
  }

  /**
   * Informa o comprimento da caixa
   * @param float $value
   * @throws \UnexpectedValueException
   */
  public function setLength($value)
  {
    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
      throw new \UnexpectedValueException("{$value} invalid");
    }

    $this->length = floatval(number_format($value, 2, ".", ""));
  }

  /**
   * @return float
   */
  public function getLength()
  {
    return $this->length;
  }

  /**
   * Informa o comprimento da caixa
   * @param float $value
   * @throws \UnexpectedValueException
   */
  public function setWidth($value)
  {
    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
      throw new \UnexpectedValueException("{$value} invalid");
    }

    $this->width = floatval(number_format($value, 2, ".", ""));
  }

  /**
   * @return float
   */
  public function getWidth()
  {
    return $this->width;
  }

  /**
   * Informa o comprimento da caixa
   * @param float $value
   * @throws \UnexpectedValueException
   */
  public function setHeight($value)
  {
    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
      throw new \UnexpectedValueException("{$value} invalid");
    }

    $this->height = floatval(number_format($value, 2, ".", ""));
  }

  /**
   * @return float
   */
  public function getHeight()
  {
    return $this->height;
  }

  /**
   * Informa se o serviço irá utilizar a opção "Entrega em mãos"
   * @param bool $value
   */
  public function setDeliveryByHand($value)
  {
    $this->deliveryByHand = !!$value;
  }

  /**
   * @return bool
   */
  public function getDeliveryByHand()
  {
    return $this->deliveryByHand;
  }

  /**
   * Informa o valor total de produtos da caixa
   * Este campo será utilizado para informar o valor
   * declarado ao Correios
   * 
   * @param float $value
   * @throws \UnexpectedValueException
   */
  public function setTotalBox($value)
  {
    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
      throw new \UnexpectedValueException("{$value} invalid");
    }

    $this->totalBox = floatval(number_format($value, 2, ".", ""));
  }

  /**
   * @return float
   */
  public function getTotalBox()
  {
    return $this->totalBox;
  }

  /**
   * Informa se a entrega deverá utilizar o parâmetro
   * "aviso de recebimento"
   * 
   * @param float $value
   * @throws \UnexpectedValueException
   */
  public function setReceiptNotice($value)
  {
    $this->receiptNotice = !!$value;
  }

  /**
   * @return bool
   */
  public function getReceiptNotice()
  {
    return $this->receiptNotice;
  }
}