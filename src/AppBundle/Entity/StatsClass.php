<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The most generic type of item.
 *
 * @see http://schema.org/Thing Documentation on Schema.org
 *
 * @author Goutis Dimitris
 *
 * @ORM\Entity
 * @UniqueEntity("url")
 * @ApiResource(iri="http://schema.org/Thing")
 */
class StatsClass
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     * @Assert\Type(type="string")
     * @Assert\NotNull
     */
    private $title;

    /**
     * @var string A description of the item
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/description")
     */
    private $description;

    /**
     * @var string URL of the item
     *
     * @ORM\Column(nullable=true, unique=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/url")
     */
    private $url;

    /**
     * @var StatsVoc
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\StatsVoc")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    private $vocabulary;

    /**
     * Sets id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets url.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets vocabulary.
     *
     * @param StatsVoc $vocabulary
     *
     * @return $this
     */
    public function setVocabulary(StatsVoc $vocabulary)
    {
        $this->vocabulary = $vocabulary;

        return $this;
    }

    /**
     * Gets vocabulary.
     *
     * @return StatsVoc
     */
    public function getVocabulary()
    {
        return $this->vocabulary;
    }
}
