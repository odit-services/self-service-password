<?php namespace smsapi;
#==============================================================================
# LTB Self Service Password
#
# Copyright (C) 2009-2017 Clement OUDOT
# Copyright (C) 2009-2017 LTB-project.org
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# GPL License: http://www.gnu.org/licenses/gpl.txt
#
#==============================================================================

/**
 * config:
 *
 * get appkey, appsecret and customerkey here:
 *   https://api.ovh.com/createToken/index.cgi?GET=/sms&GET=/sms/*&PUT=/sms/*&DELETE=/sms/*&POST=/sms/*
 *
 * $ovh_appkey = "XXX",
 * $ovh_appsecret = "YYY",
 * $ovh_consumerkey = "ZZZ",
 * //$ovh_smssender = "mysenderstring"
 *
 */

if(file_exists(__DIR__ . '/vendor/autoload.php'))
    require __DIR__ . '/vendor/autoload.php';


class smsOVH
{

    private $ovh_appkey;
    private $ovh_appsecret;
    private $ovh_consumerkey;
    private $ovh_smssender;

    public function __construct($ovh_appkey, $ovh_appsecret, $ovh_consumerkey, $ovh_smssender)
    {
         $this->ovh_appkey = $ovh_appkey;
         $this->ovh_appsecret = $ovh_appsecret;
         $this->ovh_consumerkey = $ovh_consumerkey;
         $this->ovh_smssender = $ovh_smssender;
    }


    /* @function boolean send_sms_by_api(string $mobile, string $message, array $config)
     * Send SMS trough an API
     * @param mobile mobile number
     * @param message text to send
     * @return 1 if message sent, 0 if not
     */
    public function send_sms_by_api($mobile, $message) {

        // https://api.ovh.com/createToken/index.cgi?GET=/sms&GET=/sms/*&PUT=/sms/*&DELETE=/sms/*&POST=/sms/*

        $endpoint = 'ovh-eu';
        $applicationKey=$this->ovh_appkey;
        $applicationSecret=$this->ovh_appsecret;
        $consumer_key=$this->ovh_consumerkey;
        error_log("OVH: $applicationKey $applicationSecret $consumer_key $this->ovh_smssender");
        // print_r($config);
        $Sms = new \Ovh\Sms\SmsApi( $applicationKey,
                           $applicationSecret,
                           $endpoint,
                           $consumer_key );

        $accounts = $Sms->getAccounts();
        // Set the account you will use
        // print_r($accounts);
        $Sms->setAccount($accounts[0]);

        // Get declared senders
        $sender=NULL;
        if (isset($this->ovh_smssender)) {
            $sender = $this->ovh_smssender;
        }
        if ($sender === NULL) {
            $senders = $Sms->getSenders();
            $sender = $senders[0];
        }

        // OVH needs international '+' format
        $mobile = preg_replace('/[^0-9\+]/','',$mobile);
        if ( !preg_match('/^\+/',$mobile) ) {
            if ( preg_match('/^00/',$mobile) ) {
                $mobile=preg_replace('^00','+');
            }
            if ( preg_match('/^0/',$mobile) ) {
                $mobile=preg_replace('/^0/','+33',$mobile);
            }
        }
        // Create a new message
        $Message = $Sms->createMessage();
        $Message->setSender($sender);
        $Message->addReceiver($mobile);
        $Message->setIsMarketing(false);
        return $Message->send($message);

        # PHP code
        # ...

        # Or call to external script
        # $command = escapeshellcmd(/path/to/script).' '.escapeshellarg($mobile).' '.escapeshellarg($message);
        # exec($command);

        return 1;
    }

}
