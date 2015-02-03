# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "puphpet/debian75-x32"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.50.100"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
    echo "INITIALIZING VM SETUP..."
    debconf-set-selections <<< 'mysql-server mysql-server/root_password password vagrant'
    debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password vagrant'
    echo "UPDATING REPOSITORIES"
    sudo apt-get update >> /dev/null
    echo "UPGRADIN LIBRARIES"
    sudo apt-get upgrade >> /dev/null
    echo "INSTALLING LAMP"
    sudo apt-get install -y apache2 php5 php5-dev php-pear mysql-client mysql-server >> /dev/null
    echo "INSTALLING CURL"
    sudo apt-get install -y curl >> /dev/null
    echo "INSTALLING NODEJS"
    curl -sL https://deb.nodesource.com/setup | sudo bash - >> /dev/null
    sudo apt-get install -y nodejs >> /dev/null
    echo "INSTALLING PCNTL"
    sudo mkdir /tmp/phpsrc && cd /tmp/phpsrc && sudo apt-get source php5 && cd /tmp/phpsrc/php5-*/ext/pcntl && sudo phpize && sudo ./configure && sudo make && sudo make install && sudo echo "extension=pcntl.so" | sudo tee --append /etc/php5/conf.d/pcntl.ini && cd ; >> /dev/null
    echo "INSTALLING MONGODB"
    sudo apt-key adv --keyserver keyserver.ubuntu.com --recv 7F0CEB10 >> /dev/null
    sudo echo 'deb http://downloads-distro.mongodb.org/repo/debian-sysvinit dist 10gen' | sudo tee /etc/apt/sources.list.d/mongodb.list >> /dev/null
    sudo apt-get update >> /dev/null
    sudo apt-get install -y mongodb-org-server mongodb-org-mongos mongodb-org-shell >> /dev/null
    sudo service mongod start >> /dev/null
    echo "INSTALLING MONGODB PHP DRIVER"
    sudo apt-get -y install php-mongo >> /dev/null
    sudo mkdir /tmp/mongodrv && cd /tmp/mongodrv && sudo wget http://pecl.php.net/get/mongo-1.5.8.tgz && sudo tar -zxvf mongo-1.5.8.tgz && cd mongo-* && sudo phpize && sudo ./configure && sudo make && sudo make install && sudo echo "extension=mongo.so" | sudo tee --append /etc/php5/conf.d/mongo.ini && cd ; >> /dev/null
    echo "RESTARTING SERVICES"
    sudo service apache2 restart >> /dev/null
    sudo service mysql restart >> /dev/null
    sudo service mongod restart >> /dev/null
    echo "INSTALLING AGLIO"
    sudo npm -g install aglio >> /dev/null
    echo "CONFIGURING HTTP ENVIRONMENT"
    sudo rm -rf /var/www >> /dev/null
    sudo ln -s /vargrant /var/www >> /dev/null
    sudo service apache2 restart
    echo "EVERYTHING CONFIGURED! READY TO TEST!"
  SHELL
end
