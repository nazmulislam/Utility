<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\TextAnalysis;

use Carbon\Carbon;
use NazmulIslam\Utility\HTMLParser\HTMLParser;

use Goose\Client as GooseClient;

use Illuminate\Support\Facades\Log;
use NazmulIslam\Utility\Logger\Logger;

class TextAnalysis
{
    public static string $htmlToString;
    public static array $articles;
    public static array $filteredArticles;

    public static $keywordTotalSum;
    public static $keywordTotalSumFiltered;

    public static array $tempArray;

    public static array $keywordPhraseMatch;
    public static array $configuration;

    static function analyseArticle(array $articles, array $keywordPhraseMatch, array $configuration): array
    {

        self::$keywordTotalSum = 0;
        self::$keywordTotalSumFiltered = 0;
        self::$filteredArticles = [];
        self::$keywordPhraseMatch = $keywordPhraseMatch;
        self::$articles = $articles;
        self::$configuration = $configuration;
        /**
         * Loop through each article, analasye text, and keywords
         */
        for ($i = 0; $i < count(self::$articles); $i++) {
            if (isset(self::$articles[$i])) {
                self::$tempArray = [];
                $result = self::$articles[$i];



                self::$tempArray['url'] = $result->link;


                if ($date = self::findDateInArticle($result)) {

                    self::$tempArray['dateOfArticle'] = $date;
                    self::$tempArray['ageOfArticleInDays'] = Carbon::now()->diffInDays(Carbon::createFromTimeString(date('Y-m-d H:i:s', strtotime($date))));
                    self::$tempArray['ageOfArticleInYears'] = Carbon::now()->diffInYears(Carbon::createFromTimeString(date('Y-m-d H:i:s', strtotime($date))));
                } else {
                    self::$tempArray['dateOfArticle'] = 0;
                    self::$tempArray['ageOfArticleInDays'] = 0;
                    self::$tempArray['ageOfArticleInYears'] = 0;
                }


                if (strpos(self::$tempArray['url'], '.pdf') !== FALSE) {

                    self::$htmlToString = isset($result->snippet) && !empty($result->snippet) ? $result->snippet : '';
                    self::$tempArray['contentFrom'] = 'snippet';
                    self::parseArticleForKeywordMatches();
                } else {

                    $parsedString = self::parseHtmlPageToString(self::$tempArray['url']);

                    if (!empty($parsedString) && isset($parsedString)) {
                        self::$tempArray['contentFrom'] = 'web';
                        self::$htmlToString = $parsedString;
                    } else {
                        /**
                         * If page could not be read then use the snippet
                         */
                        self::$htmlToString = isset($result->snippet) && !empty($result->snippet) ? $result->snippet : '';
                        self::$tempArray['contentFrom'] = 'snippet';
                    }
                    self::parseArticleForKeywordMatches();
                }
                self::$articles[$i]->analysis = self::$tempArray;
            }
        }

        return ['articles' => self::$articles, 'keywordTotalSum' => self::$keywordTotalSum];
    }

    static function parseArticleForKeywordMatches()
    {
        $pageWords = self::stringToWords(self::$htmlToString);
        self::$tempArray['pageWords'] = [];
        $keywordCount = [];
        $keywordTotalCount = 0;


        foreach (self::$keywordPhraseMatch as $keyword) {
            // reset the value to zero for each keyword

            $keyWordsMatchedInArticleCount = 0;
            $keyWordsMatchedInArticleCount = self::countKeywordsInArticle(self::$htmlToString, $keyword);
            $keywordStringToLower = strtolower($keyword);
            $keywordCount[] = [
                'keyword' => $keywordStringToLower,
                'count' => $keyWordsMatchedInArticleCount
            ];

            $keywordTotalCount += $keyWordsMatchedInArticleCount;
        }

        self::$tempArray['keywordMatchesTotal'] = $keywordTotalCount;


        self::$keywordTotalSum += $keywordTotalCount;
        self::$tempArray['searchKeywordCount'] = $keywordCount;


        $keywordMatches = array_intersect(self::$keywordPhraseMatch, $pageWords);

        /***
         * TODO WORK IN PROGRESS NOTWORKING
         */
        if (count($keywordMatches) > 0) {
            foreach ($keywordMatches as $keyword) {

                $nlpKeywords = (array) self::$configuration;
                $nlpKeywordsArray =  (array)$nlpKeywords['searchKeywordsNlp'];

                foreach ($nlpKeywordsArray as $nlp) {

                    /**
                     * Add logic here to see if article matches any phrases
                     */
                    if (strpos(self::$htmlToString, $nlp->keywordNlp) !== false) {
                        self::$tempArray['nlp']['keywords'][] = [
                            'keyword' => $keyword,
                            'phrase' => $nlp->keywordNlp,
                            'match' => 'exact'
                        ];
                    }
                }
            }
        }


        self::$tempArray['score']['ageOfArticle'] = $rand_keys = rand(1, 100);
        self::$tempArray['score']['keywordMatchCount'] = ($keywordTotalCount > 0) ? $rand_keys = rand(1, 100) : 0;

        self::$tempArray['pageText'] = json_encode(self::$htmlToString);
        self::$tempArray['pageTextWIthKeyWordHighlighted'] = json_encode(self::findKeywordAndReplaceInString(self::$keywordPhraseMatch, self::$htmlToString));
        self::$tempArray['keywordMatches'] = $keywordMatches;
    }

    static function countKeywordsInArticle(string $htmlToString, string $keyword): int
    {
        return intval(substr_count(strtolower($htmlToString), $keyword));
    }
    static function parseHtmlPageToString(string $url)
    {
        return HTMLParser::htmlPageToString($url);
    }

    static function stringToWords($htmlToString)
    {
        return HTMLParser::stringToWords($htmlToString);
    }

    static function stringToWordsWithCount($htmlToString)
    {
        return HTMLParser::stringToWordsWithCount($htmlToString);
    }


    /**
     * This function tries to get the date of the article, first from the meta tags and then from the snippet
     * Current only configured for google
     * @param type $article
     * @return boolean
     */
    static public function findDateInArticle($article)
    {
        $dateKeys = [
            'creationdate',
            'article.creationdate',
            'article:creationdate',
            'article.created',
            'article:published', // object
            'article:published_time', //object
            'datepublished',
            'article_date_original'
        ];
        if (isset($article->pagemap)) {
            $pageMap = (array) $article->pagemap;

            if (isset($pageMap['metatags'])) {
                $metaTags = (array) $pageMap['metatags'][0];

                foreach ($dateKeys as $key) {
                    if (array_key_exists($key, $metaTags)) {
                        return $metaTags[$key];
                    }
                    /**
                     * use date from snippet when date is not available is meta tags
                     */
                    else if (isset($article->snippet)) {

                        $elements = explode('...', $article->snippet);


                        if (is_array($elements) && count($elements) > 1) {

                            if (self::isValidDate($elements[0])) {

                                return $elements[0];
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
        }


        return false;
    }

    static function isValidDate($date, $format = 'M d, Y')
    {

        $d = date($format, strtotime(trim($date)));

        return $d === trim($date);
    }

    /**
     * Gets the domain name from a url string ignoring subdomain
     * https://domain.com/hello returns domain.com
     * https://subdomain.domain.com/hello returns domain.com
     * @param string $url
     * @return string
     * @throws \Exception
     */
    static public function extractUrl(string $url): string
    {
        //Extracts the subdomain + domain
        $parse = parse_url($url);
        if ($parse == false) {
            Logger::error("Url can't be parsed, possibly invalid. Url: $url", []);
            throw new \Exception("Url being parsed is invalid");
        }
        //Splits the domain into an array
        $domainSections = explode('.', $parse['host']);
        if (count($domainSections) < 2) {
            Logger::error("Domain has too few sections", [$domainSections]);
            throw new \Exception("Domain error");
        }
        //formats the domain to remove any subdomains.
        $domain = $domainSections[count($domainSections) - 2] . "." . $domainSections[count($domainSections) - 1];
        return $domain;
    }

    static function findKeywordAndReplaceInString(array $keywordPhraseMatch, string $text): string
    {


        foreach (self::$keywordPhraseMatch as $keyword) {
            $text = str_replace($keyword, '<span class="badge badge-danger">' . $keyword . '</span>', $text);
        }

        return $text;
    }

    /**
     * Removes the stop words from the main search string
     * @return $this
     */
    static public function removeStopWords(array $stopWords, string $textString, array $commonStopWords, bool $removeWords = false, int $wordsToRemove = 0, bool $disableBracketsRemoval = false): string
    {
        $HTMLparser = new HTMLparser();
        $wordsArray = $HTMLparser->stringToWords(strtolower($textString));
        /**
         * Tokenise string to array, split words into
         */

        if (!$disableBracketsRemoval) {
            //removes any () and its contents
            $text = trim(preg_replace('/\s*\([^)]*\)/', '', $textString));
        } else {
            $text = $textString;
        }



        $temp = strtolower($text);

        $mergedStopWord = array_map('strtolower', array_unique(array_merge($stopWords, $commonStopWords)));
        foreach ($wordsArray as $word) {

            if (in_array($word, $mergedStopWord)) {
                $temp = str_replace($word, "", $temp);
            }
        }

        /*
             * To remove words from string
             */
        if ($removeWords) {
            $tempWords = $HTMLparser->stringToWords(strtolower($temp));
            if (count($tempWords) > 1) {
                $tempShortened = '';
                for ($i = 0; $i < (count($tempWords) - $wordsToRemove); $i++) {
                    $tempShortened .= $tempWords[$i] . " ";
                }
                return trim($tempShortened);
            }
        }

        return trim($temp);
    }
}
