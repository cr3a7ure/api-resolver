<?php

// src/AppBundle/Entity/APIReference.php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * A book.
 *
 * @ApiResource(
 *
 * )
 *
 * @ORM\Entity
 */
class APIReference
{
    /**
     * @var int The id of saved API.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string The description of this api.
     *
     * @ORM\Column(type="text")
     */
    private $articleBody;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

        /**
     * Set description
     *
     * @param string $articleBody
     *
     * @return Book
     */
    public function setArticleBody($articleBody)
    {
        $this->articleBody = $articleBody;

        return $this;
    }

    /**
     * Get articleBody
     *
     * @return string
     */
    public function getArticleBody()
    {
        return $this->articleBody;
    }
}