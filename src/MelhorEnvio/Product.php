<?php


namespace MelhorEnvio;


class Product
{
    /**
     * Responsável por armazenar um array de produtos
     *
     * @var array $products
     */
    private $products = [];


    /**
     * @return array
     */
    public function getProducts()
    {
        return  json_decode(json_encode($this->products));
    } // End >> fun::getProducts()


    /**
     * Método responsável por recolher todas as informações de um produto e
     * armazenar na array de produtos.
     *
     * @param $id
     * @param $name,
     * @param $width
     * @param $height
     * @param $length
     * @param $weight
     * @param $value
     * @param int $quantity
     */
    public function setProducts($id, $name, $width, $height, $length, $weight, $value, $quantity = 1)
    {
        // Informações
        $weight = ($weight >= 0.1) ? $weight : 0.1;
        $height = ($height >= 1) ? $height : 1;
        $width = ($width >= 10) ? $width : 10;
        $length = ($length >= 15) ? $length : 15;

        // Configura o produto
        $produto = [
            "id" => $id,
            "name" => $name,
            "width" => $width,
            "height" => $height,
            "length" => $length,
            "weight" => number_format($weight, 2, ".", ""),
            "insurance_value" => number_format($value, 2, ".", ""),
            "quantity" => $quantity
        ];

        // Adiciona ao array
        $this->products[] = $produto;
    } // End >> fun::setProducts()


} // End >> Class::Product