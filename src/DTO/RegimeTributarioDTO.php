<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Regime Tributário.
 */
class RegimeTributarioDTO implements \JsonSerializable
{
    public function __construct(
        // 1 - Não Optante;
        // 2 - Optante - Microempreendedor Individual (MEI);
        // 3 - Optante - Microempresa ou Empresa de Pequeno Porte (ME/EPP);
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2', '3'])]
        public readonly string $opSimpNac = '1',

        // 0 - Nenhum;
        // 1 - Ato Cooperado (Cooperativa);
        // 2 - Estimativa;
        // 3 - Microempresa Municipal;
        // 4 - Notário ou Registrador;
        // 5 - Profissional Autônomo;
        // 6 - Sociedade de Profissionais;
        // 9 - Outros;
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['0', '1', '2', '3', '4', '5', '6'])]
        public readonly string $regEspTrib = '0', // Regime Especial de Tributação

        // 1 – Regime de apuração dos tributos federais e municipal pelo SN;
        // 2 – Regime de apuração dos tributos federais pelo SN e o ISSQN pela NFS-e conforme respectiva legislação municipal do tributo;
        // 3 – Regime de apuração dos tributos federais e municipal pela NFS-e conforme respectivas legilações federal e municipal de cada tributo;
        public readonly ?string $regApTribSN = null, // Regime de Apuração Tributária SN
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
