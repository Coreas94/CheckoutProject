<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;

class CheckoutController extends Controller
{
    protected $orderRepository;
    protected $paypal;

    public function __construct(OrderRepository $orderRepository, PayPalClient $paypal)
    {
        $this->orderRepository = $orderRepository;
        $this->paypal = $paypal;
    }

    // Proceso de checkout
    public function store(CheckoutRequest $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        // Crear pedido
        $order = $this->orderRepository->createOrder($user->id, $request->input('amount'));

        // Configurar PayPal
        $this->configurePayPal();

        // Crear pedido en PayPal
        $response = $this->createPayPalOrder($order);

        if ($response['status'] === 'CREATED') {
            $this->orderRepository->updatePaymentStatus($order, 'pending', $response['id']);

            return response()->json([
                'approval_url' => $this->getApprovalLink($response),
                'order_id' => $order->id
            ]);
        } else {
            $this->orderRepository->updatePaymentStatus($order, 'failed');
            return response()->json(['message' => 'Fallo al crear el pedido en PayPal'], 500);
        }
    }

    // Maneja el éxito del pago en PayPal
    public function paymentSuccess(Request $request)
    {
        $this->configurePayPal();
        $order = Order::where('payment_id', $request->token)->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Pago ya completado',
                'order' => $order,
            ]);
        }

        // Capturar el pago
        $response = $this->paypal->capturePaymentOrder($request->token);

        if (isset($response['error'])) {
            return $this->handlePayPalError($response);
        }

        if ($response['status'] === 'COMPLETED') {
            $this->orderRepository->updatePaymentStatus($order, 'paid');
            return response()->json([
                'message' => 'Pago completado',
                'order' => $order,
            ]);
        }

        return response()->json(['message' => 'Pago fallido'], 500);
    }

    // Maneja la cancelación del pago en PayPal
    public function paymentCancel()
    {
        return response()->json(['message' => 'Pago cancelado'], 200);
    }

    // Configurar el cliente de PayPal
    private function configurePayPal(): void
    {
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->setAccessToken($this->paypal->getAccessToken());
    }

    // Crear el pedido en PayPal
    private function createPayPalOrder(Order $order): array
    {
        $orderDetails = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => config('paypal.currency'),
                        "value" => $order->total_amount,
                    ],
                    "description" => "Pago por el pedido #" . $order->id,
                ]
            ],
            "application_context" => [
                "cancel_url" => route('payment.cancel'),
                "return_url" => route('payment.success'),
            ]
        ];

        return $this->paypal->createOrder($orderDetails);
    }

    // Obtener el enlace de aprobación de PayPal
    private function getApprovalLink(array $response): string
    {
        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }

        throw new \Exception('No se encontró el enlace de aprobación en la respuesta de PayPal');
    }

    // Manejar el error en la respuesta de PayPal
    private function handlePayPalError(array $response)
    {
        $issue = $response['error']['details'][0]['issue'] ?? 'Problema desconocido';
        $description = $response['error']['details'][0]['description'] ?? 'No hay descripción disponible';

        return response()->json([
            'message' => 'Fallo en el pago',
            'error' => [
                'issue' => $issue,
                'description' => $description,
            ]
        ], 400);
    }
}
