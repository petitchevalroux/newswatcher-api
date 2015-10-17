<?php

use Faker\Factory;
use NwApi\Controllers\JsonApiController;
use Doctrine\ORM\Mapping\ClassMetadata;
use NwApi\Di;

class JsonApiControllerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Factory
     */
    private $faker;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->faker = Factory::create();
    }

    public function testCreateEntity()
    {

        $controller = new JsonApiController();
        $object = new NwApi\Entities\User();
        $object->name = $this->faker->userName();
        $object->twitterId = $this->faker->randomDigit();
        Slim\Environment::mock([
            'HTTP_CONTENT_TYPE' => 'application/json',
            'slim.input' => json_encode($object)
        ]);
        $di = Di::getInstance();
        $di->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();
        $di->jsonApiRouter = $this->getMockBuilder('NwApi\Libraries\RestDoctrineRouter')
                ->disableOriginalConstructor()
                ->getMock();
        $meta = $this->getMockBuilder("Doctrine\ORM\Mapping\ClassMetadata")
                ->setConstructorArgs([get_class($object)])
                ->getMock();
        $meta->expects($this->any())
                ->method('hasField')->willReturn(true);
        $meta->expects($this->any())
                ->method('isIdentifier')->willReturn(false);
        $controller->createEntity($meta);
    }

}
