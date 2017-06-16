<?php

// src/AppBundle/Action/ApiRefAction.php

namespace AppBundle\Action;

use Symfony\Component\Serializer\Annotation\Groups;
use AppBundle\Entity\ApiRef;
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

class ApiRefAction
{

    protected $requestStack;
    protected $apikey;

    public function __construct(RequestStack $requestStack, string $apiKey = null)
    {
        $this->requestStack = $requestStack;
        $this->apikey = $apiKey;
        dump($apiKey);
    }

    protected function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
    protected function getApikey()
    {
        return $this->apikey;
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
        if (!($value->isBnode())&&($value->getUri()!='http://schema.org/searchAction')) {
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

    protected function sparqlClassQuery(\EasyRdf_Graph $graph,\EasyRdf_Resource $class,string $className = '')
    {
      $className = $class->getUri();
      dump("Creating search query for class ". $class);
      $classProperties = $this->retrieveProperty($graph,$class);
      $propertyOptions = '';
      $range = '';
      $i = 1;
      // dump($kapa); ?class hydra:supportedProperty ?props .
      foreach( $classProperties as $propertyType => $options ) {
        if ($propertyType == 'schema:potentialAction') {
          dump("LOL");
          dump($classProperties);
          dump($options);
          //different actions for potentialActions
          foreach ($options as $optionName => $optionsArray) {
            // dump($optionsArray);
            // foreach ($optionsArray as $predicate => $value) {
              // $propertyOptions .= '?action'.$i.'_IRI' . ' '. $predicate. ' ' . $value . " .\n          ";
              $propertyOptions .= '?action'.$i.'_IRI' . ' schema:object ' . '?class' . " .\n          ";
              //We are checking this exact class
              $propertyOptions .= '?action'.$i.'_IRI' . ' schema:query ' . '?query' . " .\n          ";
              $propertyOptions .= '?query' . ' rdf:type ' . '?queryClass' . " .\n          ";
              $propertyOptions .= '?queryClass' . ' rdf:type ' . $optionsArray['schema:query'] . " .\n          ";
              //query is not @type=@id so we need to specify it's intermidiate class
              $propertyOptions .= '?action'.$i.'_IRI' . ' schema:result ' . '?result' . " .\n          ";
              $propertyOptions .= '?result' . ' rdf:type ' . '<'. $optionsArray['schema:result'] . '>'. " .\n          ";
              $propertyOptions .= '?action'.$i.'_IRI' . ' schema:target ' . '?target' . " .\n          ";
            // }
          } //add property Options
          // $propertyOptions .= '?action ' .
        } else {
          $propertyOptions .= '?class' . ' hydra:supportedProperty ' . '?prop'.$i . " .\n          ";
          $propertyOptions .= '?prop'.$i . ' hydra:property ' . '?prop'.$i.'_IRI' . " .\n          ";
          $propertyOptions .= '?prop'.$i.'_IRI' . ' rdf:type ' . $propertyType . " .\n          ";
          foreach ($options as $optionName => $optionsArray) {
            // dump($optionsArray);
            foreach ($optionsArray as $predicate => $value) {
              $propertyOptions .= '?prop'.$i.'_IRI' . ' '. $predicate. ' ' . $value . " .\n          ";
            }
          } //add property Options
        }
        $i++;
      }
      $prefix = $this->getDefaultPrefix();
      // FROM <http://localhost:8090/test1/data/apiv17>
      $query = $prefix . "\n".
      'DESCRIBE ?class ?target
      WHERE  {
        ?class rdf:type ' .'<'. $className .'>.
        ?server hydra:supportedClass ?class.
        ?server hydra:entrypoint ?entrypoint .
        ' . $propertyOptions . '}';
      return $query;
      // dump($query);
    }

    protected function testHydra() {
        $prefix = $this->getDefaultPrefix();
        $query = $prefix .
        'DESCRIBE *
        FROM <http://localhost:8090/test1/data/apiv17>
        WHERE  { <http://localhost:8091/docs.jsonld> ?p ?o }';
        return $query;
    }

    protected function getBindings(string $class)
    {
            // FROM <http://localhost:8090/test1/data/test1>
        $query = 'prefix hydra: <http://www.w3.org/ns/hydra/core#>
            DESCRIBE ?subject
            WHERE {
              ?subject hydra:supportedProperty ?object
            }
            LIMIT 10';
        return $data;
    }

    protected function searchAction(\EasyRdf_Graph $graph,array $class)
    {

    }

    protected function buildResponse()
    {

    }
/**
 * @Route(
 *     name="api_ref_action",
 *     path="/api_ref/special",
 *     defaults={"_api_resource_class"=ApiRef::class, "_api_collection_operation_name"="special"}
 * )
 * @Method("PUT")
 */
    // public function __invoke($data)
    public function __invoke($data)
    {
      $request = $this->getRequest()->getContent();
      // $test = $container->getParameter('api_key');
      $test = $this->getApikey();
      dump($data);
      dump($test);

        $req = '{
  "@context" : {
    "expects" : {
      "@id" : "http://www.w3.org/ns/hydra/core#expects",
      "@type" : "@id"
    },
    "returns" : {
      "@id" : "http://www.w3.org/ns/hydra/core#returns",
      "@type" : "@id"
    },
    "method" : {
      "@id" : "http://www.w3.org/ns/hydra/core#method"
    },
    "title" : {
      "@id" : "http://www.w3.org/ns/hydra/core#title"
    },
    "label" : {
      "@id" : "http://www.w3.org/2000/01/rdf-schema#label"
    },
    "writable" : {
      "@id" : "http://www.w3.org/ns/hydra/core#writable",
      "@type" : "http://www.w3.org/2001/XMLSchema#boolean"
    },
    "required" : {
      "@id" : "http://www.w3.org/ns/hydra/core#required",
      "@type" : "http://www.w3.org/2001/XMLSchema#boolean"
    },
    "readable" : {
      "@id" : "http://www.w3.org/ns/hydra/core#readable",
      "@type" : "http://www.w3.org/2001/XMLSchema#boolean"
    },
    "object": {
      "@id": "schema:object",
      "@type": "@id"
    },
    "result": {
      "@id": "schema:result",
      "@type": "@id"
    },
    "target": {
      "@id": "schema:target",
      "@type": "@id"
    },
    "query": {
      "@id": "schema:query"
    },
    "property" : {
      "@id" : "http://www.w3.org/ns/hydra/core#property",
      "@type" : "@id"
    },
    "description" : {
      "@id" : "http://www.w3.org/ns/hydra/core#description"
    },
    "supportedProperty" : {
      "@id" : "http://www.w3.org/ns/hydra/core#supportedProperty",
      "@type" : "@id"
    },
    "supportedOperation" : {
      "@id" : "http://www.w3.org/ns/hydra/core#supportedOperation",
      "@type" : "@id"
    },
    "@vocab" : "http://schema.org/",
    "schema" : "http://schema.org/",
    "hydra" : "http://www.w3.org/ns/hydra/core#",
    "xmls" : "http://www.w3.org/2001/XMLSchema#",
    "owl" : "http://www.w3.org/2002/07/owl#",
    "rdf" : "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
    "xsd" : "http://www.w3.org/2001/XMLSchema#",
    "rdfs" : "http://www.w3.org/2000/01/rdf-schema#"
  },
  "offer": {
      "@type": "Offer",
      "priceCurrency": "",
      "price": "",
      "seller": "",
      "itemOffered": { "@type": "schema:Flight" },
      "potentialAction": {
        "@type": "searchAction",
        "query": {"@type": "schema:Flight"},
        "object": "schema:Offer",
        "result": "schema:Offer",
        "target": "schema:Offer"
      }
    },
    "flight": {
      "@type": "Flight",
      "arrivalAirport": {"@type": "Airport"},
      "departureAirport": {"@type": "Airport"},
      "departureTime": "",
      "arrivalTime": ""
    },
    "postalAdress": {
      "@type": "PostalAddress",
      "addressCountry": "",
      "addressLocality": ""
    },
        "airport": {
      "@type": "Airport",
      "iataCode": "",
      "address": { "@type": "PostalAddress" },
      "potentialAction": {
        "@type": "searchAction",
        "object": "schema:Airport",
        "result": "schema:Airport",
        "target": "schema:Airport",
        "query": {"@type": "schema:PostalAddress"}
      }
    }
}';
      $req = $request;
      $graph = new \EasyRdf_Graph();
      $expanded = JsonLD::expand($req);
      $graph->parse($expanded,'jsonld',null);
      dump($graph);

      // GRAPH retrieve
      $queries = array();
      $graphClasses = $this->retrieveClass($graph);
      $selectQueryArray = array();
      $responseGraph = new \EasyRdf_Graph();
      $stichResponse = '';
      $sparqlEndpoint = 'http://localhost:8090/thesis/query';
      $sparqlClient = new \EasyRdf_Sparql_Client($sparqlEndpoint);
      foreach ($graphClasses as $classUri => $classResource) {
        $queries[$classUri] = $this->sparqlClassQuery($graph,$classResource);
        $selectQueryArray[$classUri] = $sparqlClient->query($queries[$classUri]);
        // $responseGraph = $sparqlClient->query($queries[$classUri]);
        // dump($responseGraph->serialise('jsonld'));
      }

      // dump($stichResponse);
      foreach ($selectQueryArray as $classUri => $value) {
        $semigraph = $value->serialise('jsonld');
        $responseGraph->parse($semigraph,'jsonld',null);
      }
      foreach ($queries as $classUri => $queryString) {
        dump($classUri . "\n" . $queryString);
        dump($selectQueryArray[$classUri]);
      }
      $stichResponse = $responseGraph->serialise('jsonld');
      dump($responseGraph);
      //Serialize GRAPH
      $graphOut = $graph->serialise('jsonld');
      $lal = $this->validateData($graphOut);
      dump($graphOut);

      // $sparql = new \EasyRdf_Sparql_Client('http://localhost:8090/thesis/query');

      // $url = 'http://localhost:8081/docs.jsonld';
      // $headers = array("Content-Type" => "application/ld+json",
      //                 "Accept" => "application/ld+json");
      // $query = '';
      // $response = Unirest\Request::get($url,$headers,$query);
      // dump($response);
      // $expanded = JsonLD::expand($temp);
      // $temp2 = new \EasyRdf_Graph();
      // $temp2->parse($response->body,'jsonld',null);
      // throw new \Exception('DUMPSTERRRRR!');
      // return new Response($temp2->serialise('jsonld'));
      return new Response($stichResponse);

       // API Platform will automatically validate, persist (if you use Doctrine) and serialize an entity
                      // for you. If you prefer to do it yourself, return an instance of Symfony\Component\HttpFoundation\Response
    }
}