<?php

require __DIR__.'/../../vendor/autoload.php';

use NFSePHP\NotaCarioca\CancelarNfseFactory;
use NFSePHP\NotaCarioca\SoapHandler;

$nfse = [
    'IdentificacaoNfse' => [
        'Numero' => '9',
        'Cnpj' => '1111111111111',
        'InscricaoMunicipal' => '11111111',
        'CodigoMunicipio' => '111111',
    ],
    'CodigoCancelamento' => '1',
    // 1 Erro na emissão
    // 2 Serviço não prestado
    // 3 Duplicidade da nota
    // 9 Outros
];

$env = 'dev'; // dev or prod
$notaCariocaCancel = new CancelarNfseFactory($nfse, $env);

$soapHandler = new SoapHandler(['cert_path' => '/path/to/valid/cert.pfx', 'cert_pass' => 'certpassword']);

// Send SOAP xml
$response = $soapHandler->send($notaCariocaCancel);

if ($soapHandler->isSuccess($response)) {
    $nfs = $notaCariocaCancel->formatSuccessResponse($response);

    var_dump($nfs);
} else {
    $errors = $soapHandler->getErrors($response);

    var_dump($errors);
}

/* Response
array (size=2)
  'DataHoraCancelamento' => string '2020-02-20T12:13:22' (length=19)
  'Pedido' => int 54804

*/
