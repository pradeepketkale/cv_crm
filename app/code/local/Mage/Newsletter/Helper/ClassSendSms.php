<?php
class Mage_Newsletter_Helper_ClassSendSms extends Mage_Core_Helper_Abstract
{
	function sendSmsToUser($email_unique) 
    {
?>			
			<script type="text/javascript"> var _kmq = _kmq || []; function _kms(u){ setTimeout

			(function(){ var s = document.createElement('script'); var f = 
			
			document.getElementsByTagName('script')[0]; s.type = 'text/javascript'; s.async = true; 
			
			s.src = u; f.parentNode.insertBefore(s, f); }, 1); } _kms
			
			('//i.kissmetrics.com/i.js');_kms
			
			('//doug1izaerwt3.cloudfront.net/319a0179551de1a2e35f62f9f246b81c015f96ea.1.js'); 
			
			</script>
			
			<script type="text/javascript">
			_kmq.push(['identify', '<?php echo $email_unique;?>']);
			</script>
			<script type="text/javascript" src="http://trk.atomex.net/cgi-bin/tracker.fcgi/conv?px=9126&ty=1"></script>
            
<?php
	}

}
?>