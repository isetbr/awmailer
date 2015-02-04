FORMAT: X-1A

HOST: http://domain.com/api/

#AwMailer - The Awesome Mailer Service

# Group Introduction
The AwMailer is a software developed for provide a mail service which can be used by all services of iSET.
The proposal of AwMailer is provide a mail tool that runs a daemon as a observer for new services to be triggered, this services runs natively on Linux servers independent of each others.

This document covers the core resources you can use to manipulate your services inside the API that it's based on REST principles.

>**Attention:** All values such as service keys, tokens, campaign keys, ip addresses used in each *requests, responses and headers* in the following methods are examples, when you use this API new values will be generated and returned to you, not forget to replace this values on next requests.


# Group HTTP Request Basics
##Allowed HTTP methods

- `GET`    - Used to get information about an object in API.
- `POST`   - Used to create new objects in API and run processes.
- `PUT`    - Used to update and object in API.
- `DELETE` - Used to remove and object in API.

##Typical Server Responses

- 200 `OK` - The request was successful (some API calls may return 201 instead).
- 201 `Created` - The request was successful and a resource was created.
- 204 `No Content` - The request was successful but there is no representation to return (that is, the response is empty).
- 400 `Bad Request` - The request could not be understood or was missing required parameters.
- 401 `Unauthorized` - Authentication failed or user does not have permissions for the requested operation.
- 403 `Forbidden` - Access denied.
- 404 `Not Found` - Resource was not found.
- 405 `Method Not Allowed` - Requested method is not supported for the specified resource.
- 501 `Method Not Implemented` - The method requested was not implemented yet.
- 503 `Service Unavailable` - The service is temporary unavailable (e.g. scheduled Platform Maintenance). Try again later.

>**Note:** AwMailer API works with json in requests and responses then, all HTTP calls must be include a header *Content-Type: application/json*, otherwise the API will return a HTTP 400 - Bad Request response.

# Group Authentication
<a id="authentication"></a>
## IP Address

The authentication system based on client IP address, verify in all HTTP calls the client IP address and validate it in API database which have a white list of IP addresses allowed to consume API, to allow a new IP address, see the *ipaddress* resource below.

## Service-Key and Auth-Token

In some API methods you will have to send in you HTTP request headers two params that will authenticate and performr your request in the API.

- `Auth-Service-Key` - Your service key provided on service registration moment.
- `Auth-Token` - The token returned by API when you register a new service.

If one of these headers are incorrect, the API will return a HTTP 401 - Unauthorized response.

# Group Services
<a id="services"></a>

A *service* in API is any application that will consume API resources and it need to are registered to get a authorization token that is required to consume some resources.

## /service
### GET
Retrieve all Services 

+ Request (application/json)
    
+ Response 204

+ Response 200 (application/json)

        [{
          "name": "First Service", "key": "svc1", "notification_url": "http://service.com/callback/notifications"
        },
        {
          "name": "Second Service", "key": "svc2", "notification_url": "http://service2.com/callback/notifications"
        },
        {
          "name": "Third Service", "key":"svc3", "notification_url": "http://service3.com/callback/notifications"
        }]

### POST
Create a new Service

+ Parameters
    + name (required, string, `Service Name`) ... The Service name
    + key (required, string, `myservicekey`) ... The service key that will used to authenticate your HTTP requests
    + notification_url (optional, string, `http://myservice.com/callback/notification`) ... The callback URL for send API notifications

+ Request (application/json)
        
        {
          "name": "Service Name",
          "key": "myservicekey",
          "notification_url": "http://myservice.com/callback/notifications"
        }

+ Response 201 (application/json)

        { 
          "success": 1, 
          "key": "myservicekey", 
          "token": "MyServiceToken" 
        }
        
+ Response 400 (application/json)

        {
          "success": 0,
          "error": "Invalid service, see details for more information",
          "details": "The detailed information about error"
        }
        
+ Response 500 (application/json)

        {
          "success": 0,
          "error": "The error message"
        }

## /service/{key}
### GET
Get a Service

+ Request (application/json)
        
+ Response 200 (application/json)

        {
          "name": "First Service",
          "key": "svc1"
        }

+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Service not found"
        }
        

### PUT
Update a Service

+ Parameters
    + name (optional, string, `Service Name`) ... The name of service
    + key (optional, string, `myservicekey`) ... The service key that will used to authenticate your HTTP requests
    + notification_url (optional, string, `http://myservice.com/callback/notification`) ... The callback URL for send API notifications

+ Request (application/json)

        {
          "name": "Service Name",
          "key": "myservicekey",
          "notification_url": "http://myservice.com/callback/notification" 
        }

+ Response 200 (application/json)

        {
          "success": 1,
          "name": "Service Name",
          "key": "myservicekey"
        }
        
+ Response 400 (application/json)

        {
          "success": 0,
          "error": "Invalid service, see details for more information",
          "details": "The detailed information about error"
        }
        
+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Service not found"
        }
        
+ Response 500 (application/json)

        {
          "success": 0,
          "error": "The error message"
        }
        
### DELETE
Remove a Service

**Warning:** This action *permanently* destroy all information about service and it actions from API database.

+ Request (application/json)

+ Response 200 (application/json)

        {
          "success": 1
        }
        
+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Service not found"
        }

+ Response 500 (application/json)

        {
          "success": 0,
          "error": "Unknow error"
        }

# Group Ip Addresses

## /ipaddress
### GET
Retrieve all allowed IP addresses

+ Request (application/json)

+ Response 200 (application/json)

        [
          "192.168.0.1",
          "192.168.0.2",
          "192.168.0.3"
        ]
        
+ Response 204
        
### POST
Allow new IP address to consume API

+ Parameters
    + ipaddress (required,string,`127.0.0.1`) ... The Ip address that you wish to allow

+ Request (application/json)

        {
          "ipaddress": "192.168.0.1"
        }

+ Response 201 (application/json)

        {
          "success": 1
        }
        
+ Response 400 (application/json)

        {
          "success": 0,
          "error": "Invalid IP address, see details for more information",
          "details": "The detailed information about error"
        }
        
+ Response 500 (application/json)

        {
          "success": 0,
          "error": "The error message"
        }

## /ipaddress/{ipaddress}
### DELETE
Revoke access from an IP address

+ Request (application/json)

+ Response 200 (application/json)

        {
          "success": 1
        }
        
+ Response 404 (application/json)

        {
          "success": 0,
          "error": "IP address not found"
        }
        
+ Response 500 (application/json)

        {
         "success": 0,
         "error": "The error message"
        }

# Group Campaigns
## /campaign
### GET
Retrieve all campaigns

+ Request (application/json)

+ Response 501

### POST
Create a new campaign

+ Parameters
    + subject (required, string) ... The campaigns subject
    + body (required, string) ... The body of email
    + headers (required, array) ... The headers to send in each all emails
    + user_vars (optional, integer) ... Flag for parse queue with body variables
    + user_headers (optional, integer) ... Flag for set custom headers by each destination
    + external (required, integer) ... The identification of this campaign in client application.
    + additional_info (optional, string) ... The additional information for client use to indentify the campaign

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken
    
    + Body
    
            {
              "subject": "The subject of mail",
              "body": "The body of email",
              "headers": {
                "Content-Type": "text/html",
                "Bounce-Mail": "mybounce@domain.com"
              },
              "user_vars": 1,
              "user_headers": 1,
              "external": 123,
              "additional_info": "myreferenceinformation"
            }

+ Response 201 (application/json)

        {
          "success": 1,
          "campaign": "45ad434daaf7bf22e45c8938fb745b45"
        }
        
+ Response 400 (application/json)

        {
          "success": 0,
          "error": "Invalid campaign, see details for more information",
          "details": "The detailed information about error"
        }
        
+ Response 500 (application/json)

        {
          "success": 0,
          "error": "The error message"
        }
        
## /campaign/{key}
### GET
Get details of an campaign

+ Request (application/json)
    
    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken

+ Response 200 (application/json)

        {
          "id": "1",
          "key": "45ad434daaf7bf22e45c8938fb745b45",
          "total": 0,
          "sent": 0,
          "fail": 0,
          "progress": 0,
          "status": 0,
          "subject": "The subject of email",
          "body": "The body of email",
          "headers": {
            "Content-Type": "text/html",
            "Bounce-Mail": "mybounce@domain.com"
          },
          "user_vars": 1,
          "user_headers": 1,
          "date": "2014-08-05",
          "external": "123",
          "additional_info": "myreferenceinformation",
          "pid": null,
        }

+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Campaign not found"
        }
        
### PUT
Update an campaign

+ Parameters
    + subject (required, string) ... The campaigns subject
    + body (required, string) ... The body of email
    + headers (required, array) ... The headers to send in each all emails
    + user_vars (optional, integer) ... Flag for parse queue with body variables
    + user_headers (optional, integer) ... Flag for set custom headers by each destination
    + external (required, integer) ... The identification of this campaign in client application.
    + additional_info (optional, string) ... The additional information for client use to indentify the campaign

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken
    
    + Body
    
            {
              "subject": "The subject of mail",
              "body": "The body of email",
              "headers": {
                "Content-Type": "text/html",
                "Bounce-Mail": null
              },
              "user_vars": 0,
              "user_headers": 0,
              "external": 456,
              "additional_info": "myreferenceinformation"
            }

+ Response 200 (application/json)

        {
          "success": 1,
          "campaign": "45ad434daaf7bf22e45c8938fb745b45"
        }
        
+ Response 400 (application/json)

        {
          "success": 0,
          "error": "Invalid campaign, see details for more information",
          "details": "The detailed information about error"
        }

+ Response 500 (application/json)

        {
          "success": 0,
          "error": "The error message"
        }
        
### DELETE
Remove an campaign

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicetoken
            Auth-Token: MyServiceToken

+ Response 200 (application/json)

        {
          "success": 1
        }

+ Respose 404 (application/json)

        {
          "success": 0,
          "error": "Campaign not found"
        }

+ Response 409 (application/json)

        {
          "success": 0,
          "error": "Campaign running or in process"
        }

+ Response 500 (application/json)

        {
          "success": 0,
          "error": "The error message"
        }

## /campaign/{key}/status
### GET
Get the status information of an campaign

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken

+ Response 200 (application/json)

        {
          "id" : "1",
          "key" : "45ad434daaf7bf22e45c8938fb745b45",
          "total" : 0,
          "sent" : 0,
          "fail" : 0,
          "progress" : 0,
          "status" : 0,
          "external" : "123",
          "pid" : null,
          "cache": 1
        }
        
+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Campaign not found"
        }
        
## /campaign/{key}/queue
### GET
Retrieve the current email queue of an campaign

+ Request (application/json)
    
    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken

+ Response 200 (application/json)
        
        ["email1@domain.com","email2@domain.com","email3@domain.com"]
        
+ Response 204

+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Campaign not found"
        }

### PUT
Update an campaign queue

+ Parameters
    + Perform-Delete (optional, integer) ... A flag to set the operation, to include a stack of emails in queue or remove a stack of emails from it.
    
        <br /><br />This flag must be sent as a request header.

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken
            Perform-Delete: 0
            
    + Body
    
            # Simple Queue
            {"stack":["person1@domain.com","person2@domain.com","person3@domain.com"]}
    
            # Custom Queue
            {
              "stack":[
                {
                  "email":"person1@domain.com",
                  "vars":{
                    "MyVar":"My Value",
                    "MyVar2":"My Value 2"
                  },
                  "headers":{
                    "Custom-Header":"Custom-Value",
                    "Custom-Header2":"Custom Value 2"
                  }
                },
                {
                  "email":"person2@domain.com",
                  "vars":{
                    "MyVar":"My Value",
                    "MyVar2":"My Value 2"
                  },
                  "headers":{
                    "Custom-Header":"Custom-Value",
                    "Custom-Header2":"Custom Value 2"
                  }
                },
                {
                  "email":"person3@domain.com",
                  "vars":{
                    "MyVar":"My Value",
                    "MyVar2":"My Value 2"
                  },
                  "headers":{
                    "Custom-Header":"Custom-Value",
                    "Custom-Header2":"Custom Value 2"
                  }
                }
              ]
            }

+ Response 200 (application/json)

        {
          "success": 1
        }

+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Campaign not found"
        }

+ Response 500 (application/json)

        {
          "success": 0,
          "error": "The error message"
        }

## /campaign/{key}/start
### POST
Start a campaign send process

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken

+ Response 204

+ Response 404 (application/json)

        {
          "success": 0,
          "error": "Campaign not found"
        }

+ Response 500 (application/json)

        {
          "success": 0,
          "error": "Campaing was done or stopped"
        }

## /campaign/{key}/pause
### POST
Pause a campaign send processs

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken

+ Response 204

+ Response 404 (application/json)

    {
      "success": 0,
      "error": "Campaign not found"
    }

+ Response 500 (application/json)

    {
      "success": 0,
      "error": "Campaign must be started before pause"
    }

## /campaign/{key}/stop
### POST
Stop a campaign send processs

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken

+ Response 204

+ Response 404 (application/json)

    {
      "success": 0,
      "error": "Campaign not found"
    }
    
+ Response 500 (application/json)

    {
      "success": 0,
      "error": "Campaign must be started before stop"
    }

## /campaign/status
### POST
Get status from multiple campaigns

+ Parameters
    + campaigns (required, array) ... A array with campaign keys

+ Request (application/json)

    + Headers
        
            Auth-Service-Key: myservicekey
            Auth-Token: MyServiceToken
            
    + Body
    
            {
              "campaigns": [
                "45ad434daaf7bf22e45c8938fb745b45",
                "18923n13n9dw7qwhnjbabsd1lmvz1823"
              ]
            }

+ Response 200 (application/json)

        {
          "45ad434daaf7bf22e45c8938fb745b45": {
            "success": 1,
            "id" : "1",
            "key" : "45ad434daaf7bf22e45c8938fb745b45",
            "total" : 0,
            "sent" : 0,
            "fail" : 0,
            "progress" : 0,
            "status" : 0,
            "external" : "123",
            "pid" : null,
            "cache": 1
          },
          "18923n13n9dw7qwhnjbabsd1lmvz1823": {
            "success": 0,
            "error": "Campaign not found"
          }
        }
        
+ Response 400 (application/json)

    {
      "success": 0,
      "error": "You must send an array of campaign keys"
    }

# Group Status

## /status
### GET
Get the currently API status

+ Request (application/json)

+ Response 200 (text/html)

    API is running correctly
        
+ Response 503

    API is down, please contact the administrator

# Group Notifications

The AwMailer can send to any service that uses API notifications about processes events in API.

The notifications resource of API send to notification url of service a HTTP POST request with the following structure:

  {
    "resource": "resourcename",
    "context": "thecontext"
  }

See next sections for examples of notifications organized by specific resource:

##Campaign Notifications

The `campaign` resource will notificate you about the following contexts:

- `process_started` - A send process was successfully started
- `process_paused` - A send process was successfully paused
- `process_stopped` - A send process was successfully stopped
- `process_done` - The process was done
- `process_error` - An error occurred in send process, request logs.

Example:
  
  {
    "resource": "campaign",
    "context": "process_started",
    "key": "6c8bff2269a89ef7ec3cf87e72be656f"
  }

## Answering a notification

When API send to you a notification about an event occurred in a resource, after you process this notification in your system, please, print the following statement in the body of request to say to API that the notification has been processed.

    {"result":"ok"}

# Group  Changelog

- `v1.2.0-stable:` Added resource status to get the current status of API daemon with notifications by email and configurable delay of daemon loop
- `v1.1.1-stable:` Fixed Return-Path bug that prevent invalid mails to return to the specified bounce mail
- `v1.1.0-stable:` A lot of improvements in daemon, service and resources, recommended version to use in production
- `v1.0.3-stable:` Fixed compiler for use PHP cli direct output
- `v1.0.2-stable:` Fixed Makefile, process logs and minor bugs
- `v1.0.1-stable:` Fixed progress routines and binaries generation with a custom ini file
- `v1.0.0-stable:` First stable release
- `v0.5.1-beta:` Fixed encoding errors on API requests and responses, fixed notification_url removal and fixed some bugs on API resource routes
- `v0.5.0-beta:` Fixed some bugs in API resources, fixed progress counter, removed validation of notification URL and implemented some logs in campaign process, service and daemon
- `v0.4.2-beta:` Fixed HTTP status codes on API requests, overrides default error handler, optimised remove campaign process and removed unnecessary fields on get queue from an campaign
- `v0.4.1-beta:` Service information sent in notification callbacks and removed session keys from configuration file
- `v0.4.0-beta:` Refactored some API hierarchy structures and implemented notification callbacks
- `v0.3.3-beta:` Fixed some security issues in campaign resource and implemented campaign validations
- `v0.3.2-beta:` The first beta release with minor bugs
- `v0.3.2-alpha:` Optimised authentication service
- `v0.3.1-alpha:` Fix issue that cannot be start service logger
- `v0.3.0-alpha:` Implemented log, logging only daemon/service behaviour and API calls
- `v0.2.2-alpha:` AwMailer Service uses packages in sent process for reduce memory usage
- `v0.2.1-alpha:` Fixed some database connection issues in service, daemon e API
- `v0.2.0-alpha:` Implemented cached results when request the status of an campaign
- `v0.1.0-alpha:` Fixed database crash using cache to store service processes
- `v0.0.2-alpha:` Fixed header parser in service
- `v0.0.1-alpha:` First alpha release