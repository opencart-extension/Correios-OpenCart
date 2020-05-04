<?php

class ControllerExtensionShippingCorreios extends Controller
{
  private $error = [];

  /**
   * Cria a página de configuração e salva os dados quando
   * receber uma requisição do tipo POST
   *
   * @return void
   */
  public function index()
  {
    $data = $this->load->language('extension/shipping/correios');

    $this->document->setTitle($this->language->get('heading_title'));

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/shipping/correios', $data));
  }

  /**
   * Cria as tabelas necessárias para o funcionamento
   * da extensão
   *
   * @return void
   */
  public function install()
  {

  }

  /**
   * Desinstala as tabelas e modificações do DB
   * utilizadas pela extensão
   *
   * @return void
   */
  public function uninstall()
  {

  }

  /**
   * Valida os dados da requisição
   *
   * @return boolean
   */
  private function validate()
  {

  }
}