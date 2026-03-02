<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Valores do Serviço.
 */
class ValoresServicoDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $vServ, // Valor do serviço (required)

        #[Assert\NotBlank]
        public readonly string $vLiq, // Valor líquido (required)

        // Opcionais
        public readonly ?string $vBC = null,
        public readonly ?string $vDescIncond = null,
        public readonly ?string $vDescCond = null,
        public readonly ?string $vDR = null,
        public readonly ?string $vCalcDR = null,
        public readonly ?string $vCalcReeRepRes = null,
        public readonly ?string $vRedBCBM = null,
        public readonly ?string $vCalcBM = null,
        public readonly ?string $pAliqAplic = null,
        public readonly ?string $vISSQN = null,
        public readonly ?string $vRetCP = null,
        public readonly ?string $vRetIRRF = null,
        public readonly ?string $vRetCSLL = null,
        public readonly ?string $vRetISSQN = null,
        public readonly ?string $vPIS = null,
        public readonly ?string $vCOFINS = null,
        public readonly ?string $vTotalRet = null,

        // 1 ) "Isenção";
        // 2) "Redução da BC em 'ppBM' %";
        // 3) "Redução da BC em R$ 'vInfoBM' ";
        // 4) "Alíquota Diferenciada de 'aliqDifBM' %";
        public readonly ?string $tpBM = null,

        // "Tributação do ISSQN sobre o serviço prestado:
        // 1 - Operação tributável;
        // 2 - Imunidade;
        // 3 - Exportação de serviço;
        // 4 - Não Incidência;"
        public readonly ?string $tribISSQN = null,

        // 1 - Não Retido;
        // 2 - Retido pelo Tomador;
        // 3 - Retido pelo Intermediario
        public readonly ?string $tpRetISSQN = null,

        public readonly ?string $vTotTribFed = '0.00',
        public readonly ?string $vTotTribEst = '0.00',
        public readonly ?string $vTotTribMun = '0.00',
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
