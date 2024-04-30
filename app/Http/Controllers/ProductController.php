<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //
    public function index():View {

        // get all products
        $products = Product::latest()->paginate(10);

        // render view with products
        return view("products.index", compact("products"));
    }

    public function create():View {
        return view("products.create");
    }

    public function store(Request $r):RedirectResponse {
        // validate form
        $r->validate([
            "image"=> "required|image|mimes:jpeg,jpg,png|max:2048",
            "title"=> "required|min:5",
            "description" => "required|min:10",
            "price"=> "required|numeric",
            "stock"=> "required|numeric",
        ]);

        // upload image
        $image = $r->file("image");
        $image->storeAs("public/products", $image->hashName());

        // create product

        Product::create([
            "image"=> $image->hashName(),
            "title"=> $r->title,
            "description"=> $r->description,
            "price"=> $r->price,
            "stock"=> $r->stock,
        ]);

        // redirect to index
        return redirect()->route("products.index")->with(["success"=>"Data is successfully saved"]);
    }

    public function show(string $id):View {
        // get product by id
        $product = Product::findOrFail($id);
        // render view with product
        return view("products.show", compact("product"));
    }

    public function edit(string $id):View {
        // get product id
        $product = Product::findOrFail($id);
        return view("products.edit", compact("product"));
    }

    public function update(Request $r, $id): RedirectResponse {
        // validate form
        $r->validate([
            "image"=> "image|mimes:jpeg,jpg,png|max:2048",
            "title"=> "required|min:5",
            "description" => "required|min:10",
            "price"=> "required|numeric",
            "stock"=> "required|numeric",
        ]);

        // get product by id
        $product = Product::findOrFail($id);

        // check if image is uploaded
        if($r->hasFile("image")){
            // upload new image
            $image = $r->file("image");
            $image->storeAs("public/products", $image->hashName());

            // delete old image
            Storage::delete('public/products/'.$product->image);

            // update product with new image
            $product->update([
                'image'         => $image->hashName(),
                'title'         => $r->title,
                'description'   => $r->description,
                'price'         => $r->price,
                'stock'         => $r->stock
            ]);
        }else {
            // update without image
            $product->update([
                'title'         => $r->title,
                'description'   => $r->description,
                'price'         => $r->price,
                'stock'         => $r->stock
            ]);
        }
        return redirect()->route('products.index')->with(['success'=>'Data is successfully updated']);
    }

    public function destroy($id): RedirectResponse {
        // get product id
        $product = Product::findOrFail($id);
        // delete image
        Storage::delete('public/products/'.$product->image);
        // delete product
        $product->delete();
        // redirect to index
        return redirect()->route('products.index')->with(['success'=>'Data is successfully delete']);
    }
}
