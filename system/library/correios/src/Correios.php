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
   * Tipo de desconto:
   *  F para Fixo 
   *  P para Percentagem
   * @var string
   */
  private $discount_type = 'f';

  /**
   * Prazo adicional
   * @var int
   */
  private $daysAdditional = 0;

  /**
   * Serviços do Correios
   */
  private $service;

  /**
   * Initializa a classe
   * 
   * @param Service $service
   * @param Address $address
   * @param Array $products
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
  public function __construct($service, $address, $products)
  {
    if (!($service instanceof Service)) {
      throw new \InvalidArgumentException('Service invalid');
    }

    if (empty($address['postcode'])) {
      throw new \InvalidArgumentException('Postcode required');
    }

    if (!$this->validateProducts($products)) {
      throw new \InvalidArgumentException('Products invalid');
    }

    $this->service = $service;
    $this->postcodeTo = $address['postcode'];
    $this->products = $this->parseProducts($products);
  }

  /**
   * Informa o valor de desconto do preço total do envio
   * @param float $discount
   * @throws \InvalidArgumentException 
   */
  public function setDiscount($discount, $type = 'f')
  {
    $type = strtolower($type);

    if (!is_numeric($discount)) {
      throw new \InvalidArgumentException("Discount {$discount} invalid");
    }

    if ($type !== 'f' && $type !== 'p') {
      throw new \InvalidArgumentException("Discount Type {$type} invalid");
    }

    $this->discount = floatval($discount);
    $this->discount_type = $type;
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
   * @throws \InvalidArgumentException
   */
  public function setDaysAdditional($days)
  {
    if (!filter_var($days, FILTER_VALIDATE_INT)) {
      throw new \InvalidArgumentException("Days Additional {$days} invalid");
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
   * Função responsável por realizar a pesquisa de preço e prazo
   * no site do Correios
   *
   * @return Quote[]
   */
  public function getQuote()
  {
    $boxes = $this->buildBoxes();
    $quotes = [];

    foreach($boxes as $box) {
      $quotes[] = $this->service->getQuote($box);
    }

    if ($this->daysAdditional > 0) {
      array_map([$this, 'applyDaysAdditional'], $quotes);
    }

    if ($this->discount > 0) {
      array_map([$this, 'applyDiscount'], $quotes);
    }

    return $quotes;
  }

  /**
   * Transforma um produtos com quantidade superior a 1 em multiplos
   * produtos com quantidade igual a 1
   *
   * @param array $products
   * @return array
   */
  private function parseProducts($products)
  {
    foreach($products as $key => $product) {
      if ($product['quantity'] > 1) {
        for($count = 0; $count < $product['quantity']; $count++) {
          $productCopy = $product;
          $productCopy['quantity'] = 1;
    
          $products[] = $productCopy;
          unset($products[$key]);
        }
      }
    }

    $products = array_values($products);

    $postcodes = [];
    
    foreach($products as $key => $value) {
      $postcodes[$key] = $value['shipping'];
    }

    array_multisort($products, $postcodes);

    return $products;
  }

  /**
   * Verifica se os campos estão de acordo com os tipos (String, Float e Int)
   * 
   * @param array $products
   * @throws InvalidArgumentException Quando o array não encontrar as keys obrigatórias
   *                                  Quando o valor informado for inválido
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
      'length'          => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'width'           => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
      'height'          => ['filter' => FILTER_VALIDATE_FLOAT, 'flags' => ''],
    ];

    foreach ($products as $product) {
      foreach ($requiredKeysProducts as $key => $value) {
        if (!array_key_exists($key, $product)) {
          throw new \InvalidArgumentException("{$key} required");
        }
  
        if (!filter_var($product[$key], $value['filter'], $value['flags'])) {
          throw new \InvalidArgumentException("{$key} = {$product[$key]} is invalid");
        }
      }
    }

    return true;
  }

  /**
   * Função responsável por criar as caixas de acordo com o tamanho
   * dos produtos.
   *
   * @return Box[]
   */
  private function buildBoxes()
  {
    $boxes = [];
    $count = $boxId = 0;
    $total = count($this->products) - 1;

    while ($count <= $total) {
      if (!empty($boxes[$boxId]) && $boxes[$boxId]->getPostcodeFrom() != $this->products[$count]['shipping']) {
        $boxId++;
      }

      if (!isset($boxes[$boxId])) {
        $box = new Box();
        $box->setPostcodeFrom($this->products[$count]['shipping']);
        $box->setPostcodeTo($this->postcodeTo);

        $boxes[$boxId] = $box;
      }

      $box = $boxes[$boxId];

      /** Captura a dimensão do produto */
      $pLength = $this->products[$count]['length'];
      $pWidth = $this->products[$count]['width'];
      $pHeight = $this->products[$count]['height'];
      $pWeight = $this->products[$count]['weight'];
      $pPrice = $this->products[$count]['price'];
    
      /** Captura a dimensão da caixa */
      $bLength = (int)$box->getLength();
      $bWidth = (int)$box->getWidth();
      $bHeight = (int)$box->getHeight();
      $bWeight = (int)$box->getWeight();
      $bPrice = (int)$box->getTotalBox();
    
      /** Soma as dimensões do produto e da caixa */
      $box->setLength($bLength + $pLength);
      $box->setWidth($bWidth + $pWidth);
      $box->setHeight($bHeight + $pHeight);
      $box->setWeight($bWeight + $pWeight);
      $box->setTotalBox($bPrice + $pPrice);

      if ($this->service->validate($box, false)) {
        $count++;
      } elseif ($boxId > ($total + 1)) {
        /** Evita loop infinito */
        break;
      } else {
        /** Redefine as configurações da caixa */
        $box->setLength($bLength);
        $box->setWidth($bWidth);
        $box->setHeight($bHeight);
        $box->setTotalBox($bPrice);
        $boxId++;
      }
    }

    return $boxes;
  }

  /**
   * Aplica o prazo adicional 
   *
   * @param Quote $quote
   * @return Quote
   * @throws UnexpectedValueException Quando $quote houver erro
   */
  private function applyDaysAdditional($quote)
  {
    if (!!$quote->getError()) throw new \UnexpectedValueException($quote->getError());

    $days = $quote->getDays();
    $quote->setDays($days + $this->daysAdditional);

    return $quote;
  }

  /**
   * Aplica os descontos
   *
   * @param Quote $quote
   * @return Quote
   * @throws UnexpectedValueException Quando $quote houver erro
   */
  private function applyDiscount($quote)
  {
    if (!!$quote->getError()) throw new \UnexpectedValueException($quote->getError());

    $priceTotal = $quote->getPriceTotal();
    $discount = ($this->discount_type === 'p') ? ($this->discount / 100) * $priceTotal : $this->discount;
    
    $quote->setPriceTotal($priceTotal - $discount);
    
    return $quote;
  }
}