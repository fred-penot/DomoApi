<?php
$app['parameter.db.driver'] = 'pdo_mysql';
$app['parameter.db.host'] = 'localhost';
$app['parameter.db.usenet.host'] = 'localhost';
$app['parameter.db.name'] = 'DomoApi';
$app['parameter.db.usenet.name'] = 'HomeOS';
$app['parameter.db.login'] = 'root';
$app['parameter.db.password'] = 'Fwedelph6580$';

$app['parameter.db.forex.host'] = 'localhost';
$app['parameter.db.forex.name'] = 'Forex';
$app['parameter.db.forex.login'] = 'root';
$app['parameter.db.forex.password'] = 'Fwedelph6580$';

$app['parameter.log.name'] = 'app.log';
$app['parameter.log.forex.archive'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'forex.archive.log';
$app['parameter.log.forex.current'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'forex.current.log';
$app['parameter.log.japscan.chapter'] = 'japscan.chapter.log';
$app['parameter.log.domotic.gally'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'domotic.gally.log';
$app['parameter.log.playlist'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'playlist.log';
$app['parameter.log.maj.media.music'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'maj.media.music.log';
$app['parameter.log.maj.media.video'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'maj.media.video.log';
$app['parameter.log.manga.save'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'manga.save.log';
$app['parameter.log.manga.update'] = realpath(__DIR__ . '/log').DIRECTORY_SEPARATOR.'manga.update.log';

$app['parameter.path_hdd'] = '/home/ExtendHD';
$app['parameter.mount_point_hdd'] = 'système;/dev/sda1|externe;/dev/sdc1|média culte;/dev/sdd1|freebox;//mafreebox.freebox.fr/Disque dur';
$app['parameter.start_ushare'] = 'ushare -d -D';
$app['parameter.ssh_host'] = '192.168.1.50';
$app['parameter.ssh_login'] = 'fwed';
$app['parameter.ssh_password'] = 'Fwedelph6580$';
$app['parameter.command_start_kodi'] = 'DISPLAY=:0 kodi';
$app['parameter.command_running_kodi'] = '/usr/bin/kodi';
$app['parameter.command_reboot'] = 'sudo /sbin/reboot';
$app['parameter.command_apache_restart'] = 'sudo /etc/init.d/apache2 restart';
$app['parameter.command_mount_hdd'] = 'sudo ' . realpath(__DIR__ . '/script/mountHdd.sh');
$app['parameter.command_umount_hdd'] = 'sudo ' . realpath(__DIR__ . '/script/umountHdd.sh');
$app['parameter.command_kill_ushare'] = 'sudo ' . realpath(__DIR__ . '/script/killUshare.sh');
$app['parameter.command_start_ushare'] = 'sudo ' . realpath(__DIR__ . '/script/runUshare.sh');
$app['parameter.command_free_memory'] = 'sudo ' . realpath(__DIR__ . '/script/freeMemory.sh');
$app['parameter.sabnzbd.api.url'] = 'http://192.168.1.50:7680/sabnzbd/api?output=json';
$app['parameter.sabnzbd.api.key'] = '&apikey=49c6e4b1359373b6ede3a1afce2b414e';
$app['parameter.sabnzbd.api.mode.queue'] = '&mode=queue&start=START';
$app['parameter.sabnzbd.api.mode.pause'] = '&mode=pause';
$app['parameter.sabnzbd.api.mode.pause.id'] = '&name=pause&mode=queue&value=';
$app['parameter.sabnzbd.api.mode.resume'] = '&mode=resume';
$app['parameter.sabnzbd.api.mode.resume.id'] = '&name=resume&mode=queue&value=';
$app['parameter.sabnzbd.api.mode.speed'] = '&mode=config&name=speedlimit&value=';
$app['parameter.sabnzbd.api.mode.queue.delete'] = '&mode=queue&name=delete&del_files=1&value=';
$app['parameter.sabnzbd.api.mode.history'] = '&mode=history&start=START';
$app['parameter.sabnzbd.api.mode.history.delete'] = '&mode=history&name=delete&del_files=1&value=';
$app['parameter.sabnzbd.api.mode.categories'] = '&mode=get_cats';
$app['parameter.sabnzbd.api.mode.change.name'] = '&mode=queue&name=rename&value=:id&value2=:name';
$app['parameter.sabnzbd.api.mode.change.category'] = '&mode=change_cat&value=:id&value2=:name';
$app['parameter.sabnzbd.api.mode.add.url'] = '&mode=addurl&name=:url&nzbname=:title';
$app['parameter.sabnzbd.api.mode.add.url.category'] = '&cat=:category';

$app['parameter.utils.japscan.wget'] = true;
$app['parameter.utils.japscan.image_magik'] = false;

$app['parameter.ebook.path.src'] = realpath(__DIR__ . '/../tmp').DIRECTORY_SEPARATOR.'ebook/src';
$app['parameter.ebook.path.dest'] = realpath(__DIR__ . '/../tmp').DIRECTORY_SEPARATOR.'ebook/dest';
$app['parameter.ebook.path.pdf'] = realpath(__DIR__ . '/../tmp').DIRECTORY_SEPARATOR.'ebook/pdf';

$app['parameter.path.in.forex'] = realpath(__DIR__ . '/../file/in/forex');
$app['parameter.path.end.forex'] = realpath(__DIR__ . '/../file/end/forex');
$app['parameter.path.temp.forex'] = realpath(__DIR__ . '/../tmp').DIRECTORY_SEPARATOR.'forex';

$app['parameter.forex.login'] = 'fwedz';
$app['parameter.forex.password'] = '06Holly05';
$app['parameter.forex.url.auth'] = 'http://webrates.truefx.com/rates/connect.html?u=__LOGIN__&p=__PASSWORD__&q=myforex';
$app['parameter.forex.url.cotations'] = 'http://webrates.truefx.com/rates/connect.html?id=__ID__&f=csv';
$app['parameter.forex.url.cotation'] = 'http://webrates.truefx.com/rates/connect.html?id=__ID__&c=__COTATION__&f=csv';

$app['parameter.path.in.convert'] = realpath(__DIR__ . '/../file/in/convert');
$app['parameter.path.end.convert'] = realpath(__DIR__ . '/../file/end/convert');

$app['parameter.freebox.token'] = '9lbuozyry1GwNFugWk4u80t8nmIJbqPCOgATnNX4hBaGjUgJVJW0owOyaX/GE0s4';