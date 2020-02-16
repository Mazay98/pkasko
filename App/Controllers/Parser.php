<?php
namespace App;

use \phpQuery;
use \Curl\MultiCurl;

class Parser
{
    public function __construct()
    {

        $start = microtime(true);

        $links = $this->readLinks();

        $multi_curl = new MultiCurl();

        $multi_curl->success(function($instance) {

            phpQuery::newDocument($instance->response);

            $block = pq('.item-tab__chars-item');

            preg_match('~src="(.+)"\salt~', pq('.item-cover__item:first-child')->html(), $img);

            $book = [
                'title'=>pq('.item-detail__title')->text(),
                'price'=>(int)str_replace (' ' , '' , pq('.item-detail__actions')->find('.item-actions__price>b')->text()),
                'img'=>$img[1],
                'author'=>$this->getProperty($block, 'Автор'),
                'year'=>$this->getProperty($block, 'Год издания'),
            ];

            if ($book['price'] <= 500) {
                $book['coast_delivery'] = 100;
            }

            $fileopen=fopen("export.txt", "a+");
            $book = json_encode($book,JSON_UNESCAPED_UNICODE)."\n";
            fwrite($fileopen,$book);
            fclose($fileopen);
            phpQuery::unloadDocuments();


        });

        foreach ($links as $link){
            $multi_curl->addGet(URI.$link);
//            $multi_curl->addGet($link);
        }

        $multi_curl->start();

        $time = microtime(true) - $start;

        printf('Скрипт выполнялся %.4F сек.', $time);

    }
    private function getProperty($block, $needle)
    {
        $result = null;
        $block->each(function ($callback) use (&$result,&$needle) {
            if (stristr(pq($callback)->find('.item-tab__chars-key')->text(), $needle)) {
                $result =  pq($callback)->find('.item-tab__chars-value')->text();
                if(strlen($result) > 50){
                    $result =  pq($callback)->find('.item-tab__chars-value')->children()->text();
                    $result = explode("\n", $result);
                    $result = array_diff($result, array(''));
                }
            }
        });
        return $result;
    }
    private function readLinks()
    {


        $pages=$this->getCountPage();
        $links = [];
        for ($i=1; $i<=$pages; $i++)
        {
            $html = file_get_contents(URI_CATEGORY.'page-'.$i);
            phpQuery::newDocument($html);
            $link = pq('.catalog-products__item ');
            foreach ($link as $el)
            {
                $links[] = pq($el)->find('.book__title-link')->attr('href');
            }
            phpQuery::unloadDocuments();

        }
        return $links;
    }
    private function getCountPage()
    {
        $html = file_get_contents(URI_CATEGORY);
        phpQuery::newDocument($html);

        $title = pq('.catalog-pagination__list')->children('.catalog-pagination__item:not(.catalog-pagination__item:last-child)');
        $text = pq($title)->text();
        $numbers = explode("\n", $text);

        phpQuery::unloadDocuments();
        return ($numbers[count($numbers)-2]);
    }
}