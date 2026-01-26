<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DetailOrder;
use App\Models\Order;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
  {
    $user = Auth::user() ?? \App\Models\User::first();
    $orders = Order::where('user_id', $user->id)
      ->with(['event' => function($query) {
        $query->select('id', 'judul', 'lokasi', 'gambar');
      }])
      ->orderBy('created_at', 'desc')
      ->select('id', 'user_id', 'event_id', 'order_date', 'total_harga', 'created_at')
      ->get();
    
    return view('order.index', compact('orders'));
  }

  // show a specific order
  public function show(Order $order)
  {
    $order->load('detailOrders.tiket', 'event');
    return view('orders.show', compact('order'));
  }

  // store an order (AJAX POST)
  public function store(Request $request)
  {
    \Log::info('Order store request received');

    $data = $request->validate([
      'event_id' => 'required|exists:events,id',
      'items' => 'required|array|min:1',
      'items.*.tiket_id' => 'required|integer|exists:tikets,id',
      'items.*.jumlah' => 'required|integer|min:1',
    ]);

    $user = Auth::user();
    
    if (!$user) {
      return response()->json(['ok' => false, 'message' => 'User not authenticated'], 401);
    }

    \Log::info('Order by user', ['user_id' => $user->id]);

    try {
      // transaction
      $order = DB::transaction(function () use ($data, $user) {
        \Log::info('Starting transaction');
        $total = 0;
        // Get all tickets (without lock for now to avoid deadlock issues)
        $tiketIds = array_column($data['items'], 'tiket_id');
        $tikets = Tiket::whereIn('id', $tiketIds)
          ->get()
          ->keyBy('id');

        \Log::info('Tickets fetched', ['count' => count($tikets)]);

        // validate stock and calculate total
        foreach ($data['items'] as $it) {
          if (!isset($tikets[$it['tiket_id']])) {
            throw new \Exception("Tiket tidak ditemukan");
          }
          $t = $tikets[$it['tiket_id']];
          if ($t->stok < $it['jumlah']) {
            throw new \Exception("Stok tidak cukup untuk tipe: {$t->tipe}. Stok tersedia: {$t->stok}");
          }
          $total += ($t->harga ?? 0) * $it['jumlah'];
        }

        \Log::info('Creating order', ['total' => $total]);

        $order = Order::create([
          'user_id' => $user->id,
          'event_id' => $data['event_id'],
          'order_date' => Carbon::now(),
          'total_harga' => $total,
        ]);

        \Log::info('Order created', ['order_id' => $order->id]);

        foreach ($data['items'] as $it) {
          $t = $tikets[$it['tiket_id']];
          $subtotal = ($t->harga ?? 0) * $it['jumlah'];
          DetailOrder::create([
            'order_id' => $order->id,
            'tiket_id' => $t->id,
            'jumlah' => $it['jumlah'],
            'subtotal_harga' => $subtotal,
          ]);

          // reduce stock
          $t->stok = max(0, $t->stok - $it['jumlah']);
          $t->save();
          \Log::info('Ticket stock updated', ['tiket_id' => $t->id, 'new_stock' => $t->stok]);
        }

        \Log::info('Order completed successfully', ['order_id' => $order->id]);
        return $order;
      });

      \Log::info('Returning success response', ['order_id' => $order->id]);
      return response()->json([
        'ok' => true, 
        'order_id' => $order->id, 
        'redirect' => route('orders.index')
      ]);
    } catch (\Exception $e) {
      \Log::error('Order processing error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
    }
  }
}
