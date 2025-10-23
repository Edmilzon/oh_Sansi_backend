<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Routing\Controller;

class ProductController extends Controller {

    protected $productService;

    public function __construct(ProductService $productService){
        $this->productService = $productService;
    }

    public function index(){
        $products = $this->productService->getProductList();
        return response()->json($products);
    }
    
    public function store(Request $request){
        $validateData = $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'stock'       => 'required|integer',
            'category'    => 'nullable|string',
            'image_url'   => 'required|url',
            'is_active'   => 'boolean'
        ]);
        
        $product = $this->productService->createNewProduct($validateData);
        return response()->json([
            'product' => $product
        ], 201);
    }
    
}