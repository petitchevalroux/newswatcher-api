<?php

use Faker\Factory;
use NwApi\Controllers\JsonApiController;
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
            'slim.input' => json_encode($object),
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

    public function testDeleteEntity()
    {
        $controller = new JsonApiController();
        $meta = $this->getMockBuilder("Doctrine\ORM\Mapping\ClassMetadata")
                ->setConstructorArgs(["NwApi\Entities\User"])
                ->getMock();
        $di = Di::getInstance();
        $di->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $di->em->expects($this->any())
            ->method('find')->willReturn(true);
        $meta->identifier = ['id'];
        $controller->deleteEntity($meta, [$this->faker->randomDigit()]);
    }

    public function testPatchEntity()
    {
        $controller = new JsonApiController();
        $meta = $this->getMockBuilder("Doctrine\ORM\Mapping\ClassMetadata")
                ->setConstructorArgs(["NwApi\Entities\User"])
                ->getMock();
        $di = Di::getInstance();
        $di->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $di->em->expects($this->any())
            ->method('find')->willReturn(new NwApi\Entities\User());
        $meta->identifier = ['id'];
        $controller->patchEntity($meta, [$this->faker->randomDigit()]);
    }

    public function testGetEntity()
    {
        $controller = new JsonApiController();
        $meta = $this->getMockBuilder("Doctrine\ORM\Mapping\ClassMetadata")
                ->setConstructorArgs(["NwApi\Entities\User"])
                ->getMock();
        $di = Di::getInstance();
        $di->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $di->em->expects($this->any())
            ->method('find')->willReturn(new NwApi\Entities\User());
        $meta->identifier = ['id'];
        $controller->getEntity($meta, [$this->faker->randomDigit()]);
    }
}
