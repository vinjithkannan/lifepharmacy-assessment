# README

# Development Environment Setup

* Development Setup is dockerized
* Container managed with in repo itself
* Docker container is managing 4 container
    * Lifepharmacy PHP
    * Lifepharmacy NGINX
    * Lifepharmacy MYSQL
    * Lifepharmacy REDIS

## Build and Run Containers

### Follow the commands to build and run the containers
* Download and install Docker (https://docs.docker.com/engine/install/)
* Clone the repo (https://github.com/vinjithkannan/lifepharmacy-assessment.git)

* ```shell
    git clone https://github.com/vinjithkannan/lifepharmacy-assessment.git
    
    cd <path of the directory cotais the source>   
    \> docker-compose up --build  # only for very first time    
       # once build completed terminal will show the three containers are running
       # from next time up and run only need
    \> docker-compose up
    
    docker exec -it lifepharmacy-php bash
    /var/www/html#  composer install  
    /var/www/html#  php artisan migrate:fresh --seed 
    ```
* Once containers where up, dev env will able to browse with url
#### (http://localhost:8080)

### POSTMAN COLLECTION
- `Life Pharmacy.postman_collection` included in the repo and it could be import as Apis collection

- `
Add enviornment variable
{{host}} : http://localhost:8080
`
- Set the  following lines in script section of login/register endpoints
- `var jsonData = pm.response.json();
  pm.globals.set("api_token", jsonData.token);
`
### Default Users accounts 
Admin

`Email: ecomadmin@lifepharmacy.com`
`Password: ecomadmin`

Customer

`Email: customer1@lifepharmacy.com`
`Password: customer1`