htmlSQL - Version 0.6
=====================

htmlSQL - это экспериментальная PHP-библиотека, которая позволяет выбирать атрибуты и содержимое тегов HTML с помощью синтаксиса, похожего на SQL.
Это значит, что для этой работы вам не нужно писать сложные регулярные выражения.

**В htmlSQL запросы могут быть примерно такими:**

    SELECT href,title FROM a WHERE $class == "list"
           ^ Attributes    ^       ^ search query (can be empty)
             to return     ^
                           ^ HTML tag to search in
                             "*" is possible = all tags

Этот запрос выдаст массив со всеми ссылками (<a>), у которых установлен атрибут: `class="list"`.

Проект возрождён
--------------------

Похожие проекты:

* PHP: [phpQuery](http://code.google.com/p/phpquery/), [SimpleXML](http://www.php.net/simplexml), [DOM](http://www.php.net/dom), [symfony/dom-crawler](https://symfony.com/doc/current/components/dom_crawler.html), [Cquery](https://github.com/cacing69/cquery)
* Perl: [WWW::Mechanize](http://search.cpan.org/dist/WWW-Mechanize/), [pQuery](http://search.cpan.org/~ingy/pQuery-0.07/lib/pQuery.pm)
* Python: [Scrapy](http://scrapy.org/), [Beautiful Soup](http://www.crummy.com/software/BeautifulSoup/)
* JavaScript: [node.js](http://blog.nodejitsu.com/jsdom-jquery-in-5-lines-on-nodejs)
* .NET: [Html Agility Pack](http://htmlagilitypack.codeplex.com/)

См. также:

* [Stack Overflow: Options for HTML scraping?](http://stackoverflow.com/questions/2861/options-for-html-scraping)
* [Stack Overflow: HTML Scraping in PHP](http://stackoverflow.com/questions/34120/html-scraping-in-php)
* [Hacker News: PHP class to query the web by an SQL like language](http://news.ycombinator.com/item?id=2097008)
* [Hacker News: Ask YC: What do you scrape? How do you scrape?](http://news.ycombinator.com/item?id=159025)


Требования
------------

- PHP5 или выше
- [Snoopy PHP class - Version 1.2.3](http://sourceforge.net/projects/snoopy/) (входит в проект, нужен только для парсинга сайтов)


Использование
-----

Просто подключите файлы "snoopy.php" и "htmlsql.php" в ваш скрипт и всё должно работать.
В каталоге examples есть 12 коротеньких примеров с использованием библиотеки.


ВНИМАНИЕ!
-------

Для проверки инструкции WHERE использутся стандартная функция PHP `eval()` со всеми вытекающими отсюда небезопасностями.


Todo
----

* Реализовать возможность работы с невалидными HTML-документами
* Заменить вызов `eval()` на что-то более безопасное
* Добавить больше информативных сообщений об ошибках
* Перейти на php-curl
* Добавить другие важные инструкции из SQL: LIMIT, ORDER BY, IN (NOT IN)


Изначальный автор
------

* [Jonas John](http://www.jonasjohn.de/)


Лицензия
-------

htmlSQL использует модифицированную лицензию BSD.
