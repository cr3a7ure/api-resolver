<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A software application.
 *
 * @see http://schema.org/SoftwareApplication Documentation on Schema.org
 *
 * @author Goutis Dimitris
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/SoftwareApplication",
 *             type="http://schema.org/SoftwareApplication",
 *             collectionOperations={
 *             "test_upload_graph"={"route_name"="api_test_upload_action"}},
 *             attributes={
 *                 "filters"={"soft_apps.search"},
 *                 "denormalization_context"={
 *                     "groups"={"write_graph"},
 *                     "api_allow_update"={true}}
 *              }
 *         )
 */
class SoftwareApplicationTesting
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
     * @var string The name of the item
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/name")
     */
    private $name;

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
     * @ORM\Column(nullable=true)
     * @Assert\Url
     * @ApiProperty(iri="http://schema.org/url")
     */
    private $url;

    /**
     * @var string An image of the item. This can be a \[\[URL\]\] or a fully described \[\[ImageObject\]\]
     *
     * @ORM\Column(nullable=true)
     * @Assert\Url
     * @ApiProperty(iri="http://schema.org/image")
     */
    private $image;

    /**
     * @var string Type of software application, e.g. 'Game, Multimedia'
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/applicationCategory")
     */
    private $applicationCategory;

    /**
     * @var string Subcategory of the application, e.g. 'Arcade Game'
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/applicationSubCategory")
     */
    private $applicationSubCategory;

    /**
     * @var string Description of what changed in this version
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/releaseNotes")
     */
    private $releaseNotes;

    /**
     * @var string Version of the software instance
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/softwareVersion")
     */
    private $softwareVersion;

    /**
     * @var string
     *
     * @ORM\Column
     * @Assert\Type(type="string")
     * @Assert\NotNull
     */
    private $licence;

    /**
     * @var string A review of the item
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/review")
     */
    private $review;

    /**
     * @var string Keywords or tags used to describe this content. Multiple entries in a keywords list are typically delimited by commas
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/keywords")
     */
    private $keywords;

    /**
     * @var string The textual content of this CreativeWork
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="json_array")
     * @Groups({"write"})
     * @ApiProperty(iri="http://schema.org/text")
     */
    private $text;

    /**
     * @var bool A flag to signal that the publication is accessible for free
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\Type(type="boolean")
     * @Groups({"write"})
     * @ApiProperty(iri="http://schema.org/isAccessibleForFree")
     */
    private $isAccessibleForFree;

    /**
     * @var string The service provider, service operator, or service performer; the goods producer. Another party (a seller) may offer those services or goods on behalf of the provider. A provider may also serve as the seller
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @Groups({"write"})
     * @ApiProperty(iri="http://schema.org/provider")
     */
    private $provider;

    /**
     * @var \DateTime Date of first broadcast/publication
     *
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     * @Groups({"write"})
     * @ApiProperty(iri="http://schema.org/datePublished")
     */
    private $datePublished;

    /**
     * @var \DateTime The date on which the CreativeWork was most recently modified or when the item's entry was modified within a DataFeed
     *
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     * @ApiProperty(iri="http://schema.org/dateModified")
     */
    private $dateModified;

    /**
     * @var string The overall rating, based on a collection of reviews or ratings, of the item
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @Groups({"write"})
     * @ApiProperty(iri="http://schema.org/aggregateRating")
     */
    private $aggregateRating;

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
     * Sets name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Sets image.
     *
     * @param string $image
     *
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Gets image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets applicationCategory.
     *
     * @param string $applicationCategory
     *
     * @return $this
     */
    public function setApplicationCategory($applicationCategory)
    {
        $this->applicationCategory = $applicationCategory;

        return $this;
    }

    /**
     * Gets applicationCategory.
     *
     * @return string
     */
    public function getApplicationCategory()
    {
        return $this->applicationCategory;
    }

    /**
     * Sets applicationSubCategory.
     *
     * @param string $applicationSubCategory
     *
     * @return $this
     */
    public function setApplicationSubCategory($applicationSubCategory)
    {
        $this->applicationSubCategory = $applicationSubCategory;

        return $this;
    }

    /**
     * Gets applicationSubCategory.
     *
     * @return string
     */
    public function getApplicationSubCategory()
    {
        return $this->applicationSubCategory;
    }

    /**
     * Sets releaseNotes.
     *
     * @param string $releaseNotes
     *
     * @return $this
     */
    public function setReleaseNotes($releaseNotes)
    {
        $this->releaseNotes = $releaseNotes;

        return $this;
    }

    /**
     * Gets releaseNotes.
     *
     * @return string
     */
    public function getReleaseNotes()
    {
        return $this->releaseNotes;
    }

    /**
     * Sets softwareVersion.
     *
     * @param string $softwareVersion
     *
     * @return $this
     */
    public function setSoftwareVersion($softwareVersion)
    {
        $this->softwareVersion = $softwareVersion;

        return $this;
    }

    /**
     * Gets softwareVersion.
     *
     * @return string
     */
    public function getSoftwareVersion()
    {
        return $this->softwareVersion;
    }

    /**
     * Sets licence.
     *
     * @param string $licence
     *
     * @return $this
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Gets licence.
     *
     * @return string
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Sets review.
     *
     * @param string $review
     *
     * @return $this
     */
    public function setReview($review)
    {
        $this->review = $review;

        return $this;
    }

    /**
     * Gets review.
     *
     * @return string
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Sets keywords.
     *
     * @param string $keywords
     *
     * @return $this
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Gets keywords.
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Sets text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Gets text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets isAccessibleForFree.
     *
     * @param bool $isAccessibleForFree
     *
     * @return $this
     */
    public function setIsAccessibleForFree($isAccessibleForFree)
    {
        $this->isAccessibleForFree = $isAccessibleForFree;

        return $this;
    }

    /**
     * Gets isAccessibleForFree.
     *
     * @return bool
     */
    public function getIsAccessibleForFree()
    {
        return $this->isAccessibleForFree;
    }

    /**
     * Sets provider.
     *
     * @param string $provider
     *
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Gets provider.
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Sets datePublished.
     *
     * @param \DateTime $datePublished
     *
     * @return $this
     */
    public function setDatePublished(\DateTime $datePublished = null)
    {
        $this->datePublished = $datePublished;

        return $this;
    }

    /**
     * Gets datePublished.
     *
     * @return \DateTime
     */
    public function getDatePublished()
    {
        return $this->datePublished;
    }

    /**
     * Sets dateModified.
     *
     * @param \DateTime $dateModified
     *
     * @return $this
     */
    public function setDateModified(\DateTime $dateModified = null)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Gets dateModified.
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Sets aggregateRating.
     *
     * @param string $aggregateRating
     *
     * @return $this
     */
    public function setAggregateRating($aggregateRating)
    {
        $this->aggregateRating = $aggregateRating;

        return $this;
    }

    /**
     * Gets aggregateRating.
     *
     * @return string
     */
    public function getAggregateRating()
    {
        return $this->aggregateRating;
    }
}
