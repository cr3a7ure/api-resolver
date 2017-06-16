<?php

// src/AppBundle/Action/SoftwareApplicationUploadAction.php

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

class SoftwareApplicationUploadAction
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
    // protected function sparqlActionQuery(\EasyRdf_Resource $actionProperty, string $options = '')
    // {
    //   $properties = $this->retrieveAction($actionProperty);
    //   return $
    // }
/**
 * [retrievePropertyProperties description]
 * @param  \EasyRdf_Resource $property [schema:potentialAction]
 * @param  string            $class    [description]
 * @param  \EasyRdf_Graph    $graph    [description]
 * @return [type]                      [description] */
  /*
  "rdf:type" => "http://schema.org/searchAction"
  "schema:object" => "http://schema.org/Offer"
  "schema:query" => "schema:Flight"
  "schema:result" => "http://schema.org/Offer"
   */
    protected function retrieveAction(\EasyRdf_Resource $actionProperty, string $options = '')
    {
      $actionProps = $actionProperty->properties();
      foreach ($actionProps as $i => $actionPredicate) {
        $actionValues = $actionProperty->allResources($actionPredicate);
        foreach ($actionValues as $k => $value) {
          if ($value->isBNode()) {
            $nestedProperties[$actionPredicate] = $value->type();
          } else {
            $nestedProperties[$actionPredicate] = $value->getUri();
          }
        }
        // dump($actionPredicate);
        // dump($actionValues);
      }
      dump("\nRetrieved semantics for Action".$actionProperty->getUri());
      return $nestedProperties;
    }

    protected function retrievePropertyProperties(\EasyRdf_Resource $class, string $property, \EasyRdf_Graph $graph)
    {
      // dump($class);
      dump("\nProperty ".$property . "\n contains properties:");
      $actionProps = $class->properties();
      $containedResources = $class->allResources($property); //retrieves Resources
      // dump($property);
      // dump($containedResources);
      $nestedProperties[$property] = [];
      foreach ($containedResources as $i => $res) { //If we have contained resources!!
        dump("\nPredicate: ".$res);
        $rangeType = $res->types(); //Retrieve types of property
        foreach ($rangeType as $k => $typeUri) {
        dump("\nType - range: ".$typeUri);
          if(preg_match("/(Action)/", $typeUri, $checkAction)==1) {
            // $nestedProperties[$property]['rdfs:range'] = $typeUri;
            $test = $this->retrieveAction($res);
            dump($test);
             $nestedProperties[$property] = $this->retrieveAction($res);
          } else {
            // suppose we have only other classes types
            $nestedProperties[$property]['rdfs:range'] = $typeUri;
          }

        }
        // dump($rangeType);
      }
      dump('LOL');
      dump("\nRetrieved Contained Properties: ");
      dump($nestedProperties);
      return $nestedProperties;
    }

    protected function retrieveProperty(\EasyRdf_Graph $graph,\EasyRdf_Resource $class)
    {
      dump("\nRetrieve properties for Class ". $class->getUri());
      $nestedProperties = array();
      $types = $graph->allOfType($class);
      $classArray = array();
      $classProperties = array();
      $selectedProperties = array();
      $nestedProperties = array();
      // $props = $graph->properties($q[0]->getUri());// Get al the propsUris
      $k = 0;
      foreach ($types as $i => $node) {
        $classProperties = $graph->properties($node->getUri());
        dump("\nNodes of current class type: " . count($classProperties));
        if( (count($classProperties)==1)&&($classProperties[0]=='rdf:type') ){
          //used as rdfs:range option as this bnode has only the type property
        } else {
          $classArray[$k] = $node;
          dump("\nMain Class at bnode: " . $node);
          $selectedProperties = $classProperties;
          $k++;
        }
        $test = $node->types();
        // dump($node);
        // dump($classProperties);
        // dump($classArray);
      } //We found the main class
      // dump($types);
      // dump($class);
      foreach ($selectedProperties as $i => $predicate) {
        dump("\nResolving property: " . $predicate);
        if($predicate!='rdf:type') {
          //SKIP the rdf:type of this class-bnode
          $nestedProperties[$predicate] = array();
          $containedResources = $classArray[0]->allResources($predicate);//we use only 1
          $nestedProperties[$predicate] = $this->retrievePropertyProperties($classArray[0],$predicate,$graph);
          // dump($properties2);
        }
      }
      dump("MANASOU");
      dump("\nRetrieved Class Properties: ");
      dump($nestedProperties);
      return $nestedProperties; //rmeove rdf:type property
    }

    protected function graphExists(string $graphURI,string $sparqlEndpoint) {
      $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
        $query = 'ASK WHERE { GRAPH <'.$graphURI.'> { ?s ?p ?o } }';
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

    protected function parseGraph(string $stingGraph)
    {
      $baseString = '@base';
      $context = '@context';
      $id = '@id';
      $jsonGraph = json_decode($stingGraph);
      $uploadedGraph = $jsonGraph->articleBody;
      dump($uploadedGraph);
      $baseUri = $uploadedGraph->$context->$baseString . $uploadedGraph->$id;
      dump($baseUri);
      $classes = "hydra:supportedClass";
      foreach ($uploadedGraph->$classes as $key => $value) {
        $properties = "hydra:supportedProperty";
        $description = 'hydra:description';
        foreach ($value->$properties as $key => $value) {
          if (array_key_exists($description, $value)) {
            $value->$description = json_decode($value->$description);
          }
        }
      }
      return [$uploadedGraph,$baseUri];
    }

    protected function searchAction(\EasyRdf_Graph $graph,array $class)
    {

    }

    protected function buildResponse()
    {

    }
/**
 * @Route(
 *     name="api_graph_upload_action",
 *     path="/software_applications",
 *     defaults={"_api_resource_class"=SoftwareApplication::class, "_api_collection_operation_name"="upload_graph"}
 * )
 * @Method("PUT")
 */
    // public function __invoke($data)
    public function __invoke($data)
    {
      $uploadedGraph = $this->getRequest()->getContent();
      dump($this->getRequest());
      // dump($this->getRequest()->getClientIp());
      $temp = $this->parseGraph($uploadedGraph);
      $expandedGraph = JsonLD::expand($temp[0]);

      $graph = new \EasyRdf_Graph();
      $graph->parse($expandedGraph,'jsonld',null);
      dump($graph);

      // GRAPH upload
      $graphUri =  $temp[1];//'http://www.example.com/testgraph';
      $graphClasses = $this->retrieveClass($graph);
      if ($this->graphExists($graphUri,'http://localhost:8090/test1/query')) {
        $this->dropGraph($graphUri,'http://localhost:8090/test1/update');
      }
      dump($graphClasses);
      dump($graph->typesAsResources());
      $sparqlEndpoint = 'http://localhost:8090/test1/update';
      $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
      $sparqlClient->insert($graph,$graphUri);
      dump($sparqlClient);

      return new Response();
    }
}