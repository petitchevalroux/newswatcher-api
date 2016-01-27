<?php

namespace NwApi\Libraries;

use NwApi\Di;
use Slim\Slim;
use Doctrine\ORM\Mapping\ClassMetadata;
use NwApi\Controllers\JsonApiController as Controller;
use NwApi\Entities\Entity;
use Exception;

class RestDoctrineRouter
{
    private function getEntitiesMetas()
    {
        $di = Di::getInstance();

        return $di->em->getMetadataFactory()->getAllMetadata();
    }

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     *
     * @return ClassMetadata
     */
    private function getEntityMeta($className)
    {
        $di = Di::getInstance();

        return $di->em->getMetadataFactory()->getMetadataFor($className);
    }

    public function addRoutes(Slim $application, Controller $controller)
    {
        $metas = $this->getEntitiesMetas();
        foreach ($metas as $meta) {
            $application = $this->addRoutesFromMeta($application, $meta, $controller);
        }

        return $application;
    }

    private function addRoutesFromMeta(Slim $application, ClassMetadata $meta, Controller $controller)
    {
        $entitiesRoute = $this->getEntitiesRoute($meta);
        // Fetch entities route
        $application->get($entitiesRoute, function () use ($meta, $controller) {
            $controller->getEntities($meta);
        });
        // Create entity
        $application->post($entitiesRoute, function () use ($meta, $controller) {
            $controller->createEntity($meta);
        });
        $entityRoute = $this->getEntityRoute($meta);
        // Get entity
        $application->get($entityRoute, function () use ($meta, $controller) {
            $controller->getEntity($meta, func_get_args());
        });
        // Update entity
        $application->put($entityRoute, function () use ($meta, $controller) {
            $controller->updateEntity($meta, func_get_args());
        });
        // Patch entity
        $application->patch($entityRoute, function () use ($meta, $controller) {
            $controller->patchEntity($meta, func_get_args());
        });
        // Delete entity
        $application->delete($entityRoute, function () use ($meta, $controller) {
            $controller->deleteEntity($meta, func_get_args());
        });

        // Handling associated entities
        foreach ($meta->getAssociationMappings() as $aName => $aData) {
            $aTargetClass = $meta->getAssociationTargetClass($aName);
            $aMeta = $this->getEntityMeta($aTargetClass);
            $aEntitiesRoute = $this->getEntitiesRoute($aMeta, $entityRoute);
            // Create associated entity
            // allow to create entity and link source together
            // POST /articles/1/tags will fetch article 1, create tag entity and
            // associate it to article 1
            $application->post($aEntitiesRoute, function () use ($meta, $aMeta, $controller, $aData) {
                $controller->createEntity($aMeta, $aData['fieldName'], $meta, func_get_args());
            });

            $aEntityRoute = $this->getEntityRoute($aMeta, $entityRoute);
            $application->post($aEntityRoute, function () use ($meta, $aMeta, $controller, $aData) {
                $controller->associateEntities($aMeta, $aData['fieldName'], $meta, func_get_args());
            });
        };

        return $application;
    }

    /**
     * Return entities' route.
     * 
     * example: /articles
     * 
     * @param ClassMetadata $meta
     * @param string        $prefix
     *
     * @return string
     */
    private function getEntitiesRoute(ClassMetadata $meta, $prefix = '')
    {
        $namespace = $this->getNamespace($meta);

        return $prefix.'/'.$namespace;
    }

    /**
     * Return entity's route.
     *
     * example: /articles/:articles_id
     *
     * @param ClassMetadata $meta
     * @param type          $prefix
     *
     * @return type
     */
    private function getEntityRoute(ClassMetadata $meta, $prefix = '')
    {
        $namespace = $this->getNamespace($meta);

        return $this->getEntitiesRoute($meta, $prefix).'/:'.$namespace.'_'.implode('/:', $meta->identifier);
    }

    /**
     * Return namespace (first folder of the route).
     *
     * @param ClassMetadata $meta
     *
     * @return string
     */
    public function getNamespace(ClassMetadata $meta)
    {
        reset($meta->table);
        list(, $namespace) = each($meta->table);
        reset($meta->table);

        return $namespace;
    }

    /**
     * Return entity location.
     *
     * @param ClassMetadata           $meta
     * @param \NwApi\Libraries\Entity $entity
     *
     * @return string
     *
     * @throws Exception
     */
    public function getEntityLocation(ClassMetadata $meta, Entity $entity)
    {
        $location = '/'.$this->getNamespace($meta);
        foreach ($meta->identifier as $field) {
            $value = false;
            if ($meta->hasField($field)) {
                $value = $entity->{$field};
            }
            if ($meta->hasAssociation($field) && $meta->isAssociationWithSingleJoinColumn($field)) {
                $idField = $meta->associationMappings[$field]['joinColumns'][0]['referencedColumnName'];
                $value = $entity->{$field}->{$idField};
            }
            if ($value !== false) {
                $location .= '/'.rawurlencode($value);
            } else {
                throw new Exception('Unable to get value for identifier '.$field.' on '.$meta->name);
            }
        }

        return $location;
    }
}
