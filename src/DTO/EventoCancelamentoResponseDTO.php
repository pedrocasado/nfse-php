<?php

namespace NFSePHP\DTO;

/**
 * Response object for Evento de Cancelamento API.
 *
 * Example body:
 * {
 *   "tipoAmbiente": 1,
 *   "versaoAplicativo": "string",
 *   "dataHoraProcessamento": "2026-02-27T15:05:21.4015763-03:00",
 *   "eventoXmlGZipB64": "string"
 * }
 */
class EventoCancelamentoResponseDTO
{
    public function __construct(
        public int $tipoAmbiente,
        public string $versaoAplicativo,
        public string $dataHoraProcessamento,
        public ?string $eventoXmlGZipB64 = null,
    ) {
    }

    /**
     * Decode base64+gzip event XML if present.
     */
    public function getEventoXml(): ?string
    {
        if (null === $this->eventoXmlGZipB64 || '' === $this->eventoXmlGZipB64) {
            return null;
        }

        $decoded = base64_decode($this->eventoXmlGZipB64, true);
        if (false === $decoded) {
            return null;
        }

        $xml = @gzdecode($decoded);

        return false !== $xml ? $xml : null;
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
        return new self(
            tipoAmbiente: (int) ($data['tipoAmbiente'] ?? 0),
            versaoAplicativo: (string) ($data['versaoAplicativo'] ?? ''),
            dataHoraProcessamento: (string) ($data['dataHoraProcessamento'] ?? ''),
            eventoXmlGZipB64: isset($data['eventoXmlGZipB64']) ? (string) $data['eventoXmlGZipB64'] : null,
        );
    }
}
