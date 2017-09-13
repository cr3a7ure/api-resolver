<?php

// src/AppBundle/Action/SoftwareApplicationRetrieveAction.php

namespace AppBundle\Action;

use Symfony\Component\Serializer\Annotation\Groups;
use AppBundle\Entity\ApiRef;
use AppBundle\Entity\SoftwareApplication;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Unirest;
use Easyrdf;
use ML\JsonLD\JsonLD as JsonLD;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SoftwareApplicationRetrieveAction
{

    protected $requestStack;
    protected $doctrine;

    public function __construct(RequestStack $requestStack,ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    protected function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }


    protected function graphExists(string $graphURI,string $sparqlEndpoint) {
      $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
        $query = 'ASK WHERE { GRAPH <'.$graphURI.'> { ?s ?p ?o } }';
        $result = $sparqlClient->query($query);
        dump('Graph exists?: '.$result->isTrue());
        return $result->isTrue();
    }

    protected function retrieveGraph(string $graphURI,string $sparqlEndpoint) {
      $headers = array('Accept' => 'application/ld+json');
      $sparqlQuery = 'DESCRIBE ?s ?p ?o FROM <'.$graphURI.'> WHERE {  { ?s ?p ?o } }';
      $data = array('query' => $sparqlQuery);
      $body = Unirest\Request\Body::form($data);
      $q = Unirest\Request::post($sparqlEndpoint, $headers, $body);

      // $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
      // $result = $sparqlClient->query($sparqlQuery);
      return $q->raw_body;
    }

/**
 * @Route(
 *     name="api_graph_retrieve_action",
 *     path="/software_applications/{id}",
 *     defaults={"_api_resource_class"=SoftwareApplication::class, "_api_item_operation_name"="retrieve_graph"}
 * )
 * @Method("GET")
 */
    public function __invoke($data)
    {
      $graphUri = $data->getUrl();
      // dump($this->getRequest());
      $graph = '';// new \EasyRdf_Graph();

      if ($this->graphExists($graphUri,'http://vps454845.ovh.net:8090/thesis/query')) {
        $graph = $this->retrieveGraph($graphUri,'http://vps454845.ovh.net:8090/thesis/query');
        // $response = $graph->serialise('jsonld');
        dump($graph);
        $data->setText($graph);
        // $data->setText($graph->serialise('jsonld'));
      } else {
        $data->setText('Not Found!');
      }
      dump($data);
      return $data;
    }
}
// curl http://vps362714.ovh.net:8090/thesis/query -X POST --data 'query=%0ADESCRIBE+%3Fs+%3Fp+%3Fo+%0AFROM+%3Chttp%3A%2F%2Fvps362714.ovh.net%3A8090%2Fthesis%2Fdata%2Fapiv17%3E+%0AWHERE+++%7B+%3Fs+%3Fp+%3Fo+%7D+' -H 'Accept: application/ld+json'