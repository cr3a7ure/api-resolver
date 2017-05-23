<?php

// src/AppBundle/Entity/ApiRef.php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;


/**
 * A book.
 *
 * @ApiResource(
 *     iri="/apiRef",
 *     type="test",
 *     collectionOperations={
 *     "special"={"route_name"="api_ref_action", "hydra_context"={"@type"="schema:Action","@id"="searchAPi"}},
 *     "get"={"method"="GET", "hydra_context"={"@type"="schema:TestAction"}}
 * }
 * )
 *
 */
class ApiRef
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