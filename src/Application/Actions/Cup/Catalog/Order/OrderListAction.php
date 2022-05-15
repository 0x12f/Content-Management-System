<?php declare(strict_types=1);

namespace App\Application\Actions\Cup\Catalog\Order;

use App\Application\Actions\Cup\Catalog\CatalogAction;
use const App\Application\Actions\Cup\Catalog\INVOICE_TEMPLATE;

class OrderListAction extends CatalogAction
{
    protected function action(): \Slim\Psr7\Response
    {
        return $this->respondWithTemplate('cup/catalog/order/index.twig', [
            'order_list' => $this->catalogOrderService->read([
                'order' => ['date' => 'desc'],
            ]),
            'status_list' => $this->catalogOrderStatusService->read(),
            'invoice' => INVOICE_TEMPLATE,
        ]);
    }
}
