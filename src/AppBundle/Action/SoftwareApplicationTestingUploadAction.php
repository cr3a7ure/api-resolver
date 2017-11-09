<?php

// src/AppBundle/Action/SoftwareApplicationUploadAction.php

namespace AppBundle\Action;

use Symfony\Component\Serializer\Annotation\Groups;
use AppBundle\Entity\SoftwareApplicationTesting;
use AppBundle\Entity\StatsVoc;
use AppBundle\Entity\StatsClass;
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

class SoftwareApplicationTestingUploadAction
{

    protected $requestStack;
    protected $doctrine;
    protected $sparqlHost;
    protected $sparqlDataset;
    protected $sparqlURL;

    public function __construct(RequestStack $requestStack,ManagerRegistry $doctrine, $sparqlHost, $sparqlDataset)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->sparqlHost = $sparqlHost;
        $this->sparqlDataset = $sparqlDataset;
        $this->sparqlURL = $sparqlHost . $sparqlDataset;
    }

    protected function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    protected function validateData(string $data)
    {
        return $data;
    }
    protected function retrieveData(string $data)
    {
        return $data;
    }
/**
 * [retrieveClass description]
 * @param  \EasyRdf_Graph $graph [description]
 * @return classes[array]                [description]
 */
    protected function retrieveClass(\EasyRdf_Graph $graph)
    {
      $nodes = $graph->resources();
      $classes = array();
      foreach( $nodes as $value ) {
        if (!($value->isBnode())) {
          $classes[$value->getUri()] = $value;
        } else {
          //We have a bnode Class?!
        }
      }
      dump("\nContained Named Resources: ");
      dump($classes);
      return $classes;
    }

/**
 * [getDefaultPrefix ]
 * @return [string] [SPARQL syntax PREFIXes]
 */
    protected function getDefaultPrefix()
    {
      $prefix = '
      PREFIX schema: <http://schema.org/>
      PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
      PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
      PREFIX hydra: <http://www.w3.org/ns/hydra/core#>';
      return $prefix;
    }

/**
 * [getPrefix return custom prefixes]
 * @return [string] [SPARQL syntax PREFIXes]
 */
    protected function getPrefix()
    {
      $prefix = '';
      return $prefix;
    }

    protected function graphExists(string $graphURI,string $sparqlEndpoint) {
      $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
        $query = 'ASK WHERE { GRAPH <'.$graphURI.'> { ?s ?p ?o } }';
        $result = $sparqlClient->listNamedGraphs();
        dump($result);
        $result = $sparqlClient->countTriples('{ ?s ?p ?o }');
        dump($result);
        $result = $sparqlClient->query($query);
        dump('Graph exists?: '.$result->isTrue());
        return $result->isTrue();
    }

    protected function dropGraph(string $graphURI,string $sparqlEndpoint) {
      $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
        $query = 'DROP GRAPH <'.$graphURI.'>';
        $result = $sparqlClient->update($query);
        dump('Graph deleted?: '.$result->isSuccessful());
        return $result->isSuccessful();
    }

    protected function getVocabulary(string $URL) {
      $urlArray = explode('/',$URL);
      $urlArray = array_filter($urlArray);
      array_pop($urlArray);
      $urlString = '/'.implode('/',$urlArray);
    }

    protected function addVoc(array $vocs) {
      $em = $this->doctrine->getRepository('AppBundle\Entity\StatsVoc');
      $write = $this->doctrine->getManager();
      foreach ($vocs as $key => $value) {
        $voc = $em->findOneByUrl($value);
        if (!$voc) {
          $temp = new StatsVoc();
          $temp->setUrl($value);
          $temp->setTitle(parse_url($value, PHP_URL_HOST));
          $write->persist($temp);
          $write->flush();
        }
      }
      // $attr = $em->findAll();
      return true;
    }
    protected function addClass(array $classes) {
      $em = $this->doctrine->getRepository('AppBundle\Entity\StatsClass');
      $write = $this->doctrine->getManager();
      foreach ($classes as $key => $value) {
        $voc = $em->findOneByUrl($value);
        if (!$voc) {
          $temp = new StatsClass();
          $temp->setUrl($value);
          $temp->setTitle(substr($value, strrpos($url, '/') + 1));
          $write->persist($temp);
          $write->flush();
        }
      }
      return true;
    }

    protected function parseGraph(string $dataRaw,SoftwareApplicationTesting $dbEntry)
    {
      $baseString = '@base';
      $context = '@context';
      $id = '@id';
      $dataJson = json_decode(($dataRaw));
      $uploadedGraph = $dataJson->text;
      $dbEntry->setName($dataJson->name);
      $dbEntry->setDescription($dataJson->description);
      $dbEntry->setUrl($dataJson->url);
      $dbEntry->setImage($dataJson->image);
      $dbEntry->setReleaseNotes($dataJson->releaseNotes);
      $dbEntry->setSoftwareVersion($dataJson->softwareVersion);
      $dbEntry->setLicence($dataJson->licence);
      $dbEntry->setReview($dataJson->review);
      $dbEntry->setKeywords($dataJson->keywords);
      $dbEntry->setIsAccessibleForFree($dataJson->isAccessibleForFree);
      $dbEntry->setProvider($dataJson->provider);
      dump('WRA');
      dump($dataJson->datePublished);
      dump(\DateTime::createFromFormat(\DateTime::W3C,$dataJson->datePublished));
      // $dbEntry->setDatePublished(\DateTime::createFromFormat('Y-m-d\TH:iP',$dataJson->datePublished));
      $dbEntry->setDatePublished(null);
      // $dbEntry->setDateModified(\DateTime::createFromFormat(\DateTime::W3C,$dataJson->dateModified));
      $dbEntry->setDateModified(null);
      $dbEntry->setAggregateRating($dataJson->aggregateRating);
      dump($uploadedGraph);
      $baseUri = $uploadedGraph->$context->$baseString . $uploadedGraph->$id;
      dump($baseUri);
      $classes = "hydra:supportedClass";
      $classArray = array();
      $vocsArray = array();
      foreach ($uploadedGraph->$classes as $keyCl => $value) {
        $classTypes = "@type";
        $hydraClass = "hydra:Class";
        $properties = "hydra:supportedProperty";
        $description = 'hydra:description';
        dump($value->$classTypes);
        if (is_array($value->$classTypes)) {
          foreach ($value->$classTypes as $keyType => $valueType) {
            if ($valueType !== $hydraClass) {
              dump($valueType);
              array_push($classArray, $valueType);
              $voc = parse_url($valueType, PHP_URL_SCHEME) . '://' . parse_url($valueType, PHP_URL_HOST);
              array_push($vocsArray,$voc);
            }
          }
        }
        $dbEntry->setApplicationSubCategory(implode (", ", $classArray));
        $dbEntry->setApplicationCategory(implode (", ", $vocsArray));

        dump($dbEntry);
      }
      $this->addVoc($vocsArray);
      $this->addClass($classArray);

      return [$dbEntry,$uploadedGraph,$baseUri];
    }

    protected function searchAction(\EasyRdf_Graph $graph,array $class)
    {

    }

    protected function buildResponse()
    {

    }
/**
 * @Route(
 *     name="api_test_upload_action",
 *     path="/api_test/upload",
 *     defaults={"_api_resource_class"=SoftwareApplicationTesting::class, "_api_collection_operation_name"="test_upload_graph"}
 * )
 * @Method("PUT")
 */
    public function __invoke($data)
    {
      dump($data);
      $dataRaw = $this->getRequest()->getContent();
      $dbEntry = new SoftwareApplicationTesting();
      $out = $this->parseGraph($dataRaw,$dbEntry);
      $write = $this->doctrine->getManager();
      $write->persist($out[0]);
      $write->flush();

      $expandedGraph = JsonLD::expand($out[1]);

      $graph = new \EasyRdf_Graph();
      $graph->parse($expandedGraph,'jsonld',null);
      dump($graph);

      // GRAPH upload
      $graphUri =  $out[2];//'http://www.example.com/testgraph';
      if ($this->graphExists($graphUri,$this->sparqlURL . '/query')) {
        $this->dropGraph($graphUri,$this->sparqlURL . '/update');
      }
      $sparqlEndpoint = $this->sparqlURL . '/update';
      $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
      $sparqlClient->insert($graph,$graphUri);
      dump($sparqlClient);

      return new Response(200);
    }
}