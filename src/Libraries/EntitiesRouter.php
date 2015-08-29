<?php

namespace NwApi\Libraries;

use NwApi\Di;
use Slim\Slim;
use Doctrine\ORM\Mapping\ClassMetadata;
use NwApi\Controllers\RestEntities as Controller;
use NwApi\Entities\Entity;
use Exception;

class EntitiesRouter extends Singleton
{
    private function getEntitiesMetas()
    {
        $di = Di::getInstance();

        return $di->em->getMetadataFactory()->getAllMetadata();
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
        $namespace = $this->getNamespace($meta);
        $url = '/'.$namespace;
        // Fetch entities route
        $application->get($url, function () use ($meta, $controller) {
            $controller->getEntities($meta);
        });
        // Create entity
        $application->post($url, function () use ($meta, $controller) {
            $controller->createEntity($meta);
        });
        $entityPath = $url.'/:'.implode('/:', $meta->identifier);
        // Get entity
        $application->get($entityPath, function () use ($meta, $controller) {
            $controller->getEntity($meta, func_get_args());
        });
        // Update entity
        $application->put($entityPath, function () use ($meta, $controller) {
            $controller->updateEntity($meta, func_get_args());
        });
        // Patch entity
        $application->patch($entityPath, function () use ($meta, $controller) {
            $controller->patchEntity($meta, func_get_args());
        });
        // Delete entity
        $application->delete($entityPath, function () use ($meta, $controller) {
            $controller->deleteEntity($meta, func_get_args());
        });

        return $application;
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
