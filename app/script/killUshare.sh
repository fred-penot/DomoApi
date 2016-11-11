pidUshare=`ps -ef | grep ushare | grep -v grep | awk '{print $2}'`

sudo kill $pidUshare
