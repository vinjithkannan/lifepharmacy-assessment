<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->listOrders($request->user());

        return (new OrderResource($orders))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $order = $this->orderService->createOrder($request->user(), $request);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function show(Request $request)
    {
        $orders = $this->orderService->listOrders($request->user());

        return (new OrderResource($orders))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
