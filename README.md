<h1 align="center">SDK para Melhor Envio</h1>

<p align="center">
    <img src="https://img.shields.io/static/v1?label=license&message=MIT&color=0d7bbd" />
    <img src="https://img.shields.io/static/v1?label=version&message=BETA&color=0d7bbd" />
</p>



<p align="center">🚀 SDK para facilitar a integração com a plataforma Melhor Envio. Utilizando a liguagem PHP.</p>

<h3>Índice</h3>

<!--ts-->
   * [Instalação](#instalação)
   * [Autenticação](#autenticação)
   * [Renovando Token](#renovando-token)
   * [Calculando Frete](#calculando-frete)
   * [Etiquetas](#etiquetas)
      * [Solicitando Compra](#solicitando-compra)
      * [Processar Compra](#processar-compra)
      * [Gerar Etiquetas](#gerar-etiquetas)
      * [Recuperar Código de Rastreio](#recuperar-código-de-rastreio)
<!--te-->

## Pré-requisitos
    
Antes de começar, você vai precisar ter instalado em sua máquina as seguintes ferramentas:

- [Composer](https://getcomposer.org/)
- [Servidor Apache](https://www.apachefriends.org/index.html)

É necessario possuir um cadastro na plataforma [Melhor Envio](https://melhorenvio.com.br/)


## Instalação

Para instalar esse componente em seu projeto utilize o composer. ````composer require duug-com-br/melhorenvio-sdk-php````

## Autenticação

Primeiro você deve solicitar a permissão para utilização do aplicativo. Para isso utilize esse codigo de exemplo. 

````php
// Instancia o objeto
$MelhorEnvio = new MelhorEnvio\MelhorEnvio(
    "CLIENT ID",
    "SECRET KEY",
    "NOME DO APP",
    "EMAIL TECNICO"
);

// Adiciona a url de callback  
$MelhorEnvio->setCallbackURL("URL PARA RETORNO");

// Solicita a autenticacao
// O usuario será redirecionado para uma página da melhor envio.
$MelhorEnvio->requestAuthorization();
````

Após a solicitação de permissão o usuário será redirecionado para a url de callback informada. Nessa url será passado um código via GET que será utilizado para a geração de token.

````php
// Solicita o token
$retorno = $MelhorEnvio->requestToken($_GET["code"]);

// Verifica se não ocorreu erro 
if(!$retorno["error"])
{
    // Recupera as informações 
    $retorno = $retorno["data"];
}
````

Dentro do retorno data é retornado um array com as seguintes informações

````
(Array)
[
    "accessToken" => "TOKEN PARA REQUISIÇÕES",
    "refreshToken" => "TOKEN PARA RENOVAÇÂO DO accessToken",
    "tokenValidate" => "Data de validade do token (+ 30 dias)"
]
````

## Renovando Token

Exemplo de como renovar um token expirado 


````php
// Solicita a atualizacao
$resposta = $MelhorEnvio->refreshToken($refreshToken);

// Verifica se deu certo
if(!$resposta["error"])
{
    // Armaze os novos tokens 
    $resposta["data"];
}
````


## Calculando Frete

Exemplo de como calcular um frete para um determindado produto. Caso haja mais de um produto é apenas replicar a linha onde configuramos o produto.

````php
// Informa o token
$MelhorEnvio->setAccessToken("Access Token");

// Instancia o produto
$Product = new MelhorEnvio\Product();

// Seta as informações do produto.
// Pode duplicar esse item para adicionar mais produtos
$Product->setProducts(
    "Id do produto",
    "Nome do produto",
    "Largura",
    "Altura",
    "Comprimento",
    "Peso",
    "Valor do Produto",
    "Quantidade"
);

// Realiza o calculo do frete
$resposta = $MelhorEnvio->calculate("CEP do remetente", "CEP do destinatario", $Product);

// Verifica se deu certo
if(!$resposta["error"])
{
    // As informações do frete estão no array 
    $resposta["data"]
}
````

Veja um exemplo do array data retornado no calculo do frete.

````
(Array)
[
    company" => [
         "name" => Nome da transportadora
         "image" => Imagem da logo da transportadora
    ],
    "service" => Nome do serviço (ex: Pac, Sedex...)
    "timeDays" => Prazo em dias para entrega
    "code" => Codigo do servico
    "packages" => (Array) Lista dos pacotes que serão enviados
]
````

## Etiquetas

Com esse SDK é possivel realizar a compra de etiquetas atraves da plataforma Melhor Envio. Lembrando que é necessário ter

### Solicitando compra

Primeiro é necessário realizar uma solicitação de compra de etiqueta. Veja o código de exemplo:

````php
// Informa o token
$MelhorEnvio->setAccessToken("Access Token");

// Destinatario e Remetente
$Destinatario = new MelhorEnvio\User();
$Remetente = new MelhorEnvio\User();


// Adiciona as informações
$Destinatario->setDocumentos("CPF");

$Destinatario->setInformacaoPessoal("NOME", "EMAIL", "CELULAR");

$Destinatario->setEndereco([
    "endereco" => "Rua xyz",
    "numero" => 123,
    "bairro" => "Jardim São José",
    "cidade" => "São Paulo",
    "cep" => 11200363
]);



// Adiciona as informações do remetente
$Remetente->setDocumentos("CPF", "CNPJ", "INCRICAO ESTADUAL");

$Remetente->setInformacaoPessoal("NOME", "EMAIL", "CELULAR");

$Remetente->setEndereco([
    "endereco" => "Rua xyz",
    "numero" => 123,
    "bairro" => "Jardim São José",
    "cidade" => "São Paulo",
    "cep" => 11200363
]);



// Instancia o produto
$Product = new MelhorEnvio\Product();

// Seta as informações do produto.
// Pode duplicar esse item para adicionar mais produtos
$Product->setProducts(
    "Id do produto",
    "Nome do produto",
    "Largura",
    "Altura",
    "Comprimento",
    "Peso",
    "Valor do Produto",
    "Quantidade"
);

// Pacote 
// Quando foi calculado o valor do frete, ele retorno os pacotes disponiveis
$pacotes = []; 


/**
* OBS: 
* Em caso de vários pacotes para a transportadora correios 
* deverá realizar uma solicitação por pacote. As demais poderá 
* realizar apenas uma solicitação passando um array de pacotes, 
* da maneira que iremos fazer agora.
**/

// Percorre os pacotes 
foreach ($packages as $package)
{
    $pacotes[] = [
        "height" => $packages->dimensions->height,
        "width" => $packages->dimensions->width,
        "length" => $packages->dimensions->length,
        "weight" => $packages->weight
    ];
}

// Codigo do serviço de envio
$code = "CODIGO DO SERVICO (RETORNADO NA BUSCA DO VALOR)";

// Realiza a solicitação de compra das etiqueta
$resposta = $MelhorEnvio->requestBuyTag($Destinatario, $Remetente, $Product, $pacote, $code, "Identificador do Pedido");

// Verifica se deu certo
if(!$resposta["error"])
{
    // Será retorno os ids da solicitação
    // Armaze os ids para poder realizar a compra da etiqueta
    $ids = $resposta["data"];
}
````

## Processar Compra

Com os ids da solicitação em mão você agora deverá realizar a compra da etiqueta. Para esse processo funcionar é necessário que possua saldo na plataforma. 

````php
// Verifica se o id retornado não é um array 
if(!is_array($ids))
{
    // Força ser um array
    $ids = [$ids];
}

// Realiza a compra da etiqueta
$resposta = $MelhorEnvio->processBuyTag($ids);

// Verifica se deu certo
if(!$resposta["error"])
{
    // Apos o pagamento é necessário realizar a solicitação 
    // para impressão da etiqueta.
}
````

## Gerar Etiquetas

Após a etiqueta ser comprada deve-se solicitar a impressão da mesma, onde a plataforma retornará um link com o arquivo PDF da etiqueta.

````php
// Solicita a impressão das etiquetas
$resposta = $MelhorEnvio->printTag($ids);

// Verifica se deu certo
if(!$resposta["error"])
{
    // É retornado um array contendo a url para impressão 
    $resposta["data"]
    
    // Exemplo do array retornado no item data
    // (Array) ["url" => "URL DO PDF DA ETIQUETA"]
}
````

## Recuperar Código de Rastreio

Após ter gerado a etique é possivel solicitar o código de rastreio para informar ao cliente. 

veja o código de exemplo: 

````php
// Gera o codigo de rastreio
$rasteio = $MelhorEnvio->getTracking($ids);

// Verifica se deu certo
if(!$resposta["error"])
{
    // É retornado um array contendo os códigos 
    $resposta["data"]
}
````

Veja um exemplo do retorno na array data:

````
(Array)
[
    [
        "tracking" => "CÓDIGO DE RASTREIO"
    ],
    [
        "tracking" => "CÓDIGO DE RASTREIO"
    ]
]
````

Caso seja apenas um pacote será retornado apenas 1 item no array contendo o código de rastreio.


<h2>Licença</h2>
Lançado sob a licença [MIT](http://www.opensource.org/licenses/MIT)