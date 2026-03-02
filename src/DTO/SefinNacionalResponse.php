<?php

namespace NFSePHP\DTO;

/**
 * Response object for Sefin Nacional NFS-e API (success or error).
 *
 * Error response: tipoAmbiente, versaoAplicativo, dataHoraProcessamento, idDPS, erros[]
 * Success response: tipoAmbiente, versaoAplicativo, dataHoraProcessamento, idDps, chaveAcesso, nfseXmlGZipB64, alertas
 */
class SefinNacionalResponse
{
    /**
     * @param int                    $tipoAmbiente          1 = Produção, 2 = Homologação
     * @param string                 $versaoAplicativo      e.g. SefinNacional_1.6.0
     * @param string                 $dataHoraProcessamento ISO 8601
     * @param string|null            $idDps                 Success: NFS-e id (idDps in JSON)
     * @param string|null            $idDPS                 Error: DPS id (idDPS in JSON)
     * @param string|null            $chaveAcesso           Success only
     * @param string|null            $nfseXmlGZipB64        Success only: base64 gzipped NFSe XML
     * @param array|null             $alertas               Success: optional alerts
     * @param list<DpsResponseError> $erros                 Error only: list of errors
     */
    public function __construct(
        public int $tipoAmbiente,
        public string $versaoAplicativo,
        public string $dataHoraProcessamento,
        public ?string $idDps = null,
        public ?string $idDPS = null,
        public ?string $chaveAcesso = null,
        public ?string $nfseXmlGZipB64 = null,
        public ?array $alertas = null,
        public array $erros = [],
    ) {
    }

    public function isSuccess(): bool
    {
        return empty($this->erros) && null !== $this->nfseXmlGZipB64;
    }

    public function isError(): bool
    {
        return !$this->isSuccess();
    }

    /**
     * @return list<DpsResponseError>
     */
    public function getErros(): array
    {
        return $this->erros;
    }

    /**
     * DPS id (present in error response).
     */
    public function getDpsId(): ?string
    {
        return $this->idDPS;
    }

    /**
     * NFS-e id (present in success response).
     */
    public function getNfseId(): ?string
    {
        return $this->idDps;
    }

    /**
     * Decompress and decode NFSe XML from base64 gzip. Returns null if not success or empty.
     */
    public function getNfseXml(): ?string
    {
        if (null === $this->nfseXmlGZipB64 || '' === $this->nfseXmlGZipB64) {
            return null;
        }

        $decoded = base64_decode($this->nfseXmlGZipB64, true);
        if (false === $decoded) {
            return null;
        }
        $xml = @gzdecode($decoded);

        return false !== $xml ? $xml : null;
    }

    /**
     * Parse decoded NFSe XML into NFSeResponseDTO (nNFSe, cStat, valores, emit, etc.).
     * Returns null when there is no NFSe XML or parsing fails.
     */
    public function getNfseParsed(): ?NFSeResponseDTO
    {
        $xml = $this->getNfseXml();
        if (null === $xml || '' === $xml) {
            return null;
        }

        try {
            return NFSeResponseDTO::fromXml($xml);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Create from JSON string (API response body).
     *
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }

    /**
     * Create from decoded array.
     */
    public static function fromArray(array $data): self
    {
        $erros = [];
        $list = $data['erros'] ?? $data['erro'] ?? [];
        if (!empty($list)) {
            foreach ($list as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $erros[] = new DpsResponseError(
                    codigo: (string) ($item['Codigo'] ?? $item['codigo'] ?? ''),
                    descricao: (string) ($item['Descricao'] ?? $item['descricao'] ?? ''),
                );
            }
        }

        return new self(
            tipoAmbiente: (int) ($data['tipoAmbiente'] ?? 0),
            versaoAplicativo: (string) ($data['versaoAplicativo'] ?? ''),
            dataHoraProcessamento: (string) ($data['dataHoraProcessamento'] ?? ''),
            idDps: isset($data['idDps']) ? (string) $data['idDps'] : null,
            chaveAcesso: isset($data['chaveAcesso']) ? (string) $data['chaveAcesso'] : null,
            nfseXmlGZipB64: isset($data['nfseXmlGZipB64']) ? (string) $data['nfseXmlGZipB64'] : null,
            alertas: $data['alertas'] ?? null,
            erros: $erros,
        );
    }
}
