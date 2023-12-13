# Nette backend API base
This project is a base template for a backend API created using the **[Nette PHP framework](https://nette.org)**.

## Get started
1) Clone this repository
2) Run ``composer install``
3) Open the *app/config/main.neon* file and look for all entries under the **includes** config ending with *.local.neon*. You need to create these files and add them to the *app/config/* directory
4) Create a database and add it's details to the *database.local.neon* file (default *root@localhost: nette-api*)
5) Now setup your local server and you will be able to access the api at **\<domain>/api**

## Basic info
* The base router setup is **\<domain>/api/\<action>/\<id>**. This can be changed in the **App/Router/RouterFactory** class
* The base template database model can be found in the *db/* folder. This includes an *init-db* sql file as well as a *mysql/database.mwb* **[MySql Workbench](https://www.mysql.com/products/workbench/)** file
* The database model includes:
  * An ***authentication*** table which has the *id*, *code*, *api_key* and *ip* columns and is used for saving info for authenticating requests

## Request authentication
* All requests to the api must be authenticated with the ***apiId*** and ***apiKey*** parameters (except when using the *master API key* in which case the apiId is not needed):
  * ***API id*** - Id of the API access (string max length 12)
  * ***API key*** - API key for the API access (string max length 32)
* ***Client IP* is also checked against the authentication database table**
* ***Master API key*** authentication is enabled by default
* ***Master API key*** is specified in the *app/config/keys.local.neon* file under *parameters:keys:api:masterKey* (you need to set your own master key)