CHANGELOG
=========
- `v1.2.0-stable:`
    * Added status resource in API
    * Added notification by email when the status resource detects that API is down
    * Added configuration option to set a custom delay of daemon loop (default 5)
- `v1.1.1-stable:`
    * Fixed Return-Path bug that prevent invalid mails to return to the specified bounce mail
    * Turned all header keys to lowercase
- `v1.1.0-stable:`
    * Added information messages in make script
    * Compiling binaries with dynamic php binary path
    * Compiler generates a custom php.ini file in config directory
    * Implemented init.d script to handle awmailer daemon instance
    * Implemented validation of queue structure based on campaign creation configuration
    * Improved daemon fork with session and handling it with an system user
    * Improved daemon touching a PID file to be handled by linux kernel
    * Improved daemon and service using runtime.ini file generated by compiler
    * Fixed daemon service handler to don't allow multiple instances of daemon
    * Fixed dependency checker to verify if pcntl extension is installed
    * Fixed daemon crash and process defunct when tts instance is terminated
    * Fixed routine to get the correct id of a new resource when inserted in database 
    * Fixed default notification route
    * Fixed unit tests to generate random service keys avoiding test conflicts
    * Replaced doc generator library by ApiGen
    * Removed headlines from build scripts
- `v1.0.3-stable:` 
    * Fixed compiler for use PHP cli direct output
- `v1.0.2-stable:`
    * Fixed make script to create folder for process logs
    * Fixed service process log handler
    * Removed reference wildcard from gateway in QueueCollection
- `v1.0.1-stable:`
    * Binaries now uses a custom php.ini file
    * Improvements in progress calculation routine
    * Fixed parsing of blueprint file to generate API documentation
- `v1.0.0-stable:`
    * Added this documentation
    * Added support files for apache web server
    * Changed project name from M4A1 to AwMailer
    * Fixed phpdoc dist file to not parse test files
    * Fixed composer.json file to solve conflict with app files
    * Implemented build system
    * Removed documentation files from repository
- `v0.5.1-beta:`
    * Added test suite to source files
    * Implemented application kernel to parse json responses correctly
    * Fixed trailing slashes in routes
    * Fixed some encoding errors on requests
    * Removed notification url validation, it's optional right now
- `v0.5.0-beta:`
    * Implemented logger and error handler in daemon, service and processes
    * Improved api callback 
    * Improved progress calculation making routine to not use counters
    * Fixed error when try to update campaign user_vars and user_headers
- `v0.4.2-beta:`
    * Improved remove routine on campaign resource
    * Implemented HTTP X-Status-Code in API responses
    * Fixed some HTTP response codes
    * Overridden default error handler to return HTTP responses with the error occurred
    * Removed some fields on get queue in campaign resource
- `v0.4.1-beta:`
    * Improved api callback sending service key and token on request
    * Removed IP address authentication field from application configuration file
- `v0.4.0-beta:`
    * Improved application architecture organizing source hierarchy and structure
    * Fixed doc block typos
- `v0.3.4-beta:`
    * Implemented api callback resource
    * Implemented service information sending in api callback requests
    * Implemented notification url in service resource
    * Improved campaign resource to not start an finished or stopped campaign
    * Improved source architecture converted entities to resources
    * Fixed service starting quietly
    * Removed authentication session keys from application configuration file
- `v0.3.3-beta:`
    * Added blueprint api documentation
    * Added flag on cli scripts to prevent all command line output of daemon and service
    * Implemented default API homepage
    * Improved owner security in API resources
    * Improved campaign model validation
    * Fixed some security issues
- `v0.3.2-alpha/beta:`
    * Fixed authentication scope
    * Removed session references in application
- `v0.3.1-alpha:` 
    * Fixed service starting
- `v0.3.0-alpha:`
    * Added Monolog library and it configurable options to application configuration file
    * Implemented logger for API calls
    * Implemented logger for daemon and service behaviours
- `v0.2.2-alpha:`
    * Improved package size in send process to reduce the memory usage
    * Implemented pagination to get the campaign queue
- `v0.2.1-alpha:`
    * Fixed database connection on service and daemon
- `v0.2.0-alpha:`
    * Implemented Service Provider for Zend Cache library
    * Implemented support to get campaign status from cache while in processing
    * Implemented control flag to daemon force campaign to stop
- `v0.1.0-alpha:`
    * Improved daemon to work with cache while the campaign is processing
    * Fixed database disconnection in daemon
- `v0.0.2-alpha:`
    * Fixed header parse in service
- `v0.0.1-alpha:` First alpha release