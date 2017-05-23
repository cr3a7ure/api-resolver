<?php

// src/AppBundle/Action/ApiPost.php

namespace AppBundle\Action;

use Symfony\Component\Serializer\Annotation\Groups;
use AppBundle\Entity\APIReference;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class ApiPost
{
    // private $myService;

    // public function __construct(MyService $myService)
    // {
    //     $this->myService = $myService;
    // }

    /**
     * @Route(
     *     name="api_post",
     *     path="/a_p_i_references/special",
     *     defaults={"_api_resource_class"=APIReference::class, "_api_collection_operation_name"="special"}
     * )
     * @Method("POST")
     */
    public function __invoke($data) // API Platform retrieves the PHP entity using the data provider then (for POST and
                                    // PUT method) deserializes user data in it. Then passes it to the action. Here $data
                                    // is an instance of Book having the given ID. By convention, the action's parameter
                                    // must be called $data.
    {
        // $this->myService->doSomething($data);
        dump($data);
        $temp = 1;
        return $temp; // API Platform will automatically validate, persist (if you use Doctrine) and serialize an entity
                      // for you. If you prefer to do it yourself, return an instance of Symfony\Component\HttpFoundation\Response
    }
}