<?php

require __DIR__.'/../../vendor/autoload.php';

use NFSePHP\NotaCarioca\NotaCariocaOperationFactory;
use NFSePHP\NotaCarioca\SoapHandler;

$rps = [
    'Prestador' => [
        'Cnpj' => '11111111111111',
        'InscricaoMunicipal' => '11111111',
    ],
    'PeriodoEmissao' => [
        'DataInicial' => '2020-01-30',
        'DataFinal' => '2020-01-30',
    ],
    'Tomador' => [
        'CpfCnpj' => [
            'Cpf' => '11111111111',
        ],
    ],
];

$env = 'dev'; // dev or prod
$consultarNfseOperation = (new NotaCariocaOperationFactory())->createOperation('consultar-nfse', $env, $rps);

$soapHandler = new SoapHandler(['cert_path' => '/path/to/valid/cert.pfx', 'cert_pass' => 'certpassword']);

// Send SOAP xml
$response = $soapHandler->send($consultarNfseOperation);

if ($soapHandler->isSuccess($response)) {
    $nfs = $consultarNfseOperation->formatSuccessResponse($response);

    var_dump($nfs);
} else {
    $errors = $soapHandler->getErrors($response);

    var_dump($errors);
}

/* Response

array (size=7)
  0 =>
    array (size=14)
      'Numero' => string '6' (length=1)
      'CodigoVerificacao' => string 'RE4C-ZXKX' (length=9)
      'DataEmissao' => string '2020-01-30T09:15:03' (length=19)
      'IdentificacaoRps' =>
        array (size=3)
          'Numero' => string '7' (length=1)
          'Serie' => string '1' (length=1)
          'Tipo' => string '1' (length=1)
      'DataEmissaoRps' => string '2020-01-30' (length=10)
      'NaturezaOperacao' => string '1' (length=1)
      'OptanteSimplesNacional' => string '2' (length=1)
      'IncentivadorCultural' => string '2' (length=1)
      'Competencia' => string '2020-01-30T00:00:00' (length=19)
      'Servico' =>
        array (size=5)
          'Valores' =>
            array (size=6)
              'ValorServicos' => string '100' (length=3)
              'IssRetido' => string '2' (length=1)
              'ValorIss' => string '5' (length=1)
              'BaseCalculo' => string '100' (length=3)
              'Aliquota' => string '0.05' (length=4)
              'ValorLiquidoNfse' => string '100' (length=3)
          'ItemListaServico' => string '1002' (length=4)
          'CodigoTributacaoMunicipio' => string '100203' (length=6)
          'Discriminacao' => string 'Teste de RPS' (length=12)
          'CodigoMunicipio' => string '111111' (length=7)
      'ValorCredito' => string '0.5' (length=3)
      'PrestadorServico' =>
        array (size=4)
          'IdentificacaoPrestador' =>
            array (size=2)
              'Cnpj' => string '111111' (length=14)
              'InscricaoMunicipal' => string '4409477' (length=7)
          'RazaoSocial' => string '111111' (length=48)
          'Endereco' =>
            array (size=7)
              'Endereco' => string '1111111' (length=39)
              'Numero' => string '111' (length=3)
              'Complemento' => string '11111' (length=15)
              'Bairro' => string '11111' (length=7)
              'CodigoMunicipio' => string '111111' (length=7)
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
          'RazaoSocial' => string 'Fulano de Tal' (length=13)
          'Endereco' =>
            array (size=7)
              'Endereco' => string '1111111' (length=29)
              'Numero' => string '111' (length=3)
              'Complemento' => string 'Sobre Loja' (length=10)
              'Bairro' => string 'Centro' (length=6)
              'CodigoMunicipio' => string '3304557' (length=7)
              'Uf' => string 'RJ' (length=2)
              'Cep' => string '11111111' (length=8)
      'OrgaoGerador' =>
        array (size=2)
          'CodigoMunicipio' => string '11111' (length=7)
          'Uf' => string 'RJ' (length=2)
  1 =>
    array (size=14)
      'Numero' => string '7' (length=1)
      'CodigoVerificacao' => string 'QKSW-PINP' (length=9)
      'DataEmissao' => string '2020-01-30T09:20:56' (length=19)
      ......
*/
