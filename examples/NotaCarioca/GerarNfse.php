<?php

require __DIR__.'/../../vendor/autoload.php';

use NFSePHP\NotaCarioca\GerarNfseNotaCariocaFactory;
use NFSePHP\NotaCarioca\SoapHandler;

$rps = [
    'IdentificacaoRps' => [
        'Numero' => 119,
        'Serie' => 'A',
        'Tipo' => 1,
        // 1 - RPS
        // 2 – Nota Fiscal Conjugada (Mista)
        // 3 – Cupom
    ],
    'DataEmissao' => date('Y-m-d').'T'.date('H:i:s'),
    'NaturezaOperacao' => 1,
    // 1 – Tributação no município
    // 2 - Tributação fora do município
    // 3 - Isenção
    // 4 - Imune
    // 5 – Exigibilidade suspensa por decisão judicial
    // 6 – Exigibilidade suspensa por procedimento administrativo

    'RegimeEspecialTributacao' => 3, // optional
    // 1 – Microempresa municipal
    // 2 - Estimativa
    // 3 – Sociedade de profissionais
    // 4 – Cooperativa
    // 5 – MEI – Simples Nacional
    // 6 – ME EPP – Simples Nacional

    'OptanteSimplesNacional' => 2, // 1 - Sim 2 - Não
    'IncentivadorCultural' => 2, // 1 - Sim 2 - Não
    'Status' => 1, // 1 – Normal  2 – Cancelado

    'Prestador' => [
        'Cnpj' => '111111',
        'InscricaoMunicipal' => '11111', // optional
    ],

    'Tomador' => [
        'IdentificacaoTomador' => [ // optional
            'CpfCnpj' => [
                'Cpf' => '111',
                // 'Cnpj' => '111',
            ],
        ],
        'RazaoSocial' => 'Fulano de tal', // optional
        'Endereco' => [ // optional
            'Endereco' => 'Rua 1111', // optional
            'Numero' => '1', // optional
            'Complemento' => 'ap 1', // optional
            'Bairro' => '1', // optional
            'CodigoMunicipio' => 1111111, // optional
            'Uf' => 'RJ', // optional
            'Cep' => 11111111, // optional
        ],
    ],

    'Servico' => [
        'ItemListaServico' => '1002', // First 4 digits - https://notacarioca.rio.gov.br/files/leis/Resolucao_2617_2010_anexo2.pdf
        'CodigoTributacaoMunicipio' => '100203', // 6 digits - https://notacarioca.rio.gov.br/files/leis/Resolucao_2617_2010_anexo2.pdf
        'Discriminacao' => 'Pedido #1111 - Itens: #123 , #124',
        'CodigoMunicipio' => 1111111,
        'Valores' => [
            'ValorServicos' => 228.6,
            'ValorDeducoes' => 10.0, // optional
            'ValorPis' => 10.0, // optional
            'ValorCofins' => 10.0, // optional
            'ValorInss' => 10.0, // optional
            'ValorIr' => 10.0, // optional
            'ValorCsll' => 10.0, // optional
            'IssRetido' => 2, // 1 para ISS Retido - 2 para ISS não Retido,
            'ValorIss' => 10.0, // optional
            'OutrasRetencoes' => 10.0, // optional
            'Aliquota' => 5, // optional
            'DescontoIncondicionado' => 10.0, // optional
            'DescontoCondicionado' => 10.0, // optional
        ],
    ],

    'IntermediarioServico' => [ // optional
        'RazaoSocial' => 'Fulano de tal',
        'CpfCnpj' => [
            'Cnpj' => '11111',
            // 'Cpf' => '1111',
        ],
        'InscricaoMunicipal' => '11111', // optional
    ],

    'ConstrucaoCivil' => [ // optional
        'CodigoObra' => '111',
        'Art' => '111',
    ],
];

$env = 'dev'; // dev - prod
$notaCarioca = new GerarNfseNotaCariocaFactory($rps, $env);

$soapHandler = new SoapHandler(['cert_path' => '/path/to/valid/cert.pfx', 'cert_pass' => 'certpassword']);

// Send SOAP xml
$response = $soapHandler->send($notaCarioca);

if ($soapHandler->isSuccess($response)) {
    // save db
    $nfs = $notaCarioca->formatSuccessResponse($response);
    var_dump($nfs);
} else {
    var_dump($soapHandler->getErrors($response));
}

/* Response

array (size=1)
  'nfse' =>
    array (size=14)
      'Numero' => string '43' (length=2)
      'CodigoVerificacao' => string 'VZW2-EJIB' (length=9)
      'DataEmissao' => string '2020-02-28T09:09:55' (length=19)
      'IdentificacaoRps' =>
        array (size=3)
          'Numero' => string '1' (length=1)
          'Serie' => string 'A' (length=2)
          'Tipo' => string '1' (length=1)
      'DataEmissaoRps' => string '2020-02-28' (length=10)
      'NaturezaOperacao' => string '1' (length=1)
      'RegimeEspecialTributacao' => string '3' (length=1)
      'OptanteSimplesNacional' => string '2' (length=1)
      'IncentivadorCultural' => string '2' (length=1)
      'Competencia' => string '2020-02-28T00:00:00' (length=19)
      'Servico' =>
        array (size=5)
          'Valores' =>
            array (size=3)
              'ValorServicos' => string '228.6' (length=5)
              'IssRetido' => string '2' (length=1)
              'ValorLiquidoNfse' => string '228.6' (length=5)
          'ItemListaServico' => string '1002' (length=4)
          'CodigoTributacaoMunicipio' => string '100203' (length=6)
          'Discriminacao' => string 'Pedido #1111 - Itens: #123 , #124' (length=33)
          'CodigoMunicipio' => string '3304557' (length=7)
      'PrestadorServico' =>
        array (size=4)
          'IdentificacaoPrestador' =>
            array (size=2)
              'Cnpj' => string '11111111111111' (length=14)
              'InscricaoMunicipal' => string '1111111' (length=7)
          'RazaoSocial' => string '11111111' (length=48)
          'Endereco' =>
            array (size=7)
              'Endereco' => string '11111' (length=39)
              'Numero' => string '1111' (length=3)
              'Complemento' => string '11111' (length=15)
              'Bairro' => string '11111' (length=7)
              'CodigoMunicipio' => string '3304557' (length=7)
              'Uf' => string 'RJ' (length=2)
              'Cep' => string '1111111' (length=8)
          'Contato' => string '' (length=0)
      'TomadorServico' =>
        array (size=2)
          'IdentificacaoTomador' => string '' (length=0)
          'Endereco' => string '' (length=0)
      'OrgaoGerador' =>
        array (size=2)
          'CodigoMunicipio' => string '3304557' (length=7)
          'Uf' => string 'RJ' (length=2)
*/
