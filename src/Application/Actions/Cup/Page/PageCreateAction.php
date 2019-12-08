<?php

namespace App\Application\Actions\Cup\Page;

use Exception;

class PageCreateAction extends PageAction
{
    protected function action(): \Slim\Http\Response
    {
        if ($this->request->isPost()) {
            $data = [
                'title' => $this->request->getParam('title'),
                'address' => $this->request->getParam('address'),
                'date' => $this->request->getParam('date'),
                'content' => $this->request->getParam('content'),
                'type' => $this->request->getParam('type'),
                'meta' => $this->request->getParam('meta'),
                'template' => $this->request->getParam('template'),
            ];

            $check = \App\Domain\Filters\Page::check($data);

            if ($check === true) {
                $model = new \App\Domain\Entities\Page($data);
                $this->entityManager->persist($model);
                $this->handlerFileUpload(\App\Domain\Types\FileItemType::ITEM_PAGE, $model->uuid);
                $this->entityManager->flush();

                switch (true) {
                    case $this->request->getParam('save', 'exit') === 'exit':
                        return $this->response->withAddedHeader('Location', '/cup/page')->withStatus(301);
                    default:
                        return $this->response->withAddedHeader('Location', '/cup/page/' . $model->uuid . '/edit')->withStatus(301);
                }
            } else {
                $this->addErrorFromCheck($check);
            }
        }

        return $this->respondRender('cup/page/form.twig');
    }
}
