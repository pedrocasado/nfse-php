<?php

namespace NFSePHP\NotaCarioca;

use NFSePHP\NotaCarioca\Operations\CancelarNfse;
use NFSePHP\NotaCarioca\Operations\ConsultarNfse;
use NFSePHP\NotaCarioca\Operations\ConsultarNfsePorRps;
use NFSePHP\NotaCarioca\Operations\GerarNfseNotaCarioca;

class NotaCariocaOperationFactory {

    public function createOperation(string $action = '', string $env = 'dev', array $rps = []) {
        switch($action)
        {
            case "cancelar":
                $notaCariocaAction = new CancelarNfse($env, $rps);
                break;
            case "consultar-nfse":
                $notaCariocaAction = new ConsultarNfse($env, $rps);
                break;
            case "consultar-por-rps":
                $notaCariocaAction = new ConsultarNfsePorRps($env, $rps);
                break;
            case "gerar-nfse":
                $notaCariocaAction = new GerarNfseNotaCarioca($env, $rps);
                break;
            default:
                $notaCariocaAction = new ConsultarNfse($env, $rps);
                break;
        }

        return $notaCariocaAction;
    }
}
