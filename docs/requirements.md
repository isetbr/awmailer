Requirements
=======================

Operating Systems
-----------------

- Debian
- Ubuntu
- CentOS

or any other linux distro.

PHP
---

AwMailer requires PHP 5.4 or later with the following extensions compiled and enabled:

- Process Control (PCNTL)
- POSIX functions
- PDO with MySQL driver
- MongoDB

Mailing Service
---------------

You will need a mailing service configured in your PHP, such as postfix, exim or any other.

Databases
---------

AwMailer uses 2 database systems, MySQL for store services, ip addresses and campaigns, and MongoDB for store the mass data such as queue list, the recommended version for this systems are MySQL 5.5 or later and MongoDB 1.5 or later.

NodeJS and Aglio
----------------

AwMailer use the Aglio app (written in NodeJS) to generate the API documentation of the current installation to be accessible by a web browser and used as reference for integration, the install notes about NodeJS and Aglio you can see [here](support/nodejs-aglio.md)