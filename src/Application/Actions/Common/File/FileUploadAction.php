<?php declare(strict_types=1);

namespace App\Application\Actions\Common\File;

use App\Domain\Service\File\FileService;

class FileUploadAction extends FileAction
{
    protected function action(): \Slim\Http\Response
    {
        $path_only = $this->request->getParam('path_only', false);

        $models = [];

        if ($this->getParameter('file_is_enabled', 'no') === 'yes') {
            $fileService = FileService::getWithContainer($this->container);

            foreach ($this->request->getUploadedFiles() as $field => $files) {
                if (!is_array($files)) {
                    $files = [$files]; // allow upload one file
                }

                $uuids = [];
                foreach ($files as $el) {
                    if (!$el->getError()) {
                        $model = $fileService->createFromPath($el->file, $el->getClientFilename());

                        // is image
                        if (str_starts_with($model->getType(), 'image/')) {
                            $uuids[] = $model->getUuid();
                        }

                        $models[$field][] = $model;
                    }
                }

                if ($uuids) {
                    // add task convert
                    $task = new \App\Domain\Tasks\ConvertImageTask($this->container);
                    $task->execute(['uuid' => $uuids]);

                    // run worker
                    \App\Domain\AbstractTask::worker();
                }
            }

            $this->entityManager->flush();
        }

        if ($models && $path_only) {
            $file = array_shift($models)[0] ?? false;

            if ($file) {
                // @var \App\Domain\Entities\File $file
                return $this->respondWithJson(['link' => $file->getPublicPath()]);
            }
        }

        return $this->respondWithJson($models);
    }
}
