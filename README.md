AwMailer - The Awesome Mailer Service
=====================================

The AwMailer is a Mailer Service API written entire in pure PHP language, it offers a flexible application that contains a daemon that runs as a background service in your server and start isolated and uncoupled instances of awmailer when you start the send process of an campaign, it grants to you high availability of the mail service, full control over campaigns running and a generic API that can be used integrated in a variety of applications.

Features
--------

- Written in pure PHP code
- Flexible API
- Campaign management
- Custom headers and variables
- Process management
- Background service
- Independent send process
- Security by IP address and authentication keys
- Built-in API and Source Code documentation
- Dedicated service IP address (coming soon)
- Outgoing IP address balancing (coming soon)

Requirements
------------

- Debian/Ubuntu/MacOSX or others OS's based on Linux/Unix distros
- Apache 2.2+
- PHP 5.4+
- MySQL 5.5+
- MongoDB 1.5+

** More details are in the [requirements doc](docs/requirements.md).

Installation
------------

Please see the [installation](docs/installation.md) document of this repository.

Documentation
-------------

You can see the sample API documentation of latest release [here](blueprint.md).

Changelog
---------

- `v1.0.1-stable:` Fixed progress routines and binaries generation with a custom ini file
- `v1.0.0-stable:` First stable release
- `v0.5.1-beta:` Fixed encoding errors on API requests and responses, fixed notification_url removal and fixed some bugs on API resource routes

See full changelog [here](CHANGELOG.md).