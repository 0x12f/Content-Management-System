<?php

namespace App\Domain\Tasks\Generate;

use App\Domain\Tasks\Task;
use Bukashk0zzz\YmlGenerator\Generator;
use Bukashk0zzz\YmlGenerator\Model\Category;
use Bukashk0zzz\YmlGenerator\Model\Currency;
use Bukashk0zzz\YmlGenerator\Model\Delivery;
use Bukashk0zzz\YmlGenerator\Model\Offer\OfferSimple;
use Bukashk0zzz\YmlGenerator\Model\ShopInfo;
use Bukashk0zzz\YmlGenerator\Settings;
use samdark\sitemap\Sitemap;

class SiteMapTask extends Task
{
    public function execute(array $params = []): \App\Domain\Entities\Task
    {
        $default = [
            // nothing
        ];
        $params = array_merge($default, $params);

        return parent::execute($params);
    }

    protected function action(array $args = [])
    {
        /**
         * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository $pageRepository
         * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository $publicationRepository
         * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository $categoryRepository
         * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository $productRepository
         */
        $pageRepository = $this->entityManager->getRepository(\App\Domain\Entities\Page::class);
        $publicationRepository = $this->entityManager->getRepository(\App\Domain\Entities\Page::class);
        $categoryRepository = $this->entityManager->getRepository(\App\Domain\Entities\Catalog\Category::class);
        $productRepository = $this->entityManager->getRepository(\App\Domain\Entities\Catalog\Product::class);
        $data = [
            'page' => collect($pageRepository->findAll()),
            'publication' => collect($publicationRepository->findAll()),
            'category' => collect($categoryRepository->findBy(['status' => \App\Domain\Types\Catalog\CategoryStatusType::STATUS_WORK])),
            'product' => collect($productRepository->findBy(['status' => \App\Domain\Types\Catalog\ProductStatusType::STATUS_WORK])),
        ];

        $url = $this->getParameter('common_homepage', 'http://site.0x12f.com');

        // create sitemap
        $sitemap = new Sitemap(VAR_DIR . '/xml/sitemap.xml');

        // main page
        $sitemap->addItem($url, time(), Sitemap::WEEKLY, 0.5);

        // other pages
        foreach ($data['page'] as $model) {
            /** @var \App\Domain\Entities\Page $model */
            $sitemap->addItem($url . $model->address, time(), Sitemap::WEEKLY, 0.3);
        }

        // publications
        foreach ($data['publication'] as $model) {
            /** @var \App\Domain\Entities\Publication $model */
            $sitemap->addItem($url . $model->address, time(), Sitemap::WEEKLY, 0.3);
        }

        // catalog category
        foreach ($data['category'] as $model) {
            /** @var \App\Domain\Entities\Catalog\Category $model */
            $sitemap->addItem($url . 'catalog/' . $model->address, time(), Sitemap::WEEKLY, 0.5);
        }

        // catalog products
        foreach ($data['product'] as $model) {
            /** @var \App\Domain\Entities\Catalog\Product $model */
            $category = $data['category']->firstWhere('uuid', $model->category);

            $urlCategory = $url . 'catalog/';
            if ($category) {
                $urlCategory .= $category->address;
            }
            $urlCategory .= '/' . $model->address;

            $sitemap->addItem($urlCategory, time(), Sitemap::WEEKLY, 0.7);
        }

        $sitemap->write();

        $this->setStatusDone();
    }
}
