# NFSePHP - Nota Carioca

Lib to communicate with SOAP web services and generate NFS-e (Nota Fiscal de Serviços Eletrônica). Support to Nota Carioca only as of today.

### Operations Supported

-   Nota Carioca
    -   GerarNfse
    -   ConsultarNfsePorRps

# Install

```bash
composer require pedrocasado/nfse-php
```

# Usage

Check examples/ folder

You must have a valid certificate to use Nota Carioca staging environment.

```php
use NFSePHP\NotaCarioca\GerarNfseNotaCariocaFactory;
use NFSePHP\NotaCarioca\SoapHandler;

$rps = [
    'IdentificacaoRps' => [
        'Numero' => 119,
        'Serie' => 'A',
        'Tipo' => 1,
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
        'InscricaoMunicipal' => '11111',
    ],

    'Tomador' => [
        'IdentificacaoTomador' => [
            'CpfCnpj' => [
                'Cpf' => '111',
                // 'Cnpj' => '111',
            ],
        ],
        'RazaoSocial' => 'Fulano de tal',
        'Endereco' => [
            'Endereco' => 'Rua 1111',
            'Numero' => '1',
            'Complemento' => 'ap 1',
            'Bairro' => '1',
            'CodigoMunicipio' => 1111111,
            'Uf' => 'RJ',
            'Cep' => 11111111,
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
    $nfs = $notaCarioca->formatSuccessResponse($response);
    var_dump($nfs);
} else {
    var_dump($soapHandler->getErrors($response));
}

/* Response

array (size=1)
  'nfse' =>
    array (size=5)
      'Numero' => string '1' (length=2)
      'CodigoVerificacao' => string 'AMXA-UHBL' (length=9)
      'DataEmissao' => string '2020-02-12T14:31:39' (length=19)
      'IdentificacaoRps' =>
        array (size=3)
          'Numero' => string '1' (length=3)
          'Serie' => string 'A' (length=1)
          'Tipo' => string '1' (length=1)
      'DataEmissaoRps' => string '2020-02-12' (length=10)
*/

```

# TODO's

-   Add missing operations (CancelarNfse, ConsultaNfse, ConsultarLoteRps, ConsultarSituacaoLoteRps, EnviarLoteRps)
-   Add tests

Inspired by https://github.com/nfephp-org
