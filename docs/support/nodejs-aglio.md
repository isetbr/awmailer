NodeJS and Aglio
================

The Aglio is a application written in NodeJS to generate documentation for RESTful based API's using a description model in a Markdown file. The Aglio is developed on top of ProtagonistJS, a wrapper for the Snow Crash library that parses its Markdown.

Installing NodeJS
-----------------

To install NodeJS you can download the packages for your operating system or compile it from the source code, all information about how to do this you can see on [NodeJS Official WebSite](http://nodejs.org/).

Installing Aglio
----------------

After you install NodeJS and certified that his are running correctly on your server, you will install aglio, then, run the command below to install it in your server.

```shell
# You may need run this as sudo
npm install -g aglio

# Run the following command to certify that aglio was installed correclty
aglio -h
```