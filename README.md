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

###Associate an existing source to an existing user ###
Query:

```
POST /sources_twitter/1/users/1 HTTP/1.1
Host: 192.168.99.100:8081
Content-Type: application/json; charset=UTF-8
```

Answer:

```
HTTP/1.1 201 Created

{
  "sources_twitter": {
    "id": "1"
  },
  "users": {
    "id": "1"
  }
}
```

###List source's users ###
Query:

```
GET /sources_twitter/1/users HTTP/1.1
Host: 192.168.99.100:8081
Content-Type: application/json; charset=UTF-8
```

Answer:

```
HTTP/1.1 200 OK

[
    {
        "id": 1,
        "name": "petitchevalroux"
    }
]
```

###Link article to user ###
Query:

```
POST /articles_users HTTP/1.1
Host: 192.168.99.100:8081
Content-Type: application/json; charset=UTF-8
{"user":1,"article":1}
```

Answer:

```
HTTP/1.1 200 OK

{
  "article": {
    "id": 1,
    "url": "http://dev.petitchevalroux.net",
    "urlHash": "1403472b2e77a13211ac0c3035952ac2",
    "title": null
  },
  "user": {
    "id": 1,
    "name": "petitchevalroux",
    "twitterId": null,
    "twitterToken": null,
    "twitterTokenSecret": null
  },
  "status": 0
}
```

###Get user's articles###

Query :

```
GET /articles_users?filters[user]=1 HTTP/1.1
Host: 192.168.99.100:8081
Content-Type: application/json; charset=UTF-8
```

Answer :

```
HTTP/1.1 200 OK
[
    {
        "article": {
            "id": 2,
            "url": "http://example.com/",
            "urlHash": "e49e31fb7338f1fa0cf7fd9fb205d453",
            "title": "Example Domain"
        },
        "user": {
            "id": 1,
            "name": "name",
            "twitterId": "id",
            "twitterToken": "token",
            "twitterTokenSecret": "secret"
        },
        "status": 0
    }
]