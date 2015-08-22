<?php

namespace NwApi\Controllers;

use NwApi\Libraries\Singleton;
use NwApi\Di;
use Doctrine\ORM\Mapping\ClassMetadata;
use Exception;

class RestEntities extends Singleton
{
    /**
     * @var NwApi\Di
     */
    protected $di;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Slim\Slim
     */
    protected $app;

    /**
     * Retrieves a list of entities.
     *
     * @param ClassMetadata $meta entities metadata
     */
    public function getEntities(ClassMetadata $meta)
    {
        $di = Di::getInstance();
        $repository = $di->em->getRepository($meta->name);
        $filters = (array) $di->slim->request->get('filters');
        $orders = (array) $di->slim->request->get('orders');
        $limit = $di->slim->request->get('limit');
        $offset = $di->slim->request->get('offset');
        $entities = $repository->findBy($filters, $orders, $limit, $offset);
        $di->slim->response->setBody(json_encode($entities));
    }

    /**
     * Retrieves a specific entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param int           $id
     */
    public function getEntity(ClassMetadata $meta, $id)
    {
        $di = Di::getInstance();
        $entity = $this->fetchEntity($meta, $id);
        $di->slim->response->setBody(json_encode($entity));
    }

    /**
     * Creates a new entity.
     *
     * @param ClassMetadata $meta entity metadata
     */
    public function createEntity(ClassMetadata $meta)
    {
        $di = Di::getInstance();
        $entity = new $meta->name();
        $data = json_decode($di->slim->request->getBody(), true);
        $this->setProperties($meta, $entity, $data);
        $di->slim->response->setStatus(201);
        $di->slim->response->headers->set('Location', $di->slim->request->getResourceUri().'/'.$entity->getId());
        $di->slim->response->setBody(json_encode($entity));
    }

    /**
     * Updates a existing entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param int           $id
     */
    public function updateEntity(ClassMetadata $meta, $id)
    {
        $di = Di::getInstance();
        $entity = $this->fetchEntity($meta, $id);
        $this->setProperties($meta, $entity);
        $di->slim->response->setBody(json_encode($entity));
    }

    /**
     * Partially updates entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param int           $id
     */
    public function patchEntity(ClassMetadata $meta, $id)
    {
        $di = Di::getInstance();
        $qb = $di->em->createQueryBuilder();
        $qb->update($meta->name, 'e')
                ->where('e.id = :id')
                ->setParameter('id', $id);
        $this->processBodyProperties($meta, function ($name, $value) use ($qb) {
            $qb->set('e.'.$name, $qb->expr()->literal($value));
        });
        $qb->getQuery()->execute();
        $entity = $this->fetchEntity($meta, $id);
        $di->slim->response->setBody(json_encode($entity));
    }

    /**
     * Deletes entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param int           $id
     */
    public function deleteEntity(ClassMetadata $meta, $id)
    {
        $di = Di::getInstance();
        $qb = $di->em->createQueryBuilder();
        $qb->delete($meta->name, 'e')
                ->where('e.id = :id')
                ->setParameter('id', $id);
        $qb->getQuery()->execute();
        $di->slim->response->setStatus(204);
    }

    /**
     * Copy properties from body request to $entity and save it.
     *
     * @param ClassMetadata $meta
     * @param entity        $entity
     *
     * @return type
     */
    private function setProperties(ClassMetadata $meta, $entity)
    {
        $di = Di::getInstance();
        $this->processBodyProperties($meta, function ($name, $value) use ($meta,$entity) {
            $method = 'set'.ucfirst($name);
            if (!method_exists($entity, $method)) {
                throw new Exception('Undefined method '.$meta->name.'::'.$method);
            }
            call_user_func([$entity, 'set'.ucfirst($name)], $value);
        });
        $di->em->persist($entity);
        $di->em->flush();

        return $entity;
    }

    /**
     * Fetch entity $id and throw 404 if not found.
     *
     * @param ClassMetadata $meta
     * @param int           $id
     *
     * @return stdObject
     */
    private function fetchEntity(ClassMetadata $meta, $id)
    {
        $di = Di::getInstance();
        $entity = $di->em->find($meta->name, $id);
        if (is_null($entity)) {
            $di->slim->notFound();
        }

        return $entity;
    }

    /**
     * Decode the request body data and process callbalk on each data fields.
     *
     * @param ClassMetadata               $meta
     * @param \NwApi\Controllers\callable $callback
     */
    private function processBodyProperties(ClassMetadata $meta, callable $callback)
    {
        $di = Di::getInstance();
        $data = json_decode($di->slim->request->getBody(), true);
        foreach ($meta->fieldMappings as $field) {
            $name = $field['fieldName'];
            if (isset($data[$name]) && (!isset($field['id']) || $field['id'] !== true)) {
                $value = $data[$name];
                call_user_func($callback, $name, $value);
            }
        }
    }
}
