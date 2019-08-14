<?php

namespace Entity\Publication;

use AEngine\Entity\Model;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="publication_category")
 */
class Category extends Model
{
    /**
     * @var UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    public $uuid;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    public $address;

    /**
     * @ORM\Column(type="string")
     */
    public $title;

    /**
     * @ORM\Column(type="string")
     */
    public $description;

    /**
     * @ORM\Column(type="string", length=36)
     */
    public $parent;

    /**
     * @ORM\Column(type="integer", options={"default": "10"})
     */
    public $pagination;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    public $sort = [
        'by' => \Reference\Publication\Category::ORDER_BY_DATE,
        'direction' => \Reference\Publication\Category::ORDER_DIRECTION_ASC,
    ];

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    public $meta = [
        'title' => '',
        'description' => '',
        'keywords' => '',
    ];

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    public $template = [
        'list' => '',
        'short' => '',
        'full' => '',
    ];
}
