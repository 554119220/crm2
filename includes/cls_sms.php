<?php

/**
 * Class send SMS
 * @author Nixus
 */
class SMS
{
    public $content;
    public $mobile;
    public $sendTime;
    public $url = 'http://n.020sms.com/MSMSEND.ewing';

    private $data = array (
        'ECODE'    => 'ew5654',
        'USERNAME' => '康键人生',
        'PASSWORD' => '6018258140',
        'EXTNO'    => '',
        'MOBILE'   => '',
        'CONTENT'  => '',
        'SEQ'      => 1,
        'SENDTIME' => ''
    );

    /**
     * send
     * @return 发送短信
     * @author Nixus
     **/
    public function send($phone, $msg, $send_date, $username = '')
    {
        $this->data['SENDTIME'] = $send_date;
        $this->data['CONTENT']  = $msg;

        $this->mobile = $phone;
        if (is_array($this->mobile)) {
            $this->mobile = array_unique($this->mobile);

            if (count($this->mobile) > 100) {
                $send_times = ceil(count($this->mobile)/100);
                $this->mobile = breakMobileIntoGroup($this->mobile);
                foreach ($this->mobile as $val){
                    $this->data['MOBILE'] = implode(',', $val);
                    $this->curlPostSMS();

                    if (--$send_times < 0) {
                        break;
                    }
                }
            } else {
                $this->data['MOBILE'] = implode(',', $this->mobile);
                $this->curlPostSMS();
            }
        } else {
            $this->data['MOBILE'] = $this->mobile;
            $this->curlPostSMS();
        }
    }

    /**
     * curlPostSMS
     * @return boolean
     * @author Nixus
     **/
    private function curlPostSMS()
    {
        $param = array();
        foreach ($this->data as $key=>$val){
            $param[] = "$key=$val";
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $param));

        $res = curl_exec($ch);

        if (curl_error($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (200 !== $httpStatusCode) {
                throw new Exception($res, $httpStatusCode);
            }
        }

        curl_close($ch);

        return $res;
    }

    /**
     * breakMobileRow
     * @return string
     * @author Nixus
     **/
    private function breakMobileIntoGroup ()
    {
        $mobileGroup = array();
        $m = $n = 1;
        foreach ($this->mobile as $val){
            if (checkMobileNumber($val)) {
                $mobileGroup[$m] = $val;

                $n++; 
                if ($n %100 == 0) {
                    $m++;
                }
            }
        }

        return $mobileGroup;
    }

    /**
     * checkMobileNumber 
     * @return boolean
     * @author Nixus
     **/
    private function checkMobileNumber ($number)
    {
        $pattern = '/^1[3458]\d{9}$';
        if (preg_match($pattern, $number)) {
            return true;
        } else {
            return false;
        }
    }
}
