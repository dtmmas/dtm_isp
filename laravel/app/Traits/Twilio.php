<?php
namespace App\Traits;
use Illuminate\Support\Facades\Log;

class Twilio
{
	protected $secret_key;
	protected $sid;
	protected $MessagingServiceSid;
	protected $url_base;
	
	function __construct($secret_key, $sid, $ServiceSid )
	{
		$this->token = $secret_key;
		$this->sid = $sid;
		$this->MessagingServiceSid = $ServiceSid;
		$this->url_base = 'https://api.twilio.com/2010-04-01/';
	}

	public function SendMsm($to, $message)
	{
	  	$body = [
	      "MessagingServiceSid"=> $this->MessagingServiceSid,
	      "To"=> $to,
	      "Body"=> $message
	    ];

	  	return $this->enviarSMS(http_build_query($body));
	}

	//    curl 'https://api.twilio.com/2010-04-01/Accounts/[TWILIO_SID]/Messages.json' -X POST \
	// --data-urlencode 'To=+50233024105' \
	// --data-urlencode 'MessagingServiceSid=[TWILIO_MESSAGING_SERVICE_SID]' \
	// --data-urlencode 'Body=SMS DESDE TWILIO' \
	// -u [TWILIO_SID]:[TWILIO_TOKEN]
	//https://console.twilio.com/us1/monitor/logs/sms?frameUrl=%2Fconsole%2Fsms%2Flogs%3Fx-target-region%3Dus1&currentFrameUrl=%2Fconsole%2Fsms%2Flogs%3F__override_layout__%3Dembed%26bifrost%3Dtrue%26x-target-region%3Dus1   HISTORIAL DE SMS ENVIADOS
	private function enviarSMS($body)
	{
		$Status = ['queued','accepted','sent','delivered'];
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->url_base."/Accounts/".$this->sid."/Messages.json",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_USERPWD => $this->sid.":".$this->token,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		$rps =json_decode($response, true);

		if(($http_code>=200 && $http_code<300)){
			$respuesta = array('respuesta' => (array_search($rps['status'], $Status)), 'http_code'=> $http_code, 'error' => @$rps['error_message'] );
			if(!(array_search($rps['status'], $Status))){
				\Log::info('Twilio status', [$rps['status']]);
				\Log::info('Twilio status', [$response]);
			}
		}else{
			$respuesta = array('respuesta' => false, 'http_code'=> $http_code, 'error' => @$rps['message'] );
			\Log::info('Twilio response', [$response]);
			\Log::info('Twilio http_code', [$http_code]);
			\Log::info('Twilio err', [$err]);
		}

		return $respuesta;
	}
}
?>
