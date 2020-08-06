<?php
namespace AnyPlaceMedia\SendSMS\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class SendSMS extends AbstractHelper
{
    protected $scopeConfig;
    protected $storeDate;
    protected $history;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \AnyPlaceMedia\SendSMS\Model\HistoryFactory $history
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeDate = $date;
        $this->history = $history;
    }

    /**
     * @param $phone
     * @param $message
     * @param $type
    */
    public function sendSMS($phone, $message, $type = 'order')
    {
        $username = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_username',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $password = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_password',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $from = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_from',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $simulation = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_simulation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($simulation && $type !== 'test') {
            $phone = $this->scopeConfig->getValue(
                'sendsms_settings/sendsms/sendsms_settings_simulation_number',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        $phone = $this->validatePhone($phone);

        if (!empty($phone) && !empty($username) && !empty($password)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_URL, 'https://hub.sendsms.ro/json?action=message_send&username='.urlencode($username).'&password='.urlencode($password).'&from='.urlencode($from).'&to='.urlencode($phone).'&text='.urlencode($message));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Connection: keep-alive"));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $status = curl_exec($curl);
            $status = json_decode($status, true);

            # add to history
            $history = $this->history->create();
            $history->setStatus(isset($status['status'])?$status['status']:'');
            $history->setMessage(isset($status['message'])?$status['message']:'');
            $history->setDetails(isset($status['details'])?$status['details']:'');
            $history->setContent($message);
            $history->setType($type);
            $history->setSentOn($this->storeDate->date());
            $history->setPhone($phone);
            $history->save();
        }
    }

    /**
     * @param $phone
     * @return string
     */
    public function validatePhone($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (substr($phone, 0, 1) == '0' && strlen($phone) == 10) {
            $phone = '4'.$phone;
        } elseif (substr($phone, 0, 1) != '0' && strlen($phone) == 9) {
            $phone = '40'.$phone;
        } elseif (strlen($phone) == 13 && substr($phone, 0, 2) == '00') {
            $phone = substr($phone, 2);
        }
        return $phone;
    }

    /**
     * @param $string
     * @return string
     */
    public function cleanDiacritice($string)
    {
        $bad = array(
            "\xC4\x82",
            "\xC4\x83",
            "\xC3\x82",
            "\xC3\xA2",
            "\xC3\x8E",
            "\xC3\xAE",
            "\xC8\x98",
            "\xC8\x99",
            "\xC8\x9A",
            "\xC8\x9B",
            "\xC5\x9E",
            "\xC5\x9F",
            "\xC5\xA2",
            "\xC5\xA3",
            "\xC3\xA3",
            "\xC2\xAD",
            "\xe2\x80\x93");
        $cleanLetters = array("A", "a", "A", "a", "I", "i", "S", "s", "T", "t", "S", "s", "T", "t", "a", " ", "-");
        return str_replace($bad, $cleanLetters, $string);
    }
}
