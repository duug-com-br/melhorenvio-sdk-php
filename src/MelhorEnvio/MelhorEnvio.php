<?php

namespace MelhorEnvio;

/**
 * Classe responsável por realizar as solicitações com a
 * plataforma melhor envio. É possivel calcular Frete e
 * gerar etiquetas.
 * ---------------------------------------------------------
 * Class MelhorEnvio
 * @package MelhorEnvio
 * @author igorcacerez
 */
class MelhorEnvio
{
    // Identificação do app
    private $clientId;
    private $secretKey;
    private $nameApp;
    private $email; // Email tecnico

    // Constantes
    private $url = "https://www.melhorenvio.com.br/";
    private $urlRastreio = "https://www.melhorrastreio.com.br/rastreio/";

    // Url de retorno da plataforma
    private $urlCallback;

    // Informações do app já instalado
    private $accessToken;


    /**
     * MelhorEnvio constructor.
     *
     * Método construtor responsável por adicionar as constantes os valores
     * de configuração e credenciais do app utilizado.
     *
     * @param $id // Cliente ID
     * @param $secret // Secret Key
     * @param $nameApp // Nome do aplicativo
     * @param $email // Email do técnico
     */
    public function __construct($id, $secret, $nameApp, $email)
    {
        // Repassa as configurações iniciais para as globais
        $this->clientId = $id;
        $this->secretKey = $secret;
        $this->nameApp = $nameApp;
        $this->email = $email;

    } // End >> fun::__construct()


    /**
     * Método responsável por alterar a url de requisições para
     * a url de teste da plataforma.
     */
    public function activeSandbox()
    {
        // Base Url - Sandbox
        $this->baseUrl = "https://sandbox.melhorenvio.com.br/";
    } // End >> fun::activeSandbox()


    /**
     * Método responsável por receber a url de retorno da
     * plataforma e salva na constante especifica.
     *
     * @param $url
     */
    public function setCallbackURL($url)
    {
        // Salva a informação na constante
        $this->urlCallback = $url;

    } // End >> fun::setCallbackURL()


    /**
     * Método responsável por salvar o Access Token
     * na constante.
     *
     * @param $token
     */
    public function setAccessToken($token)
    {
        // Salva o Access Token
        $this->accessToken = $token;

    } // End >> fun::setAccessToken()


    /**
     * Método responsável por montar a url de redirecionamento de usuário, para o mesmo
     * autorizar o app a possui permissão sobre a conta do melhor envio.
     * Caso não seja informado a permissão, será solicitado as permissões padrao.
     * ---------------------------------------------------------------------------------------
     * @param null $state // Status a ser retornado
     * @param null $permission // Lista de permissões
     * @param bool $redirect // Informa se deve redirecionar ou retornar a url gerada
     * ---------------------------------------------------------------------------------------
     * @return string
     */
    public function requestAuthorization($state = null, $permission = null, $redirect = true)
    {
        // Verifica se informou permissão, senão habilita as padrões
        $permission = (!empty($permission) ? $permission : "cart-read cart-write companies-read companies-write coupons-read coupons-write notifications-read orders-read products-read products-write purchases-read shipping-calculate shipping-cancel shipping-checkout shipping-companies shipping-generate shipping-preview shipping-print shipping-share shipping-tracking ecommerce-shipping transactions-read users-read users-write");

        // Verifica se vai possui state
        $state = (!empty($state) ? "&state=" . $state : "");

        // Configura a url de redirecionamento
        $urlRedirect = $this->url . "oauth/authorize?client_id={$this->clientId}&redirect_uri={$this->urlCallback}&response_type=code{$state}&scope={$permission}";

        // Verifica se deve redirecionar ou retornar a url
        if($redirect == true)
        {
            // Redireciona
            header("Location: " . $urlRedirect);
        }
        else
        {
            // Retorna a url configurada
            return $urlRedirect;
        }

    } // End >> fun::requestAuthorization()



    /**
     * Método responsável por solicitar um token de acesso na plataforma do melhor envio.
     * Esse método é utilizado quando nunca foi solicitado um token antes.
     * ---------------------------------------------------------------------------------------
     *
     * -- Exemplo de retorno
     *
     *  (Array)
     *  [
     *     "error"  => [true ou false]  // Informa se teve algum erro na solicitação
     *     "data" => [
     *          "accessToken" => Token gerado e que será utilizado nas requisições
     *          "tokenValidate" => Data de validade do token (+ 30 dias)
     *          "refreshToken" => Token utilizado para renovar o token quando ele estiver vencido
     *      ]
     *  ]
     *
     * ---------------------------------------------------------------------------------------
     * @param $code // Codigo retornado pela plaforma quando solicita a permissão
     * ---------------------------------------------------------------------------------------
     * @return array
     */
    public function requestToken($code)
    {
        // Retorno
        return $this->requestoOrRefreshToken($code);

    } // End >> fun::requestToken()


    /**
     * Método responsável por solicitar a renovação de um token de acesso já existente
     * na plataforma do melhor envio.
     * ---------------------------------------------------------------------------------------
     *
     * -- Exemplo de retorno
     *
     *  (Array)
     *  [
     *     "error"  => [true ou false]  // Informa se teve algum erro na solicitação
     *     "data" => [
     *          "accessToken" => Token gerado e que será utilizado nas requisições
     *          "tokenValidate" => Data de validade do token (+ 30 dias)
     *          "refreshToken" => Token utilizado para renovar o token quando ele estiver vencido
     *      ]
     *  ]
     *
     * ---------------------------------------------------------------------------------------
     * @param $refreshToken // Token de atualização do token de solicitação
     * ---------------------------------------------------------------------------------------
     * @return array
     */
    public function refreshToken($refreshToken)
    {
        // Retorno
        return $this->requestoOrRefreshToken(null, $refreshToken);

    } // End >> fun::refreshToken()


    public function calculate($cepOrigem, $cepDestino)
    {
        //

    } // End >> fun::calculate()


    /**
     * Método interno responsável por realizar a configuração e a requisição
     * tanto para gerar um token novo como para renovar um token já existente.
     * --------------------------------------------------------------------------
     * @param null $code
     * @param null $refreshToken
     * @return array
     */
    private function requestoOrRefreshToken($code = null, $refreshToken = null)
    {
        // Variavel de retorno
        $retorno = ["error" => true, "data" => null]; // Pré definida como erro.

        // Configura a url
        $url = $this->url . "oauth/token";

        // Conteudo a ser informado na solicitação
        $conteudo = [
            "grant_type" => "authorization_code",
            "client_id" => $this->clientId,
            "client_secret" => $this->secretKey,

        ];

        // Verifica o tipo da solicitacao
        if(!empty($code))
        {
            $conteudo["redirect_uri"] = $this->urlCallback;
            $conteudo["code"] = $code;
        }
        else
        {
            $conteudo["grant_type"] = "refresh_token";
            $conteudo["refresh_token"] = $refreshToken;
        }

        // Instancia o objeto de requisição
        $SendCurl = new SendCurl($this->nameApp, $this->email);

        // Realiza a solicitação
        $resposta = $SendCurl->resquest($url, "POST", null, $conteudo);

        // Decodifica o json
        $resposta = (!empty($resposta) ? json_decode($resposta) : null);

        // Verifica se retornou o token
        if(!empty($resposta->access_token))
        {
            // Retorna como sucesso
            $retorno = [
                "error" => false,
                "data" => [
                    "accessToken" => $resposta->access_token,
                    "tokenValidate" => date("Y-m-d", strtotime("+30 days")),
                    "refreshToken" => $resposta->refresh_token
                ]
            ];
        }

        // Retorno
        return $retorno;

    } // End >> fun::requestoOrRefreshToken()


} // End >> Class::MelhorEnvio