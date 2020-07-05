[![Codacy Badge](https://app.codacy.com/project/badge/Grade/d4c026becca947f9acbaa1424ac600bf)](https://www.codacy.com/manual/ruiteix/sf-website-demo?utm_source=github.com&utm_medium=referral&utm_content=ruiteix/sf-website-demo&utm_campaign=Badge_Grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/d4c026becca947f9acbaa1424ac600bf)](https://www.codacy.com/manual/ruiteix/sf-website-demo?utm_source=github.com&utm_medium=referral&utm_content=ruiteix/sf-website-demo&utm_campaign=Badge_Coverage)

## Requirement

- Docker & docker compose
- Unix like system

## Installation

- Personalize gitlabl credentials in .env.local

```
GITLAB_LOGIN=<your login>
GITLAB_PASSWORD=<token>
```

- Initialize

`make init`

Make sure to personalize the docker-compose.override.yml

- Pull, build & start containers

`make up`

- Install dependances

`make install`

- Go to http://demo.docker.localhost/

Tools
-----

* Mail catcher : http://mail.demo.docker.localhost:81
