<?php

namespace NwApi\Controllers;

use NwApi\Libraries\Singleton;
use NwApi\Di;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Collections\Criteria;
use NwApi\Entities\Entity;
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
        list($filters, $orders, $limit, $offset) = $this->getFetchEntitiesParameters();
        $entities = $repository->findBy($filters, $orders, $limit, $offset);
        $this->responseEntities($entities, $filters, $orders, $limit, $offset);
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
     * If $sourceMeta and $sourceId are specified, the created entity will be
     * associated to the source entity
     *
     * @param ClassMetadata $meta
     * @param ClassMetadata $sourceMeta meta of associated source entity
     * @param array         $sourceId   id of associated source entity
     */
    public function createEntity(ClassMetadata $meta, $aFieldName = '', ClassMetadata $aSourceMeta = null, $aSourceId = null)
    {
        if (!is_null($aSourceMeta)) {
            $sourceEntity = $this->fetchEntity($aSourceMeta, $aSourceId);
        } else {
            $sourceEntity = false;
        }
        $di = Di::getInstance();
        $entity = new $meta->name();
        $this->setProperties($meta, $entity);
        $di->slim->response->setStatus(201);
        $di->slim->response->headers->set('Location', $di->jsonApiRouter->getEntityLocation($meta, $entity));
        if ($sourceEntity !== false) {
            $sourceEntity->{$aFieldName}[] = $entity;
            $di->em->persist($sourceEntity);
            $di->em->flush();
        }
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
     * Return request data from body.
     *
     * @return type
     *
     * @throws ClientException
     */
    private function getRequestData()
    {
        if (!isset($this->requestData)) {
            $di = Di::getInstance();
            $contentType = $di->slim->request->headers->get('Content-Type');
            if (strpos($contentType, static::DEFAULT_CONTENT_TYPE) !== 0) {
                throw new ClientException('Invalid request content type '.json_encode(['Content-Type' => $contentType]), ClientException::CODE_BAD_REQUEST);
            }
            $bodyData = $di->slim->request->getBody();
            $data = json_decode($di->slim->request->getBody(), true);
            if ($data === false) {
                throw new ClientException('Unable to json decode body '.json_encode(['body' => $bodyData]), ClientException::CODE_BAD_REQUEST);
            }
            $this->requestData = $data;
        }

        return $this->requestData;
    }

    /**
     * Copy properties from body request to $entity and save it.
     *
     * @param ClassMetadata $meta
     * @param Entity        $entity
     *
     * @return Entity
     */
    private function setProperties(ClassMetadata $meta, Entity &$entity)
    {
        $data = $this->getRequestData();
        if (is_array($data)) {
            foreach ($data as $name => $value) {
                $this->setProperty($meta, $entity, $name, $value);
            }
        }

        try {
            $di = Di::getInstance();
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
    private function setProperty(ClassMetadata $meta, Entity &$entity, $name, $value)
    {
        if ($meta->hasField($name) && !$meta->isIdentifier($name)) {
            $meta->setFieldValue($entity, $name, $value);
        } elseif ($meta->hasAssociation($name)) {
            // We have a single value and there is only one column in association
            if (!is_array($value) && !is_object($value) && $meta->isAssociationWithSingleJoinColumn($name)) {
                $id = [$meta->associationMappings[$name]['joinColumns'][0]['referencedColumnName'] => $value];
                $di = Di::getInstance();
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
            throw new ClientException('Entity '.$meta->table['name'].' not found', ClientException::CODE_NOT_FOUND);
        }

        return $entity;
    }

    private function response($body)
    {
        $di = Di::getInstance();
        $di->slim->response->headers->set('Content-Type', static::DEFAULT_CONTENT_TYPE);
        $di->slim->response->setBody(json_encode($body));
    }

    public function associateEntities(ClassMetadata $meta, $aFieldName, ClassMetadata $aSourceMeta = null, $ids)
    {
        $countSourceIdentifier = count($aSourceMeta->identifier);
        $sourceEntityIds = array_slice($ids, 0, $countSourceIdentifier);
        if (count($sourceEntityIds) !== $countSourceIdentifier) {
            throw new ClientException('wrong identifier\'s count for resource '.$aSourceMeta->table['name'], ClientException::CODE_BAD_REQUEST);
        }
        $entityIds = array_slice($ids, $countSourceIdentifier);
        if (count($entityIds) !== count($meta->identifier)) {
            throw new ClientException('wrong identifier\'s count for resource '.$meta->table['name'], ClientException::CODE_BAD_REQUEST);
        }
        $sourceEntity = $this->fetchEntity($aSourceMeta, $sourceEntityIds);
        $entity = $this->fetchEntity($meta, $entityIds);
        try {
            $sourceEntity->{$aFieldName}[] = $entity;
            $di = Di::getInstance();
            $di->em->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            throw new ClientException('Unique constraint violation, this resources are probably already associated', ClientException::CODE_BAD_REQUEST);
        }
        $di->slim->response->setStatus(201);
        $this->response([
            $aSourceMeta->table['name'] => array_combine($aSourceMeta->identifier, $sourceEntityIds),
            $meta->table['name'] => array_combine($meta->identifier, $entityIds),
        ]);
    }

    private function getEntitiesUrl($filters, $orders, $limit, $offset)
    {
        $di = Di::getInstance();
        $url = $di->slim->request->getPath();
        $query = http_build_query([
            'filters' => $filters,
            'orders' => $orders,
            'limit' => $limit,
            'offset' => $offset,
        ]);
        if (!empty($query)) {
            $url .= '?'.$query;
        }

        return $url;
    }
    public function getFetchEntitiesParameters()
    {
        $di = Di::getInstance();
        $filters = (array) $di->slim->request->get('filters');
        $orders = (array) $di->slim->request->get('orders');
        $limit = (int) $di->slim->request->get('limit');
        $offset = $di->slim->request->get('offset');
        if (empty($limit)) {
            $limit = 100;
        }
        return [
            $filters,
            $orders,
            $limit,
            $offset,
        ];
    }

    public function getAssociatedEntities($aFieldName, ClassMetadata $aSourceMeta, $aSourceId)
    {
        $sourceEntity = $this->fetchEntity($aSourceMeta, $aSourceId);
        $entities = [];
        list($filters, $orders, $limit, $offset) = $this->getFetchEntitiesParameters();
        $criteria = new Criteria(null, $orders, $offset, $limit);
        foreach ($filters as $f => $v) {
            $criteria->andWhere(Criteria::expr()->eq($f, $v));
        }
        foreach ($sourceEntity->{$aFieldName}->matching($criteria) as $entity) {
            $entities[] = $entity;
        }
        $this->responseEntities($entities, $filters, $orders, $limit, $offset);
    }

    private function responseEntities($entities, $filters, $orders, $limit, $offset)
    {
        $links = [];
        if ($limit > 0) {
            if ($offset > 0) {
                $links['prev'] = $this->getEntitiesUrl($filters, $orders, $limit, max($offset - $limit, 0));
            }
            if (count($entities) === $limit) {
                $links['next'] = $this->getEntitiesUrl($filters, $orders, $limit, $offset + $limit);
            }
        }
        $di = Di::getInstance();
        $di->slim->response->header('X-Json-Api', json_encode(['links' => $links]));
        $this->response($entities);
    }

}
