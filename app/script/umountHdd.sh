isRunningExtendHD=`df | grep /home/ExtendHD | wc -l`
if [ $isRunningExtendHD -eq 1 ] ; then
	umount /home/ExtendHD
fi
