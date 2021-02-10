<?php

namespace MelhorEnvio;

// Inicia a Classe
class MelhorEnvio
{
    // Privates
    private $clientID;
    private $token;
    private $code;
    private $nomeApp;
    private $emailTecnico;
    private $baseUrl; // Sandbox ou Producao


    // Método construtor
    public function __construct($tipo = "sandbox")
    {
        // Verifica o tipo
        if($tipo == "producao")
        {
            // Base Url - Sandbox
            $this->baseUrl = "https://sandbox.melhorenvio.com.br/";
        }
        else
        {
            // Base Url - Produto
            $this->baseUrl = "https://www.melhorenvio.com.br/";
        }

    } // End >> fun::__construct()


    /**
     * @param mixed $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    } // End >> fun::setClientID()


    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    } // End >> fun::setToken()


    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    } // End >> fun::setCode()


    /**
     * @param mixed $nomeApp
     */
    public function setNomeApp($nomeApp)
    {
        $this->nomeApp = $nomeApp;
    } // End >> fun::setNomeApp()


    /**
     * @param mixed $emailTecnico
     */
    public function setEmailTecnico($emailTecnico)
    {
        $this->emailTecnico = $emailTecnico;
    } // End >> fun::setToken()


    /**
     * Método responsável por montar a url de redirecionamento de usuário, para o mesmo
     * autorizar o app a possui permissão sobre a conta do melhor envio.
     * Caso não seja informado a permissão, será solicitado as permissões padrao.
     * ---------------------------------------------------------------------------------------
     * @param $callback | Url de retorno
     * @param null $state
     * @param null $permissoes
     */
    public function solicitaAutorizacao($callback, $state = null, $permissoes = null)
    {
        // Verifica se informou permissão, senão habilita as padrões
        $permissoes = (!empty($permissoes) ? $permissoes : "cart-write notifications-read orders-read purchases-read shipping-calculate shipping-cancel shipping-checkout shipping-companies shipping-generate shipping-preview shipping-print shipping-tracking ecommerce-shipping");

        // Verifica se vai possui state
        $state = (!empty($state) ? "&state=" . $state : "");

        // Verifica se possui o cliente id
        if(!empty($this->clientID))
        {
            // Configura a url de redirecionamento
            $urlRedirect = $this->baseUrl . "oauth/authorize?client_id={$this->clientID}&redirect_uri={$callback}&response_type=code{$state}&scope={$permissoes}";

            // Redireciona
            header("Location: " . $urlRedirect);
        }
        else
        {
            // Msg
            echo "Cliente id não informado.";
        } // Error >> Cliente id não informado.

    } // End >> fun::solicitaAutorizacao()

    // https://docs.menv.io/?version=latest#03becc90-8b38-47bd-ba14-7994017462f0
    public function solicitaToken()
    {
        
    }

} // End >> Class::MelhorEnvio