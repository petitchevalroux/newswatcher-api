<?php

namespace NwApi\Controllers;

use NwApi\Libraries\Singleton;
use NwApi\Di;
use Doctrine\ORM\Mapping\ClassMetadata;
use NwApi\Entities\Entity;
use NwApi\Libraries\RestDoctrineRouter as Router;
use NwApi\Exceptions\Server as ServerException;
use NwApi\Exceptions\Client as ClientException;

class JsonApiController extends Singleton
{
    const DEFAULT_CONTENT_TYPE = 'application/json';

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
        $this->response($entities);
    }

    /**
     * Retrieves a specific entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param array         $id
     */
    public function getEntity(ClassMetadata $meta, $id)
    {
        $entity = $this->fetchEntity($meta, $id);
        $this->response($entity);
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
        $response = $this->setProperties($meta, $entity, $data);
        $di->slim->response->setStatus(201);
        $di->slim->response->headers->set('Location', Router::getInstance()->getEntityLocation($meta, $entity));

        $this->response($entity);
    }

    /**
     * Updates a existing entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param array         $id
     */
    public function updateEntity(ClassMetadata $meta, $id)
    {
        $entity = $this->fetchEntity($meta, $id);
        $this->setProperties($meta, $entity);
        $this->response($entity);
    }

    /**
     * Partially updates entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param array         $id
     */
    public function patchEntity(ClassMetadata $meta, $id)
    {
        // For patch we use the same as update as Doctrine handle it nicely
        // plus it allow to properly use LifeCycle in entity
        $this->updateEntity($meta, $id);
    }

    /**
     * Deletes entity.
     *
     * @param ClassMetadata $meta entity metadata
     * @param array         $id
     */
    public function deleteEntity(ClassMetadata $meta, $id)
    {
        $di = Di::getInstance();
        $entity = $this->fetchEntity($meta, $id);
        if (!is_null($entity)) {
            $di->em->remove($entity);
            $di->em->flush();
            $di->slim->response->setStatus(204);
        }
    }

    /**
     * Copy properties from body request to $entity and save it.
     *
     * @param ClassMetadata $meta
     * @param Entity        $entity
     *
     * @return Entity
     */
    private function setProperties(ClassMetadata $meta, Entity $entity)
    {
        $di = Di::getInstance();
        $contentType = $di->slim->request->headers->get('Content-Type');
        if ($contentType !== static::DEFAULT_CONTENT_TYPE) {
            throw new ClientException('Invalid request content type '.json_encode(['Content-Type' => $contentType]), ClientException::CODE_BAD_REQUEST);
        }
        $bodyData = $di->slim->request->getBody();
        $data = json_decode($di->slim->request->getBody(), true);
        if ($data === false) {
            throw new ClientException('Unable to json decode body '.json_encode(['body' => $bodyData]), ClientException::CODE_BAD_REQUEST);
        }
        if (is_array($data)) {
            foreach ($data as $name => $value) {
                $entity = $this->setProperty($meta, $entity, $name, $value);
            }
        }

        try {
            $di->em->persist($entity);
            $di->em->flush();
        } catch (\Doctrine\DBAL\Exception\ConstraintViolationException $ex) {
            $match = [];
            // Catch the clean part of the message
            if (preg_match('~.*?:\s{2}(.*)$~s', $ex->getMessage(), $match)) {
                $message = trim($match[1]);
            } else {
                $message = 'Contraint violation, check your request body data';
            }
            throw new ClientException($message, ClientException::CODE_BAD_REQUEST);
        }

        return $entity;
    }

    /**
     * Set entity property value according to meta.
     *
     * @param ClassMetadata $meta
     * @param Entity        $entity
     * @param string        $name
     * @param string        $value
     *
     * @return Entity
     */
    private function setProperty(ClassMetadata $meta, Entity $entity, $name, $value)
    {
        if ($meta->hasField($name) && !$meta->isIdentifier($name)) {
            $meta->setFieldValue($entity, $name, $value);
        } elseif ($meta->hasAssociation($name)) {
            // We have a single value and there is only one column in association
            if (!is_array($value) && !is_object($value) && $meta->isAssociationWithSingleJoinColumn($name)) {
                $id = [$meta->associationMappings[$name]['joinColumns'][0]['referencedColumnName'] => $value];
                $linkedEntity = $di->em->find($meta->getAssociationTargetClass($name), $id);
                if (is_null($linkedEntity)) {
                    throw new ClientException('Entity not found for nested entity '.json_encode(['name' => $name]), ClientException::CODE_NOT_FOUND);
                } else {
                    $meta->setFieldValue($entity, $name, $linkedEntity);
                }
            } else {
                throw new ServerException('Unhandled association type for field '.$name.' on '.$meta->name, ServerException::CODE_NOT_IMPLEMENTED);
            }
        }

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
        $entity = $di->em->find($meta->name, array_combine($meta->identifier, $id));
        if (is_null($entity)) {
            throw new ClientException('Entity not found', ClientException::CODE_NOT_FOUND);
        }

        return $entity;
    }

    private function response($body)
    {
        $di = Di::getInstance();
        $di->slim->response->headers->set('Content-Type', static::DEFAULT_CONTENT_TYPE);
        $di->slim->response->setBody(json_encode($body));
    }
}
