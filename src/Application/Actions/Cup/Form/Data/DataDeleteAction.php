<?php

namespace App\Application\Actions\Cup\Form\Data;

use App\Application\Actions\Cup\Form\FormAction;

class DataDeleteAction extends FormAction
{
    protected function action(): \Slim\Http\Response
    {
        if (
            $this->resolveArg('uuid') && \Ramsey\Uuid\Uuid::isValid($this->resolveArg('uuid')) &&
            $this->resolveArg('data') && \Ramsey\Uuid\Uuid::isValid($this->resolveArg('data'))
        ) {
            /** @var \App\Domain\Entities\Form\Data $item */
            $item = $this->dataRepository->findOneBy([
                'form_uuid' => $this->resolveArg('uuid'),
                'uuid' => $this->resolveArg('data'),
            ]);

            if (!$item->isEmpty()) {
                /** @var \App\Domain\Entities\File $file */

                $files = $this->fileRepository->findOneBy([
                    'item' => \App\Domain\Types\FileItemType::ITEM_FORM_DATA,
                    'item_uuid' => $this->resolveArg('data'),
                ]);

                if ($files) {
                    foreach ($files as $file) {
                        $file->unlink();
                        $this->entityManager->remove($file);
                    }
                }

                $this->entityManager->remove($item);
                $this->entityManager->flush();
            }
        }

        return $this->response->withAddedHeader('Location', '/cup/form/' . $this->resolveArg('uuid') . '/view')->withStatus(301);
    }
}
