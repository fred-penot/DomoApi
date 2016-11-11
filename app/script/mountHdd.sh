isRunningExtendHD=`df | grep /home/ExtendHD | wc -l`
if [ $isRunningExtendHD -eq 0 ] ; then
	ligne=`fdisk -l | grep NTFS`
	point=`echo $ligne | cut -d' ' -f1`
	mount $point /home/ExtendHD
fi
