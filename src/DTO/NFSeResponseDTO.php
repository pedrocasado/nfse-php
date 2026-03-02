<?php

namespace NFSePHP\DTO;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * Parsed NFSe XML (success response from Sefin).
 * Populated from the decoded gzip+base64 NFSe XML returned in nfseXmlGZipB64.
 */
final class NFSeResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $nNFSe,
        public readonly string $xLocEmi,
        public readonly string $xLocPrestacao,
        public readonly string $cLocIncid,
        public readonly string $xLocIncid,
        public readonly string $verAplic,
        public readonly string $ambGer,
        public readonly string $cStat,
        public readonly string $dhProc,
        public readonly string $nDFSe,
        public readonly ?string $xTribNac = null,
        public readonly ?string $xTribMun = null,
        public readonly ?string $tpEmis = null,
        public readonly ?string $procEmi = null,
        public readonly ?array $emit = null,
        public readonly ?array $valores = null,
    ) {
    }

    /**
     * Parse NFSe XML (root NFSe with infNFSe) into DTO using Symfony XmlEncoder.
     *
     * @throws \InvalidArgumentException when XML is invalid or missing required elements
     */
    public static function fromXml(string $xml, ?XmlEncoder $encoder = null): self
    {
        $encoder ??= new XmlEncoder();
        try {
            $decoded = $encoder->decode($xml, XmlEncoder::FORMAT);
        } catch (NotEncodableValueException) {
            throw new \InvalidArgumentException('Invalid NFSe XML');
        }
        if (!\is_array($decoded) || !isset($decoded['infNFSe']) || !\is_array($decoded['infNFSe'])) {
            throw new \InvalidArgumentException('Missing infNFSe in NFSe XML');
        }

        $inf = $decoded['infNFSe'];

        $get = static fn (string $key): string => self::scalar($inf[$key] ?? null);

        $getOpt = static fn (string $key): ?string => self::optionalScalar($inf[$key] ?? null);

        $emit = self::normalizeEmit($inf['emit'] ?? null);
        $valores = self::normalizeValores($inf['valores'] ?? null);

        return new self(
            id: (string) ($inf['@Id'] ?? ''),
            nNFSe: $get('nNFSe'),
            xLocEmi: $get('xLocEmi'),
            xLocPrestacao: $get('xLocPrestacao'),
            cLocIncid: $get('cLocIncid'),
            xLocIncid: $get('xLocIncid'),
            verAplic: $get('verAplic'),
            ambGer: $get('ambGer'),
            cStat: $get('cStat'),
            dhProc: $get('dhProc'),
            nDFSe: $get('nDFSe'),
            xTribNac: $getOpt('xTribNac'),
            xTribMun: $getOpt('xTribMun'),
            tpEmis: $getOpt('tpEmis'),
            procEmi: $getOpt('procEmi'),
            emit: $emit,
            valores: $valores,
        );
    }

    private static function scalar(mixed $v): string
    {
        if (\is_scalar($v)) {
            return (string) $v;
        }
        if (\is_array($v) && isset($v['#'])) {
            return (string) $v['#'];
        }

        return '';
    }

    private static function optionalScalar(mixed $v): ?string
    {
        $s = self::scalar($v);

        return '' !== $s ? $s : null;
    }

    private static function normalizeEmit(mixed $emit): ?array
    {
        if (!\is_array($emit)) {
            return null;
        }
        $out = [
            'CNPJ' => self::scalar($emit['CNPJ'] ?? null),
            'xNome' => self::scalar($emit['xNome'] ?? null),
            'fone' => self::scalar($emit['fone'] ?? null),
            'email' => self::scalar($emit['email'] ?? null),
        ];
        $ender = $emit['enderNac'] ?? null;
        if (\is_array($ender)) {
            $out['enderNac'] = [
                'xLgr' => self::scalar($ender['xLgr'] ?? null),
                'nro' => self::scalar($ender['nro'] ?? null),
                'xBairro' => self::scalar($ender['xBairro'] ?? null),
                'cMun' => self::scalar($ender['cMun'] ?? null),
                'UF' => self::scalar($ender['UF'] ?? null),
                'CEP' => self::scalar($ender['CEP'] ?? null),
            ];
        }

        return $out;
    }

    private static function normalizeValores(mixed $valores): ?array
    {
        if (!\is_array($valores)) {
            return null;
        }

        return [
            'vBC' => self::scalar($valores['vBC'] ?? null),
            'pAliqAplic' => self::scalar($valores['pAliqAplic'] ?? null),
            'vISSQN' => self::scalar($valores['vISSQN'] ?? null),
            'vLiq' => self::scalar($valores['vLiq'] ?? null),
        ];
    }
}
