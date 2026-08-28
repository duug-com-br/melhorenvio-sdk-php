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

    }

    /**
     * Método responsável por alterar a url de requisições para
     * a url de teste da plataforma.
     */
    public function activeSandbox()
    {
        // Base Url - Sandbox
        $this->url = "https://sandbox.melhorenvio.com.br/";
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


    /**
     * Método responsável por calcular um frete de uma origem a um destino
     * com muitos ou um unico produto.
     * ---------------------------------------------------------------------------------------
     *
     * -- Exemplo de retorno
     *
     *  (Array)
     *  [
     *     "error"  => [true ou false]  // Informa se teve algum erro na solicitação
     *     "message" => Informação sobre o erro dado
     *     "data" => [
     *          "company" => [
     *              "name" => Nome da transportadora
     *              "image" => Imagem da logo da transportadora
     *           ],
     *          "service" => Nome do serviço (ex: Pac, Sedex...)
     *          "timeDays" => Prazo em dias para entrega
     *          "code" => Codigo do servico
     *          "packages" => Lista dos pacotes que serão enviados
     *      ]
     *  ]
     *
     * ---------------------------------------------------------------------------------------
     * @param $cepOrigem
     * @param $cepDestino
     * @param $Products
     * ----------------------------------------------------------------------
     * @return array
     */
    public function calculate($cepOrigem, $cepDestino, Product $Products, $avisoRecebimento = false, $maoPropria = false, array $services = null)
    {
        // Variavel de retorno
        $retorno = ["error" => true, "data" => null];
        $dados = null;

        // Recupera o accessToken
        $accessToken = $this->accessToken;

        // Verifica se informou o token
        if(!empty($accessToken))
        {
            // Url
            $url = $this->url . "api/v2/me/shipment/calculate";

            // Limpa os ceps
            $cepOrigem = preg_replace("/[^0-9]/", "", $cepOrigem);
            $cepDestino = preg_replace("/[^0-9]/", "", $cepDestino);

            // Recupera os produtos
            $produtos = $Products->getProducts();

            // Cria o objeto de conteudo
            $conteudo = new \StdClass();

            // Informa a origem e destino
            $conteudo->from = new \StdClass();
            $conteudo->from->postal_code = $cepOrigem;

            $conteudo->to = new \StdClass();
            $conteudo->to->postal_code = $cepDestino;

            // Percorre os produtos e adiciona ao array
            foreach ($produtos as $produto) {
                $conteudo->products[] = $produto;
            }

            // Aplicar aviso de recebimento
            if ($avisoRecebimento) {
                $conteudo->options->receipt = true;
            }
            
            // Aplicar aviso mão propria
            if ($maoPropria) {
                $conteudo->options->own_hand = true;
            }

            // Definir códigos de serviços utilizados
            if (empty($services) == false) {
                $conteudo->services = implode(',', $services);
            }

            // Converte em json o conteudo
            $conteudo = json_encode($conteudo);

            // Header
            $header = ["Authorization: Bearer {$accessToken}", "Content-Type: application/json"];

            // Realiza a requisição
            $resultado = (new SendCurl($this->nameApp, $this->email))->resquest($url, "POST", $header, $conteudo);

            // Decodifica
            $resultado = json_decode($resultado);

            // Verifica se é um array
            if(is_array($resultado))
            {
                // Percorre
                foreach ($resultado as $res)
                {
                    // Verifica se não deu erro
                    if(empty($res->error))
                    {
                        // Add o conteudo
                        $dados[] = [
                            "company" => [
                                "name" => $res->company->name,
                                "image" =>$res->company->picture
                            ],
                            "code" => $res->id,
                            "service" => $res->name,
                            "value" => $res->custom_price,
                            "timeDays" => $res->custom_delivery_time,
                            "packages" => $res->packages
                        ];
                    }
                }

                // Monta o retorno
                $retorno = [
                    "error" => false,
                    "message" => "success",
                    "data" => $dados
                ];
            }
            else
            {
                // Verifica de deu erro na autenticacao
                if($resultado == "Unauthenticated")
                {
                    // Informa do erro
                    $retorno["message"] = "Unauthenticated";
                }
                else
                {
                    // Informa do erro
                    $retorno["message"] = "Ocorreu um erro ao calcular.";
                }
            } // Error >> Autenticação ou Erro no calculo.
        }
        else
        {
            $retorno["message"] = "Access Token não informado.";
        } // Error >> Token não informado

        // Retorno
        return $retorno;

    } // End >> fun::calculate()


    /**
     * Método resposável por solicitar um a compra de uma etiqueta na
     * plataforma do melhor envio. Será retornado os ids da solicitadação
     * que apos deverá ser realizada a compra.
     * ----------------------------------------------------------------------
     * Exemplo do Packges a ser enviado:
     *
     * (Array)
     * [
     *    "height" => Altura
     *    "width" => Largura
     *    "length" => Comprimento
     *    "weight" => Peso
     * ]
     *
     * ----------------------------------------------------------------------
     * @param User $Destinario
     * @param User $Remetente
     * @param Product $Products
     * @param array $packages
     * @param $codService
     * @param $idPedido
     * @param null $urlPedido
     * ----------------------------------------------------------------------
     * @return array
     */
    public function requestBuyTag(User $Destinario, User $Remetente, Product $Products, array $packages, $codService, $idPedido, $urlPedido = null)
    {
        // Variaveis
        $retorno = ["error" => true, "data" => null];
        $valorTotal = 0;

        // Url de requisição
        $url = $this->url . "api/v2/me/cart";

        // Recupera o accessToken
        $accessToken = $this->accessToken;

        // Recupera os produtos
        $produtos = $Products->getProducts();

        // Inicia o objeto de envio
        $conteudo = new \StdClass();

        // Servico
        $conteudo->service = $codService;

        // Informações do remetente e destinatario
        $conteudo->from = $Remetente->getObjetc();
        $conteudo->to = $Destinario->getObjetc();

        // Produtos
        $conteudo->products = [];

        // Percorre os produtos
        foreach ($produtos as $produto)
        {
            // Soma o total
            $valorTotal = $valorTotal + ($produto->quantity * $produto->insurance_value);

            // Adiciona o produto
            $conteudo->products[] = [
                "name" => $produto->name,
                "quantity" => $produto->quantity,
                "unitary_value" => $produto->insurance_value
            ];
        }

        // Opcoes
        $conteudo->options = new \StdClass();
        $conteudo->options->insurance_value = $valorTotal;
        $conteudo->options->receipt = false;
        $conteudo->options->own_hand = false;
        $conteudo->options->reverse = false;
        $conteudo->options->non_commercial = true;
        $conteudo->options->tags[] = [
            "tag" => $idPedido,
            "url" => $urlPedido
        ];

        // Adiciona os pacotes para compra
        $conteudo->volumes = $packages;

        // Header
        $header = ["Authorization: Bearer {$accessToken}", "Content-Type: application/json"];

        // Codifica em json o conteudo
        $conteudo = json_encode($conteudo);

        // Realiza a requisição
        $resposta = (new SendCurl($this->nameApp, $this->email))->resquest($url, "POST", $header, $conteudo);

        // Decodifica a responsa
        $resposta = json_decode($resposta);

        // Veririfca se deu erro
        if(!empty($resposta->errors) || !empty($resposta->error))
        {
            // Adiciona o objeto
            $retorno["data"] = (!empty($resposta->errors) ? $resposta->errors : $resposta->error);
        }
        else
        {
            // Retorno de sucesso
            $retorno = [
                "error" => false,
                "message" => "success",
                "data" => $resposta->id
            ];
        }

        // Retorno
        return $retorno;

    } // End >> fun::requestBuyTag()


    /**
     * Método responsável por processar a compra das etiquetas já solicitadas
     * anteriormente e apos pago solicita a geração do numero da etiqueta.
     * ----------------------------------------------------------------------
     * @param array $ids
     * @return array
     */
    public function processBuyTag(array $ids)
    {
        // Gera o pagamento
        $retorno = ["error" => true, "data" => null];
        $resposta = null;
        $conteudo = null;

        // Url
        $url = $this->url . "api/v2/me/shipment/checkout";

        // Configura o conteudo
        $conteudo = new \StdClass();
        $conteudo->orders = $ids;

        // Codifica o conteudo em json
        $conteudo = json_encode($conteudo);

        // Instancia o objeto de requisição
        $SendCurl = new SendCurl($this->nameApp, $this->email);

        // Header
        $header = ["Authorization: Bearer {$this->accessToken}", "Content-Type: application/json"];

        // Realiza a requisição
        $resposta = $SendCurl->resquest($url, "POST", $header, $conteudo);

        // Decodifica
        $resposta = json_decode($resposta);

        // Verifica se não deu erro
        if(empty($resposta->errors) && empty($resposta->error))
        {
            // Gera a etiqueta
            $retorno = $this->requestTag($SendCurl, $header, $conteudo);
        }
        else
        {
            // Incorporra o erro
            $retorno["message"] = "Ocorreu um erro ao realizar compra das etiquetas";
            $retorno["data"] = (!empty($resposta->errors) ? $resposta->errors : $resposta->error);
        } // Error >> Erro ao realizar compra das etiquetas

        // Retorno
        return $retorno;

    } // End >> fun::processBuyTag()


    /**
     * Método responsável por solicitar o arquivo para impressão
     * da etiqueta.
     * ----------------------------------------------------------------------
     * @param array $ids
     * @return array
     */
    public function printTag(array $ids)
    {
        // Gera o pagamento
        $retorno = ["error" => true, "data" => null];
        $resposta = null;
        $conteudo = null;

        // Url
        $url = $this->url . "api/v2/me/shipment/print";

        // Configura o conteudo
        $conteudo = new \StdClass();
        $conteudo->mode = "public";
        $conteudo->orders = $ids;

        // Codifica o conteudo em json
        $conteudo = json_encode($conteudo);

        // Header
        $header = ["Authorization: Bearer {$this->accessToken}", "Content-Type: application/json"];

        // Realiza a requisição
        $resposta = (new SendCurl($this->nameApp, $this->email))->resquest($url, "POST", $header, $conteudo);

        // Decodifica
        $resposta = json_decode($resposta);

        // Verifica se deu erro
        if(!empty($resposta->errors) || !empty($resposta->error))
        {
            // Explica o erro ocorrido
            $retorno["data"] = (!empty($resposta->errors) ? $resposta->errors : $resposta->error);
            $retorno["message"] = "Ocorreu um erro ao imprimir etiqueta";
        }
        else
        {
            // Array de sucesso
            $retorno = [
                "error" => false,
                "message" => "success",
                "data" => $resposta
            ];
        }

        // Retorno
        return $retorno;

    } // End >> fun::print()



    /**
     * Método responsável por solicitar os códigos de rastreio
     * de uma etiqueta previamente gerada.
     * ----------------------------------------------------------------------
     * Exemplo retorno em caso de sucesso:
     *
     * (Array)
     * [
     *    "error" => false,
     *    "message" => "success",
     *    "data" => [
     *        {"tracking" => "CODIGO DE RASTREIO"}
     *    ]
     * ]
     *
     * ----------------------------------------------------------------------
     * @param array $ids
     * @return array
     */
    public function getTracking(array $ids)
    {
        // Variaveis
        $retorno = ["error" => true, "data" => null];

        // Configura a url
        $url = $this->url . "api/v2/me/shipment/tracking";

        // Configura o conteudo
        $conteudo = new \StdClass();
        $conteudo->orders = $ids;

        // Codifica o conteudo em json
        $conteudo = json_encode($conteudo);

        // Header
        $header = ["Authorization: Bearer {$this->accessToken}", "Content-Type: application/json"];

        // Realiza a requisição
        $resposta = (new SendCurl($this->nameApp, $this->email))->resquest($url, "POST", $header, $conteudo);

        // Decodifica
        $resposta = json_decode($resposta);

        // Verifica se deu erro
        if(!empty($resposta->errors) || !empty($resposta->error))
        {
            // Explica o erro ocorrido
            $retorno["data"] = (!empty($resposta->errors) ? $resposta->errors : $resposta->error);
            $retorno["message"] = "Ocorreu um erro ao solicitar códgio de rastreio.";
        }
        else
        {
            // Array de sucesso
            $retorno = [
                "error" => false,
                "message" => "success",
                "data" => $resposta
            ];
        }

        // Retorno
        return $retorno;

    } // End >> fun::getTracking()


    /**
     * Método responsável por listar os códigos de serviços disponiveis para o
     * cálculo de frete.
     * ----------------------------------------------------------------------
     * Exemplo retorno em caso de sucesso:
     *
     * (Array)
     * [
     *    "error" => false,
     *    "message" => "success",
     *    "data" => [
     *        {"id" => "CODIGO DO SERVICO", "name" => "NOME DO SERVICO", "status" => "available"}
     *    ]
     * ]
     *
     * ----------------------------------------------------------------------
     * @return array
     */
    public function getServices()
    {
        // Variaveis
        $retorno = ["error" => true, "data" => null];

        // Configura a url
        $url = $this->url . "api/v2/me/shipment/services";

        // Header
        $header = ["Authorization: Bearer {$this->accessToken}", "Content-Type: application/json"];

        // Realiza a requisição
        $resposta = (new SendCurl($this->nameApp, $this->email))->resquest($url, "GET", $header);

        // Decodifica
        $resposta = json_decode($resposta);

        // Verifica se deu erro
        if(!empty($resposta->errors) || !empty($resposta->error))
        {
            // Explica o erro ocorrido
            $retorno["data"] = (!empty($resposta->errors) ? $resposta->errors : $resposta->error);
            $retorno["message"] = "Ocorreu um erro ao listar os códigos de serviço.";
        }
        else
        {
            // Array de sucesso
            $retorno = [
                "error" => false,
                "message" => "success",
                "data" => $resposta
            ];
        }

        // Retorno
        return $retorno;

    } // End >> fun::getServices()


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


    /**
     * Método responsável por solicitar a geração de uma etiqueta
     * apos ela estiver comprada.
     * --------------------------------------------------------------------------
     * @param SendCurl $SendCurl
     * @param $header
     * @param $conteudo
     * --------------------------------------------------------------------------
     * @return array
     */
    private function requestTag(SendCurl $SendCurl, $header, $conteudo)
    {
        // Gera o pagamento
        $retorno = ["error" => true, "data" => null];
        $resposta = null;

        // Url
        $url = $this->url . "api/v2/me/shipment/generate";

        // Realiza a requisição
        $resposta = $SendCurl->resquest($url, "POST", $header, $conteudo);

        // Verifica se deu erro
        if(!empty($resposta->errors) || !empty($resposta->error))
        {
            // Explica o erro ocorrido
            $retorno["data"] = (!empty($resposta->errors) ? $resposta->errors : $resposta->error);
            $retorno["message"] = "Erro ao gerar etiqueta";
        }
        else
        {
            $retorno = [
                "error" => false,
                "message" => "success",
                "data" => $resposta
            ];
        }

        // Retorna
        return $retorno;

    } // End >> fun::requestTag()

} // End >> Class::MelhorEnvio