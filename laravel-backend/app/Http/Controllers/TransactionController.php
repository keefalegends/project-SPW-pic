<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('product')->get();
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        if ($request->has('items')) {
            $request->validate([
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.jumlah' => 'required|integer|min:1',
            ]);
            $items = $request->items;
        } else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'jumlah' => 'required|integer|min:1',
            ]);
            $items = [
                [
                    'product_id' => $request->product_id,
                    'jumlah' => $request->jumlah
                ]
            ];
        }

        return DB::transaction(function () use ($items) {
            $createdTransactions = [];
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stok < $item['jumlah']) {
                    return response()->json([
                        'message' => "Stok produk '{$product->nama_produk}' tidak mencukupi. (Stok tersedia: {$product->stok})"
                    ], 400);
                }

                $total_harga = $product->harga_jual * $item['jumlah'];

                $transaction = Transaction::create([
                    'product_id' => $product->id,
                    'jumlah' => $item['jumlah'],
                    'total_harga' => $total_harga,
                ]);

                $product->stok -= $item['jumlah'];
                $product->save();

                $createdTransactions[] = $transaction->load('product');
            }

            return response()->json([
                'message' => 'Transaction successful',
                'data' => $createdTransactions
            ], 201);
        });
    }
}
