# NFSePHP

Library to communicate with the **Sistema Nacional da NFS-e** (Sefin Nacional): create and cancel NFS-e via the national REST API.

**Version 3** is a complete refactor from v2. It targets the Sefin Nacional environment only (no Nota Carioca or other SOAP providers). DPS and evento XML follow the official XSDs; certificate-based signing and optional PSR-3 logging are built in.

---

## Install

```bash
composer require pedrocasado/nfse-php
```

---

## Certificate

Use a PFX (PKCS#12) certificate issued for your company. The library needs the **PFX binary content** and password (e.g. read from file or env).

```php
use NFSePHP\Certificate;

$pfxContent = file_get_contents('/path/to/certificate.pfx');
$certificate = new Certificate(pfxContent: $pfxContent, pfxPassword: 'your-pfx-password');
```

---

## Create NFS-e (DPS)

Build an `InfDpsDTO` (prestador, tomador, serviço, valores, competência, etc.), then call `createNFSe`. The service builds the DPS XML, signs it, optionally validates against the XSD, then sends it gzip+base64 to the Sefin endpoint. Environment (homolog/prod) is taken from `InfDpsDTO->tpAmb` (1 = production, 2 = homologation) unless you override the base URL.

```php
use NFSePHP\Certificate;
use NFSePHP\NFSeService;
use NFSePHP\DTO\InfDpsDTO;
use NFSePHP\DTO\PrestadorDTO;
use NFSePHP\DTO\TomadorDTO;
use NFSePHP\DTO\ServicoDTO;
use NFSePHP\DTO\ValoresServicoDTO;

$certificate = new Certificate(pfxContent: $pfxContent, pfxPassword: $pfxPassword);
$service = new NFSeService(certificate: $certificate);

$dto = new InfDpsDTO(
    tpAmb: '2',
    versao: '1.01',
    prest: new PrestadorDTO(cnpj: '...', inscricaoMunicipal: '...', razaoSocial: '...', /* ... */),
    tomador: new TomadorDTO(/* ... */),
    servico: new ServicoDTO(/* ... */),
    valores: new ValoresServicoDTO(/* ... */),
    dCompet: '...',
    cLocEmi: '...',
    serie: '1',
    nDPS: '1',
    // ...
);

$response = $service->createNFSe(infDpsDTO: $dto);

if ($response->isHttpSuccess() && $response->hasParsedResponse()) {
    $body = $response->response;
    if ($body->isSuccess()) {
        $chave = $body->chaveAcesso;
        $xml = $body->getNfseXml(); // decoded gzip+base64
    } else {
        foreach ($body->getErros() as $erro) {
            // $erro->codigo, $erro->descricao
        }
    }
} else {
    $statusCode = $response->statusCode;
    $rawBody = $response->rawBody;
}
```

---

## Cancel NFS-e (Evento)

Use `EventoCancelamentoDTO` with the NFS-e key, author (CNPJ or CPF), reason code and description, then call `cancelNFSe`. The service builds the evento XML (pedRegEvento layout per schema 1.00), signs `infPedReg`, sends it gzip+base64 to `/{chave}/eventos`.

```php
use NFSePHP\DTO\EventoCancelamentoDTO;
use NFSePHP\DTO\EventoResponse;

$evento = new EventoCancelamentoDTO(
    tpAmb: '2',
    dhEvento: '2026-02-27T19:00:00-03:00',
    chNFSe: '33045572238744743000149000000000001026029316934590',
    cMotivo: '2',
    xMotivo: 'Cancelamento de teste com motivo adequado',
    cnpjAutor: '38744743000149',
);

$response = $service->cancelNFSe(evento: $evento);

if ($response->isHttpSuccess() && $response->hasParsedResponse()) {
    $body = $response->response;
    $xml = $body->getEventoXml();
} else {
    $statusCode = $response->statusCode;
    $rawBody = $response->rawBody;
}
```

---

## Configuration

- **Endpoint**  
  By default the base URL is chosen from `tpAmb` (1 → production, 2 → homologation). You can override it when constructing `DpsService`:

    ```php
    $service = new NFSeService(certificate: $certificate, endpointUrl: 'https://custom.sefin.gov.br/nfse');
    ```

- **Logger (PSR-3)**  
  Pass a `Psr\Log\LoggerInterface` to send debug output (XML dumps, validation errors, request URLs) to your app logger:

    ```php
    $service = new NFSeService(certificate: $certificate, logger: $logger);
    ```

- **Debug flag**  
  If you don’t inject a logger but set `$debug = true`, the same information is written to stderr.

- **DTO validation**  
  `createNFSe(infDpsDTO: $dto, validateDTOs: true)` runs Symfony Validator on `InfDpsDTO` (and nested DTOs). Use `validateDTOs: false` to skip.

- **XSD validation**  
  When enabled (e.g. in debug), the signed DPS and evento XML are validated against the bundled XSDs. On failure an `InvalidXSDException` is thrown (or only logged, depending on flow).

---

## Response parsing

- **DPS**  
  `DpsResponse` holds `statusCode`, `rawBody`, and optional `response` (`SefinNacionalResponse`). Use `response->isSuccess()` / `response->isError()`, `response->getErros()`, `response->getNfseXml()`.

- **Evento**  
  `EventoResponse` holds `statusCode`, `rawBody`, and optional `response` (`EventoCancelamentoResponseDTO`). Use `response->getEventoXml()` to decode the returned gzip+base64 evento XML.

The Sefin API can return errors in `erro` or `erros`, with `codigo`/`Codigo` and `descricao`/`Descricao`; the library normalizes these when parsing.

---

## Tests

```bash
composer test
```

Tests cover DTOs (including validation), response parsing (Sefin and Evento), and response helpers. See `tests/README.md` for details and ideas for XML-building tests.

---

## Changelog / v2 → v3

- **Target**: Sefin Nacional (REST, JSON body, gzip+base64 XML). No Nota Carioca or SOAP.
- **DPS**: Build from `InfDpsDTO`, sign `infDPS`, POST to Sefin; response parsed into `SefinNacionalResponse`.
- **Cancelamento**: Evento de cancelamento (e101101) with `pedRegEvento`/`infPedReg` layout (schema 1.00), sign `infPedReg`, POST to `/{chave}/eventos`.
- **Certificate**: PFX content + password; PEM paths used for HTTP client mTLS.
- **Logging**: Optional PSR-3 logger or `$debug` stderr fallback.
- **Validation**: Symfony Validator on DTOs; optional XSD validation for signed XML.

---

## License

LGPL-3.0-or-later / GPL-3.0-or-later / MIT.
