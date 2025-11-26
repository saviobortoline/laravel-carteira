<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    // =========================================================================
    // DASHBOARD – Painel principal
    // =========================================================================
    public function dashboard()
    {
        $user = auth()->user();

        // Carteira do usuário logado
        $wallet = $user->wallet;

        // Transações do usuário (como remetente ou destinatário)
        $transactions = Transaction::with(['fromUser', 'toUser'])
            ->where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })
            ->latest()
            ->get();

        // Usuários disponíveis para transferência (exceto o próprio)
        $users = User::where('id', '!=', $user->id)->get();

        return view('dashboard', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'users' => $users,
        ]);
    }

    // =========================================================================
    // DEPÓSITO
    // =========================================================================
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $wallet = auth()->user()->wallet;

        DB::transaction(function () use ($wallet, $request) {
            // Atualiza saldo
            $wallet->balance += $request->amount;
            $wallet->save();

            // Registra transação
            Transaction::create([
                'from_user_id' => null,
                'to_user_id' => auth()->id(),
                'amount' => $request->amount,
                'type' => 'deposit',
                'status' => 'completed',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Depósito realizado!',
            'saldo' => $wallet->balance,
            'transaction' => Transaction::latest()->first(),
        ]);
    }

    // =========================================================================
    // TRANSFERÊNCIA
    // =========================================================================
    public function transfer(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Não pode transferir para si mesmo
        if ((int) $request->to_user_id === auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => 'Você não pode transferir dinheiro para você mesmo.',
            ]);
        }

        $from = auth()->user()->wallet;
        $to = Wallet::where('user_id', $request->to_user_id)->first();

        if (!$to) {
            return response()->json([
                'success' => false,
                'error' => 'O usuário de destino não possui carteira ativa.',
            ]);
        }

        if ($from->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'error' => 'Saldo insuficiente para realizar a transferência.',
            ]);
        }

        DB::transaction(function () use ($from, $to, $request) {
            // Debita remetente
            $from->balance -= $request->amount;
            $from->save();

            // Credita destinatário
            $to->balance += $request->amount;
            $to->save();

            Transaction::create([
                'from_user_id' => auth()->id(),
                'to_user_id' => $request->to_user_id,
                'amount' => $request->amount,
                'type' => 'transfer',
                'status' => 'completed',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Transferência realizada!',
            'saldo' => $from->balance,
            'transaction' => Transaction::latest()->first(),
        ]);
    }

    // =========================================================================
    // REVERSÃO
    // =========================================================================
    public function reverse($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === 'reversed') {
            return response()->json([
                'success' => false,
                'error' => 'Esta transação já foi revertida.',
            ]);
        }

        DB::transaction(function () use ($transaction) {

            if ($transaction->type === 'deposit') {
                $wallet = Wallet::where('user_id', $transaction->to_user_id)->first();
                $wallet->balance -= $transaction->amount;
                $wallet->save();
            }

            if ($transaction->type === 'transfer') {
                $from = Wallet::where('user_id', $transaction->from_user_id)->first();
                $to = Wallet::where('user_id', $transaction->to_user_id)->first();

                $to->balance -= $transaction->amount;
                $to->save();

                $from->balance += $transaction->amount;
                $from->save();
            }

            $transaction->update(['status' => 'reversed']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Transação revertida!',
            'saldo' => auth()->user()->wallet->balance,
        ]);
    }

    // =========================================================================
    // PÁGINA DE EXTRATO
    // =========================================================================
    public function transactionsPage()
    {
        $user = auth()->user();

        $transactions = Transaction::with(['fromUser', 'toUser'])
            ->where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })
            ->latest()
            ->get();

        return view('transactions.index', [
            'transactions' => $transactions,
        ]);
    }

    // =========================================================================
    // FILTRO AJAX DO EXTRATO
    // =========================================================================
    public function filterTransactions(Request $request)
    {
        $userId = auth()->id();

        $query = Transaction::with(['fromUser', 'toUser'])
            ->where(function ($q) use ($userId) {
                $q->where('from_user_id', $userId)
                    ->orWhere('to_user_id', $userId);
            });

        // Filtro por período
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', now()->toDateString());
                    break;

                case 'week':
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]);
                    break;

                case 'month':
                    $query->whereBetween('created_at', [
                        now()->startOfMonth(),
                        now()->endOfMonth(),
                    ]);
                    break;

                case 'custom':
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $query->whereBetween('created_at', [
                            $request->start_date . ' 00:00:00',
                            $request->end_date . ' 23:59:59',
                        ]);
                    }
                    break;
            }
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->orderBy('created_at', 'desc')->get();

        return response()->json($results);
    }
}
