<?php

namespace Tests\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{

    /**
     * Тестирование страницы просмотреа продукта.
     */
    public function testShowProduct()
    {
        $crawler = $this->createNewProduct('https://market.yandex.ru/product/1721714804');

        $this->assertGreaterThan(
          0,
          $crawler->filter('html:contains("Description")')->count()
        );
    }

    /**
     * Тестирование валидации формы создания продукта.
     */
    public function testValidateUrl()
    {
        $crawler = $this->createNewProduct('https://market.yandex.ru/product/1721714804/test');
        $this->assertGreaterThan(
          0,
          $crawler->filter('html:contains("must match the pattern")')->count()
        );
    }

    /**
     * Тестирование страницы редактирования продукта.
     */
    public function testEditProduct()
    {
        $crawler = $this->createNewProduct('https://market.yandex.ru/product/1721714804');

        $link = $crawler
          ->selectLink('Edit')->link();
        $client = static::createClient();
        $crawler = $client->click($link);

        $this->assertGreaterThan(
          0,
          $crawler->filter('html:contains("Product edit")')->count()
        );
    }

    /**
     * Тестирование удаления продукта.
     */
    public function testDeleteProduct()
    {
        $client = static::createClient();
        $crawler = $this->createNewProduct('https://market.yandex.ru/product/1721714804');

        $productUri = $crawler->getBaseHref();

        $crawler = $client->request('GET', $productUri);
        $form = $crawler->selectButton('Delete')->form();
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', $productUri);
        $this->assertGreaterThan(
          0,
          $crawler->filter('html:contains("404 Not Found")')->count()
        );
    }

    /**
     * Функция создания нового продукта.
     *
     * @param $yandexUrl string URL страницы товара на Яндекс Маркете
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function createNewProduct(string $yandexUrl)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/product/new');
        $form = $crawler->selectButton('Create Product')->form();
        $form['catalogbundle_product[url]'] = $yandexUrl;
        $crawler = $client->submit($form);

        // После сабмита формы создания продукта в некоторых случаях идет редирект
        // (например, на страницу просмотра), то иногда редиректа нет и в таком
        // случае просто возвращаем $crawler.
        try {
            $crawler = $client->followRedirect();
        } catch (\Exception $e) {
            return $crawler;
        }

        return $crawler;
    }
}
