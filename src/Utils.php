<?php

namespace IMDB;

class Utils
{
    /**
     * Fetches page contents DOM with DiDom
     * @param string $url
     * @param array $data
     * @param string $method
     * @return \DiDom\Document
     */
    public static function getContent ($url, $data = [], $method = "GET")
    {
        return new \DiDom\Document(self::curl($url, $data, $method));
    }

    /**
     * Makes a curl query
     * @param $url
     * @param array $data
     * @param string $method
     * @return mixed
     */
    public static function curl($url, $data = [], $method = "GET")
    {
        $plainData = http_build_query($data);

        if ($method == "GET" && sizeof($data) > 0) {
            $url = $url . "?" . $plainData;
        }

        $ch = curl_init($url);
        $header = [];
        $header[0]  = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[]   = "Cache-Control: max-age=0";
        $header[]   = "Connection: keep-alive";
        $header[]   = "Keep-Alive: 300";
        $header[]   = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[]   = "Accept-Language: en-us,en;q=0.5";
        $header[]   = "Pragma: "; // browsers keep this blank.

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);

            if (sizeof($data) > 0) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $plainData);
            }
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);

        return curl_exec($ch);
    }
}
