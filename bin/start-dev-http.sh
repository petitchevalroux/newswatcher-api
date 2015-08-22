#!/bin/bash

# Start the docker container using the repository as volume
# it allow to edit php file on host and see changes on running container
DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)

DOCKER_IMAGE=$(docker build . | egrep -o 'Successfully built [a-zA-Z0-9]+' | sed -e 's~Successfully built ~~')
echo $DOCKER_IMAGE

docker run -d --name newswatcher-db -e MYSQL_ROOT_PASSWORD=qmcHwhHNPjfnOM1y mysql:latest
docker run -d --name newswatcher-api --link newswatcher-db:db -p 8080:80 -v $DIR:/data/http $DOCKER_IMAGE
