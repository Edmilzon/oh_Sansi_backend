<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository){
        $this->productRepository = $productRepository;
    }

    public function getProductList(){
        return $this->productRepository->getAllProducts();
    }

    public function createNewProduct(array $data){
        return $this->productRepository->createProduct($data);
    }
}