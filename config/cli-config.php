<?php

require dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
use Doctrine\ORM\Tools\Console\ConsoleRunner;

return ConsoleRunner::createHelperSet(NwApi\Di::getInstance()->em);
