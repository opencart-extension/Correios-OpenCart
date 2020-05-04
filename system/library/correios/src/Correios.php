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
   * Serviços do Correios
   */
  private $services = [];

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
  public function __construct($services, $address, $products)
  {
    if (!$this->validateServices($services)) {
      throw new \InvalidArgumentException('Services invalid');
    }

    if (empty($address['postcode'])) {
      throw new \InvalidArgumentException('Postcode required');
    }

    if (!$this->validateProducts($products)) {
      throw new \InvalidArgumentException('Products invalid');
    }

    $this->services = $services;
    $this->postcodeTo = $address['postcode'];
    $this->products = $this->parseProducts($products);
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
   * Função responsável por realizar a pesquisa de preço e prazo
   * no site do Correios
   *
   * @return Quote[]
   */
  public function getQuote()
  {
    $boxes = $this->buildBoxes();
    $quotes = [];

    foreach($this->services as $service) {
      foreach($boxes as $box) {
        $quotes[] = $service->getQuote($box);
      }
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
   * Valida os serviços informados
   *
   * @param Service[] $services
   * @return boolean
   */
  private function validateServices($services)
  {
    if (empty($services)) return false;

    foreach($services as $service) {
      if (!($service instanceof Service)) {
        return false;
      }
    }

    return true;
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

    foreach ($products as $product) {
      foreach ($requiredKeysProducts as $key => $value) {
        if (!array_key_exists($key, $product)) {
          throw new \InvalidArgumentException("{$key} required");
        }
  
        if (!filter_var($product[$key], $value['filter'], $value['flags'])) {
          throw new \UnexpectedValueException("{$key} = {$product[$key]} is invalid");
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

    foreach($this->services as $service) {
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

        if ($service->validate($box, false)) {
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
    }

    return $boxes;
  }
}