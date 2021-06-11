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
    public function getProducts(): array
    {
        return $this->products;
    } // End >> fun::getProducts()


    /**
     * Método responsável por recolher todas as informações de um produto e
     * armazenar na array de produtos.
     *
     * @param $id
     * @param $width
     * @param $height
     * @param $length
     * @param $weight
     * @param $value
     * @param int $quantity
     */
    public function setProducts($id, $width, $height, $length, $weight, $value, $quantity = 1)
    {
        // Configura o produto
        $produto = [
            "id" => $id,
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