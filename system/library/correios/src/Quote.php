<?php

namespace ValdeirPsr\Correios;

/**
 * Classe responsável por armazenar os valores
 * retornados pela API do Correios
 * 
 * @author Valdeir S. <https://www.valdeirsantana.com.br>
 */
class Quote
{
  /** @var string */
  private $serviceCode;
  
  /** @var int */
  private $days;
  
  /** @var float */
  private $priceTotal;
  
  /** @var float */
  private $priceBase;
  
  /**
   * Entrega em mãos
   * @var float
   */
  private $priceDeliveryByHand;
  
  /**
   * Aviso de recebimento
   * @var float
   */
  private $priceReceiptNotice;
  
  /**
   * Seguro do Correios
   * @var float
   */
  private $priceInsuranceBox;
  
  /** @var boolean */
  private $homeDelivery;
  
  /** @var boolean */
  private $deliverySaturday;
  
  /** @var string */
  private $error;

  /**
   * @param string $value
   */
  public function setServiceCode($value)
  {
    $this->serviceCode = $value;
  }

  /**
   * @return string
   */
  public function getServiceCode()
  {
    return $this->serviceCode;
  }

  /**
   * @param int $value
   */
  public function setDays($value)
  {
    if (!is_numeric($value)) {
      throw new \UnexpectedValueException("Day {$value} invalid");
    }

    $this->days = intval($value);
  }

  /**
   * @return int
   */
  public function getDays()
  {
    return $this->days;
  }

  /**
   * @param float $value
   */
  public function setPriceTotal($value)
  {
    if (!is_numeric($value)) {
      throw new \UnexpectedValueException("Price total {$value} invalid");
    }

    $this->priceTotal = floatval(number_format($value, 2, '.', ''));
  }

  /**
   * @return float
   */
  public function getPriceTotal()
  {
    return $this->priceTotal;
  }

  /**
   * @param int $value
   */
  public function setPriceBase($value)
  {
    if (!is_numeric($value)) {
      throw new \UnexpectedValueException("Price base {$value} invalid");
    }

    $this->priceBase = floatval(number_format($value, 2, '.', ''));
  }

  public function getPriceBase()
  {
    return $this->priceBase;
  }

  public function setPriceDeliveryByHand($value)
  {
    if (!is_numeric($value)) {
      throw new \UnexpectedValueException("Price delivery by hand {$value} invalid");
    }

    $this->priceDeliveryByHand = floatval(number_format($value, 2, '.', ''));
  }

  public function getPriceDeliveryByHand()
  {
    return $this->priceDeliveryByHand;
  }

  public function setPriceReceiptNotice($value)
  {
    if (!is_numeric($value)) {
      throw new \UnexpectedValueException("Price receipt notice {$value} invalid");
    }

    $this->priceReceiptNotice = floatval(number_format($value, 2, '.', ''));
  }

  public function getPriceReceiptNotice()
  {
    return $this->priceReceiptNotice;
  }

  public function setPriceInsuranceBox($value)
  {
    $this->priceInsuranceBox = floatval($value);
  }

  public function getPriceInsuranceBox()
  {
    return $this->priceInsuranceBox;
  }

  public function setHomeDelivery($value)
  {
    $this->homeDelivery = !!$value;
  }

  public function getHomeDelivery()
  {
    return $this->homeDelivery;
  }

  public function setDeliverySaturday($value)
  {
    $this->deliverySaturday = !!$value;
  }

  public function getDeliverySaturday()
  {
    return $this->deliverySaturday;
  }

  public function setError($value)
  {
    $this->error = $value;
  }

  public function getError()
  {
    return $this->error;
  }
}