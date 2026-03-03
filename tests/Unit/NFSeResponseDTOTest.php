<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\NFSeResponseDTO;
use PHPUnit\Framework\TestCase;

final class NFSeResponseDTOTest extends TestCase
{
    private const SAMPLE_XML = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<NFSe versao="1.01" xmlns="http://www.sped.fazenda.gov.br/nfse">
    <infNFSe Id="NFS33045572238744743000149000000000001426035469277013">
        <xLocEmi>Rio de Janeiro</xLocEmi>
        <xLocPrestacao>Rio de Janeiro</xLocPrestacao>
        <nNFSe>14</nNFSe>
        <cLocIncid>3304557</cLocIncid>
        <xLocIncid>Rio de Janeiro</xLocIncid>
        <xTribNac>Agenciamento, corretagem ou intermediação.</xTribNac>
        <xTribMun>Intermediação para licenciamento.</xTribMun>
        <verAplic>SefinNacional_1.6.0</verAplic>
        <ambGer>2</ambGer>
        <tpEmis>1</tpEmis>
        <procEmi>1</procEmi>
        <cStat>100</cStat>
        <dhProc>2026-03-02T09:18:49-03:00</dhProc>
        <nDFSe>298733</nDFSe>
        <emit>
            <CNPJ>38744743000149</CNPJ>
            <xNome>BO COMPANIES COMERCIO E LOCACAO DE ROUPAS E ACESSORIOS S.A</xNome>
            <enderNac>
                <xLgr>DAS ACACIAS</xLgr>
                <nro>00039</nro>
                <xBairro>GAVEA</xBairro>
                <cMun>3304557</cMun>
                <UF>RJ</UF>
                <CEP>22451060</CEP>
            </enderNac>
            <fone>2122107277</fone>
            <email>ATENDIMENTO@BOBAGS.COM.BR</email>
        </emit>
        <valores>
            <vBC>250.00</vBC>
            <pAliqAplic>5.00</pAliqAplic>
            <vISSQN>12.50</vISSQN>
            <vLiq>250.00</vLiq>
        </valores>
    </infNFSe>
</NFSe>
XML;

    public function test_from_xml_parses_n_nfse_and_main_fields(): void
    {
        $dto = NFSeResponseDTO::fromXml(self::SAMPLE_XML);

        self::assertSame('NFS33045572238744743000149000000000001426035469277013', $dto->id);
        self::assertSame('14', $dto->nNFSe);
        self::assertSame('Rio de Janeiro', $dto->xLocEmi);
        self::assertSame('Rio de Janeiro', $dto->xLocPrestacao);
        self::assertSame('3304557', $dto->cLocIncid);
        self::assertSame('100', $dto->cStat);
        self::assertSame('2026-03-02T09:18:49-03:00', $dto->dhProc);
        self::assertSame('298733', $dto->nDFSe);
        self::assertSame('SefinNacional_1.6.0', $dto->verAplic);
        self::assertSame('2', $dto->ambGer);
    }

    public function test_from_xml_parses_emit_and_valores(): void
    {
        $dto = NFSeResponseDTO::fromXml(self::SAMPLE_XML);

        self::assertIsArray($dto->emit);
        self::assertSame('38744743000149', $dto->emit['CNPJ']);
        self::assertSame('BO COMPANIES COMERCIO E LOCACAO DE ROUPAS E ACESSORIOS S.A', $dto->emit['xNome']);
        self::assertSame('ATENDIMENTO@BOBAGS.COM.BR', $dto->emit['email']);
        self::assertSame('GAVEA', $dto->emit['enderNac']['xBairro']);

        self::assertIsArray($dto->valores);
        self::assertSame('250.00', $dto->valores['vBC']);
        self::assertSame('12.50', $dto->valores['vISSQN']);
        self::assertSame('250.00', $dto->valores['vLiq']);
    }

    public function test_from_xml_throws_on_invalid_xml(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid NFSe XML');

        NFSeResponseDTO::fromXml('<invalid>');
    }

    public function test_from_xml_throws_on_missing_inf_nfse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing infNFSe');

        NFSeResponseDTO::fromXml('<NFSe xmlns="http://www.sped.fazenda.gov.br/nfse"></NFSe>');
    }
}
