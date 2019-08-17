<?php

namespace Domain\Filters\Catalog;

use AEngine\Validator\Filter;
use AEngine\Validator\Traits\FilterRules;
use \Domain\Filters\Traits\CommonFilterRules;
use \Domain\Filters\Traits\CatalogFilterRules;

class Category extends Filter
{
    use FilterRules;
    use CommonFilterRules;
    use CatalogFilterRules;

    /**
     * Check page model data
     *
     * @param array $data
     *
     * @return array|bool
     */
    public static function check(array &$data)
    {
        $filter = new self($data);

        $filter
            ->addGlobalRule($filter->leadTrim())
            ->attr('parent')
                ->addRule($filter->leadStr())
                ->addRule($filter->checkStrlenBetween(0, 36))
            ->attr('title')
                ->addRule($filter->leadStr())
                ->addRule($filter->checkStrlenBetween(0, 255))
            ->attr('description')
                ->addRule($filter->leadStr())
                ->addRule($filter->checkStrlenBetween(0, 10000))
            ->attr('address')
                ->addRule($filter->ValidAddress())
                ->addRule($filter->UniqueCategoryAddress())
                ->addRule($filter->checkStrlenBetween(0, 255))
            ->attr('field1')
                ->addRule($filter->leadStr())
            ->attr('field2')
                ->addRule($filter->leadStr())
            ->attr('field3')
                ->addRule($filter->leadStr())
            ->attr('product')
                ->addRule($filter->ValidProductFieldNames())
            ->attr('order')
                ->addRule($filter->leadInteger())
            ->attr('meta')
                ->addRule($filter->ValidMeta())
            ->attr('template')
                ->addRule($filter->leadStr())
                ->addRule($filter->checkStrlenBetween(0, 50))
            ->attr('external_id')
                ->addRule($filter->leadStr());

        return $filter->run();
    }
}
