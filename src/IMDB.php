<?php

namespace IMDB;

use DiDom\Document;

/**
 * Based on Fabian Beiner (mail@fabian-beiner.de)
 *
 * This class can be used to retrieve data from IMDb.com with PHP.
 *
 */

class IMDB
{
    // Define what to return if something is not found.
    public $strNotFound = 'n/A';
    // Define what to return if something is not found.
    public $arrNotFound = array();
    // Please set this to 'true' for debugging purposes only.
    const IMDB_DEBUG = false;
    // Define a timeout for the request of the IMDb page.
    const IMDB_TIMEOUT = 15;
    // Define the "Accept-Language" header language (so IMDb replies with decent localization settings).
    const IMDB_LANG = 'en-US, en';
    // Define the default search type (all/tvtitle/tvepisode/movie).
    const IMDB_SEARCHFOR = 'all';
    
    // Regular expressions, I would not touch them. :)
    const IMDB_AKA = '~<h4 class="inline">Also Known As:<\/h4>(?:\s*)(.*)<span~Ui';
    const IMDB_CAST = '~itemprop="actor"(?:.*)><a href="/name/nm(\d+)/(?:.*)"[ ]?itemprop=\'url\'> <span class="itemprop" itemprop="name">(.*)</span>~Ui';
    const IMDB_CHAR = '~<td class="character">\s+<div>(.*)</div>\s+</td~Ui';
    const IMDB_COUNTRY = '~<a href="/search/title\?(.*)country_of_origin=(.*)"[ ]?itemprop=\'url\'>(.*)</a>~Ui';
    

    const IMDB_COMPANY      = '~Production Co:</h4>(.*)</div>~Ui';
    const IMDB_COMPANY_NAME = '~href="/company/co(\d+)(?:\?.*)"[ ]?itemprop=\'url\'>(.*)</a>~Ui';

    const IMDB_DESCRIPTION = '~<div class="summary_text" itemprop="description">(.*)(?:<a|<\/div>)~Ui';
    const IMDB_DIRECTOR = '~(?:Director|Directors):</h4>(.*)</div>~Ui';
    const IMDB_DIRECTOR_FULLCREDITS = '~Directed by(.*)<h4~Uis';

    const IMDB_GENRE        = '~href="/genre/(.*)(?:\?.*)"(?:\s+|)>(.*)</a>~Ui';

    const IMDB_ID = '~((?:tt\d{6,})|(?:itle\?\d{6,}))~';
    const IMDB_LANGUAGES = '~<a href="/search/title?(.*)primary_language=(.*)"[ ]?itemprop=\'url\'>(.*)</a>~Ui';
    const IMDB_LOCATION = '~href="\/search\/title\?locations=(.*)">(.*)<\/a>~Ui';

    const IMDB_NAME = '~href="/name/nm(\d+?)[/]?(?:.*)"[ ]?itemprop=\'(?:\w+)\'><span class="itemprop" itemprop="name">(.*)</span>~Ui';
    const IMDB_FULLCREDITS_NAME = '~href=\"\/name\/nm(\d+)\/.*\"[^>]*>\s+(.*)~';

    const IMDB_PLOT = '~Storyline</h2>\s+<div class="inline canwrap" itemprop="description">\s+<p>(.*)(?:<em|<\/p>|<\/div>)~Ui';
    const IMDB_POSTER = '~"src="(.*)"itemprop="image" \/>~Ui';
    const IMDB_RATING = '~<span itemprop="ratingValue">(.*)</span>~Ui';
    const IMDB_REDIRECT = '~Location:\s(.*)~';
    const IMDB_RELEASE_DATE = '~Release Date:</h4>(.*)(?:<span|<\/div>)~Ui';
    
    const IMDB_RUNTIME      = '~Runtime:</h4>\s+<time itemprop="duration" datetime="(?:.*)">(.*)</time>~Uis';

    const IMDB_SEARCH = '~<td class="result_text"> <a href="\/title\/(tt\d{6,})\/(?:.*)"(?:\s*)>(?:.*)<\/a>~Ui';
    const IMDB_SEASONS = '~(?:episodes\?season=(\d+))~Ui';

    const IMDB_TITLE = '~meta name="title" content="(.*)(\s\(.*)?"~Ui';
    const IMDB_TITLE_ORIG    = '~<span class="title-extra" itemprop="name">(\s+)?"(.*)"~Uis';

    const IMDB_URL = '~http://(?:.*\.|.*)imdb.com/(?:t|T)itle(?:\?|/)(..\d+)~i';
    

    const IMDB_YEAR         = '~<title>.*\s\(.*(\d{4}).*<\/title>~Ui';

    const IMDB_WRITER = '~(?:Writer|Writers):</h4>(.*)</div>~Ui';
    const IMDB_WRITER_FULLCREDITS = '~Series Writing Credits(.*)<h4~Uis';

    const IMDB_TYPE = '~property=\'og:type\' content="video.(.*)"~Ui';
    const IMDB_IS_RELEASED = '~<div class="star-box giga-star">(.*)</div>~Ui';
    
    private $distributors = [];
    private $production = [];
    
    /**
     * These are the regular expressions used to extract the data.
     * If you don’t know what you’re doing, you shouldn’t touch them.
     */   
    
    // cURL cookie file.
    private $_fCookie = false;
    // IMDb url.
    private $_strUrl = null;
    // IMDb source.
    private $_strSource = null;
    private $_strCreditsSource = null;
    // IMDb cache.
    private $_strCache = 0;
    // IMDb movie id.
    private $_strId = false;
    // Movie found?
    public $isReady = false;
    // Define root of this script.
    private $_strRoot = '';
    private $dom = null;
    private $_strReleasesPageContent = null;
    // Current version.
    const IMDB_VERSION = '6.0.1';
    
    private $typeMap = [
		'tv_show' => "TV Series",
		'movie' => "Movie"
	];

    /**
     * IMDB constructor.
     *
     * @param string  $strSearch The movie name / IMDb url
     * @param integer $intCache  The maximum age (in minutes) of the cache (default 1 day)
     */
    public function __construct($strSearch, $intCache = 1440)
    {
        if (!$this->_strRoot) {
            $this->_strRoot = dirname(__FILE__);
        }
        // Posters and cache directory existant?
        foreach (['posters', 'cache'] as $dir) {
            $workingDir = $this->_strRoot . "/{$dir}/";
            if (!file_exists($workingDir) && !mkdir($workingDir)){
                throw new IMDBException("{$workingDir} not exist and can't create it!");
            }
            if (!is_writable($workingDir) && !chmod($workingDir, 0777)) {
                throw new IMDBException("{$workingDir} is not writable!");
            }
        }
        // cURL.
        if (!function_exists('curl_init')) {
            throw new IMDBException('You need PHP with cURL enabled to use this script!');
        }
        // Debug only.
        if (IMDB::IMDB_DEBUG) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(-1);
            echo '<b>- Running:</b> IMDB::fetchUrl<br>';
        }
        // Set global cache and fetch the data.
        $this->_intCache = (int) $intCache;
        IMDB::fetchUrl($strSearch);
        
        $this->dom = new Document($this->_strSource);
    }

    /**
     * Regular expressions helper function.
     *
     * @param string  $strContent The content to search in
     * @param string  $strRegex   The regular expression
     * @param integer $intIndex   The index to return
     * @return string The match found
     * @return array  The matches found
     */
    private function matchRegex($strContent, $strRegex, $intIndex = NULL)
    {
        $arrMatches = false;

        preg_match_all($strRegex, $strContent, $arrMatches);

        if ($arrMatches === false) return false;

        if ($intIndex != NULL && is_int($intIndex)) {
            if ($arrMatches[$intIndex]) {
                return $arrMatches[$intIndex][0];
            }

            return false;
        }

        return $arrMatches;
    }
    
    /**
     * @param string $sInput Input (eg. HTML).
     *
     * @return string Cleaned string.
     */
    private function cleanString($sInput) {
        $aSearch  = array(
            'Full summary &raquo;',
            'Full synopsis &raquo;',
            'Add summary &raquo;',
            'Add synopsis &raquo;',
            'See more &raquo;',
            'See why on IMDbPro.',
            ' - IMDb'
        );
        $aReplace = array(
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        );
        $sInput   = strip_tags($sInput);
        $sInput   = str_replace('&nbsp;', ' ', $sInput);
        $sInput   = str_replace($aSearch, $aReplace, $sInput);
        $sInput   = html_entity_decode($sInput, ENT_QUOTES | ENT_HTML5);
        if (mb_substr($sInput, -3) === ' | ') {
            $sInput = mb_substr($sInput, 0, -3);
        }
        return trim($sInput);
    }


    /**
     * Returns a shortened text.
     *
     * @param string  $strText   The text to shorten
     * @param integer $intLength The new length of the text
     * @return string
     */
    public function getShortText($strText, $intLength = 100)
    {
        $strText = trim($strText) . ' ';
        $strText = substr($strText, 0, $intLength);
        $strText = substr($strText, 0, strrpos($strText, ' '));
        return $strText . '…';
    }
    
    /**
     * Fetch data from the given url.
     *
     * @param string  $strSearch The movie name / IMDb url
     * @return boolean
     */
    private function fetchUrl($strSearch)
    {
        // Remove whitespaces.
        $strSearch = trim($strSearch);
        
        // "Remote Debug" - so I can see which version you're running.
        // To due people complaining about broken functions while they're
        // using old versions. Feel free to remove this.
        if ($strSearch == '##REMOTEDEBUG##') {
            $strSearch = 'http://www.imdb.com/title/tt1022603/';
            echo '<pre>Running PHP-IMDB-Grabber v' . IMDB::IMDB_VERSION . '.</pre>';
        }
        
        // Get the ID of the movie.
        $strId = IMDB::matchRegex($strSearch, IMDB::IMDB_URL, 1);
        if (!$strId) {
            $strId = IMDB::matchRegex($strSearch, IMDB::IMDB_ID, 1);
        }
        
        // Check if we found an ID ...
        if ($strId) {
            $this->_strId  = preg_replace('~[\D]~', '', $strId);
            $this->_strUrl = 'http://www.imdb.com/title/tt' . $this->_strId . '/';
            $bolFound      = false;
            $this->isReady = true;
        }
        
        // ... otherwise try to find one.
        else {
            $strSearchFor = 'all';
            if (strtolower(IMDB::IMDB_SEARCHFOR) == 'movie') {
                $strSearchFor = 'tt&ttype=ft&ref_=fn_ft';
            } elseif (strtolower(IMDB::IMDB_SEARCHFOR) == 'tvtitle') {
                $strSearchFor = 'tt&ttype=tv&ref_=fn_tv';
            } elseif (strtolower(IMDB::IMDB_SEARCHFOR) == 'tvepisode') {
                $strSearchFor = 'tt&ttype=ep&ref_=fn_ep';
            }
            
            $this->_strUrl = 'http://www.imdb.com/find?s=' . $strSearchFor . '&q=' . str_replace(' ', '+', $strSearch);
            $bolFound      = true;
            
            // Check for cached redirects of this search.
            $fRedirect = @file_get_contents($this->_strRoot . '/cache/' . md5($this->_strUrl) . '.redir');
            if ($fRedirect) {
                if (IMDB::IMDB_DEBUG) {
                    echo '<b>- Found an old redirect:</b> ' . $fRedirect . '<br>';
                }
                $this->_strUrl = trim($fRedirect);
                $this->_strId  = preg_replace('~[\D]~', '', IMDB::matchRegex($fRedirect, IMDB::IMDB_URL, 1));
                $this->isReady = true;
                $bolFound      = false;
            }
        }
        
        // Check if there is a cache we can use.
        $fCache = $this->_strRoot . '/cache/' . md5($this->_strId) . '.cache';
        if (file_exists($fCache)) {
            $bolUseCache = true;
            $intChanged  = filemtime($fCache);
            $intNow      = time();
            $intDiff     = round(abs($intNow - $intChanged) / 60);
            if ($intDiff > $this->_intCache) {
                $bolUseCache = false;
            }
        } else {
            $bolUseCache = false;
        }
        
        if ($bolUseCache) {
            if (IMDB::IMDB_DEBUG) {
                echo '<b>- Using cache for ' . $strSearch . ' from ' . $fCache . '</b><br>';
            }
            $this->_strSource = file_get_contents($fCache);
            return true;
        } else {
            // Cookie path.
            if (function_exists('sys_get_temp_dir')) {
                $this->_fCookie = tempnam(sys_get_temp_dir(), 'imdb');
                if (IMDB::IMDB_DEBUG) {
                    echo '<b>- Path to cookie:</b> ' . $this->_fCookie . '<br>';
                }
            }
            // Initialize and run the request.
            if (IMDB::IMDB_DEBUG) {
                echo '<b>- Run cURL on:</b> ' . $this->_strUrl . '<br>';
            }

            $arrInfo   = $this->doCurl($this->_strUrl);
            $strOutput = $arrInfo['contents'];
            
            // Check if the request actually worked.
            if ($strOutput === false) {
                if (IMDB::IMDB_DEBUG) {
                    echo '<b>! cURL error:</b> ' . $this->_strUrl . '<br>';
                }
                $this->_strSource = file_get_contents($fCache);
                if ($this->_strSource) {
                    return true;
                }
                return false;
            }
            
            // Check if there is a redirect given (IMDb sometimes does not return 301 for this...).
            $fRedirect = $this->_strRoot . '/cache/' . md5($this->_strUrl) . '.redir';
            if ($strMatch = $this->matchRegex($strOutput, IMDB::IMDB_REDIRECT, 1)) {
                $arrExplode = explode('?fr=', $strMatch);
                $strMatch   = ($arrExplode[0] ? $arrExplode[0] : $strMatch);
                if (IMDB::IMDB_DEBUG) {
                    echo '<b>- Saved a new redirect:</b> ' . $fRedirect . '<br>';
                }
                file_put_contents($fRedirect, $strMatch);
                $this->isReady = false;
                // Run the cURL request again with the new url.
                IMDB::fetchUrl($strMatch);
                return true;
            }
            // Check if any of the search regexes is matching.
            elseif ($strMatch = $this->matchRegex($strOutput, IMDB::IMDB_SEARCH, 1)) {
                $strMatch = 'http://www.imdb.com/title/tt' . $strMatch . '/';
                if (IMDB::IMDB_DEBUG) {
                    echo '<b>- Using the first search result:</b> ' . $strMatch . '<br>';
                    echo '<b>- Saved a new redirect:</b> ' . $fRedirect . '<br>';
                }
                file_put_contents($fRedirect, $strMatch);
                // Run the cURL request again with the new url.
                $this->_strSource = null;
                $this->isReady    = false;
                IMDB::fetchUrl($strMatch);
                return true;
            }
            // If it's not a redirect and the HTTP response is not 200 or 302, abort.
                elseif ($arrInfo['http_code'] != 200 && $arrInfo['http_code'] != 302) {
                if (IMDB::IMDB_DEBUG) {
                    echo '<b>- Wrong HTTP code received, aborting:</b> ' . $arrInfo['http_code'] . '<br>';
                }
                return false;
            }
            
            $this->_strSource = $strOutput;
            
            // Set the global source.
            $this->_strSource = preg_replace('~(\r|\n|\r\n)~', '', $this->_strSource);
            
            // Save cache.
            if (!$bolFound) {
                if (IMDB::IMDB_DEBUG) {
                    echo '<b>- Saved a new cache:</b> ' . $fCache . '<br>';
                }
                file_put_contents($fCache, $this->_strSource);
            }
            
            return true;
        }
    }
    
    /**
     * Run a cURL request.
     *
     * @param string $strUrl             URL to run curl on.
     * @param boolean $bolOverWriteSource Overwrite $this->_strSource?
     *
     * @return array Array with cURL informations.
     */
    private function doCurl($strUrl, $bolOverWriteSource = true)
    {
        $oCurl = curl_init($strUrl);
        curl_setopt_array($oCurl, array(
            CURLOPT_VERBOSE => false,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array(
                'Accept-Language:' . IMDB::IMDB_LANG . ';q=0.5'
            ),
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => IMDB::IMDB_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_REFERER => 'http://www.google.com',
            CURLOPT_USERAGENT => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
            CURLOPT_FOLLOWLOCATION => false
            //CURLOPT_COOKIEFILE => $this->_fCookie
        ));
        $strOutput = curl_exec($oCurl);
        
        // Remove cookie.
        if ($this->_fCookie) {
            @unlink($this->_fCookie);
        }
        
        // Get returned information.
        $arrInfo = curl_getinfo($oCurl);
        curl_close($oCurl);
        
        $arrInfo['contents'] = $strOutput;
        
        if ($bolOverWriteSource) {
            $this->_strSource = $strOutput;
        }
        
        // If it's not a redirect and the HTTP response is not 200 or 302, abort.
        if ($arrInfo['http_code'] != 200 && $arrInfo['http_code'] != 302) {
            if (IMDB::IMDB_DEBUG) {
                echo '<b>- Wrong HTTP code received, aborting:</b> ' . $arrInfo['http_code'] . '<br>';
            }
            return false;
        }
        
        return $arrInfo;
    }
    
     /**
     * Gets age rating
     * @return int
     */
    public function getAgeRating ()
    {
        $cert = null;
        try {
            $ratingContainer = $this->dom->find('[itemprop="contentRating"]');

            if (!empty($ratingContainer)) {
                $cert = trim($ratingContainer[0]->text());

                if (empty($cert)) {
                    $cert = trim($ratingContainer[0]->attr("content"));
                }
            }
        } catch (\Exception $e) {
        }

        if (!is_numeric($cert))
        {
            // Based on
            // https://www.esrb.org/ratings/ratings_guide.aspx
            // https://en.wikipedia.org/wiki/Motion_Picture_Association_of_America_film_rating_system
            switch ($cert)
            {
                case "G":
                case "E":
                case "EC":
                    $cert = 1;
                    break;
                case "E+10":
                case "E10+":
                    $cert = 10;
                    break;
                case "T":
                case "PG":
                case "PG-13":
                    $cert = 13;
                    break;
                case "R":
                case "M":
                    $cert = 17;
                    break;
                default:
                    $cert = 18;
                    break;
            }
        }

        return (int)$cert;
    }
    
    /**
     * Returns the "also known as" name.
     *
     * @return string The aka name.
     */
    public function getAka()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_AKA, 1)) {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }

    protected function getReleasesInfoPage() {
        if ($this->_strReleasesPageContent) return $this->_strReleasesPageContent;

        if ($this->isReady) {
            $page = sprintf('http://www.imdb.com/title/tt%s/releaseinfo', $this->_strId);
            $this->_strReleasesPageContent = $this->doCurl($page, false);

            return $this->_strReleasesPageContent;
        }

        return $this->strNotFound;
    }

    public function getReleases() {
        if ($this->hasCachedReleases()) return $this->getCachedReleases();

        $arrInfo = $this->getReleasesInfoPage();
        $releases = [];

        $releasesResults = $this->matchRegex($arrInfo['contents'], "~<td><a href=\"(?:.*?)\"(?:\n?.*?)>(.*?)<\/a><\/td>(?:\n?.*?)<td(?:\n?.*?)>(.*?)<a href=\"(?:.*?)\"(?:\n?.*?)>(.*?)<\/a><\/td>(?:\n?.*?)<td>(?:\s*.*?)(\n?.*?)<\/td>~", 0);

        $countries = $releasesResults[1];
        $dayMonths = $releasesResults[2];
        $years = $releasesResults[3];
        $premiers = $releasesResults[4];

        foreach ($years as $key => $year) {
            $isPremier = (trim($premiers[$key]) != '');

            if ($isPremier) continue;

            $releases[$countries[$key]] = $dayMonths[$key] . $year;
        }

        @file_put_contents($this->getCachedReleasesFileName(), serialize($releases));

        return $releases;
    }

    protected function hasCachedReleases() {
        $fCache = $this->getCachedReleasesFileName();

        if (file_exists($fCache)) {
            $intChanged  = filemtime($fCache);
            $intNow      = time();
            $intDiff     = round(abs($intNow - $intChanged) / 60);

            return ($this->_intCache > $intDiff);
        }

        return false;
    }

    protected function getCachedReleasesFileName() {
        return $this->_strRoot . '/cache/' . md5($this->_strId) . '.releases';
    }

    protected function getCachedReleases() {
        $fCache = $this->getCachedReleasesFileName();

        if (IMDB::IMDB_DEBUG) {
            echo '<b>- Using cache for Releases from ' . $fCache . '</b><br>';
        }

        $arrReturn = @file_get_contents($fCache);
        return unserialize($arrReturn);
    }

    protected function hasCachedAkas() {
        $fCache = $this->getCachedAkasFileName();

        if (file_exists($fCache)) {
            $intChanged  = filemtime($fCache);
            $intNow      = time();
            $intDiff     = round(abs($intNow - $intChanged) / 60);

            return ($this->_intCache > $intDiff);
        }

        return false;
    }

    protected function getCachedAkasFileName() {
        return $this->_strRoot . '/cache/' . md5($this->_strId) . '.akas';
    }

    protected function getCachedAkas() {
        $fCache = $this->getCachedAkasFileName();

        if (IMDB::IMDB_DEBUG) {
            echo '<b>- Using cache for Akas from ' . $fCache . '</b><br>';
        }

        $arrReturn = @file_get_contents($fCache);
        return unserialize($arrReturn);
    }

    /**
     * Returns all local names
     *
     * @return string The aka name.
     */
    public function getAkas()
    {
        if ($this->hasCachedAkas()) return $this->getCachedAkas();

        $arrInfo = $this->getReleasesInfoPage();
        $fCache = $this->getCachedAkasFileName();
        $results  = [];
        $arrReturn = [];

        if (! $arrInfo) {
            return $this->strNotFound;
        }

        $arrReturned = $this->matchRegex($arrInfo['contents'], "~<td>(.*?)<\/td>\s+<td>(.*?)<\/td>~", 0);

        if (isset($arrReturned[1]) && isset($arrReturned[2])) {

            foreach ($arrReturned[1] as $i => $strName) {

                if (strpos($strName, '(') === false) {
                    $arrReturn[] = array(
                        'title' => trim($arrReturned[2][$i]),
                        'country' => trim($strName)
                    );
                }
            }

            @file_put_contents($fCache, serialize($arrReturn));
            $results = $arrReturn;
        }

        return $results;
    }

    /**
     * Returns the cast and character as URL .
     *
     * @return array The movie cast and character as URL (default limited to 20).
     * @param int $intLimit
     * @return array
     */
    public function getCastAndCharacter($intLimit = 20)
    {
        $arrReturn = $this->arrNotFound;

        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_CAST);
            $arrChar     = $this->matchRegex($this->_strSource, IMDB::IMDB_CHAR);

            if (count($arrReturned[2])) {
                foreach ($arrReturned[2] as $i => $strName) {
                    if ($i >= $intLimit) {
                        break;
                    }
                    $arrChar[1][$i] = trim(preg_replace('~\((.*)\)~Ui', '', $arrChar[1][$i]));
                    preg_match_all('~(.*)<a href="/character/ch(\d+)/(\?ref_=(\w+))?"(\s*)>(.*)</a>(.*)~Ui', $arrChar[1][$i], $arrMatches);
                    if (isset($arrMatches[1][0]) && isset($arrMatches[6][0])) {
                        $arrReturn[] = array(
                            'name' => trim($strName),
                            'imdb' => $arrReturned[1][$i],
                            'role' => trim($arrMatches[6][0])
                        );
                    } else {
                        if ($arrChar[1][$i]) {
                            $role        = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $arrChar[1][$i]);
                            $arrReturn[] = array(
                                'name' => trim($strName),
                                'imdb' => $arrReturned[1][$i],
                                'role' => trim(strip_tags($role))
                            );
                        } else {
                            $arrReturn[] = array(
                                'name' => trim($strName),
                                'imdb' => $arrReturned[1][$i],
                                'role' => '-'
                            );
                        }
                    }
                }
            }
        }

        return $arrReturn;
    }
    
    
    /**
     * Returns the companies.
     *
     * @return array The movie companies.
     */
    
    public function getCompany()
    {
        $arrReturn = $this->arrNotFound;

        if ($this->isReady) {

            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_COMPANY, 1);
            $arrReturned  = $this->matchRegex($strContainer, IMDB::IMDB_COMPANY_NAME);

            if (count($arrReturned[2])) {
                foreach ($arrReturned[2] as $i => $strName) {
                    $company     = strip_tags($strName);
                    $arrReturn[] = array(
                        'imdb' => trim($arrReturned[1][$i]),
                        'name' => trim($company)
                    );
                }
            }
        }

        return $arrReturn;
    }
    
    /**
     * Returns the countr(y|ies).
     *
     * @return array The movie countr(y|ies).
     */
    public function getCountry()
    {
        $arrReturn = $this->arrNotFound;

        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_COUNTRY);
            if (count($arrReturned[3])) {
                foreach ($arrReturned[3] as $strName) {
                    $arrReturn[] = trim($strName);
                }
            }
        }
        return $arrReturn;
    }
    
    /**
     * List of companies that produced the media
     * @return array
     */
    public function getProductors ()
    {
        if (empty($this->production)) {
            $this->getCompanyCredits();
        }

        return $this->production;
    }

    /**
     * List of companies that distributed the media
     * @return array
     */
    public function getDistributors ()
    {
        if (empty($this->distributors)) {
            $this->getCompanyCredits();
        }

        return $this->distributors;
    }

    /**
     * Parses all companies that participated in production & distribution of this media
     */
    private function getCompanyCredits ()
    {
        $content = Utils::getContent($this->_strUrl . "companycredits");

        // get the uls order
        $order = [];
        foreach ($content->find("#company_credits_content h4") as $title) {
            $order[] = $title->attr("id");
        }

        $ulList = $content->find("#company_credits_content ul");

        // get the uls order
        foreach ($order as $pos => $type)
        {
            foreach ($ulList[$pos]->find("li") as $company)
            {
                $a = $company->find("a")[0];

                preg_match("/\/company\/co([0-9]+)\?/", $a->attr("href"), $matches);

                if (isset($matches[1]) && !empty($matches[1]))
                {
                    $basicData = [
                        "id" => $matches[1],
                        "name" => $a->text(),
                    ];

                    if ($type == "distributors")
                    {
                        // Hispano Foxfilms S.A.E. (2009) (Spain) (theatrical) => (2009) (Spain) (theatrical)
                        $remainingText = str_replace($basicData["name"], "", $company->text());

                        preg_match("/\(([0-9]{4})-?\) \(([A-Za-z0-9_.]+)\) \((?:theatrical|TV)\)/", $remainingText, $matches);

                        if (!empty($matches)) {
                            $basicData["year"] = (int)$matches[1];
                            $basicData["country"] = $matches[2];
                            $this->{$type}[] = $basicData;
                        }
                    } else {
                        $this->{$type}[] = $basicData;
                    }
                }
            }
        }
    }

    /**
     * Gets the full movie crew divided in departments
     * @return array
     */
    public function getCastCredits ()
    {
        $content = new Document($this->getCredits());
        $titles = $content->find("#fullcredits_content h4");
        $persons = $content->find(".simpleCreditsTable tbody");

        $crew = [];
        
        // skip useless h4s, (DiDom doesnt have :not pseudo class, so we make a foreach)
        foreach ($titles as $pos => $h4)
        {
            if ($h4->hasAttribute("id") || $h4->hasAttribute("name")) {
                unset($titles[$pos]);
            }
        }
        $titles = array_values($titles);

        foreach ($titles as $pos => $h4)
        {
            $title = trim($h4->text(), " \t\n\r\0\x0B\xC2\xA0");

            switch ($title)
            {
                case "Directed by":
                    $name = "director";
                    break;
                case "Music by":
                    $name = "music";
                    break;
                case "Cinematography by":
                    $name = "cinematography";
                    break;
                case "Film Editing by":
                    $name = "editing";
                    break;
                case "Casting By":
                    $name = "casting";
                    break;
                case "Production Design by":
                    $name = "production_design";
                    break;
                case "Art Direction by":
                    $name = "art_direction";
                    break;
                case "Set Decoration by":
                    $name = "set_decoration";
                    break;
                case "Costume Design by":
                    $name = "costume_design";
                    break;
                case "Makeup Department":
                    $name = "makeup_department";
                    break;
                case "Production Management":
                    $name = "production_management";
                    break;
                case "Art Department":
                    $name = "art_department";
                    break;
                case "Sound Department":
                    $name = "sound_department";
                    break;
                case "Special Effects by":
                    $name = "special_effects";
                    break;
                case "Visual Effects by":
                    $name = "visual_effects";
                    break;
                case "Stunts":
                    $name = "stunts";
                    break;
                case "Camera and Electrical Department":
                    $name = "camera_department";
                    break;
                case "Animation Department":
                    $name = "animation_department";
                    break;
                case "Casting Department":
                    $name = "casting_department";
                    break;
                case "Costume and Wardrobe Department":
                    $name = "wardrobe_department";
                    break;
                case "Editorial Department":
                    $name = "editorial_department";
                    break;
                case "Location Management":
                    $name = "location_management";
                    break;
                case "Music Department":
                    $name = "music_department";
                    break;
                case "Transportation Department":
                    $name = "transportation_department";
                    break;
                case "Storyline":
                    $name = "storyline";
                    break;
                case "Photo & Video":
                    $name = "photo";
                    break;
                default:
                    continue 2;
                    break;
            }

            if (!isset($crew[$name])) {
                $crew[$name] = [];
            }

            $regex = "/name\/nm(\d+)\/(?:.*)/";
            
            if (!isset($persons[$pos])) {
                continue;
            }

            foreach ($persons[$pos]->find("a") as $person)
            {
                preg_match($regex, $person->attr("href"), $matches);

                if (!isset($matches[1]) || empty($matches[1])) {
                    continue;
                }

                $crew[$name][] = [
                    "id" => $matches[1],
                    "name" => trim($person->text()),
                ];
            }
        }
        return $crew;
    }
    
    /**
     * Returns the description.
     *
     * @return string The movie description.
     */
    public function getDescription()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_DESCRIPTION, 1)) {
                $strReturn = html_entity_decode(trim($strReturn));

                if(empty($strReturn)){
                    return $this->strNotFound;
                }

                return $strReturn;
            }
        }

        return $this->strNotFound;
    }
    
    /**
     * Returns the director(s) as URL.
     *
     * @return array The movie director(s) as URL.
     */
    public function getDirector()
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_DIRECTOR, 1);
            $arrReturned  = $this->matchRegex($strContainer, IMDB::IMDB_NAME);

            $array = $this->buildPersonArray($arrReturned);

            if (! empty($array)) return $array;

            /*+
             *  if we dont get directors its hidden on "see more" @ cast
             *  Lets find them!
             *
             */
            return $this->getDirectorsFromFullCredits();
        }
        return $this->arrNotFound;
    }
    
    /**
     * Returns the genre(s).
     *
     * @return array The movie genre(s).
     */
    public function getGenre()
    {
        $arrReturn = $this->arrNotFound;

        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_GENRE);

            if (count($arrReturned[1])) {
                foreach ($arrReturned[1] as $strName) {
                    if ($strName != "") {
                        $arrReturn[] = trim($strName);
                    }
                }
            }
        }

        return array_values(array_unique($arrReturn));
    }
    
    
    /**
     * Returns the language(s).
     *
     * @return string The movie language(s).
     */
    public function getLanguages()
    {
        $arrReturn = $this->arrNotFound;

        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_LANGUAGES);
          
            if (count($arrReturned[3])) {
                foreach ($arrReturned[3] as $strName) {
                    $arrReturn[] = trim($strName);
                }
            }
        }

        return $arrReturn;
    }
    
    /**
     * Returns the release date.
     *
     * @return string The movie release date.
     */
    public function getReleaseDate()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_RELEASE_DATE, 1)) {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    
    /**
     * Returns the runtime.
     *
     * @return integer The movie runtime.
     */
    public function getRuntime()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_RUNTIME, 1)) {
                return intval($strReturn);
            }
        }
        return 0;
    }

    /**
     * Returns the seasons.
     *
     * @return string The movie seasons.
     */
    public function getSeasons()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_SEASONS)) {
                if (sizeof($strReturn[1]) > 0) {
                    $strReturn = strip_tags(implode($strReturn[1]));
                    $strFind   = array(
                        '&raquo;',
                        '&nbsp;',
                        'Full episode list',
                        ' '
                    );
                    $strReturn = str_replace($strFind, '', $strReturn);
                    $arrReturn = explode('|', $strReturn);
                    return sizeof($arrReturn);
                }
            }
        }
        return 0;
    }

    /**
     *
     * @return string The title of the movie or $sNotFound.
     * @throws IMDBException
     */
    public function getTitle() {
         if ($this->isReady) {

            $sMatch = $this->matchRegex($this->_strSource, self::IMDB_TITLE_ORIG, 2);
            if (false !== $sMatch && "" !== $sMatch) {
                return $this->cleanString($sMatch);
            }

            $sMatch = $this->matchRegex($this->_strSource, self::IMDB_TITLE, 1);
            if (false !== $sMatch && "" !== $sMatch) {
                return $this->cleanString($sMatch);
            }

        }

        throw new IMDBException("Can't get title", 1);
    }


    /**
     * Returns the URL.
     *
     * @return string The movie URL.
     */
    public function getUrl()
    {
        if ($this->isReady) {
            return $this->_strUrl;
        }

        return $this->strNotFound;
    }
    
    
    /**
     * Returns the writer(s) as URL.
     *
     * @return array The movie writer(s) as URL.
     */
    public function getWriter()
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_WRITER, 1);
            $arrReturned  = $this->matchRegex($strContainer, IMDB::IMDB_NAME);

            $array = $this->buildPersonArray($arrReturned);

            if (! empty($array)) return $array;

            /*+
             *  if we dont get writers its hidden on "see more" @ cast
             *  Lets find them!
             *
             */
            return $this->getWritersFromFullCredits();
        }

        return $this->arrNotFound;
    }

    public function buildPersonArray($array) {
        if (count($array[2])) {
            $arrReturn = [];

            foreach ($array[2] as $i => $strName) {
                $arrReturn[] = array(
                    'imdb' => trim($array[1][$i]),
                    'name' => trim($strName)
                );
            }

            return $arrReturn;
        }

        return $this->arrNotFound;
    }

    protected function getCredits() {
        if ($this->_strCreditsSource) {
            return $this->_strCreditsSource;
        }

        $arrInfo = $this->doCurl('http://www.imdb.com/title/tt'.$this->_strId.'/fullcredits', false);
        return $this->_strCreditsSource = $arrInfo['contents'];
    }

    public function getWritersFromFullCredits() {
        $strContainer = $this->matchRegex($this->getCredits(), IMDB::IMDB_WRITER_FULLCREDITS, 1);
        $arrReturned  = $this->matchRegex($strContainer, IMDB::IMDB_FULLCREDITS_NAME);

        return $this->buildPersonArray($arrReturned);
    }

    public function getDirectorsFromFullCredits() {
        $strContainer = $this->matchRegex($this->getCredits(), IMDB::IMDB_DIRECTOR_FULLCREDITS, 1);
        $arrReturned  = $this->matchRegex($strContainer, IMDB::IMDB_FULLCREDITS_NAME);

        return $this->buildPersonArray($arrReturned);
    }
    
    /**
     * Returns the movie year.
     *
     * @return string The year of the movie.
     */
    public function getYear()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_YEAR, 1)) {
                return intval($strReturn);
            }
        }
        return 0;
    }


    /**
     * Returns the type of the imdb media
     *
     * @return bool|mixed|string Type of the imdb media
     * @throws IMDBException
     */
    public function getType()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_TYPE, 1)) {
                // some cases there's no info in that place
                if (is_string($strReturn)) {
                    $strReturn = str_replace("&nbsp;-&nbsp;", '', $strReturn);
                    $strReturn = str_replace("&nbsp;", '', $strReturn);
                    $type = trim($strReturn, " ");

                    return (empty($type)) ? "Movie" : $this->typeMap[$type];
                }
            }

            throw new IMDBException("Can't get type");
        }

        return false;
    }
    
    /**
     * Release date doesn't contain all the information we need to create a media and 
     * we need this function that checks if users can vote target media (if can, it's released).
     *
     * @return  true If the media is released (users can vote)
     */
    public function isReleased()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_IS_RELEASED, 1)) {
                // removing the html tags and spaces
                $strReturn = trim(strip_tags($strReturn));
                // expected this string if is not released, in other cases will get voting results
                if (isset($strReturn) && $strReturn == 'Not yet released') {
                    return false;
                }
            }
        }
        return true;
    }
    
    
}
