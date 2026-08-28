<?php

// NameSpace
namespace MelhorEnvio;

// Inicia a classe SendCurl
class SendCurl
{
    // Variaveis
    private $nomeApp;
    private $emailTecnico;

    // Método construtor
    public function __construct($nomeApp, $emailTecnico)
    {
        // Salva as informações
        $this->nomeApp = $nomeApp;
        $this->emailTecnico = $emailTecnico;

    } // End >> fun::__construct()


    /**
     * Método responsável por realizar requisições para a api de integração
     * com o melhor envio envio.
     * ----------------------------------------------------------------------------
     * @param $url
     * @param string $metodo
     * @param array $header
     * @param array $conteudo
     * ----------------------------------------------------------------------------
     * @return array $response
     */
    public function resquest($url, $metodo = "POST", $header = null, $conteudo = null)
    {
        // Verifica se possui conteudo
        if(!empty($header))
        {
            // Informa itens padrões no header
            array_push($header, "Accept: application/json", "User-Agent: {$this->nomeApp} ({$this->emailTecnico})");
        }
        else
        {
            // Informa itens padrões no header
            $header = [
                "Accept: application/json",
                "User-Agent: {$this->nomeApp} ({$this->emailTecnico})"
            ];
        }

        // Inicia o curl
        $curl = curl_init();

        // Configura o envio
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $metodo,
            CURLOPT_POSTFIELDS => $conteudo,
            CURLOPT_HTTPHEADER => $header,
        ));

        // Execulta a solicitação
        $response = curl_exec($curl);

        // Fecha a solicitação
        curl_close($curl);

        // Retorna o resultado
        return $response;

    } // End >> fun::resquest()

} // End >> Class::SendCurl()