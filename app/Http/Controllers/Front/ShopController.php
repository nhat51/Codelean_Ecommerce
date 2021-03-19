<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductComment;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Print_;

class ShopController extends Controller
{
    //
    public function show($id) {
        $product = Product::findOrFail($id);

        $avgRating = 0;
        $sumRating =array_sum(array_column($product->productComment->toArray(),'rating'));
        $countRating = count($product->productComment);
        if ($countRating !=0){
            $avgRating = $sumRating/$countRating;
        }
        $relatedProducts = Product::where('product_category_id',$product->product_category_id)
            ->where('tag',$product->tag)
            ->limit(4)
            ->get();

        return view('front.shop.show',compact('product','avgRating','relatedProducts'));
    }


    public function postComment(Request $request){
        ProductComment::create($request->all());

        return redirect()->back();

    }

    public function index(Request $request) {

        //Get Categories
        $categories = ProductCategory::all();

        //Get Products
        $penPage = $request->show ?? 3;
        $sortBy = $request->sort_by ?? 'latest';
        $search = $request->search ?? '';

        $products = Product::where('name', 'like', '%' . $search . '%');

        $products = $this->sortAndPagination($products,$sortBy,$penPage);

        return view('front.shop.index',compact('categories','products'));
    }

    public function category($categoryName,Request $request) {

        //Get Categories
        $categories = Product::all();

        //get products
        $penPage = $request->show ?? 3;
        $sortBy = $request->sort_by ?? 'latest';

        $products = ProductCategory::where('name',$categoryName)->first()->products->toQuery();

        $products = $this->sortAndPagination($products,$sortBy,$penPage);

        return view('front.shop.index',compact('categories','products'));
    }

    public function sortAndPagination($products, $sortBy, $penPage){
        switch ($sortBy){
            case 'latest':
                $products = $products->orderBy('id');
                break;
            case 'oldest':
                $products = $products->orderByDesc('id');
                break;
            case 'name-ascending':
                $products = $products->orderBy('name');
                break;
            case 'name-descending':
                $products = $products->orderByDesc('name');
                break;
            case 'price-ascending':
                $products = $products->orderBy('price');
                break;
            case 'price-descending':
                $products = $products->orderByDesc('price');
                break;
            default:
                $products = $products->orderBy('id');
        }
        $products = $products->paginate($penPage);

        $products->appends(['sort_by' => $sortBy,'show' => $penPage ]);

        return $products;
    }
}
