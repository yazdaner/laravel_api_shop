<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends ApiController
{


    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'order_items' => 'required',
            'order_items.*.product_id' => 'required|integer|exists:products,id',
            'order_items.*.quantity' => 'required|integer',
            'request_from' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $total_amount = 0;
        $delivery_amount = 0;
        foreach ($request->order_items as $order_item) {
            $product = Product::findOrFail($order_item['product_id']);
            if ($product->quantity < $order_item['quantity']) {
                return $this->errorResponse('The product quantity is incorrect', 422);
            }
            $total_amount += $order_item['quantity'] * $product->price;
            $delivery_amount += $order_item['quantity'] * $product->delivery_amount;
        }
        $paying_amount = $total_amount + $delivery_amount;

        $amounts = [
            'paying_amount' => $paying_amount,
            'total_amount' => $total_amount,
            'delivery_amount' => $delivery_amount
        ];

        $api = env('PAY_IR_API_KEY');
        $amount = $amounts['paying_amount'] . '0';
        $mobile = "شماره موبایل";
        $factorNumber = "شماره فاکتور";
        $description = "توضیحات";
        $redirect = env('PAY_IR_CALLBACK_URL');
        $result = $this->sendRequest($api, $amount, $redirect, $mobile, $factorNumber, $description);
        $result = json_decode($result);

        if ($result->status) {

            OrderController::create($request->all(), $amounts, $result->token);

            $go = "https://pay.ir/pg/$result->token";

            return $this->successResponse([
                'url' => $go
            ]);
        } else {
            return $this->errorResponse($result->errorMessage, 422);
        }
    }


    public function sendRequest($api, $amount, $redirect, $mobile = null, $factorNumber = null, $description = null)
    {
        return $this->curl_post('https://pay.ir/pg/send', [
            'api'          => $api,
            'amount'       => $amount,
            'redirect'     => $redirect,
            'mobile'       => $mobile,
            'factorNumber' => $factorNumber,
            'description'  => $description,
        ]);
    }

    public function verifyRequest($api, $token)
    {
        return $this->curl_post('https://pay.ir/pg/verify', [
            'api'     => $api,
            'token' => $token,
        ]);
    }

    public function curl_post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }



    public function verify(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'status' => 'required',

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }


        $api = env('PAY_IR_API_KEY');
        $token = $request->token;
        $result = json_decode($this->verifyRequest($api, $token));
        // return response()->json($result);
        if (isset($result->status)) {
            if ($result->status == 1) {
                if (Transaction::where('trans_id',$result->transId)->exists()) {
                    return $this->errorResponse('This transaction has already been created', 422);
                }
                OrderController::update($token, $result->transId);
                return $this->successResponse([], 200, 'The transaction was completed successfully');
            } else {
                return $this->errorResponse('The transaction encountered an error', 422);
            }
        } else {
            if ($request->status == 0) {
                return $this->errorResponse('The transaction encountered an error', 422);
            }
        }
    }
}
