<?php

namespace ValdeirPsr\Correios;

class Correios
{
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

  /**
   * Usuário do contrato (login)
   * @var string
   */
  private $contractCode;

  /**
   * Senha do contrato (login)
   * @var string
   */
  private $contractPassword;

  /** @var string[] */
  private $error = [];

  /** @var \ArrayObject */
  private $products = [];

  /** 
   * Valor de desconto do preço total
   * @var float
   */
  private $discount;

  /**
   * Initializa a classe
   * 
   * array['addresss']
   *    array['postcode']
   * 
   * array['products']
   *    array['shipping']
   *    array['quantity']
   *    array['price']
   *    array['total']
   *    array['weight']
   *    array['weight_class_id']
   *    array['length']
   *    array['width']
   *    array['height']
   *    array['length_class_id']
   *    array['multivendor_postcode']
   * 
   * @param \ArrayObject $address
   * @param \ArrayObject $products
   */
  public function __construct($address, $products)
  {
    if (empty($address['postcode'])) throw new \InvalidArgumentException('Postcode required');

    $this->validateProducts($products);
  }

  /**
   * Informa o valor de desconto do preço total do envio
   * @param float $discount
   * @throws \UnexpectedValueException 
   */
  public function setDiscount($discount)
  {
    if (!filter_var($discount, FILTER_VALIDATE_FLOAT)) {
      throw new \UnexpectedValueException("Discount {$discount} invalid");
    }

    $this->discount = floatval($discount);
  }

  /**
   * Retorna o valor formatado do desconto
   * @return float
   */
  public function getDiscount()
  {
    return number_format($this->discount, 2, '.', '');
  }

  /**
   * Informa um prazo adicional
   * @param int $days
   * @throws \UnexpectedValueException
   */
  public function setDaysAdditional($days)
  {
    if (!filter_var($days, FILTER_VALIDATE_INT)) {
      throw new \UnexpectedValueException("Days Additional {$days} invalid");
    }

    $this->daysAdditional = intval($days);
  }

  /**
   * Returna o prazo adicional
   * @return int
   */
  public function getDaysAdditional()
  {
    return $this->daysAdditional;
  }

  /**
   * Verifica se os campos estão de acordo com os tipos (String, Float e Int)
   * 
   * @param array $products
   * @throws InvalidArgumentException Quando o array não encontrar as keys obrigatórias
   * @throws UnexpectedValueException Quando o valor informado for inválido
   */
  private function validateProducts($products)
  {
    $requiredKeysProducts = [
      'shipping'        => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'flags'  => [
          'options' => [
            'regexp' => '/^\d{8}$/'
          ]
        ]
      ],
      'quantity'        => [
        'filter' => FILTER_VALIDATE_INT,
        'flags' => [
          'options' => [
            'min_range' => 0
          ]
        ]
      ],
      'price'           => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'total'           => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'weight'          => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'weight_class_id' => ['filter' => FILTER_VALIDATE_INT, 'flags' => ''],
      'length'          => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'width'           => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'height'          => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'length_class_id' => ['filter' => FILTER_VALIDATE_INT, 'flags' => '']
    ];

    foreach ($requiredKeysProducts as $key => $value) {
      if (!array_key_exists($key, $products)) {
        throw new \InvalidArgumentException("{$key} required");
      }

      if (!filter_var($products[$key], $value['filter'], $value['flags'])) {
        throw new \UnexpectedValueException("{$key} = {$products[$key]} is invalid");
      }
    }
  }
}