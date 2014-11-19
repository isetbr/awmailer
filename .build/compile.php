<?php

# Initializing paths
$root_path = realpath(dirname(__FILE__) . '/../');
$bin_path = $root_path . '/bin/';
$daemon = $bin_path . "awd";
$service = $bin_path . "awmailer";
$service_handler = $bin_path . "awmailerctl";
$php_binary = trim(exec('which php'));
$app_config = parse_ini_file($root_path . '/app/config/application.ini',true);
$user = $app_config['service']['system.user'];
$php_ini = php_ini_loaded_file();
$ini_dest = $root_path . '/app/config/runtime.ini';

/////////////////////////////////////////
// COMPILING AWMAILER DAEMON
/////////////////////////////////////////

# Removing existing daemon
if (file_exists($daemon)) {
    unlink($daemon);
}

$content = <<<EOF
#!$php_binary -q
<?php require_once("$root_path/app/Daemon.php");
EOF;
$handle = fopen($daemon,"w");
fwrite($handle,$content);
fclose($handle);
unset($content);

/////////////////////////////////////////
// COMPILING AWMAILER SERVICE
/////////////////////////////////////////

# Removing existing service
if (file_exists($service)) {
    unlink($service);
}

$content = <<<EOF
#!$php_binary -q
<?php require_once("$root_path/app/Service.php");
EOF;
$handle = fopen($service,"w");
fwrite($handle,$content);
fclose($handle);
unset($content);

# Removing existing service handler
if (file_exists($service_handler)) {
    unlink($service_handler);
}

/////////////////////////////////////////
// COMPILING AWMAILER SERVICE HANDLER
/////////////////////////////////////////

$content = <<<EOF
#!/bin/bash
#
# init.d script with LSB support
#
# Copyright (c) 2007 Javier Fernandez-Sanguino <jfs@debian.org>
#
# This is free software; you may redistribute it and/or modify
# it under the terms of the GNU General Public License as
# published by the Free Software Foundation; either version 2,
# or (at your option) any later version.
#
# This is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License with
# the Debian operating system, in /usr/share/common-licenses/GPL;  if
# not, write to the Free Software Foundation, Inc., 59 Temple Place,
# Suite 330, Boston, MA 02111-1307 USA

PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
DAEMON=$daemon
D_USER=$user
NAME=awd
PIDFILE=/var/run/awmailer/$^NAME.pid

# if the executables do not exist -- display error
if test ! -x $^DAEMON; then
    echo "Could not find $^DAEMON"
    exit 0
fi

running_pid() {
# Check if a given process pid's cmdline matches a given name
    pid=$1
    name=$2
    [ -z "$^pid" ] && return 1
    [ ! -d /proc/$^pid ] &&  return 1
    cmd=`cat /proc/$^pid/cmdline | tr "\000" "\n"|head -n 1 |cut -d : -f 1`
    # Is this the expected server
    [ "$^cmd" != "$^name" ] &&  return 1
    return 0
}

running() {
# Check if the process is running looking at /proc
# (works for all users)

    # No pidfile, probably no daemon present
    [ ! -f "$^PIDFILE" ] && return 1
    pid=`cat $^PIDFILE`
    running_pid $^pid $^DAEMON || return 1
    return 0
}

start_daemon() {
    case "$(pgrep awd | wc -w)" in
        0)
            sudo -u $^D_USER awd > /dev/null 2>&1
            sleep 3
            pgrep awd > $^PIDFILE
            echo "OK"
            ;;
        1)
            echo "already running."
            exit 0
            ;;
        *)
            echo "error."
            exit 1
    esac
}

stop_daemon() {
    case "$(pgrep awd | wc -w)" in
        0)
            echo "not running."
            exit 0
            ;;
        1)
            rm -rf $^PIDFILE
            sleep 1
            killall awd
            echo "OK"
            ;;
        *)
            echo "error."
            exit 1
    esac

}

# depending on parameter -- startup, shutdown, restart
# of the instance and listener or usage display

case "$^1" in
    start)
        echo -n "Starting AwMailer Daemon: "
        start_daemon
        ;;
    stop)
        echo -n "Shutdown AwMailer Daemon: "
        stop_daemon
        ;;
    reload|restart)
        echo -n "Shutdown AwMailer Daemon: "
        stop_daemon
        echo -n "Starting AwMailer Daemon: "
        start_daemon
        ;;
    *)
        echo "Usage: $^0 start|stop|restart|reload"
        exit 1
esac
exit 0
EOF;

# parsing bash vars
$content = str_replace('$^','$',$content);

$handle = fopen($service_handler,"w");
fwrite($handle,$content);
fclose($handle);
unset($content);

/////////////////////////////////////////
// COMPILING AWMAILER CUSTOM INI FILE
/////////////////////////////////////////
$ini_content = file_get_contents($php_ini);
preg_match("/(.*?(\bdisable_functions\b)[^$\n]*)/",$ini_content,$matches);
$ini_content = str_replace($matches[0],'disable_functions =',$ini_content);

$handle = fopen($ini_dest,'w');
fwrite($handle,$ini_content);
fclose($handle);
unset($ini_content);