<?php

// src/AppBundle/Entity/ApiDiscovery.php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;


/**
 * A book.
 *
 * @ApiResource(
 *     iri="/Apidiscovery",
 *     type="test",
 *     collectionOperations={
 *     "match"={"route_name"="api_match_action", "hydra_context"={"@type"="schema:Action","@id"="searchAPi"}},
 *     "match_test"={"route_name"="api_test_match_action", "hydra_context"={"@type"="schema:Action","@id"="searchAPi"}}
 * }
 * )
 *
 */
class ApiDiscovery
{

    /**
     *@ApiProperty(
     *     iri="http://schema.org/name"
     * )
     *  @var array The description of this api.
     */
    private $articleBody;

        /**
     * Set description
     *
     * @param array $articleBody
     *
     * @return array
     */
    public function setArticleBody($articleBody)
    {
        $this->articleBody = $articleBody;

        return $this;
    }

    /**
     * Get articleBody
     *
     * @return array
     */
    public function getArticleBody()
    {
        return $this->articleBody;
    }
}