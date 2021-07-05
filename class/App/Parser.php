<?php
namespace App;
use voku\helper\HtmlDomParser;

class Parser
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getRequest($ajaxUrl='')
    {
        if ($ajaxUrl)
            $this->url = $ajaxUrl;

        $ref='https://www.google.com/';
        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $ref);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host:www.electronictoolbox.com', 'Accept-Language:en-US,en;q=0.8;q=0.5,en;q=0.3', 'Accept-Encoding:deflate, br', 'Referer:https://www.electronictoolbox.com/category/83118/new-products/', 'X-Requested-With:XMLHttpRequest', 'Connection:keep-alive', 'Cookie:xid57=e0852c46dbcd11eb93120cc47a432d82; _gcl_au=1.1.439483861.1625296294; _ga_MVTMKKCKT6=GS1.1.1625300489.2.1.1625301659.0; _ga=GA1.2.763146710.1625296295; _gid=GA1.2.1051809546.1625296300; __stripe_mid=3006bdf7-fec3-4460-94b2-32db83b13a84b485bf; __stripe_sid=97a8bb75-7367-4fb3-8f4d-b986fc418f8f589793', 'Accept: application/json'));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;

    }

    public function createData()
    {
        $category = $this->getRequest();
        $html = HtmlDomParser::str_get_html($category);
        $links = $html->find('.departments-submenu-title');
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . 'JSON/feed.json', '{',FILE_APPEND);

        foreach ($links as $key => $val)
        {
            $url = 'https://www.electronictoolbox.com/api' . $val->href . '?page=1';
            $ajax = json_decode($this->getRequest($url), true);
            $currentP = $ajax['pager']['currentPage'];
            $allP = $ajax['pager']['pagesCount'];

            while ($allP >= $currentP)
            {
                $data = $this->getRequest('https://www.electronictoolbox.com/api' . $val->href . '?page=' . $currentP);

                if ($val->href == '/category/53387/universal-power-group/' && $currentP == $allP)
                    $this->renderJson($data, true);
                else
                    $this->renderJson($data);

                $currentP++;
            }
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . 'JSON/feed.json', '}',FILE_APPEND);

    }

    public function renderJson($resp, $last=false)
    {
        $toJson = [];
        $fileName = $_SERVER['DOCUMENT_ROOT'] . 'JSON/feed.json';
        $data = json_decode($resp,true);

        foreach ($data['items'] as $item)
            $toJson[$item['productid']] = $item;

        $result = json_encode($toJson, JSON_UNESCAPED_UNICODE);

        if (!$last)
            file_put_contents($fileName,substr($result, 1, -1) . ',', FILE_APPEND);
        else
            file_put_contents($fileName,substr($result, 1, -1), FILE_APPEND);

        unset($result);

    }
}