<?php

require __DIR__.'/../../vendor/autoload.php';

use NFSePHP\NotaCarioca\NotaCariocaOperationFactory;
use NFSePHP\NotaCarioca\SoapHandler;

$rps = [
    'IdentificacaoRps' => [
        'Numero' => 1,
        'Serie' => 'A',
        'Tipo' => 1,
    ],
    'Prestador' => [
        'Cnpj' => '12218427000172',
        'InscricaoMunicipal' => '02247211',
    ],
];

$env = 'dev'; // dev or prod
$consultarRpsOperation = (new NotaCariocaOperationFactory())->createOperation('consultar-por-rps', $env, $rps);

$soapHandler = new SoapHandler(['cert_path' => '/path/to/valid/cert.pfx', 'cert_pass' => 'certpassword']);

// Send SOAP xml
$response = $soapHandler->send($consultarRpsOperation);

if ($soapHandler->isSuccess($response)) {
    $nfs = $consultarRpsOperation->formatSuccessResponse($response);

    var_dump($nfs);
} else {
    $errors = $soapHandler->getErrors($response);

    var_dump($errors);
}

/* Response

array (size=1)
  'nfse' =>
    array (size=14)
      'Numero' => string '1' (length=2)
      'CodigoVerificacao' => string 'AMXG-UHBL' (length=9)
      'DataEmissao' => string '2020-02-12T14:31:39' (length=19)
      'IdentificacaoRps' =>
        array (size=3)
          'Numero' => string '1' (length=3)
          'Serie' => string 'A' (length=1)
          'Tipo' => string '1' (length=1)
      'DataEmissaoRps' => string '2020-02-12' (length=10)
      'NaturezaOperacao' => string '1' (length=1)
      'OptanteSimplesNacional' => string '2' (length=1)
      'IncentivadorCultural' => string '2' (length=1)
      'Competencia' => string '2020-02-12T00:00:00' (length=19)
      'Servico' =>
        array (size=5)
          'Valores' =>
            array (size=6)
              'ValorServicos' => string '228.6' (length=5)
              'IssRetido' => string '2' (length=1)
              'ValorIss' => string '11.43' (length=5)
              'BaseCalculo' => string '228.6' (length=5)
              'Aliquota' => string '0.05' (length=4)
              'ValorLiquidoNfse' => string '228.6' (length=5)
          'ItemListaServico' => string '1002' (length=4)
          'CodigoTributacaoMunicipio' => string '100203' (length=6)
          'Discriminacao' => string 'Pedido #1111 - Itens: #123 , #124' (length=65)
          'CodigoMunicipio' => string '3304557' (length=7)
      'ValorCredito' => string '1.14' (length=4)
      'PrestadorServico' =>
        array (size=4)
          'IdentificacaoPrestador' =>
            array (size=2)
              'Cnpj' => string '12218427000172' (length=14)
              'InscricaoMunicipal' => string '02247211' (length=7)
          'RazaoSocial' => string 'RAZAO SOCIAL' (length=48)
          'Endereco' =>
            array (size=7)
              'Endereco' => string 'RUA VISC DE PIRAJA' (length=39)
              'Numero' => string '1' (length=3)
              'Complemento' => string 'SAL 1' (length=15)
              'Bairro' => string 'IPANEMA' (length=7)
              'CodigoMunicipio' => string '3304557' (length=7)
              'Uf' => string 'RJ' (length=2)
              'Cep' => string '11111111' (length=8)
          'Contato' => string '' (length=0)
      'TomadorServico' =>
        array (size=3)
          'IdentificacaoTomador' =>
            array (size=1)
              'CpfCnpj' =>
                array (size=1)
                  'Cpf' => string '11111111111' (length=11)
          'RazaoSocial' => string 'Pedro L' (length=19)
          'Endereco' =>
            array (size=7)
              'Endereco' => string 'Rua x, ap 1' (length=35)
              'Numero' => string '1' (length=3)
              'Complemento' => string 'ap 1' (length=7)
              'Bairro' => string 'Caxias' (length=7)
              'CodigoMunicipio' => string '3304557' (length=7)
              'Uf' => string 'RJ' (length=2)
              'Cep' => string '11111111' (length=8)
      'OrgaoGerador' =>
        array (size=2)
          'CodigoMunicipio' => string '3304557' (length=7)
          'Uf' => string 'RJ' (length=2)
*/
