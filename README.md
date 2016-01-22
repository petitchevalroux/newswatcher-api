#Newswatcher storage api#

##Utility##

Running images without rebuilding (from the root of repository)

```bash
bin/start-dev-http.sh
```

Connect to the docker container

```bash
docker exec -it newswatcher-api bash
```

Update database schema (from the container)

```bash
cd /data/http && vendor/bin/doctrine orm:schema-tool:update --force
```

##Sources##
###Create a twitter source###
Query:

```
POST /sources_twitter HTTP/1.1
Host: 192.168.99.100:8081
Content-Type: application/json; charset=UTF-8
{"method":"user","accessTokenKey":"tokenKey","accessTokenSecret":"tokenSecret"}
```

Answer:

```
HTTP/1.1 201 Created
Location: /sources_twitter/1

{
  "id": 1,
  "method": "user",
  "consumerKey": "key",
  "consumerSecret": "secret",
  "accessTokenKey": "tokenKey",
  "accessTokenSecret": "tokenSecret"
}
```

###Create an article and associate it to an existing source###
Query:

```
POST /sources_twitter/1/articles HTTP/1.1
Host: 192.168.99.100:8081
Content-Type: application/json; charset=UTF-8
{"url":"http://petitchevalroux.net/index.html"}
```

Answer:

```
HTTP/1.1 201 Created
Location: /articles/1

{
  "id": 1,
  "url": "http://petitchevalroux.net/index.html",
  "urlHash": "e2bdebe9696bce525305d9cdb277fe10",
  "title": null
}
```
