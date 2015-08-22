<?php

namespace NwApi\Libraries;

use NwApi\Di;
use Slim\Slim;
use Doctrine\ORM\Mapping\ClassMetadata;
use NwApi\Controllers\RestEntities as Controller;

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
        $application->get($url, function () use ($meta,$controller) {
            $controller->getEntities($meta);
        });
        // Create entity
        $application->post($url, function () use ($meta,$controller) {
            $controller->createEntity($meta);
        });
        // Get entity
        $application->get($url.'/:id', function ($id) use ($meta,$controller) {
            $controller->getEntity($meta, $id);
        });
        // Update entity
        $application->put($url.'/:id', function ($id) use ($meta,$controller) {
            $controller->updateEntity($meta, $id);
        });
        // Patch entity
        $application->patch($url.'/:id', function ($id) use ($meta,$controller) {
            $controller->patchEntity($meta, $id);
        });
        // Delete entity
        $application->delete($url.'/:id', function ($id) use ($meta,$controller) {
            $controller->deleteEntity($meta, $id);
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
    protected function getNamespace(ClassMetadata $meta)
    {
        reset($meta->table);
        list(, $namespace) = each($meta->table);
        reset($meta->table);

        return $namespace;
    }
}
