Requirements
=======================

Operating Systems
-----------------

- Debian
- Ubuntu
- CentOS
- MacOSX

PHP
---

AwMailer requires PHP 5.4 or later with the following extensions compiled and enabled:

- Process Control (PCNTL)
- PDO with MySQL driver
- MongoDB
- XSL**

** XSL is used only if you need to generate the documentation of source code using PHPdocumentor.

Databases
---------

AwMailer uses 2 database systems, MySQL for store services, ip addresses and campaigns, and MongoDB for store the mass data such as queue list, the recommended version for this systems are MySQL 5.5 or later and MongoDB 1.5 or later.

NodeJS and Aglio
----------------

AwMailer use the Aglio app (written in NodeJS) to generate the API documentation of the current installation to be accessible by a web browser and used as reference for integration.