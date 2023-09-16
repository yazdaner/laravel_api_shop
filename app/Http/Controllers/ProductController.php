<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductImagesResource;

class wProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::paginate(2);
        return $this->successResponse([
            'products' => ProductResource::collection($products->load('images')),
            'links' => ProductResource::collection($products)->response()->getData()->links,
            'meta' => ProductResource::collection($products)->response()->getData()->meta,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'brand_id' => 'required|integer',
            'category_id' => 'required|integer',
            'primary_image' => 'required|image',
            'description' => 'string',
            'price' => 'integer|nullable',
            'quantity' => 'integer|nullable',
            'delivery_amount' => 'integer|nullable',
            'images.*' => 'nullable|image'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        DB::beginTransaction();

        $primary_image_name = Carbon::now()->microsecond . '.' . $request->primary_image->extension();
        $request->primary_image->storeAs('images/products', $primary_image_name, 'public_api');




        $product = Product::create([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'primary_image' => $primary_image_name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'delivery_amount' => $request->delivery_amount,
        ]);


        if ($request->has('images')) {
            $imagesName = [];
            foreach ($request->images as $image) {

                $image_name = Carbon::now()->microsecond . '.' . $image->extension();
                $image->storeAs('images/products', $image_name, 'public_api');

                array_push($imagesName, $image_name);
            }
        }

        if ($request->has('images')) {
            foreach ($imagesName as $image_name) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image_name
                ]);
            }
        }

        DB::commit();

        return $this->successResponse(new ProductResource($product->load('images')), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $this->successResponse(new ProductResource($product->load('images')));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'brand_id' => 'integer',
            'category_id' => 'integer',
            'primary_image' => 'image|nullable',
            'description' => 'string',
            'price' => 'integer|nullable',
            'quantity' => 'integer|nullable',
            'delivery_amount' => 'integer|nullable',
            'images.*' => 'nullable|image'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        DB::beginTransaction();


        if ($request->has('primary_image')) {

            $primary_image_name = Carbon::now()->microsecond . '.' . $request->primary_image->extension();
            $request->primary_image->storeAs('images/products', $primary_image_name, 'public_api');
        }



        $product->update([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'primary_image' => $request->has('primary_image') ? $primary_image_name : $product->primary_image,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'delivery_amount' => $request->delivery_amount,
        ]);


        if ($request->has('images')) {
            $imagesName = [];
            foreach ($request->images as $image) {

                $image_name = Carbon::now()->microsecond . '.' . $image->extension();
                $image->storeAs('images/products', $image_name, 'public_api');

                array_push($imagesName, $image_name);
            }
        }
        if ($request->has('images')) {

            foreach ($product->images as $productImage) {
                $productImage->delete();
            }
        }
        if ($request->has('images')) {

            foreach ($imagesName as $image_name) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image_name
                ]);
            }
        }

        DB::commit();

        return $this->successResponse(new ProductResource($product->load('images')), 200, 'updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        $product->delete();
        foreach ($product->images as $image) {
            $image->delete();
        }
        DB::commit();

        return $this->successResponse(new ProductResource($product->load('images')), 200, 'deleted');

    }
}
