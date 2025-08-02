<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Listar pagamentos do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->payments()->with(['subscription.plan']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'last_page' => $payments->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar pagamento específico
     */
    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        $payment->load(['subscription.plan']);

        return response()->json([
            'data' => $payment
        ]);
    }

    /**
     * Criar novo pagamento
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $subscription = Subscription::findOrFail($request->subscription_id);

        // Verificar se o pagamento pertence ao usuário
        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'status' => 'pending',
            ]);

            // Aqui você integraria com o gateway de pagamento
            // Por enquanto, vamos simular um pagamento bem-sucedido
            $payment->markAsCompleted();

            DB::commit();

            return response()->json([
                'message' => 'Pagamento criado com sucesso',
                'data' => $payment->load('subscription.plan')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao processar pagamento'
            ], 500);
        }
    }

    /**
     * Processar pagamento (webhook)
     */
    public function processWebhook(Request $request): JsonResponse
    {
        // Aqui você processaria o webhook do gateway de pagamento
        // Por exemplo, Mercado Pago, PagSeguro, etc.

        $transactionId = $request->get('transaction_id');
        $status = $request->get('status');

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Pagamento não encontrado'
            ], 404);
        }

        switch ($status) {
            case 'approved':
                $payment->markAsCompleted();
                break;
            case 'rejected':
                $payment->markAsFailed();
                break;
            case 'refunded':
                $payment->markAsRefunded();
                break;
        }

        return response()->json([
            'message' => 'Webhook processado com sucesso'
        ]);
    }

    /**
     * Reembolsar pagamento
     */
    public function refund(Payment $payment): JsonResponse
    {
        $this->authorize('update', $payment);

        if (!$payment->isCompleted()) {
            return response()->json([
                'message' => 'Apenas pagamentos completados podem ser reembolsados'
            ], 422);
        }

        if ($payment->isRefunded()) {
            return response()->json([
                'message' => 'Este pagamento já foi reembolsado'
            ], 422);
        }

        // Aqui você integraria com o gateway de pagamento para reembolso
        $payment->markAsRefunded();

        return response()->json([
            'message' => 'Reembolso processado com sucesso'
        ]);
    }

    /**
     * Estatísticas de pagamentos (Admin)
     */
    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $stats = [
            'total_payments' => Payment::count(),
            'completed_payments' => Payment::completed()->count(),
            'pending_payments' => Payment::pending()->count(),
            'failed_payments' => Payment::failed()->count(),
            'refunded_payments' => Payment::refunded()->count(),
            'total_revenue' => Payment::completed()->sum('amount'),
            'revenue_this_month' => Payment::completed()
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount'),
            'revenue_by_method' => Payment::completed()
                ->selectRaw('payment_method, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
}
