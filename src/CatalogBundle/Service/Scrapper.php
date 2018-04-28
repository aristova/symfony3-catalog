<?php

namespace CatalogBundle\Service;

use Symfony\Component\DomCrawler\Crawler;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Scrapper
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function parse(string $url)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, false);

        // Сохраняем HTML страницы.
        $page = curl_exec($c);

        // Если не получилось получить доступ к странице (например, из-за частых
        // запросов или если страницы не существует).
        if (empty($page)) {
            return;
        }

        $crawler = new Crawler($page, 'https://market.yandex.ru/');
        try {
            $imageExtPath = $crawler->filter('.n-gallery__item > img')
              ->attr('src');
            $imageExtPath = 'https:' . $imageExtPath;
            $relPath = $this->parseImage($imageExtPath);

            $priceFull = $crawler->filter('.n-product-price-cpa2 > .price')
              ->first()
              ->text();

            $price = preg_replace("/[^0-9]/", '', $priceFull);
            $title = $crawler->filter('.n-title__text > h1')->first()->text();
            $description = $crawler->filter('.n-product-spec-list')->html();
        } catch (\Exception $e) {
            $relPath = null;
            $price = null;
            $title = null;
            $description = null;
        }

        $product = new \stdClass();
        $product->title = $title;
        $product->image = $relPath;

        $product->price = $price;
        $product->description = $description;


        return $product;
    }


    /**
     * Функция сохраняет картинку в файловую систему и отдает тносительный путь.
     *
     * @param string $imageExtPath
     * @return string Относительный путь к изображению
     */
    public function parseImage(string $imageExtPath)
    {
        $image_name = uniqid();

        $rootDir = $this->container->getParameter('kernel.project_dir');
        $relPath = '/uploads/images/' . $image_name;
        $imageFolderPath = $rootDir . "/web" . $relPath;
        $path = $imageFolderPath;


        $headers = get_headers($imageExtPath);

        // Многие картинки на Яндекс Маркете не имеют расширения "jpg", поэтому
        // их mime-type некорректно определяется. Для избежания этого проверяем, что
        // их тип - jpg и присваиваем картинке это разрешение.
        if (isset($headers) && array_search('Content-Type: image/jpeg', $headers)) {
            $path .= '.jpeg';
            $relPath .= '.jpeg';
        }
        file_put_contents($path, file_get_contents($imageExtPath));
        return $relPath;
    }
}
