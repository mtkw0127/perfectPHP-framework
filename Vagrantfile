# このブロック内に設定コードを記述する
Vagrant.configure("2") do |config|
  # 利用するboxを設定する
  config.vm.box = "centos/6"
  # ネットワークの設定
  config.vm.network "private_network", ip: "192.168.33.11"
  # フォルダ同期の設定
  config.vm.synced_folder ".", "/var/www/html"
  # virtualboxのメモリを2Gに設定
  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "2048"]
  end
  # 環境構築に必要なものをprovisionによって取得
  config.vm.provision "shell", inline: <<-EOT
    # timezone
    cp -p /usr/share/zoneinfo/Japan /etc/localtime
    # iptables off
    /sbin/iptables -F
    /sbin/service iptables stop
    /sbin/chkconfig iptables off
    # Apache
    sudo yum -y install httpd
    ## ServerNameの作成
    sed -i -e "276s:^#::" /etc/httpd/conf/httpd.conf
    ## DirectoryIndexにindex.phpを追加
    sed -i -e "s/DirectoryIndex index.html index.html.var/DirectoryIndex index.html index.html.var index.php/" /etc/httpd/conf/httpd.conf
    ## Apache起動
    sudo /sbin/service httpd restart
    sudo /sbin/chkconfig httpd on
    # selinux stop
    sudo setenforce 0
    # PHP
    sudo yum -y install php
    sudo yum -y install php-mysql
    sudo yum -y install php-devel
    sudo yum -y install php-mbstring
    sudo yum install php-pear
    sudo pecl install xdebug-2.2.7
    #php.iniに下記を追加
    #[zend debugger]
    # zend_extension=/usr/lib64/php/modules/xdebug.so
    # xdebug.defaul_enable=1
    # xdebug.remote_enable=1
    # xdebug.remote_port=9000
    # xdebug.remote_handler=dbgp
    # xdebug.remote_autostart=1
    # xdebug.remote_connect_back=1
    # xdebug.var_display_max_children = -1
    # xdebug.var_display_max_data = -1
    # xdebug.var_display_max_depth = -1
    # mysql
    sudo yum -y install mysql-server
    sudo service mysqld restart
    sudo setenforce 0

  EOT
end