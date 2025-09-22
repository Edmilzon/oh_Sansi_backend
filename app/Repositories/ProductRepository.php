<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository{

    public function getAllProducts(){
        return Product::all();
    }

    public function createProduct(array $data){
        return Product::create($data);
    }
}